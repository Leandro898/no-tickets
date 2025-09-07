<!DOCTYPE html>
<html>
<head>
    <title>Pago con Split (Prueba)</title>
    <script src="https://sdk.mercadopago.com/js/v2"></script>
</head>
<body>
    <h2>Botón de prueba - Split de pago</h2>

    <div id="wallet_container"></div>

    <script>
        const mp = new MercadoPago('APP_USR-29fe24d9-b4f6-4007-ab4c-e059653699a7'); // ← REEMPLAZÁ AQUÍ

        mp.checkout({
            preference: {
                id: '{{ $preferenceId }}'
            },
            render: {
                container: '#wallet_container',
                label: 'Pagar Entrada'
            }
        });
    </script>
</body>
</html>

