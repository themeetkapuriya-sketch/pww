<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'client_email',
        'gst_number',
        'corporate_address',
    ];

    /**
     * Get the plants associated with the client.
     */
    public function plants()
    {
        return $this->hasMany(ClientPlant::class, 'client_id');
    }

    /**
     * Get sales orders for the client.
     */
    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'client_id')->orderBy('order_date', 'desc');
    }

    /**
     * Get all invoices for the client across plants.
     */
    public function invoices()
    {
        return Invoice::whereHas('plant', function ($q) {
            $q->where('client_id', $this->id);
        });
    }

    /**
     * Get all payments recorded for the client.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'client_id')->orderBy('payment_date', 'desc');
    }
}
