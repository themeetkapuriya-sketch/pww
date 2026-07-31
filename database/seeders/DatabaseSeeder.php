<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use App\Models\Product;
use App\Models\StaffProfile;
use App\Services\RolePermissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with complete settings, roles, users, and initial catalogs.
     */
    public function run(): void
    {
        // 1. Company Profile & System Settings
        Setting::updateOrCreate(['key' => 'business_name'], ['value' => 'Praful Welding Works']);
        Setting::updateOrCreate(['key' => 'business_subtitle'], ['value' => 'Heavy Fabrication & Industrial Racks ERP']);
        Setting::updateOrCreate(['key' => 'address'], ['value' => 'VILLAGE : KHORANA TA : RAJKOT DI : RAJKOT - 360 003']);
        Setting::updateOrCreate(['key' => 'address_line_1'], ['value' => 'VILLAGE : KHORANA TA : RAJKOT DI : RAJKOT - 360 003']);
        Setting::updateOrCreate(['key' => 'address_line_2'], ['value' => '']);
        Setting::updateOrCreate(['key' => 'business_email'], ['value' => 'pww@gmail.com']);
        Setting::updateOrCreate(['key' => 'business_mobile'], ['value' => '9409604420']);
        Setting::updateOrCreate(['key' => 'gstin'], ['value' => '24AFHPV5264M1ZU']);
        Setting::updateOrCreate(['key' => 'msme_number'], ['value' => 'UDYAM-GJ-20-0177569']);
        Setting::updateOrCreate(['key' => 'bank_name'], ['value' => 'JIVAN COMMERCIAL CO OP BANK LTD']);
        Setting::updateOrCreate(['key' => 'bank_account_name'], ['value' => 'Praful Welding Works']);
        Setting::updateOrCreate(['key' => 'bank_account_no'], ['value' => '443005101001972']);
        Setting::updateOrCreate(['key' => 'bank_ifsc'], ['value' => 'IBKL0JIVAN3']);
        Setting::updateOrCreate(['key' => 'invoice_prefix'], ['value' => 'PWW-']);
        Setting::updateOrCreate(['key' => 'order_prefix'], ['value' => 'PWW-ORD-']);
        Setting::updateOrCreate(['key' => 'invoice_next_sequence'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'order_next_sequence'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'serial_reset_frequency'], ['value' => 'financial_year']);

        Setting::updateOrCreate(['key' => 'default_gst_rate'], ['value' => '18']);
        Setting::updateOrCreate(['key' => 'financial_year_start_month'], ['value' => '4']);
        Setting::updateOrCreate(['key' => 'number_format_style'], ['value' => 'indian']);

        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => 'smtp.gmail.com']);
        Setting::updateOrCreate(['key' => 'mail_port'], ['value' => '587']);
        Setting::updateOrCreate(['key' => 'mail_username'], ['value' => '']);
        Setting::updateOrCreate(['key' => 'mail_password'], ['value' => '']);
        Setting::updateOrCreate(['key' => 'mail_encryption'], ['value' => 'tls']);
        Setting::updateOrCreate(['key' => 'mail_from_address'], ['value' => 'pww@gmail.com']);
        Setting::updateOrCreate(['key' => 'mail_from_name'], ['value' => 'Praful Welding Works']);

        Setting::updateOrCreate(['key' => 'session_timeout_minutes'], ['value' => '120']);
        Setting::updateOrCreate(['key' => 'auto_backup_enabled'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'auto_backup_frequency'], ['value' => 'monthly']);

        Setting::updateOrCreate(
            ['key' => 'terms_and_conditions'],
            ['value' => "1. All disputes are subject to Rajkot jurisdiction.\r\n2. Interest @18% p.a. charged on overdue payments after due date.\r\n3. Goods once dispatched/sold cannot be returned or exchanged."]
        );
        Setting::updateOrCreate(['key' => 'logo_path'], ['value' => 'logo.jpg']);
        Setting::updateOrCreate(['key' => 'signature_path'], ['value' => 'uploads/signature_1785313553.png']);

        // Default Active Module Controls
        Setting::updateOrCreate(['key' => 'module_invoices'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_orders'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_purchases'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_clients'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_expenses'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_production'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_bom'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_inventory'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_payroll'], ['value' => 'true']);

        // 2. Primary Super Admin Account Setup
        User::updateOrCreate(
            ['email' => 'pww@gmail.com'],
            [
                'name' => 'Hardik Vekariya',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'status' => 'active',
                'is_active' => true,
                'permissions' => RolePermissionService::getDefaultPermissionsForRole('super_admin'),
            ]
        );

        // 5. Initial Finished Goods Products Catalog
        Product::firstOrCreate(
            ['product_name' => 'Heavy Duty Industrial Storage Rack (2000x1000x500mm)'],
            [
                'hsn_code' => '7308',
                'unit' => 'NOS',
                'unit_price' => 4500.00,
                'gst_rate' => 18,
                'unit_weight_kg' => 42.50,
                'price_per_kg' => 105.88,
                'reorder_level' => 10,
                'current_stock' => 45,
            ]
        );

        Product::firstOrCreate(
            ['product_name' => 'Heavy Duty Steel Pallet (1200x1000mm)'],
            [
                'hsn_code' => '7308',
                'unit' => 'NOS',
                'unit_price' => 2800.00,
                'gst_rate' => 18,
                'unit_weight_kg' => 26.00,
                'price_per_kg' => 107.69,
                'reorder_level' => 15,
                'current_stock' => 60,
            ]
        );

        // 6. Initial Staff Profiles & Employees Catalog
        StaffProfile::firstOrCreate(
            ['worker_id' => 'EMP-1001'],
            [
                'name' => 'Rajesh Kumar',
                'phone' => '9876543210',
                'designation' => 'Senior Welder & Fabricator',
                'wage_type' => 'per-day',
                'daily_rate' => 750.00,
                'piece_rate_per_unit' => 45.00,
                'overtime_rate_per_hour' => 100.00,
                'joining_date' => '2024-01-15',
                'status' => 'active',
            ]
        );

        StaffProfile::firstOrCreate(
            ['worker_id' => 'EMP-1002'],
            [
                'name' => 'Suresh Patel',
                'phone' => '9876543211',
                'designation' => 'Assembly Helper',
                'wage_type' => 'per-day',
                'daily_rate' => 550.00,
                'piece_rate_per_unit' => 30.00,
                'overtime_rate_per_hour' => 75.00,
                'joining_date' => '2024-03-01',
                'status' => 'active',
            ]
        );
    }
}
