<?php

namespace App\Filament\Admin\Resources\MasterOfficeLocationResource\Pages;

use App\Filament\Admin\Resources\MasterOfficeLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterOfficeLocation extends EditRecord
{
    protected static string $resource = MasterOfficeLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
