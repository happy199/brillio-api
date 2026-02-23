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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sponsored_by_organization_id')->nullable()->constrained('organizations')->onDelete('set null');
            $table->string('referral_code_used', 8)->nullable();

            $table->index('sponsored_by_organization_id');
            $table->index('referral_code_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sponsored_by_organization_id']);
            $table->dropColumn(['sponsored_by_organization_id', 'referral_code_used']);
        });
    }
};
