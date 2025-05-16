<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Aplicación</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"> {{-- Si usás Laravel Mix o Vite --}}
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
</body>
</html>
