<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #f9f9f9;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            background: white;
            padding: 40px 30px;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .alert {
            background: #ecf0f1;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-box {
            background: #f0f4ff;
            border: 1px solid #667eea;
            padding: 20px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #667eea;
            font-size: 14px;
        }
        .contact-info {
            display: flex;
            align-items: center;
            margin: 8px 0;
            font-size: 13px;
        }
        .contact-label {
            font-weight: 600;
            color: #555;
            min-width: 80px;
        }
        .contact-value {
            color: #333;
        }
        .cta-button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: 600;
            text-align: center;
        }
        .footer {
            text-align: center;
            font-size: 11px;
            color: #999;
            padding: 20px;
            border-top: 1px solid #eee;
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Nuevo Lead!</h1>
            <p>Se ha registrado una nueva solicitud de contacto</p>
        </div>
        
        <div class="content">
            <div class="alert">
                <strong>📌 Información del Contacto</strong>
            </div>

            <div class="info-box">
                @if(isset($formData['name']) && $formData['name'])
                    <div class="contact-info">
                        <span class="contact-label">👤 Nombre:</span>
                        <span class="contact-value">{{ $formData['name'] }}</span>
                    </div>
                @endif

                @if(isset($formData['email']) && $formData['email'])
                    <div class="contact-info">
                        <span class="contact-label">📧 Email:</span>
                        <span class="contact-value">{{ $formData['email'] }}</span>
                    </div>
                @endif

                @if(isset($formData['phone']) && $formData['phone'])
                    <div class="contact-info">
                        <span class="contact-label">📱 Teléfono:</span>
                        <span class="contact-value">{{ $formData['phone'] }}</span>
                    </div>
                @endif

                @if(isset($formData['empresa']) && $formData['empresa'])
                    <div class="contact-info">
                        <span class="contact-label">🏢 Empresa:</span>
                        <span class="contact-value">{{ $formData['empresa'] }}</span>
                    </div>
                @endif

                @if(isset($formData['subject']) && $formData['subject'])
                    <div class="contact-info">
                        <span class="contact-label">📝 Asunto:</span>
                        <span class="contact-value">{{ $formData['subject'] }}</span>
                    </div>
                @endif
            </div>

            @if(isset($formData['message']) && $formData['message'])
                <div style="margin: 20px 0;">
                    <h3 style="color: #667eea; margin: 0 0 10px 0; font-size: 14px;">💬 Mensaje:</h3>
                    <p style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 0; color: #555;">
                        {{ $formData['message'] }}
                    </p>
                </div>
            @endif

            <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin: 20px 0;">
                <strong style="color: #856404;">⏱️ Acción Recomendada:</strong>
                <p style="margin: 5px 0 0 0; color: #856404; font-size: 13px;">
                    Contacta a este lead lo antes posible para maximizar las posibilidades de conversión.
                </p>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0;">Este es un email automático del sistema de leads.</p>
            <p style="margin: 5px 0 0 0;">&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
