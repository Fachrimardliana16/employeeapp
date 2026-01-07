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

class EmployeeSalaryResource extends Resource
{
    protected static ?string $model = EmployeeSalary::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Kompensasi & Tunjangan';

    protected static ?string $navigationLabel = 'Gaji';

    protected static ?string $modelLabel = 'Gaji Karyawan';

    protected static ?string $pluralModelLabel = 'Gaji Karyawan';

    protected static ?int $navigationSort = 201;    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Employee Information')
                    ->schema([
                        Forms\Components\Select::make('employees_id')
                            ->relationship('employee', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ]),
                Forms\Components\Section::make('Salary Details')
                    ->schema([
                        Forms\Components\TextInput::make('current_basic_salary')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\DatePicker::make('salary_effective_date')
                            ->required(),
                        Forms\Components\TextInput::make('previous_basic_salary')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\DatePicker::make('salary_change_date'),
                        Forms\Components\Textarea::make('salary_change_reason')
                            ->maxLength(500),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Hidden::make('users_id')
                            ->default(fn () => auth()->id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.employee_name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_basic_salary')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('salary_effective_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('previous_basic_salary')
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListEmployeeSalaries::route('/'),
            'create' => Pages\CreateEmployeeSalary::route('/create'),
            'edit' => Pages\EditEmployeeSalary::route('/{record}/edit'),
        ];
    }
}
