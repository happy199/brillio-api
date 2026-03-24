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
            $table->longText('transcription_raw')->nullable();
            $table->longText('transcription_summary')->nullable();
            $table->boolean('has_transcription')->default(false);
            $table->string('transcription_file_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentoring_sessions', function (Blueprint $table) {
            $table->dropColumn(['transcription_raw', 'transcription_summary', 'has_transcription', 'transcription_file_path']);
        });
    }
};
