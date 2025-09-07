@props(['slot'])

{{-- Fila de separación (40px) --}}
<tr>
    <td style="padding-top:40px; line-height:1px; font-size:1px;">
        &nbsp;
    </td>
</tr>

{{-- Footer habitual --}}
<tr>
    <td>
        <table
            class="footer"
            align="center"
            width="100%"
            cellpadding="0"
            cellspacing="0"
            role="presentation"
        >
            <tr>
                <td
                    class="content-cell"
                    align="center"
                    style="padding:10px 0; color:#6b7280; font-size:12px;"
                >
                    {!! $slot !!}
                </td>
            </tr>
        </table>
    </td>
</tr>
