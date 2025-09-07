<x-filament::page>
    {{ $this->table }}
</x-filament::page>

<script>
window.setInterval(() => {
  document.querySelectorAll('input[placeholder="Search"]').forEach(input => {
    if (input.placeholder !== 'Buscar...') input.placeholder = 'Buscar...';
  });
}, 500);
</script>
