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
        
        // 1. Fetch Active Schedules for all days
        $allSchedules = \App\Models\AttendanceSchedule::where('is_active', true)->get()->keyBy(function($item) {
            return strtolower($item->day);
        });
        
        $activeDayNames = $allSchedules->keys()->toArray();
        
        // Day translation for Indonesian DB compatibility
        $dayMap = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu',
        ];

        // 2. Fetch Employees
        $query = \App\Models\Employee::query();
        if ($request->filled('employee_id')) {
            $query->where('id', $request->employee_id);
        }
        $employees = $query->get();

        // 3. Fetch Special Schedules for the range
        $specialSchedules = \App\Models\AttendanceSpecialSchedule::whereBetween('date', [$fromDate, $toDate])
            ->get()
            ->groupBy('employee_id');

        $summaries = collect();

        foreach ($employees as $employee) {
            $empSpecialSchedules = $specialSchedules->get($employee->id, collect())->keyBy(function($item) {
                return $item->date->toDateString();
            });

            // Calculate Working Days count for summary context
            $empTotalWorkingDays = 0;
            $checkDate = $fromDate->copy();
            while ($checkDate <= $toDate) {
                $dayInd = $dayMap[strtolower($checkDate->format('l'))] ?? strtolower($checkDate->format('l'));
                if ($empSpecialSchedules->has($checkDate->toDateString())) {
                    if ($empSpecialSchedules->get($checkDate->toDateString())->is_working) $empTotalWorkingDays++;
                } elseif (in_array($dayInd, $activeDayNames)) {
                    $empTotalWorkingDays++;
                }
                $checkDate->addDay();
            }

            // --- LOGIKA BARU: DETAIL BUKTI ---
            
            $presentDetails = collect();
            $absentDetails = collect();
            $lateDetails = collect();
            $earlyDetails = collect();
            $onTimeDetails = collect();
            $leaveDetails = collect();

            // 1. Gather all actual logs for present dates
            $allLogs = \App\Models\AttendanceMachineLog::where('pin', $employee->pin)
                ->whereBetween('timestamp', [$fromDate, $toDate])
                ->get()
                ->groupBy(fn($item) => $item->timestamp->toDateString());

            // 2. Iterate Calendar to find Absences & Details
            $currentDate = $fromDate->copy();
            while ($currentDate <= $toDate) {
                $dateStr = $currentDate->toDateString();
                $dayEng = strtolower($currentDate->format('l'));
                $dayInd = $dayMap[$dayEng] ?? $dayEng;
                
                // Identify if it's a working day
                $isWork = false;
                if ($empSpecialSchedules->has($dateStr)) {
                    $isWork = $empSpecialSchedules->get($dateStr)->is_working;
                } else {
                    $isWork = in_array($dayInd, $activeDayNames);
                }

                if ($isWork) {
                    // Check if employee has approved leave on this day
                    $hasLeave = \App\Models\EmployeePermission::where('employee_id', $employee->id)
                        ->where('approval_status', 'approved')
                        ->where('start_permission_date', '<=', $dateStr)
                        ->where('end_permission_date', '>=', $dateStr)
                        ->exists();

                    if ($hasLeave) {
                        $leaveDetails->push(['date' => $dateStr, 'day' => $dayInd]);
                    } else {
                        // Check presence logs
                        if ($allLogs->has($dateStr)) {
                            $dayLogs = $allLogs->get($dateStr);
                            $inLog = $dayLogs->whereIn('type', ['0', '3', '4'])->sortBy('timestamp')->first(); // First In
                            $outLog = $dayLogs->where('type', '1')->sortByDesc('timestamp')->first(); // Last Out

                            if ($inLog) {
                                $presentDetails->push([
                                    'date' => $dateStr,
                                    'day' => $dayInd,
                                    'time' => $inLog->timestamp->format('H:i:s'),
                                    'machine' => $inLog->machine?->name ?? 'Mesin'
                                ]);

                                // Check Lateness
                                $schedule = $allSchedules->get($dayInd);
                                if ($schedule) {
                                    $limit = $schedule->late_threshold ?: $schedule->check_in_end;
                                    
                                    if ($limit && $inLog->timestamp->format('H:i:s') > $limit) {
                                        $startTime = \Carbon\Carbon::parse($limit);
                                        $endTime = \Carbon\Carbon::parse($inLog->timestamp->format('H:i:s'));
                                        $diffInMinutes = $endTime->diffInMinutes($startTime);

                                        $lateDetails->push([
                                            'date' => $dateStr,
                                            'day' => $dayInd,
                                            'time' => $inLog->timestamp->format('H:i:s'),
                                            'limit' => $limit,
                                            'duration' => $diffInMinutes
                                        ]);
                                    } else {
                                        $onTimeDetails->push([
                                            'date' => $dateStr,
                                            'day' => $dayInd,
                                            'time' => $inLog->timestamp->format('H:i:s')
                                        ]);
                                    }
                                } else {
                                    $onTimeDetails->push(['date' => $dateStr, 'day' => $dayInd, 'time' => $inLog->timestamp->format('H:i:s')]);
                                }
                            }

                            if ($outLog) {
                                // Check Early Leave
                                $schedule = $allSchedules->get($dayInd);
                                if ($schedule && $schedule->check_out_start) {
                                    if ($outLog->timestamp->format('H:i:s') < $schedule->check_out_start) {
                                        $earlyDetails->push([
                                            'date' => $dateStr,
                                            'day' => $dayInd,
                                            'time' => $outLog->timestamp->format('H:i:s'),
                                            'limit' => $schedule->check_out_start
                                        ]);
                                    }
                                }
                            }

                            if (!$inLog && !$outLog) {
                                // Scanned but maybe just break/other? technically absent if no In/Out, 
                                // but we count as present if ANY working activity log exists for simplicity
                                $absentDetails->push(['date' => $dateStr, 'day' => $dayInd]);
                            }
                        } else {
                            $absentDetails->push(['date' => $dateStr, 'day' => $dayInd]);
                        }
                    }
                }
                $currentDate->addDay();
            }

            $summaries->push((object)[
                'employee' => $employee,
                'total_working_days' => $empTotalWorkingDays,
                'effective_working_days' => max(0, $empTotalWorkingDays - $leaveDetails->count()),
                'present' => $presentDetails->count(),
                'presence_pct' => round(($presentDetails->count() / (max(1, $empTotalWorkingDays - $leaveDetails->count()))) * 100, 1),
                'absent' => $absentDetails->count(),
                'absent_pct' => round(($absentDetails->count() / (max(1, $empTotalWorkingDays - $leaveDetails->count()))) * 100, 1),
                'late' => $lateDetails->count(),
                'late_pct' => round(($lateDetails->count() / (max(1, $empTotalWorkingDays - $leaveDetails->count()))) * 100, 1),
                'early' => $earlyDetails->count(),
                'early_pct' => round(($earlyDetails->count() / (max(1, $empTotalWorkingDays - $leaveDetails->count()))) * 100, 1),
                'on_time' => $onTimeDetails->count(),
                'accuracy_pct' => round(($onTimeDetails->count() / (max(1, $empTotalWorkingDays - $leaveDetails->count()))) * 100, 1),
                'leave' => $leaveDetails->count(),
                // Detail Lists for Proof Tables
                'present_list' => $presentDetails,
                'absent_list' => $absentDetails,
                'late_list' => $lateDetails,
                'early_list' => $earlyDetails,
                'on_time_list' => $onTimeDetails,
                'leave_list' => $leaveDetails,
            ]);
        }

        return view('filament.print.attendance-summary', [
            'summaries' => $summaries,
            'startDate' => $request->from_date,
            'endDate' => $request->to_date,
            'totalWorkingDays' => $summaries->max('total_working_days'), // Use max for display header context
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

    Route::get('/attendance-logs-report-pdf', function (\Illuminate\Http\Request $request) {
        $query = \App\Models\AttendanceMachineLog::query()
            ->with(['machine.officeLocation', 'employee'])
            ->when($request->from_date, fn($q, $date) => $q->whereDate('timestamp', '>=', $date))
            ->when($request->to_date, fn($q, $date) => $q->whereDate('timestamp', '<=', $date))
            ->when($request->employee_id, function($q, $id) {
                $employee = \App\Models\Employee::find($id);
                if ($employee && $employee->pin) {
                    $q->where('pin', $employee->pin);
                }
            })
            ->when($request->attendance_machine_id, fn($q, $id) => $q->where('attendance_machine_id', $id))
            ->orderBy('timestamp', 'desc');

        $records = $query->get();

        return view('filament.print.attendance-logs', [
            'records' => $records,
            'startDate' => $request->from_date,
            'endDate' => $request->to_date,
            'singleEmployee' => $request->filled('employee_id') ? \App\Models\Employee::find($request->employee_id)?->name : false,
            'singleMachine' => $request->filled('attendance_machine_id') ? \App\Models\AttendanceMachine::find($request->attendance_machine_id)?->name : false,
        ]);
    })->name('attendance.logs.report.pdf');
});

// ADMS (Attendance Machine) Routes - Root Level
Route::any('/iclock/cdata', [\App\Http\Controllers\AdmsController::class, 'cdata']);
Route::get('/iclock/getrequest', [\App\Http\Controllers\AdmsController::class, 'getrequest']);
Route::any('/iclock/devicecmd', [\App\Http\Controllers\AdmsController::class, 'devicecmd']);
