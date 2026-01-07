<?php

namespace App\Filament\User\Resources\MyPermissionResource\Pages;

use App\Filament\User\Resources\MyPermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyPermissions extends ListRecords
{
    protected static string $resource = MyPermissionResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajukan Izin/Cuti')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
