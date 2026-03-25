@extends('layouts.app')

@section('title', 'Crear Proyecto')

@section('content')
<h1 class="mb-4"><i class="bi bi-plus-circle"></i> Crear Nuevo Proyecto</h1>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <form method="POST" action="{{ route('projects.store') }}">
                @csrf

                <div class="card-header">
                    <i class="bi bi-info-circle"></i> Información General
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre del Proyecto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" 
                               placeholder="Mi formulario de contacto" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="destination_email" class="form-label">Email Destino <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('destination_email') is-invalid @enderror" 
                               id="destination_email" name="destination_email" value="{{ old('destination_email') }}" 
                               placeholder="contacto@empresa.com" required>
                        <small class="text-muted">Los formularios se enviarán a este email</small>
                        @error('destination_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email_subject" class="form-label">Asunto del Email</label>
                        <input type="text" class="form-control @error('email_subject') is-invalid @enderror" 
                               id="email_subject" name="email_subject" value="{{ old('email_subject') }}" 
                               placeholder="Nuevo mensaje del formulario">
                        <small class="text-muted">Dejar vacío para usar asunto por defecto</small>
                        @error('email_subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card-header">
                    <i class="bi bi-shield-check"></i> Configuración reCAPTCHA
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="recaptcha_type" class="form-label">Versión <span class="text-danger">*</span></label>
                        <select class="form-select @error('recaptcha_type') is-invalid @enderror" 
                                id="recaptcha_type" name="recaptcha_type" required onchange="updateRecaptchaVersion()">
                            <option value="">Selecciona una versión</option>
                            <option value="v2" @selected(old('recaptcha_type') === 'v2')>v2 (Checkbox)</option>
                            <option value="v3" @selected(old('recaptcha_type') === 'v3')>v3 (Sin interacción)</option>
                        </select>
                        @error('recaptcha_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="recaptcha_site_key" class="form-label">Site Key <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('recaptcha_site_key') is-invalid @enderror" 
                               id="recaptcha_site_key" name="recaptcha_site_key" value="{{ old('recaptcha_site_key') }}" 
                               placeholder="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI" required>
                        <small class="text-muted">De Google reCAPTCHA</small>
                        @error('recaptcha_site_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="recaptcha_secret_key" class="form-label">Secret Key <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('recaptcha_secret_key') is-invalid @enderror" 
                               id="recaptcha_secret_key" name="recaptcha_secret_key" value="{{ old('recaptcha_secret_key') }}" 
                               placeholder="6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe" required>
                        <small class="text-muted">Se guardará encriptada</small>
                        @error('recaptcha_secret_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="minScoreDiv" style="display: none;">
                        <label for="recaptcha_min_score" class="form-label">Puntuación Mínima (v3)</label>
                        <input type="number" class="form-control @error('recaptcha_min_score') is-invalid @enderror" 
                               id="recaptcha_min_score" name="recaptcha_min_score" value="{{ old('recaptcha_min_score', 0.5) }}" 
                               min="0" max="1" step="0.1">
                        <small class="text-muted">Rango 0-1. Default: 0.5</small>
                        @error('recaptcha_min_score')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card-header">
                    <i class="bi bi-globe"></i> Orígenes Permitidos
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="allowed_origins" class="form-label">URLs Permitidas</label>
                        <textarea class="form-control @error('allowed_origins') is-invalid @enderror" 
                                  id="allowed_origins" name="allowed_origins" rows="3" 
                                  placeholder="https://ejemplo.com&#10;https://api.ejemplo.com">{{ old('allowed_origins') }}</textarea>
                        <small class="text-muted">Una en cada línea. Dejar vacío para permitir todas</small>
                        @error('allowed_origins')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card-footer bg-light">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Crear Proyecto
                    </button>
                    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-lightbulb"></i> Tips</div>
            <div class="card-body small">
                <p><strong>¿Cómo obtener llaves reCAPTCHA?</strong></p>
                <ol>
                    <li>Ve a <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA</a></li>
                    <li>Crea un nuevo sitio</li>
                    <li>Copia las llaves Site y Secret</li>
                </ol>

                <hr>

                <p><strong>Diferencias v2 vs v3:</strong></p>
                <ul class="mb-0">
                    <li><strong>v2:</strong> Checkbox visible al usuario</li>
                    <li><strong>v3:</strong> Invisible, basada en puntuación</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script>
function updateRecaptchaVersion() {
    const type = document.getElementById('recaptcha_type').value;
    document.getElementById('minScoreDiv').style.display = type === 'v3' ? 'block' : 'none';
}
// Initialize on page load
document.addEventListener('DOMContentLoaded', updateRecaptchaVersion);
</script>
@endsection
