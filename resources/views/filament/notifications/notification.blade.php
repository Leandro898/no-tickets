<x-filament-notifications::notification
    :notification="$notification"
    class="max-w-md bg-white shadow-lg rounded-lg p-4 border-l-4 border-green-500"
    x-transition:enter-start="opacity-0"
    x-transition:leave-end="opacity-0"
>
    <h4 class="text-lg font-semibold text-green-700 mb-2">
        {{ $getTitle() }}
    </h4>

    <p class="text-gray-600 mb-2">
        {{ $getBody() }}
    </p>

    <span class="cursor-pointer text-gray-400 hover:text-gray-600" x-on:click="close">
        &times;
    </span>
</x-filament-notifications::notification>
