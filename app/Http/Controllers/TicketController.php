<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\PurchasedTicket;

class TicketController extends Controller
{
    public function misEntradas()
    {
        $user = Auth::user();

        $tickets = PurchasedTicket::whereHas('order', function ($query) use ($user) {
            $query->where('buyer_email', $user->email);
        })->with('entrada.evento')->get();

        return view('tickets.user', compact('tickets'));
    }
}
