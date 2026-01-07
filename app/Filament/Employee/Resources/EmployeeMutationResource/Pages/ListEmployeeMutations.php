<?php

namespace App\Filament\Employee\Resources\EmployeeMutationResource\Pages;

use App\Filament\Employee\Resources\EmployeeMutationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeMutations extends ListRecords
{
    protected static string $resource = EmployeeMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Mutasi Baru'),
        ];
    }
}
