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
        if (! Schema::hasTable('jeune_profiles')) {
            Schema::create('jeune_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('bio')->nullable();
                $table->string('portfolio_url')->nullable();
                $table->string('cv_path')->nullable();
                $table->boolean('is_public')->default(false);
                $table->string('public_slug')->unique()->nullable();
                $table->integer('profile_views')->default(0);
                $table->integer('mentor_views')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jeune_profiles');
    }
};
