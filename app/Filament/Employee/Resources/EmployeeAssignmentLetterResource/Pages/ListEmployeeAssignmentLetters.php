<?php

namespace App\Filament\Employee\Resources\EmployeeAssignmentLetterResource\Pages;

use App\Filament\Employee\Resources\EmployeeAssignmentLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeAssignmentLetters extends ListRecords
{
    protected static string $resource = EmployeeAssignmentLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeAssignmentLetterResource\Widgets\AssignmentLetterStats::class,
        ];
    }
}
