// resources/js/scanner/overlay.js
let _overlay = null;

export function ensureOverlay() {
    if (!_overlay) {
        _overlay = document.createElement("div");
        Object.assign(_overlay.style, {
            position: "fixed",
            top: 0,
            left: 0,
            width: "100%",
            height: "100%",
            background: "rgba(0,0,0,0.75)",
            display: "none",
            alignItems: "center",
            justifyContent: "center",
            zIndex: "9999",
        });
        document.body.appendChild(_overlay);
    }
    return _overlay;
}

/**
 * @param {string} innerHtml — El HTML interno de la tarjeta
 * @param {Function} [onClosed] — Callback que se ejecuta al cerrar
 */
export function showOverlay(innerHtml, onClosed) {
    const o = ensureOverlay();
    o.innerHTML = `
    <div
      style="
        position: relative;
        background: #191225;
        color: #fff;
        max-width: 360px;
        width: 90%;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 16px rgba(0,0,0,0.5);
      "
    >
      <button
        id="overlayClose"
        style="
          position:absolute; top:0.5rem; right:0.75rem;
          background:none;border:none;color:#fff;
          font-size:1.5rem;cursor:pointer;
        "
      >&times;</button>
      ${innerHtml}
    </div>
    `;
    o.style.display = "flex";

    // botón de cierre
    o.querySelector("#overlayClose")
        .onclick = () => closeOverlay(onClosed);

    // clic fuera del modal
    o.addEventListener("click", e => {
        if (e.target === o) closeOverlay(onClosed);
    });
}

export function closeOverlay(onClosed) {
    const o = ensureOverlay();
    o.style.display = "none";
    o.innerHTML = "";
    if (typeof onClosed === "function") onClosed();
}
