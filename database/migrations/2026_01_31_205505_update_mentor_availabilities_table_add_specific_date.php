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
        Schema::table('mentor_availabilities', function (Blueprint $table) {
            $table->date('specific_date')->nullable()->after('day_of_week');
            $table->boolean('is_recurring')->default(true)->after('specific_date');
            $table->integer('day_of_week')->nullable()->change(); // Make nullable for one-off dates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentor_availabilities', function (Blueprint $table) {
            $table->dropColumn(['specific_date', 'is_recurring']);
            $table->integer('day_of_week')->nullable(false)->change();
        });
    }
};
