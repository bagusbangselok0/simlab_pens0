<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_labs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_jadwal_id')->constrained('periode_jadwals')->cascadeOnDelete();
            $table->foreignId('lab_id')->nullable()->constrained('labs')->nullOnDelete();
            $table->string('nama_ruangan', 100);
            $table->string('hari', 20);
            $table->unsignedTinyInteger('jam_ke_mulai')->nullable();
            $table->unsignedTinyInteger('jam_ke_selesai')->nullable();
            $table->string('waktu_mulai', 10);
            $table->string('waktu_berakhir', 10);
            $table->string('mata_kuliah', 200);
            $table->string('jenis', 50)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->string('semester', 10)->nullable();
            $table->string('program_studi', 50)->nullable();
            $table->foreignId('dosen_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_dosen', 150)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['periode_jadwal_id', 'nama_ruangan', 'hari']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_labs');
    }
};
