<?php

namespace App\Filament\Employee\Resources\InterviewProcessResource\Pages;

use App\Filament\Employee\Resources\InterviewProcessResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInterviewProcesses extends ListRecords
{
    protected static string $resource = InterviewProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
