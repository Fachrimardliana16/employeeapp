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
            ->headerActions([
                Tables\Actions\Action::make('sync_all_machines')
                    ->label('Auto-Sync Semua Mesin')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Sinkronisasi Otomatis Semua Mesin')
                    ->modalDescription('Sistem akan mengirim perintah tarik data ke SEMUA mesin yang online.')
                    ->action(function () {
                        $machines = AttendanceMachine::where('last_heard_at', '>=', now()->subMinutes(5))->get();
                        $count = 0;

                        foreach ($machines as $machine) {
                            \App\Models\AttendanceMachineCommand::create([
                                'attendance_machine_id' => $machine->id,
                                'command' => 'DATA QUERY ATTLOG',
                                'status' => 'pending',
                            ]);
                            $count++;
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Sinkronisasi Dijadwalkan')
                            ->body("Perintah tarik data telah dikirim ke {$count} mesin online.")
                            ->success()
                            ->duration(5000)
                            ->send();
                    }),

                Tables\Actions\Action::make('fix_all_time_drift')
                    ->label('Perbaiki Semua Jam yang Tidak Sinkron')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Perbaiki Jam Mesin Otomatis')
                    ->modalDescription('Sistem akan mengirim perintah RESTART ke semua mesin yang jamnya tidak sinkron (selisih > 20 detik).')
                    ->action(function () {
                        $machines = AttendanceMachine::whereNotNull('time_drift_seconds')
                            ->whereRaw('ABS(time_drift_seconds) > 20')
                            ->get();
                        $count = 0;

                        foreach ($machines as $machine) {
                            \App\Models\AttendanceMachineCommand::create([
                                'attendance_machine_id' => $machine->id,
                                'command' => 'REBOOT',
                                'status' => 'pending',
                            ]);
                            $count++;
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Perintah Restart Dijadwalkan')
                            ->body("Perintah restart telah dikirim ke {$count} mesin dengan jam tidak sinkron.")
                            ->warning()
                            ->duration(5000)
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
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
                ])->label('Aksi')->icon('heroicon-m-ellipsis-vertical'),
                Tables\Actions\Action::make('view_communications')
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
                    // Bulk Sync Actions
                    Tables\Actions\BulkAction::make('bulk_sync_logs')
                        ->label('Tarik Data Absensi (Massal)')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Tarik Data dari Semua Mesin Terpilih')
                        ->modalDescription('Perintah akan dikirim ke semua mesin yang dipilih untuk mengirim log absensi.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $count = 0;
                            foreach ($records as $machine) {
                                \App\Models\AttendanceMachineCommand::create([
                                    'attendance_machine_id' => $machine->id,
                                    'command' => 'DATA QUERY ATTLOG',
                                    'status' => 'pending',
                                ]);
                                $count++;
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Perintah Terkirim')
                                ->body("Perintah tarik data telah dijadwalkan untuk {$count} mesin.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('bulk_sync_users')
                        ->label('Tarik Data User (Massal)')
                        ->icon('heroicon-o-users')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Tarik Daftar User dari Semua Mesin')
                        ->modalDescription('Perintah akan dikirim ke semua mesin untuk mengirim daftar ID/PIN dan nama.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $count = 0;
                            foreach ($records as $machine) {
                                \App\Models\AttendanceMachineCommand::create([
                                    'attendance_machine_id' => $machine->id,
                                    'command' => 'DATA QUERY USERINFO',
                                    'status' => 'pending',
                                ]);
                                $count++;
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Perintah Terkirim')
                                ->body("Perintah tarik user telah dijadwalkan untuk {$count} mesin.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('bulk_restart')
                        ->label('Restart Mesin (Massal)')
                        ->icon('heroicon-o-arrow-path')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Restart Semua Mesin Terpilih')
                        ->modalDescription('PERINGATAN: Semua mesin akan di-restart dari jarak jauh. Mesin akan offline selama ±1-2 menit.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $count = 0;
                            foreach ($records as $machine) {
                                \App\Models\AttendanceMachineCommand::create([
                                    'attendance_machine_id' => $machine->id,
                                    'command' => 'REBOOT',
                                    'status' => 'pending',
                                ]);
                                $count++;
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Perintah Terkirim')
                                ->body("Perintah restart telah dijadwalkan untuk {$count} mesin.")
                                ->warning()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('bulk_check_time')
                        ->label('Cek Jam Mesin (Massal)')
                        ->icon('heroicon-o-clock')
                        ->color('gray')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $count = 0;
                            foreach ($records as $machine) {
                                \App\Models\AttendanceMachineCommand::create([
                                    'attendance_machine_id' => $machine->id,
                                    'command' => 'DATA QUERY INFO',
                                    'status' => 'pending',
                                ]);
                                $count++;
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Perintah Terkirim')
                                ->body("Permintaan info jam telah dikirim ke {$count} mesin. Tunggu 30 detik lalu refresh.")
                                ->send();
                        }),

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
