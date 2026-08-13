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
        // create_users_table.php (Modifikasi default Laravel)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles');
            $table->string('nip', 30)->unique()->nullable();
            $table->string('nrp', 30)->unique()->nullable();
            $table->string('gelar_depan', 20)->nullable();
            $table->string('nama_asli', 100);
            $table->string('gelar_belakang', 20)->nullable();
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatans')->onDelete('set null');
            $table->foreignId('prodi_id')->nullable()->constrained('prodi')->onDelete('set null');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('no_hp', 15)->nullable();
            $table->string('photo')->nullable(); // Foto User (Profile)
            $table->string('signature_path')->nullable(); // Tanda Tangan User
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
