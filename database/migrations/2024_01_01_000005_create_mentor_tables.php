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
        // Table des profils mentors
        Schema::create('mentor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('bio')->nullable();
            $table->string('current_position', 255)->nullable();
            $table->string('current_company', 255)->nullable();
            $table->unsignedSmallInteger('years_of_experience')->nullable();
            $table->string('specialization', 50)->nullable();
            $table->json('linkedin_profile_data')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            // Un seul profil mentor par utilisateur
            $table->unique('user_id');
            $table->index('specialization');
            $table->index('is_published');
        });

        // Table des étapes du parcours
        Schema::create('roadmap_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_profile_id')->constrained()->onDelete('cascade');
            $table->enum('step_type', ['education', 'work', 'certification', 'achievement']);
            $table->string('title', 255);
            $table->string('institution_company', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['mentor_profile_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roadmap_steps');
        Schema::dropIfExists('mentor_profiles');
    }
};
