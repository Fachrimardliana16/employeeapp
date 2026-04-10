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
                        Forms\Components\Hidden::make('latitude')
                            ->required(),

                        Forms\Components\Hidden::make('longitude')
                            ->required(),

                        Forms\Components\Select::make('state')
                            ->label('Status Kehadiran')
                            ->options([
                                'in'            => 'Check In ',
                                'out'           => 'Check Out',
                                'ot_in'         => 'Overtime In',
                                'ot_out'    => 'Overtime Out',
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

    public function recordAttendance(): void
    {
        $data = $this->form->getState();

        $user = Auth::user();
        
        // Try to find employee by users_id first, then by email or office_email
        $employee = Employee::where('users_id', $user->id)
            ->orWhere('email', $user->email)
            ->orWhere('office_email', $user->email)
            ->first();

        if (!$employee) {
            Notification::make()
                ->title('Gagal')
                ->body('Data pegawai tidak ditemukan untuk akun: ' . $user->email)
                ->danger()
                ->send();
            return;
        }

        // 1. Get Today's Schedule
        $dayName = now()->format('l');
        $schedule = AttendanceSchedule::where('day', $dayName)->where('is_active', true)->first();
        
        if (!$schedule) {
            Notification::make()
                ->title('Jadwal Tidak Ditemukan')
                ->body('Tidak ada jadwal aktif untuk hari ini (' . $dayName . ').')
                ->warning()
                ->send();
            return;
        }

        // 2. Validate Time Window
        $currentTime = now()->format('H:i:s');
        if ($data['state'] === 'in' && ($currentTime < $schedule->check_in_start || $currentTime > $schedule->check_in_end)) {
             Notification::make()
                ->title('Di Luar Jam Check-In')
                ->body('Waktu Check-In hari ini adalah ' . $schedule->check_in_start . ' - ' . $schedule->check_in_end)
                ->warning()
                ->send();
            return;
        }

        if ($data['state'] === 'out' && ($currentTime < $schedule->check_out_start || $currentTime > $schedule->check_out_end)) {
             Notification::make()
                ->title('Di Luar Jam Check-Out')
                ->body('Waktu Check-Out hari ini adalah ' . $schedule->check_out_start . ' - ' . $schedule->check_out_end)
                ->warning()
                ->send();
            return;
        }

        // 3. Find Office Locations - Specific Department OR Global (NULL)
        $officeLocations = MasterOfficeLocation::where(function($query) use ($employee) {
                $query->where('departments_id', $employee->departments_id)
                      ->orWhereNull('departments_id');
            })
            ->where('is_active', true)
            ->get();

        if ($officeLocations->isEmpty()) {
            Notification::make()
                ->title('Konfigurasi Lokasi Error')
                ->body('Lokasi kantor untuk departemen Anda belum diatur. Hubungi Admin.')
                ->danger()
                ->send();
            return;
        }

        // 4. Validate Location - Check if employee is in ANY allowed office radius
        if (empty($data['latitude']) || empty($data['longitude'])) {
            Notification::make()
                ->title('Lokasi Diperlukan')
                ->body('Mohon aktifkan GPS dan izinkan akses lokasi.')
                ->warning()
                ->send();
            return;
        }

        $isInRange = false;
        $closestLocation = null;
        $minDistance = null;

        foreach ($officeLocations as $location) {
            $distance = EmployeeAttendanceRecord::calculateDistance(
                $data['latitude'],
                $data['longitude'],
                $location->latitude,
                $location->longitude
            );

            if ($minDistance === null || $distance < $minDistance) {
                $minDistance = $distance;
                $closestLocation = $location;
            }

            if ($distance <= $location->radius) {
                $isInRange = true;
                $officeLocation = $location; // Save the specific office they are at
                break;
            }
        }

        if (!$isInRange) {
            Notification::make()
                ->title('Lokasi Terlalu Jauh')
                ->body(sprintf('Anda berada %.2f meter dari %s. Maksimal jarak %d meter.', $minDistance, $closestLocation->name, $closestLocation->radius))
                ->warning()
                ->send();
            return;
        }

        // Use the distance from the specific matched location for the record
        $distance = $minDistance;

        // Process picture if it's base64 from CameraCapture
        $picturePath = null;
        if (!empty($data['picture']) && str_starts_with($data['picture'], 'data:image')) {
            $picturePath = $this->processAndStoreImage($data['picture']);
        }

        // Create attendance record
        EmployeeAttendanceRecord::create([
            'pin' => $employee->pin ?? $employee->id,
            'employee_name' => $employee->name,
            'attendance_time' => now(),
            'state' => $data['state'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'distance_meters' => $distance,
            'picture' => $picturePath,
            'device' => 'web',
            'users_id' => $user->id,
        ]);

        Notification::make()
            ->title('Berhasil')
            ->body('Kehadiran berhasil direkam.')
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

        // Generate unique filename
        $filename = 'attendance_' . time() . '_' . uniqid() . '.jpg';
        $directory = storage_path('app/public/attendance-photos');
        
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . '/' . $filename;
        
        // Save as JPEG with 70% quality (optimization)
        imagejpeg($img, $path, 70);
        imagedestroy($img);

        return 'attendance-photos/' . $filename;
    }
}
