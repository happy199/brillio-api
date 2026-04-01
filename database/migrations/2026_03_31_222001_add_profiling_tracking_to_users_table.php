<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_feedback_at')->nullable()->after('last_engagement_email_sent_at');
            $table->timestamp('last_situation_update_at')->nullable()->after('last_feedback_at');
            $table->integer('last_rating')->nullable()->after('last_situation_update_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_feedback_at', 'last_situation_update_at', 'last_rating']);
        });
    }
};
