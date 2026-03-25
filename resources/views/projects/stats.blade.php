@extends('layouts.app')

@section('title', 'Stats - ' . $project->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-graph-up"></i> Estadísticas - {{ $project->name }}</h1>
    <a href="{{ route('projects.show', $project->slug) }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value">{{ $totalSubmissions }}</div>
            <div class="stat-label">Total Envíos</div>
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
            <div class="stat-label">Tasa Bloqueo</div>
        </div>
    </div>
</div>

<!-- Email Sent -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="stat-card" style="border-left-color: #17a2b8;">
            <div class="stat-value">{{ $emails_sent }}</div>
            <div class="stat-label">Emails Enviados</div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-up"></i> Últimos 30 Días
            </div>
            <div class="card-body">
                <canvas id="statsChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pie-chart"></i> Proporción
            </div>
            <div class="card-body">
                <canvas id="pieChart" height="120"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script>
// Line Chart - Últimos 30 días
const dailyStats = @json($dailyStats);
const dates = dailyStats.map(d => new Date(d.date).toLocaleDateString('es-ES', { month: 'short', day: 'numeric' }));
const passedData = dailyStats.map(d => d.passed);
const blockedData = dailyStats.map(d => d.blocked);

const ctx1 = document.getElementById('statsChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: dates,
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
            legend: { position: 'top' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Pie Chart - Proporción
const ctx2 = document.getElementById('pieChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Aprobados', 'Bloqueados'],
        datasets: [{
            data: [{{ $passedSubmissions }}, {{ $blockedSubmissions }}],
            backgroundColor: ['#28a745', '#dc3545'],
            borderColor: ['#fff', '#fff'],
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>
@endsection
