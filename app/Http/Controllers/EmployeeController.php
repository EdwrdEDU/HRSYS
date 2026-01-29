<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees
     */
    public function index(Request $request)
    {
        $query = Employee::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('section', 'like', "%{$search}%");
            });
        }

        // Filter by section
        if ($request->has('section') && $request->section) {
            $query->where('section', $request->section);
        }

        $employees = $query->orderBy('last_name')->orderBy('first_name')->paginate(15);
        
        // Calculate available overtime for each employee
        foreach ($employees as $employee) {
            // Get total approved overtime
            $approvedTotal = $employee->overtimeRecords()
                ->whereNotNull('actual_accomplishment')
                ->where('actual_accomplishment', '!=', '')
                ->sum('overtime_hours') ?? 0;
            
            // Get total subtracted
            $totalSubtracted = \App\Models\OvertimeSubtraction::whereHas('overtimeRecord', function($query) use ($employee) {
                $query->where('employee_id', $employee->id);
            })->sum('hours_subtracted') ?? 0;
            
            // Calculate available hours
            $employee->available_overtime = $approvedTotal - $totalSubtracted;
            $employee->total_approved = $approvedTotal;
            $employee->total_subtracted = $totalSubtracted;
        }
        
        // Get all unique sections for filter dropdown
        $sections = Employee::select('section')
            ->distinct()
            ->orderBy('section')
            ->pluck('section');

        return view('employees.index', compact('employees', 'sections'));
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'section' => 'required|string|max:255',
        ]);

        $employee = Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified employee
     */
    public function show(Employee $employee)
    {
        // Get overtime statistics
        $totalOvertimeHours = $employee->overtimeRecords()->sum('overtime_hours') ?? 0;
        $approvedOvertimeHours = $employee->overtimeRecords()
            ->whereNotNull('actual_accomplishment')
            ->where('actual_accomplishment', '!=', '')
            ->sum('overtime_hours') ?? 0;
        $pendingOvertimeHours = $employee->overtimeRecords()
            ->where(function($query) {
                $query->whereNull('actual_accomplishment')
                      ->orWhere('actual_accomplishment', '=', '');
            })
            ->sum('overtime_hours') ?? 0;

        // Get recent overtime records
        $recentOvertimeRecords = $employee->overtimeRecords()
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        return view('employees.show', compact(
            'employee',
            'totalOvertimeHours',
            'approvedOvertimeHours',
            'pendingOvertimeHours',
            'recentOvertimeRecords'
        ));
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'section' => 'required|string|max:255',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}