<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderResource extends JsonResource
{
    /**
     * Transform the sales order model into a sanitized array for frontend consumption.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'po_number' => $this->po_number,
            'client_id' => $this->client_id,
            'client_name' => $this->client ? $this->client->company_name : 'N/A',
            'plant_id' => $this->plant_id ?? $this->client_plant_id,
            'client_plant_id' => $this->plant_id ?? $this->client_plant_id,
            'plant_name' => $this->plant ? $this->plant->plant_name.($this->plant->state ? ' ('.$this->plant->state.')' : '') : 'Main Plant',
            'status' => $this->status,
            'formatted_status' => $this->formatted_status,
            'order_date' => $this->order_date ? $this->order_date->format('Y-m-d') : null,
            'delivery_date' => $this->delivery_date ? $this->delivery_date->format('Y-m-d') : ($this->due_date ? $this->due_date->format('Y-m-d') : null),
            'due_date' => $this->due_date ? $this->due_date->format('Y-m-d') : null,
            'total_amount' => (float) $this->total_amount,
            'notes' => $this->notes,
            'track_stock_enabled' => \App\Models\Setting::isStockEnabled(),
            'finished_goods_status' => $this->getFinishedGoodsStockStatus(),
            'raw_material_mrp' => $this->calculateRawMaterialRequirements(),
            'estimated_cost_summary' => $this->calculateEstimatedOrderCost(),
            'items' => $this->items ? $this->items->map(function ($item) {
                $product = $item->product;
                $unitMfgCost = $product ? (float) $product->estimated_manufacturing_cost : 0.0;
                $lineMfgCost = round($unitMfgCost * (float) $item->quantity, 2);
                $unitPrice = (float) $item->unit_price;
                $unitMargin = round($unitPrice - $unitMfgCost, 2);
                $totalMargin = round($unitMargin * (float) $item->quantity, 2);

                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $product ? $product->product_name : 'Item #'.$item->product_id,
                    'sku' => $product ? $product->sku : '',
                    'billing_uom' => $item->billing_uom ?? 'Pcs',
                    'quantity' => (float) $item->quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => (float) $item->total_price,
                    'unit_estimated_cost' => $unitMfgCost,
                    'total_estimated_cost' => $lineMfgCost,
                    'unit_margin' => $unitMargin,
                    'total_margin' => $totalMargin,
                ];
            })->values()->toArray() : [],
        ];
    }
}
