<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RolePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_slug',
        'permission_key',
    ];

    /**
     * Get role associated with permission record.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_slug', 'slug');
    }
}
