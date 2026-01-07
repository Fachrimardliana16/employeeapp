<?php

namespace App\Filament\Admin\Resources\PayrollComponentResource\Pages;

use App\Filament\Admin\Resources\PayrollComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayrollComponent extends EditRecord
{
    protected static string $resource = PayrollComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
