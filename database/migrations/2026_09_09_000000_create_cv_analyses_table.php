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
        Schema::create('cv_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('guest_token', 64)->unique()->index();
            $table->string('original_filename', 255);
            $table->string('file_path', 500);
            $table->unsignedInteger('file_size');
            $table->string('mime_type', 100);
            $table->string('candidate_name', 255)->nullable();
            $table->string('candidate_title', 255)->nullable();
            $table->json('candidate_contact')->nullable();
            $table->json('parsed_content')->nullable();
            $table->unsignedTinyInteger('global_score')->default(0);
            $table->string('status_label', 100)->default('À évaluer');
            $table->json('criteria_scores')->nullable();
            $table->json('strengths')->nullable();
            $table->json('improvements')->nullable();
            $table->json('recommendations')->nullable();
            $table->text('summary')->nullable();
            $table->boolean('is_claimed')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cv_analyses');
    }
};
