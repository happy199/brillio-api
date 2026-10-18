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
        Schema::create('cron_automations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->string('name');
            $table->string('category')->default('emails'); // emails, mentorship, security, organizations, system
            $table->text('description');
            $table->string('frequency_label');
            $table->text('target');
            $table->string('action_type')->default('job'); // job or command
            $table->string('action_class');
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_run_status')->nullable(); // success, failed, paused
            $table->float('last_run_duration')->nullable();
            $table->text('last_run_output')->nullable();
            $table->unsignedInteger('total_runs')->default(0);
            $table->unsignedInteger('total_failures')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_automations');
    }
};
