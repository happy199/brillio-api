<?php

use Database\Seeders\CareerSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enforce seeding of careers data
        Artisan::call('db:seed', [
            '--class' => CareerSeeder::class,
            '--force' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On ne supprime pas les métiers en cas de rollback de cette migration spécifique
        // car ils pourraient être rattachés à des données utilisateurs.
    }
};
