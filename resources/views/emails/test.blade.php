<h1>Email de Prueba</h1>

<p>Hola! Este es un email de prueba.</p>

<p>Si estás recibiendo este mensaje, significa que tu configuración SMTP es <strong>correcta</strong> ✅</p>

<p>
    <strong>Configuración usada:</strong><br>
    Host: {{ config('mail.mailers.smtp.host') }}<br>
    Puerto: {{ config('mail.mailers.smtp.port') }}<br>
    Usuario: {{ config('mail.mailers.smtp.username') }}<br>
    De: {{ config('mail.from.address') }}
</p>

<p>Saludos,<br>JM Technologies API</p>
