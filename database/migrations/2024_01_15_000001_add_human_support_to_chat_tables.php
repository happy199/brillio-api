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
        // Ajouter les champs de support humain à chat_conversations
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->boolean('needs_human_support')->default(false)->after('title');
            $table->boolean('human_support_active')->default(false)->after('needs_human_support');
            $table->foreignId('human_support_admin_id')->nullable()->after('human_support_active')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('human_support_started_at')->nullable()->after('human_support_admin_id');
            $table->timestamp('human_support_ended_at')->nullable()->after('human_support_started_at');
        });

        // Ajouter les champs pour identifier les messages humains
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->boolean('is_from_human')->default(false)->after('role');
            $table->boolean('is_system_message')->default(false)->after('is_from_human');
            $table->foreignId('admin_id')->nullable()->after('is_system_message')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropForeign(['human_support_admin_id']);
            $table->dropColumn([
                'needs_human_support',
                'human_support_active',
                'human_support_admin_id',
                'human_support_started_at',
                'human_support_ended_at',
            ]);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn(['is_from_human', 'is_system_message', 'admin_id']);
        });
    }
};
