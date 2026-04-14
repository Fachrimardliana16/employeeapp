<?php

namespace App\Filament\Employee\Resources\EmployeeBusinessTravelLetterResource\Pages;

use App\Filament\Employee\Resources\EmployeeBusinessTravelLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeBusinessTravelLetters extends ListRecords
{
    protected static string $resource = EmployeeBusinessTravelLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeBusinessTravelLetterResource\Widgets\BusinessTravelLetterStats::class,
        ];
    }
}
