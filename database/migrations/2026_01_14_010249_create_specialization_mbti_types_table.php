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
        Schema::create('specialization_mbti_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialization_id')->constrained()->onDelete('cascade');
            $table->string('mbti_type_code', 50); // Ex: 'INTJ', 'tech', 'finance', 'engineering'
            $table->timestamps();

            $table->unique(['specialization_id', 'mbti_type_code'], 'spec_mbti_unique');
            $table->index('mbti_type_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specialization_mbti_types');
    }
};
