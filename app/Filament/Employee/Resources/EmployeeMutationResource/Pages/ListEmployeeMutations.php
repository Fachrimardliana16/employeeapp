<?php

namespace App\Filament\Employee\Resources\EmployeeMutationResource\Pages;

use App\Filament\Employee\Resources\EmployeeMutationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeMutations extends ListRecords
{
    protected static string $resource = EmployeeMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Mutasi Baru'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeMutationResource\Widgets\MutationStatsWidget::class,
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
