<?php

namespace App\Filament\Admin\Resources\MasterStandarHargaSatuanResource\Pages;

use App\Filament\Admin\Resources\MasterStandarHargaSatuanResource;
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
