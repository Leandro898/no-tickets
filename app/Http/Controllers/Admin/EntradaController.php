<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Evento;

class EntradaController extends Controller
{
    public function gestionarEntradas($slug)
    {
        $evento = Evento::where('slug', $slug)->firstOrFail();

        // lógica para editar evento
        return view('filament.resources.evento-resource.pages.editar-evento', compact('evento'));
    }

    public function manage($slug)
    {
        $evento = Evento::where('slug', $slug)->firstOrFail();

        $entradas = $evento->entradas;

        return view('filament.resources.entrada-resource.pages.manage-entradas', compact('evento', 'entradas'));
    }
}
