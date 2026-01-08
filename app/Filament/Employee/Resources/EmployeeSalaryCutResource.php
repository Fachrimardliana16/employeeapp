<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeSalaryCutResource\Pages;
use App\Filament\Employee\Resources\EmployeeSalaryCutResource\RelationManagers;
use App\Models\EmployeeSalaryCut;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeSalaryCutResource extends Resource
{
    protected static ?string $model = EmployeeSalaryCut::class;
    protected static ?string $navigationIcon = 'heroicon-o-minus-circle';
    protected static ?string $navigationGroup = 'Kompensasi & Tunjangan';
    protected static ?string $navigationLabel = 'Potongan Gaji';
    protected static ?int $navigationSort = 404;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->required(),
                Forms\Components\TextInput::make('cut_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('cut_type')
                    ->required(),
                Forms\Components\TextInput::make('calculation_type')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date'),
                Forms\Components\TextInput::make('installment_months')
                    ->numeric(),
                Forms\Components\TextInput::make('paid_months')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('cut_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cut_type'),
                Tables\Columns\TextColumn::make('calculation_type'),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('installment_months')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_months')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
            'index' => Pages\ListEmployeeSalaryCuts::route('/'),
            'create' => Pages\CreateEmployeeSalaryCut::route('/create'),
            'edit' => Pages\EditEmployeeSalaryCut::route('/{record}/edit'),
        ];
    }
}
