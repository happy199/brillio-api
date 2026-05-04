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
        Schema::table('users', function (Blueprint $table) {
            $table->string('timezone')->nullable()->after('email')->default('Africa/Porto-Novo');
        });

        Schema::table('mentoring_sessions', function (Blueprint $table) {
            $table->string('timezone')->nullable()->after('scheduled_at')->default('Africa/Porto-Novo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });

        Schema::table('mentoring_sessions', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
