{{-- resources/views/livewire/reportes-evento.blade.php --}}

<div class="space-y-10">

    {{-- Card de recaudación global --}}
    <div class="rounded-2xl bg-white/90 border-l-8 border-purple-500 shadow-lg p-8 w-full">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-2 mb-4">
            <div>
                <h3 class="text-3xl font-extrabold text-purple-700 leading-none drop-shadow">
                    Recaudación global
                </h3>
                <div class="text-sm text-gray-500 font-semibold mt-1">
                    # Unidades vendidas
                </div>
            </div>
            <div class="flex flex-col items-end">
                <span class="text-4xl font-bold text-purple-700">
                    ${{ number_format(array_sum($recaudacionDiaria), 2) }}
                </span>
                <span class="text-base text-gray-400">total acumulado</span>
            </div>
        </div>

        <div class="mt-6">
            @if(array_sum($recaudacionDiaria) > 0)
            <div wire:ignore>
                <canvas id="recaudacionChart" height="120"></canvas>
            </div>
            @else
            <div class="text-gray-400 italic py-8 text-center">
                Todavía no tienes ventas suficientes para armar un gráfico.
            </div>
            @endif
        </div>
    </div>

    {{-- Métricas rápidas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 w-full">
        {{-- QRs generados --}}
        <div class="bg-gradient-to-br from-purple-100 to-white border-l-4 border-purple-400 rounded-xl shadow p-6 flex items-center gap-4">
            <div class="rounded-full bg-purple-50 text-purple-700 p-3 shadow-inner">
                <x-heroicon-o-qr-code class="w-8 h-8" />
            </div>
            <div>
                <div class="text-lg font-bold text-purple-700">QRs generados</div>
                <div class="text-2xl font-extrabold text-gray-900">{{ $qrsGenerados }}</div>
            </div>
        </div>

        {{-- QRs validados --}}
        <div class="bg-gradient-to-br from-purple-100 to-white border-l-4 border-purple-400 rounded-xl shadow p-6 flex items-center gap-4">
            <div class="rounded-full bg-purple-50 text-purple-700 p-3 shadow-inner">
                <x-heroicon-o-check-circle class="w-8 h-8" />
            </div>
            <div>
                <div class="text-lg font-bold text-purple-700">QRs validados</div>
                <div class="text-2xl font-extrabold text-gray-900">{{ $qrsEscaneados }}</div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
@once
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endonce
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('recaudacionChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($labels),
                datasets: [{
                    label: 'Recaudación ($)',
                    data: @json(array_values($recaudacionDiaria)),
                    borderColor: '#9333ea',
                    backgroundColor: 'rgba(147, 51, 234, 0.15)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                }],
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Día'
                        },
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total recaudado ($)'
                        },
                    },
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `$ ${ctx.parsed.y.toLocaleString(undefined, { minimumFractionDigits: 2 })}`
                        }
                    }
                }
            }
        });
    });
</script>
@endpush