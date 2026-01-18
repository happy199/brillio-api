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
        if (Schema::hasTable('mentor_profiles') && !Schema::hasColumn('mentor_profiles', 'public_slug')) {
            Schema::table('mentor_profiles', function (Blueprint $table) {
                $table->string('public_slug')->unique()->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mentor_profiles') && Schema::hasColumn('mentor_profiles', 'public_slug')) {
            Schema::table('mentor_profiles', function (Blueprint $table) {
                $table->dropColumn('public_slug');
            });
        }
    }
};
