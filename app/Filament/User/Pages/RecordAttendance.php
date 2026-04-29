<?php

namespace App\Filament\User\Pages;

use App\Models\EmployeeAttendanceRecord;
use App\Models\Employee;
use App\Filament\Forms\Components\CameraCapture;
use App\Models\MasterOfficeLocation;
use App\Models\AttendanceSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;

class RecordAttendance extends Page
{

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
                        Forms\Components\Hidden::make('latitude'),
                        Forms\Components\Hidden::make('longitude'),
                        Forms\Components\Hidden::make('gps_accuracy'),
                        Forms\Components\Hidden::make('gps_jitter'),

                        Forms\Components\Select::make('state')
                            ->label('Status Kehadiran')
                            ->options(function () {
                                $employee = Auth::user()?->employee;
                                if (!$employee) {
                                    return [
                                        'check_in'  => 'Masuk Kerja (Check In)',
                                        'check_out' => 'Pulang Kerja (Check Out)',
                                    ];
                                }
                                $svc     = new AttendanceService();
                                $allowed = $svc->getAllowedStates($employee, now()->timezone('Asia/Jakarta'));
                                return collect($allowed)->pluck('label', 'value')->toArray();
                            })
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

    public function submitAttendance(): void
    {
        $this->saveAttendance();
    }

    protected function saveAttendance(): void
    {
        $attendanceData = $this->form->getState();
        $user     = Auth::user();
        $employee = Employee::where('users_id', $user->id)->first();

        if (!$employee) {
            $employee = Employee::where(function ($query) use ($user) {
                $query->where('email', $user->email)
                    ->orWhere('office_email', $user->email)
                    ->orWhere('username', $user->username);
            })->first();
            if ($employee && empty($employee->users_id)) {
                $employee->update(['users_id' => $user->id]);
            }
        }

        if (!$employee) {
            Notification::make()->title('Gagal')->body('Data pegawai tidak ditemukan.')->danger()->send();
            return;
        }

        // Cek duplikat hari ini
        $exists = EmployeeAttendanceRecord::where('pin', $employee->pin ?? $employee->id)
            ->where('state', $attendanceData['state'])
            ->whereDate('attendance_time', now())
            ->exists();

        if ($exists) {
            $stateLabel = match ($attendanceData['state']) {
                'check_in'  => 'Masuk Kerja',
                'check_out' => 'Pulang Kerja',
                'dl_in'     => 'Dinas Luar (Berangkat)',
                'dl_out'    => 'Dinas Luar (Kembali)',
                'ot_in'     => 'Lembur (Masuk)',
                'ot_out'    => 'Lembur (Pulang)',
                default     => $attendanceData['state'],
            };
            Notification::make()->title('Gagal')->body("Anda sudah melakukan {$stateLabel} hari ini.")->warning()->send();
            return;
        }

        // Ambil data GPS dari form
        $lat      = (float) ($attendanceData['latitude']     ?? 0);
        $lng      = (float) ($attendanceData['longitude']    ?? 0);
        $accuracy = (float) ($attendanceData['gps_accuracy'] ?? 999);
        $jitter   = (float) ($attendanceData['gps_jitter']   ?? 0);

        if (empty($attendanceData['latitude'])) {
            Notification::make()->title('Lokasi Belum Diambil')->body('Tunggu hingga GPS berhasil terdeteksi sebelum menyimpan.')->warning()->send();
            return;
        }

        $svc = new AttendanceService();

        // Cek teleport / speed anomaly
        $speed = $svc->checkSpeedAnomaly($employee, $lat, $lng);
        if ($speed['reject']) {
            Notification::make()->title('Absensi Ditolak')->body($speed['message'])->danger()->persistent()->send();
            return;
        }

        // Validasi GPS: accuracy + jitter + radius
        $gpsResult = $svc->validateGps($employee, $lat, $lng, $accuracy, $jitter);
        if (!$gpsResult['valid']) {
            Notification::make()->title('Lokasi Tidak Valid')->body($gpsResult['reason'])->danger()->persistent()->send();
            return;
        }

        $isFake     = $gpsResult['suspected_fake'] || (!empty($speed['message']));
        $flagReason = trim(collect([$gpsResult['reason'] ?: null, $speed['message'] ?: null])->filter()->implode('; '));
        $closestLocation = $gpsResult['location'];
        $minDistance     = $gpsResult['distance'];

        // Hitung status kehadiran
        $now = now()->timezone('Asia/Jakarta');
        $attendanceStatus = $svc->calculateAttendanceStatus($attendanceData['state'], $now);

        // Proses foto selfie
        $picturePath = null;
        if (!empty($attendanceData['picture']) && str_starts_with($attendanceData['picture'], 'data:image')) {
            $picturePath = $this->processAndStoreImage($attendanceData['picture']);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $employee,
            $attendanceData,
            $minDistance,
            $picturePath,
            $user,
            $closestLocation,
            $attendanceStatus,
            $accuracy,
            $jitter,
            $isFake,
            $flagReason,
            $lat,
            $lng,
            $now
        ) {
            EmployeeAttendanceRecord::create([
                'pin'                   => $employee->pin ?? $employee->id,
                'employee_name'         => $employee->name,
                'attendance_time'       => $now,
                'state'                 => $attendanceData['state'],
                'source'                => 'online',
                'device'                => 'Online (Web)',
                'check_latitude'        => $lat,
                'check_longitude'       => $lng,
                'gps_accuracy'          => $accuracy,
                'gps_jitter'            => $jitter,
                'is_fake_gps_suspected' => $isFake,
                'gps_flag_reason'       => $flagReason ?: null,
                'office_location_id'    => $closestLocation?->id,
                'distance_from_office'  => $minDistance ? (int) round($minDistance) : null,
                'is_within_radius'      => true,
                'photo_checkin'  => in_array($attendanceData['state'], ['check_in', 'ot_in', 'dl_in'])  ? $picturePath : null,
                'photo_checkout' => in_array($attendanceData['state'], ['check_out', 'ot_out', 'dl_out']) ? $picturePath : null,
                'attendance_status'     => $attendanceStatus,
                'users_id'              => $user->id,
            ]);
        });

        $statusLabel = match ($attendanceStatus) {
            'late'      => ' (Terlambat)',
            'early_out' => ' (Pulang Cepat)',
            default     => ' (Tepat Waktu)',
        };
        $extra = $isFake ? ' ⚠️ GPS terdeteksi mencurigakan.' : '';

        Notification::make()
            ->title('Berhasil Simpan Kehadiran' . $statusLabel)
            ->body($extra ?: null)
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
