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
        // 1. Evaluations table for Mentees rating Mentors after sessions
        Schema::create('mentoring_session_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_session_id')->constrained('mentoring_sessions')->onDelete('cascade');
            $table->foreignId('mentee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mentor_id')->constrained('users')->onDelete('cascade');

            $table->unsignedTinyInteger('rating'); // 1 to 5 stars
            $table->text('comment')->nullable(); // Youth feedback on session & mentor relevance

            $table->timestamps();

            // Unique evaluation per mentee per session (custom name to fit MySQL 64 char limit)
            $table->unique(['mentoring_session_id', 'mentee_id'], 'session_mentee_eval_unique');
        });

        // 2. Add columns to mentoring_sessions for first session flag and admin quality reviews
        Schema::table('mentoring_sessions', function (Blueprint $table) {
            $table->boolean('is_first_session')->default(false)->after('scheduled_by_organization_id');
            $table->text('admin_observation')->nullable()->after('is_first_session');
            $table->string('admin_evaluation_status')->default('pending')->after('admin_observation'); // pending, reviewed, warning_issued, terminated
            $table->timestamp('admin_reviewed_at')->nullable()->after('admin_evaluation_status');
            $table->foreignId('admin_reviewer_id')->nullable()->constrained('users')->nullOnDelete()->after('admin_reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentoring_sessions', function (Blueprint $table) {
            $table->dropForeign(['admin_reviewer_id']);
            $table->dropColumn([
                'is_first_session',
                'admin_observation',
                'admin_evaluation_status',
                'admin_reviewed_at',
                'admin_reviewer_id',
            ]);
        });

        Schema::dropIfExists('mentoring_session_evaluations');
    }
};
