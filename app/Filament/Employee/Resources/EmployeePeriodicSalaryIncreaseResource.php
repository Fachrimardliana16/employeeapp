<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource\Pages;
use App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource\RelationManagers;
use App\Models\EmployeePeriodicSalaryIncrease;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeePeriodicSalaryIncreaseResource extends Resource
{
    protected static ?string $model = EmployeePeriodicSalaryIncrease::class;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    protected static ?string $navigationGroup = 'Kompensasi & Tunjangan';

    protected static ?string $navigationLabel = 'Kenaikan Gaji Berkala';

    protected static ?string $modelLabel = 'Kenaikan Gaji Berkala';

    protected static ?string $pluralModelLabel = 'Kenaikan Gaji Berkala';

    protected static ?int $navigationSort = 203;

    public static function form(Form $form): Form
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
                Forms\Components\Section::make('Salary Increase Details')
                    ->schema([
                        Forms\Components\TextInput::make('previous_basic_salary')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),
                        Forms\Components\TextInput::make('new_basic_salary')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                $previous = (float) $get('previous_basic_salary');
                                $new = (float) $state;
                                if ($previous > 0 && $new > 0) {
                                    $increase = $new - $previous;
                                    $percentage = ($increase / $previous) * 100;
                                    $set('increase_amount', number_format($increase, 2));
                                    $set('increase_percentage', number_format($percentage, 2));
                                }
                            }),
                        Forms\Components\TextInput::make('increase_amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->readOnly(),
                        Forms\Components\TextInput::make('increase_percentage')
                            ->numeric()
                            ->suffix('%')
                            ->step(0.01)
                            ->readOnly(),
                        Forms\Components\DatePicker::make('effective_date')
                            ->required(),
                        Forms\Components\Textarea::make('increase_reason')
                            ->required()
                            ->maxLength(500),
                        Forms\Components\DatePicker::make('approval_date'),
                        Forms\Components\Select::make('approved_by')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(500),
                        Forms\Components\Hidden::make('users_id')
                            ->default(fn () => auth()->id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('previous_basic_salary')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('new_basic_salary')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('increase_amount')
                    ->money('USD')
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('increase_percentage')
                    ->suffix('%')
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('effective_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Approved By')
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
                Tables\Filters\Filter::make('effective_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('effective_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('effective_date', '<=', $date),
                            );
                    }),
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
            'index' => Pages\ListEmployeePeriodicSalaryIncreases::route('/'),
            'create' => Pages\CreateEmployeePeriodicSalaryIncrease::route('/create'),
            'edit' => Pages\EditEmployeePeriodicSalaryIncrease::route('/{record}/edit'),
        ];
    }
}
