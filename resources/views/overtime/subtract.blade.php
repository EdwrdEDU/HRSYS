@extends('layouts.app')

@section('title', 'Subtract Overtime Hours')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Sidebar --}}
            <div class="lg:col-span-1">
                <a href="{{ route('overtime.index', $employee) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-medium mb-6 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back
                </a>

                <div class="backdrop-blur-sm bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl p-6 text-white sticky top-8 shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-white/20 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold ml-3">Subtract Hours</h3>
                    </div>
                    <p class="text-sm text-white/90">Subtracting hours for:</p>
                    <p class="text-xl font-bold mt-2">{{ $employee->full_name }}</p>
                    <p class="text-sm text-white/80">{{ $employee->section }}</p>

                    <div class="border-t border-white/20 mt-6 pt-6">
                        <h4 class="font-semibold mb-4 text-sm uppercase tracking-wider">Overtime Summary</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-white/90">Total Approved:</span>
                                <span class="font-bold text-lg">{{ floor($totalApprovedHours) }}:{{ str_pad(round(($totalApprovedHours - floor($totalApprovedHours)) * 60), 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-white/90">Total Subtracted:</span>
                                <span class="font-bold text-lg text-red-200">-{{ floor($totalSubtracted) }}:{{ str_pad(round(($totalSubtracted - floor($totalSubtracted)) * 60), 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-3 border-t border-white/20">
                                <span class="text-sm font-semibold">Available:</span>
                                <span class="font-bold text-2xl text-green-200">{{ floor($availableHours) }}:{{ str_pad(round(($availableHours - floor($availableHours)) * 60), 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-white/20 mt-6 pt-6">
                        <div class="bg-white/10 rounded-lg p-3">
                            <p class="text-xs text-white/90">
                                <strong class="text-white">Note:</strong> Subtracting hours will reduce the available overtime balance. This is typically used when an employee uses their overtime hours for compensatory time off or when hours have been paid out.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="lg:col-span-3">
                <div class="backdrop-blur-sm bg-white/40 border border-white/60 rounded-2xl p-8 shadow-sm">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Subtract Overtime Hours</h2>
                    <p class="text-gray-600 mb-8">Reduce available overtime balance for compensatory time or paid hours</p>

                    <form action="{{ route('overtime.subtract', $employee) }}" method="POST">
                        @csrf
                        
                        @if($availableHours <= 0)
                            <div class="backdrop-blur-sm bg-red-50/80 border border-red-200 rounded-xl p-6 flex items-start gap-4">
                                <div class="p-2 bg-red-100 rounded-lg">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-red-900 mb-1">No hours available to subtract</p>
                                    <p class="text-sm text-red-700">This employee has no remaining approved overtime hours.</p>
                                </div>
                            </div>
                        @else
                            <div class="space-y-8">
                                {{-- Hours to Subtract --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                            Hours to Subtract <span class="text-red-500">*</span>
                                        </div>
                                    </label>
                                    <input type="number" 
                                           name="hours_to_subtract" 
                                           id="hours_to_subtract" 
                                           step="0.01"
                                           min="0.01"
                                           max="{{ $availableHours }}"
                                           value="{{ old('hours_to_subtract') }}"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all @error('hours_to_subtract') border-red-500 @enderror"
                                           required>
                                    <p class="text-xs text-gray-500 mt-2">Maximum available: {{ $availableHours }} hours</p>
                                    @error('hours_to_subtract')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                                </div>

                                {{-- Subtraction Date --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            Date of Usage/Subtraction <span class="text-red-500">*</span>
                                        </div>
                                    </label>
                                    <input type="date" 
                                           name="subtraction_date" 
                                           id="subtraction_date" 
                                           value="{{ old('subtraction_date', date('Y-m-d')) }}"
                                           max="{{ date('Y-m-d') }}"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all @error('subtraction_date') border-red-500 @enderror"
                                           required>
                                    <p class="text-xs text-gray-500 mt-2">Date when hours were used (cannot be future date)</p>
                                    @error('subtraction_date')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                                </div>

                                {{-- Reason for Subtraction --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                            </svg>
                                            Reason for Subtraction <span class="text-red-500">*</span>
                                        </div>
                                    </label>
                                    <textarea name="subtraction_reason" 
                                              id="subtraction_reason" 
                                              rows="4"
                                              class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all @error('subtraction_reason') border-red-500 @enderror"
                                              placeholder="e.g., Used for compensatory time off on [date], Already paid in [month], Adjustment for error..."
                                              required>{{ old('subtraction_reason') }}</textarea>
                                    <p class="text-xs text-gray-500 mt-2">Explain why these hours are being subtracted (max 500 characters)</p>
                                    @error('subtraction_reason')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                                </div>

                                {{-- Preview Calculation --}}
                                <div class="backdrop-blur-sm bg-indigo-50/80 border border-indigo-200 rounded-xl p-6">
                                    <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Preview:
                                    </h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Current Available:</span>
                                            <span class="font-semibold text-gray-900">{{ $availableHours }} hrs</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Hours to Subtract:</span>
                                            <span class="font-semibold text-red-600">-<span id="preview_subtract">0.00</span> hrs</span>
                                        </div>
                                        <div class="flex justify-between pt-3 border-t border-indigo-200">
                                            <span class="font-semibold text-gray-900">New Available:</span>
                                            <span class="font-bold text-2xl text-indigo-600"><span id="preview_total">{{ $availableHours }}</span> hrs</span>
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
                                            class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-0.5"
                                            onclick="return confirm('Are you sure you want to subtract these hours from the total approved overtime?')">
                                        Subtract Hours
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if($availableHours <= 0)
                            <div class="flex gap-3 pt-6 mt-6 border-t border-gray-200">
                                <a href="{{ route('overtime.index', $employee) }}" 
                                   class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-0.5 text-center">
                                    Back to Overtime Records
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<script>
    // Live preview of calculation
    const hoursInput = document.getElementById('hours_to_subtract');
    if (hoursInput) {
        hoursInput.addEventListener('input', function() {
            const availableHours = {{ $availableHours }};
            const hoursToSubtract = parseFloat(this.value) || 0;
            const newTotal = Math.max(0, availableHours - hoursToSubtract);
            
            document.getElementById('preview_subtract').textContent = hoursToSubtract.toFixed(2);
            document.getElementById('preview_total').textContent = newTotal.toFixed(2);
        });
    }
</script>
@endsection