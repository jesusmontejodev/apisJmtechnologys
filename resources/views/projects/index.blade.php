@extends('layouts.app')

@section('title', 'Mis Proyectos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-folder"></i> Mis Proyectos</h1>
    <a href="{{ route('projects.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Proyecto
    </a>
</div>

@if($projects->count() > 0)
    <div class="row">
        @foreach($projects as $project)
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="card-title">{{ $project->name }}</h5>
                                <p class="text-muted small mb-0">{{ $project->slug }}</p>
                            </div>
                            @if($project->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </div>

                        <div class="my-3">
                            <small class="text-muted">Envíos:</small><br>
                            <small>
                                <strong class="text-success">{{ $project->submissionLogs()->where('status', 'passed')->count() }}</strong> aprobados |
                                <strong class="text-danger">{{ $project->submissionLogs()->where('status', 'blocked')->count() }}</strong> bloqueados |
                                <strong>{{ $project->submissionLogs()->count() }}</strong> total
                            </small>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">reCAPTCHA:</small>
                            <span class="badge bg-info">{{ strtoupper($project->recaptcha_type) }}</span>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Email destino:</small>
                            <small class="text-break">{{ $project->destination_email }}</small>
                        </div>

                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('projects.show', $project->slug) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                            <a href="{{ route('projects.stats', $project->slug) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-graph-up"></i> Stats
                            </a>
                            <a href="{{ route('projects.edit', $project->slug) }}" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($projects->hasPages())
        <nav class="mt-4">
            {{ $projects->links('pagination::bootstrap-5') }}
        </nav>
    @endif
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i>
            <p class="text-muted mt-3">No tienes proyectos creados</p>
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Crear mi primer proyecto
            </a>
        </div>
    </div>
@endif
@endsection
