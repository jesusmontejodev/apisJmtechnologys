@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1><i class="bi bi-folder"></i> {{ $project->name }}</h1>
        <p class="text-muted">{{ $project->slug }}</p>
    </div>
    <div>
        <a href="{{ route('projects.stats', $project->slug) }}" class="btn btn-info">
            <i class="bi bi-graph-up"></i> Estadísticas
        </a>
        <a href="{{ route('projects.edit', $project->slug) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Editar
        </a>
    </div>
</div>

<!-- Project Info -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Configuración</div>
            <div class="card-body small">
                <table class="table table-sm mb-0">
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            @if($project->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>reCAPTCHA:</strong></td>
                        <td><code>{{ strtoupper($project->recaptcha_type) }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Email Destino:</strong></td>
                        <td><code>{{ $project->destination_email }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Asunto:</strong></td>
                        <td>{{ $project->email_subject }}</td>
                    </tr>
                    <tr>
                        <td><strong>Dominios Permitidos:</strong></td>
                        <td>
                            @php
                                $origins = is_array($project->allowed_origins) ? $project->allowed_origins : json_decode($project->allowed_origins, true) ?? [];
                            @endphp
                            @if(empty($origins))
                                <span class="badge bg-warning">⚠️ No configurado (bloquea CORS)</span>
                            @else
                                @foreach($origins as $origin)
                                    <span class="badge bg-primary">{{ htmlspecialchars($origin, ENT_QUOTES, 'UTF-8') }}</span>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Token del Proyecto</div>
            <div class="card-body">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="projectToken" value="{{ $project->project_token }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="copyBtn" onclick="copyToken()">
                        <i class="bi bi-clipboard"></i> Copiar
                    </button>
                </div>
                <small class="text-muted">Usa este token en tu formulario frontend</small>
            </div>
        </div>
    </div>
</div>

<!-- Code Snippet -->
<div class="card mb-4">
    <div class="card-header">Código para tu Frontend</div>
    <div class="card-body">
        <pre><code>&lt;form id="contactForm" data-site-key="{{ $project->recaptcha_site_key }}" data-api-url="{{ config('app.url') }}/api/submit/{{ $project->project_token }}"&gt;
  &lt;input name="name" placeholder="Nombre" required&gt;
  &lt;input name="email" type="email" placeholder="Email" required&gt;
  &lt;textarea name="message" placeholder="Mensaje"&gt;&lt;/textarea&gt;
  &lt;button type="submit"&gt;Enviar&lt;/button&gt;
&lt;/form&gt;

&lt;script src="https://www.google.com/recaptcha/api.js?render={{ $project->recaptcha_site_key }}"&gt;&lt;/script&gt;
&lt;script&gt;
document.getElementById('contactForm').addEventListener('submit', async (e) =&gt; {
  e.preventDefault();
  
  const siteKey = e.target.getAttribute('data-site-key');
  const apiUrl = e.target.getAttribute('data-api-url');
  
  const token = await grecaptcha.execute(siteKey, {action: 'submit'});
  
  const formData = new FormData(e.target);
  const data = Object.fromEntries(formData);
  data.recaptcha_token = token;
  
  const response = await fetch(apiUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  });
  
  const result = await response.json();
  alert(result.success ? 'Enviado!' : 'Error: ' + result.message);
});
&lt;/script&gt;</code></pre>
    </div>
</div>

<!-- Submission Logs -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-list"></i> Últimos Envíos ({{ $logs->total() }})
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>IP</th>
                    <th>Status</th>
                    <th>reCAPTCHA Score</th>
                    <th>Email Enviado</th>
                    <th>Razón Bloqueo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td><small>{{ $log->created_at->format('d/m/Y H:i') }}</small></td>
                        <td><code>{{ $log->ip_address }}</code></td>
                        <td>
                            @if($log->status === 'passed')
                                <span class="badge bg-success">Aprobado</span>
                            @elseif($log->status === 'blocked')
                                <span class="badge bg-danger">Bloqueado</span>
                            @else
                                <span class="badge bg-warning">Error</span>
                            @endif
                        </td>
                        <td>
                            @if($log->recaptcha_score)
                                {{ number_format($log->recaptcha_score, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($log->email_sent)
                                <i class="bi bi-check-circle text-success"></i> Sí
                            @else
                                <i class="bi bi-x-circle text-muted"></i> No
                            @endif
                        </td>
                        <td>
                            @if($log->blocked_reason)
                                <small class="text-danger">{{ $log->blocked_reason }}</small>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No hay envíos todavía
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="card-footer">
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@endsection

@section('extra-scripts')
<script>
function copyToken() {
    const token = document.getElementById('projectToken').value;
    navigator.clipboard.writeText(token);
    const btn = document.getElementById('copyBtn');
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check"></i> Copiado!';
    setTimeout(() => { btn.innerHTML = original; }, 2000);
}
</script>
@endsection
