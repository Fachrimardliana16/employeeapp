<?php

namespace App\Filament\Employee\Resources\JobApplicationArchiveResource\Pages;

use App\Filament\Employee\Resources\JobApplicationArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewJobApplicationArchive extends ViewRecord
{
    protected static string $resource = JobApplicationArchiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada edit action karena archive read-only
        ];
    }
}
