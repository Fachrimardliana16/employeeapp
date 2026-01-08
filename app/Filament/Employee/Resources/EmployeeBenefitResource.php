<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeBenefitResource\Pages;
use App\Filament\Employee\Resources\EmployeeBenefitResource\RelationManagers;
use App\Models\EmployeeBenefit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeBenefitResource extends Resource
{
    protected static ?string $model = EmployeeBenefit::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Kompensasi & Tunjangan';

    protected static ?string $navigationLabel = 'Tunjangan';

    protected static ?string $modelLabel = 'Tunjangan Karyawan';

    protected static ?string $pluralModelLabel = 'Tunjangan Karyawan';

    protected static ?int $navigationSort = 403;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('employee_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('benefits')
                    ->required(),
                Forms\Components\TextInput::make('users_id')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_id')
                    ->numeric()
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
                Tables\Columns\TextColumn::make('deleted_at')
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
            'index' => Pages\ListEmployeeBenefits::route('/'),
            'create' => Pages\CreateEmployeeBenefit::route('/create'),
            'edit' => Pages\EditEmployeeBenefit::route('/{record}/edit'),
        ];
    }
}
