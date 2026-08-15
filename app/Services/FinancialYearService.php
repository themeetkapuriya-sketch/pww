<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Throwable;

class FinancialYearService
{
    /**
     * Get the Financial Year string (e.g. "2025-26") for a given date.
     */
    public static function getFinancialYearForDate(string|Carbon|null $date = null): string
    {
        $carbonDate = $date ? Carbon::parse($date) : Carbon::now();
        $startMonth = (int) Setting::get('financial_year_start_month', '4');

        $year = $carbonDate->year;
        if ($startMonth === 4) {
            // April - March
            if ($carbonDate->month < 4) {
                $year--;
            }
            $nextYearShort = substr((string) ($year + 1), -2);

            return "{$year}-{$nextYearShort}";
        }

        // Calendar Year (Jan - Dec)
        return (string) $year;
    }

    /**
     * Check if a given date or financial year key is locked.
     */
    public static function isFinancialYearLocked(string|Carbon|null $dateOrYear): bool
    {
        if (empty($dateOrYear)) {
            return false;
        }

        $fyKey = str_contains((string) $dateOrYear, '-') && strlen((string) $dateOrYear) <= 7 && ! str_contains((string) $dateOrYear, ' ') && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $dateOrYear)
            ? (string) $dateOrYear
            : self::getFinancialYearForDate($dateOrYear);

        $lockedYears = self::getLockedFinancialYears();

        return in_array($fyKey, $lockedYears, true);
    }

    /**
     * Get list of locked financial year keys.
     *
     * @return array<string>
     */
    public static function getLockedFinancialYears(): array
    {
        $raw = Setting::get('locked_financial_years', '[]');
        if (is_array($raw)) {
            return $raw;
        }

        try {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Get a formatted list of financial years with lock statuses (starting from base year 2026).
     */
    public static function getFinancialYearsList(int $yearsCount = 5, int $minStartYear = 2026): array
    {
        $currentDate = Carbon::now();
        $startMonth = (int) Setting::get('financial_year_start_month', '4');
        $currentStartYear = $currentDate->year;

        if ($startMonth === 4 && $currentDate->month < 4) {
            $currentStartYear--;
        }

        $lockedYears = self::getLockedFinancialYears();
        $list = [];

        for ($i = 0; $i < $yearsCount; $i++) {
            $y1 = $currentStartYear - $i;
            if ($y1 < $minStartYear) {
                break;
            }

            if ($startMonth === 4) {
                $y2 = substr((string) ($y1 + 1), -2);
                $key = "{$y1}-{$y2}";
                $label = "FY {$y1}–{$y2} (Apr 1, {$y1} – Mar 31, ".($y1 + 1).')';
                $isCurrent = ($i === 0);
            } else {
                $key = (string) $y1;
                $label = "CY {$y1} (Jan 1, {$y1} – Dec 31, {$y1})";
                $isCurrent = ($i === 0);
            }

            $list[] = [
                'key' => $key,
                'label' => $label,
                'is_locked' => in_array($key, $lockedYears, true),
                'is_current' => $isCurrent,
            ];
        }

        return $list;
    }

    /**
     * Lock a financial year.
     */
    public static function lockFinancialYear(string $yearKey): bool
    {
        $locked = self::getLockedFinancialYears();
        if (! in_array($yearKey, $locked, true)) {
            $locked[] = $yearKey;
            Setting::set('locked_financial_years', json_encode(array_values($locked)));
        }

        return true;
    }

    /**
     * Unlock a financial year.
     */
    public static function unlockFinancialYear(string $yearKey): bool
    {
        $locked = self::getLockedFinancialYears();
        $locked = array_values(array_filter($locked, fn ($y) => $y !== $yearKey));
        Setting::set('locked_financial_years', json_encode($locked));

        return true;
    }
}
