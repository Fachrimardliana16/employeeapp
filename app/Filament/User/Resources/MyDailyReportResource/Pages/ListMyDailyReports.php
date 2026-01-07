<?php

namespace App\Filament\User\Resources\MyDailyReportResource\Pages;

use App\Filament\User\Resources\MyDailyReportResource;
use Filament\Resources\Pages\ListRecords;

class ListMyDailyReports extends ListRecords
{
    protected static string $resource = MyDailyReportResource::class;
    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
