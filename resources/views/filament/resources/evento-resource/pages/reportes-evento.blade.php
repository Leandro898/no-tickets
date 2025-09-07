{{-- resources/views/filament/resources/evento-resource/pages/reportes-evento.blade.php --}}
<x-filament::page>
    <div class="px-6 flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold"></h2>
        <x-filament::button
            tag="a"
            :href="\App\Filament\Resources\EventoResource::getUrl('detalles', ['record' => $record])"
            size="sm"
            color="primary"
            icon="heroicon-o-arrow-left"
        >
            Volver a Detalles
        </x-filament::button>
    </div>

    <div class="px-6">
        <livewire:reportes-evento :evento-id="$record->id" />
    </div>
</x-filament::page>
