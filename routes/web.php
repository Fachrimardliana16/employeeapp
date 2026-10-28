<?php

use App\Http\Controllers\AdmsController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Mobile\MobileAttendanceController;
use App\Http\Controllers\Mobile\MobileAuthController;
use App\Http\Controllers\Mobile\MobileDailyReportController;
use App\Http\Controllers\Mobile\MobileDashboardController;
use App\Http\Controllers\Mobile\MobileDocumentController;
use App\Http\Controllers\Mobile\MobileFamilyController;
use App\Http\Controllers\Mobile\MobilePermissionController;
use App\Http\Controllers\Mobile\MobileProfileController;
use App\Http\Controllers\Mobile\MobileRetirementController;
use App\Http\Controllers\Mobile\MobileTrainingController;
use App\Http\Controllers\PayrollRunLaporanController;
use App\Http\Controllers\PayrollRunSlipController;
use App\Http\Controllers\ReportController;
use App\Models\AttendanceMachine;
use App\Models\AttendanceMachineLog;
use App\Models\AttendanceSchedule;
use App\Models\AttendanceSpecialSchedule;
use App\Models\Employee;
use App\Models\EmployeeAttendanceRecord;
use App\Models\EmployeeDailyReport;
use App\Models\EmployeePermission;
use App\Models\JobApplication;
use App\Models\MasterOfficeLocation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Storage Bridge route to handle file access on restrictive hostings without symlink
Route::get('/image-view/{path}', function ($path) {
    if (Storage::disk('public')->exists($path)) {
        return response()->file(Storage::disk('public')->path($path));
    }

    if (Storage::disk('local')->exists($path)) {
        return response()->file(Storage::disk('local')->path($path));
    }

    // Direct fallback to storage/app/ for legacy files
    $fallbackPath = storage_path('app/'.$path);
    if (file_exists($fallbackPath)) {
        return response()->file($fallbackPath);
    }

    abort(404);
})->where('path', '.*');

// Redirect root to user panel
Route::redirect('/', '/user');

