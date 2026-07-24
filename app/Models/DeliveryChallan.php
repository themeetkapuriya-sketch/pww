<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryChallan extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'plant_id',
        'challan_number',
        'dispatch_date',
        'status',
        'invoice_id',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
    ];

    /**
     * Get the client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Get the destination client plant.
     */
    public function plant()
    {
        return $this->belongsTo(ClientPlant::class, 'plant_id');
    }

    /**
     * Get the items in this delivery challan.
     */
    public function items()
    {
        return $this->hasMany(DeliveryChallanItem::class, 'delivery_challan_id');
    }

    /**
     * Get the associated invoice.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public static function generateNextChallanNumber(): string
    {
        $prefix = 'DC-';
        $dateStr = date('Ymd');
        $latest = self::where('challan_number', 'like', "{$prefix}{$dateStr}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $parts = explode('-', $latest->challan_number);
            $seq = intval(end($parts)) + 1;
        } else {
            $seq = 1;
        }

        return sprintf("%s%s-%04d", $prefix, $dateStr, $seq);
    }
}
