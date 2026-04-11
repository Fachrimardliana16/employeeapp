<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\MasterEmployeeBasicSalaryResource\Pages;
use App\Filament\Employee\Resources\MasterEmployeeBasicSalaryResource\RelationManagers;
use App\Models\MasterEmployeeBasicSalary;
use App\Models\MasterEmployeeServiceGrade;
use App\Models\MasterEmployeeGrade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MasterEmployeeBasicSalaryResource extends Resource
{
    protected static ?string $model = MasterEmployeeBasicSalary::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Gaji Pokok';

    protected static ?string $modelLabel = 'Gaji Pokok';

    protected static ?string $pluralModelLabel = 'Gaji Pokok';

    protected static ?int $navigationSort = 807;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_service_grade_id')
                    ->label('Masa Kerja Golongan (MKG)')
                    ->options(MasterEmployeeServiceGrade::where('is_active', true)->pluck('service_grade', 'id')->map(fn($val) => $val . ' Tahun'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('employee_grade_id')
                    ->label('Golongan')
                    ->options(MasterEmployeeGrade::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah Gaji')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->step(1000)
                    ->minValue(0),
                Forms\Components\Textarea::make('desc')
                    ->label('Keterangan')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employeeGrade.name')
                    ->label('Golongan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('serviceGrade.service_grade')
                    ->label('MKG (Tahun)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah Gaji')
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
            'index' => Pages\ListMasterEmployeeBasicSalaries::route('/'),
            'create' => Pages\CreateMasterEmployeeBasicSalary::route('/create'),
            'edit' => Pages\EditMasterEmployeeBasicSalary::route('/{record}/edit'),
        ];
    }
}
