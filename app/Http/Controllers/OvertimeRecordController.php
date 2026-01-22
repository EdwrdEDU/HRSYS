<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\OvertimeRecord;
use Illuminate\Http\Request;

class OvertimeRecordController extends Controller
{
    public function index(Request $request, Employee $employee)
    {
        $query = $employee->overtimeRecords();

        // Filter by date if search is provided
        if ($request->has('search') && $request->search) {
            $query->whereDate('date', $request->search);
        }

        // Filter by approval status if requested
        if ($request->has('status') && $request->status) {
            switch($request->status) {
                case 'approved':
                    $query->whereNotNull('actual_accomplishment')
                          ->where('actual_accomplishment', '!=', '');
                    break;
                case 'pending':
                    $query->where(function($q) {
                        $q->whereNull('actual_accomplishment')
                          ->orWhere('actual_accomplishment', '=', '');
                    });
                    break;
            }
        }

        $records = $query->orderBy('date', 'desc')->paginate(15);
        
        // Calculate total approved overtime
        $approvedTotalRaw = $employee->overtimeRecords()
            ->whereNotNull('actual_accomplishment')
            ->where('actual_accomplishment', '!=', '')
            ->sum('overtime_hours') ?? 0;
        
        // Calculate total subtracted
        $totalSubtracted = \App\Models\OvertimeSubtraction::whereHas('overtimeRecord', function($query) use ($employee) {
            $query->where('employee_id', $employee->id);
        })->sum('hours_subtracted') ?? 0;
        
        // Available approved hours = approved - subtracted
        $approvedTotal = $approvedTotalRaw - $totalSubtracted;
            
        // Calculate totals for pending overtime
        $pendingTotal = $employee->overtimeRecords()
            ->where(function($query) {
                $query->whereNull('actual_accomplishment')
                      ->orWhere('actual_accomplishment', '=', '');
            })
            ->sum('overtime_hours') ?? 0;
        
        return view('overtime.index', compact('employee', 'records', 'approvedTotal', 'pendingTotal', 'totalSubtracted', 'approvedTotalRaw'));
    }

