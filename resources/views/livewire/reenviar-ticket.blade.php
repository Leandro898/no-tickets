<div>
    <button wire:click="reenviar" wire:loading.attr="disabled"
        class="bg-purple-600 hover:bg-purple-700 text-white w-40 py-2 rounded text-xs capitalize disabled:opacity-50">
        <span wire:loading.remove>Reenviar por email</span>
        <span wire:loading>Enviando...</span>
    </button>
</div>
