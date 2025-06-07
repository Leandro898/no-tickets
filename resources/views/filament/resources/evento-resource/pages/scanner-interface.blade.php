<x-filament-panels::page>
    {{-- Aquí es donde incluyes tu componente Livewire del escáner. --}}
    {{-- Asegúrate de que el nombre del componente (en este caso 'scanner') coincida con el nombre de tu clase Livewire (App\Livewire\Scanner). --}}
    @livewire('scanner') 

    {{-- Puedes añadir aquí cualquier otro contenido HTML que desees para la página --}}
    {{-- Por ejemplo: --}}
    {{-- <div>
        <h1>Interfaz del Scanner</h1>
        <p>Este es el contenido de tu vista Blade para el escáner.</p>
    </div> --}}
</x-filament-panels::page>