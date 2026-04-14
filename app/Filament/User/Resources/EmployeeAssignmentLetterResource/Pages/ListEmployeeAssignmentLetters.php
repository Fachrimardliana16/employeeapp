<?php

namespace App\Filament\User\Resources\EmployeeAssignmentLetterResource\Pages;

use App\Filament\User\Resources\EmployeeAssignmentLetterResource;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeAssignmentLetters extends ListRecords
{
    protected static string $resource = EmployeeAssignmentLetterResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeAssignmentLetterResource\Widgets\UserAssignmentLetterStats::class,
        ];
    }
}
