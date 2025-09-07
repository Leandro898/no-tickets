@props([
    'label' => null,
    'labelHidden' => false,
    'required' => false,
])

<fieldset
    {{
        $attributes->class([
            'fi-fieldset rounded-xl border border-gray-200 p-6',
        ])
    }}
>
    @if (filled($label))
        <legend
            @class([
                '-ms-2 px-2 text-sm font-medium leading-6 text-gray-950',
                'sr-only' => $labelHidden,
            ])
        >
            {{-- Deliberately poor formatting to ensure that the asterisk sticks to the final word in the label. --}}
            {{ $label }}@if ($required)<sup class="text-danger-600 font-medium">*</sup>
            @endif
        </legend>
    @endif

    {{ $slot }}
</fieldset>
