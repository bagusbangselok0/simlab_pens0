<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen_logbook_auto_approves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plp_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->date('valid_until')->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->timestamps();

            $table->unique(['dosen_id', 'plp_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen_logbook_auto_approves');
    }
};
