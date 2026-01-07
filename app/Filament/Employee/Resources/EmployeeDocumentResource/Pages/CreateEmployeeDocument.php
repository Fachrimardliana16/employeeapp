<?php

namespace App\Filament\Employee\Resources\EmployeeDocumentResource\Pages;

use App\Filament\Employee\Resources\EmployeeDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeDocument extends CreateRecord
{
    protected static string $resource = EmployeeDocumentResource::class;
}
