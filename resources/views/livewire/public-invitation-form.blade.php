<div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg mx-auto">
    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $evento->nombre }}</h1>
        <p class="text-gray-600">{{ $evento->ubicacion }}</p>
        <p class="text-gray-600 mb-6">{{ \Carbon\Carbon::parse($evento->fecha_inicio)->isoFormat('dddd D [de] MMMM [a las] H:mm') }}</p>
    </div>

    {{-- Muestra los mensajes de la sesión --}}
    @if (session()->has('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative my-4" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif
    @if (session()->has('message'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative my-4" role="alert">
        <span class="block sm:inline">{{ session('message') }}</span>
    </div>
    @endif

    {{-- Muestra el loader mientras se valida la contraseña --}}
    <div wire:loading wire:target="submitPassword" class="text-center">
        <svg class="mx-auto h-12 w-12 text-purple-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-gray-600 mt-2">Cargando formulario...</p>
    </div>

    <div wire:loading.remove wire:target="submitPassword">
        @if ($passwordCorrect)
        {{-- Formulario de registro de datos personales --}}
        <form wire:submit.prevent="register" class="space-y-4">
            <p class="text-gray-700 font-medium">Completa los datos para obtener tu invitación:</p>

            {{-- Bucle para renderizar cada bloque de invitado --}}
            @foreach ($invitados as $index => $invitado)
            <div class="bg-gray-50 p-4 rounded-md border border-gray-200 relative mb-4">
                {{-- Contenedor flexbox para alinear el título y el botón --}}
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-gray-800">Invitado #{{ $index + 1 }}</p>
                    @if(count($invitados) > 1)
                    <button type="button" wire:click="removeInvitado({{ $index }})"
                        class="text-red-500 hover:text-red-700 text-xl font-bold leading-none"
                        wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed" wire:target="removeInvitado({{ $index }})">
                        {{-- Muestra el loader mientras se remueve el invitado --}}
                        <span wire:loading.remove wire:target="removeInvitado({{ $index }})">&times;</span>
                        <span wire:loading wire:target="removeInvitado({{ $index }})">
                            <svg class="animate-spin h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                    @endif
                </div>
                <div class="space-y-4">
                    <div>
                        <label for="nombre-{{ $index }}" class="block text-sm font-medium text-gray-700">Nombre completo</label>
                        <input type="text" id="nombre-{{ $index }}" wire:model="invitados.{{ $index }}.nombre" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('invitados.'.$index.'.nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email-{{ $index }}" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email-{{ $index }}" wire:model="invitados.{{ $index }}.email" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('invitados.'.$index.'.email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="telefono-{{ $index }}" class="block text-sm font-medium text-gray-700">Teléfono (opcional)</label>
                        <input type="text" id="telefono-{{ $index }}" wire:model="invitados.{{ $index }}.telefono"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('invitados.'.$index.'.telefono')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="dni-{{ $index }}" class="block text-sm font-medium text-gray-700">DNI (opcional)</label>
                        <input type="text" id="dni-{{ $index }}" wire:model="invitados.{{ $index }}.dni"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('invitados.'.$index.'.dni')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Botón para agregar más invitados --}}
            <button type="button" wire:click="addInvitado"
                class="w-full py-2 px-4 border-2 border-dashed border-gray-300 rounded-md text-gray-700 hover:border-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed">
                <span wire:loading.remove wire:target="addInvitado">+ Agregar otro invitado</span>
                <span wire:loading wire:target="addInvitado">
                    <svg class="animate-spin h-5 w-5 text-gray-700 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>

            <button type="submit"
                class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-700 hover:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed">
                <span wire:loading.remove wire:target="register">Registrarme y obtener mi invitación</span>
                <span wire:loading wire:target="register">Cargando...</span>
            </button>
        </form>
        @else
        {{-- Formulario de contraseña --}}
        <form wire:submit.prevent="submitPassword" class="space-y-4">
            <p class="text-gray-700 font-medium text-center">Ingresa la contraseña para acceder al registro de invitados.</p>
            <div>
                <label for="password" class="sr-only">Contraseña</label>
                <input type="password" id="password" wire:model="password" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 placeholder-gray-400"
                    placeholder="Contraseña de invitación">
                @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-700 hover:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed">
                <span wire:loading.remove wire:target="submitPassword">Acceder</span>
                <span wire:loading wire:target="submitPassword">Cargando...</span>
            </button>
        </form>
        @endif
    </div>
</div>