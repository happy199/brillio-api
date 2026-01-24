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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('amount'); // Positive for deposit, negative for spend
            $table->string('type'); // purchase, expense, bonus, refund, coupon
            $table->string('description')->nullable();

            // Polymorphic relation for what this transaction is related to (e.g. Coupon, Payment, Resource)
            $table->nullableMorphs('related');

            $table->timestamps();

            // Index for faster history lookup
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
