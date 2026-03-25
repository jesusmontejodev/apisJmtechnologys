<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'endpoint_destino',
        'recaptcha_type',
        'recaptcha_site_key',
        'recaptcha_secret_key',
        'recaptcha_min_score',
        'allowed_origins',
        'project_token',
        'is_active',
        'destination_email',
        'email_subject',
    ];

    protected $casts = [
        'allowed_origins' => 'array',
        'is_active' => 'boolean',
        'recaptcha_site_key' => 'encrypted',
        'recaptcha_secret_key' => 'encrypted',
        'recaptcha_min_score' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submissionLogs(): HasMany
    {
        return $this->hasMany(SubmissionLog::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto-generate slug from name
            if (empty($model->slug)) {
                $model->slug = \Illuminate\Support\Str::slug($model->name);
            }
            // Auto-generate project_token
            if (empty($model->project_token)) {
                $model->project_token = \Illuminate\Support\Str::uuid();
            }
        });
    }
}

