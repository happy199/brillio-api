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
        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('referral_code_used', 8)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'organization_id']);
        });

        // Data Migration: Link existing sponsored users to their organizations
        // Use DB facade to avoid dependency on models during migration
        $users = \Illuminate\Support\Facades\DB::table('users')
            ->whereNotNull('sponsored_by_organization_id')
            ->get();

        foreach ($users as $user) {
            \Illuminate\Support\Facades\DB::table('organization_user')->insert([
                'user_id' => $user->id,
                'organization_id' => $user->sponsored_by_organization_id,
                'referral_code_used' => $user->referral_code_used,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_user');
    }
};