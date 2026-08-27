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
        Schema::create('inventaris_ruangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('labs')->onDelete('cascade');
            $table->string('kode_barang', 50)->nullable()->index();
            $table->string('nama_barang');
            $table->text('spesifikasi_merk_tipe')->nullable();
            $table->year('tahun_perolehan')->nullable();
            $table->integer('jumlah')->default(1);
            $table->string('satuan', 20)->default('Unit');
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->string('nup', 50)->nullable();
            $table->boolean('is_bisa_dipinjam')->default(false);
            $table->text('keterangan')->nullable();
            $table->string('foto_barang')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris_ruangans');
    }
};
