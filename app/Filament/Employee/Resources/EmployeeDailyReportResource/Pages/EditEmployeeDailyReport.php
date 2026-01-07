<?php

namespace App\Filament\Employee\Resources\EmployeeDailyReportResource\Pages;

use App\Filament\Employee\Resources\EmployeeDailyReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeDailyReport extends EditRecord
{
    protected static string $resource = EmployeeDailyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
