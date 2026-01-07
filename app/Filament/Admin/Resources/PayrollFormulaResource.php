<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PayrollFormulaResource\Pages;
use App\Models\PayrollFormula;
use App\Models\PayrollComponent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PayrollFormulaResource extends Resource
{
    protected static ?string $model = PayrollFormula::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Pengaturan Payroll';
    protected static ?string $navigationLabel = 'Formula Payroll';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Formula')
                ->schema([
                    Forms\Components\TextInput::make('formula_name')
                        ->label('Nama Formula')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Formula Gaji CAPEG'),

                    Forms\Components\TextInput::make('formula_code')
                        ->label('Kode Formula')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('Contoh: CAPEG_80'),

                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('Jelaskan penggunaan formula ini...'),
                ])->columns(2),

            Forms\Components\Section::make('Penerapan Formula')
                ->description('Tentukan untuk siapa formula ini berlaku')
                ->schema([
                    Forms\Components\Select::make('applies_to')
                        ->label('Berlaku Untuk')
                        ->required()
                        ->options([
                            'status' => 'Status Kepegawaian',
                            'grade' => 'Grade/Golongan',
                            'position' => 'Jabatan',
                            'all' => 'Semua Pegawai',
                        ])
                        ->live()
                        ->afterStateUpdated(
                            fn($state, Forms\Set $set) =>
                            $state === 'all' ? $set('applies_to_value', null) : null
                        ),

                    Forms\Components\TextInput::make('applies_to_value')
                        ->label('Nilai')
                        ->maxLength(255)
                        ->placeholder('Contoh: CAPEG, THL, Grade 5, dll')
                        ->hidden(fn(Forms\Get $get) => $get('applies_to') === 'all')
                        ->helperText('Isi dengan nilai status/grade/jabatan yang sesuai'),

                    Forms\Components\TextInput::make('percentage_multiplier')
                        ->label('Persentase Multiplier')
                        ->numeric()
                        ->suffix('%')
                        ->step(0.01)
                        ->minValue(0)
                        ->maxValue(100)
                        ->placeholder('Contoh: 80 untuk CAPEG')
                        ->helperText('Isi jika gaji dihitung dengan persentase (mis: 80% dari gaji pokok)'),
                ])->columns(2),

            Forms\Components\Section::make('Komponen Formula')
                ->description('Pilih komponen payroll yang termasuk dalam formula ini')
                ->schema([
                    Forms\Components\CheckboxList::make('formula_components')
                        ->label('Komponen')
                        ->options(fn() => PayrollComponent::where('is_active', true)
                            ->pluck('component_name', 'component_code'))
                        ->columns(2)
                        ->searchable()
                        ->bulkToggleable()
                        ->helperText('Pilih komponen yang akan dihitung dalam formula ini'),
                ])->collapsible(),

            Forms\Components\Section::make('Aturan Perhitungan')
                ->schema([
                    Forms\Components\Textarea::make('calculation_rules')
                        ->label('Aturan Khusus')
                        ->rows(4)
                        ->placeholder('Contoh: Jika telat lebih dari 3x, potong 5% dari tunjangan kinerja')
                        ->columnSpanFull()
                        ->helperText('Isi jika ada aturan perhitungan khusus untuk formula ini'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
                        ->inline(false)
                        ->helperText('Formula hanya akan digunakan jika status aktif'),

                    Forms\Components\Hidden::make('users_id')
                        ->default(fn() => Auth::id()),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('formula_code')
                ->label('Kode')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->copyable(),

            Tables\Columns\TextColumn::make('formula_name')
                ->label('Nama Formula')
                ->searchable()
                ->sortable()
                ->description(fn($record) => $record->description),

            Tables\Columns\TextColumn::make('applies_to')
                ->label('Berlaku Untuk')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'status' => 'success',
                    'grade' => 'info',
                    'position' => 'warning',
                    'all' => 'gray',
                    default => 'gray',
                })
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    'status' => 'Status',
                    'grade' => 'Grade',
                    'position' => 'Jabatan',
                    'all' => 'Semua',
                    default => $state,
                }),

            Tables\Columns\TextColumn::make('applies_to_value')
                ->label('Nilai')
                ->placeholder('Semua')
                ->searchable(),

            Tables\Columns\TextColumn::make('percentage_multiplier')
                ->label('Multiplier')
                ->suffix('%')
                ->placeholder('-')
                ->sortable(),

            Tables\Columns\IconColumn::make('is_active')
                ->label('Aktif')
                ->boolean()
                ->sortable(),

            Tables\Columns\TextColumn::make('updated_at')
                ->label('Terakhir Diubah')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(),
        ])
            ->defaultSort('formula_code')
            ->filters([
                Tables\Filters\SelectFilter::make('applies_to')
                    ->label('Berlaku Untuk')
                    ->options([
                        'status' => 'Status',
                        'grade' => 'Grade',
                        'position' => 'Jabatan',
                        'all' => 'Semua',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrollFormulas::route('/'),
            'create' => Pages\CreatePayrollFormula::route('/create'),
            'edit' => Pages\EditPayrollFormula::route('/{record}/edit'),
        ];
    }
}
