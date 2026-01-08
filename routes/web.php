<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;

Route::get('/', function () {
    // Redirect langsung ke halaman login admin sebagai default
    return redirect('/admin');
});

// API routes for attendance
Route::prefix('api/attendance')->group(function () {
    Route::post('/upload-photo', [AttendanceController::class, 'uploadPhoto'])->name('api.attendance.upload-photo');
    Route::post('/validate-location', [AttendanceController::class, 'validateLocation'])->name('api.attendance.validate-location');
});
