<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan index pada kolom-kolom yang dipakai oleh dashboard widget
 * (birthday, retirement, contract) dan scope karir (kgb, promosi).
 *
 * Kolom ini di-WHERE setiap dashboard load sehingga tanpa index
 * MySQL harus full-scan tabel employees.
 */
return new class extends Migration
{
    private function getExistingIndexes(): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return collect(DB::select(
                "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='employees'"
            ))->pluck('name')->all();
        }

        // MySQL / MariaDB
        return collect(DB::select("SHOW INDEX FROM `employees`"))
            ->pluck('Key_name')
            ->unique()
            ->all();
    }

    public function up(): void
    {
        $existing = $this->getExistingIndexes();

        Schema::table('employees', function (Blueprint $table) use ($existing) {
            // Widget ulang tahun: WHERE MONTH(date_birth) = ?
            if (!in_array('emp_date_birth_index', $existing)) {
                $table->index('date_birth', 'emp_date_birth_index');
            }

            // Widget pensiun & daftar pensiunan: WHERE retirement BETWEEN ? AND ?
            if (!in_array('emp_retirement_index', $existing)) {
                $table->index('retirement', 'emp_retirement_index');
            }

            // Widget habis kontrak: WHERE agreement_date_end BETWEEN ? AND ?
            if (!in_array('emp_agreement_date_end_index', $existing)) {
                $table->index('agreement_date_end', 'emp_agreement_date_end_index');
            }

            // Scope kgb & promosi: WHERE YEAR(next_kgb_date) / next_promotion_date
            if (!in_array('emp_next_kgb_date_index', $existing)) {
                $table->index('next_kgb_date', 'emp_next_kgb_date_index');
            }
            if (!in_array('emp_next_promotion_date_index', $existing)) {
                $table->index('next_promotion_date', 'emp_next_promotion_date_index');
            }

            // FK ke positions — dipakai di banyak JOIN dan eager load
            if (!in_array('emp_employee_position_id_index', $existing)) {
                $table->index('employee_position_id', 'emp_employee_position_id_index');
            }
        });
    }

    public function down(): void
    {
        $existing = $this->getExistingIndexes();

        Schema::table('employees', function (Blueprint $table) use ($existing) {
            foreach ([
                'emp_date_birth_index',
                'emp_retirement_index',
                'emp_agreement_date_end_index',
                'emp_next_kgb_date_index',
                'emp_next_promotion_date_index',
                'emp_employee_position_id_index',
            ] as $idx) {
                if (in_array($idx, $existing)) {
                    $table->dropIndex($idx);
                }
            }
        });
    }
};
