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
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('subscription_plan')->default('free')->after('status'); // free, pro, enterprise
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_plan');
            $table->boolean('auto_renew')->default(false)->after('subscription_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['subscription_plan', 'subscription_expires_at', 'auto_renew']);
        });
    }
};
