<?php

namespace App\Filament\User\Resources\MyAttendanceResource\Pages;

use App\Filament\User\Resources\MyAttendanceResource;
use Filament\Resources\Pages\ListRecords;

class ListMyAttendances extends ListRecords
{
    protected static string $resource = MyAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
