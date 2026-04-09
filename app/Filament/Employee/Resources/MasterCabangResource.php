<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\MasterCabangResource\Pages;
use App\Models\MasterDepartment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterCabangResource extends Resource
{
    protected static ?string $model = MasterDepartment::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Cabang ';

    protected static ?string $modelLabel = 'Cabang';

    protected static ?string $pluralModelLabel = 'Cabang';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 802;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'Cabang');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Cabang')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Cabang')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Hidden::make('type')
                            ->default('Cabang'),
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
                    ->label('Nama Cabang')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('desc')
                    ->label('Keterangan')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterCabangs::route('/'),
            'create' => Pages\CreateMasterCabang::route('/create'),
            'edit' => Pages\EditMasterCabang::route('/{record}/edit'),
        ];
    }
}
