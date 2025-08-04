<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Seat;
use App\Models\Order;
use App\Models\PurchasedTicket;
use App\Models\Evento;

class SeatReservationController extends Controller
{
    // Reservar asientos (lock para que no se dupliquen ventas)
    public function reservar(Request $request)
    {
        $seatIds = $request->input('seats'); // array de IDs de asientos
        if (!is_array($seatIds) || !count($seatIds)) {
            return response()->json(['error' => 'No se enviaron asientos.'], 422);
        }

        DB::beginTransaction();
        try {
            // Trae los asientos y bloquea los registros
            $asientos = Seat::whereIn('id', $seatIds)->lockForUpdate()->get();

            // Valida estado
            $ocupados = $asientos->where('status', '!=', 'disponible');
            if ($ocupados->count()) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Algunos asientos ya no están disponibles.',
                    'ocupados' => $ocupados->pluck('id')
                ], 409);
            }

            // Marca como reservado
            foreach ($asientos as $asiento) {
                $asiento->status = 'reservado';
                // Opcional: $asiento->reserved_until = Carbon::now()->addMinutes(10);
                $asiento->save();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al reservar asientos.'], 500);
        }
    }

    // (Opcional) Liberar asientos reservados
    public function liberar(Request $request)
    {
        $seatIds = $request->input('seats');
        if (!is_array($seatIds) || !count($seatIds)) {
            return response()->json(['error' => 'No se enviaron asientos.'], 422);
        }

        Seat::whereIn('id', $seatIds)
            ->where('status', 'reservado')
            ->update(['status' => 'disponible']);

        return response()->json(['success' => true]);
    }

    /**
     * Simula una compra de asientos (para pruebas)
     * Aquí se marcan como vendidos y se crea una orden ficticia.
     */
    public function purchaseSimulated(Request $request, Evento $evento)
    {
        // 1️⃣ Validación de datos de entrada
        $data = $request->validate([
            'seats'             => 'required|array',
            'seats.*'           => 'integer|exists:seats,id',
            'buyer_full_name'   => 'required|string|max:255',
            'buyer_email'       => 'required|email',
            'buyer_dni'         => 'nullable|string|max:50',
        ]);

        $seatIds = $data['seats'];

        DB::beginTransaction();
        try {
            // 2️⃣ Bloquear y cargar asientos
            $asientos = Seat::whereIn('id', $seatIds)
                ->lockForUpdate()
                ->get();

            // 3️⃣ Verificar que sigan disponibles
            $ocupados = $asientos->where('status', '!=', 'disponible');
            if ($ocupados->count()) {
                DB::rollBack();
                return response()->json([
                    'error'    => 'Algunos asientos ya no están disponibles.',
                    'ocupados' => $ocupados->pluck('id'),
                ], 409);
            }

            // 4️⃣ Marcarlos como vendidos
            foreach ($asientos as $asiento) {
                $asiento->status = 'vendido';
                $asiento->save();
            }

            // 5️⃣ Crear la orden “aprobada”
            $order = Order::create([
                'event_id'         => $evento->id,
                'buyer_full_name'  => $data['buyer_full_name'],
                'buyer_email'      => $data['buyer_email'],
                'buyer_dni'        => $data['buyer_dni'] ?? null,
                'total_amount'     => 0,                // o calculado si querés
                'payment_status'   => 'approved',       // simulamos pago exitoso
                'items_data'       => json_encode($seatIds),
            ]);

            // 6️⃣ Crear un PurchasedTicket por asiento
            foreach ($asientos as $asiento) {
                PurchasedTicket::create([
                    'order_id'     => $order->id,
                    'entrada_id'   => $asiento->entrada_id,
                    'unique_code'  => (string) Str::uuid(),
                    'short_code'   => substr((string) Str::uuid(), 0, 8),
                    'qr_path'      => null,               // si vas a generar QR, aquí
                    'status'       => 'valid',
                    'buyer_name'   => $data['buyer_full_name'],
                    'ticket_type'  => $asiento->type,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order'   => $order,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al procesar la compra simulada.',
                'msg'   => $e->getMessage(),
            ], 500);
        }
    }
}
