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
    protected static ?string $navigationLabel = 'Pinjaman & Potongan Pribadi';
    protected static ?string $modelLabel = 'Potongan/Pinjaman';
    protected static ?string $pluralModelLabel = 'Pinjaman & Potongan Pribadi';
    protected static ?int $navigationSort = 404;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pinjaman / Potongan')
                    ->description('Kelola detail pemotongan gaji rutin atau cicilan pinjaman.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai')
                            ->relationship('employee', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('cut_name')
                            ->label('Nama Potongan / Pinjaman')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Hutang Bank Mandiri, Tabungan Koperasi'),
                        
                        Forms\Components\Select::make('cut_type')
                            ->label('Tipe Potongan')
                            ->options([
                                'permanent' => 'Tetap (Rutin Tiap Bulan)',
                                'temporary' => 'Sementara (Cicilan/Pinjaman)',
                            ])
                            ->required()
                            ->live()
                            ->native(false),
                        
                        Forms\Components\Select::make('calculation_type')
                            ->label('Jenis Perhitungan')
                            ->options([
                                'fixed' => 'Nominal Tetap',
                                'percentage' => 'Persentase Gaji Pokok',
                            ])
                            ->required()
                            ->default('fixed')
                            ->native(false),

                        Forms\Components\TextInput::make('amount')
                            ->label('Nilai / Nominal')
                            ->numeric()
                            ->required()
                            ->prefix(fn (Forms\Get $get) => $get('calculation_type') === 'percentage' ? '%' : 'Rp'),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai Berlaku')
                            ->default(now())
                            ->required(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Berakhir (Opsional)')
                            ->helperText('Kosongkan jika berlaku selamanya'),

                        Forms\Components\Grid::make(2)
                            ->visible(fn (Forms\Get $get) => $get('cut_type') === 'temporary')
                            ->schema([
                                Forms\Components\TextInput::make('installment_months')
                                    ->label('Total Tenor (Bulan)')
                                    ->numeric()
                                    ->default(12)
                                    ->required(fn (Forms\Get $get) => $get('cut_type') === 'temporary'),
                                Forms\Components\TextInput::make('paid_months')
                                    ->label('Sudah Dibayar (Bulan)')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Hanya potongan aktif yang akan dihitung saat payroll bulanan.')
                            ->default(true)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan Tambahan')
                            ->columnSpanFull(),
                    ]),
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
                    ->description(fn (EmployeeSalaryCut $record) => $record->description)
                    ->searchable(),
                Tables\Columns\TextColumn::make('cut_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'permanent' => 'info',
                        'temporary' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'permanent' => 'Tetap',
                        'temporary' => 'Cicilan',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal / Persen')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->calculation_type === 'percentage' 
                            ? $state . ' %' 
                            : 'Rp ' . number_format($state, 0, ',', '.');
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_months')
                    ->label('Progress Cicilan')
                    ->formatStateUsing(function ($state, EmployeeSalaryCut $record) {
                        if ($record->cut_type === 'permanent') return 'Rutin';
                        return "{$state} / {$record->installment_months} Bln";
                    })
                    ->color(function ($state, EmployeeSalaryCut $record) {
                        if ($record->cut_type === 'permanent') return 'gray';
                        return $record->isCompleted() ? 'success' : 'warning';
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cut_type')
                    ->label('Tipe Potongan')
                    ->options([
                        'permanent' => 'Tetap',
                        'temporary' => 'Cicilan',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Hanya Aktif'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
            'index' => Pages\ListEmployeeSalaryCuts::route('/'),
            'create' => Pages\CreateEmployeeSalaryCut::route('/create'),
            'edit' => Pages\EditEmployeeSalaryCut::route('/{record}/edit'),
        ];
    }
}
