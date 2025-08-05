<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Seat;
use Carbon\Carbon;

class ReleaseExpiredReservations extends Command
{
    protected $signature   = 'seats:release-expired';
    protected $description = 'Libera los asientos cuya reserva haya expirado';

    public function handle()
    {
        Seat::where('status', 'reservado')
            ->whereColumn('reserved_until', '<', now())
            ->update(['status' => 'disponible', 'reserved_until' => null]);
    }
}
