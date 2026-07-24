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
}
