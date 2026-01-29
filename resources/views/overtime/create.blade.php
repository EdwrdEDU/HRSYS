@extends('layouts.app')

@section('title', 'Add Overtime Record')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Sidebar --}}
            <div class="lg:col-span-1">
                <a href="{{ route('overtime.index', $employee) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-medium mb-6 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back
                </a>

                <div class="backdrop-blur-sm bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white sticky top-8 shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-white/20 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold ml-3">Add Record</h3>
                    </div>
                    <p class="text-sm text-white/90">Creating overtime record for:</p>
                    <p class="text-xl font-bold mt-2">{{ $employee->full_name }}</p>
                    <p class="text-sm text-white/80">{{ $employee->section }}</p>

                    <div class="border-t border-white/20 mt-6 pt-6">
                        <h4 class="font-semibold mb-3 text-sm">Auto-Calculation Guide</h4>
                        <ul class="text-xs space-y-2 text-white/90">
                            <li class="flex items-start">
                                <span class="mr-2">✓</span>
                                <span>Enter Time IN, Time OUT, and Break</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">✓</span>
                                <span>Total Hours calculated from times (min 8 hrs)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">✓</span>
                                <span>OT = Hours beyond 8</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="lg:col-span-3">
                <div class="backdrop-blur-sm bg-white/40 border border-white/60 rounded-2xl p-8 shadow-sm">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Overtime Record</h2>
                    <p class="text-gray-600 mb-8">Add overtime details for auto-calculation of hours</p>

                    <form action="{{ route('overtime.store', $employee) }}" method="POST">
                        @csrf
                        <div class="space-y-8">
                            {{-- Date Field --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Date
                                    </div>
                                </label>
                                <input type="date" name="date" value="{{ old('date') }}"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all @error('date') border-red-500 @enderror">
                                @error('date')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                            </div>

                            {{-- Time IN and OUT --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            Time IN
                                        </div>
                                    </label>
                                    <input type="text" name="time_in" value="{{ old('time_in') }}"
                                           placeholder="7:38 AM or 07:38"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all @error('time_in') border-red-500 @enderror">
                                    <p class="text-xs text-gray-500 mt-2">Examples: 7:38 AM, 07:38, 7:38 am</p>
                                    @error('time_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" transform="scaleX(-1) translate(-24 0)"></path>
                                            </svg>
                                            Time OUT
                                        </div>
                                    </label>
                                    <input type="text" name="time_out" value="{{ old('time_out') }}"
                                           placeholder="6:38 PM or 18:38"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all @error('time_out') border-red-500 @enderror">
                                    <p class="text-xs text-gray-500 mt-2">Examples: 6:38 PM, 18:38, 6:38 pm</p>
                                    @error('time_out')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            {{-- Break Hours --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Break (hours)
                                        </div>
                                    </label>
                                    <input type="text" name="break_hours" value="{{ old('break_hours', '1:00') }}"
                                           placeholder="1:00 or 1.5"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all @error('break_hours') border-red-500 @enderror">
                                    <p class="text-xs text-gray-500 mt-2">Use 1:00, 1:30, or decimal like 1.5</p>
                                    @error('break_hours')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Rendered Hours (auto-calculated)
                                        </div>
                                    </label>
                                    <input type="text" name="total_hours_rendered" value="{{ old('total_hours_rendered') }}"
                                        placeholder="Auto-calculated (e.g., 8:00)"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 text-gray-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                                    <p class="text-xs text-gray-500 mt-2">Leave blank for auto-calc or use 8:00 format</p>
                                </div>
                            </div>

                            {{-- OT Hours --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Overtime Hours (auto-calculated)
                                        </div>
                                    </label>
                                    <input type="text" name="overtime_hours" value="{{ old('overtime_hours') }}"
                                        placeholder="Auto-calculated (e.g., 2:30)"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 text-gray-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                                    <p class="text-xs text-gray-500 mt-2">Auto-calculated (Rendered - 8) or use 2:30 format</p>
                                </div>
                            </div>

                            {{-- Remarks --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                        Remarks
                                    </div>
                                </label>
                                <textarea name="remarks" rows="3"
                                          placeholder="Add any additional notes..."
                                          class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all @error('remarks') border-red-500 @enderror">{{ old('remarks') }}</textarea>
                                @error('remarks')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                            </div>

                            {{-- Approval Forms Section --}}
                            <div class="border-t-2 border-gray-200 pt-8">
                                <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Approval Forms
                                </h3>
                                <p class="text-sm text-gray-600 mb-6">Both forms must be filled for overtime to be counted as approved.</p>

                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Purpose/Deliverables (Form A)</label>
                                        <textarea name="purpose_deliverables" rows="4"
                                                  placeholder="Describe the purpose and deliverables..."
                                                  class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all @error('purpose_deliverables') border-red-500 @enderror">{{ old('purpose_deliverables') }}</textarea>
                                        @error('purpose_deliverables')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Actual Accomplishment (Form B)</label>
                                        <textarea name="actual_accomplishment" rows="4"
                                                  placeholder="Describe what was actually accomplished..."
                                                  class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all @error('actual_accomplishment') border-red-500 @enderror">{{ old('actual_accomplishment') }}</textarea>
                                        @error('actual_accomplishment')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex gap-3 pt-6">
                                <a href="{{ route('overtime.index', $employee) }}" 
                                   class="flex-1 px-6 py-3 border border-gray-300 bg-white text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-all duration-200 text-center">
                                    Cancel
                                </a>
                                <button type="submit" 
                                        class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                                    Save Record
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection