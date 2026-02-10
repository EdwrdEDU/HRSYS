@extends('layouts.app')

@section('title', 'Overtime Records')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    {{-- Header with back button --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <a href="{{ route('employees.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-medium mb-3 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Employees
                </a>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Overtime Records</h1>
                <p class="mt-2 text-gray-600">Managing overtime for <strong class="text-gray-900">{{ $employee->full_name }}</strong> • {{ $employee->section }}</p>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="backdrop-blur-sm bg-white/40 border border-white/60 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 font-medium">Approved Hours</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ floor($approvedTotal) }}:{{ str_pad(round(($approvedTotal - floor($approvedTotal)) * 60), 2, '0', STR_PAD_LEFT) }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">hours approved</p>
                    </div>
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="backdrop-blur-sm bg-white/40 border border-white/60 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 font-medium">Pending Hours</p>
                        <p class="text-2xl font-bold text-amber-600 mt-1">{{ floor($pendingTotal) }}:{{ str_pad(round(($pendingTotal - floor($pendingTotal)) * 60), 2, '0', STR_PAD_LEFT) }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">hours pending approval</p>
                    </div>
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions and Search --}}
        <div class="flex flex-col sm:flex-row gap-3 mb-8">
            @if($approvedTotal > 0)
                <a href="{{ route('overtime.subtract.form', $employee) }}"
                   class="flex items-center justify-center px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                    Subtract Hours
                </a>
            @endif

            <a href="{{ route('overtime.create', $employee) }}"
               class="flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Overtime
            </a>

            <button type="button"
                    onclick="openImportModal()"
                    class="flex items-center justify-center px-6 py-3 bg-white border border-indigo-200 text-indigo-700 font-semibold rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-3-3m3 3l3-3m6 5v2a2 2 0 01-2 2H7a2 2 0 01-2-2v-2"></path>
                </svg>
                Import Excel
            </button>

            <div class="flex-1 flex gap-2">
                <form method="GET" action="{{ route('overtime.index', $employee) }}" class="flex-1 flex gap-2">
                    <input type="date"
                           name="search"
                           value="{{ request('search') }}"
                           class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">

                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                        Search
                    </button>

                    @if(request('search'))
                        <a href="{{ route('overtime.index', $employee) }}"
                           class="px-6 py-3 bg-white border border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-all duration-200">
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Overtime Records Table --}}
        <div class="backdrop-blur-sm bg-white/40 border border-white/60 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-white/60">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">IN</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">OUT</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Break</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Rendered Hrs</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">OT</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Remarks</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/60">
                        @forelse($records as $record)
                            <tr class="hover:bg-white/30 transition-colors {{ $record->isApproved() ? 'bg-green-50/40' : '' }}">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $record->date ? $record->date->format('D, M d') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    @if($record->break_hours)
                                        {{ floor($record->break_hours) }}:{{ str_pad(round(($record->break_hours - floor($record->break_hours)) * 60), 2, '0', STR_PAD_LEFT) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    @if($record->total_hours_rendered)
                                        {{ floor($record->total_hours_rendered) }}:{{ str_pad(round(($record->total_hours_rendered - floor($record->total_hours_rendered)) * 60), 2, '0', STR_PAD_LEFT) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-indigo-600">
                                    @if($record->overtime_hours)
                                        {{ floor($record->overtime_hours) }}:{{ str_pad(round(($record->overtime_hours - floor($record->overtime_hours)) * 60), 2, '0', STR_PAD_LEFT) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($record->approval_status === 'Approved')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100/80 text-green-700 backdrop-blur-sm">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></path></svg>
                                            Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100/80 text-amber-700 backdrop-blur-sm">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></path></svg>
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                    {{ $record->remarks ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('overtime.edit', [$employee, $record]) }}"
                                           class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-medium text-sm transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </a>
                                        <button type="button"
                                                onclick="openDeleteOvertimeModal({{ $employee->id }}, {{ $record->id }}, '{{ $record->date ? $record->date->format('M d, Y') : 'N/A' }}')"
                                                class="inline-flex items-center text-red-600 hover:text-red-700 font-medium text-sm transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-gray-500 font-medium">No overtime records found.</p>
                                    <p class="text-gray-400 text-sm mt-1">Add your first overtime record to get started.</p>
                                    <p class="text-gray-400 text-sm mt-1">Add your first overtime record to get started.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $records->appends(request()->query())->links() }}
        </div>

        @php
            $allSubtractions = \App\Models\OvertimeSubtraction::whereHas('overtimeRecord', function($query) use ($employee) {
                $query->where('employee_id', $employee->id );
            })->orderBy('subtraction_date', 'desc')->get();
        @endphp

        @if($allSubtractions->count() > 0)
            <div class="mt-12">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Subtraction History</h3>
                <div class="backdrop-blur-sm bg-white/40 border border-white/60 rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-orange-50 to-red-50 border-b border-white/60">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Hours Subtracted</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Reason</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Recorded By</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/60">
                                @foreach($allSubtractions as $sub)
                                    <tr class="hover:bg-white/30 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                            {{ $sub->subtraction_date->format('D, M d') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-bold text-red-600">
                                            -{{ floor($sub->hours_subtracted) }}:{{ str_pad(round(($sub->hours_subtracted - floor($sub->hours_subtracted)) * 60), 2, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            {{ $sub->reason }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            @if($sub->subtractedBy)
                                                {{ $sub->subtractedBy->name }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button type="button"
                                                    onclick="openUndoModal({{ $employee->id }}, {{ $sub->id }}, '{{ $sub->hours_subtracted }}', '{{ $sub->subtraction_date->format('M d, Y') }}')"
                                                    class="inline-flex items-center text-red-600 hover:text-red-700 font-medium text-sm transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                </svg>
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

{{-- Import Overtime Modal --}}
<div id="importOvertimeModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeImportModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-3-3m3 3l3-3m6 5v2a2 2 0 01-2 2H7a2 2 0 01-2-2v-2" />
                    </svg>
                </div>

                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Import Overtime Records</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">Upload an Excel or CSV file with the following columns:</p>
                        <ul class="text-sm text-gray-600 mt-2 space-y-1 list-disc list-inside">
                            <li>DATE</li>
                            <li>IN (or TIME_IN)</li>
                            <li>OUT (or TIME_OUT)</li>
                            <li>BREAK (or BREAK_MINUTES)</li>
                            <li>OVERTIME (or OVERTIME_HOURS)</li>
                            <li>REMARKS</li>
                            <li>PURPOSE_DELIVERABLES</li>
                            <li>ACTUAL_ACCOMPLISHMENT</li>
                        </ul>
                        <p class="text-xs text-gray-500 mt-2">All columns are optional. Empty rows are skipped.</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('overtime.import', $employee) }}" method="POST" enctype="multipart/form-data" class="mt-6">
                @csrf
                <div class="flex flex-col gap-3">
                    <input type="file"
                           name="file"
                           accept=".xlsx,.xls,.csv"
                           class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                           required>
                </div>

                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm transition-colors">
                        Import File
                    </button>
                    <button type="button"
                            onclick="closeImportModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
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

// Import Overtime Modal Functions
function openImportModal() {
    document.getElementById('importOvertimeModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImportModal() {
    document.getElementById('importOvertimeModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteOvertimeModal();
        closeUndoModal();
        closeImportModal();
    }
});
</script>

@endsection