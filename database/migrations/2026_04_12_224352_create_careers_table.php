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
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->text('description');
            $table->string('future_prospects')->nullable(); // Impact IA / Avenir
            $table->text('african_context')->nullable();
            $table->string('ai_impact_level')->nullable(); // low, medium, high
            $table->string('demand_level')->nullable(); // e.g., high_demand
            $table->timestamps();
        });

        Schema::create('career_mbti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained()->cascadeOnDelete();
            $table->string('mbti_type', 4);
            $table->text('match_reason')->nullable();
            $table->timestamps();

            $table->unique(['career_id', 'mbti_type']);
        });

        Schema::create('career_sector', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained()->cascadeOnDelete();
            $table->string('sector_code');
            $table->timestamps();

            $table->unique(['career_id', 'sector_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_sector');
        Schema::dropIfExists('career_mbti');
        Schema::dropIfExists('careers');
    }
};
