<div class="space-y-6">

    <x-filament::card>
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-primary-500">Recaudación</h3>
            <span class="text-2xl font-bold text-primary-500">
                ${{ array_sum($recaudacionMensual) }}
            </span>
        </div>

        @if (count($recaudacionMensual))
            <canvas id="recaudacionChart" height="120"></canvas>
        @else
            <p class="text-sm text-gray-500">Todavía no tienes ventas suficientes para armar un gráfico.</p>
        @endif
    </x-filament::card>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">

        {{-- QRs generados --}}
        <x-filament::card>
            <h3 class="text-xl font-bold text-primary-500">QRs generados</h3>
            <p class="text-3xl font-extrabold text-gray-900 mt-2">
                {{ $qrsGenerados }}
            </p>
        </x-filament::card>

        {{-- QRs validados --}}
        <x-filament::card>
            <h3 class="text-xl font-bold text-primary-500">QRs validados</h3>
            <p class="text-3xl font-extrabold text-gray-900 mt-2">
                {{ $qrsValidados }}
            </p>
        </x-filament::card>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('recaudacionChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                    datasets: [{
                        label: 'Recaudación',
                        data: {!! json_encode(array_replace(array_fill(1, 12, 0), $recaudacionMensual)) !!},
                        borderColor: '#9333ea',
                        backgroundColor: 'rgba(147, 51, 234, 0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    </script>
</div>
