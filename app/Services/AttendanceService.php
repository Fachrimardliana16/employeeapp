<?php

namespace App\Services;

use App\Models\AttendanceMachineLog;
use Illuminate\Support\Collection;

class AttendanceService
{
    /**
     * Mark records as duplicates within a collection of machine logs.
     */
    public static function markDuplicates(Collection $records): Collection
    {
        // Process Records for Duplicates (ASC order for logical processing)
        $records = $records->sortBy('timestamp');
        $grouped = $records->groupBy(function ($item) {
            return $item->timestamp->toDateString() . '_' . $item->pin . '_' . $item->type;
        });

        foreach ($grouped as $group) {
            if ($group->count() > 1) {
                $type = (string)$group->first()->type;
                // Logic: 
                // For IN (0, 3, 4), take the EARLIEST (min timestamp)
                // For OUT (1, 5), take the LATEST (max timestamp)
                if (in_array($type, ['0', '3', '4'])) {
                    $primaryId = $group->sortBy('timestamp')->first()->id;
                } elseif (in_array($type, ['1', '5'])) {
                    $primaryId = $group->sortByDesc('timestamp')->first()->id;
                } else {
                    $primaryId = $group->sortBy('timestamp')->first()->id;
                }
                
                foreach ($group as $log) {
                    $log->is_record_duplicate = ($log->id !== $primaryId);
                }
            } else if ($group->count() === 1) {
                $group->first()->is_record_duplicate = false;
            }
        }

        return $records;
    }

    /**
     * Get monthly attendance statistics for an employee from raw logs.
     */
    public static function getMonthlyStatsForEmployee(\App\Models\Employee $employee, int $month, int $year): array
    {
        $startOfMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = now()->isSameMonth($startOfMonth) ? now() : $endOfMonth;

        // 1. Fetch raw logs
        $logs = \App\Models\AttendanceMachineLog::where('pin', $employee->pin)
            ->whereMonth('timestamp', $month)
            ->whereYear('timestamp', $year)
            ->orderBy('timestamp', 'asc')
            ->get();

        // Mark duplicates to identifies "valid" entries
        $processedLogs = self::markDuplicates($logs);

        // 2. Presence & Late
        $validCheckIns = $processedLogs->where('is_record_duplicate', false)->where('type', '0');
        $presenceDays = $validCheckIns->pluck('timestamp')->map(fn($t) => $t->format('Y-m-d'))->unique();
        
        $schedules = \App\Models\AttendanceSchedule::where('is_active', true)->get();
        $lateCount = 0;
        foreach ($validCheckIns as $log) {
            $dayName = $log->timestamp->format('l');
            $sched = $schedules->where('day', $dayName)->first();
            $threshold = $sched ? $sched->late_threshold : '07:30:59';
            if ($log->timestamp->format('H:i:s') > $threshold) {
                $lateCount++;
            }
        }

        // 3. Permits/Leave
        $permits = $employee->employeePermissions()
            ->where('approval_status', 'approved')
            ->where(function($q) use ($startOfMonth, $today) {
                 $q->whereBetween('start_permission_date', [$startOfMonth, $today])
                   ->orWhereBetween('end_permission_date', [$startOfMonth, $today]);
            })
            ->get();
        
        $permitDays = $permits->sum(fn($p) => max(1, $p->start_permission_date->diffInDays($p->end_permission_date) + 1));

        // 4. Overtime
        $otIn = $processedLogs->where('is_record_duplicate', false)->where('type', '4');
        $otOut = $processedLogs->where('is_record_duplicate', false)->where('type', '5');
        
        $totalMinutes = 0;
        foreach ($otIn as $in) {
            $out = $otOut->where('timestamp', '>', $in->timestamp)
                ->where('timestamp', '<', $in->timestamp->copy()->endOfDay())
                ->first();
            if ($out) {
                $totalMinutes += $in->timestamp->diffInMinutes($out->timestamp);
            }
        }

        $hours = floor($totalMinutes / 60);
        $mins = $totalMinutes % 60;

        // 5. Absence
        $workDays = 0;
        $current = $startOfMonth->copy();
        while ($current <= $today) {
            if (!$current->isSunday()) $workDays++;
            $current->addDay();
        }

        $absenceDays = max(0, $workDays - $presenceDays->count() - $permitDays);

        return [
            'presence' => $presenceDays->count(),
            'late' => $lateCount,
            'permit' => $permitDays,
            'absence' => $absenceDays,
            'overtime_count' => $otIn->count(),
            'overtime_hours' => "{$hours}j {$mins}m",
        ];
    }
}

