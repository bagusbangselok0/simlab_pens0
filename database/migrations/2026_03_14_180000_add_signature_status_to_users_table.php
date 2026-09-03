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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('signature_status', ['none', 'pending', 'approved', 'rejected'])
                ->default('none')
                ->after('signature_path');
            $table->text('signature_rejection_note')->nullable()->after('signature_status');
            $table->timestamp('signature_verified_at')->nullable()->after('signature_rejection_note');
            $table->foreignId('signature_verified_by')->nullable()->after('signature_verified_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['signature_verified_by']);
            $table->dropColumn([
                'signature_status',
                'signature_rejection_note',
                'signature_verified_at',
                'signature_verified_by',
            ]);
        });
    }
};
