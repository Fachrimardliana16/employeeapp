<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\PerformanceAppraisalResource\Pages;
use App\Filament\Employee\Resources\PerformanceAppraisalResource\RelationManagers;
use App\Models\PerformanceAppraisal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PerformanceAppraisalResource extends Resource
{
    protected static ?string $model = PerformanceAppraisal::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Penilaian Kinerja';
    protected static ?string $navigationLabel = 'Penilaian Kinerja';
    protected static ?int $navigationSort = 501;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->required(),
                Forms\Components\TextInput::make('appraisal_period')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('appraisal_date')
                    ->required(),
                Forms\Components\Select::make('appraiser_id')
                    ->relationship('appraiser', 'name')
                    ->required(),
                Forms\Components\TextInput::make('criteria_scores')
                    ->required(),
                Forms\Components\TextInput::make('total_score')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('performance_grade'),
                Forms\Components\Textarea::make('strengths')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('weaknesses')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('recommendations')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('employee_comment')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->required(),
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
                Tables\Columns\TextColumn::make('appraisal_period')
                    ->searchable(),
                Tables\Columns\TextColumn::make('appraisal_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appraiser.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('performance_grade'),
                Tables\Columns\TextColumn::make('status'),
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
            'index' => Pages\ListPerformanceAppraisals::route('/'),
            'create' => Pages\CreatePerformanceAppraisal::route('/create'),
            'edit' => Pages\EditPerformanceAppraisal::route('/{record}/edit'),
        ];
    }
}
