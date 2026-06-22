<?php

use App\Models\Organization;
use App\Models\User;
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
            $table->foreignId('organization_id')->nullable()->after('user_type')->constrained()->onDelete('set null');
        });

        // Data Migration: Link existing organization users to their organizations based on email
        $users = User::where('user_type', 'organization')->get();
        foreach ($users as $user) {
            $organization = Organization::where('contact_email', $user->email)->first();
            if ($organization) {
                $user->organization_id = $organization->id;
                $user->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
