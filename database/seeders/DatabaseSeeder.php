<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Company Profile Settings (Latest Production Values)
        Setting::updateOrCreate(['key' => 'business_name'], ['value' => 'Praful Welding Works']);
        Setting::updateOrCreate(['key' => 'business_subtitle'], ['value' => 'Heavy Fabrication & Industrial Racks ERP']);
        Setting::updateOrCreate(['key' => 'address'], ['value' => 'VILLAGE : KHORANA TA : RAJKOT DI : RAJKOT - 360 003']);
        Setting::updateOrCreate(['key' => 'address_line_1'], ['value' => 'VILLAGE : KHORANA TA : RAJKOT DI : RAJKOT - 360 003']);
        Setting::updateOrCreate(['key' => 'address_line_2'], ['value' => '']);
        Setting::updateOrCreate(['key' => 'business_email'], ['value' => 'vekariyah@gmail.com']);
        Setting::updateOrCreate(['key' => 'business_mobile'], ['value' => '9409604420']);
        Setting::updateOrCreate(['key' => 'gstin'], ['value' => '24AFHPV5264M1ZU']);
        Setting::updateOrCreate(['key' => 'msme_number'], ['value' => 'UDYAM-GJ-20-0177569']);
        Setting::updateOrCreate(['key' => 'bank_name'], ['value' => 'JIVAN COMMERCIAL CO OP BANK LTD']);
        Setting::updateOrCreate(['key' => 'bank_account_name'], ['value' => 'Praful Welding Works']);
        Setting::updateOrCreate(['key' => 'bank_account_no'], ['value' => '443005101001972']);
        Setting::updateOrCreate(['key' => 'bank_ifsc'], ['value' => 'IBKL0JIVAN3']);
        Setting::updateOrCreate(['key' => 'invoice_prefix'], ['value' => 'PWW-']);
        Setting::updateOrCreate(['key' => 'order_prefix'], ['value' => 'PWW-ORD-']);
        Setting::updateOrCreate(
            ['key' => 'terms_and_conditions'],
            ['value' => "1. All disputes are subject to Rajkot jurisdiction.\r\n2. Interest @18% p.a. charged on overdue payments after due date.\r\n3. Goods once dispatched/sold cannot be returned or exchanged."]
        );
        Setting::updateOrCreate(['key' => 'logo_path'], ['value' => 'logo.jpg']);
        Setting::updateOrCreate(['key' => 'signature_path'], ['value' => 'uploads/signature_1785313553.png']);

        // 2. Admin Account Initialization
        User::firstOrCreate(
            ['email' => 'pww@example.com'],
            [
                'name' => 'hardik vekariya',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }
}
