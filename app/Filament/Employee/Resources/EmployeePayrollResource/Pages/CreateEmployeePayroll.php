<?php

namespace App\Filament\Employee\Resources\EmployeePayrollResource\Pages;

use App\Filament\Employee\Resources\EmployeePayrollResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeePayroll extends CreateRecord
{
    protected static string $resource = EmployeePayrollResource::class;
}
