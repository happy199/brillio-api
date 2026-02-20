<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('academic_documents', function (Blueprint $table) {
            // Changement de ENUM à STRING pour plus de flexibilité
            $table->string('document_type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_documents', function (Blueprint $table) {
        // On ne peut pas facilement revenir à un ENUM strict sans risque de perte de données
        });
    }
};