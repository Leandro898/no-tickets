<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evento;

class SeatPurchaseController extends Controller
{
    public function showCheckout(Evento $evento)
    {
        return view('eventos.checkout-seats', compact('evento'));
    }

    public function store(Request $request, Evento $evento)
    {
        // aquí validás campañas, creás la orden, etc.
        $data = $request->validate([
            'seats'   => 'required|array|min:1',
            'seats.*' => 'integer|exists:asientos,id',
        ]);

        // ... tu lógica de negocio ...

        return redirect()->route('purchase.success', /* tu orden */);
    }
}
