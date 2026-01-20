@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-3xl font-semibold text-gray-900">Employees</h1>
            <p class="mt-2 text-sm text-gray-700">Manage all employees and their overtime records.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <a href="{{ route('employees.create') }}" 
               class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                Add Employee
            </a>
        </div>
    </div>

    <div class="mt-6">
        <form method="GET" action="{{ route('employees.index') }}" class="flex gap-2">
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Search by name or department..." 
                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('employees.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Department</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Approved OT</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Pending OT</th>
                                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($employees as $employee)
                                <tr>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 font-medium">
                                        {{ $employee->full_name }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $employee->department }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-green-600 font-semibold">
                                        {{ $employee->total_overtime_hours }} hrs
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-yellow-600 font-semibold">
                                        {{ $employee->pending_overtime_hours }} hrs
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <a href="{{ route('overtime.index', $employee) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Overtime</a>
                                        <a href="{{ route('employees.edit', $employee) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Are you sure? This will delete all overtime records for this employee.')" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-sm text-gray-500 text-center">No employees found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $employees->links() }}
    </div>
</div>
@endsection