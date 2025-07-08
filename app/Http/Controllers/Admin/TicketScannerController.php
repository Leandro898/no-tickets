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





    //METODO PARA DAR ESTILOS A LA NAVEGACION MENU IZQUIERDO
    public function index()
    {
        return view('filament.pages.ticket-scanner');
    }
}
