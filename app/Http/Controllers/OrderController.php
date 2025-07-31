<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PurchasedTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1️⃣ Validación: fíjate que los names del form coincidan con estos
        $data = $request->validate([
            'event_id'         => 'required|integer|exists:eventos,id',
            'seats'            => 'required|array',
            'seats.*'          => 'integer|exists:seats,id',
            'buyer_full_name'  => 'required|string|max:255',
            'buyer_email'      => 'required|email|max:255',
            'buyer_phone'      => 'nullable|string|max:20',
            'buyer_dni'        => 'nullable|string|max:50',
        ]);

        // 2️⃣ Calcular el total (ajusta según tu lógica de precios)
        $seatCount = count($data['seats']);
        // Por ejemplo, supongamos que cada asiento vale $100:
        $totalAmount = $seatCount * 100;

        // 3️⃣ Crear la orden dentro de una transacción
        DB::transaction(function () use ($data, $totalAmount, &$order) {
            $order = Order::create([
                'user_id'          => auth()->id() ?? null,  // si quieres asociar usuario
                'event_id'         => $data['event_id'],
                'buyer_full_name'  => $data['buyer_full_name'],
                'buyer_email'      => $data['buyer_email'],
                'buyer_phone'      => $data['buyer_phone']  ?? null,
                'buyer_dni'        => $data['buyer_dni']    ?? null,
                'total_amount'     => $totalAmount,
                'items_data'       => json_encode($data['seats']),
                'payment_status'   => 'pending',
                'mp_payment_id'    => null,
                'mp_preference_id' => null,
            ]);

            // 4️⃣ Registrar cada asiento como PurchasedTicket
            foreach ($data['seats'] as $seatId) {
                $order->purchasedTickets()->create([
                    'seat_id' => $seatId,
                    // otros campos que tu PurchasedTicket requiera…
                ]);
            }
        });

        // 5️⃣ Redirigir a “gracias” o iniciar flujo Mercado Pago
        return redirect()
            ->route('orders.thankyou', $order)
            ->with('success', 'Orden creada correctamente. ¡Continuá con el pago!');
    }

    public function thankyou(Order $order)
    {
        return view('orders.thankyou', compact('order'));
    }
}
