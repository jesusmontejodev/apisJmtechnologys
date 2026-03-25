<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionLog extends Model
{
    protected $fillable = [
        'project_id',
        'ip_address',
        'user_agent',
        'status',
        'recaptcha_score',
        'payload_hash',
        'response_code',
        'blocked_reason',
    ];

    protected $casts = [
        'recaptcha_score' => 'float',
        'response_code' => 'integer',
        'status' => 'string',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

