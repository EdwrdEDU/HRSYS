<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\OvertimeRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            'break_hours' => ['nullable', 'regex:/^(\d+(\.\d+)?|\d{1,3}:\d{2})$/'],
            'total_hours_rendered' => ['nullable', 'regex:/^(\d+(\.\d+)?|\d{1,3}:\d{2})$/'],
            'required_hours' => ['nullable', 'regex:/^(\d+(\.\d+)?|\d{1,3}:\d{2})$/'],
            'overtime_hours' => ['nullable', 'regex:/^(\d+(\.\d+)?|\d{1,3}:\d{2})$/'],
            'remarks' => 'nullable|string|max:1000',
            'purpose_deliverables' => 'nullable|string',
            'actual_accomplishment' => 'nullable|string',
        ]);

        // Convert empty strings to null for auto-calculation
        $validated['break_hours'] = $this->convertToNull($this->normalizeHoursInput($validated['break_hours'] ?? null));
        $validated['total_hours_rendered'] = $this->convertToNull($this->normalizeHoursInput($validated['total_hours_rendered'] ?? null));
        $validated['required_hours'] = $this->convertToNull($this->normalizeHoursInput($validated['required_hours'] ?? null));
        $validated['overtime_hours'] = $this->convertToNull($this->normalizeHoursInput($validated['overtime_hours'] ?? null));

        if ($validated['required_hours'] === null) {
            $validated['required_hours'] = 8;
        }

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
            'break_hours' => ['nullable', 'regex:/^(\d+(\.\d+)?|\d{1,3}:\d{2})$/'],
            'total_hours_rendered' => ['nullable', 'regex:/^(\d+(\.\d+)?|\d{1,3}:\d{2})$/'],
            'required_hours' => ['nullable', 'regex:/^(\d+(\.\d+)?|\d{1,3}:\d{2})$/'],
            'overtime_hours' => ['nullable', 'regex:/^(\d+(\.\d+)?|\d{1,3}:\d{2})$/'],
            'remarks' => 'nullable|string|max:1000',
            'purpose_deliverables' => 'nullable|string',
            'actual_accomplishment' => 'nullable|string',
        ]);

        // Convert empty strings to null for auto-calculation
        $validated['break_hours'] = $this->convertToNull($this->normalizeHoursInput($validated['break_hours'] ?? null));
        $validated['total_hours_rendered'] = $this->convertToNull($this->normalizeHoursInput($validated['total_hours_rendered'] ?? null));
        $validated['required_hours'] = $this->convertToNull($this->normalizeHoursInput($validated['required_hours'] ?? null));
        $validated['overtime_hours'] = $this->convertToNull($this->normalizeHoursInput($validated['overtime_hours'] ?? null));

        if ($validated['required_hours'] === null) {
            $validated['required_hours'] = 8;
        }

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

    public function import(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $file = $validated['file'];
        $reader = \PHPExcel_IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadDataOnly(true);
        $workbook = $reader->load($file->getRealPath());
        $sheet = $workbook->getActiveSheet();

        $rows = $sheet->toArray(null, true, true, true);
        if (count($rows) < 2) {
            return redirect()->route('overtime.index', $employee)
                ->with('error', 'The uploaded file has no data rows to import.');
        }

        $headerRow = array_shift($rows);
        $columnFieldMap = $this->buildImportColumnMap($headerRow);

        if (empty($columnFieldMap)) {
            return redirect()->route('overtime.index', $employee)
                ->with('error', 'No recognizable column headers were found in the file.');
        }

        $imported = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($rows as $row) {
            $data = $this->buildImportRowData($row, $columnFieldMap);

            if (!$this->rowHasData($data)) {
                $skipped++;
                continue;
            }

            $validator = Validator::make($data, [
                'date' => 'nullable|date',
                'time_in' => 'nullable|string',
                'time_out' => 'nullable|string',
                'break_hours' => ['nullable', 'regex:/^(\d+(\.\d+)?|\d{1,3}:\d{2})$/'],
                'total_hours_rendered' => ['nullable', 'regex:/^(\d+(\.\d+)?|\d{1,3}:\d{2})$/'],
                'required_hours' => ['nullable', 'regex:/^(\d+(\.\d+)?|\d{1,3}:\d{2})$/'],
                'overtime_hours' => ['nullable', 'regex:/^(\d+(\.\d+)?|\d{1,3}:\d{2})$/'],
                'remarks' => 'nullable|string|max:1000',
                'purpose_deliverables' => 'nullable|string',
                'actual_accomplishment' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $failed++;
                continue;
            }

            $data['break_hours'] = $this->convertToNull($this->normalizeHoursInput($data['break_hours'] ?? null));
            $data['total_hours_rendered'] = $this->convertToNull($this->normalizeHoursInput($data['total_hours_rendered'] ?? null));
            $data['required_hours'] = $this->convertToNull($this->normalizeHoursInput($data['required_hours'] ?? null));
            $data['overtime_hours'] = $this->convertToNull($this->normalizeHoursInput($data['overtime_hours'] ?? null));

            if ($data['required_hours'] === null) {
                $data['required_hours'] = 8;
            }

            $employee->overtimeRecords()->create($data);
            $imported++;
        }

        $message = "Imported {$imported} overtime record(s). Skipped {$skipped} empty row(s).";
        if ($failed > 0) {
            $message .= " {$failed} row(s) failed validation.";
        }

        return redirect()->route('overtime.index', $employee)
            ->with('success', $message);
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
        // Return null if empty string or null (leave zeros intact)
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Normalize hours input to decimal if in HH:MM format
     */
    private function normalizeHoursInput($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (is_string($value) && strpos($value, ':') !== false) {
            $parts = explode(':', $value, 2);
            $hours = (int) $parts[0];
            $minutes = isset($parts[1]) ? (int) $parts[1] : 0;

            return round($hours + ($minutes / 60), 2);
        }

        return $value;
    }

    private function buildImportColumnMap(array $headerRow): array
    {
        $headerMap = [];
        $aliases = [
            'DATE' => 'date',
            'IN' => 'time_in',
            'TIME_IN' => 'time_in',
            'OUT' => 'time_out',
            'TIME_OUT' => 'time_out',
            'BREAK' => 'break_hours',
            'BREAK_HOURS' => 'break_hours',
            'BREAK_MINUTES' => 'break_minutes',
            'TOTAL_HOURS_RENDERED' => 'total_hours_rendered',
            'REQUIRED_HOURS' => 'required_hours',
            'OVERTIME' => 'overtime_hours',
            'OVERTIME_HOURS' => 'overtime_hours',
            'REMARKS' => 'remarks',
            'PURPOSE_DELIVERABLES' => 'purpose_deliverables',
            'ACTUAL_ACCOMPLISHMENT' => 'actual_accomplishment',
        ];

        foreach ($headerRow as $column => $header) {
            $normalized = $this->normalizeHeader($header);
            if ($normalized && isset($aliases[$normalized])) {
                $headerMap[$column] = $aliases[$normalized];
            }
        }

        return $headerMap;
    }

    private function buildImportRowData(array $row, array $columnFieldMap): array
    {
        $data = [];

        foreach ($columnFieldMap as $column => $field) {
            $value = $row[$column] ?? null;

            if (is_string($value)) {
                $value = trim($value);
            }

            if ($field === 'break_minutes') {
                if (!array_key_exists('break_hours', $data) && is_numeric($value)) {
                    $data['break_hours'] = round(((float) $value) / 60, 2);
                }
                continue;
            }

            if ($field === 'date') {
                $value = $this->parseExcelDateValue($value);
            }

            if ($field === 'time_in' || $field === 'time_out') {
                $value = $this->parseExcelTimeValue($value);
            }

            if ($value === '') {
                $value = null;
            }

            $data[$field] = $value;
        }

        return $data;
    }

    private function parseExcelDateValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return \PHPExcel_Shared_Date::ExcelToPHPObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseExcelTimeValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return \PHPExcel_Shared_Date::ExcelToPHPObject($value)->format('H:i');
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception $e) {
            return is_string($value) ? $value : null;
        }
    }

    private function normalizeHeader($header): string
    {
        if ($header === null) {
            return '';
        }

        $normalized = strtoupper(trim((string) $header));
        $normalized = preg_replace('/\s+/', '_', $normalized);

        return $normalized;
    }

    private function rowHasData(array $data): bool
    {
        foreach ($data as $value) {
            if (is_string($value) && trim($value) !== '') {
                return true;
            }

            if (is_numeric($value) && (float) $value !== 0.0) {
                return true;
            }

            if (is_bool($value)) {
                return true;
            }
        }

        return false;
    }
}