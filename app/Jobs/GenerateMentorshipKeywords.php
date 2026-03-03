<?php

namespace App\Jobs;

use App\Models\Mentorship;
use App\Services\AIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateMentorshipKeywords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mentorship;

    /**
     * Create a new job instance.
     */
    public function __construct(Mentorship $mentorship)
    {
        $this->mentorship = $mentorship;
    }

    /**
     * Execute the job.
     */
    public function handle(AIService $aiService): void
    {
        $keywords = $aiService->generateForbiddenKeywords($this->mentorship);

        if (! empty($keywords)) {
            $this->mentorship->update([
                'custom_forbidden_keywords' => $keywords,
            ]);

            \Illuminate\Support\Facades\Log::info('Custom keywords generated for mentorship #'.$this->mentorship->id, [
                'count' => count($keywords),
            ]);
        }
    }
}
