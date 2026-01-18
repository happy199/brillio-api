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
        Schema::table('mentor_profiles', function (Blueprint $table) {
            $table->boolean('is_validated')->default(false)->after('is_published');
            $table->timestamp('validated_at')->nullable()->after('is_validated');
            $table->integer('profile_views')->default(0)->after('validated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentor_profiles', function (Blueprint $table) {
            $table->dropColumn(['is_validated', 'validated_at', 'profile_views']);
        });
    }
};
