<x-filament::page>
    <div class="space-y-6">
        <div class="text-lg font-semibold">
            Conecta tu cuenta de Mercado Pago para recibir los pagos directamente en tu billetera.
        </div>

        <a 
            href="{{ route('mercadopago.connect') }}" 
            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
            Conectar con Mercado Pago
        </a>
    </div>
</x-filament::page>
