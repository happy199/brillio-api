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
        Schema::create('personality_questions', function (Blueprint $table) {
            $table->id();
            $table->integer('openmbti_id')->unique()->comment('ID from OpenMBTI API');
            $table->string('dimension', 2)->comment('EI, SN, TF, or JP');
            $table->string('left_trait_en')->comment('Left trait in English');
            $table->string('left_trait_fr')->comment('Left trait in French');
            $table->string('right_trait_en')->comment('Right trait in English');
            $table->string('right_trait_fr')->comment('Right trait in French');
            $table->timestamps();

            $table->index('dimension');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personality_questions');
    }
};
