<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100);
            $table->string('scope_key', 100)->default('global');
            $table->text('value')->nullable();
            $table->string('value_type', 20)->default('string');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['setting_key', 'scope_key']);
            $table->index('scope_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};