<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LeadSubmissionMail extends Mailable
{
    public function __construct(
        public Project $project,
        public array $formData
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            to: [$this->project->destination_email],
            subject: '🔥 Nuevo Lead: ' . ($this->formData['name'] ?? 'Sin nombre'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.form-submission-lead',
            with: [
                'projectName' => $this->project->name,
                'formData' => $this->formData,
            ],
        );
    }
}
