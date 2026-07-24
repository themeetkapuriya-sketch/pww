<?php

namespace Database\Seeders;

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
use App\Models\ProductionLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed default business settings
        \App\Models\Setting::updateOrCreate(['key' => 'business_name'], ['value' => 'Praful Welding Works']);
        \App\Models\Setting::updateOrCreate(['key' => 'business_subtitle'], ['value' => 'Heavy Fabrication & Industrial Racks ERP']);
        \App\Models\Setting::updateOrCreate(['key' => 'address_line_1'], ['value' => 'Plot No. 12, G.I.D.C. Metoda,']);
        \App\Models\Setting::updateOrCreate(['key' => 'address_line_2'], ['value' => 'Rajkot, Gujarat - 360021']);
        \App\Models\Setting::updateOrCreate(['key' => 'gstin'], ['value' => '24PWWRK1234A1Z0']);
        \App\Models\Setting::updateOrCreate(['key' => 'msme_number'], ['value' => 'UDYAM-GJ-24-0012345']);
        \App\Models\Setting::updateOrCreate(['key' => 'logo_path'], ['value' => 'logo.jpg']);

        // Prevent duplicate seeding if data already exists
        if (User::where('email', 'pww@example.com')->exists()) {
            return;
        }

        // 1. Create Core Single User
        $adminUser = User::create([
            'name' => 'hardik  vekariya',
            'email' => 'pww@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // 2. Create Raw Materials
        $ironWire = RawMaterial::create([
            'material_name' => 'Iron Wire Coils (5mm)',
            'unit' => 'kg',
            'current_stock' => 12500.0000,
            'safety_threshold' => 2000.0000,
            'average_purchase_price' => 82.50,
        ]);

        $powderPaint = RawMaterial::create([
            'material_name' => 'Powder Paint (Industrial Blue)',
            'unit' => 'kg',
            'current_stock' => 650.0000,
            'safety_threshold' => 150.0000,
            'average_purchase_price' => 310.00,
        ]);

        $weldingRods = RawMaterial::create([
            'material_name' => 'Welding Consumables (Rods)',
            'unit' => 'packs',
            'current_stock' => 85.0000,
            'safety_threshold' => 15.0000,
            'average_purchase_price' => 450.00,
        ]);

        $co2Gas = RawMaterial::create([
            'material_name' => 'CO2 Shielding Gas',
            'unit' => 'liters',
            'current_stock' => 420.0000,
            'safety_threshold' => 80.0000,
            'average_purchase_price' => 120.00,
        ]);

        // 3. Create Finished Goods (Products)
        $rack3Tier = Product::create([
            'product_name' => 'Balaji Wire Rack 3-Tier',
            'sku' => 'WR-3T-BALAJI',
            'current_stock' => 150,
            'selling_price' => 1850.00,
            'alerts_enabled' => true,
        ]);

        $rack4Tier = Product::create([
            'product_name' => 'Balaji Wire Rack 4-Tier',
            'sku' => 'WR-4T-BALAJI',
            'current_stock' => 90,
            'selling_price' => 2400.00,
            'alerts_enabled' => true,
        ]);

        // 4. Create BOM
        // Rack 3-Tier requires 4.5kg iron, 0.3kg paint, 0.5l gas
        BillOfMaterial::create([
            'product_id' => $rack3Tier->id,
            'raw_material_id' => $ironWire->id,
            'required_quantity' => 4.5000,
            'waste_percentage' => 5.00,
        ]);
        BillOfMaterial::create([
            'product_id' => $rack3Tier->id,
            'raw_material_id' => $powderPaint->id,
            'required_quantity' => 0.3000,
            'waste_percentage' => 10.00,
        ]);
        BillOfMaterial::create([
            'product_id' => $rack3Tier->id,
            'raw_material_id' => $co2Gas->id,
            'required_quantity' => 0.5000,
            'waste_percentage' => 2.00,
        ]);

        // Rack 4-Tier requires 6kg iron, 0.4kg paint, 0.7l gas
        BillOfMaterial::create([
            'product_id' => $rack4Tier->id,
            'raw_material_id' => $ironWire->id,
            'required_quantity' => 6.0000,
            'waste_percentage' => 6.00,
        ]);
        BillOfMaterial::create([
            'product_id' => $rack4Tier->id,
            'raw_material_id' => $powderPaint->id,
            'required_quantity' => 0.4000,
            'waste_percentage' => 10.00,
        ]);
        BillOfMaterial::create([
            'product_id' => $rack4Tier->id,
            'raw_material_id' => $co2Gas->id,
            'required_quantity' => 0.7000,
            'waste_percentage' => 2.00,
        ]);

        // 5. Create B2B Client & Plants
        $client = Client::create([
            'company_name' => 'Balaji Wafers HQ',
            'client_email' => 'themeetkapuriya@gmail.com',
            'gst_number' => '24AAACB1234A1Z9',
            'corporate_address' => 'Vajdi GIDC, Kalawad Road, Rajkot - 360021, Gujarat, India',
        ]);

        $plantRajkot = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Rajkot Plant',
            'shipping_address' => 'Plot 22-25, Sector 3, Metoda GIDC, Rajkot, Gujarat',
            'state' => 'Gujarat',
        ]);

        $plantValsad = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Valsad Plant',
            'shipping_address' => 'Survey No. 412/1, Gundlav GIDC, Valsad, Gujarat',
            'state' => 'Gujarat',
        ]);

        $plantIndore = ClientPlant::create([
            'client_id' => $client->id,
            'plant_name' => 'Indore Plant',
            'shipping_address' => 'Sector I, Pithampur Industrial Area, Indore, Madhya Pradesh',
            'state' => 'Madhya Pradesh',
        ]);

        // 6. Create Staff Profiles
        $staff1 = StaffProfile::create([
            'user_id' => null,
            'full_name' => 'Amit Sharma',
            'wage_type' => 'per-day',
            'piece_rate_per_unit' => 500.00,
        ]);

        $staff2 = StaffProfile::create([
            'user_id' => null,
            'full_name' => 'Rajesh Patel',
            'wage_type' => 'per-day',
            'piece_rate_per_unit' => 500.00,
        ]);

        $staff3 = StaffProfile::create([
            'user_id' => null,
            'full_name' => 'Sunita Rao',
            'wage_type' => 'fixed',
            'monthly_salary' => 22000.00,
        ]);

        // 7. Seed Operational Expenses over past 6 months
        $categories = [
            'factory_electricity', 'industrial_gas', 'welding_consumables',
            'freight_transport', 'office_rent', 'administrative', 'machinery_depreciation'
        ];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i)->day(rand(1, 28));

            Expense::create([
                'expense_category' => 'factory_electricity',
                'amount' => rand(15000, 22000),
                'expense_date' => $date->toDateString(),
                'description' => 'Monthly Factory Power Bill - PGECL',
            ]);

            Expense::create([
                'expense_category' => 'industrial_gas',
                'amount' => rand(8000, 14000),
                'expense_date' => $date->copy()->addDays(5)->toDateString(),
                'description' => 'Argon/CO2 Cylinder Refills',
            ]);

            Expense::create([
                'expense_category' => 'office_rent',
                'amount' => 12000.00,
                'expense_date' => $date->copy()->startOfMonth()->toDateString(),
                'description' => 'Factory shed monthly lease',
            ]);

            Expense::create([
                'expense_category' => 'machinery_depreciation',
                'amount' => 4500.00,
                'expense_date' => $date->copy()->endOfMonth()->toDateString(),
                'description' => 'Calculated monthly wear on welding stations & cutters',
            ]);

            Expense::create([
                'expense_category' => 'administrative',
                'amount' => rand(3000, 6000),
                'expense_date' => $date->toDateString(),
                'description' => 'Office consumables & refreshments',
            ]);

            // Add dedicated freight expenses for Balaji Wafers plants (helps in the Sales-Freight matrix)
            Expense::create([
                'expense_category' => 'freight_transport',
                'amount' => rand(4000, 6000),
                'expense_date' => $date->copy()->addDays(2)->toDateString(),
                'description' => "Freight Charges to {$plantRajkot->plant_name}",
            ]);
            Expense::create([
                'expense_category' => 'freight_transport',
                'amount' => rand(6500, 9500),
                'expense_date' => $date->copy()->addDays(12)->toDateString(),
                'description' => "Freight Charges to {$plantValsad->plant_name}",
            ]);
            Expense::create([
                'expense_category' => 'freight_transport',
                'amount' => rand(11000, 15000),
                'expense_date' => $date->copy()->addDays(22)->toDateString(),
                'description' => "Freight Charges to {$plantIndore->plant_name}",
            ]);
        }

        // 8. Log Historical Production & Labor over the past 3 months
        for ($i = 2; $i >= 0; $i--) {
            $prodDate = Carbon::now()->subMonths($i)->subDays(15);
            
            // Log 3-tier production run
            $quantity3T = rand(80, 120);
            $rejected3T = rand(2, 6);
            $prodLog3T = ProductionLog::create([
                'product_id' => $rack3Tier->id,
                'quantity_manufactured' => $quantity3T,
                'quantity_rejected' => $rejected3T,
                'recorded_by' => $adminUser->id,
                'production_date' => $prodDate->toDateString(),
                'created_at' => $prodDate,
            ]);

            // Staff labor logs for this run
            LaborLog::create([
                'staff_profile_id' => $staff1->id,
                'production_log_id' => $prodLog3T->id,
                'units_completed' => ceil($quantity3T / 2),
                'calculated_payout' => ceil($quantity3T / 2) * $staff1->piece_rate_per_unit,
                'status' => 'paid',
                'created_at' => $prodDate,
            ]);
            LaborLog::create([
                'staff_profile_id' => $staff2->id,
                'production_log_id' => $prodLog3T->id,
                'units_completed' => floor($quantity3T / 2),
                'calculated_payout' => floor($quantity3T / 2) * $staff2->piece_rate_per_unit,
                'status' => 'paid',
                'created_at' => $prodDate,
            ]);

            // Log 4-tier production run
            $quantity4T = rand(50, 80);
            $rejected4T = rand(1, 4);
            $prodLog4T = ProductionLog::create([
                'product_id' => $rack4Tier->id,
                'quantity_manufactured' => $quantity4T,
                'quantity_rejected' => $rejected4T,
                'recorded_by' => $adminUser->id,
                'production_date' => $prodDate->copy()->addDays(5)->toDateString(),
                'created_at' => $prodDate->copy()->addDays(5),
            ]);

            // Staff labor logs for 4-tier
            LaborLog::create([
                'staff_profile_id' => $staff1->id,
                'production_log_id' => $prodLog4T->id,
                'units_completed' => ceil($quantity4T / 2),
                'calculated_payout' => ceil($quantity4T / 2) * $staff1->piece_rate_per_unit,
                'status' => 'paid',
                'created_at' => $prodDate->copy()->addDays(5),
            ]);
            LaborLog::create([
                'staff_profile_id' => $staff2->id,
                'production_log_id' => $prodLog4T->id,
                'units_completed' => floor($quantity4T / 2),
                'calculated_payout' => floor($quantity4T / 2) * $staff2->piece_rate_per_unit,
                'status' => 'paid',
                'created_at' => $prodDate->copy()->addDays(5),
            ]);
        }

        // 9. Generate Historical Invoices
        $plantsArray = [$plantRajkot, $plantValsad, $plantIndore];
        
        foreach ($plantsArray as $index => $plant) {
            $dispatchDate = Carbon::now()->subDays(20 - ($index * 5));

            $qty3T = rand(20, 40);
            $qty4T = rand(15, 30);
            
            $taxable = ($qty3T * $rack3Tier->selling_price) + ($qty4T * $rack4Tier->selling_price);
            $cgst = 0.00;
            $sgst = 0.00;
            $igst = 0.00;

            if ($plant->state === 'Gujarat') {
                $cgst = round($taxable * 0.09, 2);
                $sgst = round($taxable * 0.09, 2);
            } else {
                $igst = round($taxable * 0.18, 2);
            }

            $total = $taxable + $cgst + $sgst + $igst;
            $invNo = 'PWW-' . $dispatchDate->format('Ymd') . '-00' . ($index + 1);
            
            $invoice = Invoice::create([
                'plant_id' => $plant->id,
                'invoice_number' => $invNo,
                'invoice_date' => $dispatchDate->toDateString(),
                'total_taxable_value' => $taxable,
                'cgst' => $cgst,
                'sgst' => $sgst,
                'igst' => $igst,
                'total_amount' => $total,
                'payment_status' => ($index === 0) ? 'paid' : (($index === 1) ? 'partially_paid' : 'unpaid'),
                'paid_amount' => ($index === 0) ? $total : (($index === 1) ? round($total / 2, 2) : 0.00),
                'due_date' => $dispatchDate->copy()->addDays(30)->toDateString(),
                'created_at' => $dispatchDate,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $rack3Tier->id,
                'quantity' => $qty3T,
                'unit_price' => $rack3Tier->selling_price,
                'total_price' => round($qty3T * $rack3Tier->selling_price, 2),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $rack4Tier->id,
                'quantity' => $qty4T,
                'unit_price' => $rack4Tier->selling_price,
                'total_price' => round($qty4T * $rack4Tier->selling_price, 2),
            ]);
        }

        // 11. Create a few pending labor log entries for wage compilation test
        $pendingProdLog = ProductionLog::create([
            'product_id' => $rack3Tier->id,
            'quantity_manufactured' => 60,
            'quantity_rejected' => 1,
            'recorded_by' => $adminUser->id,
            'production_date' => Carbon::now()->subDays(1)->toDateString(),
        ]);

        LaborLog::create([
            'staff_profile_id' => $staff1->id,
            'production_log_id' => $pendingProdLog->id,
            'units_completed' => 30,
            'calculated_payout' => 30 * $staff1->piece_rate_per_unit,
            'status' => 'pending',
        ]);

        LaborLog::create([
            'staff_profile_id' => $staff2->id,
            'production_log_id' => $pendingProdLog->id,
            'units_completed' => 30,
            'calculated_payout' => 30 * $staff2->piece_rate_per_unit,
            'status' => 'pending',
        ]);

        // 12. Create Sample Sales Orders
        $sampleOrder = \App\Models\SalesOrder::create([
            'order_number' => \App\Models\SalesOrder::generateNextOrderNumber(),
            'po_number' => 'PO-BALAJI-9012',
            'client_id' => $client->id,
            'plant_id' => $plantRajkot->id,
            'order_date' => Carbon::now()->subDays(2)->toDateString(),
            'delivery_date' => Carbon::now()->addDays(5)->toDateString(),
            'status' => 'pending',
            'total_amount' => 125000.00,
            'notes' => 'Heavy-duty 3-Tier Racks with Blue Powder Coating',
        ]);

        \App\Models\SalesOrderItem::create([
            'sales_order_id' => $sampleOrder->id,
            'product_id' => $rack3Tier->id,
            'quantity' => 25,
            'unit_price' => 5000.00,
            'total_price' => 125000.00,
        ]);
    }
}

