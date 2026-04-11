<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\MasterEmployeeGradeBenefitResource\Pages;
use App\Filament\Employee\Resources\MasterEmployeeGradeBenefitResource\RelationManagers;
use App\Models\MasterEmployeeGradeBenefit;
use App\Models\MasterEmployeeGrade;
use App\Models\MasterEmployeeBenefit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MasterEmployeeGradeBenefitResource extends Resource
{
    protected static ?string $model = MasterEmployeeGradeBenefit::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Tunjangan per Golongan';

    protected static ?string $modelLabel = 'Tunjangan per Golongan';

    protected static ?string $pluralModelLabel = 'Tunjangan per Golongan';

    protected static ?int $navigationSort = 811;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_grade_id')
                    ->label('Golongan Pegawai')
                    ->options(MasterEmployeeGrade::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('benefit_id')
                    ->label('Benefit Type')
                    ->relationship('benefit', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('amount')
                    ->label('Benefit Amount')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->step(1000)
                    ->minValue(0),
                Forms\Components\Textarea::make('desc')
                    ->label('Description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employeeGrade.name')
                    ->label('Golongan Pegawai')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('benefit.name')
                    ->label('Jenis Tunjangan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah Tunjangan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('desc')
                    ->label('Keterangan')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_grade_id')
                    ->label('Golongan Pegawai')
                    ->options(MasterEmployeeGrade::where('is_active', true)->pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ])->label('Aksi'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->label('Hapus yang Dipilih'),
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
            'index' => Pages\ListMasterEmployeeGradeBenefits::route('/'),
            'create' => Pages\CreateMasterEmployeeGradeBenefit::route('/create'),
            'edit' => Pages\EditMasterEmployeeGradeBenefit::route('/{record}/edit'),
        ];
    }
}
