<?php

namespace App\Filament\Employee\Resources\EmployeePromotionResource\Pages;

use App\Filament\Employee\Resources\EmployeePromotionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeePromotions extends ListRecords
{
    protected static string $resource = EmployeePromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeePromotionResource\Widgets\PromotionStatsWidget::class,
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
