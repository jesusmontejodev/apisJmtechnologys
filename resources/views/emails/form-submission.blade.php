<x-mail::message>
# Nuevo mensaje de formulario

Recibiste un nuevo envío en el formulario **{{ $projectName }}**.

## Datos del formulario:

<x-mail::table>
| Campo | Valor |
|-------|-------|
@foreach ($formData as $key => $value)
| {{ ucfirst($key) }} | {{ is_array($value) ? json_encode($value) : $value }} |
@endforeach
</x-mail::table>

---

<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
</x-mail::footer>
</x-mail::message>
