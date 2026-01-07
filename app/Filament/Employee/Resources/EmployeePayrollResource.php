<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeePayrollResource\Pages;
use App\Filament\Employee\Resources\EmployeePayrollResource\RelationManagers;
use App\Models\EmployeePayroll;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeePayrollResource extends Resource
{
    protected static ?string $model = EmployeePayroll::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Kompensasi & Tunjangan';
    protected static ?string $navigationLabel = 'Proses Payroll';
    protected static ?int $navigationSort = 202;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('payroll_period')
                    ->required(),
                Forms\Components\TextInput::make('base_salary')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('total_allowance')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('total_deduction')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('total_bonus')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('gross_salary')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('net_salary')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('work_days')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('present_days')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('late_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('absent_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('overtime_hours')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('payment_status')
                    ->required(),
                Forms\Components\DatePicker::make('payment_date'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('approved_by')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('approved_at'),
                Forms\Components\TextInput::make('users_id')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payroll_period')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_salary')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_allowance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_deduction')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_bonus')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gross_salary')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_salary')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('present_days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('late_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('absent_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('overtime_hours')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status'),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approved_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_id')
                    ->numeric()
                    ->sortable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListEmployeePayrolls::route('/'),
            'create' => Pages\CreateEmployeePayroll::route('/create'),
            'edit' => Pages\EditEmployeePayroll::route('/{record}/edit'),
        ];
    }
}
