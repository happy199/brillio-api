<?php

namespace App\Jobs;

use App\Models\PayoutRequest;
use App\Services\MonerooService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPayoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 120, 300]; // Retry après 1min, 2min, 5min

    protected PayoutRequest $payoutRequest;

    /**
     * Create a new job instance.
     */
    public function __construct(PayoutRequest $payoutRequest)
    {
        $this->payoutRequest = $payoutRequest;
    }

    /**
     * Execute the job.
     */
    public function handle(MonerooService $monerooService): void
    {
        try {
            Log::info('ProcessPayoutJob: Starting payout processing', [
                'payout_id' => $this->payoutRequest->id,
                'amount' => $this->payoutRequest->net_amount
            ]);

            // Mettre à jour le statut en processing
            $this->payoutRequest->update([
                'status' => PayoutRequest::STATUS_PROCESSING,
                'processed_at' => now()
            ]);

            // Créer le payout avec Moneroo
            $response = $monerooService->createPayout(
                (float) $this->payoutRequest->net_amount,
                $this->payoutRequest->phone_number,
                $this->payoutRequest->payment_method
            );

            if ($response['success']) {
                // Payout réussi
                $this->payoutRequest->update([
                    'status' => PayoutRequest::STATUS_COMPLETED,
                    'moneroo_payout_id' => $response['data']['data']['id'] ?? null,
                    'completed_at' => now()
                ]);

                // Mettre à jour le total withdrawn du mentor
                $this->payoutRequest->mentorProfile->increment('total_withdrawn', $this->payoutRequest->amount);

                Log::info('ProcessPayoutJob: Payout completed successfully', [
                    'payout_id' => $this->payoutRequest->id,
                    'moneroo_payout_id' => $response['data']['data']['id'] ?? null
                ]);
            } else {
                // Payout échoué
                throw new \Exception($response['message'] ?? 'Payout failed');
            }
        } catch (\Exception $e) {
            Log::error('ProcessPayoutJob: Payout failed', [
                'payout_id' => $this->payoutRequest->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // Si c'est la dernière tentative, marquer comme failed
            if ($this->attempts() >= $this->tries) {
                $this->payoutRequest->update([
                    'status' => PayoutRequest::STATUS_FAILED,
                    'error_message' => $e->getMessage()
                ]);

                // Rembourser le solde du mentor
                $this->payoutRequest->mentorProfile->increment('available_balance', $this->payoutRequest->amount);

                Log::error('ProcessPayoutJob: Payout failed permanently after ' . $this->attempts() . ' attempts', [
                    'payout_id' => $this->payoutRequest->id,
                    'error' => $e->getMessage()
                ]);
            } else {
                // Relancer l'exception pour permettre les retries
                throw $e;
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessPayoutJob: Job failed permanently', [
            'payout_id' => $this->payoutRequest->id,
            'error' => $exception->getMessage()
        ]);

        // S'assurer que le statut est bien failed et le solde remboursé
        if ($this->payoutRequest->status !== PayoutRequest::STATUS_FAILED) {
            $this->payoutRequest->update([
                'status' => PayoutRequest::STATUS_FAILED,
                'error_message' => $exception->getMessage()
            ]);

            $this->payoutRequest->mentorProfile->increment('available_balance', $this->payoutRequest->amount);
        }
    }
}
