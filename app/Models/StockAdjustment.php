<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'raw_material_id',
        'user_id',
        'user_name',
        'previous_stock',
        'new_stock',
        'variance_qty',
        'reason',
        'notes',
        'adjusted_at',
    ];

    protected $casts = [
        'previous_stock' => 'decimal:4',
        'new_stock' => 'decimal:4',
        'variance_qty' => 'decimal:4',
        'adjusted_at' => 'datetime',
    ];

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
