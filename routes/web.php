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
    
    // Redirect root authenticated path to employees
    Route::get('/home', function () {
        return redirect()->route('employees.index');
    });

    // Employee Routesx
    Route::resource('employees', EmployeeController::class);

    // Overtime Routes
    Route::get('employees/{employee}/overtime', [OvertimeRecordController::class, 'index'])->name('overtime.index');
    Route::get('employees/{employee}/overtime/create', [OvertimeRecordController::class, 'create'])->name('overtime.create');
    Route::post('employees/{employee}/overtime', [OvertimeRecordController::class, 'store'])->name('overtime.store');
    Route::get('employees/{employee}/overtime/{overtimeRecord}/edit', [OvertimeRecordController::class, 'edit'])->name('overtime.edit');
    Route::put('employees/{employee}/overtime/{overtimeRecord}', [OvertimeRecordController::class, 'update'])->name('overtime.update');
    Route::delete('employees/{employee}/overtime/{overtimeRecord}', [OvertimeRecordController::class, 'destroy'])->name('overtime.destroy');

    // Admin Only Routes
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
});

require __DIR__.'/auth.php';