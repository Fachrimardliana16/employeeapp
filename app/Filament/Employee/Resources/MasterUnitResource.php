<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\MasterUnitResource\Pages;
use App\Models\MasterDepartment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterUnitResource extends Resource
{
    protected static ?string $model = MasterDepartment::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationLabel = 'Unit ';

    protected static ?string $modelLabel = 'Unit';

    protected static ?string $pluralModelLabel = 'Unit';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 803;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'Unit');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Unit')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Unit')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Hidden::make('type')
                            ->default('Unit'),
                        Forms\Components\Textarea::make('desc')
                            ->label('Keterangan')
                            ->rows(3),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Unit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('desc')
                    ->label('Keterangan')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ])->label('Aksi'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->label('Hapus yang Dipilih'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterUnits::route('/'),
            'create' => Pages\CreateMasterUnit::route('/create'),
            'edit' => Pages\EditMasterUnit::route('/{record}/edit'),
        ];
    }
}
