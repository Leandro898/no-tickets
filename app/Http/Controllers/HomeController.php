<?php

namespace App\Http\Controllers;

use App\Models\Evento;

class HomeController extends Controller
{
    public function index()
    {
        $slide1 = Evento::find(7);
        $slide2 = Evento::find(12);
        $slide3 = Evento::find(20);

        return view('inicio', compact('slide1','slide2','slide3'));
    }
}
