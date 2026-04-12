<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\AttendanceSchedule;
use App\Models\EmployeeAttendanceRecord;
use Illuminate\Support\Facades\Auth;

class MobileProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = Employee::with([
            'families.masterFamily',
            'employeeAgreements.masterAgreement',
            'attendanceRecords',
            'employeePermissions.permission',
            'mutations.oldPosition',
            'mutations.newPosition',
            'promotions.oldSalaryGrade',
            'promotions.newSalaryGrade',
            'salaries',
            'trainings',
            'assignmentLetters',
            'businessTravelLetters',
            'dailyReports',
            'position',
            'employmentStatus',
            'grade',
            'department',
        ])->where('users_id', $user->id)
          ->orWhere('email', $user->email)
          ->orWhere('office_email', $user->email)
          ->first();

        $monthlyStats = $this->getMonthlyStats($employee);

        return view('mobile.profile.index', compact('employee', 'monthlyStats'));
    }

    private function getMonthlyStats(?Employee $employee): array
    {
        if (!$employee) return ['presence' => 0, 'absence' => 0, 'late' => 0, 'permit' => 0, 'overtime' => '0j 0m'];

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

        $lateCount = $records->where('state', 'in')->filter(function ($r) {
            $schedule = AttendanceSchedule::where('day', $r->attendance_time->format('l'))->where('is_active', true)->first();
            return $schedule && $r->attendance_time->format('H:i:s') > $schedule->late_threshold;
        })->count();

        $permitDays = (int) $employee->employeePermissions()
            ->where('approval_status', 'approved')
            ->where(function ($q) use ($startOfMonth, $now) {
                $q->whereBetween('start_permission_date', [$startOfMonth, $now])
                  ->orWhereBetween('end_permission_date', [$startOfMonth, $now]);
            })->count();

        $workDays = 0;
        $current = $startOfMonth->copy();
        while ($current <= $now) { if (!$current->isSunday()) $workDays++; $current->addDay(); }

        $otIn = $records->where('state', 'ot_in')->sortBy('attendance_time');
        $otOut = $records->where('state', 'ot_out')->sortBy('attendance_time');
        $totalMinutes = 0;
        foreach ($otIn as $in) {
            $out = $otOut->where('attendance_time', '>', $in->attendance_time)
                ->where('attendance_time', '<', $in->attendance_time->copy()->endOfDay())->first();
            if ($out) $totalMinutes += $in->attendance_time->diffInMinutes($out->attendance_time);
        }

        return [
            'presence' => $presenceDays,
            'absence'  => max(0, $workDays - $presenceDays - $permitDays),
            'late'     => $lateCount,
            'permit'   => $permitDays,
            'overtime' => floor($totalMinutes / 60) . 'j ' . ($totalMinutes % 60) . 'm',
        ];
    }
}
