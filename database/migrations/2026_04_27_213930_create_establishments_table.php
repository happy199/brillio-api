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
        Schema::create('establishments', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->string('slug')->unique();
            $blueprint->string('type')->default('university'); // university, training_center, etc.
            $blueprint->string('photo_path')->nullable();
            $blueprint->string('country')->default('Bénin');
            $blueprint->string('city')->nullable();
            
            $blueprint->text('description')->nullable();
            $blueprint->string('email')->nullable();
            $blueprint->string('phone')->nullable();
            $blueprint->string('address')->nullable();
            $blueprint->string('website_url')->nullable();
            
            $blueprint->json('social_links')->nullable(); // facebook, linkedin, etc.
            $blueprint->json('mbti_types')->nullable(); // Target MBTI profiles
            $blueprint->json('sectors')->nullable(); // Target sectors (IT, Health, etc.)
            
            $blueprint->decimal('tuition_min', 12, 2)->nullable();
            $blueprint->decimal('tuition_max', 12, 2)->nullable();
            
            $blueprint->json('presentation_videos')->nullable(); // Array of URLs
            $blueprint->json('brochures')->nullable(); // Array of file paths with titles
            
            $blueprint->boolean('has_precise_form')->default(false);
            $blueprint->json('precise_form_config')->nullable(); // Dynamically defined fields
            
            $blueprint->boolean('is_published')->default(true);
            $blueprint->foreignId('organization_id')->nullable()->constrained()->onDelete('set null');
            
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('establishments');
    }
};
