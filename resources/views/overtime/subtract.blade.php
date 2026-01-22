@extends('layouts.app')

@section('title', 'Subtract Overtime Hours')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Subtract from Total Approved Hours</h3>
            <p class="mt-1 text-sm text-gray-600">
                Subtract hours from the total approved overtime for <strong>{{ $employee->full_name }}</strong>
            </p>
            
            <div class="mt-4 p-4 bg-blue-50 rounded-md">
                <p class="text-sm text-blue-800 font-semibold mb-3">Overtime Summary:</p>
                <ul class="text-sm text-blue-800 space-y-2">
                    <li class="flex justify-between">
                        <span>Total Approved:</span>
                        <strong>{{ $totalApprovedHours }} hrs</strong>
                    </li>
                    <li class="flex justify-between">
                        <span>Total Subtracted:</span>
                        <strong class="text-red-600">-{{ $totalSubtracted }} hrs</strong>
                    </li>
                    <li class="flex justify-between pt-2 border-t border-blue-200">
                        <span class="font-semibold">Available:</span>
                        <strong class="text-green-600 text-lg">{{ $availableHours }} hrs</strong>
                    </li>
                </ul>
            </div>

            <div class="mt-4 p-4 bg-yellow-50 rounded-md">
                <p class="text-xs text-yellow-800">
                    <strong>Note:</strong> Subtracting hours will reduce the available overtime balance. This is typically used when an employee uses their overtime hours for compensatory time off or when hours have been paid out.
                </p>
            </div>
        </div>
        
        <div class="mt-5 md:mt-0 md:col-span-2">
            <form action="{{ route('overtime.subtract', $employee) }}" method="POST">
                @csrf
                <div class="shadow sm:rounded-md sm:overflow-hidden">
                    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                        
                        @if($availableHours <= 0)
                            <div class="p-4 bg-red-50 rounded-md">
                                <p class="text-sm text-red-800">
                                    <strong>No hours available to subtract.</strong> This employee has no remaining approved overtime hours.
                                </p>
                            </div>
                        @else
                            {{-- Hours to Subtract --}}
                            <div>
                                <label for="hours_to_subtract" class="block text-sm font-medium text-gray-700">
                                    Hours to Subtract <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="hours_to_subtract" 
                                       id="hours_to_subtract" 
                                       step="0.01"
                                       min="0.01"
                                       max="{{ $availableHours }}"
                                       value="{{ old('hours_to_subtract') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('hours_to_subtract') border-red-500 @enderror"
                                       required>
                                <p class="mt-1 text-sm text-gray-500">
                                    Maximum available: {{ $availableHours }} hours
                                </p>
                                @error('hours_to_subtract')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Subtraction Date --}}
                            <div>
                                <label for="subtraction_date" class="block text-sm font-medium text-gray-700">
                                    Date of Usage/Subtraction <span class="text-red-500">*</span>
                                </label>
                                <input type="date" 
                                       name="subtraction_date" 
                                       id="subtraction_date" 
                                       value="{{ old('subtraction_date', date('Y-m-d')) }}"
                                       max="{{ date('Y-m-d') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('subtraction_date') border-red-500 @enderror"
                                       required>
                                <p class="mt-1 text-sm text-gray-500">
                                    Date when hours were used (cannot be future date)
                                </p>
                                @error('subtraction_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Reason for Subtraction --}}
                            <div>
                                <label for="subtraction_reason" class="block text-sm font-medium text-gray-700">
                                    Reason for Subtraction <span class="text-red-500">*</span>
                                </label>
                                <textarea name="subtraction_reason" 
                                          id="subtraction_reason" 
                                          rows="4"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('subtraction_reason') border-red-500 @enderror"
                                          placeholder="e.g., Used for compensatory time off on [date], Already paid in [month], Adjustment for error..."
                                          required>{{ old('subtraction_reason') }}</textarea>
                                <p class="mt-1 text-sm text-gray-500">
                                    Explain why these hours are being subtracted (max 500 characters)
                                </p>
                                @error('subtraction_reason')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Preview Calculation --}}
                            <div class="p-4 bg-gray-50 rounded-md">
                                <p class="text-sm font-medium text-gray-700">Preview:</p>
                                <div class="mt-2 text-sm text-gray-600">
                                    <p>Current Available: <strong>{{ $availableHours }} hrs</strong></p>
                                    <p class="mt-1">Hours to Subtract: <strong class="text-red-600">-<span id="preview_subtract">0.00</span> hrs</strong></p>
                                    <p class="mt-1 text-lg font-semibold text-indigo-600">
                                        New Available: <span id="preview_total">{{ $availableHours }}</span> hrs
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 space-x-2">
                        <a href="{{ route('overtime.index', $employee) }}" 
                           class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </a>
                        @if($availableHours > 0)
                            <button type="submit" 
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700"
                                    onclick="return confirm('Are you sure you want to subtract these hours from the total approved overtime?')">
                                Subtract Hours
                            </button>
                        @endif
                    </div>
                </div>
            </form>
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