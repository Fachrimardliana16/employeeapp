<?php

namespace App\Filament\Employee\Resources\MasterEmployeeBenefitResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeBenefitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeeBenefit extends EditRecord
{
    protected static string $resource = MasterEmployeeBenefitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
