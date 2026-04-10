<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\MasterEmployeeServiceGradeResource\Pages;
use App\Filament\Employee\Resources\MasterEmployeeServiceGradeResource\RelationManagers;
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

class MasterEmployeeServiceGradeResource extends Resource
{
    protected static ?string $model = MasterEmployeeServiceGrade::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Masa Kerja Golongan (MKG)';

    protected static ?string $modelLabel = 'Masa Kerja Golongan (MKG)';

    protected static ?string $pluralModelLabel = 'Masa Kerja Golongan (MKG)';

    protected static ?int $navigationSort = 806;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('service_grade')
                    ->label('Masa Kerja Golongan (MKG) Tahun')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(33)
                    ->placeholder('e.g., 0, 5, 10'),
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
                Tables\Columns\TextColumn::make('service_grade')
                    ->label('MKG (Tahun)')
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
                Tables\Filters\SelectFilter::make('employee_grade_id')
                    ->label('Golongan Pegawai')
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
            'index' => Pages\ListMasterEmployeeServiceGrades::route('/'),
            'create' => Pages\CreateMasterEmployeeServiceGrade::route('/create'),
            'edit' => Pages\EditMasterEmployeeServiceGrade::route('/{record}/edit'),
        ];
    }
}
