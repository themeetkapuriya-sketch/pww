<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ClientPlant;
use Illuminate\Support\Facades\DB;
use Exception;

class BillingService
{
    /**
     * Calculate GST breakdown for a plant and line items.
     *
     * @param int $plantId
     * @param array $items Array of ['product_id' => int, 'quantity' => float, 'unit_price' => float]
     * @return array
     */
    public function calculateGstBreakdown(int $plantId, array $items): array
    {
        $plant = ClientPlant::findOrFail($plantId);
        $isGujarat = strcasecmp(trim($plant->state), 'Gujarat') === 0;

        $taxable = 0.00;
        foreach ($items as $item) {
            $taxable += (float)$item['quantity'] * (float)$item['unit_price'];
        }

        $cgst = 0.00;
        $sgst = 0.00;
        $igst = 0.00;

        if ($isGujarat) {
            $cgst = round($taxable * 0.09, 2);
            $sgst = round($taxable * 0.09, 2);
        } else {
            $igst = round($taxable * 0.18, 2);
        }

        $totalAmount = $taxable + $cgst + $sgst + $igst;

        return [
            'plant' => $plant,
            'is_gujarat' => $isGujarat,
            'taxable_value' => round($taxable, 2),
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total_amount' => round($totalAmount, 2),
        ];
    }
}
