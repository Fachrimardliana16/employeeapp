<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeSalaryResource\Pages;
use App\Filament\Employee\Resources\EmployeeSalaryResource\RelationManagers;
use App\Models\EmployeeSalary;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class EmployeeSalaryResource extends Resource
{
    protected static ?string $model = EmployeeSalary::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Kompensasi & Tunjangan';

    protected static ?string $navigationLabel = 'Gaji & Payroll';

    protected static ?string $modelLabel = 'Gaji & Payroll';

    protected static ?string $pluralModelLabel = 'Gaji & Payroll';

    protected static ?int $navigationSort = 401;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pegawai')
                    ->schema([
                        Forms\Components\Select::make('employees_id')
                            ->label('Pegawai')
                            ->relationship('employee', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ]),
                Forms\Components\Section::make('Rincian Gaji')
                    ->schema([
                        Forms\Components\TextInput::make('current_basic_salary')
                            ->label('Gaji Pokok Saat Ini')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\DatePicker::make('salary_effective_date')
                            ->label('Tanggal Efektif Gaji')
                            ->required(),
                        Forms\Components\TextInput::make('previous_basic_salary')
                            ->label('Gaji Pokok Sebelumnya')
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\DatePicker::make('salary_change_date')
                            ->label('Tanggal Perubahan Gaji'),
                        Forms\Components\Textarea::make('salary_change_reason')
                            ->label('Alasan Perubahan Gaji')
                            ->maxLength(500),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Forms\Components\Hidden::make('users_id')
                            ->default(fn() => Auth::id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.employee_name')
                    ->label('Nama Pegawai')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_basic_salary')
                    ->label('Gaji Pokok Saat Ini')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('salary_effective_date')
                    ->label('Tanggal Efektif')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('previous_basic_salary')
                    ->label('Gaji Pokok Sebelumnya')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),
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
            'index' => Pages\ListEmployeeSalaries::route('/'),
            'create' => Pages\CreateEmployeeSalary::route('/create'),
            'edit' => Pages\EditEmployeeSalary::route('/{record}/edit'),
        ];
    }
}
