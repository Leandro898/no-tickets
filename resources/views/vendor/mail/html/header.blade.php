@props(['url'])

<tr>
  <td align="center" style="padding: 30px 0;">
    <!-- contenedor óvalo -->
    <table role="presentation" cellpadding="0" cellspacing="0">
      <tr>
        <td
          style="
            background-color: #ffffff;
            border-radius: 9999px;
            padding: 10px 30px;
            text-align: center;
          "
        >
          <a
            href="{{ $url }}"
            style="display: inline-block; text-decoration: none;"
          >
            <!-- si usas imagen -->
            <img
              src="{{ asset('images/logo-tickets-pro.png') }}"
              alt="TicketsPro"
              width="120"
              style="display: block; max-width: 100%; height: auto;"
            >

            <!-- o si prefieres texto puro, sustituye el <img> por: -->
            <!--
            <span style="
              font-family: sans-serif;
              font-size: 24px;
              font-weight: bold;
              color: #7c3aed;
            ">
              TicketsPro
            </span>
            -->
          </a>
        </td>
      </tr>
    </table>
  </td>
</tr>
