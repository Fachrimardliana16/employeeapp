<?php

namespace App\Filament\Employee\Resources\MasterBagianResource\Pages;

use App\Filament\Employee\Resources\MasterBagianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterBagians extends ListRecords
{
    protected static string $resource = MasterBagianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
