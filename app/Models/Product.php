<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $table = 'finished_goods';

    protected $fillable = [
        'product_name',
        'sku',
        'hsn_code',
        'uom',
        'current_stock',
        'selling_price',
        'alerts_enabled',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'selling_price' => 'decimal:2',
        'alerts_enabled' => 'boolean',
    ];

    /**
     * Get the bill of materials for this product.
     */
    public function billOfMaterials()
    {
        return $this->hasMany(BillOfMaterial::class, 'finished_good_id');
    }

    /**
     * Get the raw materials required for this product.
     */
    public function rawMaterials()
    {
        return $this->belongsToMany(RawMaterial::class, 'bill_of_materials', 'finished_good_id', 'raw_material_id')
                    ->withPivot('required_quantity', 'waste_percentage')
                    ->withTimestamps();
    }

    /**
     * Get the production logs for this product.
     */
    public function productionLogs()
    {
        return $this->hasMany(ProductionLog::class, 'finished_good_id');
    }

    /**
     * Get the delivery challan items containing this product.
     */
    public function deliveryChallanItems()
    {
        return $this->hasMany(DeliveryChallanItem::class, 'finished_good_id');
    }
}