    public function create(Employee $employee)
    {
        return view('overtime.create', compact('employee'));
    }

    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'time_in' => 'nullable|string',
            'time_out' => 'nullable|string',
            'break_hours' => 'nullable|numeric|min:0',
            'total_hours_rendered' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
            'purpose_deliverables' => 'nullable|string',
            'actual_accomplishment' => 'nullable|string',
        ]);

        // Convert empty strings to null for auto-calculation
        $validated['break_hours'] = $this->convertToNull($validated['break_hours'] ?? null);
        $validated['total_hours_rendered'] = $this->convertToNull($validated['total_hours_rendered'] ?? null);
        $validated['overtime_hours'] = $this->convertToNull($validated['overtime_hours'] ?? null);

        $employee->overtimeRecords()->create($validated);

        return redirect()->route('overtime.index', $employee)
            ->with('success', 'Overtime record created successfully.');
    }

    public function edit(Employee $employee, OvertimeRecord $overtimeRecord)
    {
        // Ensure the overtime record belongs to this employee
        if ($overtimeRecord->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('overtime.edit', compact('employee', 'overtimeRecord'));
    }

    public function update(Request $request, Employee $employee, OvertimeRecord $overtimeRecord)
    {
        // Ensure the overtime record belongs to this employee
        if ($overtimeRecord->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'date' => 'nullable|date',
            'time_in' => 'nullable|string',
            'time_out' => 'nullable|string',
            'break_hours' => 'nullable|numeric|min:0',
            'total_hours_rendered' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
            'purpose_deliverables' => 'nullable|string',
            'actual_accomplishment' => 'nullable|string',
        ]);

        // Convert empty strings to null for auto-calculation
        $validated['break_hours'] = $this->convertToNull($validated['break_hours'] ?? null);
        $validated['total_hours_rendered'] = $this->convertToNull($validated['total_hours_rendered'] ?? null);
        $validated['overtime_hours'] = $this->convertToNull($validated['overtime_hours'] ?? null);

        $overtimeRecord->update($validated);

        return redirect()->route('overtime.index', $employee)
            ->with('success', 'Overtime record updated successfully.');
    }

    public function destroy(Employee $employee, OvertimeRecord $overtimeRecord)
    {
        // Ensure the overtime record belongs to this employee
        if ($overtimeRecord->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $overtimeRecord->delete();

        return redirect()->route('overtime.index', $employee)
            ->with('success', 'Overtime record deleted successfully.');
    }

    /**
     * Show form to subtract hours from total approved overtime
     */
    public function subtractForm(Employee $employee)
    {
        // Calculate total approved overtime
        $totalApprovedHours = $employee->overtimeRecords()
            ->whereNotNull('actual_accomplishment')
            ->where('actual_accomplishment', '!=', '')
            ->sum('overtime_hours') ?? 0;

        // Get total already subtracted
        $totalSubtracted = \App\Models\OvertimeSubtraction::whereHas('overtimeRecord', function($query) use ($employee) {
            $query->where('employee_id', $employee->id);
        })->sum('hours_subtracted') ?? 0;

        $availableHours = $totalApprovedHours - $totalSubtracted;

        return view('overtime.subtract', compact('employee', 'totalApprovedHours', 'totalSubtracted', 'availableHours'));
    }

    /**
     * Subtract hours from total approved overtime
     */
    public function subtract(Request $request, Employee $employee)
    {
        // Calculate available hours
        $totalApprovedHours = $employee->overtimeRecords()
            ->whereNotNull('actual_accomplishment')
            ->where('actual_accomplishment', '!=', '')
            ->sum('overtime_hours') ?? 0;

        $totalSubtracted = \App\Models\OvertimeSubtraction::whereHas('overtimeRecord', function($query) use ($employee) {
            $query->where('employee_id', $employee->id);
        })->sum('hours_subtracted') ?? 0;

        $availableHours = $totalApprovedHours - $totalSubtracted;

        $validated = $request->validate([
            'hours_to_subtract' => 'required|numeric|min:0.01|max:' . $availableHours,
            'subtraction_reason' => 'required|string|max:500',
            'subtraction_date' => 'required|date|before_or_equal:today',
        ]);

        // Create a general subtraction record (not tied to specific overtime record)
        // We'll attach it to the employee's first approved overtime record as a placeholder
        $firstApprovedRecord = $employee->overtimeRecords()
            ->whereNotNull('actual_accomplishment')
            ->where('actual_accomplishment', '!=', '')
            ->orderBy('date')
            ->first();

        if (!$firstApprovedRecord) {
            return redirect()->route('overtime.index', $employee)
                ->with('error', 'No approved overtime records found.');
        }

        $firstApprovedRecord->subtractions()->create([
            'hours_subtracted' => $validated['hours_to_subtract'],
            'reason' => $validated['subtraction_reason'],
            'subtraction_date' => $validated['subtraction_date'],
            'subtracted_by' => auth()->id(),
        ]);

        $newAvailableHours = $availableHours - $validated['hours_to_subtract'];

        return redirect()->route('overtime.index', $employee)
            ->with('success', "Successfully subtracted {$validated['hours_to_subtract']} hours from total approved overtime. Available hours: {$newAvailableHours} hrs.");
    }

    /**
     * Delete a subtraction record (undo subtraction)
     */
    public function deleteSubtraction(Employee $employee, $subtractionId)
    {
        $subtraction = \App\Models\OvertimeSubtraction::findOrFail($subtractionId);

        // Verify this subtraction belongs to the employee's overtime records
        if ($subtraction->overtimeRecord->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $hours = $subtraction->hours_subtracted;
        $subtraction->delete();

        return redirect()->route('overtime.index', $employee)
            ->with('success', "Subtraction of {$hours} hours has been removed. These hours are now available again.");
    }

    /**
     * Helper method to convert empty strings and zeros to null
     * This allows the model's boot methods to auto-calculate values
     */
    private function convertToNull($value)
    {
        // Return null if empty string, null, or "0" string
        if ($value === null || $value === '' || $value === '0') {
            return null;
        }
        
        // Return null if numeric zero
        if (is_numeric($value) && (float)$value == 0) {
            return null;
        }
        
        return $value;
    }
}