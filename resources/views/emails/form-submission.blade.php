<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            background: #006db3;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background: white;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .field {
            margin: 15px 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .field:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #006db3;
            margin-bottom: 5px;
        }
        .value {
            color: #555;
            word-break: break-word;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $projectName }}</h1>
            <p>Nuevo formulario recibido</p>
        </div>
        <div class="content">
            @foreach($formData as $field => $value)
                <div class="field">
                    <div class="label">{{ ucfirst(str_replace(['_', '-'], ' ', $field)) }}:</div>
                    <div class="value">
                        @if(is_array($value))
                            {{ json_encode($value) }}
                        @else
                            {{ $value }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="footer">
            <p>Este es un email automático. No responda a este correo.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
