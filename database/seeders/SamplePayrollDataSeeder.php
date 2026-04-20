<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeAttendanceRecord;
use App\Models\EmployeeFamily;
use App\Models\MasterEmployeeGrade;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SamplePayrollDataSeeder extends Seeder
{
    public function run(): void
    {
        $service = new PayrollService();
        $period = Carbon::create(2026, 1, 1);
        $userId = 1;

        // 1. Get some target employees
        $employees = Employee::limit(5)->get();
        $statuses = [
            0 => ['id' => 1, 'salary_id' => 3, 'label' => 'THL'],
            1 => ['id' => 2, 'salary_id' => 2, 'label' => 'MAGANG'],
            2 => ['id' => 3, 'salary_id' => 1, 'label' => 'KONTRAK'],
            3 => ['id' => 4, 'salary_id' => null, 'label' => 'CPNS'],
            4 => ['id' => 5, 'salary_id' => null, 'label' => 'TETAP'],
        ];

        foreach ($employees as $index => $employee) {
            $statusInfo = $statuses[$index] ?? ['id' => 5, 'salary_id' => null, 'label' => 'TETAP'];
            $pin = (string)(9000 + $index);
            $this->command->info("Seeding data for: {$employee->name} (PIN: {$pin}, Status: {$statusInfo['label']})");

            // 2. Setup Employee Data (PIN, Status & Salary)
            try {
                $employee->update([
                    'pin' => $pin,
                    'employment_status_id' => $statusInfo['id'],
                    'non_permanent_salary_id' => $statusInfo['salary_id'],
                    'basic_salary_id' => $employee->basic_salary_id ?? 1,
                    'employee_service_grade_id' => $employee->employee_service_grade_id ?? 1
                ]);
            } catch (\Exception $e) {
                $this->command->error("Failed to update employee {$employee->name}: " . $e->getMessage());
                continue;
            }

            // 3. Setup Family (Ensure always 1 for testing 10% allowance)
            try {
                DB::table('employee_families')->where('employees_id', $employee->id)->delete();
                DB::table('employee_families')->insert([
                    'employees_id' => $employee->id,
                    'master_employee_families_id' => 2, // Istri
                    'family_name' => 'Pasangan ' . $employee->name,
                    'family_gender' => $employee->gender === 'L' ? 'female' : 'male',
                    'users_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                $this->command->error("Failed to add family for {$employee->name}: " . $e->getMessage());
            }

            // 4. Setup Attendance (Full presence for all)
            try {
                DB::table('employee_attendance_records')->where('pin', $pin)->delete();
                $start = $period->copy()->startOfMonth();
                $end = $period->copy()->endOfMonth();
                
                $current = $start->copy();
                while ($current <= $end) {
                    if (!$current->isWeekend()) {
                        DB::table('employee_attendance_records')->insert([
                            'pin' => $pin,
                            'employee_name' => $employee->name,
                            'attendance_time' => $current->copy()->setHour(7)->setMinute(30)->toDateTimeString(),
                            'state' => 'check_in',
                            'attendance_status' => (($current->day + $index) % 7 === 0) ? 'late' : 'on_time',
                            'users_id' => $userId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    $current->addDay();
                }
            } catch (\Exception $e) {
                $this->command->error("Failed to add attendance for {$employee->name}: " . $e->getMessage());
            }

            // 5. Generate Payroll
            try {
                DB::table('employee_payrolls')->where('employee_id', $employee->id)->where('payroll_period', $period->format('Y-m-d'))->delete();
                $payroll = $service->calculatePayroll($employee, $period, $userId);
                $this->command->info("SUCCESS! Net: " . number_format($payroll->net_salary));
            } catch (\Exception $e) {
                $this->command->error("Payroll calculation failed for {$employee->name}: " . $e->getMessage());
            }
        }
    }
}
