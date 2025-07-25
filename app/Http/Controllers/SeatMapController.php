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

    //METODO PARA GUARDAR LA IMAGEN DE FONDO DEL MAPA DE ASIENTOS
    public function uploadBg(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048', // max 2MB
        ]);
        $path = $request->file('image')->store('seat_maps', 'public');
        $url = asset('storage/' . $path);
        return response()->json(['url' => $url]);
    }
}
