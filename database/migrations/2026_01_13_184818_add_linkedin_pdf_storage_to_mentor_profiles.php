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
        Schema::table('mentor_profiles', function (Blueprint $table) {
            $table->string('linkedin_pdf_path')->nullable();
            $table->string('linkedin_pdf_original_name')->nullable();
            $table->integer('linkedin_import_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentor_profiles', function (Blueprint $table) {
            $table->dropColumn(['linkedin_pdf_path', 'linkedin_pdf_original_name', 'linkedin_import_count']);
        });
    }
};
