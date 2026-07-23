<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_challan_id',
        'invoice_number',
        'vehicle_number',
        'invoice_date',
        'total_taxable_value',
        'cgst',
        'sgst',
        'igst',
        'total_amount',
        'payment_status',
        'paid_amount',
        'due_date',
    ];

    protected $casts = [
        'total_taxable_value' => 'decimal:2',
        'cgst' => 'decimal:2',
        'sgst' => 'decimal:2',
        'igst' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'invoice_date' => 'date',
    ];

    /**
     * Get the primary delivery challan if single-challan invoice.
     */
    public function deliveryChallan()
    {
        return $this->belongsTo(DeliveryChallan::class, 'delivery_challan_id');
    }

    /**
     * Get all delivery challans aggregated in this invoice.
     */
    public function deliveryChallans()
    {
        return $this->hasMany(DeliveryChallan::class, 'invoice_id');
    }

    /**
     * Generate sequential invoice number for current Financial Year (Apr 1 - Mar 31).
     * Resets to 0001 after March 31st.
     */
    public static function generateNextInvoiceNumber(): string
    {
        $now = \Carbon\Carbon::now();
        if ($now->month >= 4) {
            $fyStart = \Carbon\Carbon::create($now->year, 4, 1, 0, 0, 0);
            $fyEnd = \Carbon\Carbon::create($now->year + 1, 3, 31, 23, 59, 59);
        } else {
            $fyStart = \Carbon\Carbon::create($now->year - 1, 4, 1, 0, 0, 0);
            $fyEnd = \Carbon\Carbon::create($now->year, 3, 31, 23, 59, 59);
        }

        $count = self::whereBetween('created_at', [$fyStart, $fyEnd])->count();
        $nextSequence = $count + 1;
        $sequenceStr = str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . $sequenceStr;

        while (self::where('invoice_number', $invoiceNumber)->exists()) {
            $nextSequence++;
            $sequenceStr = str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . $sequenceStr;
        }

        return $invoiceNumber;
    }
}
