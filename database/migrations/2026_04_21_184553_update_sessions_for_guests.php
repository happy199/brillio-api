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
        Schema::table('mentoring_sessions', function (Blueprint $table) {
            $table->string('guest_token', 64)->nullable()->unique()->after('meeting_link');
            $table->foreignId('scheduled_by_organization_id')->nullable()->constrained('organizations')->onDelete('set null')->after('guest_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentoring_sessions', function (Blueprint $table) {
            $table->dropForeign(['scheduled_by_organization_id']);
            $table->dropColumn(['guest_token', 'scheduled_by_organization_id']);
        });
    }
};
