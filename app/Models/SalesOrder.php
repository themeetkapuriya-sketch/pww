<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'po_number',
        'client_id',
        'plant_id',
        'order_date',
        'delivery_date',
        'status',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function plant()
    {
        return $this->belongsTo(ClientPlant::class, 'plant_id');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class, 'sales_order_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProduction($query)
    {
        return $query->where('status', 'in_production');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready_for_dispatch');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public static function generateNextOrderNumber(): string
    {
        $prefix = Setting::get('order_prefix', 'PWW-ORD-');
        $customNextSeq = (int) Setting::get('order_next_sequence', 1);

        $count = self::count();
        $nextSequence = max($count + 1, $customNextSeq);
        $candidate = Setting::formatDocumentNumber($prefix, $nextSequence);

        while (self::where('order_number', $candidate)->exists()) {
            $nextSequence++;
            $candidate = Setting::formatDocumentNumber($prefix, $nextSequence);
        }

        return $candidate;
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'in_production' => 'In Production',
            'ready_for_dispatch' => 'Ready for Dispatch',
            'dispatched' => 'Dispatched',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Check if all items in this sales order have sufficient finished goods stock available.
     */
    public function hasSufficientStock(): bool
    {
        // When stock management is OFF, always consider stock as sufficient
        if (! Setting::isStockEnabled()) {
            return true;
        }

        $this->loadMissing('items.product');
        if ($this->items->isEmpty()) {
            return false;
        }

        foreach ($this->items as $item) {
            $product = $item->product;
            if (! $product || $product->current_stock < $item->quantity) {
                return false;
            }
        }

        return true;
    }

    /**
     * Auto promote status to ready_for_dispatch if stock is available.
     */
    public function autoPromoteIfStockAvailable(): bool
    {
        if ($this->status === 'in_production' && $this->hasSufficientStock()) {
            $this->update(['status' => 'ready_for_dispatch']);

            return true;
        }

        return false;
    }

    /**
     * Get stock deficit details if any items lack sufficient stock.
     */
    public function getStockDeficitDetails(): array
    {
        // When stock management is OFF, no deficits to report
        if (! Setting::isStockEnabled()) {
            return [];
        }

        $this->loadMissing('items.product');
        $deficits = [];

        foreach ($this->items as $item) {
            $product = $item->product;
            $current = $product ? (int) $product->current_stock : 0;
            $required = (int) $item->quantity;

            if ($current < $required) {
                $deficits[] = [
                    'product_name' => $product ? $product->product_name : 'Unknown Item',
                    'current_stock' => $current,
                    'required_quantity' => $required,
                    'missing_quantity' => $required - $current,
                ];
            }
        }

        return $deficits;
    }

    /**
     * Calculate aggregated raw material requirements (MRP) across all items in this sales order.
     */
    public function calculateRawMaterialRequirements(): array
    {
        $this->loadMissing(['items.product.billOfMaterials.rawMaterial']);

        $mrpMap = [];
        $missingBoms = [];

        foreach ($this->items as $item) {
            $product = $item->product;
            if (! $product) {
                continue;
            }

            $boms = $product->billOfMaterials;
            if ($boms->isEmpty()) {
                $missingBoms[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'ordered_quantity' => (float) $item->quantity,
                ];

                continue;
            }

            foreach ($boms as $bom) {
                $rm = $bom->rawMaterial;
                if (! $rm) {
                    continue;
                }

                $rmId = $rm->id;
                $wastePct = (float) ($bom->waste_percentage ?? 0);
                $unitBomQty = (float) $bom->required_quantity;
                $effectivePerProduct = $unitBomQty * (1 + ($wastePct / 100));
                $requiredForLineItem = (float) $item->quantity * $effectivePerProduct;

                if (! isset($mrpMap[$rmId])) {
                    $mrpMap[$rmId] = [
                        'raw_material_id' => $rmId,
                        'material_name' => $rm->material_name,
                        'unit' => $rm->unit ?? 'Kg',
                        'total_required' => 0.0,
                        'current_stock' => (float) $rm->current_stock,
                    ];
                }

                $mrpMap[$rmId]['total_required'] += $requiredForLineItem;
            }
        }

        $result = [];
        foreach ($mrpMap as $rmId => $data) {
            $totalReq = round($data['total_required'], 4);
            $currStock = round($data['current_stock'], 4);
            $shortage = max(0.0, round($totalReq - $currStock, 4));
            $isSufficient = $currStock >= $totalReq;

            $result[] = [
                'raw_material_id' => $rmId,
                'material_name' => $data['material_name'],
                'unit' => $data['unit'],
                'total_required' => $totalReq,
                'current_stock' => $currStock,
                'shortage' => $shortage,
                'is_sufficient' => $isSufficient,
                'status' => $isSufficient ? 'Sufficient' : 'Shortage',
            ];
        }

        return [
            'mrp_list' => array_values($result),
            'missing_boms' => $missingBoms,
            'has_shortage' => collect($result)->contains('is_sufficient', false),
        ];
    }

    /**
     * Get detailed finished goods stock readiness status for all line items in this order.
     */
    public function getFinishedGoodsStockStatus(): array
    {
        $this->loadMissing('items.product');

        $itemsStatus = [];
        $totalItems = 0;
        $fullyStockedItems = 0;

        foreach ($this->items as $item) {
            $totalItems++;
            $product = $item->product;
            $orderedQty = (float) $item->quantity;
            $currStock = $product ? (float) $product->current_stock : 0.0;
            $allocatedQty = min($orderedQty, $currStock);
            $missingQty = max(0.0, $orderedQty - $currStock);
            $isSufficient = $currStock >= $orderedQty;

            if ($isSufficient) {
                $fullyStockedItems++;
            }

            $itemsStatus[] = [
                'item_id' => $item->id,
                'product_id' => $product ? $product->id : null,
                'product_name' => $product ? $product->product_name : 'Unknown Product',
                'sku' => $product ? $product->sku : '',
                'ordered_quantity' => $orderedQty,
                'available_stock' => $currStock,
                'allocated_quantity' => $allocatedQty,
                'missing_quantity' => $missingQty,
                'is_sufficient' => $isSufficient,
                'billing_uom' => $item->billing_uom ?? 'Pcs',
            ];
        }

        $overallStatus = 'Production Required';
        if ($totalItems > 0 && $fullyStockedItems === $totalItems) {
            $overallStatus = 'Fully Stocked (Ready to Dispatch)';
        } elseif ($fullyStockedItems > 0) {
            $overallStatus = 'Partially Stocked';
        }

        return [
            'overall_status' => $overallStatus,
            'is_fully_stocked' => ($totalItems > 0 && $fullyStockedItems === $totalItems),
            'items' => $itemsStatus,
        ];
    }
}
