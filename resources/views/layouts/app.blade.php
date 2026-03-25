<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SaaS Form Proxy') - Anti-Spam Proxy</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    
    <style>
        :root {
            --primary-color: #007bff;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --dark-color: #343a40;
        }
        
        body {
            background: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--primary-color) !important;
        }
        
        .navbar-brand i {
            margin-right: 8px;
        }
        
        .sidebar {
            background: white;
            border-right: 1px solid #e9ecef;
            min-height: calc(100vh - 56px);
            padding: 20px;
        }
        
        .sidebar .nav-link {
            color: #6c757d;
            border-left: 3px solid transparent;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 0;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #f8f9fa;
            border-left-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .main-content {
            padding: 30px;
        }
        
        .card {
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
            font-weight: 600;
            color: #333;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 15px;
        }
        
        .stat-card .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
        }
        
        .stat-card .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.danger { border-left-color: var(--danger-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        
        .alert {
            border: none;
            border-radius: 8px;
        }
        
        .form-control, .form-select {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px 12px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .table {
            background: white;
        }
        
        .badge {
            padding: 6px 12px;
            font-weight: 500;
        }
        
        .badge-passed {
            background: var(--success-color);
            color: white;
        }
        
        .badge-blocked {
            background: var(--danger-color);
            color: white;
        }
        
        .badge-error {
            background: var(--warning-color);
            color: #333;
        }
        
        .footer {
            background: white;
            border-top: 1px solid #e9ecef;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 40px;
        }
    </style>
    
    @yield('extra-styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-shield-check"></i> FormGuard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="ms-auto d-flex align-items-center gap-3">
                    @auth
                        <span class="text-muted">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    @auth
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-md-2 sidebar">
                    <nav class="nav flex-column">
                        <a href="{{ route('dashboard') }}" class="nav-link @if(request()->routeIs('dashboard')) active @endif">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="{{ route('projects.index') }}" class="nav-link @if(request()->routeIs('projects.*')) active @endif">
                            <i class="bi bi-folder"></i> Proyectos
                        </a>
                        <a href="{{ route('health') }}" class="nav-link @if(request()->routeIs('health')) active @endif">
                            <i class="bi bi-heart-pulse"></i> Estado del Sistema
                        </a>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="col-md-10 main-content">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Errores:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    @else
        <div class="main-content">
            @yield('content')
        </div>
    @endauth

    <div class="footer">
        <p>&copy; 2026 FormGuard. Todos los derechos reservados.</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('extra-scripts')
</body>
</html>
