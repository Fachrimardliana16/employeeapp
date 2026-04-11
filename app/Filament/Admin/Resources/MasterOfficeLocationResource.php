<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterOfficeLocationResource\Pages;
use App\Filament\Admin\Resources\MasterOfficeLocationResource\RelationManagers;
use App\Models\MasterOfficeLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterOfficeLocationResource extends Resource
{
    protected static ?string $model = MasterOfficeLocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Pengaturan Sistem';

    protected static ?string $navigationLabel = 'Lokasi Kantor';

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return 'Lokasi Kantor';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Lokasi Kantor';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Lokasi')
                    ->description('Data dasar lokasi kantor/cabang')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lokasi')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Kantor Pusat, Cabang Jakarta')
                                    ->helperText('Nama yang mudah dikenali untuk lokasi ini'),

                                Forms\Components\TextInput::make('code')
                                    ->label('Kode Lokasi')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('Contoh: HO, CAB-JKT, CAB-SBY')
                                    ->helperText('Kode unik untuk identifikasi')
                                    ->alphaDash(),
                            ]),

                        Forms\Components\Textarea::make('address')
                            ->label('Alamat Lengkap')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Masukkan alamat lengkap lokasi'),

                        Forms\Components\TextInput::make('description')
                            ->label('Deskripsi')
                            ->maxLength(255)
                            ->placeholder('Informasi tambahan tentang lokasi')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Koordinat & Radius')
                    ->description('Pengaturan geolocation untuk absensi')
                    ->icon('heroicon-o-map')
                    ->schema([
                        Forms\Components\Select::make('departments_id')
                            ->label('Departemen/Cabang')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Pilih departemen jika lokasi ini khusus untuk departemen tertentu. Kosongkan jika untuk semua departemen.')
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->required()
                                    ->numeric()
                                    ->step('any')
                                    ->placeholder('-6.200000')
                                    ->helperText('Koordinat lintang (misal: -6.200000)'),

                                Forms\Components\TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->required()
                                    ->numeric()
                                    ->step('any')
                                    ->placeholder('106.816666')
                                    ->helperText('Koordinat bujur (misal: 106.816666)'),

                                Forms\Components\TextInput::make('radius')
                                    ->label('Radius (meter)')
                                    ->required()
                                    ->numeric()
                                    ->default(100)
                                    ->minValue(10)
                                    ->maxValue(1000)
                                    ->suffix('meter')
                                    ->helperText('Jarak maksimal untuk absen (10-1000 meter)'),
                            ]),

                        Forms\Components\Placeholder::make('map_helper')
                            ->label('Cara Mendapatkan Koordinat')
                            ->content('1. Buka Google Maps\n2. Klik kanan pada lokasi yang diinginkan\n3. Pilih koordinat yang muncul (akan otomatis tersalin)\n4. Format: Latitude, Longitude')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Hanya lokasi aktif yang bisa digunakan untuk absensi')
                            ->inline(false),
                    ]),

                Forms\Components\Hidden::make('users_id')
                    ->default(fn() => auth()->id() ?? 0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Lokasi')
                    ->description(fn(MasterOfficeLocation $record): string => "Kode: {$record->code}")
                    ->searchable(['name', 'code'])
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Unit Kerja')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->default('Semua Unit')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn($record): ?string => $record->address)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('latitude')
                    ->label('Koordinat & Radius')
                    ->getStateUsing(fn(MasterOfficeLocation $record): string => "{$record->latitude}, {$record->longitude}")
                    ->description(fn(MasterOfficeLocation $record): string => "Radius: {$record->radius} meter")
                    ->icon('heroicon-o-map-pin')
                    ->color('primary')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail'),

                    Tables\Actions\Action::make('view_map')
                        ->label('Buka di Google Maps')
                        ->icon('heroicon-o-map')
                        ->color('info')
                        ->url(
                            fn(MasterOfficeLocation $record): string =>
                            "https://www.google.com/maps?q={$record->latitude},{$record->longitude}"
                        )
                        ->openUrlInNewTab(),

                    Tables\Actions\EditAction::make()
                        ->label('Edit'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ])
                ->button()
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Sebagian'),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ])->label('Aksi Masal'),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListMasterOfficeLocations::route('/'),
            'create' => Pages\CreateMasterOfficeLocation::route('/create'),
            'edit' => Pages\EditMasterOfficeLocation::route('/{record}/edit'),
        ];
    }
}
