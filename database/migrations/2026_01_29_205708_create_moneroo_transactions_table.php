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
        Schema::create('moneroo_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_type'); // 'App\Models\Jeune' or 'App\Models\Mentor'
            $table->string('moneroo_transaction_id')->unique()->nullable();
            $table->decimal('amount', 10, 2); // Amount in XOF
            $table->string('currency', 3)->default('XOF');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->integer('credits_amount'); // Number of credits purchased
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'user_type']);
            $table->index('moneroo_transaction_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moneroo_transactions');
    }
};
