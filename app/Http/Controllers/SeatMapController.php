<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;

class SeatMapController extends Controller
{
    public function listTickets(Evento $evento)
    {
        return $evento->entradas()->select('id', 'nombre', 'stock_inicial')->get();
    }

    public function saveSeats(Evento $evento, Request $request)
    {
        // Aquí haces tu lógica para insertar/actualizar en tu tabla `seats`
        // p.ej. foreach ($request->seats as $seat) { Seat::updateOrCreate(...); }
        return response()->json(['ok' => true]);
    }
}
