<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_pertemuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_praktikum_id')->constrained('logbook_praktikums')->cascadeOnDelete();
            $table->unsignedSmallInteger('minggu_ke');
            $table->string('judul_praktikum', 255);
            $table->date('tanggal_pelaksanaan');
            $table->string('jam_pelaksanaan', 30);
            $table->json('peralatan')->nullable();
            $table->json('aktivitas')->nullable();
            $table->json('permasalahan')->nullable();
            $table->foreignId('mahasiswa_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mahasiswa_nama', 150)->nullable();
            $table->string('mahasiswa_nrp', 50)->nullable();
            $table->string('mahasiswa_ttd_path')->nullable();
            $table->string('status', 30)->default('draft');
            $table->string('approval_mode', 20)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revoked_reason')->nullable();
            $table->timestamps();

            $table->index(['logbook_praktikum_id', 'minggu_ke']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_pertemuans');
    }
};
