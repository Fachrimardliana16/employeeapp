<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();

            // Informasi Pribadi
            $table->string('application_number')->unique(); // Nomor pendaftaran otomatis
            $table->string('name');
            $table->string('place_birth');
            $table->date('date_birth');
            $table->enum('gender', ['male', 'female']);
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed']);
            $table->text('address');
            $table->string('phone_number', 20);
            $table->string('email')->unique();
            $table->string('id_number', 16)->nullable(); // KTP

            // Posisi yang Dilamar
            $table->foreignId('applied_position_id')->constrained('master_employee_positions')->onDelete('cascade');
            $table->foreignId('applied_department_id')->constrained('master_departments')->onDelete('cascade');
            $table->foreignId('applied_sub_department_id')->nullable()->constrained('master_sub_departments')->onDelete('set null');

            // Pendidikan
            $table->foreignId('education_level_id')->constrained('master_employee_education')->onDelete('cascade');
            $table->string('education_institution'); // Nama sekolah/universitas
            $table->string('education_major')->nullable(); // Jurusan
            $table->year('education_graduation_year');
            $table->decimal('education_gpa', 3, 2)->nullable(); // IPK

            // Pengalaman Kerja Terakhir
            $table->string('last_company_name')->nullable();
            $table->string('last_position')->nullable();
            $table->date('last_work_start_date')->nullable();
            $table->date('last_work_end_date')->nullable();
            $table->text('last_work_description')->nullable();
            $table->decimal('last_salary', 15, 2)->nullable();

            // Ekspektasi
            $table->decimal('expected_salary', 15, 2)->nullable();
            $table->date('available_start_date')->nullable();

            // Dokumen
            $table->json('documents')->nullable(); // CV, Ijazah, dll

            // Status Lamaran
            $table->enum('status', [
                'submitted',     // Baru dikirim
                'reviewed',      // Sedang direview
                'interview_scheduled', // Dijadwalkan interview
                'interviewed',   // Sudah interview
                'accepted',      // Diterima
                'rejected',      // Ditolak
                'withdrawn'      // Dibatalkan pelamar
            ])->default('submitted');

            $table->text('notes')->nullable(); // Catatan HR
            $table->json('interview_schedule')->nullable(); // Jadwal interview
            $table->json('interview_results')->nullable(); // Hasil interview

            // Referensi
            $table->string('reference_name')->nullable();
            $table->string('reference_phone')->nullable();
            $table->string('reference_relation')->nullable(); // Hubungan dengan referensi

            // Tracking
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('interview_at')->nullable();
            $table->timestamp('decision_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // User tracking
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->index(['status', 'applied_position_id']);
            $table->index(['submitted_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
