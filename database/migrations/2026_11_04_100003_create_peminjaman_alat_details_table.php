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
        Schema::create('peminjaman_alat_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_alat_id')->constrained('peminjaman_alats')->onDelete('cascade');
            $table->foreignId('inventaris_ruangan_id')->nullable()->constrained('inventaris_ruangans')->onDelete('set null');
            $table->string('nama_alat');
            $table->string('kode_barang', 50)->nullable();
            $table->string('nup', 50)->nullable();
            $table->integer('jumlah')->default(1);
            $table->string('satuan', 20)->default('Unit');
            $table->boolean('is_manual')->default(false);
            $table->text('spesifikasi')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('kondisi_pinjam', 50)->default('baik');
            $table->string('kondisi_kembali', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_alat_details');
    }
};

