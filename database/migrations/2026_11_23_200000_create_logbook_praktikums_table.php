<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_praktikums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_lab_id')->nullable()->constrained('jadwal_labs')->nullOnDelete();
            $table->foreignId('lab_id')->constrained('labs')->cascadeOnDelete();
            $table->foreignId('plp_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dosen_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_dosen_fallback', 150)->nullable();
            $table->string('nama_mk', 200);
            $table->string('kelas', 100);
            $table->string('hari', 20)->nullable();
            $table->string('semester', 30);
            $table->string('tahun_akademik', 20);
            $table->string('jam_default', 30)->nullable();
            $table->string('keterangan_plp', 100)->nullable();
            $table->foreignId('default_mahasiswa_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('default_mahasiswa_nama', 150)->nullable();
            $table->string('default_mahasiswa_nrp', 50)->nullable();
            $table->string('default_mahasiswa_ttd_path')->nullable();
            $table->timestamps();

            $table->index(['plp_id', 'lab_id']);
            $table->index(['dosen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_praktikums');
    }
};
