<?php

namespace App\Filament\User\Resources\EmployeeDocumentResource\Pages;

use App\Filament\User\Resources\EmployeeDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEmployeeDocument extends CreateRecord
{
    protected static string $resource = EmployeeDocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['employee_id'] = Auth::user()->employee->id ?? null;
        $data['uploaded_by'] = 'Karyawan';
        $data['users_id'] = Auth::id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
