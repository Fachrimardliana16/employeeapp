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
        Schema::create('employee_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->string('pin');
            $table->string('employee_name');
            $table->timestamp('attendance_time');
            $table->string('state'); // in/out status
            $table->string('verification')->nullable();
            $table->string('work_code')->nullable();
            $table->string('reserved')->nullable();
            $table->string('device')->nullable();
            $table->string('picture')->nullable(); // Picture path
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_attendance_records');
    }
};
