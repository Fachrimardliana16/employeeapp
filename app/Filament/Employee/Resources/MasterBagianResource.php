<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\MasterBagianResource\Pages;
use App\Models\MasterDepartment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterBagianResource extends Resource
{
    protected static ?string $model = MasterDepartment::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Bagian ';

    protected static ?string $modelLabel = 'Bagian';

    protected static ?string $pluralModelLabel = 'Bagian';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 801;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'Bagian');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Bagian')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Bagian')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Hidden::make('type')
                            ->default('Bagian'),
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
                    ->label('Nama Bagian')
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
            'index' => Pages\ListMasterBagians::route('/'),
            'create' => Pages\CreateMasterBagian::route('/create'),
            'edit' => Pages\EditMasterBagian::route('/{record}/edit'),
        ];
    }
}
