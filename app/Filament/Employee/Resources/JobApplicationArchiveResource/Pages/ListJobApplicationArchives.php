<?php

namespace App\Filament\Employee\Resources\JobApplicationArchiveResource\Pages;

use App\Filament\Employee\Resources\JobApplicationArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobApplicationArchives extends ListRecords
{
    protected static string $resource = JobApplicationArchiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada create action karena archive dibuat otomatis
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            JobApplicationArchiveResource\Widgets\JobApplicationArchiveStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make('Semua'),
            'accepted' => \Filament\Resources\Components\Tab::make('Diterima')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('decision', 'accepted'))
                ->icon('heroicon-m-check-badge'),
            'rejected' => \Filament\Resources\Components\Tab::make('Ditolak')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('decision', 'rejected'))
                ->icon('heroicon-m-x-circle'),
            'has_agreement' => \Filament\Resources\Components\Tab::make('Kontrak Terbit')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->whereHas('employeeAgreement'))
                ->icon('heroicon-m-document-text'),
        ];
    }
}
