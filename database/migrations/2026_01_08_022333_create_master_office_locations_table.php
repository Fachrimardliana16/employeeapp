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
        Schema::create('master_office_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama lokasi kantor/cabang
            $table->string('code')->unique(); // Kode lokasi (misal: HO, CAB-JKT, CAB-SBY)
            $table->text('address')->nullable(); // Alamat lengkap
            $table->decimal('latitude', 10, 8); // Koordinat latitude
            $table->decimal('longitude', 11, 8); // Koordinat longitude
            $table->integer('radius')->default(100); // Radius dalam meter
            $table->string('description')->nullable(); // Deskripsi
            $table->boolean('is_active')->default(true); // Status aktif
            $table->unsignedBigInteger('users_id')->nullable(); // User yang membuat
            $table->foreign('users_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_office_locations');
    }
};
