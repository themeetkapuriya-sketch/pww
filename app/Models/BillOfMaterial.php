<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillOfMaterial extends Model
{
    use HasFactory;

    protected $table = 'bill_of_materials';

    protected $fillable = [
        'product_id',
        'raw_material_id',
        'required_quantity',
        'waste_percentage',
    ];

    protected $casts = [
        'required_quantity' => 'decimal:4',
        'waste_percentage' => 'decimal:2',
    ];

    /**
     * Get the product for this BOM item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Alias for product.
     */
    public function finishedGood()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the raw material for this BOM item.
     */
    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }
}
