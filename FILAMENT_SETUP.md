# Filament Admin Panel - Configuration Guide

## Installation (when dependencies are resolved)

```bash
php artisan filament:install --panels=admin
```

## Project Resource

The `ProjectResource` would provide:

- **CRUD Operations**: Create, Read, Update, Delete projects
- **Fields**:
  - `name` - Text input
  - `endpoint_destino` - URL input with validation
  - `recaptcha_type` - Select (v2/v3)
  - `recaptcha_site_key` - Textarea (encrypted)
  - `recaptcha_secret_key` - Textarea (encrypted)
  - `recaptcha_min_score` - Decimal input (0-1)
  - `allowed_origins` - JSON array editor
  - `is_active` - Toggle
  - `project_token` - Read-only (with copy button)

### Associated Model Methods

The Project model should include:
- `getProjectTokenAttribute()` - Display token
- `regenerateToken()` - Generate new token

## Submission Log Resource

The `SubmissionLogResource` would provide:

- **List View** (Read-Only):
  - `project_id` - Sortable
  - `status` - Filterable (passed/blocked/error)
  - `ip_address`
  - `recaptcha_score` - For v3
  - `created_at` - Sortable, filterable
  - `blocked_reason` - Expandable

- **Filters**:
  - By status
  - By date range
  - By project

- **Actions**:
  - View details (IP, User Agent, payload hash)

## Dashboard Widget

Display real-time statistics:

```php
StatisticsWidget::make()
    ->today()
    ->thisMonth()
    ->stats([
        'Total Submissions' => SubmissionLog::count(),
        'Blocked Today' => SubmissionLog::whereStatus('blocked')->today()->count(),
        'Pass Rate' => SubmissionLog::whereStatus('passed')->count() / SubmissionLog::count() * 100 . '%',
    ])
    ->chart(
        SubmissionLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->last30Days()
            ->get()
    );
```

## User Resource

Manage user accounts:
- Assign plans (free/pro/enterprise)
- View API usage
- Reset API calls

## Health Check Widget

Display system health:
- Database connection
- Queue status
- Cache connectivity
- Recent errors

## Future Features

- [ ] Webhook management UI
- [ ] Custom validation rules UI
- [ ] Email notification templates
- [ ] Integration marketplace
- [ ] Advanced analytics dashboards

## Setup Steps

1. Ensure Filament is installed and dependencies resolved
2. Generate admin user:
   ```bash
   php artisan make:filament-user
   ```

3. Register resources in admin panel config:
   ```php
   // config/filament/admin.php
   'resources' => [
       \App\Filament\Resources\ProjectResource::class,
       \App\Filament\Resources\SubmissionLogResource::class,
       \App\Filament\Resources\UserResource::class,
   ]
   ```

4. Access panel at `/admin`

## Permissions (Via Filament)

Implement policies using Laravel's authorization:

```php
class ProjectPolicy
{
    public function update(User $user, Project $project)
    {
        return $user->id === $project->user_id || $user->isAdmin();
    }
    
    public function delete(User $user, Project $project)
    {
        return $user->id === $project->user_id;
    }
}
```

## Custom Actions

- **Regenerate Token** - One-click token generation
- **Copy Code Snippet** - Generate JS embed code
- **Download Statistics** - Export CSV
- **Test Submission** - Send test form to endpoint

## Custom Infolist

Show detailed project information:

```php
Infolist
    ->schema([
        Section\Heading::make('Project Details'),
        TextEntry::make('name'),
        TextEntry::make('slug'),
        TextEntry::make('project_token')->copyable()->copyableState(fn ($state) => $state),
        TextEntry::make('endpoint_destino'),
        
        Section\Heading::make('reCAPTCHA Configuration'),
        TextEntry::make('recaptcha_type'),
        TextEntry::make('recaptcha_min_score'),
        
        Section\Heading::make('Statistics'),
        TextEntry::make('submissionLogs')
            ->label('Total Submissions')
            ->getStateUsing(fn ($record) => $record->submissionLogs()->count()),
    ])
```
