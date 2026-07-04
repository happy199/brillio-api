<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE resources MODIFY COLUMN type ENUM('article', 'video', 'tool', 'exercise', 'template', 'script', 'advertisement', 'book', 'podcast', 'webinar', 'guide', 'case_study', 'course') NOT NULL DEFAULT 'article'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE resources MODIFY COLUMN type ENUM('article', 'video', 'tool', 'exercise', 'template', 'script', 'advertisement') NOT NULL DEFAULT 'article'");
        }
    }
};
