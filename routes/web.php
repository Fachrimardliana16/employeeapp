<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Mobile\MobileAuthController;
use App\Http\Controllers\Mobile\MobileDashboardController;
use App\Http\Controllers\Mobile\MobileAttendanceController;
use App\Http\Controllers\Mobile\MobilePermissionController;
use App\Http\Controllers\Mobile\MobileDailyReportController;
use App\Http\Controllers\Mobile\MobileProfileController;
use App\Http\Controllers\Mobile\MobileDocumentController;
use App\Http\Controllers\Mobile\MobileTrainingController;
use App\Http\Controllers\Mobile\MobileFamilyController;
use App\Http\Controllers\Mobile\MobileRetirementController;

use App\Models\EmployeeAttendanceRecord;
use App\Models\MasterOfficeLocation;
use App\Models\Employee;
use App\Models\JobApplication;

// Temporary route to fix storage:link issue on hosting
Route::get('/linkstorage', function () {
    $target = storage_path('app/public');
    $shortcut = public_path('storage');
    
    // Check if link already exists
    if (file_exists($shortcut)) {
        return "The 'public/storage' directory already exists.";
    }
    
    try {
        symlink($target, $shortcut);
        return "Storage link created successfully!";
    } catch (\Exception $e) {
        return "Error creating storage link: " . $e->getMessage();
    }
});

// Redirect root to user panel
Route::redirect('/', '/user');

Route::get('/login', \App\Filament\Pages\Auth\Login::class)->name('login');
Route::redirect('/admin/login', '/login');
Route::redirect('/employee/login', '/login');
Route::redirect('/user/login', '/login');

// ─── Mobile PWA Portal ─────────────────────────────────────────
Route::prefix('mobile')->name('mobile.')->group(function () {

    // Auth (guest only)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [MobileAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [MobileAuthController::class, 'login'])->name('login.post');
    });

    // Logout
    Route::post('/logout', [MobileAuthController::class, 'logout'])->name('logout')->middleware('auth');

    // Protected routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/', [MobileDashboardController::class, 'index'])->name('dashboard');
        Route::get('/attendance', [MobileAttendanceController::class, 'index'])->name('attendance');
        Route::post('/attendance', [MobileAttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/permissions', [MobilePermissionController::class, 'index'])->name('permissions');
        Route::get('/permissions/create', [MobilePermissionController::class, 'create'])->name('permissions.create');
        Route::post('/permissions', [MobilePermissionController::class, 'store'])->name('permissions.store');
        Route::get('/permissions/{id}', [MobilePermissionController::class, 'show'])->name('permissions.show');
        Route::get('/daily-reports', [MobileDailyReportController::class, 'index'])->name('daily-reports');
        Route::post('/daily-reports', [MobileDailyReportController::class, 'store'])->name('daily-reports.store');
        Route::get('/profile', [MobileProfileController::class, 'index'])->name('profile');
        Route::get('/documents', [MobileDocumentController::class, 'index'])->name('documents');
        Route::post('/documents', [MobileDocumentController::class, 'store'])->name('documents.store');
        Route::get('/training', [MobileTrainingController::class, 'index'])->name('training');
        Route::post('/training', [MobileTrainingController::class, 'store'])->name('training.store');
        Route::get('/family', [MobileFamilyController::class, 'index'])->name('family');
        Route::post('/family', [MobileFamilyController::class, 'store'])->name('family.store');
        Route::get('/retirement', [MobileRetirementController::class, 'index'])->name('retirement');
        Route::post('/retirement', [MobileRetirementController::class, 'store'])->name('retirement.store');
    });
});



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
