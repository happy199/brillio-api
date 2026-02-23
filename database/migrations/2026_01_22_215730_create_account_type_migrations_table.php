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
        Schema::create('account_type_migrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('old_type'); // 'jeune' ou 'mentor'
            $table->string('new_type'); // 'mentor' ou 'jeune'
            $table->string('token')->unique(); // Token de confirmation unique
            $table->json('oauth_data')->nullable(); // Données OAuth temporaires
            $table->timestamp('expires_at'); // Expiration du token (24h)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_type_migrations');
    }
};
