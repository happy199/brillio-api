<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Utilisation de DB::statement car modifier un ENUM via Schema builder est complexe/limité
            DB::statement("ALTER TABLE resources MODIFY COLUMN type ENUM('article', 'video', 'tool', 'exercise', 'template', 'script', 'advertisement') NOT NULL DEFAULT 'article'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Revert (attention si des 'advertisement' existent, ils seront problématiques)
            DB::statement("ALTER TABLE resources MODIFY COLUMN type ENUM('article', 'video', 'tool', 'exercise', 'template', 'script') NOT NULL DEFAULT 'article'");
        }
    }
};
