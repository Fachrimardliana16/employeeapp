<?php

namespace App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\Pages;

use App\Filament\Employee\Resources\EmployeeAttendanceRecordResource;
use App\Models\MasterOfficeLocation;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\View;

class CreateEmployeeAttendanceRecord extends CreateRecord
{
    protected static string $resource = EmployeeAttendanceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validate GPS coordinates exist
        if (empty($data['check_latitude']) || empty($data['check_longitude'])) {
            Notification::make()
                ->title('GPS Tidak Terdeteksi')
                ->body('Lokasi GPS Anda tidak dapat dideteksi. Pastikan GPS aktif dan izin lokasi diberikan.')
                ->danger()
                ->send();

            $this->halt();
        }

        // Get employee's department
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $employee = $user?->employee;
        $departmentId = $employee?->departments_id;

        // Find nearest office location (filtered by department)
        $result = MasterOfficeLocation::getClosestLocation(
            $data['check_latitude'],
            $data['check_longitude'],
            $departmentId
        );

        if (!$result) {
            Notification::make()
                ->title('Tidak Ada Lokasi Kantor')
                ->body($departmentId
                    ? 'Tidak ada lokasi kantor aktif untuk departemen Anda. Hubungi administrator.'
                    : 'Tidak ada lokasi kantor aktif yang ditemukan. Hubungi administrator.')
                ->danger()
                ->send();

            $this->halt();
        }

        $location = $result['location'];
        $distance = $result['distance'];

        // Check if location matches employee's department
        if ($location->departments_id && $departmentId && $location->departments_id != $departmentId) {
            Notification::make()
                ->title('Departemen Tidak Sesuai')
                ->body("Lokasi {$location->name} khusus untuk departemen {$location->department->name}. Anda tidak dapat absen di sini.")
                ->danger()
                ->send();

            $this->halt();
        }

        // Set location data
        $data['office_location_id'] = $location->id;
        $data['distance_from_office'] = (int) round($distance);
        $data['is_within_radius'] = $location->isWithinRadius(
            $data['check_latitude'],
            $data['check_longitude']
        );

        // Validate if within radius
        if (!$data['is_within_radius']) {
            Notification::make()
                ->title('Diluar Radius Kantor')
                ->body("Anda berada {$data['distance_from_office']} meter dari {$location->name}. Batas maksimal adalah {$location->radius} meter.")
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        $record = $this->getRecord();

        return Notification::make()
            ->success()
            ->title('Kehadiran Tercatat')
            ->body("Anda telah {$record->state} di {$record->officeLocation->name} ({$record->distance_from_office}m dari kantor)");
    }

    public function getContentFooter(): ?\Illuminate\Contracts\View\View
    {
        return View::make('filament.forms.geolocation-script');
    }
}
