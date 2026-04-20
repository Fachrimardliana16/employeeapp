<?php

namespace App\Filament\Employee\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SalaryCutsRelationManager extends RelationManager
{
    protected static string $relationship = 'salaryCuts';

    protected static ?string $title = 'Potongan Pribadi (Hutang/Koperasi)';

    protected static ?string $modelLabel = 'Potongan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cut_name')
                    ->label('Nama Potongan')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Hutang Bank Mandiri'),
                Forms\Components\Select::make('cut_type')
                    ->label('Tipe Potongan')
                    ->options([
                        'permanent' => 'Tetap (Rutin Tiap Bulan)',
                        'temporary' => 'Sementara (Cicilan)',
                    ])
                    ->required()
                    ->live(),
                Forms\Components\Select::make('calculation_type')
                    ->label('Jenis Perhitungan')
                    ->options([
                        'fixed' => 'Nominal Tetap',
                        'percentage' => 'Persentase Gaji Pokok',
                    ])
                    ->required()
                    ->default('fixed'),
                Forms\Components\TextInput::make('amount')
                    ->label('Nilai/Nominal')
                    ->numeric()
                    ->required(),
                Forms\Components\Grid::make(3)
                    ->visible(fn (Forms\Get $get) => $get('cut_type') === 'temporary')
                    ->schema([
                        Forms\Components\TextInput::make('installment_months')
                            ->label('Jumlah Cicilan (Bulan)')
                            ->numeric()
                            ->default(12),
                        Forms\Components\TextInput::make('paid_months')
                            ->label('Sudah Dibayar (Bulan)')
                            ->numeric()
                            ->default(0),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Berakhir'),
                    ]),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                Forms\Components\Textarea::make('description')
                    ->label('Keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('cut_name')
            ->columns([
                Tables\Columns\TextColumn::make('cut_name')
                    ->label('Nama Potongan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cut_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'permanent' => 'info',
                        'temporary' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal/Persen')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->calculation_type === 'percentage' 
                            ? $state . ' %' 
                            : 'Rp ' . number_format($state, 0, ',', '.');
                    }),
                Tables\Columns\TextColumn::make('paid_months')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state, $record) => $record->cut_type === 'temporary' ? "{$state} / {$record->installment_months}" : '-')
                    ->visible(fn ($record) => $record && $record->cut_type === 'temporary'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
