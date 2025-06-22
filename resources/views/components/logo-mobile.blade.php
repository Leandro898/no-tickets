@once
    @push('styles')
        <style>
            @media (min-width: 768px) {
                .logo-mobile {
                    display: none;
                }
            }
        </style>
    @endpush
@endonce

<div class="logo-mobile flex items-center pl-4 font-bold text-lg text-primary-600">
    <a href="{{ url('/admin/eventos') }}">
        Innova Ticket
    </a>
</div>
