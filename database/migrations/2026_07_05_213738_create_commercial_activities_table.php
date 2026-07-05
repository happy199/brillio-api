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
        Schema::create('commercial_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commercial_id')->constrained('users')->onDelete('cascade');
            $table->morphs('assignable');
            $table->string('status')->default('active'); // active, closed
            $table->text('summary')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commercial_activities');
    }
};
