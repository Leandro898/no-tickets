<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    <!-- <script>
        document.addEventListener('livewire:load', () => {
            Livewire.on('toast', ({
                title,
                message,
                type
            }) => {
                const toast = document.createElement('div');
                toast.innerHTML = `<div class="fixed top-4 right-4 bg-${type === 'success' ? 'green' : 'red'}-500 text-white px-4 py-2 rounded shadow">
                <strong>${title}</strong><br>${message}
            </div>`;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            });
        });
    </script> -->
    <!-- Livewire Scripts -->
    @livewireScripts
    <script>
        document.addEventListener('toast', e => {
            const { title, message, type } = (e.detail?.[0]) || {};
    
            if (type !== 'success') return;
    
            const toast = document.createElement('div');
            toast.className = "toast";
            toast.innerHTML = `
                <div style="
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    background: #22c55e;
                    color: white;
                    padding: 1rem;
                    border-radius: 0.5rem;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
                    max-width: 320px;
                    font-size: 0.875rem;
                ">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" class="w-5 h-5" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 13l4 4L19 7" />
                    </svg>
                    <div>
                        <strong style="display: block;">${title}</strong>
                        ${message}
                    </div>
                </div>
            `;
    
            // Posicionar
            toast.style.position = 'fixed';
            toast.style.top = '1rem';
            toast.style.right = '1rem';
            toast.style.zIndex = '9999';
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
    
            document.body.appendChild(toast);
    
            // Mostrar con fade-in
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
            });
    
            // Ocultar luego de 3.5s
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        });
    </script>
    
    
    
    
    
    
    
    
    
    
    
    
    
</body>

</html>