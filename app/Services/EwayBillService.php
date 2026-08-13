<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use Carbon\Carbon;

class EwayBillService
{
    /**
     * Generate NIC E-Way Bill System Compliant JSON Payload (v1.0.1121).
     */
    public static function generateJsonPayload(Invoice $invoice): array
    {
        $invoice->loadMissing(['plant.client', 'items.product', 'salesOrder']);

        $businessGstin = Setting::get('gstin', Setting::get('business_gstin', '24AFHPV5264M1ZU'));
        $businessName = Setting::get('business_name', 'Praful Welding Works');
        $businessAddr = Setting::get('address_line_1', Setting::get('address', 'VILLAGE : KHORANA TA : RAJKOT DI : RAJKOT - 360 003'));
        $businessCity = Setting::get('city', Setting::get('business_city', 'Rajkot'));
        $businessPin = (int) preg_replace('/[^0-9]/', '', Setting::get('pincode', Setting::get('business_pincode', '360003'))) ?: 360003;

        $plant = $invoice->plant;
        $client = $plant ? $plant->client : null;

        $toGstin = $plant->gst_number ?? ($client->gst_number ?? 'URP');
        $toName = $client->company_name ?? ($plant->plant_name ?? 'Client Customer');
        $toAddr = $plant->shipping_address ?? ($client->corporate_address ?? 'Client Office');
        $toPlace = $plant->state ?? ($client->city ?? 'Gujarat');
        $toPin = (int) preg_replace('/[^0-9]/', '', $plant->pincode ?? ($client->pincode ?? '360001')) ?: 360001;

        $fromStateCode = (int) substr($businessGstin, 0, 2) ?: 24;
        $toStateCode = (int) (preg_match('/^[0-9]{2}/', $toGstin, $m) ? $m[0] : 24);

        $isInterstate = ($fromStateCode !== $toStateCode);
        $gstRate = (float) Setting::get('default_gst_rate', '18.00');

        $docDate = $invoice->invoice_date
            ? Carbon::parse($invoice->invoice_date)->format('d/m/Y')
            : Carbon::now()->format('d/m/Y');

        $itemList = [];
        if ($invoice->items && $invoice->items->count() > 0) {
            foreach ($invoice->items as $item) {
                $product = $item->product;
                $hsn = (int) preg_replace('/[^0-9]/', '', $product->hsn_code ?? '7314') ?: 7314;
                $qty = (float) $item->quantity;
                $taxable = (float) $item->total_price;

                $cgstRate = $isInterstate ? 0.00 : round($gstRate / 2, 2);
                $sgstRate = $isInterstate ? 0.00 : round($gstRate / 2, 2);
                $igstRate = $isInterstate ? $gstRate : 0.00;

                $itemList[] = [
                    'productName' => substr($product->product_name ?? 'Welded Mesh Item', 0, 100),
                    'productDesc' => substr($product->description ?? 'Welded Wire Mesh', 0, 100),
                    'hsnCode' => $hsn,
                    'quantity' => $qty,
                    'qtyUnit' => 'NOS',
                    'taxableAmount' => $taxable,
                    'cgstRate' => $cgstRate,
                    'sgstRate' => $sgstRate,
                    'igstRate' => $igstRate,
                    'cessRate' => 0.00,
                    'cessNonAdvol' => 0.00,
                ];
            }
        } else {
            $taxable = (float) $invoice->total_taxable_value;
            $cgstRate = $isInterstate ? 0.00 : round($gstRate / 2, 2);
            $sgstRate = $isInterstate ? 0.00 : round($gstRate / 2, 2);
            $igstRate = $isInterstate ? $gstRate : 0.00;

            $itemList[] = [
                'productName' => 'Welded Wire Mesh Products',
                'productDesc' => 'Industrial Welded Wire Mesh Batch',
                'hsnCode' => 7314,
                'quantity' => 1.00,
                'qtyUnit' => 'LOT',
                'taxableAmount' => $taxable,
                'cgstRate' => $cgstRate,
                'sgstRate' => $sgstRate,
                'igstRate' => $igstRate,
                'cessRate' => 0.00,
                'cessNonAdvol' => 0.00,
            ];
        }

        $vehicleNo = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($invoice->vehicle_number ?? 'GJ03AB1234'));

        return [
            'version' => '1.0.1121',
            'billLists' => [
                [
                    'userGstin' => $businessGstin,
                    'supplyType' => 'O',
                    'subSupplyType' => '1',
                    'subSupplyDesc' => '',
                    'docType' => 'INV',
                    'docNo' => $invoice->invoice_number,
                    'docDate' => $docDate,
                    'fromGstin' => $businessGstin,
                    'fromTrdName' => $businessName,
                    'fromAddr1' => substr($businessAddr, 0, 100),
                    'fromAddr2' => '',
                    'fromPlace' => $businessCity,
                    'fromPincode' => $businessPin,
                    'actFromStateCode' => $fromStateCode,
                    'fromStateCode' => $fromStateCode,
                    'toGstin' => $toGstin,
                    'toTrdName' => substr($toName, 0, 100),
                    'toAddr1' => substr($toAddr, 0, 100),
                    'toAddr2' => '',
                    'toPlace' => $toPlace,
                    'toPincode' => $toPin,
                    'actToStateCode' => $toStateCode,
                    'toStateCode' => $toStateCode,
                    'transactionType' => 1,
                    'totalValue' => (float) $invoice->total_taxable_value,
                    'cgstValue' => (float) $invoice->cgst,
                    'sgstValue' => (float) $invoice->sgst,
                    'igstValue' => (float) $invoice->igst,
                    'cessValue' => 0.00,
                    'cessNonAdvolValue' => 0.00,
                    'totInvValue' => (float) $invoice->total_amount,
                    'transporterId' => '',
                    'transporterName' => '',
                    'transDocNo' => '',
                    'transMode' => '1',
                    'transDistance' => '50',
                    'transDocDate' => '',
                    'vehicleNo' => $vehicleNo ?: 'GJ03AB1234',
                    'vehicleType' => 'R',
                    'itemList' => $itemList,
                ],
            ],
        ];
    }
}
