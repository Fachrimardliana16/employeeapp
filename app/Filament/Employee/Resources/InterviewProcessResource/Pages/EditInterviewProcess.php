<?php

namespace App\Filament\Employee\Resources\InterviewProcessResource\Pages;

use App\Filament\Employee\Resources\InterviewProcessResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInterviewProcess extends EditRecord
{
    protected static string $resource = InterviewProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
