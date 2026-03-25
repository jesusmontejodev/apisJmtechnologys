@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
    <a href="{{ route('projects.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Proyecto
    </a>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value">{{ $totalSubmissions }}</div>
            <div class="stat-label">Total de Envíos</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="stat-value">{{ $passedSubmissions }}</div>
            <div class="stat-label">Aprobados</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="stat-value">{{ $blockedSubmissions }}</div>
            <div class="stat-label">Bloqueados</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="stat-value">{{ $blockRate }}%</div>
            <div class="stat-label">Tasa de Bloqueo</div>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-graph-up"></i> Últimos 7 Días
    </div>
    <div class="card-body">
        <canvas id="submissionChart" height="80"></canvas>
    </div>
</div>

<!-- Projects Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-folder"></i> Tus Proyectos ({{ $projects->count() }})
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Envíos</th>
                    <th>Aprobados</th>
                    <th>Bloqueados</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    <tr>
                        <td>
                            <strong>{{ $project->name }}</strong><br>
                            <small class="text-muted">{{ $project->slug }}</small>
                        </td>
                        <td>{{ $project->submissionLogs()->count() }}</td>
                        <td>
                            <span class="badge bg-success">
                                {{ $project->submissionLogs()->where('status', 'passed')->count() }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-danger">
                                {{ $project->submissionLogs()->where('status', 'blocked')->count() }}
                            </span>
                        </td>
                        <td>
                            @if($project->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('projects.show', $project->slug) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('projects.stats', $project->slug) }}" 
                               class="btn btn-sm btn-outline-info">
                                <i class="bi bi-graph-up"></i>
                            </a>
                            <a href="{{ route('projects.edit', $project->slug) }}" 
                               class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-inbox"></i> No hay proyectos<br>
                            <a href="{{ route('projects.create') }}" class="text-primary">Crea uno ahora</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('extra-scripts')
<script>
    const ctx = document.getElementById('submissionChart').getContext('2d');
    const dailyStats = @json($dailyStats);

    const labels = dailyStats.map(d => new Date(d.date).toLocaleDateString('es-ES', { month: 'short', day: 'numeric' }));
    const passedData = dailyStats.map(d => d.passed);
    const blockedData = dailyStats.map(d => d.blocked);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Aprobados',
                    data: passedData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Bloqueados',
                    data: blockedData,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
</script>
@endsection
