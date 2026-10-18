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
        Schema::table('email_suppressions', function (Blueprint $table) {
            if (! Schema::hasColumn('email_suppressions', 'bounce_count')) {
                $table->unsignedInteger('bounce_count')->default(1)->after('source');
            }
            if (! Schema::hasColumn('email_suppressions', 'bounce_type')) {
                $table->string('bounce_type')->default('hard')->after('bounce_count'); // hard, soft, preflight_dns, manual
            }
            if (! Schema::hasColumn('email_suppressions', 'last_bounce_at')) {
                $table->timestamp('last_bounce_at')->nullable()->after('bounce_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_suppressions', function (Blueprint $table) {
            $table->dropColumn(['bounce_count', 'bounce_type', 'last_bounce_at']);
        });
    }
};
