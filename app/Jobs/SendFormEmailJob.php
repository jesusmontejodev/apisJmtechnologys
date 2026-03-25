<?php

namespace App\Jobs;

use App\Mail\FormSubmissionMail;
use App\Models\SubmissionLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendFormEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public int $maxExceptions = 3;

    /**
     * Get the backoff milliseconds for an invocation exception.
     *
     * @return array
     */
    public function backoff(): array
    {
        return [1, 5, 10]; // seconds
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SubmissionLog $submissionLog,
        public array $formData
    ) {
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $project = $this->submissionLog->project;

            // Send email via Mailable
            Mail::send(new FormSubmissionMail($project, $this->formData));

            // Mark submission log as email sent
            $this->submissionLog->update(['email_sent' => true]);
        } catch (Throwable $exception) {
            // Log the error and rethrow to trigger retry
            logger()->error('SendFormEmailJob failed', [
                'submission_id' => $this->submissionLog->id,
                'project_id' => $this->submissionLog->project_id,
                'error' => $exception->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $exception;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        // Log failure after all retries exhausted
        logger()->error('SendFormEmailJob permanently failed', [
            'submission_id' => $this->submissionLog->id,
            'project_id' => $this->submissionLog->project_id,
            'error' => $exception->getMessage(),
        ]);

        // Update submission log with error status
        $this->submissionLog->update([
            'status' => 'error',
            'blocked_reason' => 'Email delivery failed after ' . $this->attempts() . ' attempts',
        ]);
    }
}
