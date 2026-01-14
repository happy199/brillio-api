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
        Schema::table('mentor_profiles', function (Blueprint $table) {
            // Ajouter la nouvelle colonne
            $table->foreignId('specialization_id')->nullable()->after('specialization')->constrained()->onDelete('set null');

            // Garder l'ancienne colonne temporairement pour migration de données
            // Elle sera supprimée dans une migration ultérieure après migration des données
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentor_profiles', function (Blueprint $table) {
            $table->dropForeign(['specialization_id']);
            $table->dropColumn('specialization_id');
        });
    }
};
