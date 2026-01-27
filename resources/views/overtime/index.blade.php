@extends('layouts.app')

@section('title', 'Overtime Records')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-3xl font-semibold text-gray-900">Overtime Records</h1>
            <p class="mt-2 text-sm text-gray-700">
                Managing overtime for:
                <strong>{{ $employee->full_name }}</strong> ({{ $employee->department }})
            </p>

            <div class="mt-2 flex gap-4">
                <span class="text-sm text-green-600 font-semibold">
                    Approved: {{ number_format($approvedTotal, 2) }} hrs
                </span>
                <span class="text-sm text-yellow-600 font-semibold">
                    Pending: {{ number_format($pendingTotal, 2) }} hrs
                </span>
            </div>
        </div>

        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none space-x-2">
            <a href="{{ route('employees.index') }}"
               class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Back to Employees
            </a>

            @if($approvedTotal > 0)
                <a href="{{ route('overtime.subtract.form', $employee) }}"
                   class="inline-flex items-center rounded-md border border-orange-600 bg-white px-4 py-2 text-sm font-medium text-orange-600 shadow-sm hover:bg-orange-50">
                    Subtract Hours
                </a>
            @endif

            <a href="{{ route('overtime.create', $employee) }}"
               class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                Add Overtime
            </a>
        </div>
    </div>

    {{-- Search --}}
    <div class="mt-6">
        <form method="GET" action="{{ route('overtime.index', $employee) }}" class="flex gap-2">
            <input type="date"
                   name="search"
                   value="{{ request('search') }}"
                   class="flex-1 rounded-md border-gray-300 shadow-sm pl-3 focus:ring-indigo-500 focus:border-indigo-500">

            <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Search
            </button>

            @if(request('search'))
                <a href="{{ route('overtime.index', $employee) }}"
                   class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 text-sm font-medium shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- OVERTIME TABLE --}}
    <div class="mt-8">
        <div class="shadow ring-1 ring-black ring-opacity-5 rounded-lg">
            {{-- 6 rows visible then scroll --}}
            <div class="overflow-y-auto" style="max-height: 300px;">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="sticky top-0 bg-gray-50 z-10">
                        <tr>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">IN</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">OUT</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Break</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Hrs</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">OT</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Remarks</th>
                            <th class="sticky top-0 bg-gray-50 py-3.5 pl-3 pr-4 sm:pr-6 text-right text-sm font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($records as $record)
                            <tr class="{{ $record->isApproved() ? 'bg-green-50' : '' }}">
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                    {{ $record->date ? $record->date->format('D, F d, Y') : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('h:i A') : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ $record->break_hours ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    @if($record->total_hours_rendered)
                                        {{ $record->total_hours_rendered }} {{ $record->total_hours_rendered == 1 ? 'hr' : 'hrs' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                                    @if($record->overtime_hours)
                                        {{ $record->overtime_hours }} {{ $record->overtime_hours == 1 ? 'hr' : 'hrs' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    @if($record->approval_status === 'Approved')
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-green-100 text-green-800">
                                            ✓ Approved
                                        </span>
                                    @elseif(str_contains($record->approval_status, 'Form A Only'))
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-yellow-100 text-yellow-800">
                                            Pending (Form A Only)
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    {{ $record->remarks ?? '-' }}
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <a href="{{ route('overtime.edit', [$employee, $record]) }}"
                                       class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        Edit
                                    </a>
                                    <button type="button"
                                            onclick="openDeleteOvertimeModal({{ $employee->id }}, {{ $record->id }}, '{{ $record->date ? $record->date->format('M d, Y') : 'N/A' }}')"
                                            class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-8 text-center text-sm text-gray-500">
                                    No overtime records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $records->appends(request()->query())->links() }}
    </div>

    @php
        $allSubtractions = \App\Models\OvertimeSubtraction::whereHas('overtimeRecord', function($query) use ($employee) {
            $query->where('employee_id', $employee->id );
        })->orderBy('subtraction_date', 'desc')->get();
    @endphp

    @if($allSubtractions->count() > 0)
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Subtraction History</h3>
            <div class="shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                {{-- 3 rows visible then scroll --}}
                <div class="overflow-y-auto" style="max-height: 160px;">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Hours Subtracted</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Reason</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Recorded By</th>
                                <th class="sticky top-0 bg-gray-50 py-3.5 pl-3 pr-4 sm:pr-6 text-right text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($allSubtractions as $sub)
                                <tr>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                        {{ $sub->subtraction_date->format('D, F d, Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-red-600">
                                        -{{ $sub->hours_subtracted }} hrs
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500">
                                        {{ $sub->reason }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        @if($sub->subtractedBy)
                                            {{ $sub->subtractedBy->name }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <button type="button"
                                                onclick="openUndoModal({{ $employee->id }}, {{ $sub->id }}, '{{ $sub->hours_subtracted }}', '{{ $sub->subtraction_date->format('M d, Y') }}')"
                                                class="text-red-600 hover:text-red-900">
                                            Undo
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>

{{-- Delete Overtime Record Modal --}}
<div id="deleteOvertimeModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDeleteOvertimeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Overtime Record</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to delete the overtime record for <strong id="overtimeDate" class="text-gray-900"></strong>?
                        </p>
                        <div class="mt-3 bg-red-50 border-l-4 border-red-400 p-3 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        This action cannot be undone. This will permanently delete the overtime record.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-2">
                <form id="deleteOvertimeForm" method="POST" action="" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Record
                    </button>
                </form>
                <button type="button" 
                        onclick="closeDeleteOvertimeModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Undo Subtraction Modal --}}
<div id="undoModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeUndoModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                </div>

                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Undo Subtraction</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to undo the subtraction from <strong id="subtractionDate" class="text-gray-900"></strong>?
                        </p>
                        <div class="mt-3 bg-orange-50 border-l-4 border-orange-400 p-3 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-orange-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-orange-700">
                                        <strong id="undoHours" class="font-semibold"></strong> will be added back to the available overtime hours.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-2">
                <form id="undoForm" method="POST" action="" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:w-auto sm:text-sm transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        Undo Subtraction
                    </button>
                </form>
                <button type="button" 
                        onclick="closeUndoModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Delete Overtime Modal Functions
function openDeleteOvertimeModal(employeeId, recordId, date) {
    document.getElementById('overtimeDate').textContent = date;
    document.getElementById('deleteOvertimeForm').action = `/employees/${employeeId}/overtime/${recordId}`;
    document.getElementById('deleteOvertimeModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeleteOvertimeModal() {
    document.getElementById('deleteOvertimeModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Undo Subtraction Modal Functions
function openUndoModal(employeeId, subtractionId, hours, date) {
    document.getElementById('subtractionDate').textContent = date;
    document.getElementById('undoHours').textContent = hours + ' hrs';
    document.getElementById('undoForm').action = `/employees/${employeeId}/overtime/subtraction/${subtractionId}`;
    document.getElementById('undoModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeUndoModal() {
    document.getElementById('undoModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteOvertimeModal();
        closeUndoModal();
    }
});
</script>

@endsection
