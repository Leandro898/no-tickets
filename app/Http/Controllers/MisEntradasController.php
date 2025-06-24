<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\PurchasedTicket;

class MisEntradasController extends Controller
{
    public function index()
    {
        $tickets = PurchasedTicket::with('order', 'entrada.evento')// relación con evento
            ->whereHas('order', function ($query) {
                $query->where('buyer_email', Auth::user()->email);
            })
            ->latest()
            ->get();

        return view('mis-entradas.index', compact('tickets'));
    }
}
