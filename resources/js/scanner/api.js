// resources/js/scanner/api.js
export async function fetchTicket(url, code) {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const res = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": token,
            "Accept": "application/json",
        },
        body: JSON.stringify({ code }),
    });
    return res.json();
}
  