<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Ejemplo: trae los próximos eventos ordenados por fecha
        $eventos = Evento::where('fecha_inicio', '>=', now())
            ->orderBy('fecha_inicio', 'asc')
            ->get();

        // Si quieres paginación:
        // $eventos = Evento::where('fecha','>=',now())
        //                  ->orderBy('fecha','asc')
        //                  ->paginate(12);

        return view('inicio', compact('eventos'));
    }
}
