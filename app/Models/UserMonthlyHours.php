<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMonthlyHours extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'contracted_hours',
        'billable_hours',
        'non_billable_hours',
        'vacation_hours',
        'sick_hours',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'contracted_hours' => 'decimal:2',
        'billable_hours' => 'decimal:2',
        'non_billable_hours' => 'decimal:2',
        'vacation_hours' => 'decimal:2',
        'sick_hours' => 'decimal:2',
    ];

    /**
     * Get the user that owns the monthly hours.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get total worked hours
     */
    public function getTotalWorkedHoursAttribute()
    {
        return ($this->billable_hours ?? 0) + ($this->non_billable_hours ?? 0);
    }

    /**
     * Get total absent hours
     */
    public function getTotalAbsentHoursAttribute()
    {
        return $this->vacation_hours + $this->sick_hours;
    }

    /**
     * Get productivity percentage
     */
    public function getProductivityPercentageAttribute()
    {
        if ($this->contracted_hours == 0) return 0;
        
        return round(($this->total_worked_hours / $this->contracted_hours) * 100, 2);
    }

    /**
     * Get billability percentage
     */
    public function getBillabilityPercentageAttribute()
    {
        if ($this->total_worked_hours == 0) return 0;
        
        return round(($this->billable_hours / $this->total_worked_hours) * 100, 2);
    }

    /**
     * Get month name
     */
    public function getMonthNameAttribute()
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maart', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Augustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'December'
        ];
        
        return $months[$this->month] ?? '';
    }
}