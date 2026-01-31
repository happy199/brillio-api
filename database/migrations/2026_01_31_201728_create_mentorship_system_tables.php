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
        // 1. Mentorships (Relationships)
        Schema::create('mentorships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mentee_id')->constrained('users')->onDelete('cascade');

            // Status: pending, accepted, refused, disconnected
            $table->string('status')->default('pending');

            $table->text('request_message')->nullable();
            $table->text('refusal_reason')->nullable();
            $table->text('diction_reason')->nullable(); // For disconnection

            $table->timestamps();

            // Ensure unique pair to avoid duplicate active requests? 
            // Maybe not unique if they reconnect later, but usually unique active.
            // For now, index them for speed.
            $table->index(['mentor_id', 'mentee_id']);
        });

        // 2. Mentor Availabilities
        Schema::create('mentor_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('users')->onDelete('cascade');

            // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            $table->integer('day_of_week');

            $table->time('start_time');
            $table->time('end_time');

            $table->timestamps();
        });

        // 3. Mentoring Sessions
        Schema::create('mentoring_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('users')->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();

            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(60);

            $table->boolean('is_paid')->default(false);
            $table->decimal('price', 10, 2)->nullable();

            // proposed, pending_payment, confirmed, cancelled, completed
            $table->string('status')->default('proposed');

            $table->text('cancel_reason')->nullable();
            $table->string('meeting_link')->nullable();

            // JSON column for the 3-part report
            $table->json('report_content')->nullable();

            // mentor or mentee (if feature allowed later, for now mainly mentor)
            $table->string('created_by')->default('mentor');

            $table->timestamps();
        });

        // 4. Session Participants (Pivot)
        Schema::create('mentoring_session_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_session_id')->constrained('mentoring_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // The mentee

            // pending, accepted, rejected, paid (if separate status needed)
            // Usually 'accepted' implies paid if verified elsewhere.
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            // Unique participant per session
            $table->unique(['mentoring_session_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentoring_session_user');
        Schema::dropIfExists('mentoring_sessions');
        Schema::dropIfExists('mentor_availabilities');
        Schema::dropIfExists('mentorships');
    }
};
