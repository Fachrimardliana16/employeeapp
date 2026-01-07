<?php

namespace App\Filament\Employee\Resources\MasterStandarHargaSatuanResource\Pages;

use App\Filament\Employee\Resources\MasterStandarHargaSatuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterStandarHargaSatuans extends ListRecords
{
    protected static string $resource = MasterStandarHargaSatuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
