// resources/js/scanner/api.js
export async function fetchTicket(url, code) {
    // 1) Intentamos leer la meta; si no existe, usamos window.csrfToken
    let token = null;
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        token = meta.content;
    } else if (window.csrfToken) {
        token = window.csrfToken;
    } else {
        console.warn('CSRF token no encontrado en <meta> ni en window.csrfToken');
    }

    const res = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": token,
            "Accept": "application/json",
        },
        body: JSON.stringify({ code }),
    });

    if (!res.ok) {
        // opcional: loguear el status para debugging
        console.error(`fetchTicket falló: ${res.status}`);
    }

    return res.json();
}
