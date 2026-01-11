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
        Schema::create('personality_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('test_type', 50)->default('openmbti');
            $table->json('raw_responses')->nullable(); // Toutes les réponses au test
            $table->string('personality_type', 10)->nullable(); // Ex: INTJ, ENFP
            $table->string('personality_label', 100)->nullable(); // Ex: Le Logicien
            $table->text('personality_description')->nullable();
            $table->json('traits_scores')->nullable(); // Scores détaillés par trait
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Un seul test par utilisateur
            $table->unique('user_id');
            $table->index('personality_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personality_tests');
    }
};
