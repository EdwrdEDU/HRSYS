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

        if ($request->has('search')) {
            $query->whereDate('date', $request->search);
        }

        // Filter by approval status if requested
        if ($request->has('status')) {
            switch($request->status) {
                case 'approved':
                    $query->whereNotNull('purpose_deliverables')
                          ->where('purpose_deliverables', '!=', '')
                          ->whereNotNull('actual_accomplishment')
                          ->where('actual_accomplishment', '!=', '');
                    break;
                case 'pending':
                    $query->where(function($q) {
                        $q->whereNull('purpose_deliverables')
                          ->orWhere('purpose_deliverables', '=', '')
                          ->orWhereNull('actual_accomplishment')
                          ->orWhere('actual_accomplishment', '=', '');
                    });
                    break;
            }
        }

        $records = $query->orderBy('date', 'desc')->paginate(15);
        
        // Calculate totals
        $approvedTotal = $employee->overtimeRecords()
            ->whereNotNull('purpose_deliverables')
            ->where('purpose_deliverables', '!=', '')
            ->whereNotNull('actual_accomplishment')
            ->where('actual_accomplishment', '!=', '')
            ->sum('overtime_hours');
            
        $pendingTotal = $employee->overtimeRecords()
            ->where(function($query) {
                $query->whereNull('purpose_deliverables')
                      ->orWhere('purpose_deliverables', '=', '')
                      ->orWhereNull('actual_accomplishment')
                      ->orWhere('actual_accomplishment', '=', '');
            })
            ->sum('overtime_hours');
        
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
            'remarks' => 'nullable|string',
            'purpose_deliverables' => 'nullable|string',
            'actual_accomplishment' => 'nullable|string',
        ]);

        // Convert empty strings and zeros to null for auto-calculation
        $validated['break_hours'] = $this->nullIfEmpty($validated['break_hours'] ?? null);
        $validated['total_hours_rendered'] = $this->nullIfEmpty($validated['total_hours_rendered'] ?? null);
        $validated['overtime_hours'] = $this->nullIfEmpty($validated['overtime_hours'] ?? null);

        $employee->overtimeRecords()->create($validated);

        return redirect()->route('overtime.index', $employee)
            ->with('success', 'Overtime record created successfully.');
    }

    public function edit(Employee $employee, OvertimeRecord $overtimeRecord)
    {
        return view('overtime.edit', compact('employee', 'overtimeRecord'));
    }

    public function update(Request $request, Employee $employee, OvertimeRecord $overtimeRecord)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'time_in' => 'nullable|string',
            'time_out' => 'nullable|string',
            'break_hours' => 'nullable|numeric|min:0',
            'total_hours_rendered' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'purpose_deliverables' => 'nullable|string',
            'actual_accomplishment' => 'nullable|string',
        ]);

        // Convert empty strings and zeros to null for auto-calculation
        $validated['break_hours'] = $this->nullIfEmpty($validated['break_hours'] ?? null);
        $validated['total_hours_rendered'] = $this->nullIfEmpty($validated['total_hours_rendered'] ?? null);
        $validated['overtime_hours'] = $this->nullIfEmpty($validated['overtime_hours'] ?? null);

        $overtimeRecord->update($validated);

        return redirect()->route('overtime.index', $employee)
            ->with('success', 'Overtime record updated successfully.');
    }

    public function destroy(Employee $employee, OvertimeRecord $overtimeRecord)
    {
        $overtimeRecord->delete();

        return redirect()->route('overtime.index', $employee)
            ->with('success', 'Overtime record deleted successfully.');
    }

    public function importForm(Employee $employee)
    {
        return view('overtime.import', compact('employee'));
    }

    public function import(Request $request, Employee $employee)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(
                new \App\Imports\OvertimeRecordsImport($employee), 
                $request->file('file')
            );

            return redirect()->route('overtime.index', $employee)
                ->with('success', 'Overtime records imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to convert empty strings, zeros, and null to null
     */
    private function nullIfEmpty($value)
    {
        // Check for null, empty string, string '0', or numeric 0
        if ($value === null || $value === '' || $value === '0' || $value === 0 || $value === 0.0) {
            return null;
        }
        return $value;
    }
}