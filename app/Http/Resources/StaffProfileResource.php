<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffProfileResource extends JsonResource
{
    /**
     * Transform the staff profile model into a sanitized array for frontend consumption.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'full_name' => $this->full_name,
            'wage_type' => $this->wage_type,
            'monthly_salary' => (float) $this->monthly_salary,
            'piece_rate_per_unit' => (float) $this->piece_rate_per_unit,
        ];
    }
}
