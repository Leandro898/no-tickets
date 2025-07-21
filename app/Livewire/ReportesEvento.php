<?php
// app/Livewire/ReportesEvento.php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportesEvento extends Component
{
    public $qrsGenerados = 0;
    public $qrsEscaneados = 0;
    public $eventoId;
    public $recaudacionDiaria = [];
    public $labels = [];

    public function mount($eventoId)
    {
        $this->eventoId = $eventoId;

        // 1) QRs generados
        $this->qrsGenerados = DB::table('purchased_tickets')
            ->join('entradas', 'purchased_tickets.entrada_id', '=', 'entradas.id')
            ->where('entradas.evento_id', $eventoId)
            ->count();

        // 2) QRs validados
        $this->qrsEscaneados = DB::table('purchased_tickets')
            ->join('entradas', 'purchased_tickets.entrada_id', '=', 'entradas.id')
            ->where('entradas.evento_id', $eventoId)
            ->where('purchased_tickets.status', 'used')
            ->count();

        // 3) Recaudación diaria últimos 30 días
        $end   = Carbon::now()->endOfDay();
        $start = (clone $end)->subDays(30)->startOfDay();

        // Traigo sumas por fecha
        $raw = DB::table('orders')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->where('event_id', $eventoId)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // Ensamblo el período completo (30 días), rellenando con ceros
        $period = [];
        foreach (new CarbonPeriod($start, $end) as $d) {
            $key = $d->format('Y-m-d');
            $period[$key] = isset($raw[$key]) ? (float) $raw[$key] : 0;
        }

        $this->recaudacionDiaria = $period;
        $this->labels = array_map(
            fn($d) => Carbon::parse($d)->format('d M'),
            array_keys($period)
        );
    }

    public function render()
    {
        return view('livewire.reportes-evento');
    }
}
