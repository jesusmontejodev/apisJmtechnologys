# Quick Start Guide - SaaS Proxy Anti-Spam

## 5-Minute Setup

### Prerequisites
- PHP 8.3+
- MySQL 8 (or create SQLite for local dev)
- Composer
- Node.js & npm (optional, for Vite assets)

### 1. Install & Config (2 min)

```bash
# Install dependencies
composer install

# Setup environment
copy .env.example .env
# Edit .env with your database credentials:
# DB_HOST=127.0.0.1
# DB_DATABASE=apisjm
# DB_USERNAME=root
# DB_PASSWORD=admin

# Generate app key
php artisan key:generate
```

### 2. Database Migration (1 min)

```bash
php artisan migrate
```

### 3. Start Services (1 min)

**Terminal 1 - Web Server (Windows/Mac/Linux):**

```bash
# Option 1: Built-in PHP server (RECOMENDADO para Windows)
php -S 127.0.0.1:8000 server.php

# Option 2: Laravel Artisan (puede fallar en Windows)
# php artisan serve
```

**Terminal 2 - Queue (opcional):**
```bash
php artisan queue:listen
```

### 4. Test API 

**Opción A - Interface Web (La más fácil):**
1. Abre el navegador: `http://127.0.0.1:8000/test.html`
2. Haz clic en los botones para testear
3. ¡Listo!

**Opción B - PowerShell:**

```powershell
# 1. Registro
$body = @{
    name = "Test User"
    email = "test_$(Get-Random)@example.com"
    password = "password123"
    password_confirmation = "password123"
} | ConvertTo-Json

$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/auth/register" `
    -Method POST `
    -ContentType "application/json" `
    -Body $body `
    -SkipHttpErrorCheck

$token = ($response.Content | ConvertFrom-Json).data.token
Write-Host "Token: $token"

# 2. Crear proyecto
$projectBody = @{
    name = "Mi Formulario"
    endpoint_destino = "https://httpbin.org/post"
    recaptcha_type = "v2"
    recaptcha_site_key = "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"
    recaptcha_secret_key = "6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe"
    allowed_origins = @("http://localhost:3000", "https://example.com")
} | ConvertTo-Json

$projectResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/projects" `
    -Method POST `
    -Headers @{ Authorization = "Bearer $token" } `
    -ContentType "application/json" `
    -Body $projectBody `
    -SkipHttpErrorCheck

Write-Host ($projectResponse.Content | ConvertFrom-Json | ConvertTo-Json)
```

---

## Testing

### Run All Tests
```bash
php artisan test
```

### Expected Output
```
PASS  Tests\Feature\AuthTest (5 tests)
PASS  Tests\Feature\ProjectsTest (7 tests)
PASS  Tests\Feature\ProxySubmitTest (8 tests)

Tests: 20 passed
```

---

## Using with Frontend

### JavaScript Example

```javascript
// 1. Register/Login to get token
const loginResponse = await fetch('http://api.localhost:8000/api/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'test@example.com',
    password: 'password'
  })
});
const { data: { token } } = await loginResponse.json();

// 2. Create a project and get token
const projectResponse = await fetch('http://api.localhost:8000/api/projects', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'Contact Form',
    endpoint_destino: 'https://httpbin.org/post',
    recaptcha_type: 'v2',
    recaptcha_site_key: 'YOUR_SITE_KEY',
    recaptcha_secret_key: 'YOUR_SECRET',
    allowed_origins: ['http://localhost:3000']
  })
});
const { data: { project_token } } = await projectResponse.json();

// 3. On your frontend form, submit to SaaS proxy
document.getElementById('contactForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const response = await fetch(`http://api.localhost:8000/api/submit/${project_token}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      name: document.getElementById('name').value,
      email: document.getElementById('email').value,
      message: document.getElementById('message').value,
      recaptcha_token: grecaptcha.getResponse() // From reCAPTCHA widget
    })
  });
  
  const result = await response.json();
  if (result.success) {
    console.log('Form submitted successfully!');
  } else {
    console.error('Submission failed:', result.message);
  }
});
```

---

## Environment Variables

```ini
# .env
APP_NAME="Anti-Spam Proxy"
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apisjm
DB_USERNAME=root
DB_PASSWORD=admin

QUEUE_CONNECTION=database
CACHE_STORE=database
```

---

## Troubleshooting

### "Port 8000 already in use"
```bash
# Use different port
php -S 127.0.0.1:3000 server.php
```

### "Call to undefined function decrypt()"
```bash
php artisan key:generate
```

### "SQLSTATE[HY000]: General error"
```bash
php artisan migrate:fresh
```

### API returns 404
- Ensure you're using `server.php` as the router
- Access via `http://127.0.0.1:8000/api/...` not direct file paths

---

## Documentation

- Full setup: [SETUP.md](./SETUP.md)
- Implementation details: [IMPLEMENTATION_SUMMARY.md](./IMPLEMENTATION_SUMMARY.md)
- Filament admin: [FILAMENT_SETUP.md](./FILAMENT_SETUP.md)

---

**Happy coding! 🚀**
