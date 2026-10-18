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
        // 1. Profil Enfant (Avatar, Âge, Clé de liaison Parent)
        Schema::create('kids_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->integer('age')->default(6);
            $table->string('avatar_key')->default('avatarHero');
            $table->string('favorite_color')->default('#4f46e5');
            $table->integer('stars_count')->default(280);
            $table->integer('gems_count')->default(14);
            $table->integer('streak_days')->default(5);
            $table->integer('current_world')->default(1);
            $table->integer('current_step')->default(2);
            $table->timestamps();
        });

        // 2. Sessions de Mini-Jeux & Télémétrie Cognitive
        Schema::create('kids_game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kids_profile_id')->constrained('kids_profiles')->onDelete('cascade');
            $table->string('game_id'); // echo_tambour, reflexe_guepard, architecte_ombres, cristaux_algo
            $table->string('dimension'); // Musicale, Kinesthésique, Spatiale, Logique
            $table->integer('score')->default(100);
            $table->float('accuracy_rate')->default(94.0); // Précision temporelle ou spatiale
            $table->integer('reaction_time_ms')->nullable(); // Ex: 412ms pour Guépard
            $table->integer('rotations_count')->nullable(); // Ex: 4 pour Architecte
            $table->json('telemetry_data')->nullable(); // Vecteurs bruts
            $table->boolean('completed')->default(true);
            $table->timestamps();
        });

        // 3. Scores de Talents Gardner 8D (Agrégats pondérés R&D)
        Schema::create('kids_talent_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kids_profile_id')->constrained('kids_profiles')->onDelete('cascade');
            $table->string('dimension'); // musical, kinesthetic, spatial, logical, linguistic, natural, interpersonal, intrapersonal
            $table->float('score_percent')->default(50.0);
            $table->float('confidence_index')->default(80.0); // Indice de confiance R&D
            $table->timestamps();
        });

        // 4. Micro-Journaling des Parents (Observations concrètes maison)
        Schema::create('kids_parent_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kids_profile_id')->constrained('kids_profiles')->onDelete('cascade');
            $table->string('question_key'); // fredonne_rythme, parcours_moteur, etc.
            $table->string('answer'); // souvent, parfois, jamais
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kids_parent_observations');
        Schema::dropIfExists('kids_talent_scores');
        Schema::dropIfExists('kids_game_sessions');
        Schema::dropIfExists('kids_profiles');
    }
};
