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
        Schema::table('messages', function (Blueprint $table) {
            $table->text('original_body')->nullable()->after('body');
            $table->boolean('is_flagged')->default(false)->after('original_body');
            $table->string('flag_reason')->nullable()->after('is_flagged');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->text('original_content')->nullable()->after('content');
            $table->boolean('is_flagged')->default(false)->after('original_content');
            $table->string('flag_reason')->nullable()->after('is_flagged');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['original_body', 'is_flagged', 'flag_reason']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['original_content', 'is_flagged', 'flag_reason']);
        });
    }
};
