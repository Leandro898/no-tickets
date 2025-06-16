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
        const mp = new MercadoPago('APP_USR-029daf4b-c8e6-4220-874f-cbe813d526e7'); // ← REEMPLAZÁ AQUÍ

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
