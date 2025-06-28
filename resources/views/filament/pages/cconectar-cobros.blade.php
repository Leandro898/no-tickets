<x-filament::page>

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show"
            class="mb-6 p-4 bg-red-200 border-t-4 border-red-600 text-red-900 rounded shadow-lg flex items-center gap-3 max-w-xl mx-auto">
            <svg class="w-6 h-6 text-red-700 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <div>
                <span class="font-bold">Error al vincular Mercado Pago:</span>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
            <button @click="show = false"
                class="ml-auto text-red-600 hover:text-red-800 font-bold px-2">&times;</button>
        </div>
    @endif




    @php
        $user = $this->getUser();
    @endphp

    <div class="space-y-8 max-w-xl mx-auto mt-8">
        <div class="text-2xl font-bold text-gray-800 flex items-center gap-2 mb-2">
            <!-- SVG "logo Mercado Pago" -->
            <svg class="w-8 h-8" viewBox="0 0 40 40" fill="none">
                <ellipse cx="20" cy="20" rx="18" ry="13" fill="#009ee3" />
                <path d="M13 20c2-2.5 6-3 7-3s5 .5 7 3" stroke="#fff" stroke-width="2" stroke-linecap="round"
                    fill="none" />
                <path d="M13 20c2 2.5 6 3 7 3s5-.5 7-3" stroke="#fff" stroke-width="2" stroke-linecap="round"
                    fill="none" />
            </svg>
            Cobros / Mercado Pago
        </div>
        <p class="text-gray-600 mb-4">
            Conectá tu cuenta de Mercado Pago para recibir los pagos directamente en tu billetera.
        </p>

        @if ($user->hasMercadoPagoAccount())
            <div class="p-4 bg-green-50 border-l-4 border-green-400 text-green-800 rounded-lg shadow">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="font-semibold">¡Cuenta de Mercado Pago conectada!</span>
                </div>
                <div class="ml-2">
                    <span class="font-bold">ID MP:</span> <span class="font-mono">{{ $user->mp_user_id }}</span><br>
                    <span class="font-bold">Válido hasta:</span> {{ $user->mp_expires_in?->format('Y-m-d H:i') }}
                </div>
            </div>

            <div x-data="{ open: false }" class="mt-6">
                <button @click="open = true"
                    class="inline-flex items-center px-6 py-3 bg-red-50 text-red-700 font-semibold rounded-lg shadow hover:bg-red-100 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Desvincular cuenta
                </button>

                <div x-show="open" x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 transition">
                    <div class="bg-white w-full max-w-md rounded-lg p-6 shadow-lg">
                        <h2 class="text-lg font-bold text-gray-800 mb-2">¿Desvincular Mercado Pago?</h2>
                        <p class="text-sm text-gray-700">
                            ¿Estás seguro? Si no tenés otro método de pago configurado, se pausarán tus ventas.
                        </p>
                        <div class="mt-6 flex justify-end gap-4">
                            <button @click="open = false"
                                class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded hover:bg-gray-100 transition">
                                Cancelar
                            </button>
                            <form action="{{ route('mercadopago.unlink') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded transition">
                                    Desvincular
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center">
                <a href="{{ route('mercadopago.connect') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-[#009ee3] text-white font-semibold rounded-lg shadow hover:bg-[#0077b6] transition focus:outline-none focus:ring-2 focus:ring-[#009ee3] focus:ring-opacity-50">
                    <!-- SVG "logo Mercado Pago" -->
                    <svg class="w-7 h-7 bg-white rounded-full p-1" viewBox="0 0 40 40" fill="none">
                        <ellipse cx="20" cy="20" rx="18" ry="13" fill="#009ee3" />
                        <path d="M13 20c2-2.5 6-3 7-3s5 .5 7 3" stroke="#fff" stroke-width="2" stroke-linecap="round"
                            fill="none" />
                        <path d="M13 20c2 2.5 6 3 7 3s5-.5 7-3" stroke="#fff" stroke-width="2" stroke-linecap="round"
                            fill="none" />
                    </svg>
                    <span class="text-base font-semibold">Conectar con Mercado Pago</span>
                </a>

                <p class="text-sm text-gray-500 mt-2 text-center max-w-xs">
                    Si no conectás tu cuenta, no podrás recibir pagos por tus ventas.
                </p>
            </div>
        @endif
    </div>
</x-filament::page>
