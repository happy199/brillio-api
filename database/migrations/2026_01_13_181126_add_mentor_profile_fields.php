<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mentor_profiles', function (Blueprint $table) {
            $table->json('skills')->nullable();
            $table->timestamp('linkedin_imported_at')->nullable();
            $table->json('linkedin_raw_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentor_profiles', function (Blueprint $table) {
            $table->dropColumn(['skills', 'linkedin_imported_at', 'linkedin_raw_data']);
        });
    }
};
