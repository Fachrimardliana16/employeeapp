<?php

namespace App\Filament\User\Pages;

use App\Models\EmployeeAttendanceRecord;
use App\Models\Employee;
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

    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    // Koordinat kantor (ganti dengan koordinat kantor Anda)
    const OFFICE_LATITUDE = -6.200000;
    const OFFICE_LONGITUDE = 106.816666;
    const MAX_DISTANCE_METERS = 100; // 100 meter

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

                        Forms\Components\FileUpload::make('picture')
                            ->label('Foto (Opsional)')
                            ->image()
                            ->directory('attendance-photos')
                            ->imageEditor()
                            ->helperText('Anda dapat mengambil foto sebagai bukti kehadiran'),
                    ])
            ])
            ->statePath('data');
    }

    public function recordAttendance(): void
    {
        $data = $this->form->getState();

        $user = Auth::user();
        $employee = Employee::where('email', $user->email)->first();

        if (!$employee) {
            Notification::make()
                ->title('Gagal')
                ->body('Data pegawai tidak ditemukan.')
                ->danger()
                ->send();
            return;
        }

        // Validate location if provided
        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            $distance = EmployeeAttendanceRecord::calculateDistance(
                $data['latitude'],
                $data['longitude'],
                self::OFFICE_LATITUDE,
                self::OFFICE_LONGITUDE
            );

            if ($distance > self::MAX_DISTANCE_METERS) {
                Notification::make()
                    ->title('Lokasi Terlalu Jauh')
                    ->body(sprintf('Anda berada %.2f meter dari kantor. Maksimal jarak %d meter.', $distance, self::MAX_DISTANCE_METERS))
                    ->warning()
                    ->send();
                return;
            }
        } else {
            Notification::make()
                ->title('Lokasi Diperlukan')
                ->body('Mohon aktifkan GPS dan izinkan akses lokasi.')
                ->warning()
                ->send();
            return;
        }

        // Calculate distance
        $distance = EmployeeAttendanceRecord::calculateDistance(
            $data['latitude'],
            $data['longitude'],
            self::OFFICE_LATITUDE,
            self::OFFICE_LONGITUDE
        );

        // Create attendance record
        EmployeeAttendanceRecord::create([
            'pin' => $employee->pin ?? $employee->id,
            'employee_name' => $employee->name,
            'attendance_time' => now(),
            'state' => $data['state'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'distance_meters' => $distance,
            'picture' => $data['picture'] ?? null,
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
}
