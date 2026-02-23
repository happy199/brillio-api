<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed initial values
        DB::table('system_settings')->insert([
            [
                'key' => 'credit_price',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Prix d\'un crédit en FCFA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'feature_cost_advanced_targeting',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Coût en crédits pour utiliser le ciblage avancé',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
