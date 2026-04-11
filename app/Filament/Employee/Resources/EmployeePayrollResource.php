<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeePayrollResource\Pages;
use App\Filament\Employee\Resources\EmployeePayrollResource\RelationManagers;
use App\Models\EmployeePayroll;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeePayrollResource extends Resource
{
    protected static ?string $model = EmployeePayroll::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Kompensasi & Tunjangan';
    protected static ?string $navigationLabel = 'Proses Payroll';
    protected static ?string $modelLabel = 'Proses Payroll';
    protected static ?string $pluralModelLabel = 'Proses Payroll';
    protected static ?int $navigationSort = 402;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->label('Pegawai')
                    ->relationship('employee', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('payroll_period')
                    ->label('Periode Payroll')
                    ->required(),
                Forms\Components\TextInput::make('base_salary')
                    ->label('Gaji Dasar')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('total_allowance')
                    ->label('Total Tunjangan')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('total_deduction')
                    ->label('Total Potongan')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('total_bonus')
                    ->label('Total Bonus')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('gross_salary')
                    ->label('Gaji Kotor')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('net_salary')
                    ->label('Gaji Bersih')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('work_days')
                    ->label('Hari Kerja')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('present_days')
                    ->label('Hari Kehadiran')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('late_count')
                    ->label('Jumlah Terlambat')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('absent_count')
                    ->label('Jumlah Alpa')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('overtime_hours')
                    ->label('Jam Lembur')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('payment_status')
                    ->label('Status Pembayaran')
                    ->required(),
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Tanggal Pembayaran'),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payroll_period')
                    ->label('Periode')
                    ->date('M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_salary')
                    ->label('Gaji Dasar')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_allowance')
                    ->label('Total Tunjangan')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_deduction')
                    ->label('Total Potongan')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_bonus')
                    ->label('Total Bonus')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('gross_salary')
                    ->label('Gaji Kotor')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('net_salary')
                    ->label('Gaji Bersih')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_days')
                    ->label('Hari Kerja')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('present_days')
                    ->label('Hari Kehadiran')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('late_count')
                    ->label('Jumlah Terlambat')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('absent_count')
                    ->label('Jumlah Alpa')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('overtime_hours')
                    ->label('Jam Lembur')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'approved' => 'info',
                        'calculated' => 'warning',
                        'draft' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'paid' => 'Dibayar',
                        'approved' => 'Disetujui',
                        'calculated' => 'Terhitung',
                        'draft' => 'Draft',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tanggal Pembayaran')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Disetujui Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Pegawai')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'draft' => 'Draft',
                        'calculated' => 'Terhitung',
                        'approved' => 'Disetujui',
                        'paid' => 'Dibayar',
                    ]),
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
            'index' => Pages\ListEmployeePayrolls::route('/'),
            'create' => Pages\CreateEmployeePayroll::route('/create'),
            'edit' => Pages\EditEmployeePayroll::route('/{record}/edit'),
        ];
    }
}
