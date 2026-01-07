<?php

namespace App\Filament\Employee\Resources\EmployeePromotionResource\Pages;

use App\Filament\Employee\Resources\EmployeePromotionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeePromotion extends EditRecord
{
    protected static string $resource = EmployeePromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
