<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\MyAttendanceResource\Pages;
use App\Models\AttendanceMachineLog;
use App\Models\AttendanceSchedule;
use App\Models\EmployeeAttendanceRecord;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyAttendanceResource extends Resource
{
    protected static ?string $model = EmployeeAttendanceRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Presensi & Laporan';

    protected static ?string $navigationLabel = 'Riwayat Kehadiran';

    protected static ?string $modelLabel = 'Riwayat Kehadiran';

    protected static ?string $pluralModelLabel = 'Riwayat Kehadiran';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $user     = Auth::user();
        $employee = $user?->employee;

        if (!$employee || !$employee->pin) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->where('pin', $employee->pin)
            ->latest('attendance_time');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        $user     = Auth::user();
        $employee = $user?->employee;
        $now      = now()->timezone('Asia/Jakarta');

        // Build smart dropdown options
        $stateOptions = [];
        if ($employee) {
            $svc     = new AttendanceService();
            $allowed = $svc->getAllowedStates($employee, $now);
            foreach ($allowed as $opt) {
                $stateOptions[$opt['value']] = $opt['label'];
            }
        }

        if (empty($stateOptions)) {
            $stateOptions = [
                'check_in'  => 'Masuk Kerja (Check In)',
                'check_out' => 'Pulang Kerja (Check Out)',
            ];
        }

        return $form->schema([
            Forms\Components\Section::make('Absensi Online')
                ->description('Pastikan GPS aktif sebelum mengambil lokasi.')
                ->schema([
                    Forms\Components\Select::make('state')
                        ->label('Tipe Kehadiran')
                        ->options($stateOptions)
                        ->required()
                        ->native(false)
                        ->helperText('Pilihan berdasarkan jadwal kerja dan jam saat ini.'),

                    Forms\Components\DateTimePicker::make('attendance_time')
                        ->label('Waktu Absensi')
                        ->default($now)
                        ->disabled()
                        ->dehydrated(true)
                        ->required(),
                ])->columns(2),

            Forms\Components\Section::make('Lokasi GPS')
                ->description('Tekan tombol "Tangkap Lokasi GPS" lalu tunggu hingga berhasil.')
                ->schema([
                    Forms\Components\View::make('filament.forms.gps-capture'),

                    Forms\Components\Hidden::make('check_latitude')
                        ->required()
                        ->rules(['required', 'numeric']),
                    Forms\Components\Hidden::make('check_longitude')
                        ->required()
                        ->rules(['required', 'numeric']),
                    Forms\Components\Hidden::make('gps_accuracy'),
                    Forms\Components\Hidden::make('gps_jitter'),
                ]),

            Forms\Components\Section::make('Foto Selfie')
                ->description('Ambil foto wajah langsung menggunakan kamera depan.')
                ->schema([
                    Forms\Components\FileUpload::make('photo_checkin')
                        ->label('Foto Selfie')
                        ->image()
                        ->required()
                        ->imageEditor()
                        ->imageEditorMode(2)
                        ->imageEditorAspectRatios(['1:1'])
                        ->directory('attendance/online')
                        ->visibility('public')
                        ->disk('public')
                        ->maxSize(5120)
                        ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                        ->extraInputAttributes(['capture' => 'user'])
                        ->optimize('webp')
                        ->resize(50)
                        ->helperText('Gunakan kamera depan. Foto wajah harus jelas.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $dayMap = [
            'monday' => 'SENIN',
            'tuesday' => 'SELASA',
            'wednesday' => 'RABU',
            'thursday' => 'KAMIS',
            'friday' => 'JUMAT',
            'saturday' => 'SABTU',
            'sunday' => 'MINGGU',
        ];

        return $table
            ->defaultSort('attendance_time', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('attendance_time')
                    ->label('Hari & Tanggal')
                    ->formatStateUsing(function ($state) use ($dayMap) {
                        $date = Carbon::parse($state);
                        $day  = $dayMap[strtolower($date->format('l'))] ?? $date->format('l');
                        return "{$day}, " . $date->format('d/m/Y');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('jam')
                    ->label('Jam')
                    ->state(fn($record) => Carbon::parse($record->attendance_time)->format('H:i:s'))
                    ->fontFamily('mono')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('state')
                    ->label('Tipe Kehadiran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'check_in', 'dl_in', 'ot_in'   => 'success',
                        'check_out', 'dl_out', 'ot_out' => 'danger',
                        default                          => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'check_in'  => 'MASUK KERJA',
                        'check_out' => 'PULANG KERJA',
                        'dl_in'     => 'DINAS LUAR (BERANGKAT)',
                        'dl_out'    => 'DINAS LUAR (KEMBALI)',
                        'ot_in'     => 'LEMBUR (MASUK)',
                        'ot_out'    => 'LEMBUR (PULANG)',
                        default     => strtoupper($state),
                    }),

                Tables\Columns\TextColumn::make('attendance_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        'on_time'   => 'success',
                        'late'      => 'danger',
                        'early_out' => 'warning',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn($state): string => match ($state) {
                        'on_time'   => 'TEPAT WAKTU',
                        'late'      => 'TERLAMBAT',
                        'early_out' => 'PULANG CEPAT',
                        default     => '-',
                    }),

                Tables\Columns\TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->color(fn($state): string => $state === 'online' ? 'info' : 'gray')
                    ->formatStateUsing(fn($state): string => $state === 'online' ? '📱 Online' : '🖥️ Mesin'),

                Tables\Columns\IconColumn::make('is_fake_gps_suspected')
                    ->label('GPS')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->tooltip(
                        fn($record) => $record->is_fake_gps_suspected
                            ? ('⚠️ GPS mencurigakan: ' . ($record->gps_flag_reason ?? '-'))
                            : 'GPS terverifikasi'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('officeLocation.name')
                    ->label('Lokasi')
                    ->placeholder('-')
                    ->description(
                        fn($record) => $record->distance_from_office
                            ? $record->distance_from_office . 'm dari kantor'
                            : null
                    ),
            ])
            ->filters([
                Tables\Filters\Filter::make('attendance_time')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('to')->label('Hingga Tanggal'),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder => $query
                            ->when($data['from'], fn($q, $d) => $q->whereDate('attendance_time', '>=', $d))
                            ->when($data['to'],   fn($q, $d) => $q->whereDate('attendance_time', '<=', $d))
                    ),
                Tables\Filters\SelectFilter::make('source')
                    ->label('Sumber')
                    ->options(['machine' => 'Mesin', 'online' => 'Online']),
                Tables\Filters\SelectFilter::make('state')
                    ->label('Tipe Kehadiran')
                    ->options([
                        'check_in'  => 'Masuk Kerja',
                        'check_out' => 'Pulang Kerja',
                        'dl_in'     => 'Dinas Luar (Berangkat)',
                        'dl_out'    => 'Dinas Luar (Kembali)',
                        'ot_in'     => 'Lembur (Masuk)',
                        'ot_out'    => 'Lembur (Pulang)',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMyAttendances::route('/'),
        ];
    }
}
