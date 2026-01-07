<?php

namespace App\Filament\Employee\Resources\EmployeeAgreementResource\Widgets;

use App\Models\EmployeeAgreement;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeAgreementStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Get total agreements
        $totalAgreements = EmployeeAgreement::count();

        // Get new agreements this month
        $newAgreementsThisMonth = EmployeeAgreement::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Get active agreements (not expired)
        $activeAgreements = EmployeeAgreement::where('agreement_date_end', '>=', Carbon::now()->toDateString())
            ->count();

        // Get agreements expiring soon (within 30 days)
        $expiringSoon = EmployeeAgreement::whereBetween('agreement_date_end', [
            Carbon::now()->toDateString(),
            Carbon::now()->addDays(30)->toDateString()
        ])->count();

        // Get agreements by type
        $pkwtCount = EmployeeAgreement::whereHas('masterAgreement', function ($query) {
            $query->where('name', 'like', '%PKWT%')
                  ->orWhere('name', 'like', '%kontrak%')
                  ->orWhere('name', 'like', '%tertentu%');
        })->count();

        $pkwttCount = EmployeeAgreement::whereHas('masterAgreement', function ($query) {
            $query->where('name', 'like', '%PKWTT%')
                  ->orWhere('name', 'like', '%tetap%')
                  ->orWhere('name', 'like', '%tidak tertentu%');
        })->count();

        // Get average contract duration
        $avgDuration = EmployeeAgreement::whereNotNull('agreement_date_start')
            ->whereNotNull('agreement_date_end')
            ->get()
            ->map(function ($agreement) {
                return Carbon::parse($agreement->agreement_date_start)
                    ->diffInMonths(Carbon::parse($agreement->agreement_date_end));
            })
            ->average();

        $avgDurationYears = $avgDuration ? round($avgDuration / 12, 1) : 0;

        // Get completion rate (agreements with all required fields)
        $completeAgreements = EmployeeAgreement::whereNotNull('agreement_number')
            ->whereNotNull('name')
            ->whereNotNull('agreement_id')
            ->whereNotNull('employee_position_id')
            ->whereNotNull('employment_status_id')
            ->whereNotNull('basic_salary_id')
            ->whereNotNull('agreement_date_start')
            ->whereNotNull('agreement_date_end')
            ->whereNotNull('departments_id')
            ->count();

        $completionRate = $totalAgreements > 0
            ? round(($completeAgreements / $totalAgreements) * 100, 1)
            : 0;

        return [
            Stat::make('Total Kontrak', $totalAgreements)
                ->description($newAgreementsThisMonth . ' kontrak baru bulan ini')
                ->descriptionIcon('heroicon-m-document-plus')
                ->color('primary')
                ->chart([5, 4, 6, 7, 5, 8, 6]),

            Stat::make('Kontrak Aktif', $activeAgreements)
                ->description($expiringSoon . ' akan berakhir dalam 30 hari')
                ->descriptionIcon($expiringSoon > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($expiringSoon > 0 ? 'warning' : 'success')
                ->chart([3, 4, 5, 6, 4, 7, 5]),

            Stat::make('PKWT vs PKWTT', $pkwtCount . ' : ' . $pkwttCount)
                ->description('Kontrak vs Tetap')
                ->descriptionIcon('heroicon-m-scale')
                ->color('info')
                ->chart([$pkwttCount, $pkwtCount, $pkwttCount, $pkwtCount, $pkwttCount, $pkwtCount, $pkwtCount]),

            Stat::make('Kelengkapan Data', $completionRate . '%')
                ->description('Rata-rata durasi: ' . $avgDurationYears . ' tahun')
                ->descriptionIcon($completionRate >= 80 ? 'heroicon-m-check-circle' : 'heroicon-m-document-text')
                ->color($completionRate >= 80 ? 'success' : ($completionRate >= 60 ? 'warning' : 'danger'))
                ->chart([50, 60, 70, 75, 80, 75, $completionRate]),
        ];
    }
}
