@extends('layouts.app')

@section('title', 'Overtime Records')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-3xl font-semibold text-gray-900">Overtime Records</h1>
            <p class="mt-2 text-sm text-gray-700">
                Managing overtime for: <strong>{{ $employee->full_name }}</strong> ({{ $employee->department }})
            </p>
            <div class="mt-2 flex gap-4">
                <span class="text-sm text-green-600 font-semibold">
                    Approved: {{ $approvedTotal }} {{ $approvedTotal == 1 ? 'hr' : 'hrs' }}
                </span>
                <span class="text-sm text-yellow-600 font-semibold">
                    Pending: {{ $pendingTotal }} {{ $pendingTotal == 1 ? 'hr' : 'hrs' }}
                </span>
            </div>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none space-x-2">
            <a href="{{ route('employees.index') }}" 
               class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Back to Employees
            </a>
            <a href="{{ url('employees/' . $employee->id . '/overtime/create') }}" 
               class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                Add Overtime
            </a>
        </div>
    </div>

    {{-- Search/Filter Section --}}
    <div class="mt-6 flex gap-2">
        <form method="GET" action="{{ url('employees/' . $employee->id . '/overtime') }}" class="flex gap-2 flex-1">
            <input type="date" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Filter by date"
                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Search
            </button>
            @if(request('search') || request('status'))
                <a href="{{ url('employees/' . $employee->id . '/overtime') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table Section --}}
    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">IN</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">OUT</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Break</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Hrs</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">OT</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Remarks</th>
                                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6">Actions</th>
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
                                        {{ $record->break_hours ? $record->break_hours : '-' }}
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
                                        <a href="{{ url('employees/' . $employee->id . '/overtime/' . $record->id . '/edit') }}" 
                                           class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form action="{{ url('employees/' . $employee->id . '/overtime/' . $record->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    onclick="return confirm('Are you sure you want to delete this overtime record?')" 
                                                    class="text-red-600 hover:text-red-900">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-3 py-8 text-sm text-gray-500 text-center">
                                        No overtime records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $records->appends(request()->query())->links() }}
    </div>
</div>
@endsection