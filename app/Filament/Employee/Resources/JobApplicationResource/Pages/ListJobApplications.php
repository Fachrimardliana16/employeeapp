<?php

namespace App\Filament\Employee\Resources\JobApplicationResource\Pages;

use App\Filament\Employee\Resources\JobApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListJobApplications extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            JobApplicationResource\Widgets\JobApplicationStatsOverview::class,
        ];
    }
}
