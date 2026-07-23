<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'bill_number',
        'vendor_name',
        'purchase_type',
        'raw_material_id',
        'item_name',
        'quantity',
        'unit',
        'total_amount',
        'gst_rate',
        'gst_amount',
        'purchase_date',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'quantity' => 'decimal:4',
        'total_amount' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'gst_amount' => 'decimal:2',
    ];

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }
}
