@extends('layouts.app')

@section('title', 'Edit Overtime Record')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Edit Overtime Record</h3>
            <p class="mt-1 text-sm text-gray-600">
                Update overtime record for <strong>{{ $employee->full_name }}</strong>
            </p>
            <div class="mt-4 p-4 rounded-md {{ $overtimeRecord->isApproved() ? 'bg-green-50' : 'bg-yellow-50' }}">
                <p class="text-xs font-semibold {{ $overtimeRecord->isApproved() ? 'text-green-800' : 'text-yellow-800' }}">
                    Status: {{ $overtimeRecord->approval_status }}
                </p>
            </div>
            <div class="mt-4 p-4 bg-blue-50 rounded-md">
                <p class="text-xs text-blue-800">
                    <strong>Auto-calculation:</strong> Enter Time IN, Time OUT, and Break hours.
                    <br>• <strong>Total Hours Rendered:</strong> Calculated from times (minimum 8 hours if blank)
                    <br>• <strong>OT:</strong> Hours beyond 8 (e.g., if Total = 10, then OT = 2)
                </p>
            </div>
            <div class="mt-4 p-4 bg-blue-50 rounded-md">
                <p class="text-xs text-blue-800">
                    <strong>Auto-calculation:</strong> To recalculate, clear Total Hours and OT fields (or set to 0).
                </p>
            </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <form action="{{ route('overtime.update', [$employee, $overtimeRecord]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="shadow sm:rounded-md sm:overflow-hidden">
                    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6">
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="date" value="{{ old('date', $overtimeRecord->date ? $overtimeRecord->date->format('Y-m-d') : '') }}"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Time IN</label>
                                <input type="text" name="time_in" value="{{ old('time_in', $overtimeRecord->time_in) }}"
                                       placeholder="7:38 AM or 07:38"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <p class="mt-1 text-xs text-gray-500">Examples: 7:38 AM, 07:38, 7:38 am</p>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Time OUT</label>
                                <input type="text" name="time_out" value="{{ old('time_out', $overtimeRecord->time_out) }}"
                                       placeholder="6:38 PM or 18:38"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <p class="mt-1 text-xs text-gray-500">Examples: 6:38 PM, 18:38, 6:38 pm</p>
                            </div>

                            <div class="col-span-6 sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Break (hours)</label>
                                <input type="number" name="break_hours" value="{{ old('break_hours', $overtimeRecord->break_hours) }}" step="0.5" min="0"
                                       placeholder="e.g., 1"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Total Hours Rendered</label>
                                <input type="number" name="total_hours_rendered" value="{{ old('total_hours_rendered', $overtimeRecord->total_hours_rendered) }}" step="0.01" min="0"
                                       placeholder="Set to 0 to recalculate"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <p class="mt-1 text-xs text-gray-500">Set to 0 to auto-recalculate</p>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Overtime Hours</label>
                                <input type="number" name="overtime_hours" value="{{ old('overtime_hours', $overtimeRecord->overtime_hours) }}" step="0.01" min="0"
                                    placeholder="Set to 0 to recalculate"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <p class="mt-1 text-xs text-gray-500">Set to 0 to auto-recalculate</p>
                            </div>

                            <div class="col-span-6">
                                <label class="block text-sm font-medium text-gray-700">Remarks</label>
                                <textarea name="remarks" rows="2"
                                          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('remarks', $overtimeRecord->remarks) }}</textarea>
                            </div>

                            <div class="col-span-6 border-t pt-4">
                                <h4 class="text-md font-medium text-gray-900 mb-4">Approval Forms</h4>
                                <p class="text-xs text-gray-600 mb-4">Both forms must be filled for overtime to be counted as approved.</p>
                            </div>

                            <div class="col-span-6">
                                <label class="block text-sm font-medium text-gray-700">Purpose/Deliverables (Form A)</label>
                                <textarea name="purpose_deliverables" rows="3"
                                          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('purpose_deliverables', $overtimeRecord->purpose_deliverables) }}</textarea>
                            </div>

                            <div class="col-span-6">
                                <label class="block text-sm font-medium text-gray-700">Actual Accomplishment (Form B)</label>
                                <textarea name="actual_accomplishment" rows="3"
                                          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('actual_accomplishment', $overtimeRecord->actual_accomplishment) }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 space-x-2">
                        <a href="{{ route('overtime.index', $employee) }}" 
                           class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection