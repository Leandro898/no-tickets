<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ReportesEvento extends Component
{
    public $qrsGenerados = 0;
    public $qrsEscaneados = 0;
    public $eventoId;
    public $recaudacionMensual = [];

    public function mount($eventoId)
    {
        $this->eventoId = $eventoId;

        $this->qrsGenerados = DB::table('purchased_tickets')
            ->join('entradas', 'purchased_tickets.entrada_id', '=', 'entradas.id')
            ->where('entradas.evento_id', $eventoId)
            ->count();


        $this->qrsEscaneados = DB::table('purchased_tickets')
            ->join('entradas', 'purchased_tickets.entrada_id', '=', 'entradas.id')
            ->where('entradas.evento_id', $eventoId)
            ->where('purchased_tickets.status', 'used') // o el estado correcto
            ->count();


        $this->recaudacionMensual = DB::table('orders')
            ->selectRaw('MONTH(created_at) as mes, SUM(total_amount) as total')
            ->where('event_id', $eventoId)
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.reportes-evento');
    }
}
