<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->string('guidebook_file')->nullable()->after('poster_image');
            $table->string('contact_person_name')->nullable()->after('guidebook_file');
            $table->string('contact_person_phone')->nullable()->after('contact_person_name');
            $table->string('contact_person_email')->nullable()->after('contact_person_phone');
            $table->string('official_website')->nullable()->after('contact_person_email');
            $table->string('social_media')->nullable()->after('official_website');
            $table->string('external_registration_url')->nullable()->after('social_media');
            $table->text('requirements')->nullable()->after('external_registration_url');
            $table->text('benefits')->nullable()->after('requirements');
            $table->text('timeline')->nullable()->after('benefits');
        });
    }

    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn([
                'guidebook_file',
                'contact_person_name',
                'contact_person_phone',
                'contact_person_email',
                'official_website',
                'social_media',
                'external_registration_url',
                'requirements',
                'benefits',
                'timeline',
            ]);
        });
    }
};
