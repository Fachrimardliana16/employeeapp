<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\MasterPromotionTypeResource\Pages;
use App\Filament\Employee\Resources\MasterPromotionTypeResource\RelationManagers;
use App\Models\MasterPromotionType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterPromotionTypeResource extends Resource
{
    protected static ?string $model = MasterPromotionType::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?string $navigationLabel = 'Jenis Kenaikan Pangkat';

    protected static ?string $modelLabel = 'Jenis Kenaikan Pangkat';

    protected static ?string $pluralModelLabel = 'Jenis Kenaikan Pangkat';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 850;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jenis Kenaikan Pangkat')
                    ->description('Sesuai PP Perusahaan Pasal 10-16')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('Contoh: biasa, pilihan, penyesuaian')
                            ->helperText('Kode unik untuk jenis kenaikan pangkat'),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Jenis Kenaikan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Kenaikan Pangkat Biasa/Reguler'),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Deskripsi jenis kenaikan pangkat sesuai PP'),
                        Forms\Components\Textarea::make('requirements')
                            ->label('Persyaratan')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Persyaratan yang harus dipenuhi pegawai'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Forms\Components\Hidden::make('users_id')
                            ->default(auth()->id()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Jenis Kenaikan')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->code)
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(100)
                    ->searchable()
                    ->wrap(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->label('Hapus yang Dipilih'),
            ])
            ->defaultSort('name');
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
            'index' => Pages\ListMasterPromotionTypes::route('/'),
            'create' => Pages\CreateMasterPromotionType::route('/create'),
            'edit' => Pages\EditMasterPromotionType::route('/{record}/edit'),
        ];
    }
}
