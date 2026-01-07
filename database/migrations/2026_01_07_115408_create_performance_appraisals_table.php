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
        Schema::create('performance_appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('appraisal_period'); // periode penilaian (Q1 2024, Semester 1 2024, dll)
            $table->date('appraisal_date');
            $table->foreignId('appraiser_id')->constrained('users')->onDelete('cascade'); // penilai
            $table->json('criteria_scores'); // {"kualitas_kerja": 85, "kedisiplinan": 90, ...}
            $table->decimal('total_score', 5, 2)->default(0); // total nilai
            $table->enum('performance_grade', ['A', 'B', 'C', 'D', 'E'])->nullable(); // grade
            $table->text('strengths')->nullable(); // kelebihan
            $table->text('weaknesses')->nullable(); // kekurangan
            $table->text('recommendations')->nullable(); // rekomendasi
            $table->text('employee_comment')->nullable(); // komentar pegawai
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('users_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_appraisals');
    }
};
