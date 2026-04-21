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
            // Delete action removed
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Aktivitas')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('log_name')
                            ->label('Nama Log')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Deskripsi'),
                        Infolists\Components\TextEntry::make('subject_type')
                            ->label('Tipe Subjek')
                            ->color('gray'),
                        Infolists\Components\TextEntry::make('subject_id')
                            ->label('ID Subjek')
                            ->color('gray'),
                        Infolists\Components\TextEntry::make('causer.name')
                            ->label('Pelaku (User)')
                            ->icon('heroicon-m-user')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Waktu Kejadian')
                            ->dateTime('d F Y, H:i:s')
                            ->icon('heroicon-m-clock'),
                    ])->columns(2),
                Infolists\Components\Section::make('Detail Perubahan Data')
                    ->description('Perbandingan nilai lama dan nilai baru yang tercatat.')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        Infolists\Components\ViewEntry::make('properties')
                            ->label('')
                            ->view('filament.admin.activity-log.properties-view')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
