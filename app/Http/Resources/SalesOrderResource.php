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
            'id'             => $this->id,
            'order_number'   => $this->order_number,
            'client_id'      => $this->client_id,
            'client_plant_id' => $this->client_plant_id,
            'status'         => $this->status,
            'order_date'     => $this->order_date ? $this->order_date->format('Y-m-d') : null,
            'due_date'       => $this->due_date ? $this->due_date->format('Y-m-d') : null,
            'total_amount'   => (float) $this->total_amount,
            'notes'          => $this->notes,
        ];
    }
}
