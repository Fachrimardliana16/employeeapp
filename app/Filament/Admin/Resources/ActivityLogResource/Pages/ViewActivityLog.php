<?php

namespace App\Filament\Admin\Resources\ActivityLogResource\Pages;

use App\Filament\Admin\Resources\ActivityLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Activity Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('log_name')
                            ->label('Log Name')
                            ->badge(),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description'),
                        Infolists\Components\TextEntry::make('subject_type')
                            ->label('Subject Type'),
                        Infolists\Components\TextEntry::make('subject_id')
                            ->label('Subject ID'),
                        Infolists\Components\TextEntry::make('causer.name')
                            ->label('User'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Date')
                            ->dateTime(),
                    ])->columns(2),
                Infolists\Components\Section::make('Properties')
                    ->schema([
                        Infolists\Components\TextEntry::make('properties')
                            ->label('Properties')
                            ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
