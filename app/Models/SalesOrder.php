<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public static function generateNextOrderNumber(): string
    {
        $prefix = 'PWW-ORD-';
        $dateStr = date('Ymd');
        $latest = self::where('order_number', 'like', "{$prefix}{$dateStr}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $parts = explode('-', $latest->order_number);
            $seq = intval(end($parts)) + 1;
        } else {
            $seq = 1;
        }

        return sprintf("%s%s-%04d", $prefix, $dateStr, $seq);
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
        $this->loadMissing('items.product');
        if ($this->items->isEmpty()) {
            return false;
        }

        foreach ($this->items as $item) {
            $product = $item->product;
            if (!$product || $product->current_stock < $item->quantity) {
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
        $this->loadMissing('items.product');
        $deficits = [];

        foreach ($this->items as $item) {
            $product = $item->product;
            $current = $product ? (int)$product->current_stock : 0;
            $required = (int)$item->quantity;

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
}
