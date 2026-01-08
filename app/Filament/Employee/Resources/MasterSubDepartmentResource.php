<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\MasterSubDepartmentResource\Pages;
use App\Filament\Employee\Resources\MasterSubDepartmentResource\RelationManagers;
use App\Models\MasterSubDepartment;
use App\Models\MasterDepartment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MasterSubDepartmentResource extends Resource
{
    protected static ?string $model = MasterSubDepartment::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Sub Bagian';

    protected static ?string $modelLabel = 'Sub Bagian';

    protected static ?string $pluralModelLabel = 'Sub Bagian';

    protected static ?int $navigationSort = 803;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('departments_id')
                    ->label('Department')
                    ->options(MasterDepartment::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
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
                Tables\Filters\SelectFilter::make('departments_id')
                    ->label('Department')
                    ->options(MasterDepartment::where('is_active', true)->pluck('name', 'id')),
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
            'index' => Pages\ListMasterSubDepartments::route('/'),
            'create' => Pages\CreateMasterSubDepartment::route('/create'),
            'edit' => Pages\EditMasterSubDepartment::route('/{record}/edit'),
        ];
    }
}
