<?php

namespace App\Filament\Employee\Resources\ActivityLogResource\Pages;

use App\Filament\Employee\Resources\ActivityLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
