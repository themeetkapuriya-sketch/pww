<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientPlant extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'plant_name',
        'shipping_address',
        'state',
        'gst_number',
    ];

    /**
     * Get the client parent profile.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Get the delivery challans sent to this plant.
     */
    public function deliveryChallans()
    {
        return $this->hasMany(DeliveryChallan::class, 'plant_id');
    }

    /**
     * Get all invoices associated with this plant.
     */
    public function invoices()
    {
        return Invoice::whereHas('deliveryChallan', function ($q) {
            $q->where('plant_id', $this->id);
        })->orWhereHas('deliveryChallans', function ($q) {
            $q->where('plant_id', $this->id);
        });
    }

    /**
     * Calculate outstanding balance for this specific plant.
     */
    public function getOutstandingBalanceAttribute(): float
    {
        $invoices = $this->invoices()->get();
        $totalInvoiced = $invoices->sum('total_amount');
        $totalPaid = $invoices->sum('paid_amount');
        return max(0.00, round($totalInvoiced - $totalPaid, 2));
    }
}
