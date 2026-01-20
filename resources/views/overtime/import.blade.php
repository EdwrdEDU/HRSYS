@extends('layouts.app')

@section('title', 'Import Overtime Records')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Import from Excel</h3>
            <p class="mt-1 text-sm text-gray-600">
                Import multiple overtime records for <strong>{{ $employee->full_name }}</strong>
            </p>
            <div class="mt-4 p-4 bg-blue-50 rounded-md">
                <p class="text-xs text-blue-800 font-semibold mb-2">Excel Format:</p>
                <p class="text-xs text-blue-800">Your Excel file should have these column headers:</p>
                <ul class="text-xs text-blue-800 mt-2 space-y-1 list-disc list-inside">
                    <li>DATE</li>
                    <li>IN (or TIME_IN)</li>
                    <li>OUT (or TIME_OUT)</li>
                    <li>BREAK (or BREAK_MINUTES)</li>
                    <li>OVERTIME (or OVERTIME_HOURS)</li>
                    <li>REMARKS</li>
                    <li>PURPOSE_DELIVERABLES</li>
                    <li>ACTUAL_ACCOMPLISHMENT</li>
                </ul>
                <p class="text-xs text-blue-800 mt-3">All fields are optional. The system will import whatever data is available.</p>
            </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <form action="{{ route('overtime.import', $employee) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="shadow sm:rounded-md sm:overflow-hidden">
                    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Choose Excel File
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                            <span>Upload a file</span>
                                            <input id="file" name="file" type="file" class="sr-only" accept=".xlsx,.xls,.csv" required>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">XLSX, XLS, CSV up to 2MB</p>
                                </div>
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
                            Import
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>