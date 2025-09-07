<?php

namespace App\Http\Controllers;

use App\Models\Order;

class PurchaseController extends Controller
{
    public function success(Order $order)
    {
        return view('purchase.success', compact('order'));
    }

    public function failed(Order $order)
    {
        return view('purchase.failed', compact('order'));
    }
}
