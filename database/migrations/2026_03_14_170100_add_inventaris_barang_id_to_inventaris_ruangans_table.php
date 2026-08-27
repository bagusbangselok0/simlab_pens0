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
        Schema::table('inventaris_ruangans', function (Blueprint $table) {
            $table->foreignId('inventaris_barang_id')->nullable()->after('id')->constrained('inventaris_barangs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventaris_ruangans', function (Blueprint $table) {
            $table->dropForeign(['inventaris_barang_id']);
            $table->dropColumn('inventaris_barang_id');
        });
    }
};
