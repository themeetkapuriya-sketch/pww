<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\AttendanceRecord;
use App\Models\BillOfMaterial;
use App\Models\Client;
use App\Models\ClientPlant;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LaborLog;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\Purchase;
use App\Models\RawMaterial;
use App\Models\SalaryAdvance;
use App\Models\SalaryPayment;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Setting;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\BillingService;
use App\Services\FinancialService;
use App\Services\PayrollService;
use App\Services\ProductionService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ErpFlowTest extends TestCase
{
    use RefreshDatabase;

    protected ProductionService $productionService;

    protected BillingService $billingService;

    protected PayrollService $payrollService;

    protected FinancialService $financialService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $this->productionService = resolve(ProductionService::class);
        $this->billingService = resolve(BillingService::class);
        $this->payrollService = resolve(PayrollService::class);
        $this->financialService = resolve(FinancialService::class);
    }

    /* =========================================================================
     * SECTION 1: INVENTORY, BILL OF MATERIALS & PRODUCTION ENGINE
     * ========================================================================= */

    /**
     * Test the Multi-Stage Stock Auto-Deduction Engine.
     */
    public function test_stock_auto_deduction_engine(): void
    {
        $user = User::create([
            'name' => 'Manager User',
            'email' => 'manager@pww.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $iron = RawMaterial::create([
            'material_name' => 'Iron Coil',
            'unit' => 'kg',
            'current_stock' => 100.00,
            'safety_threshold' => 10.00,
            'average_purchase_price' => 50.00,
        ]);

        $rack = Product::create([
            'product_name' => 'Super Rack',
            'sku' => 'SR-01',
            'current_stock' => 10,
            'selling_price' => 1000.00,
        ]);

        // BOM: Requires 5.0kg iron + 10% waste = 5.5kg per rack
        BillOfMaterial::create([
            'product_id' => $rack->id,
            'raw_material_id' => $iron->id,
            'required_quantity' => 5.0,
            'waste_percentage' => 10.00,
        ]);

        $staff = StaffProfile::create([
            'user_id' => null,
            'full_name' => 'Worker Amit',
            'wage_type' => 'per-day',
            'piece_rate_per_unit' => 20.00,
        ]);

        // Log production of 10 racks (55.0kg consumed)
        $productionLog = $this->productionService->logProduction(
            $rack->id,
            10,
            1,
            $user->id,
            Carbon::now()->toDateString(),
            [
                [
                    'staff_profile_id' => $staff->id,
                    'units_completed' => 10,
                ],
            ]
        );

        $this->assertInstanceOf(ProductionLog::class, $productionLog);
        $this->assertEquals(20, Product::find($rack->id)->current_stock);
        $this->assertEquals(45.00, RawMaterial::find($iron->id)->current_stock);

        $laborLog = LaborLog::where('production_log_id', $productionLog->id)->first();
        $this->assertNotNull($laborLog);
        $this->assertEquals(10, $laborLog->units_completed);
        $this->assertEquals(200.00, $laborLog->calculated_payout);
        $this->assertEquals('pending', $laborLog->status);

        // Exceed remaining stock
        $this->expectException(InsufficientStockException::class);
        $this->productionService->logProduction(
            $rack->id,
            10,
            0,
            $user->id,
            Carbon::now()->toDateString()
        );
    }

    /**
     * Test Multi-Product Batch Production Logging.
     */
    public function test_multi_product_batch_production_logging(): void
    {
        $user = User::factory()->create();

        $rawMaterial1 = RawMaterial::create([
            'material_name' => 'Steel Wire 4mm',
            'unit' => 'kg',
            'current_stock' => 500.0,
            'safety_threshold' => 50.0,
            'average_purchase_price' => 70.0,
        ]);

        $product1 = Product::create([
            'product_name' => 'Rack Frame A',
            'sku' => 'RACK-A',
            'hsn_code' => '7308',
            'uom' => 'pcs',
            'unit_weight_kg' => 2.0,
            'selling_price' => 350.0,
            'current_stock' => 10,
        ]);

        $product2 = Product::create([
            'product_name' => 'Rack Frame B',
            'sku' => 'RACK-B',
            'hsn_code' => '7308',
            'uom' => 'pcs',
            'unit_weight_kg' => 3.0,
            'selling_price' => 500.0,
            'current_stock' => 5,
        ]);

        BillOfMaterial::create([
            'product_id' => $product1->id,
            'raw_material_id' => $rawMaterial1->id,
            'required_quantity' => 2.0,
        ]);

        BillOfMaterial::create([
            'product_id' => $product2->id,
            'raw_material_id' => $rawMaterial1->id,
            'required_quantity' => 3.0,
        ]);

        $response = $this->actingAs($user)->post(route('production.store'), [
            'production_date' => now()->format('Y-m-d'),
            'recorded_by' => $user->id,
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity_manufactured' => 10,
                    'quantity_rejected' => 1,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity_manufactured' => 5,
                    'quantity_rejected' => 0,
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertEquals(20, Product::find($product1->id)->current_stock);
        $this->assertEquals(10, Product::find($product2->id)->current_stock);
        $this->assertEquals(465.0, RawMaterial::find($rawMaterial1->id)->current_stock);
    }

    /**
     * Test BOM Raw Material Restoration on Production Log Deletion.
     */
    public function test_production_deletion_restores_bom_raw_materials(): void
    {
        $admin = User::create([
            'name' => 'Prod Manager',
            'email' => 'prod@pww.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
            'status' => 'active',
        ]);

        $rawMaterial = RawMaterial::create([
            'material_name' => 'Steel Rod 8mm',
            'unit' => 'kg',
            'current_stock' => 1000.00,
            'safety_threshold' => 100.00,
            'average_purchase_price' => 60.00,
        ]);

        $product = Product::create([
            'product_name' => 'Steel Grating 1x1m',
            'hsn_code' => '7314',
            'selling_price' => 1500.00,
            'current_stock' => 10,
            'uom' => 'piece',
        ]);

        BillOfMaterial::create([
            'product_id' => $product->id,
            'raw_material_id' => $rawMaterial->id,
            'required_quantity' => 10.00,
            'waste_percentage' => 0.00,
        ]);

        $res = $this->actingAs($admin)->postJson(route('production.store'), [
            'product_id' => $product->id,
            'quantity_manufactured' => 20,
            'quantity_rejected' => 0,
            'production_date' => now()->toDateString(),
        ]);
        $res->assertStatus(200);
        $prodLogId = $res->json('data.id');

        $this->assertEquals(800.00, (float) $rawMaterial->fresh()->current_stock);
        $this->assertEquals(30, $product->fresh()->current_stock);

        $delRes = $this->actingAs($admin)->deleteJson(route('production.delete', $prodLogId));
        $delRes->assertStatus(200);

        $this->assertEquals(1000.00, (float) $rawMaterial->fresh()->current_stock);
        $this->assertEquals(10, $product->fresh()->current_stock);
    }

    /**
     * Test Product Stock Manual Adjustments.
     */
    public function test_product_stock_adjustment_endpoint(): void
    {
        $admin = User::create([
            'name' => 'Inventory Manager',
            'email' => 'inv_mgr_'.uniqid().'@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $product = Product::create([
            'product_name' => 'Stock Test Frame',
            'hsn_code' => '73269099',
            'selling_price' => 500.00,
            'current_stock' => 50,
            'uom' => 'piece',
        ]);

        // 1. Set total to 120
        $res1 = $this->actingAs($admin)->postJson(route('inventory.goods.adjust', $product->id), [
            'adjustment_type' => 'set_total',
            'quantity' => 120,
            'reason' => 'Physical Count / Audit Correction',
            'notes' => 'Verified on rack 3',
        ]);
        $res1->assertStatus(200)->assertJson(['success' => true, 'new_stock' => 120]);
        $this->assertEquals(120, $product->fresh()->current_stock);

        // 2. Add 30 pcs
        $res2 = $this->actingAs($admin)->postJson(route('inventory.goods.adjust', $product->id), [
            'adjustment_type' => 'add_stock',
            'quantity' => 30,
            'reason' => 'Sample / Trial Dispatch',
        ]);
        $res2->assertStatus(200)->assertJson(['success' => true, 'new_stock' => 150]);
        $this->assertEquals(150, $product->fresh()->current_stock);

        // 3. Deduct 50 pcs
        $res3 = $this->actingAs($admin)->postJson(route('inventory.goods.adjust', $product->id), [
            'adjustment_type' => 'reduce_stock',
            'quantity' => 50,
            'reason' => 'Damaged in Warehouse / Scrapped',
        ]);
        $res3->assertStatus(200)->assertJson(['success' => true, 'new_stock' => 100]);
        $this->assertEquals(100, $product->fresh()->current_stock);
    }

    /* =========================================================================
     * SECTION 2: B2B CLIENTS, PLANTS & SALES ORDERS
     * ========================================================================= */

    /**
     * Test Client & Plant full CRUD flow with 1-click plant creation & plant-specific GSTIN.
     */
    public function test_client_and_plant_crud_operations(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_client@pww.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'approved',
            'is_active' => true,
        ]);

        // 1. Create Client with 1-Click Primary Plant Creation
        $response = $this->actingAs($user)->postJson(route('clients.store'), [
            'company_name' => 'Supreme Logistics Pvt Ltd',
            'client_email' => 'contact@supremelogistics.com',
            'gst_number' => '24SUPREME1234A1Z1',
            'corporate_address' => 'HQ Tower, Ring Road, Surat, Gujarat',
            'create_primary_plant' => 1,
            'plant_name' => 'Surat Main Factory',
            'state' => 'Gujarat',
            'plant_gst_number' => '24SUPREME1234A1Z1',
            'shipping_address' => 'Plot 45 GIDC, Surat, Gujarat',
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);

        /** @var Client|null $client */
        $client = Client::where('company_name', 'Supreme Logistics Pvt Ltd')->first();
        $this->assertNotNull($client);
        $this->assertEquals(1, $client->plants()->count());

        /** @var ClientPlant $plant */
        $plant = $client->plants()->first();
        $this->assertEquals('Surat Main Factory', $plant->plant_name);
        $this->assertEquals('24SUPREME1234A1Z1', $plant->gst_number);

        // 2. Add Secondary Interstate Plant
        $response = $this->actingAs($user)->postJson(route('clients.plants.store'), [
            'client_id' => $client->id,
            'plant_name' => 'Mumbai Distribution Hub',
            'state' => 'Maharashtra',
            'gst_number' => '27SUPREME1234A1Z8',
            'shipping_address' => 'MIDC Area, Thane, Maharashtra',
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals(2, $client->plants()->count());

        /** @var ClientPlant|null $secPlant */
        $secPlant = ClientPlant::where('plant_name', 'Mumbai Distribution Hub')->first();
        $this->assertNotNull($secPlant);
        $this->assertEquals('27SUPREME1234A1Z8', $secPlant->gst_number);

        // 3. Update Client Profile
        $response = $this->actingAs($user)->put(route('clients.update', $client->id), [
            'company_name' => 'Supreme Global Logistics Pvt Ltd',
            'client_email' => 'info@supremeglobal.com',
            'gst_number' => '24SUPREME1234A1Z1',
            'corporate_address' => 'HQ Tower, Ring Road, Surat, Gujarat',
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        /** @var Client $freshClient */
        $freshClient = $client->fresh();
        $this->assertEquals('Supreme Global Logistics Pvt Ltd', $freshClient->company_name);

        // 4. Update Plant Details
        $response = $this->actingAs($user)->put(route('clients.plants.update', $secPlant->id), [
            'plant_name' => 'Mumbai Mega Hub',
            'state' => 'Maharashtra',
            'gst_number' => '27SUPREME9999A1Z9',
            'shipping_address' => 'Navi Mumbai Logistics Park, Maharashtra',
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        /** @var ClientPlant $freshSecPlant */
        $freshSecPlant = $secPlant->fresh();
        $this->assertEquals('Mumbai Mega Hub', $freshSecPlant->plant_name);
        $this->assertEquals('27SUPREME9999A1Z9', $freshSecPlant->gst_number);

        // 5. Delete Plant
        $response = $this->actingAs($user)->delete(route('clients.plants.delete', $secPlant->id));
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals(1, $client->plants()->count());

        // 6. Delete Client
        $response = $this->actingAs($user)->delete(route('clients.delete', $client->id));
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNull(Client::find($client->id));
    }

    /**
     * Test Out-of-State Plant GSTIN Validation (State Code Matching).
     */
    public function test_out_of_state_plant_gstin_validation(): void
    {
        $user = User::create([
            'name' => 'Patel Admin',
            'email' => 'gst_test_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'status' => 'approved',
            'is_active' => true,
            'role' => 'super_admin',
        ]);

        $client = Client::create([
            'company_name' => 'Gujarat Steel HQ',
            'client_email' => 'gujsteel@example.com',
            'gst_number' => '24AAAAA0000A1Z5',
            'corporate_address' => 'Rajkot, Gujarat',
        ]);

        // Wrong state code for Madhya Pradesh (24 instead of 23)
        $response = $this->actingAs($user)->postJson(route('clients.plants.store'), [
            'client_id' => $client->id,
            'plant_name' => 'Indore Factory',
            'state' => 'Madhya Pradesh',
            'gst_number' => '24AAACB1234A1Z9',
            'shipping_address' => 'Indore, MP',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['gst_number']);

        // Correct MP state code 23
        $responseValid = $this->actingAs($user)->postJson(route('clients.plants.store'), [
            'client_id' => $client->id,
            'plant_name' => 'Indore Factory',
            'state' => 'Madhya Pradesh',
            'gst_number' => '23AAACB1234A1Z9',
            'shipping_address' => 'Indore, MP',
        ]);
        $responseValid->assertStatus(200);
    }

    /**
     * Test Sales Orders CRUD, status updates, and auto-dispatch flow.
     */
    public function test_sales_orders_workflow(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'company_name' => 'Tata Motors Supply Chain',
            'contact_person' => 'Rajesh Sharma',
            'email' => 'tata@example.com',
            'phone' => '9876543210',
            'billing_address' => 'Sanand GIDC, Gujarat',
        ]);
        $plant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Sanand Heavy Fabrication Plant',
            'state' => 'Gujarat',
            'gst_number' => '24TATA9999A1Z1',
            'shipping_address' => 'Sanand Plant No 4, Gujarat',
        ]);
        $product = Product::create([
            'product_name' => 'Heavy Duty Storage Rack 4-Tier',
            'sku' => 'HD-RACK-4T',
            'selling_price' => 7500.00,
            'current_stock' => 50,
        ]);

        // 1. Create Sales Order
        $response = $this->actingAs($user)->post(route('orders.store'), [
            'client_id' => $client->id,
            'plant_id' => $plant->id,
            'po_number' => 'PO-TATA-9988',
            'order_date' => date('Y-m-d'),
            'delivery_date' => date('Y-m-d', strtotime('+7 days')),
            'product_ids' => [$product->id],
            'quantities' => [10],
            'unit_prices' => [7500.00],
            'notes' => 'Test order creation',
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);

        /** @var SalesOrder|null $order */
        $order = SalesOrder::where('po_number', 'PO-TATA-9988')->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(75000.00, $order->total_amount);

        // 2. Update Order Status
        $response = $this->actingAs($user)->patch(route('orders.updateStatus', $order->id), [
            'status' => 'in_production',
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        /** @var SalesOrder $freshOrder */
        $freshOrder = $order->fresh();
        $this->assertEquals('ready_for_dispatch', $freshOrder->status);

        // 3. Invoice prefilled from Sales Order
        $response = $this->actingAs($user)->post(route('invoice.generate'), [
            'invoice_number' => 'PWW-TEST-ORD-01',
            'plant_id' => $plant->id,
            'vehicle_number' => 'GJ-03-BW-1234',
            'sales_order_id' => $order->id,
            'product_ids' => [$product->id],
            'quantities' => [10],
            'unit_prices' => [7500.00],
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        /** @var SalesOrder $dispatchedOrder */
        $dispatchedOrder = $order->fresh();
        $this->assertEquals('dispatched', $dispatchedOrder->status);

        // 4. Stock Shortage Guard on Excessive Order
        $largeOrder = SalesOrder::create([
            'order_number' => SalesOrder::generateNextOrderNumber(),
            'client_id' => $client->id,
            'plant_id' => $plant->id,
            'order_date' => date('Y-m-d'),
            'status' => 'pending',
            'total_amount' => 7500000.00,
        ]);
        SalesOrderItem::create([
            'sales_order_id' => $largeOrder->id,
            'product_id' => $product->id,
            'quantity' => 9999,
            'unit_price' => 7500.00,
            'total_price' => 7500000.00,
        ]);

        $failResp = $this->actingAs($user)->patch(route('orders.updateStatus', $largeOrder->id), [
            'status' => 'ready_for_dispatch',
        ]);
        $failResp->assertStatus(422)->assertJson(['success' => false]);

        // Cleanup
        $this->actingAs($user)->delete(route('orders.delete', $largeOrder->id));
        $response = $this->actingAs($user)->delete(route('orders.delete', $order->id));
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNull(SalesOrder::find($order->id));
    }

    /**
     * Test Single Stock Deduction on Dispatched Sales Order Invoice.
     */
    public function test_single_stock_deduction_on_dispatched_sales_order_invoice(): void
    {
        $admin = User::create([
            'name' => 'Sales Admin',
            'email' => 'salesadmin@pww.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
            'status' => 'active',
        ]);

        $client = Client::create([
            'company_name' => 'Tata Steel',
            'client_email' => 'tata@steel.com',
            'gst_number' => '24AAAAT1234A1Z5',
            'corporate_address' => 'Surat, Gujarat',
        ]);

        $plant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Hazira Plant',
            'state' => 'Gujarat',
            'gst_number' => '24AAAAT1234A1Z5',
        ]);

        $product = Product::create([
            'product_name' => 'Welded Mesh Sheet',
            'hsn_code' => '7314',
            'selling_price' => 1000.00,
            'current_stock' => 100,
            'uom' => 'piece',
        ]);

        $order = SalesOrder::create([
            'order_number' => 'ORD-TEST-99',
            'client_id' => $client->id,
            'plant_id' => $plant->id,
            'order_date' => now()->toDateString(),
            'status' => 'ready_for_dispatch',
            'total_amount' => 20000.00,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 20,
            'unit_price' => 1000.00,
            'total_price' => 20000.00,
        ]);

        // 1. Dispatch order from Sales Orders page (Stock: 100 -> 80)
        $statusRes = $this->actingAs($admin)->patchJson(route('orders.updateStatus', $order->id), [
            'status' => 'dispatched',
        ]);
        $statusRes->assertStatus(200);
        $this->assertEquals(80, $product->fresh()->current_stock);

        // 2. Generate Invoice for dispatched order (Stock must remain 80, no double deduction)
        $invRes = $this->actingAs($admin)->postJson(route('invoice.generate'), [
            'invoice_number' => 'INV-TEST-99',
            'invoice_mode' => 'finished_goods',
            'tax_type' => 'with_gst',
            'plant_id' => $plant->id,
            'sales_order_id' => $order->id,
            'vehicle_number' => 'GJ03AB1234',
            'invoice_date' => now()->toDateString(),
            'product_ids' => ["product_{$product->id}"],
            'quantities' => [20],
            'unit_prices' => [1000],
        ]);
        $invRes->assertStatus(200);
        $this->assertEquals(80, $product->fresh()->current_stock);
    }

    /**
     * Test Client Opening Balance Single Count & Model Scopes.
     */
    public function test_client_opening_balance_not_duplicated(): void
    {
        $admin = User::create([
            'name' => 'Accountant',
            'email' => 'acc@pww.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
            'status' => 'active',
        ]);

        $res = $this->actingAs($admin)->postJson(route('clients.store'), [
            'company_name' => 'Reliance Industries',
            'client_email' => 'ril@reliance.com',
            'gst_number' => '24AAACR1234A1Z5',
            'corporate_address' => 'Jamnagar, Gujarat',
            'opening_balance' => 50000,
            'create_primary_plant' => true,
            'state' => 'Gujarat',
        ]);
        $res->assertStatus(200);
        $clientId = $res->json('data.id');

        $ledger = $this->financialService->getClientLedger($clientId);
        $this->assertEquals(50000.00, (float) $ledger['opening_balance']);

        $inv = Invoice::create([
            'invoice_number' => 'INV-TEST-PARTIAL',
            'total_amount' => 1000.00,
            'paid_amount' => 500.00,
            'payment_status' => 'partially_paid',
        ]);
        $this->assertEquals(1, Invoice::partial()->where('invoice_number', 'INV-TEST-PARTIAL')->count());

        $pendingUser = User::create([
            'name' => 'Pending Staff',
            'email' => 'pending@pww.com',
            'password' => bcrypt('password'),
            'role' => 'pending',
            'status' => 'pending',
        ]);
        $this->assertTrue($pendingUser->isPending());
    }

    /* =========================================================================
     * SECTION 3: INVOICES, BILLING & GST TAX CALCULATIONS
     * ========================================================================= */

    /**
     * Test Regional GST Calculation (CGST/SGST vs IGST).
     */
    public function test_regional_gst_billing_logic(): void
    {
        $client = Client::create([
            'company_name' => 'Balaji Wafers',
            'gst_number' => '24AAACB1234A1Z9',
            'corporate_address' => 'Rajkot, Gujarat',
        ]);

        $gujaratPlant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Valsad Plant',
            'shipping_address' => 'Valsad, Gujarat',
            'state' => 'Gujarat',
        ]);

        $indorePlant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Indore Plant',
            'shipping_address' => 'Indore, MP',
            'state' => 'Madhya Pradesh',
        ]);

        $rack = Product::create([
            'product_name' => 'Wire Rack',
            'sku' => 'WR-01',
            'current_stock' => 100,
            'selling_price' => 1000.00,
        ]);

        // 1. Intrastate Gujarat (CGST 9% + SGST 9%)
        $gstGuj = $this->billingService->calculateGstBreakdown($gujaratPlant->id, [
            ['product_id' => $rack->id, 'quantity' => 10, 'unit_price' => 1000.00],
        ]);
        $this->assertEquals(10000.00, $gstGuj['taxable_value']);
        $this->assertEquals(900.00, $gstGuj['cgst']);
        $this->assertEquals(900.00, $gstGuj['sgst']);
        $this->assertEquals(0.00, $gstGuj['igst']);
        $this->assertEquals(11800.00, $gstGuj['total_amount']);

        // 2. Interstate MP (IGST 18%)
        $gstInd = $this->billingService->calculateGstBreakdown($indorePlant->id, [
            ['product_id' => $rack->id, 'quantity' => 20, 'unit_price' => 1000.00],
        ]);
        $this->assertEquals(20000.00, $gstInd['taxable_value']);
        $this->assertEquals(0.00, $gstInd['cgst']);
        $this->assertEquals(0.00, $gstInd['sgst']);
        $this->assertEquals(3600.00, $gstInd['igst']);
        $this->assertEquals(23600.00, $gstInd['total_amount']);
    }

    /**
     * Test AJAX Custom Direct Invoice Generation.
     */
    public function test_ajax_custom_invoice_generation(): void
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $client = Client::create(['company_name' => 'Balaji Wafers']);
        $plant = ClientPlant::create(['client_id' => $client->id, 'plant_name' => 'Rajkot', 'state' => 'Gujarat']);
        $good = Product::create(['product_name' => 'Rack A', 'sku' => 'RA-01', 'current_stock' => 10, 'selling_price' => 500]);

        $response = $this->actingAs($user)->postJson(route('invoice.generate'), [
            'invoice_number' => 'PWW-CUSTOM-999',
            'plant_id' => $plant->id,
            'vehicle_number' => 'GJ-03-BW-1234',
            'due_date' => Carbon::now()->addDays(30)->toDateString(),
            'product_ids' => [$good->id],
            'quantities' => [10],
            'unit_prices' => [500],
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $invoice = Invoice::where('invoice_number', 'PWW-CUSTOM-999')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(5000.00, $invoice->total_taxable_value);
        $this->assertEquals(450.00, $invoice->cgst);
        $this->assertEquals(450.00, $invoice->sgst);
        $this->assertEquals(5900.00, $invoice->total_amount);
    }

    /**
     * Test Delivery Vehicle Number Validation (Valid format vs Invalid).
     */
    public function test_vehicle_number_validation(): void
    {
        $user = User::create([
            'name' => 'Patel Admin',
            'email' => 'v_test_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'status' => 'approved',
            'is_active' => true,
            'role' => 'super_admin',
        ]);

        $client = Client::create([
            'company_name' => 'Logistics Co',
            'client_email' => 'logistics@example.com',
            'gst_number' => '24AAAAA0000A1Z5',
            'corporate_address' => 'Surat, Gujarat',
        ]);

        $plant = ClientPlant::create(['client_id' => $client->id, 'plant_name' => 'Surat Plant', 'state' => 'Gujarat']);
        $good = Product::create(['product_name' => 'Transport Item', 'sku' => 'TR-'.uniqid(), 'current_stock' => 50, 'selling_price' => 100]);

        // 1. Valid vehicle number
        $respValid = $this->actingAs($user)->postJson(route('invoice.generate'), [
            'invoice_number' => 'PWW-VEH-001',
            'plant_id' => $plant->id,
            'vehicle_number' => 'GJ-03-BW-1234',
            'finished_good_ids' => [$good->id],
            'quantities' => [1],
            'unit_prices' => [100],
        ]);
        $respValid->assertStatus(200);

        $invObj = Invoice::where('invoice_number', 'PWW-VEH-001')->first();
        $this->assertEquals('GJ-03-BW-1234', $invObj->vehicle_number);

        // 2. Invalid vehicle number
        $respInvalid = $this->actingAs($user)->postJson(route('invoice.generate'), [
            'invoice_number' => 'PWW-VEH-002',
            'plant_id' => $plant->id,
            'vehicle_number' => 'INVALID_VEHICLE_NUM',
            'finished_good_ids' => [$good->id],
            'quantities' => [1],
            'unit_prices' => [100],
        ]);
        $respInvalid->assertStatus(422)->assertJsonValidationErrors(['vehicle_number']);
    }

    /**
     * Test AJAX Invoice Deletion.
     */
    public function test_ajax_invoice_deletion(): void
    {
        $user = User::create([
            'name' => 'Patel Admin',
            'email' => 'del_inv_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'status' => 'approved',
            'is_active' => true,
            'role' => 'super_admin',
        ]);

        $client = Client::create([
            'company_name' => 'Deletion Test Client',
            'client_email' => 'delclient@example.com',
            'gst_number' => '24AAAAA0000A1Z5',
            'corporate_address' => 'Rajkot, Gujarat',
        ]);

        $plant = ClientPlant::create(['client_id' => $client->id, 'plant_name' => 'Deletion Plant', 'state' => 'Gujarat']);
        $good = Product::create(['product_name' => 'Delete Item', 'sku' => 'DEL-01', 'current_stock' => 10, 'selling_price' => 500]);

        $this->actingAs($user)->postJson(route('invoice.generate'), [
            'invoice_number' => 'PWW-DEL-999',
            'plant_id' => $plant->id,
            'vehicle_number' => 'GJ-03-BW-1234',
            'finished_good_ids' => [$good->id],
            'quantities' => [1],
            'unit_prices' => [500],
        ]);

        $inv = Invoice::where('invoice_number', 'PWW-DEL-999')->first();
        $this->assertNotNull($inv);

        $response = $this->actingAs($user)->deleteJson(route('invoice.delete', $inv->id));
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNull(Invoice::find($inv->id));
    }

    /**
     * Test Mark Invoice as Paid.
     */
    public function test_ajax_pay_invoice(): void
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'PWW-PAYTEST-999',
            'total_taxable_value' => 1000.00,
            'cgst' => 90.00,
            'sgst' => 90.00,
            'igst' => 0.00,
            'total_amount' => 1180.00,
            'payment_status' => 'unpaid',
            'due_date' => Carbon::now()->addDays(30)->toDateString(),
        ]);

        $response = $this->actingAs($user)->postJson(route('invoice.pay', $invoice->id));
        $response->assertStatus(200)->assertJson(['success' => true]);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->payment_status);
        $this->assertEquals(1180.00, $invoice->paid_amount);
    }

    /**
     * Test Invoice Print Layout Rendering.
     */
    public function test_invoice_print_rendering(): void
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $client = Client::create(['company_name' => 'Balaji Wafers']);
        $plant = ClientPlant::create(['client_id' => $client->id, 'plant_name' => 'Rajkot plant', 'state' => 'Gujarat']);
        $good = Product::create(['product_name' => 'Special Rack X', 'sku' => 'SRX-99', 'current_stock' => 10, 'selling_price' => 500]);

        $invoice = Invoice::create([
            'plant_id' => $plant->id,
            'invoice_number' => 'PWW-PRINTTEST-999',
            'total_taxable_value' => 2500.00,
            'cgst' => 225.00,
            'sgst' => 225.00,
            'igst' => 0.00,
            'total_amount' => 2950.00,
            'payment_status' => 'unpaid',
            'due_date' => Carbon::now()->addDays(30)->toDateString(),
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $good->id,
            'quantity' => 5,
            'unit_price' => 500.00,
            'total_price' => 2500.00,
        ]);

        $response = $this->actingAs($user)->get(route('invoice.print', $invoice->id));
        $response->assertStatus(200);
        $response->assertSee('PWW-PRINTTEST-999');
        $response->assertSee('Balaji Wafers');
        $response->assertSee('Special Rack X');
        $response->assertDontSee('(Rajkot plant)');

        // Adding 2nd plant triggers plant display
        ClientPlant::create(['client_id' => $client->id, 'plant_name' => 'Baroda plant', 'state' => 'Gujarat']);
        $responseMulti = $this->actingAs($user)->get(route('invoice.print', $invoice->id));
        $responseMulti->assertSee('(Rajkot plant)');
    }

    /**
     * Test E-Way Bill JSON File Export Payload.
     */
    public function test_export_eway_bill_json_payload(): void
    {
        $admin = User::create([
            'name' => 'Eway Admin',
            'email' => 'eway_admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $client = Client::create([
            'company_name' => 'Gujarat Steel Corp',
            'client_email' => 'steel@gujarat.com',
            'gst_number' => '24ABCDE1234F1Z5',
        ]);

        $plant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Ahmedabad Works',
            'opening_balance' => 0,
            'gst_number' => '24ABCDE1234F1Z5',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'PWW/25-26/999',
            'plant_id' => $plant->id,
            'vehicle_number' => 'GJ03AB1234',
            'invoice_date' => now()->toDateString(),
            'total_taxable_value' => 10000.00,
            'cgst' => 900.00,
            'sgst' => 900.00,
            'igst' => 0.00,
            'total_amount' => 11800.00,
        ]);

        $response = $this->actingAs($admin)->get(route('invoice.exportEwayJson', $invoice->id));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');

        $json = $response->json();
        $this->assertEquals('1.0.1121', $json['version']);
        $this->assertCount(1, $json['billLists']);
        $this->assertEquals('PWW/25-26/999', $json['billLists'][0]['docNo']);
        $this->assertEquals('24ABCDE1234F1Z5', $json['billLists'][0]['toGstin']);
        $this->assertEquals(11800.00, $json['billLists'][0]['totInvValue']);
    }

    /**
     * Test Non-GST (Without GST) Invoice Creation & Print View.
     */
    public function test_without_gst_invoice_creation_and_print_rendering(): void
    {
        $admin = User::create([
            'name' => 'Bill Admin',
            'email' => 'bill_admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $client = Client::create(['company_name' => 'Local Cash Buyer', 'client_email' => 'cash@local.com']);
        $plant = ClientPlant::create(['client_id' => $client->id, 'plant_name' => 'Rajkot Shop', 'opening_balance' => 0, 'state' => 'Gujarat']);
        $product = Product::create(['product_name' => 'Mild Steel Bracket', 'selling_price' => 250.00, 'gst_rate' => 18.00, 'current_stock' => 100]);

        $response = $this->actingAs($admin)->postJson(route('invoice.generate'), [
            'invoice_number' => 'PWW/26-27/NOGST-01',
            'plant_id' => $plant->id,
            'invoice_mode' => 'finished_goods',
            'tax_type' => 'without_gst',
            'invoice_date' => now()->toDateString(),
            'vehicle_number' => '',
            'product_ids' => ['product_'.$product->id],
            'quantities' => [10],
            'unit_prices' => [250.00],
            'billing_uoms' => ['Pcs'],
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $invoice = Invoice::where('invoice_number', 'PWW/26-27/NOGST-01')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(2500.00, (float) $invoice->total_taxable_value);
        $this->assertEquals(0.00, (float) $invoice->cgst);
        $this->assertEquals(0.00, (float) $invoice->sgst);
        $this->assertEquals(0.00, (float) $invoice->igst);
        $this->assertEquals(2500.00, (float) $invoice->total_amount);

        $printResponse = $this->actingAs($admin)->get(route('invoice.print', $invoice->id));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('INVOICE');
        $printResponse->assertDontSee('TAX INVOICE');
    }

    /* =========================================================================
     * SECTION 4: PURCHASES, EXPENSES & FINANCIAL PROFIT ENGINE
     * ========================================================================= */

    /**
     * Test Financial Net Profit Engine calculation framework.
     */
    public function test_financial_profit_engine(): void
    {
        Invoice::create([
            'delivery_challan_id' => null,
            'invoice_number' => 'PWW-001',
            'total_taxable_value' => 10000.00,
            'cgst' => 900.00,
            'sgst' => 900.00,
            'igst' => 0.00,
            'total_amount' => 11800.00,
            'payment_status' => 'paid',
            'paid_amount' => 11800.00,
            'due_date' => Carbon::now()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        Purchase::create([
            'bill_number' => 'BILL-101',
            'vendor_name' => 'Steel Supplier',
            'purchase_type' => 'raw_material',
            'item_name' => 'Wire Coil',
            'quantity' => 100,
            'unit' => 'kg',
            'gst_rate' => 18,
            'gst_amount' => 360,
            'total_amount' => 2000.00,
            'purchase_date' => Carbon::now()->toDateString(),
        ]);

        Expense::create([
            'expense_category' => 'office_rent',
            'amount' => 1500.00,
            'expense_date' => Carbon::now()->toDateString(),
        ]);

        Expense::create([
            'expense_category' => 'machinery_depreciation',
            'amount' => 500.00,
            'expense_date' => Carbon::now()->toDateString(),
        ]);

        // Net Profit = 11,800 - 2,000 - 2,000 = ₹7,800
        $summary = $this->financialService->getFinancialSummary(
            Carbon::now()->subDay()->toDateString(),
            Carbon::now()->addDay()->toDateString()
        );

        $this->assertEquals(11800.00, $summary['revenue']);
        $this->assertEquals(2000.00, $summary['total_purchases']);
        $this->assertEquals(2000.00, $summary['total_expenses']);
        $this->assertEquals(7800.00, $summary['net_profit']);
    }

    /**
     * Test GST payment status auto-detection from Expense Ledger.
     */
    public function test_gst_payment_status_from_expense_ledger(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $client = Client::create(['company_name' => 'GST Test Client', 'gstin' => '24AAAAA0000A1Z5', 'contact_person' => 'John']);
        $plant = ClientPlant::create(['client_id' => $client->id, 'plant_name' => 'HQ Plant', 'state' => 'Gujarat', 'city' => 'Surat']);

        Invoice::create([
            'plant_id' => $plant->id,
            'invoice_number' => 'PWW-GST-001',
            'invoice_date' => Carbon::now()->toDateString(),
            'total_taxable_value' => 10000.00,
            'cgst' => 900.00,
            'sgst' => 900.00,
            'igst' => 0.00,
            'total_amount' => 11800.00,
            'payment_status' => 'paid',
            'paid_amount' => 11800.00,
            'due_date' => Carbon::now()->toDateString(),
        ]);

        // Unpaid initially
        $response1 = $this->actingAs($user)->get('/reports?report_type=gst&filter_period=month');
        $response1->assertStatus(200)->assertSee('UNPAID');

        // Log GST Expense
        Expense::create([
            'expense_category' => 'gst_payment',
            'amount' => 1800.00,
            'expense_date' => Carbon::now()->toDateString(),
            'description' => 'GSTR-3B Tax Paid via Bank Challan',
        ]);

        // Paid after logging
        $response2 = $this->actingAs($user)->get('/reports?report_type=gst&filter_period=month');
        $response2->assertStatus(200)->assertSee('PAID');
    }

    /**
     * Test Purchase Ledger logging workflow.
     */
    public function test_purchase_logging_workflow(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $mat = RawMaterial::create([
            'material_name' => 'Test Steel Plate 5mm',
            'unit' => 'kg',
            'current_stock' => 100,
            'safety_threshold' => 10,
            'average_purchase_price' => 50,
        ]);

        // 1. Raw Material Purchase
        $response1 = $this->actingAs($user)->postJson('/purchases', [
            'vendor_name' => 'Jindal Steel',
            'purchase_type' => 'raw_material',
            'raw_material_id' => $mat->id,
            'quantity' => 500,
            'total_amount' => 25000,
            'gst_rate' => 18,
            'purchase_date' => Carbon::now()->toDateString(),
        ]);
        $response1->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals(600, $mat->fresh()->current_stock);

        // 2. Non-raw Material Purchase
        $response2 = $this->actingAs($user)->postJson('/purchases', [
            'vendor_name' => 'Atlas Copco',
            'purchase_type' => 'machinery',
            'item_name' => 'Air Compressor 10HP',
            'quantity' => 1,
            'unit' => 'unit',
            'total_amount' => 150000,
            'gst_rate' => 18,
            'purchase_date' => Carbon::now()->toDateString(),
        ]);
        $response2->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test Purchase Deletion Deducts Raw Material Stock.
     */
    public function test_purchase_deletion_deducts_raw_material_stock(): void
    {
        $admin = User::create([
            'name' => 'Purchase Manager',
            'email' => 'pur@pww.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
            'status' => 'active',
        ]);

        $rawMaterial = RawMaterial::create([
            'material_name' => 'Brass Wire',
            'unit' => 'kg',
            'current_stock' => 50.00,
            'safety_threshold' => 10.00,
            'average_purchase_price' => 400.00,
        ]);

        $res = $this->actingAs($admin)->postJson(route('purchases.store'), [
            'vendor_name' => 'Brass Suppliers Ltd',
            'purchase_type' => 'raw_material',
            'raw_material_id' => $rawMaterial->id,
            'quantity' => 100,
            'total_amount' => 40000.00,
            'gst_rate' => 18,
            'purchase_date' => now()->toDateString(),
        ]);
        $res->assertStatus(200);
        $purchaseId = $res->json('data.id');
        $this->assertEquals(150.00, (float) $rawMaterial->fresh()->current_stock);

        $delRes = $this->actingAs($admin)->deleteJson(route('purchases.delete', $purchaseId));
        $delRes->assertStatus(200);
        $this->assertEquals(50.00, (float) $rawMaterial->fresh()->current_stock);
    }

    /* =========================================================================
     * SECTION 5: EMPLOYEES, ATTENDANCE, ADVANCES & SALARY PAYROLL
     * ========================================================================= */

    /**
     * Test Legacy Piece Rate Wage Compilation.
     */
    public function test_payroll_piece_rate_matrix(): void
    {
        $staff = StaffProfile::create([
            'user_id' => null,
            'full_name' => 'Amit Sharma',
            'wage_type' => 'per-day',
            'piece_rate_per_unit' => 50.00,
        ]);

        $good = Product::create(['product_name' => 'Rack', 'sku' => 'RK-01', 'current_stock' => 10, 'selling_price' => 500]);
        $user = User::create(['name' => 'Manager', 'email' => 'm@pww.com', 'password' => bcrypt('password'), 'role' => 'manager']);

        $prodLog = ProductionLog::create([
            'product_id' => $good->id,
            'quantity_manufactured' => 100,
            'quantity_rejected' => 0,
            'recorded_by' => $user->id,
            'production_date' => Carbon::now()->toDateString(),
        ]);

        $log1 = LaborLog::create([
            'staff_profile_id' => $staff->id,
            'production_log_id' => $prodLog->id,
            'units_completed' => 40,
            'calculated_payout' => 2000.00,
            'status' => 'pending',
        ]);

        $log2 = LaborLog::create([
            'staff_profile_id' => $staff->id,
            'production_log_id' => $prodLog->id,
            'units_completed' => 20,
            'calculated_payout' => 1000.00,
            'status' => 'pending',
        ]);

        $compiled = $this->payrollService->compilePendingPieceRateWages();
        $this->assertCount(1, $compiled);
        $this->assertEquals(60, $compiled[0]['total_units_completed']);
        $this->assertEquals(3000.00, $compiled[0]['total_pending_payout']);

        $updatedRows = $this->payrollService->markWagesAsPaid([$log1->id, $log2->id]);
        $this->assertEquals(2, $updatedRows);

        $compiledAfter = $this->payrollService->compilePendingPieceRateWages();
        $this->assertCount(0, $compiledAfter);
        $this->assertEquals('paid', LaborLog::find($log1->id)->status);
        $this->assertEquals('paid', LaborLog::find($log2->id)->status);
    }

    /**
     * Test Employee Profile CRUD, Status Toggle & Statement Endpoint.
     */
    public function test_employee_crud_operations(): void
    {
        $user = User::factory()->create();

        // 1. Store Employee
        $response = $this->actingAs($user)->post(route('employees.store'), [
            'full_name' => 'Ramesh Kumar',
            'mobile_number' => '9876543210',
            'wage_type' => 'per-day',
            'piece_rate_per_unit' => 600.00,
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        $staffId = $response->json('data.id');

        // 2. Update Employee
        $response = $this->actingAs($user)->put(route('employees.update', $staffId), [
            'full_name' => 'Ramesh Kumar Updated',
            'mobile_number' => '9876543211',
            'wage_type' => 'fixed',
            'monthly_salary' => 25000.00,
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals('Ramesh Kumar Updated', StaffProfile::find($staffId)->full_name);
        $this->assertEquals('9876543211', StaffProfile::find($staffId)->mobile_number);
        $this->assertEquals('fixed', StaffProfile::find($staffId)->wage_type);

        // 3. Toggle Active/Inactive Status
        $this->assertTrue(StaffProfile::find($staffId)->is_active);
        $response = $this->actingAs($user)->postJson(route('employees.toggle-status', $staffId));
        $response->assertStatus(200)->assertJson(['success' => true, 'is_active' => false]);
        $this->assertFalse(StaffProfile::find($staffId)->is_active);

        $response = $this->actingAs($user)->postJson(route('employees.toggle-status', $staffId));
        $response->assertStatus(200)->assertJson(['success' => true, 'is_active' => true]);
        $this->assertTrue(StaffProfile::find($staffId)->is_active);

        // 4. Test Statement JSON Endpoint
        SalaryAdvance::create([
            'staff_profile_id' => $staffId,
            'amount' => 5000.00,
            'advance_date' => '2026-08-15',
            'status' => 'pending',
        ]);

        $julyResp = $this->actingAs($user)->getJson(route('employees.statement', ['id' => $staffId, 'month' => '2026-07']));
        $julyResp->assertStatus(200);
        $this->assertEquals(0, $julyResp->json('pending_advances_total'));

        $augResp = $this->actingAs($user)->getJson(route('employees.statement', ['id' => $staffId, 'month' => '2026-08']));
        $augResp->assertStatus(200);
        $this->assertEquals(5000.00, $augResp->json('pending_advances_total'));

        // 5. Delete Employee
        $response = $this->actingAs($user)->delete(route('employees.delete', $staffId));
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNull(StaffProfile::find($staffId));
    }

    /**
     * Test Complete Employee Workflow: Attendance, Advances, Salary Calculations & Reversals.
     */
    public function test_complete_employee_workflow_attendance_advance_salary_reconciliation(): void
    {
        $admin = User::create([
            'name' => 'HR Manager',
            'email' => 'hr@pww.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
            'status' => 'active',
        ]);

        // 1. Create Per-Day Staff (₹800/day)
        $perDayStaff = StaffProfile::create([
            'full_name' => 'Mahesh Sharma',
            'mobile_number' => '9898989898',
            'wage_type' => 'per-day',
            'piece_rate_per_unit' => 800.00,
            'is_active' => true,
        ]);

        // 2. Mark Daily Attendance
        $this->actingAs($admin)->postJson(route('employees.attendance.store'), [
            'date' => '2026-08-01',
            'attendance' => [$perDayStaff->id => 'present'],
        ])->assertStatus(200);

        $this->actingAs($admin)->postJson(route('employees.attendance.store'), [
            'date' => '2026-08-02',
            'attendance' => [$perDayStaff->id => 'half_day'],
        ])->assertStatus(200);

        $this->actingAs($admin)->postJson(route('employees.attendance.store'), [
            'date' => '2026-08-03',
            'attendance' => [$perDayStaff->id => 'absent'],
        ])->assertStatus(200);

        // 3. Monthly Attendance Summary
        $summaryRes = $this->actingAs($admin)->getJson(route('employees.attendance.summary', ['month' => '2026-08']));
        $summaryRes->assertStatus(200);
        $this->assertGreaterThanOrEqual(1.5, (float) $summaryRes->json("summary.{$perDayStaff->id}"));

        // 4. Issue Salary Advance of ₹3,000
        $advRes = $this->actingAs($admin)->postJson(route('employees.advance.store'), [
            'staff_profile_id' => $perDayStaff->id,
            'amount' => 3000.00,
            'advance_date' => '2026-08-05',
            'payment_method' => 'Cash',
            'notes' => 'Medical Advance',
        ]);
        $advRes->assertStatus(200);
        $advId = $advRes->json('data.id');

        $advExpense = Expense::where('expense_category', 'Employee Salary Advance')->where('amount', 3000.00)->first();
        $this->assertNotNull($advExpense);

        // 5. Pay Salary with Partial Advance Deduction (Deduct ₹2,000)
        $payRes = $this->actingAs($admin)->postJson(route('employees.salary.payment'), [
            'staff_profile_id' => $perDayStaff->id,
            'month_year' => '2026-08',
            'days_present' => 20,
            'total_salary' => 14000.00,
            'advance_deduction' => 2000.00,
            'payment_status' => 'paid',
            'payment_date' => '2026-08-31',
            'payment_method' => 'Bank Transfer',
            'notes' => 'August 2026 Salary Settled',
        ]);
        $payRes->assertStatus(200);
        $paymentId = $payRes->json('data.id');

        $salaryExpense = Expense::where('expense_category', 'Employee Salary / Payroll')->where('amount', 14000.00)->first();
        $this->assertNotNull($salaryExpense);

        /** @var SalaryAdvance|null $originalAdv */
        $originalAdv = SalaryAdvance::find($advId);
        $this->assertNotNull($originalAdv);
        $this->assertEquals('deducted', $originalAdv->status);
        $this->assertEquals(2000.00, (float) $originalAdv->amount);

        $carryOverAdv = SalaryAdvance::where('staff_profile_id', $perDayStaff->id)
            ->where('status', 'pending')
            ->where('amount', 1000.00)
            ->first();
        $this->assertNotNull($carryOverAdv);

        // 6. Delete Salary Payment (Rollback test)
        $delPayRes = $this->actingAs($admin)->deleteJson(route('employees.salary.delete', $paymentId));
        $delPayRes->assertStatus(200);

        $this->assertNull(Expense::find($salaryExpense->id));
        /** @var SalaryAdvance $freshOriginalAdv */
        $freshOriginalAdv = $originalAdv->fresh();
        $this->assertEquals('pending', $freshOriginalAdv->status);
        $this->assertNull(SalaryAdvance::find($carryOverAdv->id));

        // 7. Delete Salary Advance
        $delAdvRes = $this->actingAs($admin)->deleteJson(route('employees.advance.delete', $advId));
        $delAdvRes->assertStatus(200);
        $this->assertNull(SalaryAdvance::find($advId));
        $this->assertNull(Expense::find($advExpense->id));
    }

    /* =========================================================================
     * SECTION 6: AUTHENTICATION, PROFILE, SECURITY & BACKUPS
     * ========================================================================= */

    /**
     * Test Guest Redirection to Login.
     */
    public function test_guest_redirect_to_login(): void
    {
        $response = $this->get('/overview');
        $response->assertRedirect('/login');
    }

    /**
     * Test AJAX Login Authentication & Rate Limiting.
     */
    public function test_ajax_login_flow(): void
    {
        User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $response = $this->postJson('/login', [
            'email' => 'praful@pww.com',
            'password' => 'admin123',
        ]);
        $response->assertStatus(200)->assertJson(['success' => true, 'redirect' => route('overview')]);

        // Trigger rate limit
        $targetEmail = 'lockout@pww.com';
        $headers = ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'];
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/login', ['email' => $targetEmail, 'password' => 'wrongpassword'], $headers);
        }
        $response = $this->postJson('/login', ['email' => $targetEmail, 'password' => 'wrongpassword'], $headers);
        $this->assertTrue(in_array($response->status(), [429, 302]));
    }

    /**
     * Test Profile Settings View.
     */
    public function test_profile_settings_view(): void
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(route('profile'));
        $response->assertStatus(200);
        $response->assertSee('Profile Information');
        $response->assertSee('Update Password');
        $response->assertSee('Back to Panel');
    }

    /**
     * Test AJAX Profile Details Update.
     */
    public function test_ajax_profile_update(): void
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->postJson(route('profile.update'), [
            'name' => 'New Praful Name',
            'email' => 'newemail@pww.com',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $user->refresh();
        $this->assertEquals('New Praful Name', $user->name);
        $this->assertEquals('newemail@pww.com', $user->email);
    }

    /**
     * Test AJAX Password Modification.
     */
    public function test_ajax_password_update(): void
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        // Wrong current password
        $response = $this->actingAs($user)->postJson(route('profile.password'), [
            'current_password' => 'wrongcurrent',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);
        $response->assertStatus(422);

        // Success password change
        $response = $this->actingAs($user)->postJson(route('profile.password'), [
            'current_password' => 'admin123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /**
     * Test Business Settings & Logo Upload.
     */
    public function test_update_business_settings(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@pww.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        Storage::fake('public');
        $file = UploadedFile::fake()->create('business_logo.png', 100, 'image/png');

        $response = $this->actingAs($user)->post(route('profile.business'), [
            'business_name' => 'Custom Weld Inc',
            'business_subtitle' => 'Industrial Fabrication Division',
            'address' => 'GIDC Plot 100, Baroda, Gujarat',
            'business_email' => 'customweld@example.com',
            'gstin' => '24CUSTO1234A1Z9',
            'bank_name' => 'State Bank of India',
            'bank_account_name' => 'Custom Weld Inc',
            'bank_account_no' => '12345678901',
            'bank_ifsc' => 'SBIN0001234',
            'logo' => $file,
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertEquals('Custom Weld Inc', Setting::get('business_name'));
        $this->assertEquals('Industrial Fabrication Division', Setting::get('business_subtitle'));
        $this->assertEquals('GIDC Plot 100, Baroda, Gujarat', Setting::get('address'));
        $this->assertEquals('customweld@example.com', Setting::get('business_email'));
        $this->assertEquals('24CUSTO1234A1Z9', Setting::get('gstin'));
        $this->assertStringContainsString('uploads/logo_', Setting::get('logo_path'));
    }

    /**
     * Test Settings Hub View, Module Toggles & User Provisioning.
     */
    public function test_settings_hub_rendering_module_toggles_and_user_creation(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        // 1. Render Settings Hub View
        $response = $this->actingAs($admin)->get(route('settings.index'));
        $response->assertStatus(200)->assertViewIs('pages.settings');

        // 2. Toggle Modules
        $toggleResponse = $this->actingAs($admin)->post(route('settings.modules'), [
            'module_invoices' => 'true',
            'module_orders' => 'true',
            'module_purchases' => 'true',
        ]);
        $toggleResponse->assertRedirect();
        $this->assertEquals('true', Setting::get('module_invoices'));

        // 3. Create User Account
        $userResponse = $this->actingAs($admin)->post(route('settings.users.store'), [
            'name' => 'Accountant Staff',
            'email' => 'accountant@test.com',
            'password' => 'password123',
            'role' => 'accountant',
        ]);
        $userResponse->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'accountant@test.com', 'role' => 'accountant']);
    }

    /**
     * Test Backup Hub View and Full SQL Dump Download.
     */
    public function test_backup_dashboard_rendering_and_backup_generation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('backup.index'));
        $response->assertStatus(200)->assertViewIs('pages.backup');

        $fullResponse = $this->actingAs($user)->get(route('backup.full'));
        $fullResponse->assertStatus(200)->assertHeader('Content-Type', 'application/octet-stream');

        // Clean up test backup files
        if (File::exists(storage_path('app/backups'))) {
            foreach (File::files(storage_path('app/backups')) as $file) {
                if (str_contains($file->getFilename(), 'pww_full_backup_')) {
                    File::delete($file->getPathname());
                }
            }
        }
    }

    /**
     * Test Settings & Backup RBAC Authorization Guard.
     */
    public function test_settings_and_backup_rbac_authorization(): void
    {
        $staff = User::create([
            'name' => 'Operator Staff',
            'email' => 'operator@pww.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'is_active' => true,
            'status' => 'active',
        ]);

        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pww.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
            'status' => 'active',
        ]);

        // Staff blocked
        $res1 = $this->actingAs($staff)->getJson(route('backup.index'));
        $this->assertTrue(in_array($res1->status(), [302, 403]));

        $res2 = $this->actingAs($staff)->postJson(route('settings.modules'), ['simplified_billing_mode' => 'true']);
        $this->assertTrue(in_array($res2->status(), [302, 403]));

        $res3 = $this->actingAs($staff)->postJson(route('profile.business'), ['business_name' => 'Hacked Name']);
        $this->assertTrue(in_array($res3->status(), [302, 403]));

        // Admin authorized
        $this->actingAs($admin)->get(route('backup.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('settings.index'))->assertStatus(200);
    }

    /* =========================================================================
     * SECTION 7: DASHBOARD, REPORTS, SPA ROUTING & SESSION INACTIVITY
     * ========================================================================= */

    /**
     * Test Overview Dashboard Rendering & Metrics.
     */
    public function test_overview_dashboard_rendering(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('overview'));
        $response->assertStatus(200);
        $response->assertViewHasAll([
            'yearlyRevenue',
            'yearlyTaxable',
            'monthlyRevenue',
            'monthlyTaxable',
            'totalReceivables',
            'currentMonthNetGst',
            'activeOrdersCount',
            'monthlyExpenses',
            'lowStockCount',
            'chartMonths',
            'chartSalesData',
            'chartExpenseData',
            'topClientsData',
            'recentInvoices',
            'recentOrders',
            'lowStockMaterials',
        ]);
    }

    /**
     * Test Reports Page Tabs and Filters.
     */
    public function test_reports_page_tabs_and_gst_calculation(): void
    {
        $user = User::create([
            'name' => 'Patel Admin',
            'email' => 'rep_test_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'status' => 'approved',
            'is_active' => true,
            'role' => 'super_admin',
        ]);

        // Default parameters
        $response = $this->actingAs($user)->get(route('reports'));
        $response->assertStatus(200);
        $response->assertViewHasAll(['startDate', 'endDate', 'period', 'reportType']);
        $response->assertViewHas('period', 'all');

        // Tabs
        $this->actingAs($user)->get(route('reports', ['report_type' => 'invoice']))->assertStatus(200)->assertViewHas('period', 'all');
        $this->actingAs($user)->get(route('reports', ['report_type' => 'purchase']))->assertStatus(200)->assertViewHas('period', 'all');
        $this->actingAs($user)->get(route('reports', ['report_type' => 'financial']))->assertStatus(200)->assertViewHas('period', 'all');
        $this->actingAs($user)->get(route('reports', ['report_type' => 'expense']))->assertStatus(200);

        // Date period filters
        $responseMonth = $this->actingAs($user)->get(route('reports', ['filter_period' => 'month', 'filter_month' => '2026-05']));
        $responseMonth->assertStatus(200)->assertViewHas('startDate', '2026-05-01')->assertViewHas('endDate', '2026-05-31');

        $responseYear = $this->actingAs($user)->get(route('reports', ['filter_period' => 'year', 'filter_year' => '2025']));
        $responseYear->assertStatus(200)->assertViewHas('startDate', '2025-04-01')->assertViewHas('endDate', '2026-03-31');

        // CSV Exports
        $responseExportAll = $this->actingAs($user)->get(route('reports.export', ['report_type' => 'invoice', 'filter_period' => 'all']));
        $responseExportAll->assertStatus(200);
        $this->assertTrue(str_contains($responseExportAll->headers->get('Content-Disposition'), 'PWW_Invoice_Report_'));

        $responseExportPurchase = $this->actingAs($user)->get(route('reports.export', ['report_type' => 'purchase']));
        $responseExportPurchase->assertStatus(200);
        $this->assertTrue(str_contains($responseExportPurchase->headers->get('Content-Disposition'), 'PWW_Purchase_Report_'));
    }

    /**
     * Test Every ERP Page Renders Cleanly as Full Page AND SPA Partial with Correct Cache Headers.
     */
    public function test_all_erp_pages_render_cleanly_both_full_and_spa(): void
    {
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@pww.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
            'status' => 'active',
        ]);

        $routesToTest = [
            route('overview'),
            route('rawmaterial'),
            route('product'),
            route('bom'),
            route('production'),
            route('clients'),
            route('orders'),
            route('invoices'),
            route('purchases'),
            route('expenses'),
            route('employees'),
            route('reports'),
            route('backup.index'),
            route('settings.index'),
            route('activity-logs'),
            route('profile'),
        ];

        foreach ($routesToTest as $url) {
            // 1. Full Page Load
            $fullRes = $this->actingAs($admin)->get($url);
            $fullRes->assertStatus(200);
            $fullRes->assertSee('<!DOCTYPE html>', false);
            $fullRes->assertSee('id="sidebar"', false);
            $fullRes->assertSee('id="page-content"', false);

            // 2. SPA Partial Load
            $spaRes = $this->actingAs($admin)->get($url, [
                'X-Requested-With' => 'XMLHttpRequest',
                'X-PWW-SPA' => '1',
            ]);
            $spaRes->assertStatus(200);
            $spaRes->assertSee('id="page-content"', false);
            $spaRes->assertHeader('Vary', 'X-PWW-SPA, X-Requested-With, Accept');
            $spaRes->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store, private');
            $spaRes->assertHeader('Pragma', 'no-cache');
        }
    }

    /**
     * Test Session Inactivity Timeout Returns Clean 401 on SPA and 302 on Standard GET.
     */
    public function test_session_inactivity_timeout_handling(): void
    {
        $admin = User::create([
            'name' => 'Timeout User',
            'email' => 'timeout@pww.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
            'status' => 'active',
        ]);

        // 1. SPA request after timeout -> 401 JSON
        session(['last_activity_time' => time() - (300 * 60)]);
        $this->actingAs($admin);
        $spaRes = $this->get(route('activity-logs'), [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-PWW-SPA' => '1',
        ]);
        $spaRes->assertStatus(401)->assertJson(['success' => false, 'redirect' => route('login')]);

        // 2. Standard browser request after timeout -> 302 Redirect
        session(['last_activity_time' => time() - (300 * 60)]);
        $this->actingAs($admin);
        $webRes = $this->get(route('activity-logs'));
        $webRes->assertRedirect(route('login'));
    }

    /**
     * Test Global Search API Endpoint with Multi-Category Grouping.
     */
    public function test_global_search_endpoint_returns_categorized_results(): void
    {
        // 1. Guest request should redirect to login
        $guestRes = $this->get(route('global.search', ['q' => 'Invoice']));
        $guestRes->assertRedirect(route('login'));

        $admin = User::create([
            'name' => 'Search Tester',
            'email' => 'search_tester@pww.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
            'status' => 'active',
            'is_active' => true,
        ]);

        // 2. Empty query returns empty set
        $emptyRes = $this->actingAs($admin)->getJson(route('global.search', ['q' => '']));
        $emptyRes->assertStatus(200)
            ->assertJson(['success' => true, 'total' => 0, 'results' => []]);

        // 3. Navigation shortcuts search
        $navRes = $this->actingAs($admin)->getJson(route('global.search', ['q' => 'Attendance']));
        $navRes->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['results' => ['Navigation & Pages']]);

        // 4. Create entities and test deep multi-category search
        $client = Client::create(['company_name' => 'Global Search Client Ltd', 'client_email' => 'search@client.com', 'gst_number' => '24GSCLL1234A1Z1']);
        $plant = ClientPlant::create(['client_id' => $client->id, 'plant_name' => 'Main Facility', 'opening_balance' => 0, 'state' => 'Gujarat']);
        $product = Product::create(['product_name' => 'Welded Flange Bracket', 'selling_price' => 450.00, 'gst_rate' => 18.00, 'current_stock' => 80]);
        $invoice = Invoice::create([
            'invoice_number' => 'PWW/26-27/SRCH-99',
            'plant_id' => $plant->id,
            'invoice_mode' => 'finished_goods',
            'tax_type' => 'regular',
            'taxable_amount' => 1000.00,
            'cgst' => 90.00,
            'sgst' => 90.00,
            'igst' => 0.00,
            'total_amount' => 1180.00,
            'payment_status' => 'unpaid',
            'vehicle_number' => 'GJ03AA1234',
        ]);

        // Search by Invoice Doc No
        $invRes = $this->actingAs($admin)->getJson(route('global.search', ['q' => 'SRCH-99']));
        $invRes->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonFragment(['title' => 'PWW/26-27/SRCH-99 — Global Search Client Ltd (Main Facility)']);

        // Search by Client Company Name
        $clientRes = $this->actingAs($admin)->getJson(route('global.search', ['q' => 'Global Search Client']));
        $clientRes->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonFragment(['title' => 'Global Search Client Ltd']);

        // Search by Product Name
        $prodRes = $this->actingAs($admin)->getJson(route('global.search', ['q' => 'Welded Flange']));
        $prodRes->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonFragment(['title' => 'Welded Flange Bracket']);

        // 5. Test Disabling Global Search Module via Settings
        $toggleRes = $this->actingAs($admin)->postJson(route('settings.modules'), [
            'module_global_search' => 'false'
        ]);
        $toggleRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals('false', Setting::get('module_global_search'));

        // When disabled, header should not include search and search endpoint returns disabled
        $disabledSearchRes = $this->actingAs($admin)->getJson(route('global.search', ['q' => 'Welded Flange']));
        $disabledSearchRes->assertStatus(200)
            ->assertJson([
                'success' => false,
                'total' => 0,
                'results' => []
            ]);

        $overviewRes = $this->actingAs($admin)->get(route('overview'));
        $overviewRes->assertStatus(200);
        $overviewRes->assertDontSee('id="globalSearchInput"', false);

        // Re-enable for subsequent tests
        Setting::set('module_global_search', 'true');
    }

    /**
     * Test Route-Level Page Permissions Enforcement (SEC-001).
     */
    public function test_route_level_page_permissions_enforced(): void
    {
        // Create a restricted custom user who lacks reports and invoices permissions
        $restrictedUser = User::factory()->create([
            'role' => 'custom',
            'status' => 'active',
            'is_active' => true,
            'permissions' => ['page_overview'],
        ]);

        // Accessing allowed page succeeds
        $resAllowed = $this->actingAs($restrictedUser)->get(route('overview'));
        $resAllowed->assertStatus(200);

        // Accessing forbidden reports route via web redirects to overview with error message
        $resReports = $this->actingAs($restrictedUser)->get(route('reports'));
        $resReports->assertRedirect(route('overview'));
        $resReports->assertSessionHas('error');

        // Accessing forbidden invoices route via JSON returns 403 Forbidden
        $resInvoices = $this->actingAs($restrictedUser)->getJson(route('invoices'));
        $resInvoices->assertStatus(403);
    }

    /**
     * Test Super Admin Role Escalation Guard (SEC-002).
     */
    public function test_super_admin_role_escalation_guard(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'status' => 'active', 'is_active' => true]);
        $standardAdmin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'is_active' => true]);
        $targetStaff = User::factory()->create(['role' => 'staff', 'status' => 'active', 'is_active' => true]);

        // Standard admin tries to promote a staff user to super_admin -> should be rejected
        $promoteRes = $this->actingAs($standardAdmin)->putJson(route('settings.users.update', $targetStaff->id), [
            'name' => 'Target Staff',
            'email' => $targetStaff->email,
            'role' => 'super_admin',
        ]);
        $promoteRes->assertStatus(422)->assertJson(['success' => false]);
        $this->assertEquals('staff', $targetStaff->fresh()->role);

        // Standard admin tries to demote the super_admin account -> should be rejected
        $demoteRes = $this->actingAs($standardAdmin)->putJson(route('settings.users.update', $superAdmin->id), [
            'name' => 'Super Owner',
            'email' => $superAdmin->email,
            'role' => 'staff',
        ]);
        $demoteRes->assertStatus(422)->assertJson(['success' => false]);
        $this->assertEquals('super_admin', $superAdmin->fresh()->role);
    }

    /**
     * Test storePlant Authorization Guard (SEC-004).
     */
    public function test_store_plant_permission_guard(): void
    {
        $client = Client::create(['company_name' => 'Plant Test Client', 'gst_number' => '24AAAAB1111A1Z5']);

        // User with no insert permission
        $viewOnlyUser = User::factory()->create([
            'role' => 'view_only',
            'status' => 'active',
            'is_active' => true,
            'permissions' => [
                'page_clients' => true,
                'action_insert' => false,
            ],
        ]);

        $res = $this->actingAs($viewOnlyUser)->postJson(route('clients.plants.store'), [
            'client_id' => $client->id,
            'plant_name' => 'Unauthorized Plant',
            'state' => 'Gujarat',
            'shipping_address' => 'Test Address, Rajkot',
        ]);
        $res->assertStatus(403);
    }

    /**
     * Test GSTR-1 CSV and Blade Views Client GST Number (BUG-001, BUG-002, BUG-003).
     */
    public function test_gstr1_csv_and_views_output_client_gst_number(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $client = Client::create([
            'company_name' => 'GST Test Enterprises',
            'gst_number' => '24ABCDE1234F1Z5',
        ]);
        $plant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Sanand Plant',
            'gst_number' => '24ABCDE1234F1Z5',
            'state' => 'Gujarat',
        ]);

        $invoice = Invoice::create([
            'plant_id' => $plant->id,
            'invoice_number' => 'PWW/26-27/GST-01',
            'invoice_date' => '2026-08-15',
            'total_taxable_value' => 10000.00,
            'cgst' => 900.00,
            'sgst' => 900.00,
            'igst' => 0.00,
            'total_amount' => 11800.00,
            'payment_status' => 'paid',
            'paid_amount' => 11800.00,
        ]);

        // Test GSTR-1 CSV export contains real GSTIN
        $csvResponse = $this->actingAs($admin)->get(route('reports.export', [
            'report_type' => 'gst',
            'gst_type' => 'gstr1',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));
        $csvResponse->assertStatus(200);

        ob_start();
        $csvResponse->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('24ABCDE1234F1Z5', $content);
        $this->assertStringContainsString('GST Test Enterprises', $content);
    }

    /**
     * Test E-Way Bill Dynamic GST Rates (BUG-004).
     */
    public function test_eway_bill_dynamic_gst_rates(): void
    {
        $product12 = Product::create([
            'product_name' => '12% Special Mesh',
            'gst_rate' => 12.00,
            'hsn_code' => '7314',
            'selling_price' => 500.00,
        ]);

        $client = Client::create(['company_name' => 'EWay Client', 'gst_number' => '24AAAAB1111A1Z5']);
        $plant = ClientPlant::create(['client_id' => $client->id, 'plant_name' => 'Main', 'gst_number' => '24AAAAB1111A1Z5', 'state' => 'Gujarat']);

        $invoice = Invoice::create([
            'plant_id' => $plant->id,
            'invoice_number' => 'PWW/26-27/EWAY-12',
            'invoice_date' => '2026-08-20',
            'total_taxable_value' => 5000.00,
            'cgst' => 300.00,
            'sgst' => 300.00,
            'igst' => 0.00,
            'total_amount' => 5600.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'product',
            'product_id' => $product12->id,
            'item_name' => $product12->product_name,
            'quantity' => 10,
            'unit_price' => 500.00,
            'total_price' => 5000.00,
        ]);

        $payload = \App\Services\EwayBillService::generateJsonPayload($invoice);
        $items = $payload['billLists'][0]['itemList'];

        $this->assertCount(1, $items);
        $this->assertEquals(6.0, $items[0]['cgstRate']);
        $this->assertEquals(6.0, $items[0]['sgstRate']);
        $this->assertEquals(0.0, $items[0]['igstRate']);
    }

    /**
     * Test Purchase Deletion Locked FY Check & Transaction Cleanup (BUG-005 & BUG-007).
     */
    public function test_purchase_deletion_locked_fy_check_and_transaction(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        // Lock FY 2025-26
        Setting::set('locked_financial_years', json_encode(['2025-26']));

        $lockedPurchase = Purchase::create([
            'vendor_name' => 'Historical Vendor Ltd',
            'item_name' => 'Historical Steel Rods',
            'purchase_type' => 'raw_material',
            'total_amount' => 5000.00,
            'gst_rate' => 18.00,
            'gst_amount' => 762.71,
            'purchase_date' => '2025-06-15',
            'payment_status' => 'paid',
        ]);

        // Attempting to delete purchase from locked FY should return 422
        $delLockedRes = $this->actingAs($admin)->deleteJson(route('purchases.delete', $lockedPurchase->id));
        $delLockedRes->assertStatus(422)
            ->assertJson(['success' => false]);
        $this->assertNotNull(Purchase::find($lockedPurchase->id));

        // Unlock for clean state
        Setting::set('locked_financial_years', json_encode([]));

        // Create unlocked purchase with payment
        $unlockedPurchase = Purchase::create([
            'vendor_name' => 'Current Vendor Ltd',
            'item_name' => 'Current Steel Rods',
            'purchase_type' => 'others',
            'total_amount' => 3000.00,
            'gst_rate' => 18.00,
            'gst_amount' => 457.63,
            'purchase_date' => '2026-08-10',
            'payment_status' => 'paid',
        ]);

        $payment = \App\Models\Payment::create([
            'payment_number' => \App\Models\Payment::generatePaymentNumber('paid'),
            'payment_type' => 'paid',
            'purchase_id' => $unlockedPurchase->id,
            'amount' => 3000.00,
            'payment_date' => '2026-08-10',
        ]);

        $delUnlockedRes = $this->actingAs($admin)->deleteJson(route('purchases.delete', $unlockedPurchase->id));
        $delUnlockedRes->assertStatus(200)->assertJson(['success' => true]);

        $this->assertNull(Purchase::find($unlockedPurchase->id));
        $this->assertNull(\App\Models\Payment::find($payment->id));
    }

    /**
     * Test Product Deletion Safeguard with History (BUG-006).
     */
    public function test_product_deletion_safeguard_with_history(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $product = Product::create([
            'product_name' => 'Critical Protected Product',
            'hsn_code' => '7314',
            'selling_price' => 100.00,
        ]);

        // Add a production log
        ProductionLog::create([
            'product_id' => $product->id,
            'quantity_manufactured' => 50,
            'quantity_rejected' => 0,
            'recorded_by' => $admin->id,
            'production_date' => '2026-08-20',
        ]);

        // Attempting to delete product with production history should be blocked with 422
        $delRes = $this->actingAs($admin)->deleteJson(route('inventory.goods.delete', $product->id));
        $delRes->assertStatus(422)
            ->assertJson(['success' => false]);
        $this->assertNotNull(Product::find($product->id));
    }

    /**
     * Test Overview Top Clients Includes Direct Sales (BUG-008).
     */
    public function test_overview_top_clients_with_direct_sales(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        // Create direct raw material sale with null plant_id
        $directInv = Invoice::create([
            'plant_id' => null,
            'invoice_mode' => 'raw_material',
            'custom_client_name' => 'Direct Retail Buyer',
            'invoice_number' => 'PWW/26-27/RMS-999',
            'invoice_date' => '2026-08-22',
            'total_taxable_value' => 50000.00,
            'total_amount' => 59000.00,
            'payment_status' => 'paid',
        ]);

        $overviewRes = $this->actingAs($admin)->get(route('overview'));
        $overviewRes->assertStatus(200);
        $overviewRes->assertSee('Direct Retail Buyer');
    }

    /**
     * Test Expense Deletion Cleans Up Salary Links (BUG-011).
     */
    public function test_expense_deletion_cleans_salary_links(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $staff = StaffProfile::create([
            'full_name' => 'Salary Staff Profile',
            'wage_type' => 'fixed',
            'monthly_salary' => 15000.00,
            'is_active' => true,
        ]);

        $expense = Expense::create([
            'expense_category' => 'Employee Salary / Payroll',
            'amount' => 15000.00,
            'expense_date' => '2026-08-20',
            'description' => 'Salary payout test',
        ]);

        $payment = SalaryPayment::create([
            'staff_profile_id' => $staff->id,
            'month_year' => '2026-08',
            'total_salary' => 15000.00,
            'status' => 'paid',
            'expense_id' => $expense->id,
        ]);

        $delExpRes = $this->actingAs($admin)->deleteJson(route('expense.delete', $expense->id));
        $delExpRes->assertStatus(200)->assertJson(['success' => true]);

        $this->assertNull(Expense::find($expense->id));
        $this->assertNull($payment->fresh()->expense_id);
    }

    public function test_measurement_units_crud_and_uqc_mapping()
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // 1. Initial default units & GST UQC resolution
        $units = \App\Services\UnitService::getUnits();
        $this->assertNotEmpty($units);
        $this->assertEquals('KGS', \App\Services\UnitService::mapToUqc('kg'));
        $this->assertEquals('NOS', \App\Services\UnitService::mapToUqc('pcs'));
        $this->assertEquals('MTR', \App\Services\UnitService::mapToUqc('meter'));

        // 2. Store custom measurement unit
        $storeRes = $this->actingAs($admin)->postJson(route('settings.units.store'), [
            'name' => 'Industrial Drum',
            'symbol' => 'drum',
            'uqc' => 'DRM',
            'type' => 'volume',
            'precision' => 2,
        ]);
        $storeRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals('DRM', \App\Services\UnitService::mapToUqc('drum'));

        // 3. Update measurement unit
        $updateRes = $this->actingAs($admin)->postJson(route('settings.units.update'), [
            'key' => 'drum',
            'name' => 'Large Steel Drum',
            'symbol' => 'drum',
            'uqc' => 'DRM',
            'type' => 'packaging',
            'precision' => 0,
        ]);
        $updateRes->assertStatus(200)->assertJson(['success' => true]);

        // 4. Protection on core system units
        $delCoreRes = $this->actingAs($admin)->postJson(route('settings.units.delete'), [
            'key' => 'kg',
        ]);
        $delCoreRes->assertStatus(422)->assertJson(['success' => false]);

        // 5. Delete custom unit
        $delCustomRes = $this->actingAs($admin)->postJson(route('settings.units.delete'), [
            'key' => 'drum',
        ]);
        $delCustomRes->assertStatus(200)->assertJson(['success' => true]);
    }
}



