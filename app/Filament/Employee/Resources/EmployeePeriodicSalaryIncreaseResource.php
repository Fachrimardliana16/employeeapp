<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource\Pages;
use App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource\RelationManagers;
use App\Models\EmployeePeriodicSalaryIncrease;
use App\Models\Employee;
use App\Models\EmployeeAgreement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class EmployeePeriodicSalaryIncreaseResource extends Resource
{
    protected static ?string $model = EmployeePeriodicSalaryIncrease::class;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    protected static ?string $navigationGroup = 'Kompensasi & Tunjangan';

    protected static ?string $navigationLabel = 'Kenaikan Gaji Berkala';

    protected static ?string $modelLabel = 'Kenaikan Gaji Berkala';

    protected static ?string $pluralModelLabel = 'Kenaikan Gaji Berkala';

    protected static ?int $navigationSort = 405;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Employee Information')
                    ->schema([
                        Forms\Components\Select::make('employees_id')
                            ->relationship('employee', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $employee = Employee::find($state);
                                    if ($employee) {
                                        // Cek masa kerja dari kontrak pertama
                                        $firstContract = EmployeeAgreement::where('employees_id', $state)
                                            ->orderBy('effective_date_start')
                                            ->first();

                                        if ($firstContract) {
                                            $yearsOfService = Carbon::parse($firstContract->effective_date_start)
                                                ->diffInYears(Carbon::now());

                                            $set('years_of_service_info', $yearsOfService . ' tahun masa kerja');

                                            // Cek kenaikan berkala terakhir
                                            $lastIncrease = EmployeePeriodicSalaryIncrease::where('employees_id', $state)
                                                ->orderBy('effective_date', 'desc')
                                                ->first();

                                            if ($lastIncrease) {
                                                $yearsSinceLastIncrease = Carbon::parse($lastIncrease->effective_date)
                                                    ->diffInYears(Carbon::now());

                                                $set(
                                                    'last_increase_info',
                                                    $yearsSinceLastIncrease . ' tahun sejak kenaikan terakhir (' .
                                                        Carbon::parse($lastIncrease->effective_date)->format('d/m/Y') . ')'
                                                );
                                            } else {
                                                $set('last_increase_info', 'Belum pernah mendapat kenaikan berkala');
                                            }
                                        }
                                    }
                                }
                            })
                            ->helperText(
                                fn(Forms\Get $get): ?string =>
                                $get('years_of_service_info') ?? 'Pilih pegawai untuk melihat masa kerja'
                            ),

                        Forms\Components\Placeholder::make('eligibility_info')
                            ->label('Informasi Kelayakan')
                            ->content(
                                fn(Forms\Get $get): string =>
                                $get('last_increase_info') ?? 'Pilih pegawai terlebih dahulu'
                            )
                            ->visible(fn(Forms\Get $get): bool => $get('employees_id') !== null),
                    ]),
                Forms\Components\Section::make('Salary Increase Details')
                    ->schema([
                        Forms\Components\TextInput::make('previous_basic_salary')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),
                        Forms\Components\TextInput::make('new_basic_salary')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                $previous = (float) $get('previous_basic_salary');
                                $new = (float) $state;
                                if ($previous > 0 && $new > 0) {
                                    $increase = $new - $previous;
                                    $percentage = ($increase / $previous) * 100;
                                    $set('increase_amount', number_format($increase, 2));
                                    $set('increase_percentage', number_format($percentage, 2));
                                }
                            }),
                        Forms\Components\TextInput::make('increase_amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->readOnly(),
                        Forms\Components\TextInput::make('increase_percentage')
                            ->numeric()
                            ->suffix('%')
                            ->step(0.01)
                            ->readOnly(),
                        Forms\Components\DatePicker::make('effective_date')
                            ->required(),
                        Forms\Components\Textarea::make('increase_reason')
                            ->required()
                            ->maxLength(500),
                        Forms\Components\DatePicker::make('approval_date'),
                        Forms\Components\Select::make('approved_by')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Toggle::make('is_applied')
                            ->label('Terapkan langsung (Realisasi)')
                            ->default(true)
                            ->helperText('Jika dicentang, gaji pokok dan MKG di profil pegawai akan langsung diperbarui saat disimpan. Jika tidak, data akan tersimpan sebagai usulan.'),
                        
                        Forms\Components\FileUpload::make('proposal_docs')
                            ->label('Dokumen Usulan')
                            ->directory('employee-kgb/proposals')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120),

                        Forms\Components\Select::make('new_employee_service_grade_id')
                            ->label('MKG Baru (Masa Kerja)')
                            ->relationship('newServiceGrade', 'service_grade')
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih Masa Kerja Golongan (MKG) baru setelah kenaikan.'),

                        Forms\Components\Textarea::make('notes')
                            ->maxLength(500),
                        Forms\Components\Hidden::make('users_id')
                            ->default(fn() => auth()->id() ?? 0),
                    ]),
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
                Tables\Columns\TextColumn::make('is_applied')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Realisasi' : 'Usulan'),
                Tables\Columns\TextColumn::make('previous_basic_salary')
                    ->label('Gaji Pokok Sebelumnya')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('new_basic_salary')
                    ->label('Gaji Pokok Baru')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('increase_amount')
                    ->label('Jumlah Kenaikan')
                    ->money('IDR')
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('increase_percentage')
                    ->label('Persentase Kenaikan')
                    ->suffix('%')
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('effective_date')
                    ->label('Tanggal Efektif')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employees_id')
                    ->label('Pegawai')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('effective_date')
                    ->label('Tanggal Efektif')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('effective_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('effective_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\Action::make('terapkan_kgb')
                        ->label('Terapkan KGB')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\DatePicker::make('applied_date')
                                ->label('Tanggal Realisasi')
                                ->required()
                                ->default(now()),
                            Forms\Components\TextInput::make('notes')
                                ->label('Keterangan Tambahan'),
                        ])
                        ->action(function ($record, array $data) {
                            if ($record->employee) {
                                // Update record
                                $record->update([
                                    'is_applied' => true,
                                    'applied_at' => now(),
                                    'applied_by' => auth()->id(),
                                    'notes' => ($record->notes ? $record->notes . "\n" : "") . "Direalisasikan pada " . $data['applied_date'],
                                ]);

                                // Update Employee Profile
                                $record->employee->update([
                                    'periodic_salary_date_start' => $data['applied_date'],
                                    'employee_service_grade_id' => $record->new_employee_service_grade_id ?? $record->employee->employee_service_grade_id,
                                    'basic_salary' => $record->new_basic_salary, // Update salary field if it exists
                                ]);

                                Notification::make()
                                    ->title('KGB Berhasil Direalisasikan')
                                    ->body('Data Pegawai ' . $record->employee->name . ' telah diperbarui.')
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn ($record) => !$record->is_applied),

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
            'index' => Pages\ListEmployeePeriodicSalaryIncreases::route('/'),
            'create' => Pages\CreateEmployeePeriodicSalaryIncrease::route('/create'),
            'edit' => Pages\EditEmployeePeriodicSalaryIncrease::route('/{record}/edit'),
        ];
    }
}
