<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SeatMapController extends Controller
{
    /**
     * Devuelve las entradas (tickets) para este evento.
     */
    public function listTickets(Evento $evento)
    {
        return $evento
            ->entradas()
            ->select('id', 'nombre', 'stock_inicial')
            ->get();
    }

    /**
     * Guarda únicamente la posición de los asientos.
     * (Este método puedes dejarlo si lo usas en otro flujo,
     *  pero el flujo completo ahora es saveMap.)
     */
    public function saveSeats(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'seats'       => 'required|array',
            'seats.*.x'   => 'required|numeric',
            'seats.*.y'   => 'required|numeric',
            'seats.*.row'    => 'nullable|string|max:10',
            'seats.*.number'    => 'nullable|integer',
            'seats.*.entrada_id' => 'nullable|integer|exists:entradas,id',
            'radius'     => $s['radius'],
        ]);

        // Reemplazamos todos los asientos de golpe
        $evento->seats()->delete();
        foreach ($data['seats'] as $s) {
            $evento->seats()->create([
                'x'          => $s['x'],
                'y'          => $s['y'],
                'row'        => $s['row']       ?? 0,
                'number'     => $s['number']    ?? 0,
                'entrada_id' => $s['entrada_id'] ?? null,
                'radius'     => $s['radius'],
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Sube la imagen de fondo al storage y devuelve su URL pública.
     */
    public function uploadBg(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048', // hasta 2MB
        ]);

        $path = $request->file('image')->store('seat_maps', 'public');
        $url  = asset("storage/{$path}");

        return response()->json(['url' => $url]);
    }

    // Bajo tu método uploadBg agrega:
    public function listSeats(Evento $evento)
    {
        return $evento->seats()
            ->select(
                'id',
                'x',
                'y',
                'row',
                'prefix',
                'number',
                'entrada_id',
                'label',    // ← lo agregas
                'radius'    // ← lo agregas
            )
            ->get();
    }



    /**
     * Guarda el flujo completo:
     *  • borra/sube bg_image_url
     *  • guarda los seats
     *  • guarda el JSON completo del canvas en map_data
     */
    public function saveMap(Request $request, Evento $evento)
    {
        $validated = $request->validate([
            'seats' => 'array',
            'seats.*.x'         => 'required|numeric',
            'seats.*.y'         => 'required|numeric',
            'seats.*.row'       => 'nullable|string|max:10',
            'seats.*.prefix'      => 'nullable|string',
            'seats.*.number'    => 'nullable|integer',
            'seats.*.entrada_id' => 'nullable|integer|exists:entradas,id',
            'seats.*.label'    => 'nullable|string|max:20',
            'seats.*.radius'      => 'required|numeric',

            'bgUrl' => 'nullable|string',        // url de fondo
            'map'   => 'nullable|string',        // JSON del lienzo
        ]);

        // 1️⃣ Actualizo fondo y JSON
        $evento->update([
            'bg_image_url' => $validated['bgUrl'] ?? null,
            'map_data'     => $validated['map']   ?? $evento->map_data,
        ]);

        // 2️⃣ Reemplazo todos los asientos
        $evento->seats()->delete();
        foreach ($validated['seats'] as $s) {
            $evento->seats()->create([
                'x'          => $s['x'],
                'y'          => $s['y'],
                'row'        => $s['row']       ?? 0,
                'prefix'     => $s['prefix']   ?? null,
                'number'     => $s['number']    ?? 0,
                'entrada_id' => $s['entrada_id'] ?? null,
                'label'      => $s['label'],
                'radius'     => $s['radius'],
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function deleteBg(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'url' => 'required|string',
        ]);

        // 1) Elimina el fichero de disco (opcional):
        // asume que tu URL es asset('storage/seat_maps/archivo.jpg')
        $relativePath = str_replace(asset('storage/'), '', $data['url']);
        Storage::disk('public')->delete($relativePath);

        // 2) Limpia el campo en la BBDD:
        $evento->update(['bg_image_url' => null]);

        return response()->json(['status' => 'ok']);
    }

    // FUNCION PARA QUE EL FRONT PUEDA OBTENER EL MAPA COMPLETO
    // (si usas getMap)
    public function getMap(Evento $evento)
    {
        return response()->json([
            'seats' => $evento->seats()
                ->get([
                    'x',
                    'y',
                    'row',
                    'prefix',
                    'number',
                    'entrada_id',
                    'label',
                    'radius'   // ← idem aquí
                ]),
            'bgUrl' => $evento->bg_image_url,
            'map'   => $evento->map_data,
        ]);
    }
}
