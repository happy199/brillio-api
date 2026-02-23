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
        Schema::table('credit_packs', function (Blueprint $table) {
            // Modify user_type enum to include 'organization' - using string for flexibility
            $table->string('user_type')->change();

            // Add subscription related columns
            $table->enum('type', ['credits', 'subscription'])->default('credits')->after('user_type');
            $table->integer('duration_days')->nullable()->after('credits'); // For subscriptions
            $table->string('target_plan')->nullable()->after('duration_days'); // 'pro', 'enterprise'

            // Add features column for dynamic benefits
            $table->json('features')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_packs', function (Blueprint $table) {
            $table->dropColumn(['type', 'duration_days', 'target_plan', 'features']);
            // Reverting user_type to enum is complex without raw SQL, skipping for now
        });
    }
};
