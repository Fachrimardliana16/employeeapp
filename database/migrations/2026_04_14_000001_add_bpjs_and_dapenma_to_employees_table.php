<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('bpjs_tk_status')->nullable()->after('bpjs_tk_number');
            $table->string('bpjs_kes_status')->nullable()->after('bpjs_kes_number');
            $table->string('bpjs_kes_class')->nullable()->after('bpjs_kes_status');
            $table->string('dapenma_number')->nullable()->after('rek_dplk_bersama');
            $table->decimal('dapenma_phdp', 15, 2)->nullable()->after('dapenma_number');
            $table->string('dapenma_status')->nullable()->after('dapenma_phdp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'bpjs_tk_status',
                'bpjs_kes_status',
                'bpjs_kes_class',
                'dapenma_number',
                'dapenma_phdp',
                'dapenma_status',
            ]);
        });
    }
};
