<?php

namespace App\Filament\User\Resources\MyAttendanceResource\Pages;

use App\Filament\User\Resources\MyAttendanceResource;
use App\Models\EmployeeAttendanceRecord;
use App\Services\AttendanceService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMyAttendance extends CreateRecord
{
    protected static string $resource = MyAttendanceResource::class;

    protected static ?string $title = 'Absensi Online';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user     = Auth::user();
        $employee = $user?->employee;

        if (!$employee || !$employee->pin) {
            Notification::make()
                ->title('Gagal Absen')
                ->body('Data kepegawaian atau PIN tidak ditemukan. Hubungi administrator.')
                ->danger()
                ->send();
            $this->halt();
        }

        $lat      = (float) ($data['check_latitude']  ?? 0);
        $lng      = (float) ($data['check_longitude'] ?? 0);
        $accuracy = (float) ($data['gps_accuracy']    ?? 999);
        $jitter   = (float) ($data['gps_jitter']      ?? 0);

        // Wajib GPS sudah diambil
        if (empty($data['check_latitude']) || empty($data['check_longitude'])) {
            Notification::make()
                ->title('Lokasi GPS wajib diambil')
                ->body('Tekan tombol "Tangkap Lokasi GPS" dan tunggu hingga berhasil sebelum submit.')
                ->warning()
                ->persistent()
                ->send();
            $this->halt();
        }

        $svc = new AttendanceService();

        // Layer 4: Speed/teleport check dulu (sebelum proses GPS radius)
        $speed = $svc->checkSpeedAnomaly($employee, $lat, $lng);
        if ($speed['reject']) {
            Notification::make()
                ->title('Absensi Ditolak')
                ->body($speed['message'])
                ->danger()
                ->persistent()
                ->send();
            $this->halt();
        }

        // Layer 1-3: Validasi GPS (accuracy + jitter + radius)
        $gpsResult = $svc->validateGps($employee, $lat, $lng, $accuracy, $jitter);

        if (!$gpsResult['valid']) {
            Notification::make()
                ->title('Lokasi Tidak Valid')
                ->body($gpsResult['reason'])
                ->danger()
                ->persistent()
                ->send();
            $this->halt();
        }

        // Susun data record
        $now    = now()->timezone('Asia/Jakarta');
        $state  = $data['state'];

        $data['pin']               = $employee->pin;
        $data['employee_name']     = $employee->name;
        $data['attendance_time']   = $now;
        $data['source']            = 'online';
        $data['device']            = 'Online (Web)';
        $data['check_latitude']    = $lat;
        $data['check_longitude']   = $lng;
        $data['gps_accuracy']      = $accuracy;
        $data['gps_jitter']        = $jitter;
        $data['is_fake_gps_suspected'] = $gpsResult['suspected_fake'];
        $data['gps_flag_reason']   = $gpsResult['reason'];
        $data['distance_from_office'] = $gpsResult['distance'];
        $data['is_within_radius']  = true; // already validated above

        if ($gpsResult['location']) {
            $data['office_location_id'] = $gpsResult['location']->id;
        }

        // Speed flag (mencurigakan tapi tidak ditolak)
        if (!empty($speed['message'])) {
            $data['is_fake_gps_suspected'] = true;
            $data['gps_flag_reason'] = trim(($data['gps_flag_reason'] ? $data['gps_flag_reason'] . '; ' : '') . $speed['message']);
        }

        // Hitung attendance_status (terlambat / tepat waktu / pulang cepat)
        $data['attendance_status'] = $svc->calculateAttendanceStatus($state, $now);

        // users_id untuk tracking
        $data['users_id'] = $user->id;

        // Bersihkan field yang tidak ada di fillable
        unset($data['_']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $user   = Auth::user();

        $msg = match ($record->state) {
            'check_in'  => 'Absensi masuk kerja berhasil dicatat.',
            'check_out' => 'Absensi pulang kerja berhasil dicatat.',
            'dl_in'     => 'Keberangkatan dinas luar berhasil dicatat.',
            'dl_out'    => 'Kembali dari dinas luar berhasil dicatat.',
            'ot_in'     => 'Absensi masuk lembur berhasil dicatat.',
            'ot_out'    => 'Absensi pulang lembur berhasil dicatat.',
            default     => 'Absensi berhasil dicatat.',
        };

        $extra = '';
        if ($record->is_fake_gps_suspected) {
            $extra = ' ⚠️ Catatan: Lokasi GPS Anda terdeteksi mencurigakan dan akan ditinjau.';
        }
        if ($record->attendance_status === 'late') {
            $extra .= ' Anda tercatat terlambat.';
        }

        Notification::make()
            ->title('✅ ' . $msg)
            ->body(now()->format('H:i:s') . ' · ' . ($record->officeLocation?->name ?? 'Lokasi tidak terdeteksi') . $extra)
            ->success()
            ->persistent()
            ->send();
    }
}
