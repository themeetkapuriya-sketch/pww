<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use App\Services\RolePermissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with core settings and Super Admin account.
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
        Setting::updateOrCreate(['key' => 'simplified_billing_mode'], ['value' => 'false']);
        Setting::updateOrCreate(['key' => 'auto_backup_enabled'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'auto_email_backup'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'auto_backup_frequency'], ['value' => 'monthly']);
        Setting::updateOrCreate(['key' => 'auto_backup_time'], ['value' => '18:00']);
        Setting::updateOrCreate(['key' => 'auto_backup_day'], ['value' => 'Wednesday']);
        Setting::updateOrCreate(['key' => 'auto_backup_retention'], ['value' => '3_months']);

        Setting::updateOrCreate(
            ['key' => 'terms_and_conditions'],
            ['value' => "1. All disputes are subject to Rajkot jurisdiction.\r\n2. Interest @18% p.a. charged on overdue payments after due date.\r\n3. Goods once dispatched/sold cannot be returned or exchanged."]
        );
        Setting::updateOrCreate(['key' => 'logo_path'], ['value' => 'logo.jpg']);
        Setting::updateOrCreate(['key' => 'signature_path'], ['value' => 'uploads/signature_1785313553.png']);

        // Default Active Module & Feature Controls
        Setting::updateOrCreate(['key' => 'module_invoices'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_orders'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_purchases'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_clients'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_expenses'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_production'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_bom'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_inventory'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_payroll'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_reports'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_backups'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'module_activity_logs'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'track_stock'], ['value' => 'true']);
        Setting::updateOrCreate(['key' => 'track_payments'], ['value' => 'true']);

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
    }
}
