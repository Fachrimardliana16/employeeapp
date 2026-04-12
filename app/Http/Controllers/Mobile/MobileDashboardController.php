<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\AttendanceSchedule;
use App\Models\EmployeeAttendanceRecord;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MobileDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = Employee::with(['position', 'employmentStatus', 'department'])
            ->where('users_id', $user->id)
            ->orWhere('email', $user->email)
            ->orWhere('office_email', $user->email)
            ->first();

        $stats = $this->getMonthlyStats($employee);
        $recentAttendance = $this->getRecentAttendance($employee);
        $todaySchedule = $this->getTodaySchedule();
        $todayRecords = $this->getTodayRecords($employee);

        return view('mobile.dashboard.index', compact(
            'employee',
            'stats',
            'recentAttendance',
            'todaySchedule',
            'todayRecords'
        ));
    }

    private function getMonthlyStats(?Employee $employee): array
    {
        if (!$employee) {
            return ['presence' => 0, 'absence' => 0, 'late' => 0, 'permit' => 0, 'overtime' => 0];
        }

        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();

        $records = $employee->attendanceRecords()
            ->whereMonth('attendance_time', $now->month)
            ->whereYear('attendance_time', $now->year)
            ->get();

        $presenceDays = $records->where('state', 'in')
            ->pluck('attendance_time')
            ->map(fn($t) => $t->format('Y-m-d'))
            ->unique()->count();

        $lateCount = $records->where('attendance_status', 'late')->count();

        $permitDays = (int) $employee->employeePermissions()
            ->where('approval_status', 'approved')
            ->where(function ($q) use ($startOfMonth, $now) {
                $q->whereBetween('start_permission_date', [$startOfMonth, $now])
                  ->orWhereBetween('end_permission_date', [$startOfMonth, $now]);
            })->count();

        $workDays = 0;
        $current = $startOfMonth->copy();
        while ($current <= $now) {
            if (!$current->isSunday()) $workDays++;
            $current->addDay();
        }

        return [
            'presence' => $presenceDays,
            'absence'  => max(0, $workDays - $presenceDays - $permitDays),
            'late'     => $lateCount,
            'permit'   => $permitDays,
            'overtime' => $records->where('state', 'ot_in')->count(),
        ];
    }

    private function getRecentAttendance(?Employee $employee)
    {
        if (!$employee) return collect();
        return $employee->attendanceRecords()
            ->orderBy('attendance_time', 'desc')
            ->limit(7)
            ->get();
    }

    private function getTodaySchedule()
    {
        $dayName = now()->format('l');
        return AttendanceSchedule::where('day', $dayName)->where('is_active', true)->first();
    }

    private function getTodayRecords(?Employee $employee)
    {
        if (!$employee) return collect();
        return $employee->attendanceRecords()
            ->whereDate('attendance_time', today())
            ->orderBy('attendance_time')
            ->get();
    }
}
