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
        Schema::table('mentor_profiles', function (Blueprint $table) {
            $table->decimal('available_balance', 10, 2)->default(0); // Solde disponible pour retrait
            $table->decimal('total_withdrawn', 10, 2)->default(0); // Total des retraits effectués
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentor_profiles', function (Blueprint $table) {
            $table->dropColumn(['available_balance', 'total_withdrawn']);
        });
    }
};
