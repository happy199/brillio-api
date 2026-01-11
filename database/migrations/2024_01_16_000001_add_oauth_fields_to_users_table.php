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
            // OAuth fields
            if (!Schema::hasColumn('users', 'auth_provider')) {
                $table->string('auth_provider')->default('email')->after('password');
            }
            if (!Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable()->after('auth_provider');
            }
            if (!Schema::hasColumn('users', 'profile_photo_url')) {
                $table->string('profile_photo_url')->nullable()->after('provider_id');
            }
            if (!Schema::hasColumn('users', 'onboarding_completed')) {
                $table->boolean('onboarding_completed')->default(false)->after('profile_photo_url');
            }
            if (!Schema::hasColumn('users', 'onboarding_data')) {
                $table->json('onboarding_data')->nullable()->after('onboarding_completed');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('onboarding_data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'auth_provider',
                'provider_id',
                'profile_photo_url',
                'onboarding_completed',
                'onboarding_data',
                'last_login_at',
            ]);
        });
    }
};
