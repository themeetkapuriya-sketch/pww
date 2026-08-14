<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'plant_id',
        'invoice_mode',
        'custom_client_name',
        'custom_gst_rate',
        'custom_buyer_gstin',
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
     * Scope query to paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope query to unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    /**
     * Scope query to partially paid invoices.
     */
    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    /**
     * Get the client plant for this invoice.
     */
    public function plant()
    {
        return $this->belongsTo(ClientPlant::class, 'plant_id');
    }

    /**
     * Get the associated sales order.
     */
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
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
        return max(0.00, round((float) $this->total_amount - (float) $this->paid_amount, 2));
    }

    /**
     * Generate sequential invoice number for current Financial Year (Apr 1 - Mar 31).
     * Resets to 0001 after March 31st.
     */
    public static function generateNextInvoiceNumber(): string
    {
        $now = Carbon::now();
        if ($now->month >= 4) {
            $fyStart = Carbon::create($now->year, 4, 1, 0, 0, 0);
            $fyEnd = Carbon::create($now->year + 1, 3, 31, 23, 59, 59);
        } else {
            $fyStart = Carbon::create($now->year - 1, 4, 1, 0, 0, 0);
            $fyEnd = Carbon::create($now->year, 3, 31, 23, 59, 59);
        }

        $prefix = Setting::get('invoice_prefix', 'PWW-');
        $customNextSeq = (int) Setting::get('invoice_next_sequence', 1);

        $count = self::where(function ($q) {
            $q->where('invoice_mode', 'finished_goods')->orWhereNull('invoice_mode');
        })->whereBetween('created_at', [$fyStart, $fyEnd])->count();

        $nextSequence = max($count + 1, $customNextSeq);
        $candidate = Setting::formatDocumentNumber($prefix, $nextSequence);
        while (self::where('invoice_number', $candidate)->exists()) {
            $nextSequence++;
            $candidate = Setting::formatDocumentNumber($prefix, $nextSequence);
        }

        return $candidate;
    }

    /**
     * Generate separate voucher number sequence for Raw Material / Scrap Sales.
     */
    public static function generateNextRawMaterialNumber(): string
    {
        $now = Carbon::now();
        if ($now->month >= 4) {
            $fyStart = Carbon::create($now->year, 4, 1, 0, 0, 0);
            $fyEnd = Carbon::create($now->year + 1, 3, 31, 23, 59, 59);
        } else {
            $fyStart = Carbon::create($now->year - 1, 4, 1, 0, 0, 0);
            $fyEnd = Carbon::create($now->year, 3, 31, 23, 59, 59);
        }

        $count = self::where('invoice_mode', 'raw_material')->whereBetween('created_at', [$fyStart, $fyEnd])->count();
        $nextSequence = $count + 1;
        $candidate = Setting::formatDocumentNumber('RMS-', $nextSequence);
        while (self::where('invoice_number', $candidate)->exists()) {
            $nextSequence++;
            $candidate = Setting::formatDocumentNumber('RMS-', $nextSequence);
        }

        return $candidate;
    }
}
