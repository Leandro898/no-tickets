<?php

namespace App\Filament\Resources\EventoResource\Widgets;

use Filament\Widgets\Widget;

class CustomHeaderBox extends Widget
{
    // Asegúrate de que esta línea apunte a tu archivo Blade recién creado
    protected static string $view = 'filament.resources.evento-resource.widgets.custom-header-box';

    // Opcional: Para que ocupe todo el ancho disponible.
    protected int | string | array $columnSpan = 'full';
}