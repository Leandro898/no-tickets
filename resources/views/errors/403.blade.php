<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso denegado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background: #f4f4fa; font-family: Arial, sans-serif; color: #7c3aed; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { text-align: center; background: #fff; padding: 40px 30px; border-radius: 16px; box-shadow: 0 2px 16px 0 #7c3aed22; }
        h1 { font-size: 4rem; margin-bottom: 0.5em; color: #7c3aed; }
        p { font-size: 1.25rem; }
        a { color: #fff; background: #7c3aed; padding: 10px 28px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 2em; display: inline-block; }
        a:hover { background: #5f2bb3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>403</h1>
        <p><strong>Acceso denegado.</strong></p>
        <p>No tienes permisos para acceder a esta sección.</p>
        <a href="{{ url('/') }}">Ir al inicio</a>
    </div>
</body>
</html>
