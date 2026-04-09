<?php

namespace App\Filament\Employee\Resources\MasterCabangResource\Pages;

use App\Filament\Employee\Resources\MasterCabangResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListMasterCabangs extends ListRecords
{
    protected static string $resource = MasterCabangResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
