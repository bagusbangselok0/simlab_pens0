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
        Schema::create('inventaris_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang', 50)->nullable()->index();
            $table->string('nup', 50)->nullable();
            $table->string('nama_barang');
            $table->string('merk')->nullable();
            $table->string('tipe')->nullable();
            $table->date('tgl_buku_pertama')->nullable();
            $table->date('tgl_perolehan')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris_barangs');
    }
};
