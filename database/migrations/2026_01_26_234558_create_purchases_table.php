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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('item'); // item_type, item_id (ex: App\Models\Resource)
            $table->integer('cost_credits');
            $table->integer('original_price_fcfa')->nullable();
            $table->timestamp('purchased_at');
            $table->timestamps();

            // Un utilisateur ne peut acheter le même item qu'une fois
            $table->unique(['user_id', 'item_type', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
