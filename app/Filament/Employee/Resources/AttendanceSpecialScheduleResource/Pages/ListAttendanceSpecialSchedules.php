<?php

namespace App\Filament\Employee\Resources\AttendanceSpecialScheduleResource\Pages;

use App\Filament\Employee\Resources\AttendanceSpecialScheduleResource;
use App\Models\AttendanceSpecialSchedule;
use App\Models\Employee;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Http;

class ListAttendanceSpecialSchedules extends ListRecords
{
    protected static string $resource = AttendanceSpecialScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import_libur_nasional')
                ->label('Import Libur Nasional')
                ->icon('heroicon-o-calendar')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('year')
                        ->label('Tahun')
                        ->options([
                            '2026' => '2026',
                            '2027' => '2027',
                            '2028' => '2028',
                        ])
                        ->default(date('Y'))
                        ->required(),
                    Forms\Components\Select::make('employees')
                        ->label('Pegawai')
                        ->helperText('Pilih pegawai yang akan di-apply jadwal libur nasional. Kosongkan untuk apply ke semua pegawai aktif.')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(Employee::query()->pluck('name', 'id'))
                        ->placeholder('Semua Pegawai Aktif'),
                    Forms\Components\Toggle::make('skip_existing')
                        ->label('Lewati Jika Sudah Ada')
                        ->helperText('Aktifkan untuk tidak menimpa jadwal khusus yang sudah ada pada tanggal yang sama.')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    $year = $data['year'];

                    // Fetch data dari API
                    try {
                        $response = Http::timeout(10)->get("https://libur.deno.dev/api", [
                            'year' => $year
                        ]);

                        if (!$response->successful()) {
                            Notification::make()
                                ->title('Gagal Mengambil Data')
                                ->body('Tidak dapat mengambil data libur nasional dari API.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $holidays = $response->json();

                        if (empty($holidays)) {
                            Notification::make()
                                ->title('Data Kosong')
                                ->body("Tidak ada data libur nasional untuk tahun {$year}.")
                                ->warning()
                                ->send();
                            return;
                        }

                        // Tentukan pegawai yang akan di-apply
                        $employeeIds = empty($data['employees'])
                            ? Employee::pluck('id')->toArray()
                            : $data['employees'];

                        if (empty($employeeIds)) {
                            Notification::make()
                                ->title('Tidak Ada Pegawai')
                                ->body('Tidak ada data pegawai di database. Silakan tambahkan pegawai terlebih dahulu.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $created = 0;
                        $skipped = 0;

                        // Kelompokkan libur berdasarkan nama untuk deteksi cuti bersama
                        $holidayGroups = [];
                        foreach ($holidays as $holiday) {
                            $holidayGroups[$holiday['name']][] = $holiday['date'];
                        }

                        // Definisi libur nasional resmi (hari pertama dari setiap libur)
                        // Berdasarkan SKB 3 Menteri tentang hari libur nasional dan cuti bersama
                        $liburNasionalKeywords = [
                            'Tahun Baru' => 1,  // 1 hari
                            'Imlek' => 1,       // 1 hari (hari pertama)
                            'Nyepi' => 1,       // 1 hari
                            'Isra' => 1,        // 1 hari
                            'Wafat' => 1,       // 1 hari
                            'Paskah' => 1,      // 1 hari (Jumat Agung)
                            'Buruh' => 1,       // 1 hari
                            'Kenaikan' => 1,    // 1 hari
                            'Waisak' => 1,      // 1 hari
                            'Pancasila' => 1,   // 1 hari
                            'Idul Fitri' => 2,  // 2 hari resmi
                            'Idul Adha' => 1,   // 1 hari
                            'Muharam' => 1,     // 1 hari
                            'Kemerdekaan' => 1, // 1 hari
                            'Maulid' => 1,      // 1 hari
                            'Natal' => 1,       // 1 hari
                        ];

                        foreach ($holidays as $index => $holiday) {
                            $holidayName = $holiday['name'];
                            $holidayDate = $holiday['date'];

                            // Default: libur nasional
                            $type = 'libur_nasional';

                            // Cek berapa hari libur nasional resmi untuk nama ini
                            $officialDays = 1; // default
                            foreach ($liburNasionalKeywords as $keyword => $days) {
                                if (stripos($holidayName, $keyword) !== false) {
                                    $officialDays = $days;
                                    break;
                                }
                            }

                            // Jika ada lebih banyak hari dari yang resmi, sisanya adalah cuti bersama
                            if (count($holidayGroups[$holidayName]) > $officialDays) {
                                $dateIndex = array_search($holidayDate, $holidayGroups[$holidayName]);
                                if ($dateIndex >= $officialDays) {
                                    $type = 'cuti_bersama';
                                }
                            }

                            $description = ($type === 'cuti_bersama' ? 'Cuti Bersama: ' : 'Libur Nasional: ') . $holidayName;

                            foreach ($employeeIds as $employeeId) {
                                // Check jika sudah ada
                                if ($data['skip_existing']) {
                                    $exists = AttendanceSpecialSchedule::where('employee_id', $employeeId)
                                        ->whereDate('date', $holiday['date'])
                                        ->exists();

                                    if ($exists) {
                                        $skipped++;
                                        continue;
                                    }
                                }

                                AttendanceSpecialSchedule::updateOrCreate(
                                    [
                                        'employee_id' => $employeeId,
                                        'date' => $holiday['date'],
                                    ],
                                    [
                                        'is_working' => false, // Libur
                                        'type' => $type,
                                        'description' => $description,
                                        'users_id' => auth()->id(),
                                    ]
                                );

                                $created++;
                            }
                        }

                        Notification::make()
                            ->title('Import Berhasil')
                            ->body("Berhasil mengimport {$created} jadwal libur nasional untuk " . count($employeeIds) . " pegawai. {$skipped} data dilewati.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Terjadi Kesalahan')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->modalWidth('md')
                ->requiresConfirmation()
                ->modalDescription('Import data libur nasional Indonesia dari API untuk diterapkan ke jadwal khusus pegawai.'),
            Actions\CreateAction::make(),
        ];
    }
}
