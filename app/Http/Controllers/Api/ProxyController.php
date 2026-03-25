<?php

namespace App\Http\Controllers\Api;

use App\Jobs\SendFormEmailJob;
use App\Mail\FormSubmissionMail;
use App\Models\Contact;
use App\Models\Project;
use App\Models\SubmissionLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use ReCaptcha\ReCaptcha;
use App\Http\Controllers\Controller;

class ProxyController extends Controller
{
    public function submit(Request $request, string $project_token): JsonResponse
    {
        // 1. Find project by token
        $project = Project::where('project_token', $project_token)
            ->where('is_active', true)
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found or inactive',
                'data' => null
            ], 404);
        }

        // 1.5 Validate that destination_email is configured
        if (!$project->destination_email) {
            return response()->json([
                'success' => false,
                'message' => 'Project not configured for email delivery',
                'data' => null
            ], 422);
        }

        // 2. Validate origin/referer against allowed_origins
        $origin = $request->header('origin') ?? $request->header('referer');
        if ($project->allowed_origins && !empty($project->allowed_origins)) {
            $originAllowed = false;
            foreach ($project->allowed_origins as $allowedOrigin) {
                if (strpos($origin, $allowedOrigin) === 0 || $origin === $allowedOrigin) {
                    $originAllowed = true;
                    break;
                }
            }
            if (!$originAllowed) {
                $this->logSubmission($project, $request, 'blocked', 'Origin not allowed');
                return response()->json([
                    'success' => false,
                    'message' => 'Verification failed',
                    'data' => null
                ], 422);
            }
        }

        // 3. Extract recaptcha token
        $recaptchaToken = $request->input('recaptcha_token');
        if (!$recaptchaToken) {
            $this->logSubmission($project, $request, 'blocked', 'Missing recaptcha_token');
            return response()->json([
                'success' => false,
                'message' => 'Verification failed',
                'data' => null
            ], 422);
        }

        // 4. Validate reCAPTCHA with Google
        $recaptcha = new ReCaptcha($project->recaptcha_secret_key);
        $resp = $recaptcha->verify($recaptchaToken, $request->ip());

        $recaptchaScore = null;
        if (!$resp->isSuccess()) {
            $this->logSubmission($project, $request, 'blocked', 'reCAPTCHA verification failed');
            return response()->json([
                'success' => false,
                'message' => 'Verification failed',
                'data' => null
            ], 422);
        }

        if ($project->recaptcha_type === 'v3') {
            $recaptchaScore = $resp->getScore();
            if ($recaptchaScore < $project->recaptcha_min_score) {
                $this->logSubmission($project, $request, 'blocked', "Score too low: $recaptchaScore < {$project->recaptcha_min_score}", $recaptchaScore);
                return response()->json([
                    'success' => false,
                    'message' => 'Verification failed',
                    'data' => null
                ], 422);
            }
        }

        // 5. Prepare payload without recaptcha_token
        $payload = $request->except('recaptcha_token');
        $payloadHash = hash('sha256', json_encode($payload));

        // 6. Create contact and send email
        try {
            // Create submission log for audit
            $submissionLog = $this->logSubmission(
                $project,
                $request,
                'passed',
                null,
                $recaptchaScore,
                $payloadHash
            );

            // Extract common fields - try various field names for email/name
            $contactName = $payload['name'] ?? $payload['nombre'] ?? $payload['full_name'] ?? 'Anonymous';
            $contactEmail = $payload['email'] ?? $payload['correo'] ?? null;
            $contactPhone = $payload['phone'] ?? $payload['teléfono'] ?? $payload['tel'] ?? null;
            $contactSubject = $payload['subject'] ?? $payload['asunto'] ?? $payload['tema'] ?? 'Form Submission';
            $contactMessage = $payload['message'] ?? $payload['mensaje'] ?? $payload['descripcion'] ?? '';

            // Create contact record
            $contact = Contact::create([
                'project_id' => $project->id,
                'name' => $contactName,
                'email' => $contactEmail,
                'phone' => $contactPhone,
                'subject' => $contactSubject,
                'message' => $contactMessage,
                'form_data' => $payload,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('user-agent'),
                'recaptcha_score' => $recaptchaScore,
                'status' => 'received',
            ]);

            // Send email synchronously (not queued)
            try {
                // Validate destination email exists
                if (!$project->destination_email) {
                    throw new \Exception('Project destination_email is not configured');
                }

                logger()->info('Sending form email', [
                    'contact_id' => $contact->id,
                    'project_id' => $project->id,
                    'destination_email' => $project->destination_email,
                    'from_address' => config('mail.from.address'),
                ]);

                Mail::to($project->destination_email)
                    ->send(new FormSubmissionMail($project, $payload));
                
                $contact->update([
                    'status' => 'sent',
                    'email_sent_at' => now(),
                ]);

                // Mark submission log as email sent
                $submissionLog->update(['email_sent' => true]);

                logger()->info('Form email sent successfully', [
                    'contact_id' => $contact->id,
                    'destination_email' => $project->destination_email,
                ]);
            } catch (\Throwable $e) {
                logger()->error('Failed to send form email', [
                    'contact_id' => $contact->id,
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'destination_email' => $project->destination_email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $contact->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            // Increment API calls count
            $project->user->increment('api_calls_count');

            return response()->json([
                'success' => true,
                'message' => 'Form submission received',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            $this->logSubmission($project, $request, 'error', $e->getMessage(), $recaptchaScore, $payloadHash);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process submission',
                'data' => null
            ], 500);
        }
    }

    private function logSubmission(
        Project $project,
        Request $request,
        string $status,
        ?string $blockedReason = null,
        ?float $recaptchaScore = null,
        ?string $payloadHash = null,
        ?int $responseCode = null
    ): SubmissionLog {
        return SubmissionLog::create([
            'project_id' => $project->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('user-agent'),
            'status' => $status,
            'recaptcha_score' => $recaptchaScore,
            'payload_hash' => $payloadHash,
            'response_code' => $responseCode,
            'blocked_reason' => $blockedReason,
        ]);
    }
}

