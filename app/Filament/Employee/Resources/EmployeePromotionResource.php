<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeePromotionResource\Pages;
use App\Filament\Employee\Resources\EmployeePromotionResource\RelationManagers;
use App\Models\EmployeePromotion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeePromotionResource extends Resource
{
    protected static ?string $model = EmployeePromotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

   protected static ?string $navigationGroup = 'Operasional Pegawai';

    protected static ?string $navigationLabel = 'Promosi';

    protected static ?string $modelLabel = 'Promosi Golongan';

    protected static ?string $pluralModelLabel = 'Promosi Golongan';

    protected static ?int $navigationSort = 303;
    public static function getModelLabel(): string
    {
        return 'Promosi Pegawai Tetap';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Promosi Pegawai Tetap';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Promosi')
                    ->schema([
                        Forms\Components\TextInput::make('decision_letter_number')
                            ->label('Nomor Surat Keputusan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('SK/HRD/001/2025'),

                        Forms\Components\DatePicker::make('promotion_date')
                            ->label('Tanggal Promosi')
                            ->required()
                            ->default(now()),
                    ])->columns(2),

                Forms\Components\Section::make('Data Pegawai')
                    ->description('Pilih Pegawai yang akan dipromosikan (hanya Pegawai tetap)')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai')
                            ->relationship(
                                'employee',
                                'name',
                                fn (Builder $query) => $query->whereHas('employmentStatus', function (Builder $query) {
                                    $query->where('name', 'like', '%tetap%')
                                          ->orWhere('name', 'like', '%permanen%')
                                          ->orWhere('name', 'like', '%PKWTT%');
                                })
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $employee = \App\Models\Employee::find($state);
                                    if ($employee) {
                                        $set('old_basic_salary_id', $employee->basic_salary_id);
                                    }
                                }
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' - ' . ($record->nippam ?? 'No NIPPAM') . ' (' . ($record->employmentStatus?->name ?? 'Status tidak diketahui') . ')')
                            ->helperText('Hanya menampilkan Pegawai dengan status tetap/permanen yang dapat dipromosikan')
                            ->rules([
                                fn () => function (string $attribute, $value, \Closure $fail) {
                                    if ($value) {
                                        $employee = \App\Models\Employee::with('employmentStatus')->find($value);
                                        if (!$employee || !$employee->employmentStatus) {
                                            $fail('Pegawai tidak memiliki status employment yang valid.');
                                            return;
                                        }

                                        $statusName = strtolower($employee->employmentStatus->name);
                                        $isPermanent = str_contains($statusName, 'tetap') ||
                                                      str_contains($statusName, 'permanen') ||
                                                      str_contains($statusName, 'pkwtt');

                                        if (!$isPermanent) {
                                            $fail('Hanya Pegawai dengan status tetap/permanen yang dapat dipromosikan. Status Pegawai saat ini: ' . $employee->employmentStatus->name);
                                        }
                                    }
                                },
                            ]),

                        Forms\Components\Placeholder::make('employee_info')
                            ->label('Info Pegawai')
                            ->content(function (Forms\Get $get) {
                                $employeeId = $get('employee_id');
                                if (!$employeeId) {
                                    return 'Pilih Pegawai terlebih dahulu';
                                }

                                $employee = \App\Models\Employee::with(['employmentStatus', 'department', 'position'])->find($employeeId);
                                if (!$employee) {
                                    return 'Pegawai tidak ditemukan';
                                }

                                return new \Illuminate\Support\HtmlString('
                                    <div class="space-y-2">
                                        <div><strong>NIPPAM:</strong> ' . ($employee->nippam ?? 'Belum ada') . '</div>
                                        <div><strong>Status Employment:</strong> <span class="px-2 py-1 text-sm bg-green-100 text-green-800 rounded">' . ($employee->employmentStatus?->name ?? 'Tidak diketahui') . '</span></div>
                                        <div><strong>Departemen:</strong> ' . ($employee->department?->name ?? 'Tidak diketahui') . '</div>
                                        <div><strong>Posisi:</strong> ' . ($employee->position?->name ?? 'Tidak diketahui') . '</div>
                                    </div>
                                ');
                            })
                            ->visible(fn (Forms\Get $get) => $get('employee_id')),
                    ]),

                Forms\Components\Section::make('Perubahan Gaji')
                    ->schema([
                        Forms\Components\Select::make('old_basic_salary_id')
                            ->label('Grade Gaji Lama')
                            ->relationship('oldSalaryGrade', 'name')
                            ->required()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('new_basic_salary_id')
                            ->label('Grade Gaji Baru')
                            ->relationship('newSalaryGrade', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('Dokumen & Keterangan')
                    ->schema([
                        Forms\Components\FileUpload::make('doc_promotion')
                            ->label('Dokumen Promosi')
                            ->directory('employee-promotions')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120), // 5MB

                        Forms\Components\Textarea::make('desc')
                            ->label('Deskripsi/Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Hidden::make('users_id')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('employee.employmentStatus', function (Builder $query) {
                $query->where('name', 'like', '%tetap%')
                      ->orWhere('name', 'like', '%permanen%')
                      ->orWhere('name', 'like', '%PKWTT%');
            }))
            ->columns([
                Tables\Columns\TextColumn::make('decision_letter_number')
                    ->label('No. SK')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.nippam')
                    ->label('NIPPAM')
                    ->searchable(),

                Tables\Columns\TextColumn::make('employee.employmentStatus.name')
                    ->label('Status Pegawai')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-check-circle'),

                Tables\Columns\TextColumn::make('promotion_date')
                    ->label('Tanggal Promosi')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('oldSalaryGrade.name')
                    ->label('Grade Lama')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('newSalaryGrade.name')
                    ->label('Grade Baru')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('salary_increase')
                    ->label('Kenaikan Gaji')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->salary_increase),

                Tables\Columns\IconColumn::make('doc_promotion')
                    ->label('Dokumen')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-x-mark')
                    ->getStateUsing(fn ($record) => !empty($record->doc_promotion)),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Pegawai')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('promotion_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date): Builder => $query->whereDate('promotion_date', '>=', $date))
                            ->when($data['until'], fn (Builder $query, $date): Builder => $query->whereDate('promotion_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download_document')
                    ->label('Unduh Dokumen')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => $record->doc_promotion ? asset('storage/' . $record->doc_promotion) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->doc_promotion)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('promotion_date', 'desc');
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
            'index' => Pages\ListEmployeePromotions::route('/'),
            'create' => Pages\CreateEmployeePromotion::route('/create'),
            'view' => Pages\ViewEmployeePromotion::route('/{record}'),
            'edit' => Pages\EditEmployeePromotion::route('/{record}/edit'),
        ];
    }
}
