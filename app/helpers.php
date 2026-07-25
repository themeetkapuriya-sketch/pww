<?php

if (!function_exists('format_indian')) {
    /**
     * Format a number in Indian Numbering System (Lakhs and Crores).
     * e.g., 100000000 -> 10,00,00,000.00
     */
    function format_indian($amount, $decimals = 2) {
        if ($amount === null || $amount === '') {
            $amount = 0;
        }

        $isNegative = false;
        $floatVal = (float)$amount;
        if ($floatVal < 0) {
            $isNegative = true;
            $floatVal = abs($floatVal);
        }

        $parts = explode('.', sprintf('%.' . $decimals . 'f', $floatVal));
        $num = $parts[0];
        $dec = isset($parts[1]) && $decimals > 0 ? '.' . $parts[1] : '';

        $len = strlen($num);
        if ($len <= 3) {
            return ($isNegative ? '-' : '') . $num . $dec;
        }

        $lastThree = substr($num, -3);
        $rest = substr($num, 0, -3);

        $restFormatted = '';
        while (strlen($rest) > 2) {
            $restFormatted = ',' . substr($rest, -2) . $restFormatted;
            $rest = substr($rest, 0, -2);
        }
        $restFormatted = $rest . $restFormatted;

        return ($isNegative ? '-' : '') . $restFormatted . ',' . $lastThree . $dec;
    }
}

if (!function_exists('inr_format')) {
    function inr_format($amount, $decimals = 2) {
        return format_indian($amount, $decimals);
    }
}