Route::redirect('/login', '/employee/login')->name('login');

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
    Route::get('/job-applications/{record}/print', function (JobApplication $record) {
        return view('job-applications.print-profile', compact('record'));
    })->name('job-applications.print');

    Route::get('/job-applications/{record}/print-interview-result', function (JobApplication $record) {
        $record->load(['interviewProcesses', 'archive']);

        return view('job-applications.print-interview-result', compact('record'));
    })->name('job-applications.print-interview-result');

    Route::get('/employees/{record}/print', function (Employee $record) {
        return view('reports.employee-profile', compact('record'));
    })->name('employees.print');

    Route::get('/attendance-records/{record}/print', function (EmployeeAttendanceRecord $record) {
        return view('filament.print.attendance-slip', ['record' => $record]);
    })->name('attendance.print');

    Route::get('/attendance-report', function (Request $request) {
        $query = EmployeeAttendanceRecord::query()->with('officeLocation');

        if ($request->filled('from_date')) {
            $query->whereDate('attendance_time', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('attendance_time', '<=', $request->to_date);
        }
        if ($request->filled('employee_id')) {
            $ids = is_array($request->employee_id) ? $request->employee_id : [$request->employee_id];
            $employees = Employee::whereIn('id', $ids)->get();
            $pins = $employees->pluck('pin')->filter()->toArray();
            if (! empty($pins)) {
                $query->whereIn('pin', $pins);
            }
        }
        if ($request->filled('office_location_id')) {
            $query->where('office_location_id', $request->office_location_id);
        }

        $records = $query->orderBy('attendance_time', 'asc')->get();

        // Integrate Approved Permissions (Leaves)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $permissionQuery = EmployeePermission::where('approval_status', 'approved')
                ->with(['employee', 'permission'])
                ->where(function ($q) use ($request) {
                    $q->whereBetween('start_permission_date', [$request->from_date, $request->to_date])
                        ->orWhereBetween('end_permission_date', [$request->from_date, $request->to_date])
                        ->orWhere(function ($subQ) use ($request) {
                            $subQ->where('start_permission_date', '<=', $request->from_date)
                                ->where('end_permission_date', '>=', $request->to_date);
                        });
                });

            if ($request->filled('employee_id')) {
                $ids = is_array($request->employee_id) ? $request->employee_id : [$request->employee_id];
                $permissionQuery->whereIn('employee_id', $ids);
            }

            $permissions = $permissionQuery->get();

            foreach ($permissions as $permission) {
                $reqFrom = Carbon::parse($request->from_date)->startOfDay();
                $reqTo = Carbon::parse($request->to_date)->endOfDay();
                $startDate = Carbon::parse($permission->start_permission_date)->max($reqFrom);
                $endDate = Carbon::parse($permission->end_permission_date)->min($reqTo);

                $period = CarbonPeriod::create($startDate, $endDate);

                foreach ($period as $date) {
                    $records->push((object) [
                        'id' => 'perm_'.$permission->id.'_'.$date->format('Ymd'),
                        'attendance_time' => $date->startOfDay(),
                        'employee_name' => $permission->employee->name ?? 'Pegawai',
                        'pin' => $permission->employee->pin ?? '-',
                        'state' => 'permission',
                        'attendance_status' => 'on_time',
                        'permission_name' => $permission->permission->name ?? 'Izin/Cuti',
                        'officeLocation' => (object) ['name' => '-'],
                        'distance_from_office' => null,
                        'is_within_radius' => true,
                    ]);
                }
            }

            // Re-sort records by attendance_time
            $records = $records->sortBy(function ($record) {
                return Carbon::parse($record->attendance_time)->timestamp;
            });
        }

        if ($request->filled('employee_id')) {
            $ids = is_array($request->employee_id) ? $request->employee_id : [$request->employee_id];
            $selectedEmployees = Employee::whereIn('id', $ids)->get();
            if ($selectedEmployees->count() === 1) {
                $employeeName = $selectedEmployees->first()->name;
            } elseif ($selectedEmployees->count() > 1) {
                $employeeName = 'Beberapa Pegawai ('.$selectedEmployees->count().' Orang)';
            } else {
                $employeeName = 'Beberapa Pegawai';
            }
        } else {
            $employeeName = null;
        }

        $locationName = $request->filled('office_location_id') ? MasterOfficeLocation::find($request->office_location_id)?->name : null;

        return view('filament.print.attendance-report', [
            'records' => $records,
            'startDate' => $request->from_date,
            'endDate' => $request->to_date,
            'employeeName' => $employeeName,
            'locationName' => $locationName,
        ]);
    })->name('attendance.report');

    Route::get('/attendance-summary-report', function (Request $request) {
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        // 1. Fetch Active Schedules for all days
        $allSchedules = AttendanceSchedule::where('is_active', true)->get()->keyBy(function ($item) {
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
        $query = Employee::with(['position', 'employmentStatus', 'department', 'subDepartment']);
        if ($request->filled('employee_id')) {
            $ids = is_array($request->employee_id) ? $request->employee_id : [$request->employee_id];
            $query->whereIn('id', $ids);
        }
        $employees = $query->get();

        // 3. Fetch Special Schedules for the range
        $specialSchedules = AttendanceSpecialSchedule::whereBetween('date', [$fromDate, $toDate])
            ->get()
            ->groupBy('employee_id');

        $summaries = collect();

        foreach ($employees as $employee) {
            $empSpecialSchedules = $specialSchedules->get($employee->id, collect())->keyBy(function ($item) {
                return $item->date->toDateString();
            });

            // Calculate Working Days count for summary context
            $empTotalWorkingDays = 0;
            $checkDate = $fromDate->copy();
            while ($checkDate <= $toDate) {
                $dayInd = $dayMap[strtolower($checkDate->format('l'))] ?? strtolower($checkDate->format('l'));
                if ($empSpecialSchedules->has($checkDate->toDateString())) {
                    if ($empSpecialSchedules->get($checkDate->toDateString())->is_working) {
                        $empTotalWorkingDays++;
                    }
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
            $allLogs = AttendanceMachineLog::where('pin', $employee->pin)
                ->whereBetween('timestamp', [$fromDate, $toDate])
                ->get()
                ->groupBy(fn ($item) => $item->timestamp->toDateString());

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
                    $hasLeave = EmployeePermission::where('employee_id', $employee->id)
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

                            if ($inLog && $outLog) {
                                $presentDetails->push([
                                    'date' => $dateStr,
                                    'day' => $dayInd,
                                    'time' => $inLog->timestamp->format('H:i:s'),
                                    'machine' => $inLog->machine?->name ?? 'Mesin',
                                ]);

                                // Check Lateness
                                $schedule = $allSchedules->get($dayInd);
                                if ($schedule) {
                                    $limit = $schedule->late_threshold ?: $schedule->check_in_end;

                                    if ($limit && $inLog->timestamp->format('H:i:s') > $limit) {
                                        $startTime = Carbon::parse($limit);
                                        $endTime = Carbon::parse($inLog->timestamp->format('H:i:s'));
                                        $diffInMinutes = $endTime->diffInMinutes($startTime);

                                        $lateDetails->push([
                                            'date' => $dateStr,
                                            'day' => $dayInd,
                                            'time' => $inLog->timestamp->format('H:i:s'),
                                            'limit' => $limit,
                                            'duration' => $diffInMinutes,
                                        ]);
                                    } else {
                                        $onTimeDetails->push([
                                            'date' => $dateStr,
                                            'day' => $dayInd,
                                            'time' => $inLog->timestamp->format('H:i:s'),
                                        ]);
                                    }

                                    // Check Early Leave
                                    if ($schedule->check_out_start) {
                                        if ($outLog->timestamp->format('H:i:s') < $schedule->check_out_start) {
                                            $earlyDetails->push([
                                                'date' => $dateStr,
                                                'day' => $dayInd,
                                                'time' => $outLog->timestamp->format('H:i:s'),
                                                'limit' => $schedule->check_out_start,
                                            ]);
                                        }
                                    }
                                } else {
                                    $onTimeDetails->push(['date' => $dateStr, 'day' => $dayInd, 'time' => $inLog->timestamp->format('H:i:s')]);
                                }
                            } else {
                                // Missing In or Out = Absent
                                $absentDetails->push(['date' => $dateStr, 'day' => $dayInd]);
                            }
                        } else {
                            $absentDetails->push(['date' => $dateStr, 'day' => $dayInd]);
                        }
                    }
                }
                $currentDate->addDay();
            }

            $denom = max(1, $empTotalWorkingDays - $leaveDetails->count());
            $performanceScore = $presentDetails->count() - $lateDetails->count() - $earlyDetails->count();
            $presencePerformancePct = round((max(0, $performanceScore) / $denom) * 100, 1);

            $summaries->push((object) [
                'employee' => $employee,
                'total_working_days' => $empTotalWorkingDays,
                'effective_working_days' => max(0, $empTotalWorkingDays - $leaveDetails->count()),
                'present' => $presentDetails->count(),
                'presence_pct' => round(($presentDetails->count() / $denom) * 100, 1),
                'absent' => $absentDetails->count(),
                'absent_pct' => round(($absentDetails->count() / $denom) * 100, 1),
                'late' => $lateDetails->count(),
                'late_pct' => round(($lateDetails->count() / $denom) * 100, 1),
                'early' => $earlyDetails->count(),
                'early_pct' => round(($earlyDetails->count() / $denom) * 100, 1),
                'on_time' => $onTimeDetails->count(),
                'accuracy_pct' => $presencePerformancePct, // This is the new Performance Pct
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
            'singleEmployee' => $request->filled('employee_id') ? (
                is_array($request->employee_id)
                    ? (count($request->employee_id) === 1 ? Employee::find($request->employee_id[0])?->name : 'Beberapa Pegawai ('.count($request->employee_id).' Orang)')
                    : Employee::find($request->employee_id)?->name
            ) : false,
        ]);
    })->name('attendance.summary.report');

    Route::get('/daily-reports-report', function (Request $request) {
        $query = EmployeeDailyReport::query()->with('employee');

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
        $employeeName = $request->filled('employee_id') ? Employee::find($request->employee_id)?->name : null;

        return view('filament.print.daily-report', [
            'records' => $records,
            'startDate' => $request->from_date,
            'endDate' => $request->to_date,
            'employeeName' => $employeeName,
        ]);
    })->name('daily-reports.report');

    Route::get('/career-movement-report', [ReportController::class, 'careerMovement'])->name('report.career-movement');
    Route::get('/career-schedule-report', [ReportController::class, 'careerSchedule'])->name('report.career-schedule');
    Route::get('/kgb-schedule-report', [ReportController::class, 'kgbSchedule'])->name('report.kgb-schedule');
    Route::get('/promotion-schedule-report', [ReportController::class, 'promotionSchedule'])->name('report.promotion-schedule');
    Route::get('/contract-schedule-report', [ReportController::class, 'contractSchedule'])->name('report.contract-schedule');

    Route::get('/attendance-logs-report-pdf', function (Request $request) {
        $query = AttendanceMachineLog::with(['machine.officeLocation', 'employee'])
            ->when($request->from_date, fn ($q, $date) => $q->whereDate('timestamp', '>=', $date))
            ->when($request->to_date, fn ($q, $date) => $q->whereDate('timestamp', '<=', $date))
            ->when($request->employee_id, function ($q, $id) {
                $ids = is_array($id) ? $id : [$id];
                $pins = Employee::whereIn('id', $ids)->pluck('pin')->filter()->toArray();
                if (! empty($pins)) {
                    $q->whereIn('pin', $pins);
                }
            })
            ->when($request->attendance_machine_id, fn ($q, $id) => $q->where('attendance_machine_id', $id))
            ->orderBy('timestamp', 'asc'); // Use ASC to process properly for duplicate detection

        $records = $query->get();

        $dayMap = [
            'monday' => 'SENIN', 'tuesday' => 'SELASA', 'wednesday' => 'RABU',
            'thursday' => 'KAMIS', 'friday' => 'JUMAT', 'saturday' => 'SABTU', 'sunday' => 'MINGGU',
        ];

        // 1. Identify Duplicates
        $grouped = $records->groupBy(function ($item) {
            return $item->timestamp->toDateString().'_'.$item->pin.'_'.$item->type;
        });

        foreach ($grouped as $group) {
            if ($group->count() > 1) {
                // Determine primary based on type
                $type = $group->first()->type;
                if (in_array((string) $type, ['0', '3', '4'])) {
                    $primaryId = $group->sortBy('timestamp')->first()->id;
                } elseif ((string) $type === '1') {
                    $primaryId = $group->sortByDesc('timestamp')->first()->id;
                } else {
                    $primaryId = $group->sortBy('timestamp')->first()->id;
                }

                foreach ($group as $log) {
                    $log->is_record_duplicate = ($log->id !== $primaryId);
                }
            } else {
                $group->first()->is_record_duplicate = false;
            }
        }

        // Add Indonesian Day
        $records->each(function ($log) use ($dayMap) {
            $dayEng = strtolower($log->timestamp->format('l'));
            $log->hari_indonesia = $dayMap[$dayEng] ?? $dayEng;
        });

        return view('filament.print.attendance-logs', [
            'records' => $records->sortByDesc('timestamp'), // Re-sort for display
            'startDate' => $request->from_date,
            'endDate' => $request->to_date,
            'singleEmployee' => $request->filled('employee_id') ? (
                is_array($request->employee_id)
                    ? (count($request->employee_id) === 1 ? Employee::find($request->employee_id[0])?->name : 'Beberapa Pegawai ('.count($request->employee_id).' Orang)')
                    : Employee::find($request->employee_id)?->name
            ) : false,
            'singleMachine' => $request->filled('attendance_machine_id') ? AttendanceMachine::find($request->attendance_machine_id)?->name : false,
        ]);
    })->name('attendance.logs.report.pdf');
});

