<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entrada;

class Primero extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            Entrada::create([
                'evento_id' => 1, // Cambia al ID de evento que tengas en tu proyecto
                'nombre' => "Entrada de prueba $i",
                'descripcion' => "Descripción de la entrada de prueba número $i",
                'stock_inicial' => 100 + $i * 10,
                'stock_actual' => 100 + $i * 10,
                'max_por_compra' => rand(1, 5),
                'precio' => rand(1000, 5000) / 100,
                'valido_todo_el_evento' => true,
                'disponible_desde' => null,
                'disponible_hasta' => null,
                'tipo' => 'digital',
                'visible' => true,
            ]);
        }
    }
}
