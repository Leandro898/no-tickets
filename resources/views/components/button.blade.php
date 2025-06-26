{{-- resources/views/components/button.blade.php --}}
<button
    {{ $attributes->merge([
        'type'  => $attributes->get('type') ?? 'submit',
        'class' => 'inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent
                    rounded-md font-semibold text-xs text-white uppercase tracking-widest
                    hover:bg-purple-500 focus:outline-none focus:ring-2 focus:ring-offset-2
                    focus:ring-purple-500 disabled:opacity-25 transition'
    ]) }}>
    {{ $slot }}
</button>
