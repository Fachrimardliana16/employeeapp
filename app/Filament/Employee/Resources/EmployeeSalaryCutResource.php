<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeSalaryCutResource\Pages;
use App\Filament\Employee\Resources\EmployeeSalaryCutResource\RelationManagers;
use App\Models\EmployeeSalaryCut;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeSalaryCutResource extends Resource
{
    protected static ?string $model = EmployeeSalaryCut::class;
    protected static ?string $navigationIcon = 'heroicon-o-minus-circle';
    protected static ?string $navigationGroup = 'Kompensasi & Tunjangan';
    protected static ?string $navigationLabel = 'Potongan Gaji';
    protected static ?int $navigationSort = 404;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Potongan Gaji')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai')
                            ->relationship('employee', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('cut_name')
                            ->label('Nama Potongan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Potongan BPJS'),
                        Forms\Components\TextInput::make('cut_type')
                            ->label('Jenis Potongan')
                            ->required()
                            ->placeholder('Contoh: BPJS'),
                        Forms\Components\TextInput::make('calculation_type')
                            ->label('Tipe Perhitungan')
                            ->required()
                            ->placeholder('Contoh: Persentase/Nominal'),
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Berakhir'),
                        Forms\Components\TextInput::make('installment_months')
                            ->label('Jumlah Cicilan (Bulan)')
                            ->numeric(),
                        Forms\Components\TextInput::make('paid_months')
                            ->label('Angsuran Dibayar (Bulan)')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Hidden::make('users_id')
                    ->default(fn() => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Pegawai')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('cut_name')
                    ->label('Nama Potongan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cut_type')
                    ->label('Jenis'),
                Tables\Columns\TextColumn::make('calculation_type')
                    ->label('Tipe Hitung'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tgl Mulai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Tgl Berakhir')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('installment_months')
                    ->label('Tenor')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_months')
                    ->label('Dibayar')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),
                    Tables\Actions\EditAction::make()->label('Edit'),
                    Tables\Actions\DeleteAction::make()->label('Hapus'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
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
            'index' => Pages\ListEmployeeSalaryCuts::route('/'),
            'create' => Pages\CreateEmployeeSalaryCut::route('/create'),
            'edit' => Pages\EditEmployeeSalaryCut::route('/{record}/edit'),
        ];
    }
}
