<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchasedTicket;

class ScannerController extends Controller
{
    // Muestra la interfaz (si NO usas Filament Page)
    public function index()
    {
        return view('tickets.scanner_interface');
    }

    // Recibe el POST desde el JS y marca el ticket
    public function scan(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $ticket = PurchasedTicket::where('unique_code', $request->code)->first();

        if (! $ticket) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ticket no encontrado.',
            ], 404);
        }

        if ($ticket->status !== 'valid') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ticket no es válido para escanear.',
            ], 422);
        }

        $ticket->status    = 'used';
        $ticket->scan_date = now();
        $ticket->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Ticket validado exitosamente.',
        ]);
    }

    
}
