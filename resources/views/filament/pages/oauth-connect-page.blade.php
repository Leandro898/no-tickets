<x-filament::page>

    {{-- Mensaje de error --}}
    @if (session('error'))
        <div 
            x-data="{ show: true }" 
            x-show="show"
            x-transition
            class="mb-6 p-4 bg-red-100 border-l-4 border-red-600 text-red-900 rounded shadow flex items-center gap-4 max-w-xl mx-auto"
        >
            <x-heroicon-o-x-circle class="w-6 h-6 text-red-700 flex-shrink-0" />
            <div class="flex-1">
                <p class="font-bold">Error al vincular Mercado Pago:</p>
                <p class="font-semibold">{{ session('error') }}</p>
            </div>
            <button 
                @click="show = false" 
                class="ml-4 text-red-600 hover:text-red-800 font-bold text-2xl leading-none px-2"
                aria-label="Cerrar mensaje de error"
            >
                &times;
            </button>
        </div>
    @endif

    @php $user = $this->getUser(); @endphp

    <div class="max-w-xl mx-auto mt-10 space-y-12">

        <header class="mb-6">
            <h1 class="text-3xl font-extrabold text-[#7c3aed] select-none">Cobros / Mercado Pago</h1>
            <p class="mt-2 text-gray-700 leading-relaxed">
                Conectá tu cuenta de Mercado Pago para recibir los pagos directamente en tu billetera.
            </p>
        </header>

        @if ($user->hasMercadoPagoAccount())
            <section class="bg-[#f3e8ff] border-l-8 border-[#7c3aed] rounded-lg shadow p-6">
                <div class="flex items-center gap-3 mb-4">
                    <x-heroicon-o-check-circle class="w-8 h-8 text-[#7c3aed]" />
                    <h2 class="text-xl font-semibold text-[#5b21b6]">¡Cuenta de Mercado Pago conectada!</h2>
                </div>
                <dl class="ml-2 space-y-2 text-[#5b21b6] font-mono text-sm">
                    <div>
                        <dt class="inline font-bold">ID MP:</dt>
                        <dd class="inline">{{ $user->mp_user_id }}</dd>
                    </div>
                    <div>
                        <dt class="inline font-bold">Válido hasta:</dt>
                        <dd class="inline">{{ $user->mp_expires_in?->format('Y-m-d H:i') }}</dd>
                    </div>
                </dl>
            </section>

            <div x-data="{ open: false }" class="mt-10 text-center">
                <button
                    @click="open = true"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-[#7c3aed] text-white font-semibold rounded-lg shadow hover:bg-[#5b21b6] transition focus:outline-none focus:ring-2 focus:ring-[#5b21b6] focus:ring-opacity-50"
                >
                    <x-heroicon-o-x-circle class="w-5 h-5" />
                    Desvincular cuenta
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition
                    class="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-90"
                    role="dialog"
                    aria-modal="true"
                >
                    <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-lg border border-[#7c3aed]">
                        <h3 class="text-lg font-bold text-[#5b21b6] mb-4">¿Desvincular Mercado Pago?</h3>
                        <p class="text-[#5b21b6] mb-6">
                            ¿Estás seguro? Si no tenés otro método de pago configurado, se pausarán tus ventas.
                        </p>
                        <div class="flex justify-end gap-4">
                            <button
                                @click="open = false"
                                class="px-4 py-2 text-[#5b21b6] border border-[#7c3aed] rounded hover:bg-[#ede9fe] transition"
                            >
                                Cancelar
                            </button>
                            <form action="{{ route('mercadopago.unlink') }}" method="POST">
                                @csrf
                                <button
                                    type="submit"
                                    class="px-4 py-2 font-semibold text-white bg-[#7c3aed] rounded hover:bg-[#5b21b6] transition"
                                >
                                    Desvincular
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center space-y-4">
                <a
                    href="{{ route('mercadopago.connect') }}"
                    class="inline-flex items-center gap-3 px-6 py-3 bg-[#7c3aed] text-white font-semibold rounded-lg shadow hover:bg-[#5b21b6] transition focus:outline-none focus:ring-2 focus:ring-[#5b21b6] focus:ring-opacity-50"
                >
                    <span class="text-base">Conectar con Mercado Pago</span>
                </a>
                <p class="text-gray-600 max-w-xs text-center">
                    Si no conectás tu cuenta, no podrás recibir pagos por tus ventas.
                </p>
            </div>
        @endif

    </div>

</x-filament::page>
