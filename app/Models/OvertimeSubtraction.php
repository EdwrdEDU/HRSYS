<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeSubtraction extends Model
{
    protected $fillable = [
        'overtime_record_id',
        'hours_subtracted',
        'reason',
        'subtraction_date',
        'subtracted_by',
    ];

    protected $casts = [
        'hours_subtracted' => 'decimal:2',
        'subtraction_date' => 'date',
    ];

    /**
     * Get the overtime record this subtraction belongs to
     */
    public function overtimeRecord(): BelongsTo
    {
        return $this->belongsTo(OvertimeRecord::class);
    }

    /**
     * Get the user who made the subtraction
     */
    public function subtractedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subtracted_by');
    }
}