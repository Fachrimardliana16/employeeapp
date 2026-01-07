<?php

namespace App\Filament\User\Resources\EmployeeDocumentResource\Pages;

use App\Filament\User\Resources\EmployeeDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeDocument extends CreateRecord
{
    protected static string $resource = EmployeeDocumentResource::class;
}
