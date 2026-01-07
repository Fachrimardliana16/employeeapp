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
        Schema::create('master_standar_harga_satuans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama item SHS (misal: Akomodasi Hotel Bintang 3)
            $table->string('category'); // Kategori: accommodation, pocket_money, reserve, transport, meal
            $table->string('location')->nullable(); // Lokasi/daerah (misal: Jakarta, Bandung)
            $table->string('grade_level')->nullable(); // Tingkat jabatan yang berhak
            $table->decimal('amount', 15, 2); // Jumlah biaya
            $table->string('unit')->default('per_day'); // Satuan: per_day, per_trip, lump_sum
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('users_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_standar_harga_satuans');
    }
};
