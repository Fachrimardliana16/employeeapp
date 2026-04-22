<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ActivityLogResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

use Filament\Infolists;
use Filament\Infolists\Infolist;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Log Aktivitas';

    protected static ?string $modelLabel = 'Log Aktivitas';

    protected static ?string $pluralModelLabel = 'Log Aktivitas';

    protected static ?string $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 2;

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Dasar')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('log_name')
                                    ->label('Nama Log')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('description')
                                    ->label('Deskripsi'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Waktu Aktivitas')
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                    ]),

                Infolists\Components\Grid::make(2)
                    ->schema([
                        Infolists\Components\Section::make('Subjek & Pelaku')
                            ->schema([
                                Infolists\Components\TextEntry::make('causer.name')
                                    ->label('Pengguna (Pelaku)')
                                    ->placeholder('Sistem'),
                                Infolists\Components\TextEntry::make('subject_type')
                                    ->label('Tipe Subjek'),
                                Infolists\Components\TextEntry::make('subject_id')
                                    ->label('ID Subjek'),
                            ])->columnSpan(1),

                        Infolists\Components\Section::make('Konteks')
                            ->schema([
                                Infolists\Components\TextEntry::make('event')
                                    ->label('Event')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'created' => 'success',
                                        'updated' => 'warning',
                                        'deleted' => 'danger',
                                        default => 'gray',
                                    }),
                                Infolists\Components\TextEntry::make('batch_uuid')
                                    ->label('Batch ID')
                                    ->placeholder('-'),
                            ])->columnSpan(1),
                    ]),

                Infolists\Components\Section::make('Data Perubahan')
                    ->icon('heroicon-m-arrow-path')
                    ->schema([
                        Infolists\Components\KeyValue::make('properties.attributes')
                            ->label('Data Baru / Sekarang'),
                        Infolists\Components\KeyValue::make('properties.old')
                            ->label('Data Lama (Sebelum Perubahan)')
                            ->visible(fn ($record) => !empty($record->properties['old'])),
                    ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Nama Log')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Tipe Subjek')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label('ID Subjek')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Nama Log'),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->label('Tipe Subjek'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
