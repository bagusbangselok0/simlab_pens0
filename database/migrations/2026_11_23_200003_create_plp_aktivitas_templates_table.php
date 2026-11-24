<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plp_aktivitas_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plp_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_template', 120);
            $table->json('aktivitas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plp_aktivitas_templates');
    }
};
