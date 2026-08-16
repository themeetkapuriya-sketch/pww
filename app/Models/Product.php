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

    /**
     * Calculate estimated raw material cost per product unit from BOM.
     */
    public function getEstimatedManufacturingCostAttribute(): float
    {
        $boms = $this->relationLoaded('billOfMaterials') ? $this->billOfMaterials : $this->billOfMaterials()->with('rawMaterial')->get();
        $totalCost = 0.0;

        foreach ($boms as $bom) {
            $material = $bom->rawMaterial;
            if ($material) {
                $price = (float) ($material->average_purchase_price ?? 0);
                $qty = (float) ($bom->required_quantity ?? 0);
                $waste = (float) ($bom->waste_percentage ?? 0);
                $effectiveQty = $qty * (1 + ($waste / 100));
                $totalCost += ($effectiveQty * $price);
            }
        }

        return round($totalCost, 2);
    }

    /**
     * Calculate base raw material cost without waste allowance.
     */
    public function getBaseMaterialCostAttribute(): float
    {
        $boms = $this->relationLoaded('billOfMaterials') ? $this->billOfMaterials : $this->billOfMaterials()->with('rawMaterial')->get();
        $baseCost = 0.0;

        foreach ($boms as $bom) {
            $material = $bom->rawMaterial;
            if ($material) {
                $price = (float) ($material->average_purchase_price ?? 0);
                $qty = (float) ($bom->required_quantity ?? 0);
                $baseCost += ($qty * $price);
            }
        }

        return round($baseCost, 2);
    }

    /**
     * Calculate waste / scrap buffer cost.
     */
    public function getWasteAllowanceCostAttribute(): float
    {
        return round(max(0, $this->estimated_manufacturing_cost - $this->base_material_cost), 2);
    }

    /**
     * Calculate gross profit per unit (Selling Price - Manufacturing Cost).
     */
    public function getGrossProfitAttribute(): float
    {
        $sellingPrice = (float) ($this->selling_price ?? 0);
        $cost = $this->estimated_manufacturing_cost;

        return round($sellingPrice - $cost, 2);
    }

    /**
     * Calculate gross profit margin percentage.
     */
    public function getProfitMarginPercentageAttribute(): float
    {
        $sellingPrice = (float) ($this->selling_price ?? 0);
        if ($sellingPrice <= 0) {
            return 0.0;
        }

        $margin = ($this->gross_profit / $sellingPrice) * 100;

        return round($margin, 1);
    }
}
