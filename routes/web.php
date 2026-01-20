<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OvertimeRecordController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('employees.index');
    })->name('dashboard');

    // Employee Routes
    Route::resource('employees', EmployeeController::class);

    // Overtime Routes
    Route::prefix('employees/{employee}')->group(function () {
        Route::get('overtime', [OvertimeRecordController::class, 'index'])->name('overtime.index');
        Route::get('overtime/create', [OvertimeRecordController::class, 'create'])->name('overtime.create');
        
        // Excel Import Routes - MOVED BEFORE the store route
        Route::get('overtime/import', [OvertimeRecordController::class, 'importForm'])->name('overtime.import.form');
        Route::post('overtime/import', [OvertimeRecordController::class, 'import'])->name('overtime.import');
        
        Route::post('overtime', [OvertimeRecordController::class, 'store'])->name('overtime.store');
        Route::get('overtime/{overtimeRecord}/edit', [OvertimeRecordController::class, 'edit'])->name('overtime.edit');
        Route::put('overtime/{overtimeRecord}', [OvertimeRecordController::class, 'update'])->name('overtime.update');
        Route::delete('overtime/{overtimeRecord}', [OvertimeRecordController::class, 'destroy'])->name('overtime.destroy');
    });

    // Admin Only Routes
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
});

require __DIR__.'/auth.php';