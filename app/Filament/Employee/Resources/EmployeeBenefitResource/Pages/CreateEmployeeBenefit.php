<?php

namespace App\Filament\Employee\Resources\EmployeeBenefitResource\Pages;

use App\Filament\Employee\Resources\EmployeeBenefitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeBenefit extends CreateRecord
{
    protected static string $resource = EmployeeBenefitResource::class;
}
