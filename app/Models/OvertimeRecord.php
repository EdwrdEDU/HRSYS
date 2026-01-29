<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * Get all subtractions for this overtime record
     */
    public function subtractions(): HasMany
    {
        return $this->hasMany(OvertimeSubtraction::class);
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
        // Convert HH:MM format to decimal for break_hours if needed
        if (!empty($record->break_hours) && is_string($record->break_hours) && strpos($record->break_hours, ':') !== false) {
            $record->break_hours = self::convertTimeToDecimal($record->break_hours);
        }

        // Convert HH:MM format to decimal for total_hours_rendered if needed
        if (!empty($record->total_hours_rendered) && is_string($record->total_hours_rendered) && strpos($record->total_hours_rendered, ':') !== false) {
            $record->total_hours_rendered = self::convertTimeToDecimal($record->total_hours_rendered);
        }

        // Convert HH:MM format to decimal for overtime_hours if needed
        if (!empty($record->overtime_hours) && is_string($record->overtime_hours) && strpos($record->overtime_hours, ':') !== false) {
            $record->overtime_hours = self::convertTimeToDecimal($record->overtime_hours);
        }

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
            // This handles formats like "7:38 AM", "07:38", "7:38 am", "20:00:00", etc.
            
            // Check if time_in is already in H:i:s format (from database)
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $record->time_in)) {
                $timeInParsed = Carbon::parse($dateString . ' ' . $record->time_in);
            } else {
                $timeInParsed = Carbon::parse($record->time_in);
                $timeInParsed = Carbon::parse($dateString . ' ' . $timeInParsed->format('H:i:s'));
            }
            
            // Check if time_out is already in H:i:s format (from database)
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $record->time_out)) {
                $timeOutParsed = Carbon::parse($dateString . ' ' . $record->time_out);
            } else {
                $timeOutParsed = Carbon::parse($record->time_out);
                $timeOutParsed = Carbon::parse($dateString . ' ' . $timeOutParsed->format('H:i:s'));
            }
            
            $timeIn = $timeInParsed;
            $timeOut = $timeOutParsed;
            
            // Handle overnight shifts (if time_out is before time_in, add a day to time_out)
            if ($timeOut->lte($timeIn)) {
                $timeOut->addDay();
            }
            
            // Store normalized time values (H:i:s format for time column)
            $record->time_in = $timeIn->format('H:i:s');
            $record->time_out = $timeOut->format('H:i:s');
            
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
            
            // Auto-calculate total_hours_rendered if not provided, is null, empty, or zero
            // Also recalculate if times have changed
            $shouldRecalculate = $record->total_hours_rendered === null 
                || $record->total_hours_rendered === '' 
                || $record->total_hours_rendered == 0;
            
            // Check if times changed on update (compare with original values)
            if (!$record->wasRecentlyCreated && $record->isDirty(['time_in', 'time_out', 'break_hours'])) {
                $shouldRecalculate = true;
                \Log::info('Times changed, forcing recalculation');
            }
            
            if ($shouldRecalculate) {
                $record->total_hours_rendered = $renderedHours;
                \Log::info('Auto-calculated total_hours_rendered: ' . $renderedHours);
            } else {
                $renderedHours = (float)$record->total_hours_rendered;
                \Log::info('Using provided total_hours_rendered: ' . $renderedHours);
            }
            
            // Auto-calculate overtime_hours if not provided, is null, empty, or zero
            // Also recalculate if times have changed or if we just recalculated rendered hours
            $shouldRecalculateOT = $record->overtime_hours === null 
                || $record->overtime_hours === '' 
                || $record->overtime_hours == 0
                || $shouldRecalculate;
            
            if ($shouldRecalculateOT) {
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

    /**
     * Convert HH:MM format to decimal hours
     * Examples: "1:00" => 1.0, "1:30" => 1.5, "2:15" => 2.25
     */
    private static function convertTimeToDecimal($time)
    {
        if (strpos($time, ':') === false) {
            return (float)$time;
        }

        $parts = explode(':', $time);
        $hours = (int)$parts[0];
        $minutes = isset($parts[1]) ? (int)$parts[1] : 0;
        
        return round($hours + ($minutes / 60), 2);
    }

    /**
     * Convert decimal hours to HH:MM format for display
     * Examples: 1.0 => "1:00", 1.5 => "1:30", 2.25 => "2:15"
     */
    public function getFormattedBreakHoursAttribute()
    {
        return $this->formatDecimalToTime($this->break_hours);
    }

    public function getFormattedTotalHoursAttribute()
    {
        return $this->formatDecimalToTime($this->total_hours_rendered);
    }

    public function getFormattedOvertimeHoursAttribute()
    {
        return $this->formatDecimalToTime($this->overtime_hours);
    }

    private function formatDecimalToTime($decimal)
    {
        if (empty($decimal)) {
            return null;
        }

        $hours = floor($decimal);
        $minutes = round(($decimal - $hours) * 60);
        
        return sprintf('%d:%02d', $hours, $minutes);
    }
}