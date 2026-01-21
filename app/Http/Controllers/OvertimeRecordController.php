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
        
        // Calculate totals for approved overtime
        $approvedTotal = $employee->overtimeRecords()
            ->whereNotNull('actual_accomplishment')
            ->where('actual_accomplishment', '!=', '')
            ->sum('overtime_hours') ?? 0;
            
        // Calculate totals for pending overtime
        $pendingTotal = $employee->overtimeRecords()
            ->where(function($query) {
                $query->whereNull('actual_accomplishment')
                      ->orWhere('actual_accomplishment', '=', '');
            })
            ->sum('overtime_hours') ?? 0;
        
        return view('overtime.index', compact('employee', 'records', 'approvedTotal', 'pendingTotal'));
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