<x-filament::page>
    @php
        $user = $this->getUser();
    @endphp

    <div class="space-y-6">
        <div class="text-lg font-semibold">
            Conecta tu cuenta de Mercado Pago para recibir los pagos directamente en tu billetera.
        </div>

        @if ($user->hasMercadoPagoAccount())
            <div class="p-4 bg-green-100 text-green-800 rounded-lg">
                ✅ Cuenta conectada<br>
                <strong>ID MP:</strong> {{ $user->mp_user_id }}<br>
                <strong>Válido hasta:</strong> {{ $user->mp_expires_in?->format('Y-m-d H:i') }}
            </div>

            <div x-data="{ open: false }" class="mt-4">
                <button 
                    @click="open = true"
                    class="inline-flex items-center px-6 py-3 bg-blue text-gray font-semibold rounded-lg hover:bg-red transition">
                    Desvincular cuenta
                </button>

                <div 
                    x-show="open"
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 transition"
                >
                    <div class="bg-white w-full max-w-md rounded-lg p-6 shadow-lg">
                        <h2 class="text-lg font-bold text-gray-800 mb-2">¿Desvincular Mercado Pago?</h2>
                        <p class="text-sm text-gray-700">
                            ¿Estás seguro? Si no tenés otro método de pago configurado, se pausarán tus ventas.
                        </p>

                        <div class="mt-6 flex justify-end gap-4">
                            <button 
                                @click="open = false"
                                class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded hover:bg-gray-100 transition"
                            >
                                Cancelar
                            </button>

                            <form action="{{ route('mercadopago.unlink') }}" method="POST">
                                @csrf
                                <button 
                                    type="submit"
                                    class="px-4 py-2 text-sm font-semibold text-gray bg-red-600 hover:bg-red-700 rounded transition"
                                >
                                    Desvincular
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <a 
                href="{{ route('mercadopago.connect') }}" 
                class="inline-flex items-center px-6 py-3 bg-blue-600 text-gray font-semibold rounded-lg hover:bg-blue-700 transition">
                Conectar con Mercado Pago
            </a>
        @endif
    </div>
</x-filament::page>

