@extends('layouts.app')

@section('title', 'Editar - ' . $project->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil-square"></i> Editar Proyecto</h1>
    <a href="{{ route('projects.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('projects.update', $project->slug) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Nombre del Proyecto -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre del Proyecto</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $project->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email Destino -->
                    <div class="mb-3">
                        <label for="destination_email" class="form-label">Email Destino</label>
                        <input type="email" class="form-control @error('destination_email') is-invalid @enderror" id="destination_email" name="destination_email" value="{{ old('destination_email', $project->destination_email) }}" required>
                        <small class="text-muted">Email donde se enviarán los formularios</small>
                        @error('destination_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Asunto Email -->
                    <div class="mb-3">
                        <label for="email_subject" class="form-label">Asunto del Email</label>
                        <input type="text" class="form-control @error('email_subject') is-invalid @enderror" id="email_subject" name="email_subject" value="{{ old('email_subject', $project->email_subject) }}" required>
                        <small class="text-muted">Ej: "Nuevo envío desde mi sitio web"</small>
                        @error('email_subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- reCAPTCHA Type Toggle -->
                    <div class="mb-3">
                        <label for="recaptcha_type" class="form-label">Tipo de reCAPTCHA</label>
                        <select class="form-select @error('recaptcha_type') is-invalid @enderror" id="recaptcha_type" name="recaptcha_type" required onchange="toggleV3Fields()">
                            <option value="v2" {{ old('recaptcha_type', $project->recaptcha_type) === 'v2' ? 'selected' : '' }}>reCAPTCHA v2 (Checkbox)</option>
                            <option value="v3" {{ old('recaptcha_type', $project->recaptcha_type) === 'v3' ? 'selected' : '' }}>reCAPTCHA v3 (Sin interacción)</option>
                        </select>
                        @error('recaptcha_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- reCAPTCHA Site Key -->
                    <div class="mb-3">
                        <label for="recaptcha_site_key" class="form-label">Site Key reCAPTCHA</label>
                        <input type="text" class="form-control @error('recaptcha_site_key') is-invalid @enderror" id="recaptcha_site_key" name="recaptcha_site_key" value="{{ old('recaptcha_site_key', $project->recaptcha_site_key) }}" required>
                        @error('recaptcha_site_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- reCAPTCHA Secret Key -->
                    <div class="mb-3">
                        <label for="recaptcha_secret_key" class="form-label">Secret Key reCAPTCHA</label>
                        <input type="password" class="form-control @error('recaptcha_secret_key') is-invalid @enderror" id="recaptcha_secret_key" name="recaptcha_secret_key" value="{{ old('recaptcha_secret_key', $project->recaptcha_secret_key) }}" required>
                        <small class="text-muted">Solo actualiza si quieres cambiar la clave</small>
                        @error('recaptcha_secret_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Min Score (V3 only) -->
                    <div class="mb-3" id="v3-score-field" style="display: {{ old('recaptcha_type', $project->recaptcha_type) === 'v3' ? 'block' : 'none' }};">
                        <label for="recaptcha_min_score" class="form-label">Puntuación Mínima (v3)</label>
                        <input type="number" class="form-control" id="recaptcha_min_score" name="recaptcha_min_score" step="0.1" min="0" max="1" value="{{ old('recaptcha_min_score', $project->recaptcha_min_score ?? 0.5) }}">
                        <small class="text-muted">Entre 0 y 1. Valores más altos = más estricto (0.5 es default)</small>
                    </div>

                    <!-- Orígenes Permitidos -->
                    <div class="mb-3">
                        <label for="allowed_origins" class="form-label">Orígenes Permitidos</label>
                        <textarea class="form-control @error('allowed_origins') is-invalid @enderror" id="allowed_origins" name="allowed_origins" rows="4" placeholder="https://ejemplo.com&#10;https://www.ejemplo.com&#10;https://blog.ejemplo.com">{{ old('allowed_origins', is_array($project->allowed_origins) ? implode("\n", $project->allowed_origins) : implode("\n", json_decode($project->allowed_origins, true) ?? [])) }}</textarea>
                        <small class="text-muted">Un origen por línea (ej: https://ejemplo.com)</small>
                        @error('allowed_origins')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Estado Activo -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $project->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Proyecto Activo
                        </label>
                        <small class="d-block text-muted">Desactiva para pausar temporalmente</small>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Actualizar Proyecto
                    </button>
                </form>
            </div>
        </div>

        <!-- Delete Section -->
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-trash"></i> Zona de Peligro
            </div>
            <div class="card-body">
                <p class="text-muted">Una vez borrado, no se puede recuperar</p>
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i> Eliminar Proyecto
                </button>
            </div>
        </div>
    </div>

    <!-- Tips Column -->
    <div class="col-lg-4">
        <div class="card mb-3 bg-light">
            <div class="card-header">
                <i class="bi bi-lightbulb"></i> Consejos
            </div>
            <div class="card-body small">
                <p><strong>reCAPTCHA v2:</strong> Los usuarios ven un checkbox. Más visible pero requiere interacción.</p>
                <p><strong>reCAPTCHA v3:</strong> Sin interacción visible. La puntuación indica probabilidad (0-1).</p>
                <p><strong>Orígenes:</strong> Solo estos dominios podrán usar tu proyecto. Déjalo vacío para permitir todos.</p>
                <hr>
                <p class="mb-0"><a href="https://www.google.com/recaptcha/admin" target="_blank">Gestionar claves</a> en Google reCAPTCHA</p>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Eliminar Proyecto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    ¿Estás seguro de que quieres eliminar <strong>{{ $project->name }}</strong>?
                </p>
                <p class="text-muted small">
                    Se eliminarán {% raw %}{{ $project->submissionLogs()->count() }}{% endraw %} registros de envíos.
                    Esta acción es irreversible.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('projects.destroy', $project->slug) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleV3Fields() {
    const type = document.getElementById('recaptcha_type').value;
    const v3Field = document.getElementById('v3-score-field');
    v3Field.style.display = type === 'v3' ? 'block' : 'none';
}
</script>
@endsection
