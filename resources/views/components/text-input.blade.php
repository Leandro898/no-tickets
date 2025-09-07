@props(['disabled' => false])

<input
    {{ $attributes->merge([
        'class' => '
            border border-gray-300
            focus:border-purple-600 focus:ring-2 focus:ring-purple-300
            rounded-lg
            bg-gray-50
            text-gray-800
            placeholder-gray-400
            px-4 py-3
            text-base
            transition-all
            w-full
            outline-none
            shadow-sm
        '
    ]) }}
>

