@php
    $formData = $data ?? [];
@endphp

<div style="background: #f5f5f5; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0;">
    @if(empty($formData))
        <p style="color: #999; margin: 0;">No hay datos adicionales</p>
    @else
        <table style="width: 100%; border-collapse: collapse;">
            @foreach($formData as $key => $value)
                <tr style="border-bottom: 1px solid #e0e0e0;">
                    <td style="padding: 12px; font-weight: 600; color: #667eea; width: 30%;">
                        {{ ucfirst(str_replace(['_', '-'], ' ', $key)) }}
                    </td>
                    <td style="padding: 12px; color: #333;">
                        @if(is_array($value))
                            <pre style="background: white; padding: 10px; border-radius: 4px; font-size: 12px; margin: 0; overflow-x: auto;">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        @else
                            {{ $value }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
</div>
