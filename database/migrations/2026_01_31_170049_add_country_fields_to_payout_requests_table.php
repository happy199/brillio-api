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
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->string('country_code', 5)->nullable()->after('phone_number'); // ex: "BJ", "SN"
            $table->string('dial_code', 10)->nullable()->after('country_code'); // ex: "+229", "+221"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'dial_code']);
        });
    }
};
