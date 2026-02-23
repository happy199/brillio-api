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
            $table->boolean('is_archived')->default(false)->after('email_verified_at');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->text('archived_reason')->nullable()->after('archived_at');

            // Index for performance when filtering archived users
            $table->index('is_archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_archived']);
            $table->dropColumn(['is_archived', 'archived_at', 'archived_reason']);
        });
    }
};
