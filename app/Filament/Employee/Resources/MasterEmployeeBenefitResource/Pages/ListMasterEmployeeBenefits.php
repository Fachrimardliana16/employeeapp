<?php

namespace App\Filament\Employee\Resources\MasterEmployeeBenefitResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeBenefitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterEmployeeBenefits extends ListRecords
{
    protected static string $resource = MasterEmployeeBenefitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
