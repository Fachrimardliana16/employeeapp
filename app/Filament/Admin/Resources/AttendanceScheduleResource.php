<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AttendanceScheduleResource\Pages;
use App\Models\AttendanceSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceScheduleResource extends Resource
{
    protected static ?string $model = AttendanceSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Pengaturan Sistem';

    protected static ?string $navigationLabel = 'Jadwal Kehadiran';

    protected static ?string $title = 'Jadwal Kehadiran';

    public static function getModelLabel(): string
    {
        return 'Jadwal Kehadiran';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Jadwal Kehadiran';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ketentuan Waktu')
                    ->schema([
                        Forms\Components\TextInput::make('day')
                            ->label('Hari')
                            ->disabled()
                            ->required(),
                        
                        Forms\Components\TimePicker::make('late_threshold')
                            ->label('Batas Telat')
                            ->required()
                            ->seconds(true)
                            ->helperText('Waktu dimana karyawan mulai dianggap terlambat.'),

                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TimePicker::make('check_in_start')
                                    ->label('Mulai Check-In')
                                    ->required(),
                                Forms\Components\TimePicker::make('check_in_end')
                                    ->label('Selesai Check-In')
                                    ->required(),
                                Forms\Components\TimePicker::make('check_out_start')
                                    ->label('Mulai Check-Out')
                                    ->required(),
                                Forms\Components\TimePicker::make('check_out_end')
                                    ->label('Selesai Check-Out')
                                    ->required(),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Status')
                    ->schema([
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
                Tables\Columns\TextColumn::make('day')
                    ->label('Hari')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('late_threshold')
                    ->label('Batas Telat')
                    ->time('H:i:s')
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('check_in_start')
                    ->label('Check-In')
                    ->formatStateUsing(fn ($record) => $record->check_in_start . ' - ' . $record->check_in_end),
                Tables\Columns\TextColumn::make('check_out_start')
                    ->label('Check-Out')
                    ->formatStateUsing(fn ($record) => $record->check_out_start . ' - ' . $record->check_out_end),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),
                    Tables\Actions\EditAction::make()->label('Edit'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendanceSchedules::route('/'),
            'edit' => Pages\EditAttendanceSchedule::route('/{record}/edit'),
        ];
    }
}
