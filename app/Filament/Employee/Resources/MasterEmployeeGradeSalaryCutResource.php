<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\MasterEmployeeGradeSalaryCutResource\Pages;
use App\Filament\Employee\Resources\MasterEmployeeGradeSalaryCutResource\RelationManagers;
use App\Models\MasterEmployeeGradeSalaryCut;
use App\Models\MasterEmployeeGrade;
use App\Models\MasterEmployeeSalaryCut;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MasterEmployeeGradeSalaryCutResource extends Resource
{
    protected static ?string $model = MasterEmployeeGradeSalaryCut::class;

    protected static ?string $navigationIcon = 'heroicon-o-minus-circle';

    protected static ?string $navigationGroup = 'Data Induk';

    protected static ?string $navigationLabel = 'Potongan per Golongan';

    protected static ?string $modelLabel = 'Potongan per Golongan';

    protected static ?string $pluralModelLabel = 'Potongan per Golongan';

    protected static ?int $navigationSort = 804;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_grade_id')
                    ->label('Employee Grade')
                    ->options(MasterEmployeeGrade::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('salary_cuts_id')
                    ->label('Salary Cut Type')
                    ->relationship('salaryCut', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('amount')
                    ->label('Cut Amount')
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
                    ->label('Employee Grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('salaryCut.name')
                    ->label('Salary Cut Type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Cut Amount')
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
            'index' => Pages\ListMasterEmployeeGradeSalaryCuts::route('/'),
            'create' => Pages\CreateMasterEmployeeGradeSalaryCut::route('/create'),
            'edit' => Pages\EditMasterEmployeeGradeSalaryCut::route('/{record}/edit'),
        ];
    }
}
