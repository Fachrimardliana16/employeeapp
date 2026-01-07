<?php

namespace App\Filament\Employee\Resources\PerformanceAppraisalResource\Pages;

use App\Filament\Employee\Resources\PerformanceAppraisalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerformanceAppraisal extends EditRecord
{
    protected static string $resource = PerformanceAppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
