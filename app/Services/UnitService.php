<?php

namespace App\Services;

use App\Models\Setting;

class UnitService
{
    /**
     * Get all active measurement units.
     */
    public static function getUnits(): array
    {
        $json = Setting::get('measurement_units');
        if ($json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }

        return self::getDefaultUnits();
    }

    /**
     * Default pre-configured measurement units.
     */
    public static function getDefaultUnits(): array
    {
        return [
            [
                'key' => 'pcs',
                'name' => 'Pieces',
                'symbol' => 'pcs',
                'uqc' => 'NOS',
                'type' => 'count',
                'precision' => 0,
                'protected' => true,
            ],
            [
                'key' => 'nos',
                'name' => 'Numbers',
                'symbol' => 'nos',
                'uqc' => 'NOS',
                'type' => 'count',
                'precision' => 0,
                'protected' => false,
            ],
            [
                'key' => 'kg',
                'name' => 'Kilograms',
                'symbol' => 'kg',
                'uqc' => 'KGS',
                'type' => 'weight',
                'precision' => 4,
                'protected' => true,
            ],
            [
                'key' => 'gram',
                'name' => 'Grams',
                'symbol' => 'g',
                'uqc' => 'GMS',
                'type' => 'weight',
                'precision' => 2,
                'protected' => false,
            ],
            [
                'key' => 'tonne',
                'name' => 'Metric Tonne',
                'symbol' => 'MT',
                'uqc' => 'TON',
                'type' => 'weight',
                'precision' => 3,
                'protected' => false,
            ],
            [
                'key' => 'meter',
                'name' => 'Meters',
                'symbol' => 'mtr',
                'uqc' => 'MTR',
                'type' => 'length',
                'precision' => 2,
                'protected' => false,
            ],
            [
                'key' => 'feet',
                'name' => 'Feet',
                'symbol' => 'ft',
                'uqc' => 'FTK',
                'type' => 'length',
                'precision' => 2,
                'protected' => false,
            ],
            [
                'key' => 'sq_ft',
                'name' => 'Square Feet',
                'symbol' => 'sq ft',
                'uqc' => 'SQF',
                'type' => 'area',
                'precision' => 2,
                'protected' => false,
            ],
            [
                'key' => 'liter',
                'name' => 'Liters',
                'symbol' => 'ltr',
                'uqc' => 'LTR',
                'type' => 'volume',
                'precision' => 3,
                'protected' => false,
            ],
            [
                'key' => 'box',
                'name' => 'Boxes',
                'symbol' => 'box',
                'uqc' => 'BOX',
                'type' => 'packaging',
                'precision' => 0,
                'protected' => false,
            ],
            [
                'key' => 'bundle',
                'name' => 'Bundles',
                'symbol' => 'bdl',
                'uqc' => 'BND',
                'type' => 'packaging',
                'precision' => 0,
                'protected' => false,
            ],
            [
                'key' => 'roll',
                'name' => 'Rolls / Coils',
                'symbol' => 'roll',
                'uqc' => 'ROL',
                'type' => 'packaging',
                'precision' => 2,
                'protected' => false,
            ],
            [
                'key' => 'packet',
                'name' => 'Packets',
                'symbol' => 'pkt',
                'uqc' => 'PAC',
                'type' => 'packaging',
                'precision' => 0,
                'protected' => false,
            ],
            [
                'key' => 'set',
                'name' => 'Sets',
                'symbol' => 'set',
                'uqc' => 'SET',
                'type' => 'count',
                'precision' => 0,
                'protected' => false,
            ],
            [
                'key' => 'pair',
                'name' => 'Pairs',
                'symbol' => 'pair',
                'uqc' => 'PRS',
                'type' => 'count',
                'precision' => 0,
                'protected' => false,
            ],
        ];
    }

    /**
     * Get units formatted for Combobox dropdown options.
     */
    public static function getComboboxOptions(): array
    {
        $units = self::getUnits();
        $options = [];

        foreach ($units as $u) {
            $options[] = [
                'value' => $u['symbol'] ?? $u['key'],
                'label' => "{$u['name']} ({$u['symbol']})",
                'search' => strtolower("{$u['name']} {$u['symbol']} {$u['key']} {$u['uqc']}"),
                'precision' => (int) ($u['precision'] ?? 2),
                'type' => $u['type'] ?? 'count',
            ];
        }

        return $options;
    }

    /**
     * Save all units to system settings.
     */
    public static function saveUnits(array $units): void
    {
        Setting::set('measurement_units', json_encode(array_values($units)));
    }

    /**
     * Translate any unit string to its official GST Unique Quantity Code (UQC).
     */
    public static function mapToUqc(?string $unit): string
    {
        if (empty($unit)) {
            return 'NOS';
        }

        $clean = strtolower(trim($unit));
        $units = self::getUnits();

        foreach ($units as $u) {
            if (
                strtolower($u['key']) === $clean ||
                strtolower($u['symbol']) === $clean ||
                strtolower($u['name']) === $clean
            ) {
                return strtoupper($u['uqc'] ?? 'NOS');
            }
        }

        // Fallbacks for common abbreviations
        return match ($clean) {
            'kg', 'kgs', 'kilogram', 'kilograms' => 'KGS',
            'g', 'gm', 'gms', 'gram', 'grams' => 'GMS',
            'ton', 'tonne', 'mt' => 'TON',
            'pcs', 'piece', 'pieces', 'pc', 'nos', 'no', 'number', 'numbers', 'item', 'items' => 'NOS',
            'ltr', 'liter', 'liters', 'litre', 'litres', 'l' => 'LTR',
            'mtr', 'meter', 'meters', 'm' => 'MTR',
            'ft', 'feet', 'foot' => 'FTK',
            'sq ft', 'sqft', 'sq.ft', 'square feet' => 'SQF',
            'box', 'boxes', 'ctn', 'carton' => 'BOX',
            'bdl', 'bundle', 'bundles' => 'BND',
            'roll', 'rolls', 'coil', 'coils' => 'ROL',
            'pkt', 'packet', 'packets', 'pack', 'packs' => 'PAC',
            'set', 'sets' => 'SET',
            'pair', 'pairs', 'pr' => 'PRS',
            'bag', 'bags' => 'BAG',
            'can', 'cans' => 'CAN',
            'drum', 'drums' => 'DRM',
            default => 'NOS',
        };
    }
}
