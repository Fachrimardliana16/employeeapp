<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;

// Redirect root to user panel
Route::redirect('/', '/user');

Route::get('/login', \App\Filament\Pages\Auth\Login::class)->name('login');
Route::redirect('/admin/login', '/login');
Route::redirect('/employee/login', '/login');
Route::redirect('/user/login', '/login');

// API routes for attendance
Route::prefix('api/attendance')->group(function () {
    Route::post('/upload-photo', [AttendanceController::class, 'uploadPhoto'])->name('api.attendance.upload-photo');
    Route::post('/validate-location', [AttendanceController::class, 'validateLocation'])->name('api.attendance.validate-location');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/job-applications/{record}/print', function (\App\Models\JobApplication $record) {
        return view('job-applications.print-profile', compact('record'));
    })->name('job-applications.print');

    Route::get('/employees/{record}/print', function (\App\Models\Employee $record) {
        return view('reports.employee-profile', compact('record'));
    })->name('employees.print');

    Route::get('/attendance-records/{record}/print', function (\App\Models\EmployeeAttendanceRecord $record) {
        return view('filament.print.attendance-slip', ['record' => $record]);
    })->name('attendance.print');

    Route::get('/attendance-report', function (\Illuminate\Http\Request $request) {
        $query = \App\Models\EmployeeAttendanceRecord::query()->with('officeLocation');

        if ($request->filled('from_date')) {
            $query->whereDate('attendance_time', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('attendance_time', '<=', $request->to_date);
        }
        if ($request->filled('employee_id')) {
            $employee = \App\Models\Employee::find($request->employee_id);
            if ($employee) {
                $query->where('pin', $employee->pin);
            }
        }
        if ($request->filled('office_location_id')) {
            $query->where('office_location_id', $request->office_location_id);
        }

        $records = $query->orderBy('attendance_time', 'asc')->get();
        
        $employeeName = $request->filled('employee_id') ? \App\Models\Employee::find($request->employee_id)?->name : null;
        $locationName = $request->filled('office_location_id') ? \App\Models\MasterOfficeLocation::find($request->office_location_id)?->name : null;

        return view('filament.print.attendance-report', [
            'records' => $records,
            'startDate' => $request->from_date,
            'endDate' => $request->to_date,
            'employeeName' => $employeeName,
            'locationName' => $locationName,
        ]);
    })->name('attendance.report');
});
