<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\AttendanceMachineResource\Pages;
use App\Filament\Employee\Resources\AttendanceMachineResource\RelationManagers;
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
                            ->content(fn ($record) => $record?->last_heard_at?->diffForHumans() ?? '-')
                            ->label('Terakhir Aktif'),
                        Forms\Components\Placeholder::make('ip_address')
                            ->content(fn ($record) => $record?->ip_address ?? '-')
                            ->label('Alamat IP'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('30s')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Mesin'),
                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable()
                    ->sortable()
                    ->label('SN'),
                Tables\Columns\TextColumn::make('officeLocation.name')
                    ->searchable()
                    ->sortable()
                    ->label('Lokasi Kantor'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($record): string => match (true) {
                        $record->is_online => 'success',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($record): string => $record->is_online ? 'Online' : 'Offline')
                    ->label('Status'),
                Tables\Columns\TextColumn::make('time_sync_status')
                    ->getStateUsing(fn ($record): string => $record->time_drift_label)
                    ->badge()
                    ->color(fn ($record): string => $record->time_sync_color)
                    ->label('Sinkronisasi Waktu')
                    ->tooltip(fn ($record): string => $record->time_checked_at
                        ? 'Dicek: ' . $record->time_checked_at->format('d/m/Y H:i:s') . ' | Jam Mesin: ' . ($record->machine_datetime?->format('d/m/Y H:i:s') ?? '-')
                        : 'Belum pernah dicek. Klik tombol "Cek Waktu" untuk memverifikasi.'
                    ),
                Tables\Columns\TextColumn::make('last_heard_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Terakhir Aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('sync_users')
                    ->label('Tarik Data User')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Tarik Daftar ID/User')
                    ->modalDescription('Perintah akan dikirim ke mesin untuk mengirim daftar ID (PIN) dan Nama yang terdaftar di dalamnya. Berguna jika ada perubahan ID langsung di mesin.')
                    ->action(function (AttendanceMachine $record) {
                        \App\Models\AttendanceMachineCommand::create([
                            'attendance_machine_id' => $record->id,
                            'command' => 'DATA QUERY USERINFO',
                            'status' => 'pending',
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Perintah Terkirim')
                            ->body('Perintah penarikan data User telah dijadwalkan.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('sync_logs')
                    ->label('Tarik Data')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Tarik Log Absensi')
                    ->modalDescription('Perintah akan dikirim ke mesin. Mesin akan mulai mengirim log absensi pada koneksi heartbeat berikutnya.')
                    ->action(function (AttendanceMachine $record) {
                        \App\Models\AttendanceMachineCommand::create([
                            'attendance_machine_id' => $record->id,
                            'command' => 'DATA QUERY ATTLOG',
                            'status' => 'pending',
                        ]);

                        // Log activity for the button click
                        if (function_exists('activity')) {
                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->log('Menjalankan perintah Tarik Data Absensi (ATTLOG) untuk mesin: ' . $record->name);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Perintah Terkirim')
                            ->body('Perintah penarikan data telah dijadwalkan untuk mesin ' . $record->name)
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('sync_time')
                    ->label('Sinkronisasi Waktu')
                    ->icon('heroicon-o-clock')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Sinkronisasi Waktu Mesin')
                    ->modalDescription('Perintah akan dikirim ke mesin untuk: (1) Set TimeZone ke WIB, (2) Set jam mesin = jam server, (3) Verifikasi hasil. Tunggu ±30 detik untuk melihat hasilnya di kolom Sinkronisasi Waktu.')
                    ->action(function (AttendanceMachine $record) {
                        $now = now()->timezone('Asia/Jakarta');
                        $dateTimeStr = $now->format('Y-m-d H:i:s');

                        // Step 1: Set timezone to GMT+7 (WIB)
                        \App\Models\AttendanceMachineCommand::create([
                            'attendance_machine_id' => $record->id,
                            'command' => 'SET OPTIONS TimeZone=7',
                            'status' => 'pending',
                        ]);

                        // Step 2: Set the correct date/time
                        \App\Models\AttendanceMachineCommand::create([
                            'attendance_machine_id' => $record->id,
                            'command' => "SET OPTIONS Date={$dateTimeStr}",
                            'status' => 'pending',
                        ]);

                        // Step 3: Request INFO to verify the machine's time after sync
                        \App\Models\AttendanceMachineCommand::create([
                            'attendance_machine_id' => $record->id,
                            'command' => 'INFO',
                            'status' => 'pending',
                        ]);

                        if (function_exists('activity')) {
                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->log('Sinkronisasi Waktu + Verifikasi untuk mesin: ' . $record->name . ' (Target: ' . $dateTimeStr . ')');
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Perintah Terkirim (3 Langkah)')
                            ->body('1. Set TimeZone=WIB ✓ | 2. Set Jam=' . $dateTimeStr . ' ✓ | 3. Verifikasi ✓ — Tunggu ±30 detik, lalu lihat kolom "Sinkronisasi Waktu".')
                            ->success()
                            ->duration(10000)
                            ->send();
                    }),
                Tables\Actions\Action::make('check_time')
                    ->label('Cek Waktu')
                    ->icon('heroicon-o-magnifying-glass-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Cek Waktu Mesin')
                    ->modalDescription('Perintah akan dikirim untuk mengambil jam mesin saat ini. Hasilnya akan muncul di kolom "Sinkronisasi Waktu" dalam ±15 detik. Tidak mengubah apapun di mesin.')
                    ->action(function (AttendanceMachine $record) {
                        // Only send INFO to check, don't change anything
                        \App\Models\AttendanceMachineCommand::create([
                            'attendance_machine_id' => $record->id,
                            'command' => 'INFO',
                            'status' => 'pending',
                        ]);

                        if (function_exists('activity')) {
                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->log('Cek Waktu Mesin (INFO) untuk: ' . $record->name);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Perintah Cek Waktu Terkirim')
                            ->body('Menunggu respons dari mesin ' . $record->name . '. Lihat kolom "Sinkronisasi Waktu" dalam ±15 detik.')
                            ->info()
                            ->duration(8000)
                            ->send();
                    }),
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
            'edit' => Pages\EditAttendanceMachine::route('/{record}/edit'),
        ];
    }
}
