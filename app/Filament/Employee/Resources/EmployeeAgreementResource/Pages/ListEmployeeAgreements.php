<?php

namespace App\Filament\Employee\Resources\EmployeeAgreementResource\Pages;

use App\Filament\Employee\Resources\EmployeeAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeAgreements extends ListRecords
{
    protected static string $resource = EmployeeAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeAgreementResource\Widgets\EmployeeAgreementStatsOverview::class,
        ];
    }
}
