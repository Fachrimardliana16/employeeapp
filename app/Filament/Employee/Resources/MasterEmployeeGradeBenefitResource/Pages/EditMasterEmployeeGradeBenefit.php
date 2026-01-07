<?php

namespace App\Filament\Employee\Resources\MasterEmployeeGradeBenefitResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeGradeBenefitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeeGradeBenefit extends EditRecord
{
    protected static string $resource = MasterEmployeeGradeBenefitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
