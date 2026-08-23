<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RawMaterial extends Model
{
    public const CATEGORIES = [
        'pipes' => ['label' => 'Pipes & Tubes', 'icon' => '🔩', 'color' => 'blue'],
        'powders' => ['label' => 'Powder Coating Powders', 'icon' => '🎨', 'color' => 'purple'],
        'sheets' => ['label' => 'Sheet Metal & Plates', 'icon' => '📐', 'color' => 'indigo'],
        'welding' => ['label' => 'Welding Wire, Rods & Gas', 'icon' => '⚡', 'color' => 'amber'],
        'hardware' => ['label' => 'Fasteners & Hardware', 'icon' => '🔧', 'color' => 'teal'],
        'other' => ['label' => 'Other Consumables', 'icon' => '📦', 'color' => 'slate'],
    ];

    protected $fillable = [
        'material_name',
        'material_category',
        'specification',
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
     * Get human-readable category info.
     */
    public function getCategoryInfoAttribute(): array
    {
        $categories = \App\Services\CategoryService::getMaterialCategories();
        foreach ($categories as $cat) {
            if ($cat['key'] === $this->material_category) {
                return [
                    'label' => $cat['label'],
                    'icon' => $cat['icon'] ?? '📦',
                    'color' => $cat['color'] ?? 'slate',
                ];
            }
        }

        return [
            'label' => ucfirst(str_replace('_', ' ', $this->material_category ?? 'General')),
            'icon' => '📦',
            'color' => 'slate',
        ];
    }

    /**
     * Scope a query to only include raw materials of a given category.
     */
    public function scopeCategory($query, ?string $category)
    {
        if (! empty($category) && $category !== 'all') {
            return $query->where('material_category', $category);
        }

        return $query;
    }

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

    /**
     * Check if material is below safety threshold.
     */
    public function getIsLowStockAttribute(): bool
    {
        return (float) $this->current_stock < (float) $this->safety_threshold;
    }

    /**
     * Calculate smart suggested replenishment quantity.
     */
    public function getSuggestedReorderQuantityAttribute(): float
    {
        $threshold = (float) $this->safety_threshold;
        $current = (float) $this->current_stock;

        if ($threshold <= 0) {
            return 100.0;
        }

        // Target stock is 2x safety threshold
        $deficit = ($threshold * 2) - $current;

        return round(max($threshold, $deficit), 2);
    }

    /**
     * Check if the current average purchase price matches the weighted purchase average or is a custom manual master rate.
     */
    public function getIsAutoAvgAttribute(): bool
    {
        $purchases = $this->relationLoaded('purchases')
            ? $this->purchases->where('purchase_type', 'raw_material')
            : $this->purchases()->where('purchase_type', 'raw_material')->get();

        $totalQty = 0.0;
        $totalCost = 0.0;

        foreach ($purchases as $p) {
            $qty = (float) ($p->quantity ?? 0);
            $amount = (float) ($p->total_amount ?? 0);
            if ($qty > 0 && $amount > 0) {
                $totalQty += $qty;
                $totalCost += $amount;
            }
        }

        if ($totalQty > 0) {
            $weightedAvg = round($totalCost / $totalQty, 2);

            return abs((float) $this->average_purchase_price - $weightedAvg) < 0.01;
        }

        return false;
    }

    /**
     * Recalculate and update the weighted average purchase rate based on all logged purchases.
     */
    public function recalculateAveragePurchasePrice(): float
    {
        $purchases = $this->purchases()->where('purchase_type', 'raw_material')->get();
        $totalQty = 0.0;
        $totalCost = 0.0;

        foreach ($purchases as $p) {
            $qty = (float) ($p->quantity ?? 0);
            $amount = (float) ($p->total_amount ?? 0);
            if ($qty > 0 && $amount > 0) {
                $totalQty += $qty;
                $totalCost += $amount;
            }
        }

        if ($totalQty > 0) {
            $weightedAvg = round($totalCost / $totalQty, 2);
            $this->update(['average_purchase_price' => $weightedAvg]);

            return $weightedAvg;
        }

        return (float) $this->average_purchase_price;
    }
}
