<?php

namespace App\Filament\Employee\Resources\MasterPromotionTypeResource\Pages;

use App\Filament\Employee\Resources\MasterPromotionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterPromotionType extends EditRecord
{
    protected static string $resource = MasterPromotionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
