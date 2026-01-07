<?php

namespace App\Filament\Admin\Resources\PayrollComponentResource\Pages;

use App\Filament\Admin\Resources\PayrollComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayrollComponents extends ListRecords
{
    protected static string $resource = PayrollComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
