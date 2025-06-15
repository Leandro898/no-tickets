{{-- resources/views/filament/resources/evento-resource/pages/scanner-interface.blade.php --}}

<x-filament-panels::page>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            {{ $this->getTitle() }}
        </h2>
    </x-slot>

    {{-- Carga tu componente Livewire del escáner --}}
    @livewire('qr-scanner')

</x-filament-panels::page>