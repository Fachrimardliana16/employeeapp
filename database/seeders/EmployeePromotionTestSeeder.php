<?php

namespace Database\Seeders;

use App\Models\MasterEmployeeGrade;
use App\Models\MasterEmployeeBasicSalary;
use App\Models\MasterEmployeeServiceGrade;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeePromotionTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        // Create service grades
        $serviceGrades = [
            ['name' => 'Staff', 'desc' => 'Entry level position'],
            ['name' => 'Senior Staff', 'desc' => 'Experienced staff position'],
            ['name' => 'Supervisor', 'desc' => 'Supervisory position'],
        ];

        foreach ($serviceGrades as $serviceGrade) {
            MasterEmployeeServiceGrade::firstOrCreate(
                ['name' => $serviceGrade['name']],
                array_merge($serviceGrade, ['users_id' => $user->id, 'is_active' => true])
            );
        }

        // Create employee grades
        $grades = [
            ['name' => 'Grade I', 'desc' => 'Entry level grade', 'salary' => 5000000],
            ['name' => 'Grade II', 'desc' => 'Junior grade', 'salary' => 7000000],
            ['name' => 'Grade III', 'desc' => 'Senior grade', 'salary' => 10000000],
            ['name' => 'Grade IV', 'desc' => 'Supervisor grade', 'salary' => 15000000],
        ];

        foreach ($grades as $gradeData) {
            $grade = MasterEmployeeGrade::firstOrCreate(
                ['name' => $gradeData['name']],
                [
                    'desc' => $gradeData['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );

            // Create basic salary for each grade
            $serviceGrade = MasterEmployeeServiceGrade::first();
            if ($serviceGrade) {
                MasterEmployeeBasicSalary::firstOrCreate(
                    [
                        'employee_grade_id' => $grade->id,
                        'employee_service_grade_id' => $serviceGrade->id,
                    ],
                    [
                        'amount' => $gradeData['salary'],
                        'desc' => 'Basic salary for ' . $gradeData['name'],
                        'is_active' => true,
                        'users_id' => $user->id,
                    ]
                );
            }
        }

        $this->command->info('Employee promotion test data created successfully!');
    }
}
