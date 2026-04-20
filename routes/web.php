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

// Storage Bridge route to handle file access on restrictive hostings without symlink
Route::get('/image-view/{path}', function ($path) {
    $fullPath = storage_path("app/public/" . $path);
    
    if (!file_exists($fullPath)) {
        abort(404);
    }

    return response()->file($fullPath);
})->where('path', '.*');

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

    Route::get('/job-applications/{record}/print-interview-result', function (\App\Models\JobApplication $record) {
        $record->load(['interviewProcesses', 'archive']);
        return view('job-applications.print-interview-result', compact('record'));
    })->name('job-applications.print-interview-result');

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

        // Integrate Approved Permissions (Leaves)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $permissionQuery = \App\Models\EmployeePermission::where('approval_status', 'approved')
                ->with(['employee', 'permission'])
                ->where(function($q) use ($request) {
                    $q->whereBetween('start_permission_date', [$request->from_date, $request->to_date])
                      ->orWhereBetween('end_permission_date', [$request->from_date, $request->to_date])
                      ->orWhere(function($subQ) use ($request) {
                          $subQ->where('start_permission_date', '<=', $request->from_date)
                               ->where('end_permission_date', '>=', $request->to_date);
                      });
                });

            if ($request->filled('employee_id')) {
                $permissionQuery->where('employee_id', $request->employee_id);
            }

            $permissions = $permissionQuery->get();

            foreach ($permissions as $permission) {
                $reqFrom = \Carbon\Carbon::parse($request->from_date)->startOfDay();
                $reqTo = \Carbon\Carbon::parse($request->to_date)->endOfDay();
                $startDate = \Carbon\Carbon::parse($permission->start_permission_date)->max($reqFrom);
                $endDate = \Carbon\Carbon::parse($permission->end_permission_date)->min($reqTo);

                $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

                foreach ($period as $date) {
                    $records->push((object)[
                        'id' => 'perm_' . $permission->id . '_' . $date->format('Ymd'),
                        'attendance_time' => $date->startOfDay(),
                        'employee_name' => $permission->employee->name ?? 'Pegawai',
                        'pin' => $permission->employee->pin ?? '-',
                        'state' => 'permission',
                        'attendance_status' => 'on_time',
                        'permission_name' => $permission->permission->name ?? 'Izin/Cuti',
                        'officeLocation' => (object)['name' => '-'],
                        'distance_from_office' => null,
                        'is_within_radius' => true,
                    ]);
                }
            }
            
            // Re-sort records by attendance_time
            $records = $records->sortBy(function($record) {
                return \Carbon\Carbon::parse($record->attendance_time)->timestamp;
            });
        }
        
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

    Route::get('/attendance-summary-report', function (\Illuminate\Http\Request $request) {
        $fromDate = \Carbon\Carbon::parse($request->from_date)->startOfDay();
        $toDate = \Carbon\Carbon::parse($request->to_date)->endOfDay();
        
        // 1. Calculate Active Working Days from Schedule
        $activeSchedules = \App\Models\AttendanceSchedule::where('is_active', true)->pluck('day')->map(function ($day) {
            return strtolower($day);
        })->toArray();
        
        $totalWorkingDays = 0;
        $currentDate = $fromDate->copy();
        while ($currentDate <= $toDate) {
            $dayName = strtolower($currentDate->format('l'));
            if (in_array($dayName, $activeSchedules)) {
                $totalWorkingDays++;
            }
            $currentDate->addDay();
        }

        // 2. Fetch Employees
        $query = \App\Models\Employee::query();
        if ($request->filled('employee_id')) {
            $query->where('id', $request->employee_id);
        }
        $employees = $query->get();

        $summaries = collect();

        foreach ($employees as $employee) {
            // Count unique check-in days
            $presentDaysCount = \App\Models\EmployeeAttendanceRecord::where('pin', $employee->pin)
                ->whereBetween('attendance_time', [$fromDate, $toDate])
                ->whereIn('state', ['check_in', 'in', 'dl_in', 'ot_in'])
                ->selectRaw('DATE(attendance_time) as date')
                ->groupBy('date')
                ->get()
                ->count();
                
            $lateCount = \App\Models\EmployeeAttendanceRecord::where('pin', $employee->pin)
                ->whereBetween('attendance_time', [$fromDate, $toDate])
                ->whereIn('state', ['check_in', 'in', 'dl_in', 'ot_in'])
                ->where('attendance_status', 'late')
                ->count();

            $onTimeCount = \App\Models\EmployeeAttendanceRecord::where('pin', $employee->pin)
                ->whereBetween('attendance_time', [$fromDate, $toDate])
                ->whereIn('state', ['check_in', 'in', 'dl_in', 'ot_in'])
                ->whereIn('attendance_status', ['on_time', 'early'])
                ->count();

            // Permissions / Leave
            $leaveQuery = \App\Models\EmployeePermission::where('employee_id', $employee->id)
                ->where('approval_status', 'approved')
                ->where(function($q) use ($fromDate, $toDate) {
                    $q->whereBetween('start_permission_date', [$fromDate, $toDate])
                      ->orWhereBetween('end_permission_date', [$fromDate, $toDate])
                      ->orWhere(function($subQ) use ($fromDate, $toDate) {
                          $subQ->where('start_permission_date', '<=', $fromDate)
                               ->where('end_permission_date', '>=', $toDate);
                      });
                })->get();
                
            $leaveDays = 0;
            // Count overlapping days that are actual working days
            foreach ($leaveQuery as $leave) {
                // If it's half day, we might handle it differently but let's count as full day leave for simplicity
                $lStart = \Carbon\Carbon::parse($leave->start_permission_date)->max($fromDate);
                $lEnd = \Carbon\Carbon::parse($leave->end_permission_date)->min($toDate);
                
                $period = \Carbon\CarbonPeriod::create($lStart, $lEnd);
                foreach ($period as $date) {
                    if (in_array(strtolower($date->format('l')), $activeSchedules)) {
                        $leaveDays++;
                    }
                }
            }

            $effectiveWorkingDays = max(0, $totalWorkingDays - $leaveDays);
            $absentCount = max(0, $effectiveWorkingDays - $presentDaysCount);
            
            $percentage = $effectiveWorkingDays > 0 ? round(($presentDaysCount / $effectiveWorkingDays) * 100, 2) : 0;

            $summaries->push((object)[
                'employee' => $employee,
                'total_working_days' => $totalWorkingDays,
                'present' => $presentDaysCount,
                'late' => $lateCount,
                'on_time' => $onTimeCount,
                'leave' => $leaveDays,
                'absent' => $absentCount,
                'percentage' => $percentage,
            ]);
        }

        return view('filament.print.attendance-summary', [
            'summaries' => $summaries,
            'startDate' => $request->from_date,
            'endDate' => $request->to_date,
            'totalWorkingDays' => $totalWorkingDays,
            'singleEmployee' => $request->filled('employee_id') ? \App\Models\Employee::find($request->employee_id)?->name : false,
        ]);
    })->name('attendance.summary.report');

    Route::get('/daily-reports-report', function (\Illuminate\Http\Request $request) {
        $query = \App\Models\EmployeeDailyReport::query()->with('employee');

        if ($request->filled('from_date')) {
            $query->whereDate('daily_report_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('daily_report_date', '<=', $request->to_date);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $records = $query->orderBy('daily_report_date', 'asc')->get();
        $employeeName = $request->filled('employee_id') ? \App\Models\Employee::find($request->employee_id)?->name : null;

        return view('filament.print.daily-report', [
            'records' => $records,
            'startDate' => $request->from_date,
            'endDate' => $request->to_date,
            'employeeName' => $employeeName,
        ]);
    })->name('daily-reports.report');

    Route::get('/career-movement-report', [\App\Http\Controllers\ReportController::class, 'careerMovement'])->name('report.career-movement');
    Route::get('/career-schedule-report', [\App\Http\Controllers\ReportController::class, 'careerSchedule'])->name('report.career-schedule');
});

// ADMS (Attendance Machine) Routes - Root Level
Route::any('/iclock/cdata', [\App\Http\Controllers\AdmsController::class, 'cdata']);
Route::get('/iclock/getrequest', [\App\Http\Controllers\AdmsController::class, 'getrequest']);
