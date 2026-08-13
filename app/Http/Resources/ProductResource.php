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
            'name' => $this->name,
            'hsn_code' => $this->hsn_code,
            'unit_price' => (float) $this->unit_price,
            'unit' => $this->unit,
            'current_stock' => (float) ($this->current_stock ?? 0),
        ];
    }
}
