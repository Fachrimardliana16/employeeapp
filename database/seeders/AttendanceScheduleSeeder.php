<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceSchedule;

class AttendanceScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            ['day' => 'Senin', 'check_in_start' => '06:00:00', 'check_in_end' => '07:30:00', 'check_out_start' => '15:00:00', 'check_out_end' => '16:00:00', 'late_threshold' => '07:30:59'],
            ['day' => 'Selasa', 'check_in_start' => '06:00:00', 'check_in_end' => '07:30:00', 'check_out_start' => '15:00:00', 'check_out_end' => '16:00:00', 'late_threshold' => '07:30:59'],
            ['day' => 'Rabu', 'check_in_start' => '06:00:00', 'check_in_end' => '07:30:00', 'check_out_start' => '15:00:00', 'check_out_end' => '16:00:00', 'late_threshold' => '07:30:59'],
            ['day' => 'Kamis', 'check_in_start' => '06:00:00', 'check_in_end' => '07:30:00', 'check_out_start' => '15:00:00', 'check_out_end' => '16:00:00', 'late_threshold' => '07:30:59'],
            ['day' => 'Jumat', 'check_in_start' => '06:00:00', 'check_in_end' => '07:30:00', 'check_out_start' => '11:00:00', 'check_out_end' => '12:00:00', 'late_threshold' => '07:30:59'],
            ['day' => 'Sabtu', 'check_in_start' => '06:00:00', 'check_in_end' => '07:30:00', 'check_out_start' => '13:00:00', 'check_out_end' => '14:00:00', 'late_threshold' => '07:30:59'],
        ];

        foreach ($schedules as $schedule) {
            AttendanceSchedule::updateOrCreate(['day' => $schedule['day']], $schedule);
        }
    }
}
