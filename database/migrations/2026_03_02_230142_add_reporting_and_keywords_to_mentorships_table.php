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
        Schema::table('mentorships', function (Blueprint $table) {
            $table->json('custom_forbidden_keywords')->nullable()->after('diction_reason');
            $table->timestamp('reported_at')->nullable()->after('custom_forbidden_keywords');
            $table->foreignId('reported_by_id')->nullable()->constrained('users')->onDelete('SET NULL')->after('reported_at');
            $table->text('report_reason')->nullable()->after('reported_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentorships', function (Blueprint $table) {
            $table->dropColumn(['custom_forbidden_keywords', 'reported_at', 'reported_by_id', 'report_reason']);
        });
    }
};
