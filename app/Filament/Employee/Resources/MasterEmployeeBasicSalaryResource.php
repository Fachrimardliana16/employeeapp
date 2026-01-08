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
                    ->label('Service Grade')
                    ->options(function () {
                        return MasterEmployeeServiceGrade::with('employeeGrade')
                            ->where('is_active', true)
                            ->get()
                            ->mapWithKeys(function ($serviceGrade) {
                                return [$serviceGrade->id => $serviceGrade->employeeGrade->name . ' - ' . $serviceGrade->service_grade];
                            });
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('employee_grade_id')
                    ->label('Employee Grade')
                    ->options(MasterEmployeeGrade::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('amount')
                    ->label('Salary Amount')
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
                Tables\Columns\TextColumn::make('serviceGrade.employeeGrade.name')
                    ->label('Employee Grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('serviceGrade.service_grade')
                    ->label('Service Grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employeeGrade.name')
                    ->label('Grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Salary Amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('desc')
                    ->label('Description')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('employee_grade_id')
                    ->label('Employee Grade')
                    ->options(MasterEmployeeGrade::where('is_active', true)->pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
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
            'index' => Pages\ListMasterEmployeeBasicSalaries::route('/'),
            'create' => Pages\CreateMasterEmployeeBasicSalary::route('/create'),
            'edit' => Pages\EditMasterEmployeeBasicSalary::route('/{record}/edit'),
        ];
    }
}
