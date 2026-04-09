<?php

namespace App\Filament\Employee\Resources\JobApplicationResource\Pages;

use App\Filament\Employee\Resources\JobApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewJobApplication extends ViewRecord
{
    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Cetak Profil')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn ($record) => route('job-applications.print', $record))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }
}
