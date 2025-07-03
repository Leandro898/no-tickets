{{-- resources/views/filament/resources/evento-resource/pages/partials/copiar-link.blade.php --}}
<div>
    <input
        id="eventoLinkInput"
        type="text"
        readonly
        value="{{ route('eventos.show', ['evento' => $record->id]) }}"
        class="w-full border border-gray-300 rounded-md p-2 mb-4 bg-gray-50 text-gray-700 select-all focus:outline-none focus:ring-2 focus:ring-purple-600"
        onclick="this.select()"
    />
    <script>
        document.addEventListener('livewire:load', function () {
            const btnSubmit = document.querySelector('[data-action="submit"]');
            btnSubmit.addEventListener('click', function (event) {
                event.preventDefault();
                const input = document.getElementById('eventoLinkInput');
                navigator.clipboard.writeText(input.value).then(() => {
                    alert('Link copiado al portapapeles');
                });
            });
        });
    </script>
</div>
