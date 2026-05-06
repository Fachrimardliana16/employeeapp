<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\MasterEmployeeGradeResource\Pages;
use App\Filament\Employee\Resources\MasterEmployeeGradeResource\RelationManagers;
use App\Models\MasterEmployeeGrade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterEmployeeGradeResource extends Resource
{
    protected static ?string $model = MasterEmployeeGrade::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Golongan';

    protected static ?string $modelLabel = 'Golongan';

    protected static ?string $pluralModelLabel = 'Golongan';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 805;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Golongan Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Kode Golongan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: A1, B2, C3'),
                        Forms\Components\TextInput::make('pangkat_name')
                            ->label('Nama Pangkat')
                            ->maxLength(255)
                            ->placeholder('Sesuai PP Pasal 8: Pegawai Dasar Muda, Pelaksana, Staf, dll')
                            ->helperText('Nama pangkat sesuai PP Perusahaan Pasal 8'),
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
                    ->label('Golongan')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('pangkat_name')
                    ->label('Nama Pangkat')
                    ->searchable()
                    ->sortable()
                    ->placeholder('(Belum diisi)')
                    ->description(fn ($record) => $record->desc),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employees_count')
                    ->label('Jumlah Pegawai')
                    ->counts('employees')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterEmployeeGrades::route('/'),
            'create' => Pages\CreateMasterEmployeeGrade::route('/create'),
            'edit' => Pages\EditMasterEmployeeGrade::route('/{record}/edit'),
        ];
    }
}
