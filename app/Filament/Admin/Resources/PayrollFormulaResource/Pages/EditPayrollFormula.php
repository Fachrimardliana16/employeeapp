<?php

namespace App\Filament\Admin\Resources\PayrollFormulaResource\Pages;

use App\Filament\Admin\Resources\PayrollFormulaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayrollFormula extends EditRecord
{
    protected static string $resource = PayrollFormulaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
