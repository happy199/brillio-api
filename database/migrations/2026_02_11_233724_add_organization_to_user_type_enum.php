<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Modify user_type ENUM to include 'organization'
            DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('jeune', 'mentor', 'organization') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Remove 'organization' from ENUM (be careful: this will fail if any users have type 'organization')
            DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('jeune', 'mentor') NOT NULL");
        }
    }
};