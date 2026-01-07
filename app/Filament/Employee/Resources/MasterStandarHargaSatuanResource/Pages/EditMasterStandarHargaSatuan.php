<?php

namespace App\Filament\Employee\Resources\MasterStandarHargaSatuanResource\Pages;

use App\Filament\Employee\Resources\MasterStandarHargaSatuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterStandarHargaSatuan extends EditRecord
{
    protected static string $resource = MasterStandarHargaSatuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
