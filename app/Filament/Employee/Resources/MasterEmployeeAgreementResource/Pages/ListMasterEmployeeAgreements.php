<?php

namespace App\Filament\Employee\Resources\MasterEmployeeAgreementResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterEmployeeAgreements extends ListRecords
{
    protected static string $resource = MasterEmployeeAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
