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

    protected static ?string $navigationLabel = 'Referensi Gaji Pokok';

    protected static ?string $modelLabel = 'Referensi Gaji Pokok';

    protected static ?string $pluralModelLabel = 'Referensi Gaji Pokok';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 806;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Gaji Non-PNS')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Standar Gaji')
                            ->placeholder('Contoh: Gaji Standar THL Admin')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('employment_status_id')
                            ->label('Status Kepegawaian')
                            ->relationship('employmentStatus', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Nominal Gaji Pokok')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->step(10000),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employmentStatus.name')
                    ->label('Status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
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
