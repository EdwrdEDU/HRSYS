<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class OvertimeRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
        'break_hours',
        'total_hours_rendered',
        'overtime_hours',
        'remarks',
        'purpose_deliverables',
        'actual_accomplishment',
    ];

    protected $casts = [
        'date' => 'date',
        'break_hours' => 'decimal:2',
        'total_hours_rendered' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Check if overtime is approved
     * Approved if: Form B is filled
     */
    public function isApproved(): bool
    {
        return !empty($this->actual_accomplishment);
    }

    /**
     * Get approval status badge
     */
    public function getApprovalStatusAttribute(): string
    {
        $hasFormA = !empty($this->purpose_deliverables);
        $hasFormB = !empty($this->actual_accomplishment);
        
        if ($hasFormB) {
            return 'Approved';
        }
        
        if ($hasFormA) {
            return 'Pending (Form A Only)';
        }
        
        return 'Pending';
    }

    protected static function booted()
    {
        static::creating(function ($record) {
            self::calculateOvertimeHours($record);
        });

        static::updating(function ($record) {
            self::calculateOvertimeHours($record);
        });
    }

    private static function calculateOvertimeHours($record)
    {
        // Only process if both time_in and time_out are provided
        if (empty($record->time_in) || empty($record->time_out)) {
            \Log::warning('Missing time_in or time_out', [
                'time_in' => $record->time_in,
                'time_out' => $record->time_out
            ]);
            return;
        }

        try {
            // Get date or use today
            $date = $record->date ? Carbon::parse($record->date) : Carbon::today();
            $dateString = $date->format('Y-m-d');
            
            // Parse the input times and create full datetime objects
            // This handles formats like "7:38 AM", "07:38", "7:38 am", etc.
            $timeInParsed = Carbon::parse($record->time_in);
            $timeOutParsed = Carbon::parse($record->time_out);
            
            // Create datetime objects using the date and parsed times
            $timeIn = Carbon::parse($dateString . ' ' . $timeInParsed->format('H:i:s'));
            $timeOut = Carbon::parse($dateString . ' ' . $timeOutParsed->format('H:i:s'));
            
            // Handle overnight shifts (if time_out is before time_in, add a day to time_out)
            if ($timeOut->lte($timeIn)) {
                $timeOut->addDay();
            }
            
            // Store normalized time values (H:i:s format for time column)
            $record->time_in = $timeInParsed->format('H:i:s');
            $record->time_out = $timeOutParsed->format('H:i:s');
            
            // Calculate total hours between in and out
            $totalMinutes = $timeIn->diffInMinutes($timeOut);
            $totalHours = $totalMinutes / 60;
            $breakHours = is_numeric($record->break_hours) ? (float)$record->break_hours : 0;
            $renderedHours = round($totalHours - $breakHours, 2);
            
            \Log::info('Calculation Debug', [
                'date' => $dateString,
                'time_in_raw' => $record->time_in,
                'time_out_raw' => $record->time_out,
                'time_in_parsed' => $timeIn->format('Y-m-d H:i:s'),
                'time_out_parsed' => $timeOut->format('Y-m-d H:i:s'),
                'total_minutes' => $totalMinutes,
                'total_hours' => $totalHours,
                'break_hours' => $breakHours,
                'rendered_hours' => $renderedHours,
                'current_total_hours_rendered' => $record->total_hours_rendered,
                'current_overtime_hours' => $record->overtime_hours,
            ]);
            
            // Auto-calculate total_hours_rendered if not provided or is null
            if ($record->total_hours_rendered === null || $record->total_hours_rendered === '') {
                $record->total_hours_rendered = $renderedHours;
                \Log::info('Auto-calculated total_hours_rendered: ' . $renderedHours);
            } else {
                $renderedHours = (float)$record->total_hours_rendered;
                \Log::info('Using provided total_hours_rendered: ' . $renderedHours);
            }
            
            // Auto-calculate overtime_hours if not provided or is null
            if ($record->overtime_hours === null || $record->overtime_hours === '') {
                $standardHours = 8;
                $overtimeCalculated = max(0, $renderedHours - $standardHours);
                $record->overtime_hours = round($overtimeCalculated, 2);
                \Log::info('Auto-calculated overtime_hours: ' . $record->overtime_hours);
            } else {
                \Log::info('Using provided overtime_hours: ' . $record->overtime_hours);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error calculating overtime', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}