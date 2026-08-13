<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'mobile_number',
        'wage_type',
        'monthly_salary',
        'piece_rate_per_unit',
        'is_active',
    ];

    protected $casts = [
        'monthly_salary' => 'decimal:2',
        'piece_rate_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active staff.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the user account associated with the staff member.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the labor logs for the staff member.
     */
    public function laborLogs()
    {
        return $this->hasMany(LaborLog::class, 'staff_profile_id');
    }

    /**
     * Get the daily attendance records for the staff member.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'staff_profile_id');
    }

    /**
     * Get the monthly salary payments for the staff member.
     */
    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class, 'staff_profile_id');
    }

    /**
     * Get salary advances given to the staff member.
     */
    public function advances()
    {
        return $this->hasMany(SalaryAdvance::class, 'staff_profile_id');
    }

    /**
     * Total pending advance amount for the staff member up to a specific date.
     */
    public function pendingAdvanceTotal(?string $beforeDate = null): float
    {
        $query = $this->advances()->where('status', 'pending');
        if ($beforeDate) {
            $query->where('advance_date', '<=', $beforeDate);
        }
        return (float) $query->sum('amount');
    }
}
