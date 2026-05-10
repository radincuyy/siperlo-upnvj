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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('organizer');
            $table->string('category');
            $table->string('type')->nullable();
            $table->dateTime('registration_deadline');
            $table->dateTime('event_start')->nullable();
            $table->dateTime('event_end')->nullable();
            $table->string('location')->nullable();
            $table->decimal('fee', 12, 2)->default(0);
            $table->string('poster_image')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
