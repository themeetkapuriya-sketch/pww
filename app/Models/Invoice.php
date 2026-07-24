<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id',
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
     * Get the client plant for this invoice.
     */
    public function plant()
    {
        return $this->belongsTo(ClientPlant::class, 'plant_id');
    }

    /**
     * Get the client via client plant.
     */
    public function getClientAttribute()
    {
        return $this->plant ? $this->plant->client : null;
    }

    /**
     * Get all line items attached directly to this invoice.
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    /**
     * Get all payments recorded against this invoice.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id')->orderBy('payment_date', 'desc');
    }

    /**
     * Get remaining balance due on invoice.
     */
    public function getRemainingBalanceAttribute(): float
    {
        return max(0.00, round((float)$this->total_amount - (float)$this->paid_amount, 2));
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
        return 'PWW-' . date('Ymd') . '-' . $sequenceStr;
    }
}
