<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Sitio en Mantenimiento</title>
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <style>
            /* 1. Fondo único: todo el viewport */
            html,
            body {
                margin: 0;
                height: 100%;
                /* Aquí cambiamos el degradado a uno más suave para texto negro */
                background: linear-gradient(135deg, #f3e8ff 0%, #ffffff 100%);
                color: #000;
                font-family: Arial, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 1rem;
            }

            /* 2. Contenedor: transparente y centrado */
            .maintenance-container {
                width: 100%;
                max-width: 500px;
            }

            /* 3. Título */
            .maintenance-title {
                font-size: 3rem;
                margin-bottom: 1.5rem;
                line-height: 1.2;
            }

            /* 4. Logo: grande y responsivo */
            .maintenance-logo {
                /* Baja del 60vw al 50vw */
                width: 50vw;
                /* Máximo de 300px en lugar de 350px */
                max-width: 250px;
                height: auto;
                margin: 1.5rem 0;
            }


            /* 5. Texto descriptivo */
            .maintenance-text {
                font-size: 1.25rem;
                opacity: 0.9;
                line-height: 1.4;
            }

            /* 6. Ajustes extra para pantallas muy pequeñas */
            @media (max-width: 480px) {
                .maintenance-title {
                    font-size: 2rem;
                }

                .maintenance-logo {
                    /* Antes tenías width: 80vw; max-width: 300px */
                    width: 70vw;
                    /* ahora ocupa el 70% del ancho de pantalla */
                    max-width: 200px;
                    /* y nunca pase de 200px */
                }

                .maintenance-text {
                    font-size: 1rem;
                }
            }
        </style>
    </head>

    <body>
        <div class="maintenance-container">
            <h1 class="maintenance-title">Próximamente...</h1>
            <img src="{{ asset('images/logo-innova.png') }}" alt="Logo Tickets Pro" class="maintenance-logo">
            <p class="maintenance-text">Tickets Pro</p>
        </div>
    </body>

</html>
