<?php

namespace App\Filament\Employee\Resources\MasterEmployeeGradeBenefitResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeGradeBenefitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterEmployeeGradeBenefits extends ListRecords
{
    protected static string $resource = MasterEmployeeGradeBenefitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
