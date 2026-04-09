<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterOfficeLocation;
use App\Models\AttendanceSchedule;
use App\Models\MasterDepartment;
use App\Models\User;

class AttendanceSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@employapp.com')->first() ?? User::first();

        // 1. Seed Attendance Schedules
        $schedules = [
            ['day' => 'Monday', 'check_in_start' => '06:00:00', 'check_in_end' => '09:00:00', 'check_out_start' => '15:00:00', 'check_out_end' => '20:00:00', 'late_threshold' => '07:30:59'],
            ['day' => 'Tuesday', 'check_in_start' => '06:00:00', 'check_in_end' => '09:00:00', 'check_out_start' => '15:00:00', 'check_out_end' => '20:00:00', 'late_threshold' => '07:30:59'],
            ['day' => 'Wednesday', 'check_in_start' => '06:00:00', 'check_in_end' => '09:00:00', 'check_out_start' => '15:00:00', 'check_out_end' => '20:00:00', 'late_threshold' => '07:30:59'],
            ['day' => 'Thursday', 'check_in_start' => '06:00:00', 'check_in_end' => '09:00:00', 'check_out_start' => '15:00:00', 'check_out_end' => '20:00:00', 'late_threshold' => '07:30:59'],
            ['day' => 'Friday', 'check_in_start' => '06:00:00', 'check_in_end' => '09:00:00', 'check_out_start' => '11:00:00', 'check_out_end' => '15:00:00', 'late_threshold' => '07:30:59'],
            ['day' => 'Saturday', 'check_in_start' => '06:00:00', 'check_in_end' => '09:00:00', 'check_out_start' => '13:00:00', 'check_out_end' => '17:00:00', 'late_threshold' => '07:30:59'],
        ];

        foreach ($schedules as $schedule) {
            AttendanceSchedule::updateOrCreate(['day' => $schedule['day']], $schedule);
        }

        // 2. Seed Office Locations & Map to Departments
        $locations = [
            ['name' => 'Kantor Pusat', 'lat' => -7.404543, 'lng' => 109.374670, 'dept' => 'Bagian Umum', 'type' => 'Bagian'],
            ['name' => 'Cabang Kota Bangga', 'lat' => -7.404543, 'lng' => 109.374670, 'dept' => 'Cabang Kota Bangga', 'type' => 'Cabang'],
            ['name' => 'Cabang Jendral Soedirman', 'lat' => -7.398324, 'lng' => 109.336666, 'dept' => 'Cabang Jendral Soedirman', 'type' => 'Cabang'],
            ['name' => 'Cabang Usman Janatin 1', 'lat' => -7.332313, 'lng' => 109.351656, 'dept' => 'Cabang Usman Janatin', 'type' => 'Cabang'],
            ['name' => 'Cabang Usman Janatin 2', 'lat' => -7.309401, 'lng' => 109.368014, 'dept' => 'Cabang Usman Janatin', 'type' => 'Cabang'],
            ['name' => 'Cabang Ardilawet 1', 'lat' => -7.352508, 'lng' => 109.355665, 'dept' => 'Cabang Ardilawet', 'type' => 'Cabang'],
            ['name' => 'Cabang Ardilawet 2', 'lat' => -7.368791, 'lng' => 109.343375, 'dept' => 'Cabang Ardilawet', 'type' => 'Cabang'],
            ['name' => 'Cabang Goentoer Djarjono', 'lat' => -7.378655, 'lng' => 109.404692, 'dept' => 'Cabang Goentoer Djarjono', 'type' => 'Cabang'],
            ['name' => 'Unit IKK Kemangkon', 'lat' => -7.445152, 'lng' => 109.390904, 'dept' => 'Unit IKK Kemangkon', 'type' => 'Unit'],
            ['name' => 'Unit IKK Bukateja', 'lat' => -7.424277, 'lng' => 109.409476, 'dept' => 'Unit IKK Bukateja', 'type' => 'Unit'],
            ['name' => 'Unit IKK Karangreja', 'lat' => -7.228770, 'lng' => 109.285521, 'dept' => 'Unit IKK Karangreja', 'type' => 'Unit'],
            ['name' => 'Unit IKK Rembang', 'lat' => -7.305272, 'lng' => 109.523307, 'dept' => 'Unit IKK Rembang', 'type' => 'Unit'],
        ];

        foreach ($locations as $loc) {
            $dept = MasterDepartment::firstOrCreate(
                ['name' => $loc['dept']],
                [
                    'type' => $loc['type'],
                    'is_active' => true,
                    'users_id' => $admin->id
                ]
            );

            // Force update type if already exists
            $dept->update(['type' => $loc['type']]);

            MasterOfficeLocation::updateOrCreate(
                ['name' => $loc['name']],
                [
                    'latitude' => $loc['lat'],
                    'longitude' => $loc['lng'],
                    'is_active' => true,
                    'users_id' => $admin->id,
                    'address' => 'Alamat ' . $loc['name'],
                ]
            );
        }
    }
}
