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
        Schema::create('advisor_video_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('counselor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('initiated_by', ['jeune', 'counselor'])->default('jeune');
            $table->enum('status', ['pending_acceptance', 'accepted', 'refused', 'completed', 'cancelled'])->default('pending_acceptance');
            $table->integer('credits_cost')->default(50);
            $table->string('meeting_id')->unique();
            $table->json('transcription_raw')->nullable();
            $table->text('ai_summary')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advisor_video_calls');
    }
};
