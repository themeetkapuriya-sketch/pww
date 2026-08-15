<?php

namespace App\Services;

use App\Models\Setting;

class CategoryService
{
    /**
     * Get Purchase Categories formatted for Combobox.
     */
    public static function getPurchaseCategories(): array
    {
        $json = Setting::get('purchase_categories');
        if ($json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }

        return [
            ['key' => 'raw_material', 'label' => 'Raw Material Purchase (Auto-Restocks Inventory)', 'protected' => true],
            ['key' => 'office_assets', 'label' => 'Office Assets & Electronics (Mobiles, Laptops, CCTV)', 'protected' => false],
            ['key' => 'machinery', 'label' => 'Machinery & Capital Equipment', 'protected' => false],
            ['key' => 'factory_spares', 'label' => 'Machinery Spare Parts', 'protected' => false],
            ['key' => 'supplies', 'label' => 'Factory Consumables & Tools', 'protected' => false],
            ['key' => 'vehicle_transport', 'label' => 'Vehicle & Freight Expenses (Transport/Fuel)', 'protected' => false],
            ['key' => 'others', 'label' => 'Other Purchases / Miscellaneous', 'protected' => false],
        ];
    }

    /**
     * Get Combobox options for Purchase Categories.
     */
    public static function getPurchaseComboboxOptions(): array
    {
        $cats = self::getPurchaseCategories();
        $options = [];
        foreach ($cats as $cat) {
            $options[] = [
                'value' => $cat['key'],
                'label' => $cat['label'],
                'search' => strtolower($cat['label'].' '.$cat['key']),
            ];
        }

        return $options;
    }

    /**
     * Save Purchase Categories.
     */
    public static function savePurchaseCategories(array $categories): void
    {
        Setting::set('purchase_categories', json_encode(array_values($categories)));
    }

    /**
     * Get Expense Categories.
     */
    public static function getExpenseCategories(): array
    {
        $json = Setting::get('expense_categories');
        if ($json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }

        return [
            ['key' => 'factory_electricity', 'label' => 'Factory Electricity', 'protected' => false],
            ['key' => 'industrial_gas', 'label' => 'Industrial Gas / Consumables', 'protected' => false],
            ['key' => 'welding_consumables', 'label' => 'Welding Consumables', 'protected' => false],
            ['key' => 'freight_transport', 'label' => 'Freight & Transport Charges', 'protected' => false],
            ['key' => 'salary', 'label' => 'Salary / Wages', 'protected' => true],
            ['key' => 'gst_payment', 'label' => 'GST Payment / Tax Payment', 'protected' => true],
            ['key' => 'administrative', 'label' => 'Administrative Expenses', 'protected' => false],
            ['key' => 'machinery_depreciation', 'label' => 'Machinery Depreciation Schedule', 'protected' => false],
            ['key' => 'others', 'label' => 'Other Expenses / Miscellaneous', 'protected' => false],
        ];
    }

    /**
     * Get Combobox options for Expense Categories.
     */
    public static function getExpenseComboboxOptions(): array
    {
        $cats = self::getExpenseCategories();
        $options = [];
        foreach ($cats as $cat) {
            $options[] = [
                'value' => $cat['key'],
                'label' => $cat['label'],
                'search' => strtolower($cat['label'].' '.$cat['key']),
            ];
        }

        return $options;
    }

    /**
     * Save Expense Categories.
     */
    public static function saveExpenseCategories(array $categories): void
    {
        Setting::set('expense_categories', json_encode(array_values($categories)));
    }

    /**
     * Get Raw Material Categories.
     */
    public static function getMaterialCategories(): array
    {
        $json = Setting::get('material_categories');
        if ($json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }

        return [
            ['key' => 'pipes', 'label' => 'Pipes & Tubes', 'icon' => '🔩', 'color' => 'blue', 'protected' => false],
            ['key' => 'powders', 'label' => 'Powder Coating Powders', 'icon' => '🎨', 'color' => 'purple', 'protected' => false],
            ['key' => 'sheets', 'label' => 'Sheet Metal & Plates', 'icon' => '📐', 'color' => 'indigo', 'protected' => false],
            ['key' => 'welding', 'label' => 'Welding Wire, Rods & Gas', 'icon' => '⚡', 'color' => 'amber', 'protected' => false],
            ['key' => 'hardware', 'label' => 'Fasteners & Hardware', 'icon' => '🔧', 'color' => 'teal', 'protected' => false],
            ['key' => 'other', 'label' => 'Other Consumables', 'icon' => '📦', 'color' => 'slate', 'protected' => false],
        ];
    }

    /**
     * Get Combobox options for Material Categories.
     */
    public static function getMaterialComboboxOptions(): array
    {
        $cats = self::getMaterialCategories();
        $options = [];
        foreach ($cats as $cat) {
            $icon = $cat['icon'] ?? '📦';
            $options[] = [
                'value' => $cat['key'],
                'label' => "{$icon} {$cat['label']}",
                'search' => strtolower($cat['label'].' '.$cat['key']),
            ];
        }

        return $options;
    }

    /**
     * Save Material Categories.
     */
    public static function saveMaterialCategories(array $categories): void
    {
        Setting::set('material_categories', json_encode(array_values($categories)));
    }
}
