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

    // app/Http/Controllers/SeatMapController.php

    public function saveSeats(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'seats' => 'required|array',
            'seats.*.x' => 'required|numeric',
            'seats.*.y' => 'required|numeric',
            'seats.*.selected' => 'boolean',
        ]);

        // Borrá viejos y creá nuevos, o tu lógica de update
        $evento->seats()->delete();
        foreach ($data['seats'] as $s) {
            $evento->seats()->create([
                'x'        => $s['x'],
                'y'        => $s['y'],
                'row'      => 0,
                'number'   => 0,
                'entrada_id' => 1,  // ajustá según tu lógica
            ]);
        }

        return response()->json(['status' => 'ok']);
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
