<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_agreement', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('docs');
        });

        // Seed nilai is_active berdasarkan agreement_date_end:
        // Jika tidak ada tanggal berakhir (PKWTT) → aktif
        // Jika tanggal berakhir masih di masa depan → aktif
        // Jika sudah lewat → tidak aktif
        DB::table('employee_agreement')->whereNull('agreement_date_end')->update(['is_active' => true]);
        DB::table('employee_agreement')->whereNotNull('agreement_date_end')->where('agreement_date_end', '>=', now()->toDateString())->update(['is_active' => true]);
        DB::table('employee_agreement')->whereNotNull('agreement_date_end')->where('agreement_date_end', '<', now()->toDateString())->update(['is_active' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_agreement', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
