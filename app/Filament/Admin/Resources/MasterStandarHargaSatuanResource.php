<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterStandarHargaSatuanResource\Pages;
use App\Filament\Admin\Resources\MasterStandarHargaSatuanResource\RelationManagers;
use App\Models\MasterStandarHargaSatuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterStandarHargaSatuanResource extends Resource
{
    protected static ?string $model = MasterStandarHargaSatuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Standar Harga Satuan (SHS)';

    protected static ?string $modelLabel = 'Standar Harga Satuan';

    protected static ?string $pluralModelLabel = 'Standar Harga Satuan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Item')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Akomodasi Hotel Bintang 3'),

                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->options(MasterStandarHargaSatuan::getCategoryOptions())
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('location')
                            ->label('Lokasi/Daerah')
                            ->maxLength(255)
                            ->placeholder('Contoh: Jakarta, Bandung'),

                        Forms\Components\TextInput::make('grade_level')
                            ->label('Tingkat Jabatan')
                            ->maxLength(255)
                            ->placeholder('Contoh: Direktur, Manager, Staff'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Biaya')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah Biaya')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->step(0.01),

                        Forms\Components\Select::make('unit')
                            ->label('Satuan')
                            ->options(MasterStandarHargaSatuan::getUnitOptions())
                            ->required()
                            ->default('per_day')
                            ->native(false),

                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Non-aktifkan jika SHS ini tidak digunakan lagi'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Item')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->formatStateUsing(fn(string $state): string => MasterStandarHargaSatuan::getCategoryOptions()[$state] ?? $state)
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'accommodation' => 'info',
                        'pocket_money' => 'success',
                        'reserve' => 'warning',
                        'transport' => 'primary',
                        'meal' => 'secondary',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('grade_level')
                    ->label('Tingkat Jabatan')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan')
                    ->formatStateUsing(fn(string $state): string => MasterStandarHargaSatuan::getUnitOptions()[$state] ?? $state)
                    ->badge(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(MasterStandarHargaSatuan::getCategoryOptions()),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada data SHS')
            ->emptyStateDescription('Mulai dengan membuat data Standar Harga Satuan pertama.')
            ->emptyStateIcon('heroicon-o-currency-dollar');
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
            'index' => Pages\ListMasterStandarHargaSatuans::route('/'),
            'create' => Pages\CreateMasterStandarHargaSatuan::route('/create'),
            'edit' => Pages\EditMasterStandarHargaSatuan::route('/{record}/edit'),
        ];
    }
}
