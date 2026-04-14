<?php

namespace App\Filament\User\Resources\EmployeeAssignmentLetterResource\Widgets;

use App\Models\EmployeeAssignmentLetter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserAssignmentLetterStats extends BaseWidget
{
    protected function getStats(): array
    {
        $year = Carbon::now()->year;
        $employeeId = Auth::user()->employee?->id;

        if (!$employeeId) {
            return [];
        }

        $query = EmployeeAssignmentLetter::whereYear('start_date', $year)
            ->where(function ($q) use ($employeeId) {
                $q->where('assigning_employee_id', $employeeId)
                  ->orWhereJsonContains('additional_employee_ids', (string)$employeeId)
                  ->orWhereJsonContains('additional_employee_ids', (int)$employeeId);
            })
            ->whereNotNull('signed_file_path');

        $total = (clone $query)->count();
        $selesai = (clone $query)->where('status', 'selesai')->count();
        $onProgress = (clone $query)->where('status', 'on progress')->count();

        return [
            Stat::make('Tugas Saya ('.$year.')', $total)
                ->description('Total penugasan resmi')
                ->color('info'),
            Stat::make('Tugas Selesai', $selesai)
                ->description('Dokumen telah lengkap')
                ->color('success'),
            Stat::make('Tugas On Progress', $onProgress)
                ->description('Perlu diupload cap kunjungan')
                ->color('warning'),
        ];
    }
}
