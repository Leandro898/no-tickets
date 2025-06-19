<?php

namespace App\Livewire;

use Livewire\Component;

class TestScanner extends Component
{
    public $message = 'Listo para escanear...';

    protected $listeners = ['codeScanned' => 'handleScan'];

    public function handleScan($payload)
    {
        $this->message = 'Código escaneado: ' . ($payload['code'] ?? 'N/A');
    }

    public function render()
    {
        return view('livewire.test-scanner');
    }
}
