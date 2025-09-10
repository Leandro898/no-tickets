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

    @if ($passwordCorrect)
    {{-- Formulario de registro de datos personales --}}
    <form wire:submit.prevent="register" class="space-y-4">
        {{-- ... código del formulario de registro ... --}}
        <p class="text-gray-700 font-medium">Completa tus datos para obtener tu invitación:</p>
        <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre completo</label>
            <input type="text" id="nombre" wire:model="nombre" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" wire:model="email" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono (opcional)</label>
            <input type="text" id="telefono" wire:model="telefono"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('telefono')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="dni" class="block text-sm font-medium text-gray-700">DNI (opcional)</label>
            <input type="text" id="dni" wire:model="dni"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('dni')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit"
            class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-700 hover:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
            Registrarme y obtener mi invitación
        </button>
    </form>
    @else
    {{-- Formulario de contraseña --}}
    <form wire:submit.prevent="submitPassword" class="space-y-4">
        {{-- ... código del formulario de contraseña ... --}}
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
            class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-700 hover:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
            Acceder
        </button>
    </form>
    @endif
</div>