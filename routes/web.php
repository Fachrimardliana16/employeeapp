<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;

// Root path is now handled by the User Panel
Route::get('/login', \App\Filament\Pages\Auth\Login::class)->name('login');
Route::redirect('/admin/login', '/login');
Route::redirect('/employee/login', '/login');
Route::redirect('/user/login', '/login');

// API routes for attendance
Route::prefix('api/attendance')->group(function () {
    Route::post('/upload-photo', [AttendanceController::class, 'uploadPhoto'])->name('api.attendance.upload-photo');
    Route::post('/validate-location', [AttendanceController::class, 'validateLocation'])->name('api.attendance.validate-location');
});
