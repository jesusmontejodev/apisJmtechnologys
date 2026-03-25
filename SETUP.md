# SaaS Proxy Anti-Spam - Setup Guide

## Quick Start

### 1. Install Dependencies
```bash
composer install
npm install
npm run build
```

### 2. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

**Important:** Configure the following in `.env`:
```
QUEUE_CONNECTION=database  # Or redis|sync for local dev
CACHE_STORE=file           # Or database|redis
```

### 3. Run Migrations
```bash
php artisan migrate
```

### 4. Create Demo User (Optional)
```bash
php artisan tinker
>>> $user = \App\Models\User::create(['name' => 'Demo', 'email' => 'demo@test.com', 'password' => bcrypt('password'), 'plan' => 'pro', 'api_calls_count' => 0]);
>>> exit
```

### 5. Start Development Server
```bash
php artisan serve
# In another terminal:
php artisan queue:listen
```

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `DELETE /api/auth/logout` - Logout (requires token)

### Projects (Authenticated)
- `GET /api/projects` - List user's projects
- `POST /api/projects` - Create new project
- `GET /api/projects/{slug}` - Get project details
- `PUT /api/projects/{slug}` - Update project
- `DELETE /api/projects/{slug}` - Delete project
- `POST /api/projects/{slug}/regenerate-token` - Regenerate project token
- `GET /api/projects/{slug}/logs` - Get submission logs (paginated)
- `GET /api/projects/{slug}/stats` - Get submission statistics

### Public Proxy
- `POST /api/submit/{project_token}` - Submit form with recaptcha validation

## Rate Limiting
- `/api/submit/*` - 60 requests/minute per IP
- `/api/auth/*` - 10 requests/minute per IP
- Other authenticated routes - 60 requests/minute per user

## Project Configuration

When creating a project, you need to provide:
- `name` - Project display name
- `endpoint_destino` - URL to forward validated submissions to
- `recaptcha_type` - Either 'v2' or 'v3'
- `recaptcha_site_key` - From Google reCAPTCHA admin console (encrypted in DB)
- `recaptcha_secret_key` - From Google reCAPTCHA admin console (encrypted in DB)
- `recaptcha_min_score` - For v3 only, between 0-1 (default 0.5)
- `allowed_origins` - JSON array of allowed origin domains

## Database Tables

- `users` - User accounts with plan and API usage tracking
- `projects` - Form projects with reCAPTCHA configuration
- `submission_logs` - Log of all form submissions (validation results)
- `job_batches` - Job batch tracking for queue management

## Testing Form Submission

### JavaScript Example
```javascript
// Send form to SaaS proxy
fetch('https://api.example.com/api/submit/PROJECT_TOKEN', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    name: 'John Doe',
    email: 'john@example.com',
    message: 'Hello!',
    recaptcha_token: '... token from reCAPTCHA ...'
  })
})
.then(r => r.json())
.then(data => console.log(data));
```

## Features Implemented

✅ Sanctum API Authentication
✅ Project CRUD with token generation
✅ reCAPTCHA v2 & v3 integration
✅ Form submission logging and validation
✅ Stats and analytics endpoints
✅ Rate limiting
✅ API call counting per user
✅ Encrypted sensitive data (API keys)
✅ Job queuing for async processing

## Future Enhancements

- [ ] Filament admin panel
- [ ] Payment integration (Stripe)
- [ ] Webhook support for real-time notifications
- [ ] Custom validation rules
- [ ] Email notifications
- [ ] Multi-captcha support (hCaptcha, Turnstile)

## Troubleshooting

### "Project not found" error
- Verify the `project_token` is correct
- Ensure project is marked as `is_active = true`

### "Origin not allowed" error
- Add your domain to the project's `allowed_origins` list
- Include the full domain with http/https (e.g., `https://example.com`)

### reCAPTCHA verification failed
- Verify reCAPTCHA keys are correct
- Check that token is being passed in request body as `recaptcha_token`
- Ensure reCAPTCHA keys match the domain

## Docker Compose (Optional)

```yaml
version: '3.8'
services:
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: apisjm
      MYSQL_ROOT_PASSWORD: admin
      MYSQL_PASSWORD: password
      MYSQL_USER: laravel
    ports:
      - "3306:3306"
    volumes:
      - dbdata:/var/lib/mysql

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

volumes:
  dbdata:
```

Start with: `docker-compose up -d`
