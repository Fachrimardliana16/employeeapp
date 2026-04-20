<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\MyAttendanceResource\Pages;
use App\Models\AttendanceMachineLog;
use App\Models\AttendanceSchedule;
use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyAttendanceResource extends Resource
{
    protected static ?string $model = AttendanceMachineLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Presensi & Laporan';

    protected static ?string $navigationLabel = 'Riwayat Kehadiran';

    protected static ?string $modelLabel = 'Riwayat Kehadiran';

    protected static ?string $pluralModelLabel = 'Riwayat Kehadiran';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee || !$employee->pin) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->with(['machine.officeLocation'])
            ->where('pin', $employee->pin);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        $dayMap = [
            'monday' => 'SENIN', 'tuesday' => 'SELASA', 'wednesday' => 'RABU',
            'thursday' => 'KAMIS', 'friday' => 'JUMAT', 'saturday' => 'SABTU', 'sunday' => 'MINGGU',
        ];

        // Cache schedules for performance
        $schedules = AttendanceSchedule::where('is_active', true)->get()->groupBy(fn($item) => strtolower($item->day));

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('timestamp')
                    ->label('Hari & Tanggal')
                    ->formatStateUsing(function ($state) use ($dayMap) {
                        $date = Carbon::parse($state);
                        $day = $dayMap[strtolower($date->format('l'))] ?? $date->format('l');
                        return "{$day}, " . $date->format('d/m/Y');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('jam')
                    ->label('Jam')
                    ->state(fn ($record) => $record->timestamp->format('H:i:s'))
                    ->fontFamily('mono')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe Log')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '0', '3', '4' => 'success', // In-like
                        '1', '2', '5' => 'danger',  // Out-like
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '0' => 'MASUK',
                        '1' => 'KELUAR',
                        '2' => 'ISTIRAHAT KELUAR',
                        '3' => 'ISTIRAHAT MASUK',
                        '4' => 'LEMBUR MASUK',
                        '5' => 'LEMBUR KELUAR',
                        default => "TIPE $state",
                    }),

                Tables\Columns\TextColumn::make('status_performa')
                    ->label('Status')
                    ->state(function ($record) use ($schedules, $dayMap) {
                        $date = $record->timestamp;
                        $dayEng = strtolower($date->format('l'));
                        $dayInd = $dayMap[$dayEng] ?? $dayEng;
                        $schedule = $schedules->get(strtolower($dayInd))?->first();

                        if (!$schedule) return '-';
                        $time = $date->format('H:i:s');

                        // MASUK
                        if (in_array((string)$record->type, ['0', '3', '4'])) {
                            $limit = $schedule->late_threshold ?: $schedule->check_in_end;
                            return ($limit && $time > $limit) ? 'TERLAMBAT' : 'TEPAT WAKTU';
                        }
                        // KELUAR
                        if ((string)$record->type === '1') {
                            return ($schedule->check_out_start && $time < $schedule->check_out_start) ? 'PULANG CEPAT' : 'TEPAT WAKTU';
                        }
                        return '-';
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'TEPAT WAKTU' => 'success',
                        'TERLAMBAT' => 'danger',
                        'PULANG CEPAT' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('is_duplicate')
                    ->label('Validasi')
                    ->state(function ($record) {
                        $dateStr = $record->timestamp->toDateString();
                        $pin = $record->pin;
                        $type = $record->type;

                        // Query to find if this is the "Primary" record for that day/pin/type
                        $logs = AttendanceMachineLog::whereDate('timestamp', $dateStr)
                            ->where('pin', $pin)
                            ->where('type', $type)
                            ->orderBy('timestamp', 'asc')
                            ->get();

                        if ($logs->count() <= 1) return 'ASLI';

                        if (in_array((string)$type, ['0', '3', '4'])) {
                            $primaryId = $logs->first()->id;
                        } elseif ((string)$type === '1') {
                            $primaryId = $logs->last()->id;
                        } else {
                            $primaryId = $logs->first()->id;
                        }

                        return ($record->id === $primaryId) ? 'ASLI' : 'DUPLIKAT';
                    })
                    ->badge()
                    ->color(fn ($state) => $state === 'ASLI' ? 'emerald' : 'gray'),

                Tables\Columns\TextColumn::make('machine.name')
                    ->label('Lokasi / Mesin')
                    ->description(fn ($record) => $record->machine?->officeLocation?->name ?? '-'),
            ])
            ->defaultSort('timestamp', 'desc')
            ->filters([
                Tables\Filters\Filter::make('timestamp')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('to')->label('Hingga Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('timestamp', '>=', $date))
                            ->when($data['to'], fn ($q, $date) => $q->whereDate('timestamp', '<=', $date));
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMyAttendances::route('/'),
        ];
    }
}
