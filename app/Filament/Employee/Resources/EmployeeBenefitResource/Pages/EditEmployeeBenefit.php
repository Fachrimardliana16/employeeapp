<?php

namespace App\Filament\Employee\Resources\EmployeeBenefitResource\Pages;

use App\Filament\Employee\Resources\EmployeeBenefitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeBenefit extends EditRecord
{
    protected static string $resource = EmployeeBenefitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
