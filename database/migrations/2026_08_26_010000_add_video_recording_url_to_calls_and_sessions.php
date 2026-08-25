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
        if (Schema::hasTable('advisor_video_calls')) {
            Schema::table('advisor_video_calls', function (Blueprint $table) {
                if (! Schema::hasColumn('advisor_video_calls', 'video_recording_url')) {
                    $table->text('video_recording_url')->nullable()->after('meeting_id');
                }
            });
        }

        if (Schema::hasTable('mentoring_sessions')) {
            Schema::table('mentoring_sessions', function (Blueprint $table) {
                if (! Schema::hasColumn('mentoring_sessions', 'video_recording_url')) {
                    $table->text('video_recording_url')->nullable()->after('meeting_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('advisor_video_calls')) {
            Schema::table('advisor_video_calls', function (Blueprint $table) {
                if (Schema::hasColumn('advisor_video_calls', 'video_recording_url')) {
                    $table->dropColumn('video_recording_url');
                }
            });
        }

        if (Schema::hasTable('mentoring_sessions')) {
            Schema::table('mentoring_sessions', function (Blueprint $table) {
                if (Schema::hasColumn('mentoring_sessions', 'video_recording_url')) {
                    $table->dropColumn('video_recording_url');
                }
            });
        }
    }
};
