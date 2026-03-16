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
        Schema::create('scheduled_task_logs', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->string('status');
            $table->decimal('duration', 8, 2)->nullable();
            $table->longText('output')->nullable();
            $table->timestamp('run_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_task_logs');
    }
};
