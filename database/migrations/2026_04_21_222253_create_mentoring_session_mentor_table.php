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
        Schema::create('mentoring_session_mentor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_session_id')->constrained('mentoring_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // S'assurer qu'un mentor n'est pas ajouté deux fois à la même séance
            $table->unique(['mentoring_session_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentoring_session_mentor');
    }
};
