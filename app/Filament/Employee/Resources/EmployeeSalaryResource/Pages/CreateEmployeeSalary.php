<?php

namespace App\Filament\Employee\Resources\EmployeeSalaryResource\Pages;

use App\Filament\Employee\Resources\EmployeeSalaryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeSalary extends CreateRecord
{
    protected static string $resource = EmployeeSalaryResource::class;
}
