<?php

namespace App\Filament\User\Resources\MyDailyReportResource\Pages;

use App\Filament\User\Resources\MyDailyReportResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMyDailyReport extends CreateRecord
{
    protected static string $resource = MyDailyReportResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $employee = \App\Models\Employee::where('email', Auth::user()->email)->first();
        $data['employee_id'] = $employee?->id;
        $data['users_id'] = Auth::id();
        return $data;
    }
}
