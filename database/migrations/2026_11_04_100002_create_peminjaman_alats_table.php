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
        Schema::create('peminjaman_alats', function (Blueprint $table) {
            $table->id();
            $table->string('kode_peminjaman', 50)->unique();
            $table->foreignId('mahasiswa_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lab_id')->nullable()->constrained('labs')->onDelete('set null');
            $table->foreignId('plp_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('keperluan');
            $table->dateTime('waktu_pinjam');
            $table->dateTime('rencana_kembali');
            $table->dateTime('waktu_kembali')->nullable();
            $table->enum('status', [
                'pending_plp',
                'disetujui',
                'ditolak',
                'sedang_dipinjam',
                'selesai',
                'dibatalkan'
            ])->default('pending_plp');
            $table->text('catatan_penolakan')->nullable();
            $table->text('alasan_batal')->nullable();
            $table->string('ttd_mahasiswa_file')->nullable();
            $table->string('ttd_plp_file')->nullable();
            $table->dateTime('tgl_ttd_plp')->nullable();
            $table->text('kondisi_kembali_catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_alats');
    }
};

