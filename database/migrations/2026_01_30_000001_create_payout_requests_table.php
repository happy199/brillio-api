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
        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_profile_id')->constrained('mentor_profiles')->onDelete('cascade');
            $table->decimal('amount', 10, 2); // Montant demandé en FCFA
            $table->decimal('fee', 10, 2)->default(0); // Frais de retrait
            $table->decimal('net_amount', 10, 2); // Montant net (amount - fee)
            $table->string('payment_method'); // ex: mtn_bj, moov_bj, wave_sn
            $table->string('phone_number'); // Numéro de téléphone du bénéficiaire
            $table->string('moneroo_payout_id')->nullable(); // ID du payout Moneroo
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable(); // Message d'erreur en cas d'échec
            $table->timestamp('processed_at')->nullable(); // Date de début de traitement
            $table->timestamp('completed_at')->nullable(); // Date de complétion
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index('mentor_profile_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
    }
};
