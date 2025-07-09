<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchasedTicket;

class TicketScannerController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $ticket = PurchasedTicket::where('unique_code', $request->code)->first();

        if (!$ticket) {
            return response()->json([
                'status' => 'error',
                'title' => 'Ticket no encontrado',
                'message' => 'Ticket no encontrado.'
            ], 404);
        }

        if ($ticket->status === 'used') {
            return response()->json([
                'status' => 'error',
                'title' => $ticket->evento->nombre ?? 'Ticket ya usado',
                'message' => '⚠️ Este ticket ya fue utilizado.'
            ], 422);
        }

        if ($ticket->status !== 'valid') {
            return response()->json([
                'status' => 'error',
                'title' => $ticket->evento->nombre ?? 'Ticket inválido',
                'message' => 'Ticket no es válido.'
            ], 422);
        }

        // Marca el ticket como usado
        $ticket->status    = 'used';
        $ticket->scan_date = now();
        $ticket->save();

        return response()->json([
            'status' => 'success',
            'title' => $ticket->evento->nombre ?? 'Validado',
            'message' => '✅ Ticket validado exitosamente.'
        ]);
    }

    // Buscar ticket por código QR (NO cambia el estado)
    public function buscar(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $ticket = PurchasedTicket::where('unique_code', $request->code)
            ->orWhere('short_code',   $request->code)
            ->first();

        if (!$ticket) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket no encontrado.',
            ], 404);
        }

        // Cargar entrada y evento asociados
        $entrada = $ticket->entrada; // Modelo Entrada (debería tener relación en el modelo)
        $evento  = $entrada?->evento; // Modelo Evento (debería tener relación en el modelo Entrada)

        return response()->json([
            'status' => $ticket->status === 'used' ? 'used' : 'valid',
            'message' => $ticket->status === 'used'
                ? '⚠️ Este ticket ya fue utilizado.'
                : '✅ Ticket válido, listo para validar.',
            'data' => [
                'unique_code' => $ticket->unique_code,
                'short_code'  => $ticket->short_code,
                'tipo'        => $ticket->ticket_type ?? '-',
                'precio'      => $entrada?->precio ?? '-',             // Precio real
                'evento'      => $evento?->nombre ?? '-',              // Nombre real del evento
                // 'validez'     => $entrada?->validez ?? '-',            // Validez (agregá este campo en Entrada si querés mostrarlo)
                'nombre'      => $ticket->buyer_name ?? '-',
                'email'       => $ticket->order?->buyer_email ?? '-',  // Email del comprador desde la orden
                'dni'         => $ticket->order?->buyer_dni ?? '-',    // DNI del comprador desde la orden
            ],
        ]);
    }

    // Validar ticket (cambiar a usado)
    public function validar(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $ticket = PurchasedTicket::where('unique_code', $request->code)->first();

        if (! $ticket) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Código inválido, por favor verificá.',
            ], 404);
        }


        if ($ticket->status === 'used') {
            return response()->json([
                'status' => 'error',
                'message' => '⚠️ Este ticket ya fue utilizado.',
            ], 422);
        }

        // Marca como usado
        $ticket->status    = 'used';
        $ticket->scan_date = now();
        $ticket->save();

        // Cargar datos relacionados para mostrar en la respuesta
        $entrada = $ticket->entrada; // Relación a Entrada (modelo)
        $evento  = $entrada?->evento; // Relación a Evento (modelo Entrada)

        return response()->json([
            'status' => 'success',
            'message' => '✅ Ticket validado exitosamente.',
            'data' => [
                'unique_code' => $ticket->unique_code,
                'tipo'        => $ticket->ticket_type ?? '-',
                'precio'      => $entrada?->precio ?? '-',
                'evento'      => $evento?->nombre ?? '-',
                // 'validez'  => $entrada?->validez ?? '-', // No se muestra por ahora
                'nombre'      => $ticket->buyer_name ?? '-',
                'email'       => $ticket->order?->buyer_email ?? '-',
                'dni'         => $ticket->order?->buyer_dni ?? '-',
            ]
        ]);
    }





    //METODO PARA DAR ESTILOS A LA NAVEGACION MENU IZQUIERDO
    public function index()
    {
        return view('filament.pages.ticket-scanner');
    }
}
