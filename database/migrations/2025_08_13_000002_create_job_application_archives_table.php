<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_application_archives', function (Blueprint $table) {
            $table->id();

            // Referensi ke lamaran asli
            $table->foreignId('job_application_id')->constrained('job_applications')->onDelete('cascade');

            // Data snapshot saat diterima
            $table->json('application_data'); // Snapshot data lengkap lamaran
            $table->json('interview_data')->nullable(); // Data interview

            // Keputusan penerimaan
            $table->enum('decision', ['accepted', 'rejected']);
            $table->text('decision_reason')->nullable();
            $table->date('decision_date');
            $table->unsignedBigInteger('decided_by'); // User yang memutuskan

            // Data kontrak yang akan dibuat (jika diterima)
            $table->foreignId('proposed_agreement_type_id')->nullable()->constrained('master_employee_agreements');
            $table->foreignId('proposed_employment_status_id')->nullable()->constrained('master_employee_status_employments');
            $table->foreignId('proposed_grade_id')->nullable()->constrained('master_employee_grades');
            $table->decimal('proposed_salary', 15, 2)->nullable();
            $table->date('proposed_start_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['decision', 'decision_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_application_archives');
    }
};
