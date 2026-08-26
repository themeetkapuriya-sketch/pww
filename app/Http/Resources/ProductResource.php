<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the product/finished-good model into a sanitized array for frontend consumption.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->product_name,
            'product_name' => $this->product_name,
            'sku' => $this->sku,
            'hsn_code' => $this->hsn_code,
            'unit_price' => (float) ($this->selling_price ?? 0),
            'selling_price' => (float) ($this->selling_price ?? 0),
            'price_per_kg' => (float) ($this->price_per_kg ?? 0),
            'unit_weight_kg' => (float) ($this->unit_weight_kg ?? 0),
            'uom' => $this->uom ?? 'piece',
            'unit' => $this->uom ?? 'piece',
            'current_stock' => (float) ($this->current_stock ?? 0),
        ];
    }
}
