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
        // create_peminjaman_lab_table.php
        Schema::create('peminjaman_lab', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lab_id')->constrained('labs')->onDelete('cascade');
            $table->foreignId('lab_manager_id')->constrained('lab_managers')->onDelete('cascade');
            $table->text('tujuan');
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai');
            $table->enum('status', ['dibatalkan', 'pending_plp', 'pending_kalab', 'disetujui', 'ditolak', 'kadaluarsa', 'dibatalkan_mahasiswa'])->default('pending_plp');
            $table->string('ttd_mahasiswa_file')->nullable();

            // Approval PLP
            $table->dateTime('tgl_ttd_plp')->nullable();
            $table->string('ttd_plp_file')->nullable();

            // Approval Kalab
            $table->dateTime('tgl_ttd_kalab')->nullable();
            $table->string('ttd_kalab_file')->nullable();

            $table->text('catatan_tolak')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_labs');
    }
};
