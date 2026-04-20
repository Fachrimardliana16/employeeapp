<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\AttendanceSpecialScheduleResource\Pages;
use App\Models\AttendanceSpecialSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceSpecialScheduleResource extends Resource
{
    protected static ?string $model = AttendanceSpecialSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Absensi & Kehadiran';

    protected static ?string $navigationLabel = 'Jadwal Khusus';

    protected static ?string $modelLabel = 'Jadwal Khusus';

    protected static ?string $pluralModelLabel = 'Jadwal Khusus';

    protected static ?int $navigationSort = 502;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Jadwal Khusus')
                    ->description('Tentukan pengecualian jadwal untuk pegawai pada tanggal tertentu.')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Toggle::make('is_working')
                            ->label('Wajib Masuk')
                            ->helperText('Aktifkan jika pegawai wajib masuk di tanggal ini (misalnya lembur). Matikan jika pegawai libur (misalnya gilir libur).')
                            ->default(false),
                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->placeholder('Contoh: Gilir Libur Sabtu Minggu ke-2')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_working')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-briefcase')
                    ->falseIcon('heroicon-o-home')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($state) => $state ? 'Wajib Masuk' : 'Libur (Pengecualian)'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_working')
                    ->label('Wajib Masuk'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendanceSpecialSchedules::route('/'),
            'create' => Pages\CreateAttendanceSpecialSchedule::route('/create'),
            'edit' => Pages\EditAttendanceSpecialSchedule::route('/{record}/edit'),
        ];
    }
}
