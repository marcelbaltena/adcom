<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'hours_per_day',
        'hours_per_week',
        'hours_per_month',
    ];

    protected $casts = [
        'monday' => 'boolean',
        'tuesday' => 'boolean',
        'wednesday' => 'boolean',
        'thursday' => 'boolean',
        'friday' => 'boolean',
        'saturday' => 'boolean',
        'sunday' => 'boolean',
        'hours_per_day' => 'decimal:2',
        'hours_per_week' => 'decimal:2',
        'hours_per_month' => 'decimal:2',
    ];

    /**
     * Get the user that owns the work schedule.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get working days as array
     */
    public function getWorkingDaysAttribute()
    {
        $days = [];
        if ($this->monday) $days[] = 'Maandag';
        if ($this->tuesday) $days[] = 'Dinsdag';
        if ($this->wednesday) $days[] = 'Woensdag';
        if ($this->thursday) $days[] = 'Donderdag';
        if ($this->friday) $days[] = 'Vrijdag';
        if ($this->saturday) $days[] = 'Zaterdag';
        if ($this->sunday) $days[] = 'Zondag';
        
        return $days;
    }

    /**
     * Calculate working days in month
     */
    public function calculateWorkingDaysInMonth($year, $month)
    {
        $workingDays = 0;
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayOfWeek = date('N', mktime(0, 0, 0, $month, $day, $year));
            
            switch ($dayOfWeek) {
                case 1: if ($this->monday) $workingDays++; break;
                case 2: if ($this->tuesday) $workingDays++; break;
                case 3: if ($this->wednesday) $workingDays++; break;
                case 4: if ($this->thursday) $workingDays++; break;
                case 5: if ($this->friday) $workingDays++; break;
                case 6: if ($this->saturday) $workingDays++; break;
                case 7: if ($this->sunday) $workingDays++; break;
            }
        }
        
        return $workingDays;
    }

    /**
     * Calculate expected hours for a specific month
     */
    public function calculateMonthlyHours($year, $month)
    {
        $workingDays = $this->calculateWorkingDaysInMonth($year, $month);
        return $workingDays * $this->hours_per_day;
    }
}