<?php

namespace App\Filament\Employee\Resources\MasterUnitResource\Pages;

use App\Filament\Employee\Resources\MasterUnitResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListMasterUnits extends ListRecords
{
    protected static string $resource = MasterUnitResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
