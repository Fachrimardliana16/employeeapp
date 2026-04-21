<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PayrollComponentResource\Pages;
use App\Models\PayrollComponent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class PayrollComponentResource extends Resource
{
    protected static ?string $model = PayrollComponent::class;
    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationGroup = 'Pengaturan Payroll';
    protected static ?string $navigationLabel = 'Komponen Payroll';
    protected static ?string $modelLabel = 'Komponen Payroll';
    protected static ?string $pluralModelLabel = 'Komponen Payroll';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Komponen')
                ->schema([
                    Forms\Components\TextInput::make('component_name')
                        ->label('Nama Komponen')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Tunjangan Transport'),

                    Forms\Components\TextInput::make('component_code')
                        ->label('Kode Komponen')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('Contoh: TUNJ_TRANSPORT')
                        ->alphaNum(),

                    Forms\Components\Select::make('component_type')
                        ->label('Tipe Komponen')
                        ->required()
                        ->options([
                            'income' => 'Tunjangan (Pendapatan)',
                            'deduction' => 'Potongan',
                            'bonus' => 'Bonus',
                        ])
                        ->native(false),

                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('Jelaskan komponen ini...'),
                ])->columns(2),

            Forms\Components\Section::make('Metode Perhitungan')
                ->description('Tentukan cara perhitungan nilai komponen')
                ->schema([
                    Forms\Components\Select::make('calculation_method')
                        ->label('Metode Perhitungan')
                        ->required()
                        ->options([
                            'fixed' => 'Jumlah Tetap',
                            'percentage_base' => 'Persentase dari Gaji Pokok',
                            'percentage_gross' => 'Persentase dari Gross Salary',
                            'custom_formula' => 'Formula Kustom',
                        ])
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state === 'fixed') {
                                $set('formula', null);
                            }
                        }),

                    Forms\Components\TextInput::make('default_amount')
                        ->label('Jumlah Default')
                        ->required()
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0)
                        ->visible(fn(Forms\Get $get) => $get('calculation_method') === 'fixed')
                        ->helperText('Jumlah tetap yang akan ditambahkan/dikurangkan'),

                    Forms\Components\TextInput::make('default_amount')
                        ->label('Persentase')
                        ->required()
                        ->numeric()
                        ->suffix('%')
                        ->step(0.01)
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(0)
                        ->visible(fn(Forms\Get $get) => in_array($get('calculation_method'), ['percentage_base', 'percentage_gross']))
                        ->helperText('Persentase yang akan dihitung dari basis'),

                    Forms\Components\Textarea::make('formula')
                        ->label('Formula Kustom')
                        ->rows(4)
                        ->placeholder('Contoh: base_salary * 0.05 + (present_days / work_days) * 100000')
                        ->visible(fn(Forms\Get $get) => $get('calculation_method') === 'custom_formula')
                        ->helperText('Gunakan variabel: base_salary, gross_salary, work_days, present_days, late_count, absent_count')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Pengaturan Tambahan')
                ->schema([
                    Forms\Components\Toggle::make('is_taxable')
                        ->label('Kena Pajak')
                        ->default(false)
                        ->inline(false)
                        ->helperText('Tandai jika komponen ini termasuk dalam perhitungan pajak'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
                        ->inline(false)
                        ->helperText('Komponen hanya akan dihitung jika aktif'),

                    Forms\Components\Hidden::make('users_id')
                        ->default(fn() => Auth::id()),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('component_code')
                ->label('Kode')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->copyable(),

            Tables\Columns\TextColumn::make('component_name')
                ->label('Nama Komponen')
                ->searchable()
                ->sortable()
                ->description(fn($record) => $record->description),

            Tables\Columns\TextColumn::make('component_type')
                ->label('Tipe')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'allowance' => 'success',
                    'deduction' => 'danger',
                    'bonus' => 'warning',
                    default => 'gray',
                })
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    'income' => 'Tunjangan',
                    'allowance' => 'Tunjangan', // fallback
                    'deduction' => 'Potongan',
                    'bonus' => 'Bonus',
                    default => $state,
                }),

            Tables\Columns\TextColumn::make('calculation_method')
                ->label('Metode Hitung')
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    'fixed' => 'Tetap',
                    'percentage_base' => '% Gaji Pokok',
                    'percentage_gross' => '% Gross',
                    'custom_formula' => 'Formula',
                    default => $state,
                }),

            Tables\Columns\TextColumn::make('default_amount')
                ->label('Nilai Default')
                ->money('IDR')
                ->sortable(),

            Tables\Columns\IconColumn::make('is_taxable')
                ->label('Pajak')
                ->boolean()
                ->sortable(),

            Tables\Columns\IconColumn::make('is_active')
                ->label('Aktif')
                ->boolean()
                ->sortable(),
        ])
            ->defaultSort('component_code')
            ->filters([
                Tables\Filters\SelectFilter::make('component_type')
                    ->label('Tipe')
                    ->options([
                        'income' => 'Tunjangan',
                        'deduction' => 'Potongan',
                        'bonus' => 'Bonus',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                Tables\Filters\TernaryFilter::make('is_taxable')
                    ->label('Kena Pajak'),
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
            ActivitylogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrollComponents::route('/'),
            'create' => Pages\CreatePayrollComponent::route('/create'),
            'edit' => Pages\EditPayrollComponent::route('/{record}/edit'),
        ];
    }
}
