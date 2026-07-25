<?php

namespace App\Http\Requests\Purchases;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bill_number' => ['nullable', 'string', 'max:100'],
            'vendor_name' => ['required', 'string', 'max:255'],
            'purchase_type' => ['required', 'in:raw_material,office_assets,machinery,factory_spares,supplies,vehicle_transport,others'],
            'raw_material_id' => ['nullable', 'exclude_unless:purchase_type,raw_material', 'required_if:purchase_type,raw_material', 'exists:raw_materials,id'],
            'item_name' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0.0001'],
            'unit' => ['nullable', 'string', 'max:50'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'gst_rate' => ['required', 'numeric', 'in:0,5,12,18,28'],
            'purchase_date' => ['required', 'date'],
        ];
    }
}
