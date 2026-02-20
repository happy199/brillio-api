<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing organizations that have the old default green back to Brillio Red
        DB::table('organizations')
            ->where('primary_color', '#10b981')
            ->update(['primary_color' => '#f43f5e']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: revert back to green if needed, but usually not recommended for brand fixes
        DB::table('organizations')
            ->where('primary_color', '#f43f5e')
            ->update(['primary_color' => '#10b981']);
    }
};