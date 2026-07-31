<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'is_active',
        'phone',
        'salary',
        'avatar_path',
        'permissions',
    ];

    /**
     * Check if user account is active & approved by admin.
     */
    public function isApproved(): bool
    {
        if (in_array($this->role, ['super_admin', 'admin'])) {
            return true;
        }
        return (bool) $this->is_active && ($this->status === 'active' || $this->status === 'approved');
    }

    /**
     * Check if user account is pending approval.
     */
    public function isPending(): bool
    {
        return false;
    }

    /**
     * Scope query to active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    /**
     * Check if user has explicit permission key.
     */
    public function hasPermission(string $permissionKey): bool
    {
        return \App\Services\RolePermissionService::userHasPermission($this, $permissionKey);
    }

    /**
     * Get the staff profile associated with the user.
     */
    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    /**
     * Get the production logs recorded by the user.
     */
    public function productionLogs()
    {
        return $this->hasMany(ProductionLog::class, 'recorded_by');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }
}
