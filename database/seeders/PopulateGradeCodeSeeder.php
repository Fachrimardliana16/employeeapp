<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterEmployeeGrade;
use Illuminate\Support\Facades\DB;

class PopulateGradeCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Populate grade_code untuk master_employee_grades
     * Berdasarkan mapping pangkat_name ke grade_code standar PNS
     */
    public function run(): void
    {
        echo "\n🔧 Populating grade_code for master_employee_grades...\n\n";

        // Mapping pangkat_name → grade_code
        $gradeMapping = [
            'Pegawai Dasar Muda'    => 'I/a',
            'Pegawai Dasar Muda I'  => 'I/b',
            'Pegawai Dasar'         => 'I/c',
            'Pegawai Dasar I'       => 'I/d',
            
            'Pelaksana Muda'        => 'II/a',
            'Pelaksana Muda I'      => 'II/b',
            'Pelaksana'             => 'II/c',
            'Pelaksana I'           => 'II/d',
            
            'Staf Muda'             => 'III/a',
            'Staf Muda I'           => 'III/b',
            'Staf'                  => 'III/c',
            'Staf I'                => 'III/d',
            
            'Staf Madya'            => 'IV/a',
            'Staf Madya I'          => 'IV/b',
            'Staf Utama Madya'      => 'IV/c',
            'Staf Utama'            => 'IV/d',
        ];

        $updated = 0;
        $notFound = [];

        foreach ($gradeMapping as $pangkatName => $gradeCode) {
            $grade = MasterEmployeeGrade::where('pangkat_name', $pangkatName)->first();
            
            if ($grade) {
                DB::table('master_employee_grades')
                    ->where('id', $grade->id)
                    ->update([
                        'grade_code' => $gradeCode,
                        'updated_at' => now()
                    ]);
                
                echo "✅ Updated: {$pangkatName} → {$gradeCode}\n";
                $updated++;
            } else {
                $notFound[] = $pangkatName;
            }
        }

        // Handle ID 17 yang pangkat_name-nya kosong
        $emptyGrade = MasterEmployeeGrade::where('id', 17)->first();
        if ($emptyGrade && empty($emptyGrade->grade_code)) {
            DB::table('master_employee_grades')
                ->where('id', 17)
                ->update([
                    'grade_code' => 'IV/e',
                    'pangkat_name' => 'Pembina Utama',
                    'updated_at' => now()
                ]);
            echo "✅ Updated ID 17: (empty) → IV/e (Pembina Utama)\n";
            $updated++;
        }

        echo "\n" . str_repeat('═', 60) . "\n";
        echo "📊 Summary:\n";
        echo "   ✅ Updated: {$updated} records\n";
        
        if (!empty($notFound)) {
            echo "   ⚠️  Not found: " . count($notFound) . " records\n";
            foreach ($notFound as $name) {
                echo "      - {$name}\n";
            }
        }

        echo "\n🎯 Next step: Re-run PayrollComponentRuleSeeder\n";
        echo "   php artisan db:seed --class=PayrollComponentRuleSeeder\n\n";
    }
}
