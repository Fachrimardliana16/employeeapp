<?php

namespace App\Filament\Employee\Resources\MasterEmployeeSalaryCutResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeSalaryCutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeeSalaryCut extends EditRecord
{
    protected static string $resource = MasterEmployeeSalaryCutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
