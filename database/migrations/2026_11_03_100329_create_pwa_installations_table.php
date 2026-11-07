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
        Schema::create('pwa_installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_uuid', 64)->index()->comment('Identifiant anonyme persistant côté client');
            $table->string('platform', 32)->default('unknown')->index()->comment('android, ios, desktop, unknown');
            $table->string('browser', 64)->nullable();
            $table->string('user_agent')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('installed_at')->nullable()->index();
            $table->timestamp('last_active_at')->nullable()->index();
            $table->unsignedInteger('launches_count')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pwa_installations');
    }
};
