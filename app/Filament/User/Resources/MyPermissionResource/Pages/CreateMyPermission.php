<?php

namespace App\Filament\User\Resources\MyPermissionResource\Pages;

use App\Filament\User\Resources\MyPermissionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMyPermission extends CreateRecord
{
    protected static string $resource = MyPermissionResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $employee = \App\Models\Employee::where('email', Auth::user()->email)->first();
        $data['employee_id'] = $employee?->id;
        $data['approval_status'] = 'pending';
        $data['users_id'] = Auth::id();
        return $data;
    }
}
