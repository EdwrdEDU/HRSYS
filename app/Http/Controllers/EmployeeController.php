<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\OvertimeRecord;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function index(Request $request, Employee $employee)
    {
        $query = $employee->overtimeRecords();

        // Filter by date (search)
        if ($request->filled('search')) {
            $query->whereDate('date', $request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->whereNotNull('actual_accomplishment')
                    ->where('actual_accomplishment', '!=', '');
            } elseif ($request->status === 'pending') {
                $query->where(function($q) {
                    $q->whereNull('actual_accomplishment')
                    ->orWhere('actual_accomplishment', '=', '');
                });
            }
        }

        $records = $query->orderBy('date', 'desc')->paginate(15);

        // Calculate totals (always calculate for all records, not filtered)
        $approvedTotal = $employee->overtimeRecords()
            ->whereNotNull('actual_accomplishment')
            ->where('actual_accomplishment', '!=', '')
            ->sum('overtime_hours');

        $pendingTotal = $employee->overtimeRecords()
            ->where(function($q) {
                $q->whereNull('actual_accomplishment')
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
        // Log the raw input FIRST
        \Log::info('Raw Input Received', [
            'all_input' => $request->all(),
            'time_in' => $request->input('time_in'),
            'time_out' => $request->input('time_out'),
            'total_hours_rendered' => $request->input('total_hours_rendered'),
            'overtime_hours' => $request->input('overtime_hours'),
        ]);

        $validated = $request->validate([
            'date' => 'required|date',
            'time_in' => 'required|string',
            'time_out' => 'required|string',
            'break_hours' => 'nullable|numeric|min:0',
            'total_hours_rendered' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'purpose_deliverables' => 'nullable|string',
            'actual_accomplishment' => 'nullable|string',
        ]);

        \Log::info('After Validation', $validated);

        $validated['employee_id'] = $employee->id;

        // Calculate hours from time in/out
        $calculated = $this->calculateHours(
            $validated['date'],
            $validated['time_in'],
            $validated['time_out'],
            $validated['break_hours'] ?? 0
        );

        \Log::info('Calculated Values', $calculated);

        // Handle Total Hours Rendered
        if (empty($validated['total_hours_rendered']) || $validated['total_hours_rendered'] == '') {
            // If blank, check if calculated hours is less than 8
            if ($calculated['total_hours'] < 8) {
                $validated['total_hours_rendered'] = 8; // Minimum 8 hours
                \Log::info('Using minimum 8 hours (calculated was less)');
            } else {
                $validated['total_hours_rendered'] = $calculated['total_hours'];
                \Log::info('Using calculated total_hours_rendered: ' . $calculated['total_hours']);
            }
        } else {
            \Log::info('Using user-provided total_hours_rendered: ' . $validated['total_hours_rendered']);
        }

        // Handle Overtime Hours - calculate based on final Total Hours Rendered
        $finalTotalHours = (float)$validated['total_hours_rendered'];
        
        if (empty($validated['overtime_hours']) || $validated['overtime_hours'] == '') {
            $standardHours = 8;
            $validated['overtime_hours'] = max(0, round($finalTotalHours - $standardHours, 2));
            \Log::info('Calculated overtime_hours: ' . $validated['overtime_hours'] . ' (from total hours: ' . $finalTotalHours . ')');
        } else {
            \Log::info('Using user-provided overtime_hours: ' . $validated['overtime_hours']);
        }

        // Normalize time format
        $validated['time_in'] = $calculated['time_in_normalized'];
        $validated['time_out'] = $calculated['time_out_normalized'];

        \Log::info('Final Data to Store', $validated);

        OvertimeRecord::create($validated);

        return redirect()->route('overtime.index', $employee)
            ->with('success', 'Overtime record created successfully.');
    }

    public function edit(Employee $employee, OvertimeRecord $overtimeRecord)
    {
        return view('overtime.edit', compact('employee', 'overtimeRecord'));
    }

    public function update(Request $request, Employee $employee, OvertimeRecord $overtimeRecord)
    {
        \Log::info('Raw Update Input', [
            'all_input' => $request->all(),
            'total_hours_rendered' => $request->input('total_hours_rendered'),
            'overtime_hours' => $request->input('overtime_hours'),
        ]);

        $validated = $request->validate([
            'date' => 'required|date',
            'time_in' => 'required|string',
            'time_out' => 'required|string',
            'break_hours' => 'nullable|numeric|min:0',
            'total_hours_rendered' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'purpose_deliverables' => 'nullable|string',
            'actual_accomplishment' => 'nullable|string',
        ]);

        // Calculate hours
        $calculated = $this->calculateHours(
            $validated['date'],
            $validated['time_in'],
            $validated['time_out'],
            $validated['break_hours'] ?? 0
        );

        \Log::info('Calculated Values for Update', $calculated);

        // Handle Total Hours Rendered
        if (empty($validated['total_hours_rendered']) || $validated['total_hours_rendered'] == 0 || $validated['total_hours_rendered'] == '') {
            // If blank/0, recalculate with minimum 8 hours rule
            if ($calculated['total_hours'] < 8) {
                $validated['total_hours_rendered'] = 8;
                \Log::info('Recalculated: Using minimum 8 hours');
            } else {
                $validated['total_hours_rendered'] = $calculated['total_hours'];
                \Log::info('Recalculated total_hours_rendered: ' . $calculated['total_hours']);
            }
        } else {
            \Log::info('Keeping user-provided total_hours_rendered: ' . $validated['total_hours_rendered']);
        }

        // Handle Overtime Hours
        $finalTotalHours = (float)$validated['total_hours_rendered'];
        
        if (empty($validated['overtime_hours']) || $validated['overtime_hours'] == 0 || $validated['overtime_hours'] == '') {
            $standardHours = 8;
            $validated['overtime_hours'] = max(0, round($finalTotalHours - $standardHours, 2));
            \Log::info('Recalculated overtime_hours: ' . $validated['overtime_hours']);
        } else {
            \Log::info('Keeping user-provided overtime_hours: ' . $validated['overtime_hours']);
        }

        // Normalize time format
        $validated['time_in'] = $calculated['time_in_normalized'];
        $validated['time_out'] = $calculated['time_out_normalized'];

        \Log::info('Final Update Data', $validated);

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
}