<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key.
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        return ($setting !== null && $setting->value !== null) ? $setting->value : $default;
    }

    /**
     * Set/Update a setting value by key.
     */
    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Check if Stock Management is enabled.
     */
    public static function isStockEnabled(): bool
    {
        // If Simplified Billing Mode is ON, Stock Management is strictly OFF
        if (in_array(strtolower((string) self::get('simplified_billing_mode', 'false')), ['true', '1', 'yes', 'on'], true)) {
            return false;
        }

        $val = self::get('track_stock', 'true');

        return in_array(strtolower((string) $val), ['true', '1', 'yes', 'on'], true);
    }

    /**
     * Format a document number with configurable date middle portion and digit padding.
     */
    public static function formatDocumentNumber(string $prefix, int $sequenceNumber): string
    {
        $dateFormat = self::get('serial_date_format', 'Ymd'); // Ymd, Ym, ym, FY, none
        $digits = (int) self::get('serial_number_digits', 4); // 3, 4, 5, 6, 1

        $datePart = '';
        $now = Carbon::now();

        if ($dateFormat === 'Ymd') {
            $datePart = $now->format('Ymd');
        } elseif ($dateFormat === 'Ym') {
            $datePart = $now->format('Ym');
        } elseif ($dateFormat === 'ym') {
            $datePart = $now->format('ym');
        } elseif ($dateFormat === 'FY') {
            $year = $now->year;
            if ($now->month >= 4) {
                $fyStart = substr((string) $year, -2);
                $fyEnd = substr((string) ($year + 1), -2);
            } else {
                $fyStart = substr((string) ($year - 1), -2);
                $fyEnd = substr((string) $year, -2);
            }
            $datePart = $fyStart.$fyEnd;
        }

        $paddedSeq = str_pad((string) $sequenceNumber, $digits, '0', STR_PAD_LEFT);

        if (! empty($datePart)) {
            if (empty($prefix)) {
                return $datePart.'-'.$paddedSeq;
            }
            $separator = (str_ends_with($prefix, '-') || str_ends_with($prefix, '/')) ? '' : '-';

            return $prefix.$separator.$datePart.'-'.$paddedSeq;
        }

        return $prefix.$paddedSeq;
    }
}
