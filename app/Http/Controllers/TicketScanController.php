<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchasedTicket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TicketScanController extends Controller
{
    public function validar(Request $request)
    {
        try {
            $codigo = $request->input('codigo');

            // Si escanea una URL completa, extrae el UUID del código
            if (Str::contains($codigo, '/ticket/')) {
                $codigo = basename(dirname($codigo)); // Extrae el UUID del código QR
            }

            Log::info('Código recibido en validar(): ' . $codigo);

            $ticket = PurchasedTicket::where('unique_code', $codigo)->first();

            if (!$ticket) {
                return response()->json(['estado' => 'invalido']);
            }

            if ($ticket->status !== 'valid') {
                return response()->json(['estado' => 'usado']);
            }

            $ticket->status = 'used';
            $ticket->scanned_at = Carbon::now();
            $ticket->save();

            return response()->json([
                'estado' => 'valido',
                'nombre' => 'Ticket ID ' . $ticket->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al validar ticket: ' . $e->getMessage());
            return response()->json(['estado' => 'error', 'mensaje' => 'Error interno'], 500);
        }
    }
}

