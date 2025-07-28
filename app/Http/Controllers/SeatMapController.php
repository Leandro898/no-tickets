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
     * Devuelve todos los elementos (asientos y shapes) para este evento.
     */
    public function listSeats(Evento $evento)
    {
        return $evento->seats()
            ->select(
                'id',
                'type',
                'x',
                'y',
                'row',
                'prefix',
                'number',
                'entrada_id',
                'width',
                'height',
                'radius',
                'label',
                'font_size'
            )
            ->get();
    }

    /**
     * Guarda únicamente los asientos (legacy).
     */
    public function saveSeats(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'seats'               => 'required|array',
            'seats.*.type'        => 'nullable|string|in:seat,rect,circle,text',
            'seats.*.x'           => 'required|numeric',
            'seats.*.y'           => 'required|numeric',
            'seats.*.row'         => 'nullable|string|max:10',
            'seats.*.prefix'      => 'nullable|string',
            'seats.*.number'      => 'nullable|integer',
            'seats.*.entrada_id'  => 'nullable|integer|exists:entradas,id',
            'seats.*.width'       => 'nullable|numeric',
            'seats.*.height'      => 'nullable|numeric',
            'seats.*.radius'      => 'nullable|numeric',
            'seats.*.label'       => 'nullable|string|max:255',
            'seats.*.fontSize'    => 'nullable|numeric',
        ]);

        // Borramos todo y creamos de nuevo
        $evento->seats()->delete();
        foreach ($data['seats'] as $s) {
            $evento->seats()->create([
                'type'        => $s['type']       ?? 'seat',
                'x'           => $s['x'],
                'y'           => $s['y'],
                'row'         => $s['row']        ?? null,
                'prefix'      => $s['prefix']     ?? null,
                'number'      => $s['number']     ?? 0,
                'entrada_id'  => $s['entrada_id'] ?? null,
                'width'       => $s['width']      ?? null,
                'height'      => $s['height']     ?? null,
                'radius'      => $s['radius']     ?? null,
                'label'       => $s['label']      ?? null,
                'font_size'   => $s['fontSize']   ?? null,
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
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('seat_maps', 'public');
        $url  = asset("storage/{$path}");

        return response()->json(['url' => $url]);
    }

    /**
     * Guarda el flujo completo:
     *  • borra/sube bg_image_url
     *  • guarda todos los elementos (seats y shapes)
     *  • guarda el JSON del canvas en map_data
     */
    public function saveMap(Request $request, Evento $evento)
    {
        $validated = $request->validate([
            'seats'               => 'array',
            'seats.*.type'        => 'required|string|in:seat,rect,circle,text',
            'seats.*.x'           => 'required|numeric',
            'seats.*.y'           => 'required|numeric',
            'seats.*.row'         => 'nullable|string|max:10',
            'seats.*.prefix'      => 'nullable|string',
            'seats.*.number'      => 'nullable|integer',
            'seats.*.entrada_id'  => 'nullable|integer|exists:entradas,id',
            'seats.*.width'       => 'nullable|numeric',
            'seats.*.height'      => 'nullable|numeric',
            'seats.*.radius'      => 'nullable|numeric',
            'seats.*.label'       => 'nullable|string|max:255',
            'seats.*.fontSize'    => 'nullable|numeric',
            'seats.*.rotation' => 'nullable|numeric',

            'bgUrl'               => 'nullable|string',
            'map'                 => 'nullable|string',
        ]);

        // 1️⃣ Borrar fondo antiguo si corresponde
        if (!empty($validated['bgUrl']) && $evento->bg_image_url && $validated['bgUrl'] !== $evento->bg_image_url) {
            $relative = str_replace(asset('storage/'), '', $evento->bg_image_url);
            Storage::disk('public')->delete($relative);
        }

        // 2️⃣ Subir nuevo fondo si viene
        if (!empty($validated['bgUrl']) && $validated['bgUrl'] !== $evento->bg_image_url) {
            $evento->update(['bg_image_url' => $validated['bgUrl']]);
        }

        // 3️⃣ Guardar JSON del canvas
        if (isset($validated['map'])) {
            $evento->update(['map_data' => $validated['map']]);
        }

        // 4️⃣ Reemplazar todos los elementos (asientos y shapes)
        $evento->seats()->delete();
        foreach ($validated['seats'] as $s) {
            $evento->seats()->create([
                'type'        => $s['type'],
                'x'           => $s['x'],
                'y'           => $s['y'],
                'row'         => $s['row']        ?? null,
                'prefix'      => $s['prefix']     ?? null,
                'number'      => $s['number']     ?? 0,
                'entrada_id'  => $s['entrada_id'] ?? null,
                'width'       => $s['width']      ?? null,
                'height'      => $s['height']     ?? null,
                'radius'      => $s['radius']     ?? null,
                'label'       => $s['label']      ?? null,
                'font_size'   => $s['fontSize']   ?? null,
                'rotation' => $s['rotation'] ?? 0,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Elimina la imagen de fondo del disco y de la BBDD.
     */
    public function deleteBg(Request $request, Evento $evento)
    {
        $data = $request->validate(['url' => 'required|string']);

        $relative = str_replace(asset('storage/'), '', $data['url']);
        Storage::disk('public')->delete($relative);
        $evento->update(['bg_image_url' => null]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Devuelve el mapa completo (asientos + shapes + bg + JSON).
     */
    public function getMap(Evento $evento)
    {
        return response()->json([
            'seats' => $evento->seats()
                ->get([
                    'type',
                    'x',
                    'y',
                    'row',
                    'prefix',
                    'number',
                    'entrada_id',
                    'width',
                    'height',
                    'radius',
                    'label',
                    'font_size',
                    'rotation',
                ]),
            'bgUrl' => $evento->bg_image_url,
            'map'   => $evento->map_data,
        ]);
    }
}
