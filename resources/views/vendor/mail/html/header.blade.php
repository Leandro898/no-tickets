{{-- resources/views/vendor/mail/html/header.blade.php --}}
@props(['url'])

<tr>
  <td align="center" style="padding: 30px 0;">
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
            <span style="
              font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
              font-size: 24px;
              font-weight: 700;
              color: #7c3aed;
              line-height: 1;
              display: inline-block;
            ">
              {{ config('app.name') }}
            </span>
          </a>
        </td>
      </tr>
    </table>
  </td>
</tr>
