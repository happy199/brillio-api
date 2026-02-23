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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Content
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->enum('type', ['article', 'video', 'tool', 'exercise', 'template', 'script'])->default('article');
            $table->string('file_path')->nullable();
            $table->string('preview_image_path')->nullable();

            // Monetization
            $table->integer('price')->default(0); // 0 = Free
            $table->boolean('is_premium')->default(false);

            // Workflow
            $table->boolean('is_published')->default(false);
            $table->boolean('is_validated')->default(false);
            $table->timestamp('validated_at')->nullable();

            // Metadata
            $table->json('metadata')->nullable(); // Langue, Pays, Niveau, Durée
            $table->json('mbti_types')->nullable(); // Types ciblés
            $table->json('tags')->nullable(); // Thématiques

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
