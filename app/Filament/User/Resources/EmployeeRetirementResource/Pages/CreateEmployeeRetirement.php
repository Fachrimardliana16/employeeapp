<?php

namespace App\Filament\User\Resources\EmployeeRetirementResource\Pages;

use App\Filament\User\Resources\EmployeeRetirementResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEmployeeRetirement extends CreateRecord
{
    protected static string $resource = EmployeeRetirementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $employee = \App\Models\Employee::where('email', Auth::user()->email)->first();
        $data['employee_id'] = $employee?->id;
        $data['approval_status'] = 'pending';
        $data['users_id'] = Auth::id();
        return $data;
    }
}
