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
        Schema::create('personality_compatibilities', function (Blueprint $table) {
            $table->id();
            $table->string('type_a', 4); // Ex: INTJ
            $table->string('type_b', 4); // Ex: ENFP
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['type_a', 'type_b']);
            $table->index('type_a');
            $table->index('type_b');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personality_compatibilities');
    }
};
