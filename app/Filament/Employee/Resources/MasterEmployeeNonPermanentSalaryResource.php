<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\MasterEmployeeNonPermanentSalaryResource\Pages;
use App\Models\MasterEmployeeNonPermanentSalary;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterEmployeeNonPermanentSalaryResource extends Resource
{
    protected static ?string $model = MasterEmployeeNonPermanentSalary::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Standar UMK & Gaji';

    protected static ?string $modelLabel = 'Standar UMK';

    protected static ?string $pluralModelLabel = 'Standar UMK';

    protected static ?string $navigationGroup = 'Pengaturan Penggajian';

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pengaturan Standar UMK & Gaji')
                    ->description('Tentukan nilai nominal standar UMK atau gaji pokok untuk status kepegawaian Non-ASN seperti Kontrak, Magang, dan THL.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Label Standar UMK/Gaji')
                            ->placeholder('Contoh: UMK THL 2026')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('employment_status_id')
                            ->label('Untuk Status Kepegawaian')
                            ->relationship('employmentStatus', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->hint('Berlaku untuk Kontrak, Magang, atau THL'),
                        Forms\Components\TextInput::make('amount')
                            ->label('Nominal UMK / Gaji Pokok')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->step(1000)
                            ->minValue(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Hanya standar yang aktif yang dapat dipilih dalam proses administrasi.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Kategori / Label')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employmentStatus.name')
                    ->label('Status Pegawai')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal UMK')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employment_status_id')
                    ->label('Status Kepegawaian')
                    ->relationship('employmentStatus', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),
                    Tables\Actions\EditAction::make()->label('Edit'),
                    Tables\Actions\DeleteAction::make()->label('Hapus'),
                ])->label('Aksi'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->label('Hapus yang Dipilih'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterEmployeeNonPermanentSalaries::route('/'),
            'create' => Pages\CreateMasterEmployeeNonPermanentSalary::route('/create'),
            'edit' => Pages\EditMasterEmployeeNonPermanentSalary::route('/{record}/edit'),
        ];
    }
}
