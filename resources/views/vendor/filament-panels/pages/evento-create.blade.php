{{-- resources/views/vendor/filament-panels/resources/pages/evento-create.blade.php --}}
<x-filament-panels::page>
    <div x-data="{ showModal: false }" class="relative">

        {{-- FORMULARIO --}}
        <form 
            x-on:submit.prevent="showModal = true"
            class="space-y-6 bg-white p-6 rounded-lg shadow"
        >
            {{ $this->form }}

            <div class="flex justify-end gap-2 mt-6">
                <a href="{{ $this->getResource()::getUrl('index') }}">
                    <x-filament::button color="secondary" size="sm">
                        Cancelar
                    </x-filament::button>
                </a>

                <x-filament::button type="submit" color="primary" size="sm">
                    Crear Evento
                </x-filament::button>
            </div>
        </form>

        {{-- MODAL --}}
        <div
            x-show="showModal"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            x-on:click.self="showModal = false" {{-- Cierra al hacer click fuera --}}
        >
            <div
                x-transition.scale.95.duration.200ms
                class="bg-white border-2 border-purple-200 rounded-2xl shadow-2xl w-full max-w-lg p-6 mx-4"
            >
                <h2 class="text-xl font-semibold mb-4 text-purple-700">¿Crear este evento?</h2>
                <p class="mb-6 text-gray-700">
                    Una vez creado, serás redirigido a agregar las entradas.
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        class="px-4 py-2 border border-purple-600 text-purple-600 rounded hover:bg-purple-50 transition"
                        x-on:click="showModal = false"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 bg-purple-700 text-white rounded hover:bg-purple-800 transition"
                        x-on:click="
                            showModal = false;
                            $wire.create();
                        "
                    >
                        Sí, crear
                    </button>
                </div>
            </div>
        </div>

        {{-- Aviso de datos sin guardar --}}
        <x-filament-panels::page.unsaved-data-changes-alert />
    </div>
</x-filament-panels::page>
