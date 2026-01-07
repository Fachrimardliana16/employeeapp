<?php

namespace App\Filament\Employee\Resources\MasterEmployeeSalaryCutResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeSalaryCutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterEmployeeSalaryCuts extends ListRecords
{
    protected static string $resource = MasterEmployeeSalaryCutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
