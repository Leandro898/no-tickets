{{-- resources/views/vendor/mail/html/layout.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        /* ==== Tu branding ==== */
        body,
        table,
        td {
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #F0E6FF !important;
            /* un lila muy clarito */
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .wrapper {
            width: 100%;
            background-color: #F0E6FF;
            /* mismo lila de body */
            padding: 40px 0;
        }

        .content {
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            background-color: #FFFFFF;
            padding: 24px 0;
            text-align: center;
        }

        .inner-body {
            background-color: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .content-cell {
            padding: 32px;
            color: #1F2937;
            font-size: 16px;
            line-height: 1.6;
        }

        .button {
            display: inline-block;
            background-color: #8B5CF6;
            /* tu púrpura de marca */
            color: #ffffff !important;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
        }

        .button:hover {
            background-color: #7C3AED;
            /* hover un pelín más oscuro */
        }

        .subcopy {
            padding: 24px 32px;
            font-size: 13px;
            color: #6B7280;
            border-top: 1px solid #E5E7EB;
        }

        .footer {
            text-align: center;
            padding: 24px 0 0;
            font-size: 12px;
            color: #9CA3AF;
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
                box-sizing: border-box;
            }
        }
    </style>

    {!! $head ?? '' !!}
</head>

<body bgcolor="#F0E6FF" style="background-color: #F0E6FF;">
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation"
        style="background-color: #F0E6FF;">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">

                    {{-- Header slot --}}
                    {!! $header ?? '' !!}

                    {{-- Cuerpo --}}
                    <tr>
                        <td>
                            <table class="inner-body" width="100%" cellpadding="0" cellspacing="0"
                                role="presentation">
                                <tr>
                                    <td class="content-cell">
                                        {{-- Inyecta directamente el HTML del slot sin volver a parsear Markdown --}}
                                        {!! $slot !!}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Subcopy slot --}}
                    @isset($subcopy)
                        <tr>
                            <td>
                                <div class="subcopy">{!! $subcopy !!}</div>
                            </td>
                        </tr>
                    @endisset

                    {{-- Footer slot con espacio abajo --}}
                    <tr>
                        <td style="padding-bottom:40px;">
                            <div class="footer">
                                © {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
