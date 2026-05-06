<?php

namespace App\Filament\Employee\Resources\MasterPromotionTypeResource\Pages;

use App\Filament\Employee\Resources\MasterPromotionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterPromotionTypes extends ListRecords
{
    protected static string $resource = MasterPromotionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
