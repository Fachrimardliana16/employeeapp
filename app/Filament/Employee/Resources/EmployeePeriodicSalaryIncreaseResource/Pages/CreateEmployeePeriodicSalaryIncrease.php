<?php

namespace App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource\Pages;

use App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeePeriodicSalaryIncrease extends CreateRecord
{
    protected static string $resource = EmployeePeriodicSalaryIncreaseResource::class;
}
