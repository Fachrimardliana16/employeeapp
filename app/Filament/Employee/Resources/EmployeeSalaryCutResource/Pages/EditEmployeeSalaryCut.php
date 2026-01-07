<?php

namespace App\Filament\Employee\Resources\EmployeeSalaryCutResource\Pages;

use App\Filament\Employee\Resources\EmployeeSalaryCutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeSalaryCut extends EditRecord
{
    protected static string $resource = EmployeeSalaryCutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
