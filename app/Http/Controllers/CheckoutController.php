<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function show(Evento $evento, Request $request)
    {
        // recoge los IDs desde la query: ?seats[]=157&seats[]=158
        $seatIds = $request->query('seats', []);
        // trae esos asientos (para mostrar fila, número, precio…)
        $seats = $evento->seats()->whereIn('id', $seatIds)->get();

        // ejemplo: calcula total
        $total = $seats->count() * $evento->precio ?? 0;

        return view('eventos.checkout-form', compact('evento', 'seats', 'total'));
    }
}
