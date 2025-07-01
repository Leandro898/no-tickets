<?php

return [

    'title' => 'Ingresar',

    'heading' => 'ACCESO PARA PRODUCTOR',

    'actions' => [

        'register' => [
            'before' => 'o',
            'label' => 'crear una cuenta',
        ],

        'request_password_reset' => [
            'label' => 'Perdiste la contraseña?',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'Email',
        ],

        'password' => [
            'label' => 'Contraseña',
        ],

        'remember' => [
            'label' => 'Mantener conectado',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'Ingresar',
            ],

        ],

    ],

    'messages' => [

        'failed' => 'Estas credenciales no coinciden con nuestros registros.',

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Demasiados intentos de inicio de sesión',
            'body' => 'Por favor, inténtelo de nuevo en :segundos segundos.',
        ],

    ],

];
