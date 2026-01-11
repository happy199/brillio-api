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
        Schema::create('academic_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('document_type', ['bulletin', 'releve_notes', 'diplome', 'autre']);
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->unsignedInteger('file_size'); // Taille en bytes
            $table->string('mime_type', 100)->nullable();
            $table->string('academic_year', 20)->nullable(); // Ex: 2023-2024
            $table->string('grade_level', 50)->nullable(); // Classe: Terminale, Licence 3, etc.
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_documents');
    }
};
