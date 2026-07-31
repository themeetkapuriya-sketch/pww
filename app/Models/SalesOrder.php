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
