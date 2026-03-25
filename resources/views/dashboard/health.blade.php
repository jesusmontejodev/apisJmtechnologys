@extends('layouts.app')

@section('title', 'Estado del Sistema')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-heart-pulse"></i> Estado del Sistema</h1>
    <a href="#" onclick="location.reload()" class="btn btn-outline-primary">
        <i class="bi bi-arrow-clockwise"></i> Actualizar
    </a>
</div>

<div class="row">
    @foreach($checks as $name => $check)
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-2">
                                <i class="bi bi-{{ $name === 'database' ? 'database' : ($name === 'cache' ? 'lightning' : ($name === 'queue' ? 'hourglass-split' : 'envelope')) }}"></i>
                                {{ ucfirst($name) }}
                            </h5>
                            <p class="text-muted mb-0">{{ $check['message'] }}</p>
                        </div>
                        <div>
                            @if($check['status'] === 'healthy')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Saludable
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-exclamation-circle"></i> Error
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-info-circle"></i> Status General
    </div>
    <div class="card-body">
        @if($allHealthy)
            <div class="alert alert-success mb-0">
                <i class="bi bi-check-circle"></i> 
                <strong>Todos los sistemas están funcionando correctamente</strong>
            </div>
        @else
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle"></i> 
                <strong>Algunos componentes reportan problemas</strong>. Verifica los detalles arriba.
            </div>
        @endif
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-gear"></i> Configuración
    </div>
    <div class="card-body">
        <table class="table table-sm mb-0">
            <tr>
                <td><strong>Entorno:</strong></td>
                <td><code>{{ env('APP_ENV') }}</code></td>
            </tr>
            <tr>
                <td><strong>Base de Datos:</strong></td>
                <td><code>{{ config('database.default') }}</code></td>
            </tr>
            <tr>
                <td><strong>Cache:</strong></td>
                <td><code>{{ config('cache.default') }}</code></td>
            </tr>
            <tr>
                <td><strong>Queue:</strong></td>
                <td><code>{{ config('queue.default') }}</code></td>
            </tr>
            <tr>
                <td><strong>Mail:</strong></td>
                <td><code>{{ config('mail.default') }}</code></td>
            </tr>
        </table>
    </div>
</div>
@endsection
