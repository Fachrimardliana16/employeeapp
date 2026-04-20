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
        if (!Schema::hasTable('attendance_special_schedules')) {
            Schema::create('attendance_special_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->date('date');
                $table->boolean('is_working')->default(false)->comment('True = Wajib Masuk, False = Libur/Pengecualian');
                $table->string('description')->nullable();
                $table->foreignId('users_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                
                $table->unique(['employee_id', 'date'], 'emp_date_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_special_schedules');
    }
};
