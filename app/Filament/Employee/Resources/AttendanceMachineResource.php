<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\AttendanceMachineResource\Pages;
use App\Models\AttendanceMachine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceMachineResource extends Resource
{
    protected static ?string $model = AttendanceMachine::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Absensi & Kehadiran';

    protected static ?string $modelLabel = 'Mesin Absensi';

    protected static ?string $pluralModelLabel = 'Mesin Absensi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Mesin')
                    ->schema([
                        Forms\Components\TextInput::make('serial_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('Serial Number (SN)'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Nama Mesin'),
                        Forms\Components\Select::make('master_office_location_id')
                            ->relationship('officeLocation', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Lokasi Kantor'),
                        Forms\Components\Placeholder::make('last_heard_at')
                            ->content(fn($record) => $record?->last_heard_at?->diffForHumans() ?? '-')
                            ->label('Terakhir Aktif'),
                        Forms\Components\Placeholder::make('ip_address')
                            ->content(fn($record) => $record?->ip_address ?? '-')
                            ->label('Alamat IP'),
                    ])->columns(2),
                Forms\Components\Section::make('Pengaturan Waktu Mesin')
                    ->description('Pengaturan sinkronisasi jam antara mesin dan server.')
                    ->schema([
                        Forms\Components\Toggle::make('auto_sync_time')
                            ->label('Auto Sinkron Jam')
                            ->helperText('Aktifkan hanya jika mesin mendukung sinkronisasi jam otomatis. Beberapa mesin dapat mengalami drift +1 jam jika diaktifkan.')
                            ->reactive()
                            ->default(false),
                        Forms\Components\TextInput::make('timezone_offset')
                            ->label('Offset Timezone (jam)')
                            ->helperText('Contoh: 7 untuk WIB (UTC+7). Digunakan saat auto sinkron jam aktif.')
                            ->numeric()
                            ->default(7)
                            ->minValue(-12)
                            ->maxValue(14)
                            ->visible(fn(Forms\Get $get) => (bool) $get('auto_sync_time')),
                    ])->columns(2),
                Forms\Components\Section::make('Statistik Komunikasi')
                    ->description('Informasi komunikasi antara mesin dan server.')
                    ->schema([
                        Forms\Components\Placeholder::make('communication_success_count')
                            ->content(fn($record) => number_format($record?->communication_success_count ?? 0))
                            ->label('Komunikasi Berhasil'),
                        Forms\Components\Placeholder::make('communication_error_count')
                            ->content(fn($record) => number_format($record?->communication_error_count ?? 0))
                            ->label('Komunikasi Gagal'),
                        Forms\Components\Placeholder::make('last_error_at')
                            ->content(fn($record) => $record?->last_error_at?->format('d/m/Y H:i:s') ?? '-')
                            ->label('Error Terakhir'),
                        Forms\Components\Placeholder::make('last_error_message')
                            ->content(fn($record) => $record?->last_error_message ?? '-')
                            ->label('Pesan Error Terakhir'),
                    ])->columns(2)->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Disable auto-polling to reduce DB load on shared hosting
            // Users can manually refresh if needed
            // ->poll('30s')  // DISABLED: Causes too many queries on shared hosting
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with([
                    'officeLocation:id,name',  // Only load needed columns
                ])
            )
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Mesin'),
                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable()
                    ->sortable()
                    ->label('SN'),
                Tables\Columns\TextColumn::make('device_model')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Tidak diketahui')
                    ->badge()
                    ->color('info')
                    ->label('Tipe Mesin'),
                Tables\Columns\TextColumn::make('officeLocation.name')
                    ->searchable()
                    ->sortable()
                    ->label('Lokasi Kantor'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($record): string => match (true) {
                        $record->is_online => 'success',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn($record): string => $record->is_online ? 'Online' : 'Offline')
                    ->label('Status'),
                Tables\Columns\TextColumn::make('time_sync_status')
                    ->getStateUsing(fn($record): string => $record->time_drift_label)
                    ->badge()
                    ->color(fn($record): string => $record->time_sync_color)
                    ->label('Sinkronisasi Waktu')
                    ->tooltip(
                        fn($record): string => $record->time_checked_at
                            ? 'Dicek: ' . $record->time_checked_at->format('d/m/Y H:i:s') . ' | Jam Mesin: ' . ($record->machine_datetime?->format('d/m/Y H:i:s') ?? '-')
                            : 'Belum pernah dicek. Jam mesin akan dicek otomatis saat pegawai scan.'
                    ),
                Tables\Columns\TextColumn::make('last_heard_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Terakhir Aktif'),
                Tables\Columns\IconColumn::make('auto_sync_time')
                    ->boolean()
                    ->label('Auto Sync Jam')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip('Apakah mesin dikonfigurasi untuk sinkronisasi jam otomatis'),
                Tables\Columns\TextColumn::make('communication_success_count')
                    ->numeric()
                    ->sortable()
                    ->label('✓ Komunikasi')
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('communication_error_count')
                    ->numeric()
                    ->sortable()
                    ->label('✗ Error')
                    ->color(fn($state): string => $state > 0 ? 'danger' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_error_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Error Terakhir')
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('master_office_location_id')
                    ->relationship('officeLocation', 'name')
                    ->label('Lokasi Kantor'),
                Tables\Filters\Filter::make('status')
                    ->query(fn(Builder $query): Builder => $query->where('last_heard_at', '>=', now()->subMinutes(5)))
                    ->label('Online Saja'),
                Tables\Filters\Filter::make('time_drift')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('time_drift_seconds')->whereRaw('ABS(time_drift_seconds) > 20'))
                    ->label('Jam Tidak Sinkron'),
            ])
            ])
            ->actions([
                    ->label('Log Komunikasi')
                    ->icon('heroicon-o-signal')
                    ->color('gray')
                    ->modalHeading(fn(AttendanceMachine $record) => 'Log Komunikasi: ' . $record->name)
                    ->modalContent(function (AttendanceMachine $record) {
                        $logs = $record->communications()->latest()->limit(50)->get();
                        return view('filament.modals.machine-communications', compact('logs', 'record'));
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendanceMachines::route('/'),
            'create' => Pages\CreateAttendanceMachine::route('/create'),
            'view' => Pages\ViewAttendanceMachine::route('/{record}'),
            'edit' => Pages\EditAttendanceMachine::route('/{record}/edit'),
        ];
    }
}
