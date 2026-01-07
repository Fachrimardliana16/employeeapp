<?php

namespace App\Filament\Employee\Resources\PerformanceAppraisalResource\Pages;

use App\Filament\Employee\Resources\PerformanceAppraisalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePerformanceAppraisal extends CreateRecord
{
    protected static string $resource = PerformanceAppraisalResource::class;
}
