<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'form_data',
        'ip_address',
        'user_agent',
        'recaptcha_score',
        'status',
        'error_message',
        'email_sent_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'recaptcha_score' => 'float',
        'email_sent_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
