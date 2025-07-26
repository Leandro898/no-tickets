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
            'seats.*.row' => 'nullable|integer',
            'seats.*.number'    => 'nullable|integer',
            'seats.*.entrada_id' => 'nullable|integer|exists:entradas,id',
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
            'seats.*.row'       => 'nullable|integer',
            'seats.*.number'    => 'nullable|integer',
            'seats.*.entrada_id' => 'nullable|integer|exists:entradas,id',

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
                'number'     => $s['number']    ?? 0,
                'entrada_id' => $s['entrada_id'] ?? null,
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
}
