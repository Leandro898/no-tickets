{{-- resources/views/.../evento-create.blade.php --}}
<x-filament-panels::page>
    <form 
        x-data 
        x-on:submit.prevent="
            if (confirm('¿Estás seguro de crear este evento?')) {
                $wire.create()
            }
        "
        wire:submit="create"
        class="space-y-6 bg-white p-6 rounded-lg shadow"
    >
        {{ $this->form }}

        <div class="flex justify-end gap-2 mt-6">
            <a href="{{ $this->getResource()::getUrl('index') }}">
                <x-filament::button color="secondary" size="sm">
                    Cancelar
                </x-filament::button>
            </a>

            {{-- Aquí interceptamos el submit para pedir confirm --}}
            <x-filament::button type="submit" color="primary" size="sm">
                Crear Evento
            </x-filament::button>
        </div>
    </form>

    <x-filament-panels::page.unsaved-data-changes-alert />
</x-filament-panels::page>
