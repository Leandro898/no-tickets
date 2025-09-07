{{-- resources/views/vendor/notifications/email.blade.php --}}
@component('mail::layout')

    {{-- 1) Cabecera con logo de marca --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            <img src="{{ asset('images/logo.png') }}" 
                 alt="{{ config('app.name') }}" 
                 style="height: 40px;">
        @endcomponent
    @endslot

    {{-- 2) Saludo destacado --}}
     {{ $greeting }}

    {{-- 3) Descripción del flujo --}}
    <p>¡Gracias por unirte a {{ config('app.name') }}! Tu cuenta ha sido creada exitosamente.</p>
    <p>Para confirmar haz clic en el botón que te llevará a configurar la constraseña de tu cuenta</p>

    {{-- 4) Botón principal --}}
    @isset($actionText)
        @component('mail::button', ['url' => $actionUrl])
            {{ $actionText }}
        @endcomponent
    @endisset

    {{-- 5) Recordatorio de expiración --}}
    <p>Este enlace es seguro y válido durante los próximos 60 minutos.</p>

    {{-- 6) Nota de seguridad --}}
    <p>Si no solicitaste este enlace, puedes ignorar este correo sin problema.</p>

    {{-- 7) Subcopy: URL en texto plano --}}
    @isset($actionText)
        @slot('subcopy')
            <p>Si no ves el botón haz clic en el enlace:</p>
            <p><a href="{{ $actionUrl }}">{{ $actionUrl }}</a></p>
        @endslot
    @endisset

    {{-- 8) Footer con espacio extra para separar marca --}}
    @slot('footer')
        <div style="margin-top: 40px;"></div>
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
        @endcomponent
    @endslot

@endcomponent
