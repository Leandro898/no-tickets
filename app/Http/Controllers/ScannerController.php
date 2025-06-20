<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function index()
    {
        return view('scanner.test-scanner'); // Asegurate de tener esta vista creada
    }
}
