<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'section',
    ];

    public function overtimeRecords(): HasMany
    {
        return $this->hasMany(OvertimeRecord::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->last_name}, {$this->first_name}";
    }

    /**
     * Get total approved overtime hours (only count when both Form A and B are filled)
     */
    public function getTotalOvertimeHoursAttribute(): float
    {
        return $this->overtimeRecords()
            ->whereNotNull('purpose_deliverables')
            ->where('purpose_deliverables', '!=', '')
            ->whereNotNull('actual_accomplishment')
            ->where('actual_accomplishment', '!=', '')
            ->sum('overtime_hours');
    }

    /**
     * Get total pending overtime hours (not yet approved)
     */
    public function getPendingOvertimeHoursAttribute(): float
    {
        return $this->overtimeRecords()
            ->where(function($query) {
                $query->whereNull('purpose_deliverables')
                      ->orWhere('purpose_deliverables', '=', '')
                      ->orWhereNull('actual_accomplishment')
                      ->orWhere('actual_accomplishment', '=', '');
            })
            ->sum('overtime_hours');
    }
}