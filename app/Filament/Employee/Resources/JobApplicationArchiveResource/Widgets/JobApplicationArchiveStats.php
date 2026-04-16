<?php

namespace App\Filament\Employee\Resources\JobApplicationArchiveResource\Widgets;

use App\Models\JobApplicationArchive;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JobApplicationArchiveStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Arsip', JobApplicationArchive::count())
                ->description('Total lamaran diarsipkan')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),

            Stat::make('Diterima', JobApplicationArchive::where('decision', 'accepted')->count())
                ->description('Kandidat yang lolos seleksi')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Ditolak', JobApplicationArchive::where('decision', 'rejected')->count())
                ->description('Kandidat yang tidak lolos')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Kontrak Terbit', JobApplicationArchive::whereHas('employeeAgreement')->count())
                ->description('Kontrak kerja sudah dibuat')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
        ];
    }
}
