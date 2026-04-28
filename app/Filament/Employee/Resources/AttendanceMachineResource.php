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
                        : 'Belum pernah dicek. Jam mesin akan dicek otomatis saat pegawai scan.'
                    ),
                Tables\Columns\TextColumn::make('last_command_status')
                    ->getStateUsing(function ($record): string {
                        $last = \App\Models\AttendanceMachineCommand::where('attendance_machine_id', $record->id)
                            ->latest('id')->first();
                        if (!$last) return 'Tidak ada';
                        return match ($last->status) {
                            'pending' => '⏳ Menunggu',
                            'sent' => '📤 Terkirim',
                            'completed' => '✅ Berhasil',
                            'failed' => '❌ Gagal',
                            default => $last->status,
                        };
                    })
                    ->badge()
                    ->color(function ($record): string {
                        $last = \App\Models\AttendanceMachineCommand::where('attendance_machine_id', $record->id)
                            ->latest('id')->first();
                        if (!$last) return 'gray';
                        return match ($last->status) {
                            'pending' => 'gray',
                            'sent' => 'warning',
                            'completed' => 'success',
                            'failed' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->label('Perintah Terakhir')
                    ->tooltip(function ($record): string {
                        $last = \App\Models\AttendanceMachineCommand::where('attendance_machine_id', $record->id)
                            ->latest('id')->first();
                        if (!$last) return 'Belum ada perintah yang dikirim.';
                        $info = $last->command . ' | ' . $last->status;
                        if ($last->response_payload) {
                            $info .= ' | ' . \Illuminate\Support\Str::limit($last->response_payload, 80);
                        }
                        if ($last->created_at) {
                            $info .= ' | ' . $last->created_at->format('d/m H:i:s');
                        }
                        return $info;
                    }),
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
                    ->label('Perbaiki Jam & Restart')
                    ->icon('heroicon-o-clock')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Perbaiki Jam Mesin (Remote Restart)')
                    ->modalDescription('Mesin akan di-RESTART dari jarak jauh. Setelah restart, mesin akan konek ulang dan menerima TimeZone=WIB dari server. Jam mesin akan otomatis terkoreksi. Proses ini memakan waktu ±1-2 menit. Data absensi di mesin TIDAK akan hilang.')
                    ->modalSubmitActionLabel('Ya, Restart Mesin')
                    ->action(function (AttendanceMachine $record) {
                        // Send REBOOT command — machine will restart, re-handshake,
                        // and receive TimeZone=7 from server, correcting its clock.
                        \App\Models\AttendanceMachineCommand::create([
                            'attendance_machine_id' => $record->id,
                            'command' => 'REBOOT',
                            'status' => 'pending',
                        ]);

                        if (function_exists('activity')) {
                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->log('Remote Restart untuk perbaikan jam mesin: ' . $record->name);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Perintah Terkirim')
                            ->body('Perintah telah masuk antrean. Silakan pantau kolom "Perintah Terakhir". Jika dalam 2 menit status berubah menjadi "❌ Gagal (TIMEOUT)", berarti mesin di cabang ini tidak mendukung perintah jarak jauh dan harus direstart manual dengan cabut-colok listrik.')
                            ->warning()
                            ->duration(20000)
                            ->send();
                    }),
                Tables\Actions\Action::make('force_set_time')
                    ->label('Paksa Set Waktu')
                    ->icon('heroicon-o-variable')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Paksa Sinkron Waktu (Tanpa Restart)')
                    ->modalDescription('Perintah akan dikirim untuk mengubah jam mesin secara paksa mengikuti jam server detik ini juga. Gunakan ini jika "Perbaiki Jam & Restart" tidak mengubah waktu.')
                    ->modalSubmitActionLabel('Kirim Perintah Set Waktu')
                    ->action(function (AttendanceMachine $record) {
                        $now = now()->format('Y-m-d H:i:s');
                        \App\Models\AttendanceMachineCommand::create([
                            'attendance_machine_id' => $record->id,
                            'command' => "SET OPTIONS DateTime={$now}",
                            'status' => 'pending',
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Perintah Terkirim')
                            ->body('Perintah paksa set waktu (' . $now . ') telah dijadwalkan.')
                            ->info()
                            ->send();
                    }),
                Tables\Actions\Action::make('refresh_info')
                    ->label('Cek Jam Detik Ini')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->modalHeading('Refresh Informasi & Waktu Mesin')
                    ->modalDescription('Server akan meminta informasi terbaru (termasuk jam internal mesin) saat ini juga. Gunakan ini setelah Anda memperbaiki jam secara manual agar angka selisih di dashboard terupdate.')
                    ->action(function (AttendanceMachine $record) {
                        \App\Models\AttendanceMachineCommand::create([
                            'attendance_machine_id' => $record->id,
                            'command' => "DATA QUERY INFO",
                            'status' => 'pending',
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Perintah Terkirim')
                            ->body('Permintaan info jam telah dikirim. Silakan tunggu ±15 detik lalu refresh halaman.')
                            ->send();
                    }),
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
            RelationManagers\CommandsRelationManager::class,
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
