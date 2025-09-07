// resources/js/scanner/manualEntry.js
import { ensureOverlay, closeOverlay } from "./overlay.js";

/**
 * Abre un overlay para ingresar manualmente un código de 5 caracteres.
 * onSubmit recibe el código válido.
 * onCancel se dispara al cerrar el modal.
 */
export function showManualEntry(onSubmit, onCancel) {
  const o = ensureOverlay();

  o.innerHTML = `
  <div class="bg-white rounded-xl p-4 max-w-sm w-11/12 relative">
    <button id="btnCloseManual"
            class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">
      ×
    </button>
    <h3 class="text-lg font-semibold mb-2">Ingresar código</h3>
    <input
      id="manualInput"
      type="text"
      maxlength="5"
      class="w-full border border-gray-300 rounded px-3 py-2 mb-4 text-center text-xl font-mono"
      placeholder="ABCDE"
    />
    <button
      id="btnSubmitManual"
      class="w-full bg-violet-600 hover:bg-violet-700 text-white font-bold py-2 rounded"
    >
      Buscar
    </button>
  </div>
  `;
  o.style.display = "flex";

  const input = o.querySelector("#manualInput");
  const btn = o.querySelector("#btnSubmitManual");

  // autofocus
  setTimeout(() => input.focus(), 50);

  // Cerrar con la X → onCancel
  o.querySelector("#btnCloseManual").onclick = () => closeOverlay(onCancel);
  // Cerrar tocando fuera del modal → onCancel
  o.onclick = e => { if (e.target === o) closeOverlay(onCancel); };

  // Envía el código
  function submitCode() {
    const code = input.value.trim();
    if (code.length === 5) {
      closeOverlay();
      onSubmit(code);
    } else {
      alert("Por favor ingresá 5 caracteres.");
      input.focus();
    }
  }

  btn.onclick = submitCode;
  input.addEventListener("keydown", e => { if (e.key === "Enter") submitCode(); });
}
