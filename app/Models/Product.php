<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'sku',
        'hsn_code',
        'uom',
        'unit_weight_kg',
        'current_stock',
        'selling_price',
        'price_per_kg',
        'gst_rate',
        'safety_threshold',
        'alerts_enabled',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'safety_threshold' => 'integer',
        'selling_price' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'unit_weight_kg' => 'decimal:3',
        'alerts_enabled' => 'boolean',
    ];

    /**
     * Get the bill of materials for this product.
     */
    public function billOfMaterials()
    {
        return $this->hasMany(BillOfMaterial::class, 'product_id');
    }

    /**
     * Alias for billOfMaterials.
     */
    public function bom()
    {
        return $this->hasMany(BillOfMaterial::class, 'product_id');
    }

    /**
     * Get the raw materials required for this product.
     */
    public function rawMaterials()
    {
        return $this->belongsToMany(RawMaterial::class, 'bill_of_materials', 'product_id', 'raw_material_id')
            ->withPivot('required_quantity', 'waste_percentage')
            ->withTimestamps();
    }

    /**
     * Get the production logs for this product.
     */
    public function productionLogs()
    {
        return $this->hasMany(ProductionLog::class, 'product_id');
    }

    /**
     * Get the invoice items containing this product.
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class, 'product_id');
    }
}
