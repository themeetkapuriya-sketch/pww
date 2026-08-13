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
            'finished_goods_status' => $this->getFinishedGoodsStockStatus(),
            'raw_material_mrp' => $this->calculateRawMaterialRequirements(),
            'items' => $this->items ? $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product ? $item->product->product_name : 'Item #'.$item->product_id,
                    'sku' => $item->product ? $item->product->sku : '',
                    'billing_uom' => $item->billing_uom ?? 'Pcs',
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                ];
            })->values()->toArray() : [],
        ];
    }
}
