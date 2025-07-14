@props([
    'url',
    // Puedes pasar aquí un color en HEX o en CSS var/class:
    'color' => '#7c3aed',
    'align' => 'center',
])

<table
    class="action"
    align="{{ $align }}"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    role="presentation"
>
    <tr>
        <td align="{{ $align }}">
            <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td>
                        <a
                            href="{{ $url }}"
                            target="_blank"
                            rel="noopener"
                            style="
                                display: inline-block;
                                background-color: #7E22CE;
                                border-radius: 3px;
                                color: white;
                                padding: 12px 24px;
                                text-decoration: none;
                                font-weight: bold;
                            "
                        >
                            {{ $slot }}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
