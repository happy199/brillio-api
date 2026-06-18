<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\EmailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

abstract class EngagementReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Maximum number of emails to queue per execution (respects SMTP quota). */
    protected int $limit = 500;

    /**
     * Return the Eloquent query that selects eligible users.
     * Subclasses must implement this to define their own filter criteria.
     */
    abstract protected function eligibleUsers(): Builder;

    /**
     * Build the Mailable instance to send to a given user.
     */
    abstract protected function buildMailable(User $user): \Illuminate\Mail\Mailable;

    /**
     * A label used in the log message at the end of the job.
     */
    protected function jobLabel(): string
    {
        return class_basename(static::class);
    }

    /**
     * Execute the job.
     */
    public function handle(EmailDeliveryService $deliveryService): void
    {
        $processedCount = 0;

        $this->eligibleUsers()
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$processedCount, $deliveryService) {
                foreach ($users as $user) {
                    if ($processedCount >= $this->limit) {
                        return false; // Stop chunking
                    }

                    if ($deliveryService->isExcludedEmail($user->email)) {
                        continue;
                    }

                    // Basic email format guard to avoid SMTP errors
                    if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                        Log::warning("Email d'engagement sauté : format invalide pour l'utilisateur #{$user->id}", [
                            'email' => $user->email,
                        ]);

                        $user->update(['last_engagement_email_sent_at' => now()]);

                        continue;
                    }

                    try {
                        Mail::to($user->email)->send($this->buildMailable($user));
                        $user->update(['last_engagement_email_sent_at' => now()]);
                    } catch (\Exception $e) {
                        Log::error("Email d'engagement échoué pour l'utilisateur #{$user->id}: ".$e->getMessage());
                        $deliveryService->handleDeliveryFailure($user->email, $e);
                    }

                    $processedCount++;
                }
            });

        Log::info("Fin du job {$this->jobLabel()} : {$processedCount} emails traités.");
    }
}
