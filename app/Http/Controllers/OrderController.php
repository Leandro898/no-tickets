<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'event_id'         => 'required|integer|exists:eventos,id',
            'seats'            => 'required|array',
            'seats.*'          => 'integer|exists:seats,id',
            'buyer_full_name'  => 'required|string|max:255',
            'buyer_email'      => 'required|email|max:255',
            'buyer_phone'      => 'nullable|string|max:20',
            'buyer_dni'        => 'nullable|string|max:50',
        ]);

        // Traemos los seats completos para tener su entrada_id
        $seats = Seat::whereIn('id', $data['seats'])->get();

        // Calcula total según tu lógica
        $totalAmount = $seats->count() * 100; // ejemplo

        DB::transaction(function () use ($data, $seats, $totalAmount, &$order) {
            $order = Order::create([
                'user_id'          => auth()->id(),
                'event_id'         => $data['event_id'],
                'buyer_full_name'  => $data['buyer_full_name'],
                'buyer_email'      => $data['buyer_email'],
                'buyer_phone'      => $data['buyer_phone']  ?? null,
                'buyer_dni'        => $data['buyer_dni']    ?? null,
                'total_amount'     => $totalAmount,
                'items_data'       => $seats->pluck('id')->toJson(),
                'payment_status'   => 'pending',
                'mp_payment_id'    => null,
                'mp_preference_id' => null,
            ]);

            // Creamos un PurchasedTicket por cada seat, incluyendo entrada_id
            foreach ($seats as $seat) {
                $uuid = (string) Str::uuid();      // genera un UUID
                $order->purchasedTickets()->create([
                    'entrada_id'   => $seat->entrada_id,
                    'short_code'   => strtoupper(Str::random(6)),
                    'unique_code'  => $uuid,         // ahora sí lo envías
                    'qr_path'      => null,          // o lo que corresponda
                    'status'       => 'valid',       // si tu tabla lo requiere
                    // … cualquier otro campo NOT NULL
                ]);
            }
        });

        return redirect()
            ->route('orders.thankyou', $order)
            ->with('success', 'Orden creada correctamente. ¡Continuá con el pago!');
    }

    public function thankyou(Order $order)
    {
        return view('orders.thankyou', compact('order'));
    }
}
