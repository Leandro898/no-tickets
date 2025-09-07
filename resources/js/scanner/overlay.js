// resources/js/scanner/overlay.js
let _overlay = null;

/**
 * Crea o retorna el div overlay único para todo el flujo
 */
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
 * Muestra contenido en el overlay. Si onClosed está definido,
 * se ejecutará cuando se cierre (clic en × o fuera).
 * @param {string} innerHtml 
 * @param {Function} [onClosed]
 */
export function showOverlay(innerHtml, onClosed) {
  const o = ensureOverlay();
  o.innerHTML = `
    <div style="
      position: relative;
      background: #191225;
      color: #fff;
      max-width: 360px;
      width: 90%;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 16px rgba(0,0,0,0.5);
    ">
      <button id="overlayClose" style="
       position:absolute;
       top:0.5rem;           /* más arriba */
       right:0.5rem;
       width:1.5rem;         /* área justo alrededor de la X */
       height:1.5rem;
       padding:0;            /* sin padding extra */
       background:none;
       border:none;
       display:flex;
       align-items:center;   /* centra verticalmente el icono */
       justify-content:center;
       color:#fff;
       font-size:1rem;       /* ajusta tamaño al área */
       line-height:1.5rem;   /* centra mejor la X */
       cursor:pointer;
     ">✖</button>
      ${innerHtml}
    </div>
  `;
  o.style.display = "flex";

  // cerrar al hacer click en la X
  o.querySelector("#overlayClose").onclick = () => closeOverlay(onClosed);

  // cerrar al hacer click fuera del modal
  o.onclick = (e) => {
    if (e.target === o) closeOverlay(onClosed);
  };
}

/**
 * Oculta el overlay y dispara callback onClosed si existe
 */
export function closeOverlay(onClosed) {
  const o = ensureOverlay();
  o.style.display = "none";
  o.innerHTML = "";
  if (typeof onClosed === "function") {
    onClosed();
  }
}
