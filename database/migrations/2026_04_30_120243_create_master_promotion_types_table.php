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
        Schema::create('master_promotion_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Kode jenis kenaikan: biasa, pilihan, penyesuaian, istimewa, pengabdian, anumerta');
            $table->string('name')->comment('Nama jenis kenaikan pangkat');
            $table->text('description')->nullable()->comment('Deskripsi/ketentuan sesuai PP Pasal 10');
            $table->text('requirements')->nullable()->comment('Persyaratan khusus');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('users_id');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_promotion_types');
    }
};
