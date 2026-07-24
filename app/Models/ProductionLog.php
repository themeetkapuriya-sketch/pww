<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity_manufactured',
        'quantity_rejected',
        'recorded_by',
        'production_date',
    ];

    protected $casts = [
        'quantity_manufactured' => 'integer',
        'quantity_rejected' => 'integer',
        'production_date' => 'date',
    ];

    /**
     * Get the product that was manufactured.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Alias for product.
     */
    public function finishedGood()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the user who recorded this log.
     */
    public function recordedByUser()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the labor logs associated with this production.
     */
    public function laborLogs()
    {
        return $this->hasMany(LaborLog::class, 'production_log_id');
    }
}
