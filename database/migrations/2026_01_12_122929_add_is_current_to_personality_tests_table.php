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
        Schema::table('personality_tests', function (Blueprint $table) {
            $table->boolean('is_current')->default(true)->after('completed_at');
            $table->index(['user_id', 'is_current']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personality_tests', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_current']);
            $table->dropColumn('is_current');
        });
    }
};
