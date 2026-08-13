<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_name',
        'unit',
        'current_stock',
        'safety_threshold',
        'average_purchase_price',
    ];

    protected $casts = [
        'current_stock' => 'decimal:4',
        'safety_threshold' => 'decimal:4',
        'average_purchase_price' => 'decimal:2',
    ];

    /**
     * Get the bill of materials referencing this raw material.
     */
    public function billOfMaterials()
    {
        return $this->hasMany(BillOfMaterial::class, 'raw_material_id');
    }

    /**
     * Get the products that consume this raw material.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'bill_of_materials', 'raw_material_id', 'product_id')
            ->withPivot('required_quantity', 'waste_percentage')
            ->withTimestamps();
    }

    /**
     * Alias for products.
     */
    public function finishedGoods()
    {
        return $this->belongsToMany(Product::class, 'bill_of_materials', 'raw_material_id', 'product_id')
            ->withPivot('required_quantity', 'waste_percentage')
            ->withTimestamps();
    }

    /**
     * Get all purchase entries for this raw material.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'raw_material_id')->orderBy('purchase_date', 'desc');
    }

    /**
     * Get the latest purchase entry for this raw material.
     */
    public function latestPurchase()
    {
        return $this->hasOne(Purchase::class, 'raw_material_id')->latestOfMany('purchase_date');
    }

    /**
     * Get all physical stock adjustment vouchers for this raw material.
     */
    public function adjustments()
    {
        return $this->hasMany(StockAdjustment::class, 'raw_material_id')->orderBy('adjusted_at', 'desc');
    }
}
