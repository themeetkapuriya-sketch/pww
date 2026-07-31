<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'route_name',
        'icon_class',
        'parent_id',
        'permission_key',
        'order_weight',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_weight' => 'integer',
    ];

    /**
     * Parent module relationship.
     */
    public function parent()
    {
        return $this->belongsTo(Module::class, 'parent_id');
    }

    /**
     * Sub-modules / Children relationship.
     */
    public function children()
    {
        return $this->hasMany(Module::class, 'parent_id')->orderBy('order_weight', 'asc');
    }
}
