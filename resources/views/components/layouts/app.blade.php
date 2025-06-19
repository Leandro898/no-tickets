<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Scanner Test</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900">
    {{ $slot }}
    @livewireScripts
</body>
</html>
