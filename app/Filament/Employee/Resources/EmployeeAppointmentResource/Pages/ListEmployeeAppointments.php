<?php

namespace App\Filament\Employee\Resources\EmployeeAppointmentResource\Pages;

use App\Filament\Employee\Resources\EmployeeAppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeAppointments extends ListRecords
{
    protected static string $resource = EmployeeAppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Pengangkatan Baru')
                ->icon('heroicon-m-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeAppointmentResource\Widgets\AppointmentStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make('Semua'),
            'usulan' => \Filament\Resources\Components\Tab::make('Usulan')
                ->modifyQueryUsing(fn ($query) => $query->where('is_applied', false))
                ->icon('heroicon-m-document-text'),
            'realisasi' => \Filament\Resources\Components\Tab::make('Realisasi')
                ->modifyQueryUsing(fn ($query) => $query->where('is_applied', true))
                ->icon('heroicon-m-check-badge'),
        ];
    }
}