// Slip Gaji Payroll Run
Route::get('/payroll-run/{run}/slip', [PayrollRunSlipController::class, 'index'])
    ->middleware(['auth'])
    ->name('payroll-run.slip');

Route::get('/payroll-run/{run}/laporan', [PayrollRunLaporanController::class, 'index'])
    ->middleware(['auth'])
    ->name('payroll-run.laporan');

// ADMS (Attendance Machine) Routes - Root Level
Route::any('/iclock/cdata', [AdmsController::class, 'cdata']);
Route::get('/iclock/getrequest', [AdmsController::class, 'getrequest']);
Route::any('/iclock/devicecmd', [AdmsController::class, 'devicecmd']);

// ─── Utility Routes (Sementara untuk Testing Redis) ─────────────────────

Route::get('/cek-redis', function () {
    try {
        \Illuminate\Support\Facades\Redis::ping();
        
        \Illuminate\Support\Facades\Cache::put('test_key', 'Redis Berhasil Menyimpan Data!', 10);
        $data = \Illuminate\Support\Facades\Cache::get('test_key');

        return "<h1>Status: BERHASIL TERKONEKSI! 🎉</h1> <p>Pesan dari Cache: " . $data . "</p>";
    } catch (\Exception $e) {
        return "<h1>Status: GAGAL ❌</h1> <p>Pesan Error: " . $e->getMessage() . "</p>";
    }
});

Route::get('/clear-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    return "<h1>Cache and Config cleared successfully!</h1>";
});
