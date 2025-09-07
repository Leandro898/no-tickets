<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use Illuminate\Support\Str;

class OrdersSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 5; $i++) {
            Order::create([
                'event_id' => 1, // O ajusta a un event_id válido que tengas
                'buyer_full_name' => "Cliente $i",
                'buyer_email' => "cliente{$i}@mail.com",
                'buyer_phone' => '123456789',
                'buyer_dni' => 'DNI123456',
                'total_amount' => rand(1000, 5000) / 100, // Ej: entre 10 y 50
                'items_data' => json_encode([]), // Puedes ajustar con datos reales o vacíos
                'payment_status' => 'paid',
                'mp_payment_id' => Str::random(10),
                'mp_preference_id' => Str::random(10),
                'email_sent_at' => now(),
            ]);
        }
    }
}
