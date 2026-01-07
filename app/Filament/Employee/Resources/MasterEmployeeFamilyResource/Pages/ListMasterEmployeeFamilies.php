<?php

namespace App\Filament\Employee\Resources\MasterEmployeeFamilyResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeFamilyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterEmployeeFamilies extends ListRecords
{
    protected static string $resource = MasterEmployeeFamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
