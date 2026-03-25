<?php

namespace App\Jobs;

use App\Models\SubmissionLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ForwardSubmissionJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $maxExceptions = 3;
    public $backoff = [1, 5, 10]; // Exponential backoff: 1s, 5s, 10s

    public function __construct(
        protected SubmissionLog $submissionLog,
        protected array $payload,
        protected string $endpoint
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = Http::timeout(10)->post(
                $this->endpoint,
                $this->payload
            );

            // Update submission log with response code if not already logged
            if ($this->submissionLog->status === 'passed') {
                $this->submissionLog->update([
                    'response_code' => $response->status(),
                ]);
            }

            Log::info('ForwardSubmissionJob completed successfully', [
                'submission_log_id' => $this->submissionLog->id,
                'response_code' => $response->status(),
            ]);
        } catch (\Exception $e) {
            Log::warning('ForwardSubmissionJob failed, retrying...', [
                'submission_log_id' => $this->submissionLog->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->submissionLog->update([
                    'status' => 'error',
                    'blocked_reason' => 'Failed after ' . $this->tries . ' attempts: ' . $e->getMessage(),
                ]);

                Log::error('ForwardSubmissionJob failed permanently', [
                    'submission_log_id' => $this->submissionLog->id,
                    'error' => $e->getMessage(),
                ]);

                return;
            }

            throw $e;
        }
    }
}

