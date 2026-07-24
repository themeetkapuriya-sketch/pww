<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RawMaterial;
use App\Models\Product;
use App\Models\BillOfMaterial;
use App\Models\Client;
use App\Models\ClientPlant;
use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\StaffProfile;
use App\Models\LaborLog;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\ProductionLog;
use App\Services\ProductionService;
use App\Services\BillingService;
use App\Services\PayrollService;
use App\Services\FinancialService;
use App\Exceptions\InsufficientStockException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ErpFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $productionService;
    protected $billingService;
    protected $payrollService;
    protected $financialService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

        $this->productionService = resolve(ProductionService::class);
        $this->billingService = resolve(BillingService::class);
        $this->payrollService = resolve(PayrollService::class);
        $this->financialService = resolve(FinancialService::class);
    }

    /**
     * Test the Multi-Stage Stock Auto-Deduction Engine.
     */
    public function test_stock_auto_deduction_engine()
    {
        // Setup User
        $user = User::create([
            'name' => 'Manager User',
            'email' => 'manager@pww.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        // Setup Raw Materials
        $iron = RawMaterial::create([
            'material_name' => 'Iron Coil',
            'unit' => 'kg',
            'current_stock' => 100.00,
            'safety_threshold' => 10.00,
            'average_purchase_price' => 50.00,
        ]);

        // Setup Finished Good
        $rack = Product::create([
            'product_name' => 'Super Rack',
            'sku' => 'SR-01',
            'current_stock' => 10,
            'selling_price' => 1000.00,
        ]);

        // Setup BOM (Requires 5.0kg of iron per rack, with 10% waste percentage)
        // Consumed per rack = 5.0 * (1 + 10/100) = 5.5kg
        BillOfMaterial::create([
            'product_id' => $rack->id,
            'raw_material_id' => $iron->id,
            'required_quantity' => 5.0,
            'waste_percentage' => 10.00,
        ]);

        // Setup Staff
        $staff = StaffProfile::create([
            'user_id' => null,
            'full_name' => 'Worker Amit',
            'wage_type' => 'per-day',
            'piece_rate_per_unit' => 20.00,
        ]);

        // Log production of 10 racks
        // Consumed iron: 10 * 5.5 = 55.0kg
        $productionLog = $this->productionService->logProduction(
            $rack->id,
            10, // manufactured
            1,  // rejected
            $user->id,
            Carbon::now()->toDateString(),
            [
                [
                    'staff_profile_id' => $staff->id,
                    'units_completed' => 10
                ]
            ]
        );

        // Assertions
        $this->assertInstanceOf(ProductionLog::class, $productionLog);
        $this->assertEquals(20, Product::find($rack->id)->current_stock); // 10 initial + 10 made
        $this->assertEquals(45.00, RawMaterial::find($iron->id)->current_stock); // 100 - 55 = 45

        // Verify Labor log created
        $laborLog = LaborLog::where('production_log_id', $productionLog->id)->first();
        $this->assertNotNull($laborLog);
        $this->assertEquals(10, $laborLog->units_completed);
        $this->assertEquals(200.00, $laborLog->calculated_payout); // 10 units * 20.00 rate
        $this->assertEquals('pending', $laborLog->status);

        // Attempt production that exceeds current stock (Requires 10 * 5.5 = 55kg, only 45kg left)
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
     * Test the Corporate B2B Billing Module for state tax configurations.
     */
    public function test_regional_gst_billing_logic()
    {
        // Setup B2B Client
        $client = Client::create([
            'company_name' => 'Balaji Wafers',
            'gst_number' => '24AAACB1234A1Z9',
            'corporate_address' => 'Rajkot, Gujarat',
        ]);

        // Gujarat plant (Intra-state)
        $gujaratPlant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Valsad Plant',
            'shipping_address' => 'Valsad, Gujarat',
            'state' => 'Gujarat',
        ]);

        // Indore plant (Inter-state)
        $indorePlant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Indore Plant',
            'shipping_address' => 'Indore, MP',
            'state' => 'Madhya Pradesh',
        ]);

        // Setup Finished Good
        $rack = Product::create([
            'product_name' => 'Wire Rack',
            'sku' => 'WR-01',
            'current_stock' => 100,
            'selling_price' => 1000.00,
        ]);

        // 1. Test Intrastate (Gujarat) Invoice GST Calculation
        $gstGuj = $this->billingService->calculateGstBreakdown($gujaratPlant->id, [
            ['product_id' => $rack->id, 'quantity' => 10, 'unit_price' => 1000.00]
        ]);

        $this->assertEquals(10000.00, $gstGuj['taxable_value']);
        $this->assertEquals(900.00, $gstGuj['cgst']); // 9% of 10000
        $this->assertEquals(900.00, $gstGuj['sgst']); // 9% of 10000
        $this->assertEquals(0.00, $gstGuj['igst']);
        $this->assertEquals(11800.00, $gstGuj['total_amount']);

        // 2. Test Interstate (Indore, MP) Invoice GST Calculation
        $gstInd = $this->billingService->calculateGstBreakdown($indorePlant->id, [
            ['product_id' => $rack->id, 'quantity' => 20, 'unit_price' => 1000.00]
        ]);

        $this->assertEquals(20000.00, $gstInd['taxable_value']);
        $this->assertEquals(0.00, $gstInd['cgst']);
        $this->assertEquals(0.00, $gstInd['sgst']);
        $this->assertEquals(3600.00, $gstInd['igst']); // 18% of 20000
        $this->assertEquals(23600.00, $gstInd['total_amount']);
    }

    /**
     * Test Payroll compilation and payout mark as paid.
     */
    public function test_payroll_piece_rate_matrix()
    {
        $staff = StaffProfile::create([
            'user_id' => null,
            'full_name' => 'Amit Sharma',
            'wage_type' => 'per-day',
            'piece_rate_per_unit' => 50.00,
        ]);

        // Mock production log
        $good = Product::create([
            'product_name' => 'Rack',
            'sku' => 'RK-01',
            'current_stock' => 10,
            'selling_price' => 500,
        ]);

        $user = User::create([
            'name' => 'Manager',
            'email' => 'm@pww.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

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

        // Compile pending wages
        $compiled = $this->payrollService->compilePendingPieceRateWages();
        $this->assertCount(1, $compiled);
        $this->assertEquals(60, $compiled[0]['total_units_completed']);
        $this->assertEquals(3000.00, $compiled[0]['total_pending_payout']);

        // Mark as paid
        $updatedRows = $this->payrollService->markWagesAsPaid([$log1->id, $log2->id]);
        $this->assertEquals(2, $updatedRows);

        // Compile again (should be empty now)
        $compiledAfter = $this->payrollService->compilePendingPieceRateWages();
        $this->assertCount(0, $compiledAfter);

        $this->assertEquals('paid', LaborLog::find($log1->id)->status);
        $this->assertEquals('paid', LaborLog::find($log2->id)->status);
    }

    /**
     * Test Financial Net Profit Engine calculation framework.
     */
    public function test_financial_profit_engine()
    {
        // 1. Revenue: create paid invoice (excl tax 10,000)
        $inv = Invoice::create([
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

        // 2. Purchase entry: ₹2,000
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

        // 3. Logged Expense: Rent ₹1,500
        Expense::create([
            'expense_category' => 'office_rent',
            'amount' => 1500.00,
            'expense_date' => Carbon::now()->toDateString(),
        ]);

        // 4. Logged Expense: Depreciation ₹500
        Expense::create([
            'expense_category' => 'machinery_depreciation',
            'amount' => 500.00,
            'expense_date' => Carbon::now()->toDateString(),
        ]);

        // Calculate expected net profit:
        // Revenue (excl tax): 10,000
        // Purchases: 2,000
        // Total Expenses: 1,500 + 500 = 2,000
        // Expected Net Profit = 10,000 - 2,000 - 2,000 = ₹6,000

        $summary = $this->financialService->getFinancialSummary(
            Carbon::now()->subDay()->toDateString(),
            Carbon::now()->addDay()->toDateString()
        );

        $this->assertEquals(10000.00, $summary['revenue']);
        $this->assertEquals(2000.00, $summary['total_purchases']);
        $this->assertEquals(2000.00, $summary['total_expenses']);
        $this->assertEquals(6000.00, $summary['net_profit']);
    }

    /**
     * Test guest redirection to login.
     */
    public function test_guest_redirect_to_login()
    {
        $response = $this->get('/overview');
        $response->assertRedirect('/login');
    }

    /**
     * Test AJAX login verification.
     */
    public function test_ajax_login_flow()
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        // Failed login attempts to test rate limiting
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/login', [
                'email' => 'ratelimit_' . uniqid() . '@pww.com',
                'password' => 'wrong'
            ]);
        }

        // 6th attempt with same email + IP should trigger HTTP 429
        $targetEmail = 'lockout@pww.com';
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/login', [
                'email' => $targetEmail,
                'password' => 'wrongpassword'
            ]);
        }
        $response = $this->postJson('/login', [
            'email' => $targetEmail,
            'password' => 'wrongpassword'
        ]);
        $response->assertStatus(429);

        // Successful login for valid user
        $response = $this->postJson('/login', [
            'email' => 'praful@pww.com',
            'password' => 'admin123'
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('overview')
        ]);
    }

    /**
     * Test AJAX custom direct invoice generation.
     */
    public function test_ajax_custom_invoice_generation()
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $client = Client::create([
            'company_name' => 'Balaji Wafers',
        ]);

        $plant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Rajkot',
            'state' => 'Gujarat',
        ]);

        $good = Product::create([
            'product_name' => 'Rack A',
            'sku' => 'RA-01',
            'current_stock' => 10,
            'selling_price' => 500,
        ]);

        $response = $this->actingAs($user)->postJson(route('invoice.generate'), [
            'invoice_number' => 'PWW-CUSTOM-999',
            'plant_id' => $plant->id,
            'due_date' => Carbon::now()->addDays(30)->toDateString(),
            'product_ids' => [$good->id],
            'quantities' => [10],
            'unit_prices' => [500],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $invoice = Invoice::where('invoice_number', 'PWW-CUSTOM-999')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(5000.00, $invoice->total_taxable_value);
        $this->assertEquals(450.00, $invoice->cgst); // 9%
        $this->assertEquals(450.00, $invoice->sgst); // 9%
        $this->assertEquals(5900.00, $invoice->total_amount);
    }

    /**
     * Test Delivery Vehicle Number Validation (Accepts valid RTO & BH series, rejects invalid strings).
     */
    public function test_vehicle_number_validation()
    {
        $user = User::create([
            'name' => 'Patel Admin',
            'email' => 'v_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $client = Client::create([
            'company_name' => 'Logistics Co',
            'client_email' => 'logistics@example.com',
            'gst_number' => '24AAAAA0000A1Z5',
            'corporate_address' => 'Surat, Gujarat',
        ]);

        $plant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Surat Plant',
            'state' => 'Gujarat',
        ]);

        $good = Product::create([
            'product_name' => 'Transport Item',
            'sku' => 'TR-' . uniqid(),
            'current_stock' => 50,
            'selling_price' => 100,
        ]);

        // 1. Test VALID vehicle number (GJ-03-BW-1234) -> Should succeed (200)
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
        $this->assertNotNull($invObj);
        $this->assertEquals('GJ-03-BW-1234', $invObj->vehicle_number);

        // 2. Test INVALID vehicle number ("INVALID_VEHICLE_NUM") -> Should fail validation (422)
        $respInvalid = $this->actingAs($user)->postJson(route('invoice.generate'), [
            'invoice_number' => 'PWW-VEH-002',
            'plant_id' => $plant->id,
            'vehicle_number' => 'INVALID_VEHICLE_NUM',
            'finished_good_ids' => [$good->id],
            'quantities' => [1],
            'unit_prices' => [100],
        ]);
        $respInvalid->assertStatus(422);
        $respInvalid->assertJsonValidationErrors(['vehicle_number']);
    }

    /**
     * Test Out-of-State Plant GSTIN Validation (Rejects Gujarat 24 GSTIN for Madhya Pradesh 23 Plant).
     */
    public function test_out_of_state_plant_gstin_validation()
    {
        $user = User::create([
            'name' => 'Patel Admin',
            'email' => 'gst_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $client = Client::create([
            'company_name' => 'Gujarat Steel HQ',
            'client_email' => 'gujsteel@example.com',
            'gst_number' => '24AAAAA0000A1Z5',
            'corporate_address' => 'Rajkot, Gujarat',
        ]);

        // Trying to save Gujarat 24 GSTIN for Madhya Pradesh 23 Plant -> MUST FAIL (422)
        $response = $this->actingAs($user)->postJson(route('clients.plants.store'), [
            'client_id' => $client->id,
            'plant_name' => 'Indore Factory',
            'state' => 'Madhya Pradesh',
            'gst_number' => '24AAACB1234A1Z9', // Incorrect state code for MP!
            'shipping_address' => 'Indore, MP',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gst_number']);

        // Correct MP GSTIN starting with 23 -> MUST SUCCEED (200)
        $responseValid = $this->actingAs($user)->postJson(route('clients.plants.store'), [
            'client_id' => $client->id,
            'plant_name' => 'Indore Factory',
            'state' => 'Madhya Pradesh',
            'gst_number' => '23AAACB1234A1Z9', // Correct MP state code 23!
            'shipping_address' => 'Indore, MP',
        ]);

        $responseValid->assertStatus(200);
    }

    /**
     * Test AJAX Invoice Deletion.
     */
    public function test_ajax_invoice_deletion()
    {
        $user = User::create([
            'name' => 'Patel Admin',
            'email' => 'del_inv_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $client = Client::create([
            'company_name' => 'Deletion Test Client',
            'client_email' => 'delclient@example.com',
            'gst_number' => '24AAAAA0000A1Z5',
            'corporate_address' => 'Rajkot, Gujarat',
        ]);

        $plant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Deletion Plant',
            'state' => 'Gujarat',
            'shipping_address' => 'Rajkot, Gujarat',
        ]);

        $good = Product::create([
            'product_name' => 'Delete Item',
            'sku' => 'DEL-01',
            'current_stock' => 10,
            'selling_price' => 500,
        ]);

        // Generate Invoice
        $this->actingAs($user)->postJson(route('invoice.generate'), [
            'invoice_number' => 'PWW-DEL-999',
            'plant_id' => $plant->id,
            'finished_good_ids' => [$good->id],
            'quantities' => [1],
            'unit_prices' => [500],
        ]);

        $inv = Invoice::where('invoice_number', 'PWW-DEL-999')->first();
        $this->assertNotNull($inv);

        // Delete Invoice via DELETE Route
        $response = $this->actingAs($user)->deleteJson(route('invoice.delete', $inv->id));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertNull(Invoice::find($inv->id));
    }

    /**
     * Test Reports Page Tabs and GST ITC calculations.
     */
    public function test_reports_page_tabs_and_gst_calculation()
    {
        $user = User::create([
            'name' => 'Patel Admin',
            'email' => 'rep_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);

        // 1. Test page load with default parameters
        $response = $this->actingAs($user)->get(route('reports'));
        $response->assertStatus(200);
        $response->assertViewHasAll(['startDate', 'endDate', 'period', 'reportType']);
        $response->assertViewHas('period', 'all'); // Sales defaults to all records!

        // 2. Test different tabs
        $responseInvoice = $this->actingAs($user)->get(route('reports', ['report_type' => 'invoice']));
        $responseInvoice->assertStatus(200);
        $responseInvoice->assertViewHas('period', 'all');

        $responsePurchase = $this->actingAs($user)->get(route('reports', ['report_type' => 'purchase']));
        $responsePurchase->assertStatus(200);
        $responsePurchase->assertViewHas('period', 'all');

        $responseFinancial = $this->actingAs($user)->get(route('reports', ['report_type' => 'financial']));
        $responseFinancial->assertStatus(200);
        $responseFinancial->assertViewHas('period', 'all');

        $responseExpense = $this->actingAs($user)->get(route('reports', ['report_type' => 'expense']));
        $responseExpense->assertStatus(200);

        // 3. Test predefined period filters
        $responseMonth = $this->actingAs($user)->get(route('reports', [
            'filter_period' => 'month',
            'filter_month' => '2026-05'
        ]));
        $responseMonth->assertStatus(200);
        $responseMonth->assertViewHas('startDate', '2026-05-01');
        $responseMonth->assertViewHas('endDate', '2026-05-31');

        $responseYear = $this->actingAs($user)->get(route('reports', [
            'filter_period' => 'year',
            'filter_year' => '2025'
        ]));
        $responseYear->assertStatus(200);
        $responseYear->assertViewHas('startDate', '2025-04-01');
        $responseYear->assertViewHas('endDate', '2026-03-31');

        $responseAll = $this->actingAs($user)->get(route('reports', ['filter_period' => 'all']));
        $responseAll->assertStatus(200);

        // 4. Test CSV Export status and headers
        $responseExportAll = $this->actingAs($user)->get(route('reports.export', ['report_type' => 'invoice', 'filter_period' => 'all']));
        $responseExportAll->assertStatus(200);
        $this->assertTrue(str_contains($responseExportAll->headers->get('Content-Disposition'), 'PWW_Invoice_Report_'));

        $responseExportPurchase = $this->actingAs($user)->get(route('reports.export', ['report_type' => 'purchase']));
        $responseExportPurchase->assertStatus(200);
        $this->assertTrue(str_contains($responseExportPurchase->headers->get('Content-Disposition'), 'PWW_Purchase_Report_'));
    }

    /**
     * Test Profile views and settings rendering.
     */
    public function test_profile_settings_view()
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
     * Test AJAX profile details update.
     */
    public function test_ajax_profile_update()
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->postJson(route('profile.update'), [
            'name' => 'New Praful Name',
            'email' => 'newemail@pww.com'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $user->refresh();
        $this->assertEquals('New Praful Name', $user->name);
        $this->assertEquals('newemail@pww.com', $user->email);
    }

    /**
     * Test AJAX password modification.
     */
    public function test_ajax_password_update()
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        // Failed password change (wrong current)
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
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $user->password));
    }

    /**
     * Test AJAX mark invoice as paid.
     */
    public function test_ajax_pay_invoice()
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
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
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->payment_status);
        $this->assertEquals(1180.00, $invoice->paid_amount);
    }

    /**
     * Test Invoice printable tax details sheet rendering.
     */
    public function test_invoice_print_rendering()
    {
        $user = User::create([
            'name' => 'Praful Patel',
            'email' => 'praful@pww.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $client = Client::create([
            'company_name' => 'Balaji Wafers',
        ]);

        $plant = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Rajkot plant',
            'state' => 'Gujarat',
        ]);

        $good = Product::create([
            'product_name' => 'Special Rack X',
            'sku' => 'SRX-99',
            'current_stock' => 10,
            'selling_price' => 500,
        ]);

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
        $response->assertSee('Rajkot plant');
        $response->assertSee('Special Rack X');
        $response->assertSee('SRX-99');
    }

    /**
     * Test Update Business Settings and logo file upload.
     */
    public function test_update_business_settings()
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@pww.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->create('business_logo.png', 100, 'image/png');

        $response = $this->actingAs($user)->post(route('profile.business'), [
            'business_name' => 'Custom Weld Inc',
            'business_subtitle' => 'Industrial Fabrication Division',
            'address_line_1' => 'GIDC Plot 100',
            'address_line_2' => 'Baroda, Gujarat',
            'gstin' => '24CUSTO1234A1Z9',
            'bank_name' => 'State Bank of India',
            'bank_account_name' => 'Custom Weld Inc',
            'bank_account_no' => '12345678901',
            'bank_ifsc' => 'SBIN0001234',
            'logo' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Business settings updated successfully!'
        ]);

        $this->assertEquals('Custom Weld Inc', \App\Models\Setting::get('business_name'));
        $this->assertEquals('Industrial Fabrication Division', \App\Models\Setting::get('business_subtitle'));
        $this->assertEquals('GIDC Plot 100', \App\Models\Setting::get('address_line_1'));
        $this->assertEquals('Baroda, Gujarat', \App\Models\Setting::get('address_line_2'));
        $this->assertEquals('24CUSTO1234A1Z9', \App\Models\Setting::get('gstin'));
        $this->assertStringContainsString('uploads/logo_', \App\Models\Setting::get('logo_path'));
    }

    /**
     * Test Client & Plant full CRUD flow with 1-click plant creation & plant-specific GSTIN.
     */
    public function test_client_and_plant_crud_operations()
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_client@pww.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // 1. Create Client with 1-Click Primary Plant Creation
        $response = $this->actingAs($user)->post(route('clients.store'), [
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

        $client = Client::where('company_name', 'Supreme Logistics Pvt Ltd')->first();
        $this->assertNotNull($client);
        $this->assertEquals(1, $client->plants()->count());

        $plant = $client->plants()->first();
        $this->assertEquals('Surat Main Factory', $plant->plant_name);
        $this->assertEquals('24SUPREME1234A1Z1', $plant->gst_number);

        // 2. Add Secondary Interstate Plant with State-Specific GSTIN
        $response = $this->actingAs($user)->post(route('clients.plants.store'), [
            'client_id' => $client->id,
            'plant_name' => 'Mumbai Distribution Hub',
            'state' => 'Maharashtra',
            'gst_number' => '27SUPREME1234A1Z8',
            'shipping_address' => 'MIDC Area, Thane, Maharashtra',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals(2, $client->plants()->count());

        $secPlant = ClientPlant::where('plant_name', 'Mumbai Distribution Hub')->first();
        $this->assertEquals('27SUPREME1234A1Z8', $secPlant->gst_number);

        // 3. Update Client Profile
        $response = $this->actingAs($user)->put(route('clients.update', $client->id), [
            'company_name' => 'Supreme Global Logistics Pvt Ltd',
            'client_email' => 'info@supremeglobal.com',
            'gst_number' => '24SUPREME1234A1Z1',
            'corporate_address' => 'HQ Tower, Ring Road, Surat, Gujarat',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals('Supreme Global Logistics Pvt Ltd', $client->fresh()->company_name);

        // 4. Update Plant Details
        $response = $this->actingAs($user)->put(route('clients.plants.update', $secPlant->id), [
            'plant_name' => 'Mumbai Mega Hub',
            'state' => 'Maharashtra',
            'gst_number' => '27SUPREME9999A1Z9',
            'shipping_address' => 'Navi Mumbai Logistics Park, Maharashtra',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals('Mumbai Mega Hub', $secPlant->fresh()->plant_name);
        $this->assertEquals('27SUPREME9999A1Z9', $secPlant->fresh()->gst_number);

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
     * Test Sales Orders CRUD, status updates, and 1-Click Delivery Challan conversion.
     */
    public function test_sales_orders_workflow()
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
        $order = \App\Models\SalesOrder::where('po_number', 'PO-TATA-9988')->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(75000.00, $order->total_amount);

        // 2. Update Order Status
        $response = $this->actingAs($user)->patch(route('orders.updateStatus', $order->id), [
            'status' => 'in_production',
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals('in_production', $order->fresh()->status);

        // 3. Visit Invoice Page with order_id prefill
        $response = $this->actingAs($user)->get(route('invoices', ['order_id' => $order->id]));
        $response->assertStatus(200);

        // 4. Generate Invoice prefilled from Sales Order
        $response = $this->actingAs($user)->post(route('invoice.generate'), [
            'invoice_number' => 'PWW-TEST-ORD-01',
            'plant_id' => $plant->id,
            'sales_order_id' => $order->id,
            'product_ids' => [$product->id],
            'quantities' => [10],
            'unit_prices' => [7500.00],
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals('dispatched', $order->fresh()->status);

        // 5. Delete Order
        $response = $this->actingAs($user)->delete(route('orders.delete', $order->id));
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNull(\App\Models\SalesOrder::find($order->id));
    }

    /**
     * Test Employee CRUD Operations (Create, Update, Delete).
     */
    public function test_employee_crud_operations()
    {
        $user = User::factory()->create();

        // 1. Store Employee
        $response = $this->actingAs($user)->post(route('employees.store'), [
            'full_name' => 'Ramesh Kumar',
            'wage_type' => 'per-day',
            'piece_rate_per_unit' => 600.00,
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        $staffId = $response->json('data.id');

        // 2. Update Employee
        $response = $this->actingAs($user)->put(route('employees.update', $staffId), [
            'full_name' => 'Ramesh Kumar Updated',
            'wage_type' => 'fixed',
            'monthly_salary' => 25000.00,
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals('Ramesh Kumar Updated', StaffProfile::find($staffId)->full_name);
        $this->assertEquals('fixed', StaffProfile::find($staffId)->wage_type);

        // 3. Delete Employee
        $response = $this->actingAs($user)->delete(route('employees.delete', $staffId));
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNull(StaffProfile::find($staffId));
    }
}
