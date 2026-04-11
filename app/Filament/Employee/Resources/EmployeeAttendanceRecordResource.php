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
                            ->capture('user')
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
                            ->capture('user')
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
                Tables\Columns\TextColumn::make('pin')
                    ->label('PIN')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee_name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('attendance_time')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('state')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'check_in' => 'success',
                        'check_out' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'check_in' => 'Check In',
                        'check_out' => 'Check Out',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('officeLocation.name')
                    ->label('Lokasi Kantor')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('distance_from_office')
                    ->label('Jarak')
                    ->suffix(' m')
                    ->badge()
                    ->color(fn($state) => $state <= 50 ? 'success' : ($state <= 100 ? 'warning' : 'danger'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_within_radius')
                    ->label('Dalam Radius')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(),

                Tables\Columns\ImageColumn::make('photo_checkin')
                    ->label('Foto Check In')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ImageColumn::make('photo_checkout')
                    ->label('Foto Check Out')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('device')
                    ->label('Perangkat')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
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
                    Tables\Actions\EditAction::make()->label('Edit'),
                    Tables\Actions\DeleteAction::make()->label('Hapus'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->label('Hapus yang Dipilih'),
            ])
            ->defaultSort('attendance_time', 'desc');
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
            'create' => Pages\CreateEmployeeAttendanceRecord::route('/create'),
            'edit' => Pages\EditEmployeeAttendanceRecord::route('/{record}/edit'),
        ];
    }
}
