<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds `member_limit` to subscription plans.
     * null = unlimited (establishment plan)
     */
    public function up(): void
    {
        Schema::table('credit_packs', function (Blueprint $table) {
            $table->unsignedInteger('member_limit')->nullable()->after('target_plan')
                ->comment('Max members (jeunes+mentors) allowed. null = unlimited.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_packs', function (Blueprint $table) {
            $table->dropColumn('member_limit');
        });
    }
};
