<?php

namespace App\Filament\Employee\Resources\MasterEmployeeGradeSalaryCutResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeGradeSalaryCutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeeGradeSalaryCut extends EditRecord
{
    protected static string $resource = MasterEmployeeGradeSalaryCutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
