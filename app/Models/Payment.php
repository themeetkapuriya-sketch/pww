<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'payment_type',
        'invoice_id',
        'purchase_id',
        'client_id',
        'plant_id',
        'vendor_name',
        'amount',
        'payment_date',
        'payment_method',
        'account_type',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function plant()
    {
        return $this->belongsTo(ClientPlant::class, 'plant_id');
    }

    /**
     * Generate unique payment voucher number.
     */
    public static function generatePaymentNumber(string $type = 'received'): string
    {
        $prefix = ($type === 'received') ? 'PAY-REC-' : 'PAY-VND-';
        $year = date('Y');
        $latest = self::where('payment_number', 'like', "{$prefix}{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $parts = explode('-', $latest->payment_number);
            $seq = intval(end($parts)) + 1;
        } else {
            $seq = 1;
        }

        return sprintf("%s%s-%04d", $prefix, $year, $seq);
    }

    /**
     * Formatted label for payment method.
     */
    public function getFormattedMethodAttribute(): string
    {
        return match ($this->payment_method) {
            'bank_transfer' => 'Bank Transfer (NEFT/RTGS)',
            'cheque' => 'Cheque',
            'upi' => 'UPI / Online',
            'cash' => 'Cash',
            default => ucfirst(str_replace('_', ' ', $this->payment_method ?? 'Bank')),
        };
    }
}
