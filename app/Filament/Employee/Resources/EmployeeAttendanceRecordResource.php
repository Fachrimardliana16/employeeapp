<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\Pages;
use App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\RelationManagers;
use App\Models\EmployeeAttendanceRecord;
use App\Models\MasterOfficeLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class EmployeeAttendanceRecordResource extends Resource
{
    protected static ?string $model = EmployeeAttendanceRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Absensi & Kehadiran';

    protected static ?string $navigationLabel = 'Kehadiran';

    protected static ?string $modelLabel = 'Catatan Kehadiran';

    protected static ?string $pluralModelLabel = 'Catatan Kehadiran';

    protected static ?int $navigationSort = 501;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kehadiran')
                    ->schema([
                        Forms\Components\TextInput::make('pin')
                            ->label('PIN Pegawai')
                            ->required()
                            ->maxLength(255)
                            ->default(fn() => auth()->user()->employee?->pin)
                            ->readOnly(),

                        Forms\Components\TextInput::make('employee_name')
                            ->label('Nama Pegawai')
                            ->required()
                            ->maxLength(255)
                            ->default(fn() => auth()->user()->employee?->name)
                            ->readOnly(),

                        Forms\Components\DateTimePicker::make('attendance_time')
                            ->label('Waktu Kehadiran')
                            ->required()
                            ->default(now())
                            ->seconds(false),

                        Forms\Components\Select::make('state')
                            ->label('Status')
                            ->required()
                            ->options([
                                'check_in' => 'Check In',
                                'check_out' => 'Check Out',
                            ])
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Foto Kehadiran')
                    ->description('Ambil foto selfie langsung menggunakan kamera')
                    ->schema([
                        Forms\Components\FileUpload::make('photo_checkin')
                            ->label('Foto Check In')
                            ->image()
                            ->required()
                            ->imageEditor()
                            ->imageEditorMode(2)
                            ->imageEditorAspectRatios(['1:1'])
                            ->imageEditorEmptyFillColor('#000000')
                            ->imageEditorViewportWidth('1280')
                            ->imageEditorViewportHeight('720')
                            ->directory('attendance/photo_checkin')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                            ->disk('public')
                            ->extraInputAttributes([
                                'capture' => 'user',
                            ])
                            ->helperText('Klik untuk membuka kamera dan ambil foto selfie. Wajib diisi.')
                            ->visible(fn(Forms\Get $get) => $get('state') === 'check_in'),

                        Forms\Components\FileUpload::make('photo_checkout')
                            ->label('Foto Check Out')
                            ->image()
                            ->required()
                            ->imageEditor()
                            ->imageEditorMode(2)
                            ->imageEditorAspectRatios(['1:1'])
                            ->imageEditorEmptyFillColor('#000000')
                            ->imageEditorViewportWidth('1280')
                            ->imageEditorViewportHeight('720')
                            ->directory('attendance/photo_checkout')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                            ->disk('public')
                            ->extraInputAttributes([
                                'capture' => 'user',
                            ])
                            ->helperText('Klik untuk membuka kamera dan ambil foto selfie. Wajib diisi.')
                            ->visible(fn(Forms\Get $get) => $get('state') === 'check_out'),
                    ]),

                Forms\Components\Section::make('Lokasi & GPS')
                    ->description('Lokasi akan otomatis terdeteksi menggunakan GPS')
                    ->schema([
                        Forms\Components\Placeholder::make('location_info')
                            ->label('Informasi Lokasi')
                            ->content(new HtmlString('
                                <div id="location-status" class="text-sm">
                                    <div class="flex items-center gap-2 text-gray-500">
                                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Mendeteksi lokasi Anda...</span>
                                    </div>
                                </div>
                            ')),

                        Forms\Components\Hidden::make('check_latitude')
                            ->reactive(),

                        Forms\Components\Hidden::make('check_longitude')
                            ->reactive(),

                        Forms\Components\Hidden::make('office_location_id'),
                        Forms\Components\Hidden::make('distance_from_office'),
                        Forms\Components\Hidden::make('is_within_radius'),
                    ]),

                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\TextInput::make('verification')
                            ->label('Verifikasi')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('work_code')
                            ->label('Kode Kerja')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('device')
                            ->label('Perangkat')
                            ->maxLength(255)
                            ->default(fn() => request()->userAgent()),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('visual_proof')
                    ->label('Foto')
                    ->getStateUsing(fn (EmployeeAttendanceRecord $record): ?string => 
                        $record->photo_checkin ?? $record->photo_checkout ?? $record->picture
                    )
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('employee_name')
                    ->label('Pegawai & PIN')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (EmployeeAttendanceRecord $record): string => "PIN: {$record->pin}"),

                Tables\Columns\TextColumn::make('attendance_time')
                    ->label('Waktu & Status')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (EmployeeAttendanceRecord $record): string => match ($record->state) {
                        'check_in' => 'Masuk',
                        'check_out' => 'Keluar',
                        default => $record->state,
                    }),

                Tables\Columns\TextColumn::make('attendance_status')
                    ->label('Ketepatan')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->attendance_status ?? 'on_time')
                    ->color(fn(string $state): string => match ($state) {
                        'late' => 'danger',
                        'early' => 'warning',
                        'on_time' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'late' => 'Terlambat',
                        'early' => 'Terlalu Cepat',
                        'on_time' => 'Tepat Waktu',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\ViewColumn::make('mini_map')
                    ->label('Peta')
                    ->view('filament.tables.columns.leaflet-map'),

                Tables\Columns\TextColumn::make('officeLocation.name')
                    ->label('Lokasi & Jarak')
                    ->searchable()
                    ->sortable()
                    ->description(fn (EmployeeAttendanceRecord $record): string => $record->distance_from_office ? "Jarak: {$record->distance_from_office}m" : 'Lokasi tidak terdeteksi'),

                Tables\Columns\IconColumn::make('is_within_radius')
                    ->label('Radius')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('device')
                    ->label('Device')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Rekam')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->label('Status')
                    ->options([
                        'check_in' => 'Check In',
                        'check_out' => 'Check Out',
                    ])
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_within_radius')
                    ->label('Dalam Radius')
                    ->placeholder('Semua')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),

                Tables\Filters\Filter::make('attendance_time')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('attendance_time', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('attendance_time', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),

                    Tables\Actions\Action::make('view_location')
                        ->label('Lihat Lokasi')
                        ->icon('heroicon-o-map-pin')
                        ->color('info')
                        ->url(
                            fn(EmployeeAttendanceRecord $record): ?string =>
                            $record->check_latitude && $record->check_longitude
                                ? "https://www.google.com/maps?q={$record->check_latitude},{$record->check_longitude}"
                                : null
                        )
                        ->openUrlInNewTab()
                        ->visible(fn(EmployeeAttendanceRecord $record) => $record->check_latitude && $record->check_longitude),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Bulk delete removed
                ])->label('Aksi Masal'),
            ])
            ->defaultSort('attendance_time', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Baris 1: Informasi Pegawai
                Components\Section::make('Informasi Pegawai')
                    ->icon('heroicon-m-user')
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('employee_name')
                                    ->label('Nama Pegawai')
                                    ->weight(FontWeight::Bold),
                                Components\TextEntry::make('pin')
                                    ->label('PIN'),
                                Components\TextEntry::make('attendance_status')
                                    ->label('Ketepatan Waktu')
                                    ->badge()
                                    ->getStateUsing(fn ($record) => $record->attendance_status ?? 'on_time')
                                    ->color(fn(string $state): string => match ($state) {
                                        'late' => 'danger',
                                        'early' => 'warning',
                                        'on_time' => 'success',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'late' => 'Terlambat',
                                        'early' => 'Terlalu Cepat',
                                        'on_time' => 'Tepat Waktu',
                                        default => $state,
                                    }),
                            ]),
                    ]),

                // Baris 2: Detail (Waktu & Lokasi) | Foto
                Components\Grid::make(3)
                    ->schema([
                        // Kolom Kiri: Data (Span 2)
                        Components\Group::make([
                            Components\Section::make('Waktu & Perangkat')
                                ->icon('heroicon-m-clock')
                                ->schema([
                                    Components\Grid::make(2)
                                        ->schema([
                                            Components\TextEntry::make('attendance_time')
                                                ->label('Waktu Kejadian')
                                                ->dateTime('l, d F Y | H:i')
                                                ->weight(FontWeight::Bold),
                                            Components\TextEntry::make('device')
                                                ->label('Perangkat')
                                                ->limit(50),
                                        ]),
                                ]),

                            Components\Section::make('Lokasi & GPS')
                                ->icon('heroicon-m-map-pin')
                                ->schema([
                                    Components\Grid::make(2)
                                        ->schema([
                                            Components\TextEntry::make('officeLocation.name')
                                                ->label('Lokasi Kantor')
                                                ->weight(FontWeight::Bold)
                                                ->color('primary'),
                                            Components\TextEntry::make('distance_from_office')
                                                ->label('Jarak')
                                                ->suffix(' meter')
                                                ->color(fn($state) => $state <= 100 ? 'success' : 'danger'),
                                            
                                            Components\TextEntry::make('check_latitude')
                                                ->label('Latitude')
                                                ->size('sm'),
                                            Components\TextEntry::make('check_longitude')
                                                ->label('Longitude')
                                                ->size('sm'),

                                            Components\IconEntry::make('is_within_radius')
                                                ->label('Radius')
                                                ->boolean(),
                                        ]),
                                ]),
                        ])->columnSpan(2),

                        // Kolom Kanan: Foto (Span 1)
                        Components\Section::make('Foto Bukti')
                            ->icon('heroicon-m-camera')
                            ->schema([
                                Components\ImageEntry::make('photo_checkin')
                                    ->hiddenLabel()
                                    ->height(350)
                                    ->extraImgAttributes(['class' => 'rounded-lg shadow-sm w-full object-cover'])
                                    ->visible(fn($record) => $record->photo_checkin),
                                    
                                Components\ImageEntry::make('photo_checkout')
                                    ->hiddenLabel()
                                    ->height(350)
                                    ->extraImgAttributes(['class' => 'rounded-lg shadow-sm w-full object-cover'])
                                    ->visible(fn($record) => $record->photo_checkout),
                                    
                                Components\TextEntry::make('no_photo')
                                    ->hiddenLabel()
                                    ->default('Tidak ada foto')
                                    ->visible(fn($record) => !$record->photo_checkin && !$record->photo_checkout),
                            ])->columnSpan(1),
                    ]),

                // Baris 3: Visualisasi Lokasi (Peta)
                Components\Section::make('Visualisasi Lokasi GPS')
                    ->icon('heroicon-m-globe-asia-australia')
                    ->schema([
                        Components\ViewEntry::make('location_map')
                            ->hiddenLabel()
                            ->view('filament.infolists.leaflet-map'),
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
            'index' => Pages\ListEmployeeAttendanceRecords::route('/'),
            'view' => Pages\ViewEmployeeAttendanceRecord::route('/{record}'),
        ];
    }
}
