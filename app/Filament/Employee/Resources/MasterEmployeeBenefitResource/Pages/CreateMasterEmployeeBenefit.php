<?php

namespace App\Filament\Employee\Resources\MasterEmployeeBenefitResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeBenefitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterEmployeeBenefit extends CreateRecord
{
    protected static string $resource = MasterEmployeeBenefitResource::class;
}
