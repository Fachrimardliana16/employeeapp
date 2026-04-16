<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\MasterDepartment;
use App\Models\MasterEmployeePosition;
use App\Models\MasterEmployeeGrade;
use App\Models\MasterEmployeeStatusEmployment;
use App\Models\MasterEmployeeServiceGrade;
use Illuminate\Support\Str;

class CareerMovementDummySeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        if ($employees->isEmpty()) {
            $this->command->error('No employees found.');
            return;
        }

        $userId = DB::table('users')->first()?->id ?? 1;
        $departments = MasterDepartment::pluck('id')->toArray();
        $positions = MasterEmployeePosition::pluck('id')->toArray();
        $grades = MasterEmployeeGrade::pluck('id')->toArray();
        $employmentStatuses = MasterEmployeeStatusEmployment::pluck('id')->toArray();
        $serviceGrades = MasterEmployeeServiceGrade::pluck('id')->toArray();

        if (empty($departments) || empty($positions) || empty($grades)) {
            $this->command->error('Master data (Departments/Positions/Grades) is missing.');
            return;
        }

        // 1. Promotions
        $this->command->info('Seeding Promotions...');
        for ($i = 0; $i < 15; $i++) {
            $emp = $employees->random();
            DB::table('employee_promotions')->insert([
                'employee_id' => $emp->id,
                'old_basic_salary_id' => $emp->basic_salary_id ?? $grades[array_rand($grades)],
                'new_basic_salary_id' => $grades[array_rand($grades)],
                'promotion_date' => now()->subDays(rand(1, 365))->format('Y-m-d'),
                'next_promotion_date' => now()->addYears(4)->format('Y-m-d'),
                'decision_letter_number' => 'SK/PROM/' . strtoupper(Str::random(6)),
                'is_applied' => rand(0, 1),
                'users_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Mutations
        $this->command->info('Seeding Mutations...');
        for ($i = 0; $i < 15; $i++) {
            $emp = $employees->random();
            DB::table('employee_mutations')->insert([
                'employee_id' => $emp->id,
                'old_department_id' => $emp->departments_id ?? $departments[array_rand($departments)],
                'new_department_id' => $departments[array_rand($departments)],
                'old_position_id' => $emp->employee_position_id ?? $positions[array_rand($positions)],
                'new_position_id' => $positions[array_rand($positions)],
                'mutation_date' => now()->subDays(rand(1, 365))->format('Y-m-d'),
                'decision_letter_number' => 'SK/MUT/' . strtoupper(Str::random(6)),
                'is_applied' => rand(0, 1),
                'users_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Appointments
        $this->command->info('Seeding Appointments...');
        for ($i = 0; $i < 10; $i++) {
            $emp = $employees->random();
            DB::table('employee_appointments')->insert([
                'employee_id' => $emp->id,
                'old_employment_status_id' => $emp->employment_status_id ?? $employmentStatuses[array_rand($employmentStatuses)],
                'new_employment_status_id' => $employmentStatuses[array_rand($employmentStatuses)],
                'appointment_date' => now()->subDays(rand(1, 180))->format('Y-m-d'),
                'decision_letter_number' => 'SK/APP/' . strtoupper(Str::random(6)),
                'is_applied' => rand(0, 1),
                'users_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. KGB (Periodic Salary Increase)
        $this->command->info('Seeding KGB...');
        for ($i = 0; $i < 15; $i++) {
            $emp = $employees->random();
            DB::table('employee_periodic_salary_increase')->insert([
                'employee_id' => $emp->id,
                'old_basic_salary_id' => $emp->basic_salary_id ?? $grades[array_rand($grades)],
                'new_basic_salary_id' => $grades[array_rand($grades)],
                'new_employee_service_grade_id' => !empty($serviceGrades) ? $serviceGrades[array_rand($serviceGrades)] : null,
                'total_basic_salary' => rand(3000000, 7000000),
                'date_periodic_salary_increase' => now()->subDays(rand(1, 730))->format('Y-m-d'),
                'number_psi' => 'KGB/' . strtoupper(Str::random(6)),
                'is_applied' => rand(0, 1),
                'users_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Career Movements
        $this->command->info('Seeding Career Movements...');
        for ($i = 0; $i < 15; $i++) {
            $emp = $employees->random();
            DB::table('employee_career_movements')->insert([
                'employee_id' => $emp->id,
                'old_department_id' => $emp->departments_id ?? $departments[array_rand($departments)],
                'new_department_id' => $departments[array_rand($departments)],
                'old_position_id' => $emp->employee_position_id ?? $positions[array_rand($positions)],
                'new_position_id' => $positions[array_rand($positions)],
                'type' => rand(0, 1) ? 'promotion' : 'demotion',
                'movement_date' => now()->subDays(rand(1, 365))->format('Y-m-d'),
                'decision_letter_number' => 'SK/CAREER/' . strtoupper(Str::random(6)),
                'is_applied' => rand(0, 1),
                'users_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 6. Retirements
        $this->command->info('Seeding Retirements...');
        for ($i = 0; $i < 10; $i++) {
            $emp = $employees->random();
            DB::table('employee_retirements')->insert([
                'employee_id' => $emp->id,
                'retirement_date' => now()->addDays(rand(-365, 365))->format('Y-m-d'),
                'reason' => 'Mencapai Batas Usia Pensiun',
                'approval_status' => collect(['pending', 'approved'])->random(),
                'users_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
