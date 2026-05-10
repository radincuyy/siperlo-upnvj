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
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('result_status')->nullable();
            $table->text('result_description')->nullable();
            $table->string('result_proof_file')->nullable();
            $table->timestamp('result_submitted_at')->nullable();
            $table->timestamp('result_reviewed_at')->nullable();
            $table->text('result_admin_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'result_status',
                'result_description',
                'result_proof_file',
                'result_submitted_at',
                'result_reviewed_at',
                'result_admin_notes',
            ]);
        });
    }
};
