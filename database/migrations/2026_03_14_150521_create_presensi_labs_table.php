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
        Schema::create('presensi_lab', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_lab_id')->constrained('peminjaman_lab')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('users');
            
            $table->date('tanggal_presensi');

            // Satpam yang dipilih mahasiswa
            $table->foreignId('satpam_masuk_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('satpam_keluar_id')->nullable()->constrained('users')->onDelete('set null');

            $table->dateTime('jam_masuk')->nullable();
            $table->dateTime('jam_keluar')->nullable();

            // Penambahan status menunggu_konfirmasi dan belum_hadir
            $table->enum('status_presensi', [
                'belum_hadir',
                'menunggu_konfirmasi_masuk',
                'menunggu_konfirmasi_keluar',
                'didalam',
                'selesai',
                'tidak_hadir'
            ])->default('belum_hadir');

            $table->timestamps();

            $table->unique(['peminjaman_lab_id', 'tanggal_presensi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_lab');
    }
    
};
