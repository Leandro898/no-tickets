<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\PurchasedTicket; // Ajusta el namespace si es otro

class PurchasedTicketsSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 15; $i++) {
            PurchasedTicket::create([
                'order_id' => rand(1, 5),               // Asume que tienes orders con IDs del 1 al 5
                'entrada_id' => rand(1, 10),            // Asume entradas con IDs del 1 al 10
                'unique_code' => Str::upper(Str::random(10)),
                'qr_path' => "qrcodes/ticket_{$i}.png",
                'status' => 'valid',                    // Por ejemplo: valid, used, cancelled
                'scan_date' => null,                    // NULL porque no fue escaneado aún
                'scanned_at' => null,
                'buyer_name' => "Cliente $i",
                'ticket_type' => 'digital',             // O físico u otro tipo que uses
            ]);
        }
    }
}
