<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ActivityLogResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Log Aktivitas';

    protected static ?string $modelLabel = 'Log Aktivitas';

    protected static ?string $pluralModelLabel = 'Log Aktivitas';

    protected static ?string $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 2;

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Informasi Dasar')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        \Filament\Infolists\Components\Grid::make(3)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('log_name')
                                    ->label('Nama Log')
                                    ->badge(),
                                \Filament\Infolists\Components\TextEntry::make('description')
                                    ->label('Deskripsi'),
                                \Filament\Infolists\Components\TextEntry::make('created_at')
                                    ->label('Waktu Aktivitas')
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                    ]),

                \Filament\Infolists\Components\Grid::make(3)
                    ->schema([
                        \Filament\Infolists\Components\Section::make('Subjek')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('subject_type')
                                    ->label('Tipe Subjek'),
                                \Filament\Infolists\Components\TextEntry::make('subject_id')
                                    ->label('ID Subjek'),
                            ])->columnSpan(1),

                        \Filament\Infolists\Components\Section::make('Pelaku (Pelaksana)')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('causer_id')
                                    ->label('ID Pelaku')
                                    ->placeholder('System / Otomatis'),
                                \Filament\Infolists\Components\TextEntry::make('causer_type')
                                    ->label('Tipe Pelaku')
                                    ->placeholder('-'),
                            ])->columnSpan(1),

                        \Filament\Infolists\Components\Section::make('Konteks')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('event')
                                    ->label('Event')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'created' => 'success',
                                        'updated' => 'warning',
                                        'deleted' => 'danger',
                                        default => 'gray',
                                    }),
                                \Filament\Infolists\Components\TextEntry::make('batch_uuid')
                                    ->label('Batch ID')
                                    ->placeholder('-'),
                            ])->columnSpan(1),
                    ]),

                \Filament\Infolists\Components\Section::make('Detail Perubahan')
                    ->icon('heroicon-m-arrow-path')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('properties')
                            ->label('Properti Log (JSON)')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return '-';
                                try {
                                    $data = is_array($state) ? $state : json_decode($state, true);
                                    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                                } catch (\Exception $e) {
                                    return 'Data corrupt or invalid JSON';
                                }
                            })
                            ->fontFamily('mono'),
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
