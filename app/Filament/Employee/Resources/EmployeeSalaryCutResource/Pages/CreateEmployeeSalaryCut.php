<?php

namespace App\Filament\Employee\Resources\EmployeeSalaryCutResource\Pages;

use App\Filament\Employee\Resources\EmployeeSalaryCutResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeSalaryCut extends CreateRecord
{
    protected static string $resource = EmployeeSalaryCutResource::class;
}
