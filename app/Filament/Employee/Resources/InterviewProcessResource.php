<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\InterviewProcessResource\Pages;
use App\Filament\Employee\Resources\InterviewProcessResource\RelationManagers;
use App\Models\InterviewProcess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InterviewProcessResource extends Resource
{
    protected static ?string $model = InterviewProcess::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Rekrutmen & Seleksi';
    protected static ?string $navigationLabel = 'Proses Interview';
    protected static ?int $navigationSort = 102;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('job_application_id')
                    ->relationship('jobApplication', 'name')
                    ->required(),
                Forms\Components\TextInput::make('interview_stage')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('interview_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('interview_date')
                    ->required(),
                Forms\Components\TextInput::make('interview_time')
                    ->required(),
                Forms\Components\TextInput::make('interview_location')
                    ->maxLength(255),
                Forms\Components\TextInput::make('interviewer_name')
                    ->maxLength(255),
                Forms\Components\Select::make('interviewer_id')
                    ->relationship('interviewer', 'name'),
                Forms\Components\TextInput::make('result')
                    ->required(),
                Forms\Components\TextInput::make('score')
                    ->numeric(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('feedback')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\TextInput::make('users_id')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jobApplication.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('interview_stage')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('interview_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('interview_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('interview_time'),
                Tables\Columns\TextColumn::make('interview_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('interviewer_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('interviewer.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('result'),
                Tables\Columns\TextColumn::make('score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
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
            'index' => Pages\ListInterviewProcesses::route('/'),
            'create' => Pages\CreateInterviewProcess::route('/create'),
            'edit' => Pages\EditInterviewProcess::route('/{record}/edit'),
        ];
    }
}
