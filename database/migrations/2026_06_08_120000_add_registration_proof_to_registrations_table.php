<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('registration_proof_file')->nullable()->after('notes');
            $table->string('proof_status')->nullable()->after('registration_proof_file');
            $table->string('proof_admin_notes')->nullable()->after('proof_status');
            $table->timestamp('proof_verified_at')->nullable()->after('proof_admin_notes');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'registration_proof_file',
                'proof_status',
                'proof_admin_notes',
                'proof_verified_at',
            ]);
        });
    }
};
