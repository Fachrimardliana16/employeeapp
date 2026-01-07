<?php

namespace App\Filament\Employee\Resources\JobApplicationArchiveResource\Pages;

use App\Filament\Employee\Resources\JobApplicationArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobApplicationArchives extends ListRecords
{
    protected static string $resource = JobApplicationArchiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada create action karena archive dibuat otomatis
        ];
    }
}
