<?php

namespace App\Filament\Admin\Resources\PayrollFormulaResource\Pages;

use App\Filament\Admin\Resources\PayrollFormulaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayrollFormulas extends ListRecords
{
    protected static string $resource = PayrollFormulaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
