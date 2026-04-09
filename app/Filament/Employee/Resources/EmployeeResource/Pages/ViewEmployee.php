<?php

namespace App\Filament\Employee\Resources\EmployeeResource\Pages;

use App\Filament\Employee\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Cetak Profil')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn($record): string => route('employees.print', $record))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }
}
