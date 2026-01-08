<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\MasterEmployeeArchiveTypeResource\Pages;
use App\Filament\Employee\Resources\MasterEmployeeArchiveTypeResource\RelationManagers;
use App\Models\MasterEmployeeArchiveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterEmployeeArchiveTypeResource extends Resource
{
    protected static ?string $model = MasterEmployeeArchiveType::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Jenis Arsip';

    protected static ?string $modelLabel = 'Jenis Arsip';

    protected static ?string $pluralModelLabel = 'Jenis Arsip';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 815;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Archive Type Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('desc')
                            ->label('Description')
                            ->rows(3),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('desc')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),
            ])
            ->actions([
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
            'index' => Pages\ListMasterEmployeeArchiveTypes::route('/'),
            'create' => Pages\CreateMasterEmployeeArchiveType::route('/create'),
            'edit' => Pages\EditMasterEmployeeArchiveType::route('/{record}/edit'),
        ];
    }
}
