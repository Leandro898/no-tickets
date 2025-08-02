<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Evento;

class EntradaController extends Controller
{
    public function manage($evento_slug)
    {
        $evento = Evento::where('slug', $evento_slug)->first();

        if (!$evento) {
            return redirect()->route('admin.dashboard')->with('error', 'Evento no encontrado.');
        }

        $entradas = $evento->entradas;

        return view('filament.resources.entrada-resource.pages.manage-entradas', compact('evento', 'entradas'));
    }
}
