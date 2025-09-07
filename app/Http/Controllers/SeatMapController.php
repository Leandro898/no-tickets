<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SeatMapController extends Controller
{
    /**
     * Muestra la vista de selección de asientos para el front.
     */
    public function showCheckout(Evento $evento)
    {
        //dd('Llegué a showCheckout', $evento->slug);
        // $evento trae el registro completo por slug
        return view('eventos.checkout-seats', compact('evento'));
    }
    
    /**
     * Devuelve las entradas (tickets) para este evento,
     * con un count de los asientos ya asignados y el remaining.
     */
    public function listTickets(Evento $evento)
    {
        // Usamos withCount para contar cuántos seats apuntan a cada entrada
        $entradas = $evento
            ->entradas()
            ->select('id', 'nombre', 'stock_inicial')
            ->withCount(['seats as assigned'])
            ->get()
            // Mapeamos para añadir remaining
            ->map(function ($e) {
                return [
                    'id'             => $e->id,
                    'nombre'         => $e->nombre,
                    'stock_inicial'  => $e->stock_inicial,
                    'assigned'       => $e->assigned,  // count generado por withCount
                    'remaining'      => max(0, $e->stock_inicial - $e->assigned),
                ];
            });

        return response()->json($entradas);
    }

    /**
     * Guarda únicamente los asientos (legacy).
     */
    public function saveSeats(Request $request, Evento $evento)
    {
        // 1️⃣ Validación de esquema básico
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

        // 2️⃣ Contar cuántos asientos quieren asignar por cada entrada_id
        $requestedCounts = [];
        foreach ($data['seats'] as $seat) {
            if (! empty($seat['entrada_id'])) {
                $requestedCounts[$seat['entrada_id']] = ($requestedCounts[$seat['entrada_id']] ?? 0) + 1;
            }
        }

        // 3️⃣ Obtener el stock_inicial de cada entrada involucrada
        if (! empty($requestedCounts)) {
            $stocks = $evento->entradas()
                ->whereIn('id', array_keys($requestedCounts))
                ->pluck('stock_inicial', 'id')  // [ entrada_id => stock_inicial ]
                ->toArray();

            // 4️⃣ Validar que no excedan el stock
            foreach ($requestedCounts as $entradaId => $qty) {
                $available = $stocks[$entradaId] ?? 0;
                if ($qty > $available) {
                    return response()->json([
                        'error' => "Has intentado asignar {$qty} asientos para la entrada ID {$entradaId}, 
                                pero solo hay {$available} disponibles."
                    ], 422);
                }
            }
        }

        // 5️⃣ Si todo está OK, borramos y recreamos los asientos
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
        $request->validate(['image' => 'required|image|max:2048']);

        // ★ Guardamos y devolvemos la ruta relativa
        $path = $request->file('image')->store('seat_maps', 'public');
        // No hagas asset() aquí, sólo path: "seat_maps/archivo.png"
        return response()->json(['url' => $path]);
    }


    /**
     * Guarda el flujo completo:
     *  • borra/sube bg_image_url
     *  • guarda todos los elementos (seats y shapes)
     *  • guarda el JSON del canvas en map_data
     */
    public function saveMap(Request $request, Evento $evento)
    {
        //Log::info('REQUEST:', $request->all());
        // 1️⃣ Validación
        $v = $request->validate([
            'seats'               => 'required|array',
            'seats.*.type'        => 'required|string|in:seat,rect,circle,text',
            'seats.*.x'           => 'required|numeric',
            'seats.*.y'           => 'required|numeric',
            // obligamos entrada_id sólo si es un asiento
            'seats.*.entrada_id'  => 'required_if:seats.*.type,seat|integer|exists:entradas,id',
            'seats.*.row'         => 'nullable|string|max:10',
            'seats.*.prefix'      => 'nullable|string',
            'seats.*.number'      => 'nullable|integer',
            'seats.*.width'       => 'nullable|numeric',
            'seats.*.height'      => 'nullable|numeric',
            'seats.*.radius'      => 'nullable|numeric',
            'seats.*.label'       => 'nullable|string|max:255',
            'seats.*.fontSize'    => 'nullable|numeric',
            'seats.*.rotation'    => 'nullable|numeric',
            'bgUrl'               => 'nullable|string|max:255',
            'map'                 => 'nullable|string',
        ]);

        // 2️⃣ Fondo y JSON
        if (!empty($v['bgUrl'])) {
            // Extrae sólo "seat_maps/archivo.png"
            $relative = Str::afterLast($v['bgUrl'], '/storage/');
            $evento->update(['bg_image_url' => $relative]);
        }
        if (isset($v['map'])) {
            $evento->update(['map_data' => $v['map']]);
        }

        
        // 3️⃣ Borrar anteriores
        $evento->seats()->delete();
        $evento->shapes()->delete();

        // 4️⃣ Volver a crear asientos y shapes
        foreach ($v['seats'] as $el) {
            if ($el['type'] === 'seat') {
                $evento->seats()->create([
                    'type'       => $el['type'],
                    'x'          => $el['x'],
                    'y'          => $el['y'],
                    'row'        => $el['row']      ?? null,
                    'prefix'     => $el['prefix']   ?? null,
                    'number'     => $el['number']   ?? 0,
                    'entrada_id' => $el['entrada_id'],
                    'radius'     => $el['radius']   ?? null,
                    'label'      => $el['label']    ?? null,
                    'font_size'  => $el['fontSize'] ?? null,
                    'rotation'   => $el['rotation'] ?? 0,
                ]);
            } else {
                $evento->shapes()->create([
                    'type'      => $el['type'],
                    'x'         => $el['x'],
                    'y'         => $el['y'],
                    'width'     => $el['width']    ?? null,
                    'height'    => $el['height']   ?? null,
                    'rotation'  => $el['rotation'] ?? 0,
                    'label'     => $el['label']    ?? null,
                    'font_size' => $el['fontSize'] ?? null,
                ]);
            }
        }
        // Log::info('llega 3');
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
    /**
     * Devuelve el mapa completo (asientos + shapes + bg + JSON).
     */
    public function getMap(Evento $evento)
    {
        // Limpieza automática de reservados expirados
        \App\Models\Seat::where('status', 'reservado')
            ->where('reserved_until', '<', now())
            ->update([
                'status'         => 'disponible',
                'reserved_until' => null,
            ]);

        // Imagen de fondo
        $bg       = $evento->bg_image_url;
        $relative = $bg ? Str::afterLast($bg, '/storage/') : null;
        $bgUrl    = $relative ? asset("storage/{$relative}") : null;

        // Traemos entradas del evento (ID => [nombre, precio])
        $entradas = $evento->entradas()->get(['id', 'nombre', 'precio'])->keyBy('id');

        // Asientos con los datos extendidos
        $seats = $evento->seats()
            ->select([
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
                'font_size',
                'rotation',
                'status',
                'reserved_until'
            ])->get()
            ->map(function ($seat) use ($entradas) {
                $entrada = $seat->entrada_id ? $entradas[$seat->entrada_id] ?? null : null;
                return [
                    'id'            => $seat->id,
                    'type'          => $seat->type,
                    'x'             => $seat->x,
                    'y'             => $seat->y,
                    'row'           => $seat->row,
                    'prefix'        => $seat->prefix,
                    'number'        => $seat->number,
                    'entrada_id'    => $seat->entrada_id,
                    'width'         => $seat->width,
                    'height'        => $seat->height,
                    'radius'        => $seat->radius,
                    'label'         => $seat->label,
                    'font_size'     => $seat->font_size,
                    'rotation'      => $seat->rotation,
                    'status'        => $seat->status,
                    'reserved_until' => $seat->reserved_until,
                    // ----------- estos dos son los que vas a mostrar en el pop-up ------------
                    'nombre_entrada' => $entrada ? $entrada->nombre : null,   // ejemplo: "Popular", "Platea"
                    'price'         => $entrada ? $entrada->precio : null,   // precio real del asiento
                ];
            })
            ->values(); // para que sea array simple

        // Shapes
        $shapes = $evento->shapes()
            ->select(['type', 'x', 'y', 'width', 'height', 'rotation', 'label', 'font_size'])
            ->get();

        return response()->json([
            'seats'  => $seats,
            'shapes' => $shapes,
            'bgUrl'  => $bgUrl,
            'map'    => $evento->map_data,
        ]);
    }


    // Lista los asientos guardados para el mapa en el front
    public function listSeats(Evento $evento)
    {
        return response()->json(
            $evento->seats()->get(['id', 'x', 'y', 'radius'])
        );
    }
}
