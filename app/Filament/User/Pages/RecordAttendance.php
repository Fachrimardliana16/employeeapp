<?php

namespace App\Filament\User\Pages;

use App\Models\EmployeeAttendanceRecord;
use App\Models\Employee;
use App\Filament\Forms\Components\CameraCapture;
use App\Models\MasterOfficeLocation;
use App\Models\AttendanceSchedule;
use App\Models\EmployeeDailyReport;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class RecordAttendance extends Page implements HasActions
{
    use InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static string $view = 'filament.user.pages.record-attendance';

    protected static ?string $navigationLabel = 'Kehadiran';

    protected static ?string $title = 'Rekam Kehadiran';

    protected static ?string $navigationGroup = 'Utama';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kehadiran')
                    ->schema([
                        Forms\Components\Hidden::make('latitude')
                            ->required(),

                        Forms\Components\Hidden::make('longitude')
                            ->required(),

                        Forms\Components\Select::make('state')
                            ->label('Status Kehadiran')
                            ->options([
                                'in'            => 'Check In ',
                                'out'           => 'Check Out',
                                'dl_in'         => 'Dinas Luar (Masuk)',
                                'dl_out'        => 'Dinas Luar (Pulang)',
                                'ot_in'         => 'Overtime In',
                                'ot_out'        => 'Overtime Out',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Placeholder::make('location_info')
                            ->label('Informasi Lokasi')
                            ->content('Lokasi Anda akan dideteksi otomatis saat merekam kehadiran.'),

                        CameraCapture::make('picture')
                            ->label('Foto Kehadiran')
                            ->required()
                            ->helperText('Ambil foto selfie Anda sebagai bukti kehadiran'),
                    ])
            ])
            ->statePath('data');
    }

    public function recordAttendanceAction(): Action
    {
        return Action::make('recordAttendanceAction')
            ->label('Simpan Kehadiran')
            ->modalHeading('Laporan Kerja Harian')
            ->modalDescription('Silakan isi laporan pekerjaan Anda sebelum menyimpan kehadiran.')
            ->modalSubmitActionLabel('Simpan & Absen')
            ->modalIcon('heroicon-o-document-text')
            ->form([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('daily_report_date')
                            ->label('Tanggal Laporan')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->readonly(),
                        Forms\Components\Select::make('work_status')
                            ->label('Status Pekerjaan')
                            ->options([
                                'completed' => 'Selesai',
                                'in_progress' => 'Proses',
                                'pending' => 'Tertunda',
                            ])
                            ->required()
                            ->default('completed'),
                    ]),
                Forms\Components\Textarea::make('work_description')
                    ->label('Isi Laporan Kerja')
                    ->placeholder('Apa yang Anda kerjakan atau capai hari ini?')
                    ->required()
                    ->rows(5),
                Forms\Components\Textarea::make('desc')
                    ->label('Catatan/Keterangan (Opsional)')
                    ->placeholder('Tambahan detail jika diperlukan...')
                    ->rows(3),
            ])
            ->action(function (array $data) {
                $this->saveAttendanceWithReport($data);
            });
    }

    public function submitAttendance(): void
    {
        $attendanceData = $this->form->getState();

        if (($attendanceData['state'] ?? '') === 'out') {
            $this->mountAction('recordAttendanceAction');
        } else {
            $this->saveAttendanceWithReport();
        }
    }

    protected function saveAttendanceWithReport(?array $reportData = null): void
    {
        // 1. Validate Attendance Form State
        $attendanceData = $this->form->getState();

        $user = Auth::user();
        
        // Cek berdasarkan users_id terlebih dahulu (canonical link)
        $employee = Employee::where('users_id', $user->id)->first();
        
        // Fallback ke email atau username jika link users_id belum diatur
        if (!$employee) {
            $employee = Employee::where(function($query) use ($user) {
                $query->where('email', $user->email)
                    ->orWhere('office_email', $user->email)
                    ->orWhere('username', $user->username);
            })->first();
            
            // Link secara otomatis jika ditemukan (self-healing)
            if ($employee && empty($employee->users_id)) {
                $employee->update(['users_id' => $user->id]);
            }
        }

        if (!$employee) {
            Notification::make()
                ->title('Gagal')
                ->body('Data pegawai tidak ditemukan.')
                ->danger()
                ->send();
            return;
        }

        // 2. Schedule Validation & Status Calculation
        $daysMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];
        
        $dayName = $daysMap[now()->format('l')];
        $schedule = AttendanceSchedule::where('day', $dayName)->where('is_active', true)->first();
        
        // 2.1 Duplicate Check
        $exists = EmployeeAttendanceRecord::where('pin', $employee->pin ?? $employee->id)
            ->where('state', $attendanceData['state'])
            ->whereDate('attendance_time', now())
            ->exists();

        if ($exists) {
            $stateLabel = match($attendanceData['state']) {
                'in' => 'Check In',
                'out' => 'Check Out',
                'ot_in' => 'Overtime In',
                'ot_out' => 'Overtime Out',
                default => $attendanceData['state'],
            };
            
            Notification::make()
                ->title('Gagal')
                ->body("Anda sudah melakukan {$stateLabel} hari ini.")
                ->warning()
                ->send();
            return;
        }

        $attendanceStatus = 'on_time';
        $currentTime = now()->format('H:i:s');

        if (!$schedule) {
            // Default to on_time if no schedule found
        } else {
            if ($attendanceData['state'] === 'in') {
                if ($currentTime < $schedule->check_in_start || $currentTime > $schedule->check_in_end) {
                    Notification::make()->title('Di Luar Jam Check-In')->warning()->send();
                    return;
                }
                if ($currentTime > $schedule->late_threshold) {
                    $attendanceStatus = 'late';
                }
            }
            if ($attendanceData['state'] === 'out') {
                if ($currentTime > $schedule->check_out_end) {
                     Notification::make()->title('Di Luar Jam Check-Out')->warning()->send();
                    return;
                }
                if ($currentTime < $schedule->check_out_start) {
                    $attendanceStatus = 'early';
                }
            }
        }

        // 3. Office & Location Validation
        $officeLocations = MasterOfficeLocation::where(function($query) use ($employee) {
                $query->where('departments_id', $employee->departments_id)->orWhereNull('departments_id');
            })->where('is_active', true)->get();

        if ($officeLocations->isEmpty() || empty($attendanceData['latitude'])) {
            Notification::make()->title('Lokasi Error')->danger()->send();
            return;
        }

        $isInRange = false;
        $minDistance = null;
        $closestLocation = null;

        foreach ($officeLocations as $location) {
            $distance = EmployeeAttendanceRecord::calculateDistance(
                $attendanceData['latitude'],
                $attendanceData['longitude'],
                $location->latitude,
                $location->longitude
            );
            if ($minDistance === null || $distance < $minDistance) {
                $minDistance = $distance;
                $closestLocation = $location;
            }
            if ($distance <= $location->radius) {
                $isInRange = true;
                break;
            }
        }

        // 3.1 Bypass Radius for Dinas Luar (LUAR KANTOR)
        if (str_starts_with($attendanceData['state'], 'dl_')) {
            $isInRange = true;
        }

        if (!$isInRange) {
            Notification::make()
                ->title('Lokasi Terlalu Jauh')
                ->body(sprintf('Jarak %.2fm. Maks %dm.', $minDistance, $closestLocation->radius))
                ->warning()
                ->send();
            return;
        }

        // 4. Process Image
        $picturePath = null;
        if (!empty($attendanceData['picture']) && str_starts_with($attendanceData['picture'], 'data:image')) {
            $picturePath = $this->processAndStoreImage($attendanceData['picture']);
        }

        // 5. START SAVING - Use Database Transaction to ensure both or none
        \Illuminate\Support\Facades\DB::transaction(function () use ($employee, $attendanceData, $reportData, $minDistance, $picturePath, $user, $closestLocation, $isInRange, $attendanceStatus) {
            // Save Daily Report ONLY if data is provided (for 'out' state)
            if ($reportData) {
                EmployeeDailyReport::create([
                    'employee_id' => $employee->id,
                    'daily_report_date' => $reportData['daily_report_date'],
                    'work_description' => $reportData['work_description'],
                    'work_status' => $reportData['work_status'],
                    'desc' => $reportData['desc'],
                    'users_id' => $user->id,
                ]);
            }

            // Save Attendance Record
            EmployeeAttendanceRecord::create([
                'pin' => $employee->pin ?? $employee->id,
                'employee_name' => $employee->name,
                'attendance_time' => now(),
                'state' => $attendanceData['state'],
                
                // Fields untuk kompatibilitas (Lama)
                'latitude' => $attendanceData['latitude'],
                'longitude' => $attendanceData['longitude'],
                'distance_meters' => $minDistance,
                'picture' => $picturePath,

                // Fields Baru (Sesuai Migrasi & Resource)
                'office_location_id' => $closestLocation?->id,
                'check_latitude' => $attendanceData['latitude'],
                'check_longitude' => $attendanceData['longitude'],
                'distance_from_office' => (int) round($minDistance),
                'is_within_radius' => $isInRange,
                'photo_checkin' => in_array($attendanceData['state'], ['in', 'ot_in', 'dl_in']) ? $picturePath : null,
                'photo_checkout' => in_array($attendanceData['state'], ['out', 'ot_out', 'dl_out']) ? $picturePath : null,
                'attendance_status' => $attendanceStatus,
                'users_id' => $user->id,
                'device' => 'web',
            ]);
        });

        $statusLabel = match($attendanceStatus) {
            'late' => ' (Terlambat)',
            'early' => ' (Terlalu Cepat)',
            default => ' (Tepat Waktu)',
        };

        Notification::make()
            ->title('Berhasil Simpan Kehadiran' . $statusLabel)
            ->success()
            ->send();

        $this->form->fill();
    }

    /**
     * Decode base64 image, resize, compress and store it
     */
    protected function processAndStoreImage(string $base64Data): string
    {
        $data = explode(',', $base64Data);
        $decodedImage = base64_decode($data[1]);
        
        $img = imagecreatefromstring($decodedImage);
        if (!$img) {
            throw new \Exception('Gagal memproses gambar.');
        }

        // Get original dimensions
        $width = imagesx($img);
        $height = imagesy($img);

        // Max dimension 1024px
        $maxDim = 1024;
        if ($width > $maxDim || $height > $maxDim) {
            $ratio = $width / $height;
            if ($ratio > 1) {
                $newWidth = $maxDim;
                $newHeight = $maxDim / $ratio;
            } else {
                $newHeight = $maxDim;
                $newWidth = $maxDim * $ratio;
            }
            $img = imagescale($img, $newWidth, $newHeight);
        }

        // Generate unique filename with .webp extension
        $filename = 'attendance_' . time() . '_' . uniqid() . '.webp';
        $directory = storage_path('app/public/attendance-photos');
        
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . '/' . $filename;
        
        // Save as WebP with 75% quality (better optimization)
        if (function_exists('imagewebp')) {
            imagewebp($img, $path, 75);
        } else {
            // Fallback to JPEG if WebP not supported
            $filename = str_replace('.webp', '.jpg', $filename);
            $path = str_replace('.webp', '.jpg', $path);
            imagejpeg($img, $path, 75);
        }
        
        imagedestroy($img);

        return 'attendance-photos/' . $filename;
    }
}
