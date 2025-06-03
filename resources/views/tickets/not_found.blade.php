<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket No Encontrado</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; text-align: center; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; padding: 25px; border: 1px solid #ddd; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h1 { color: #dc3545; margin-bottom: 20px; }
        p { color: #555; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Ticket No Encontrado!</h1>
        <p>Lo sentimos, el código de ticket que escaneó o ingresó no corresponde a ningún ticket en nuestro sistema.</p>
        <p>Por favor, verifique que el código sea correcto e intente de nuevo.</p>
        @if(isset($code))
            <p>Código intentado: <strong>{{ $code }}</strong></p>
        @endif
    </div>
</body>
</html>