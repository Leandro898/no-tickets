{{-- resources/views/filament/resources/evento-resource/pages/reportes-evento.blade.php --}}
<x-filament::page>
    {{-- Inyectamos nuestro componente Livewire, que ya incluye el canvas y el script --}}
    <livewire:reportes-evento :evento-id="$record->id" />
</x-filament::page>