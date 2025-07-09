// resources/js/scanner/manualEntry.js
import { ensureOverlay, closeOverlay } from "./overlay.js";

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
        class="w-full border border-gray-300 rounded px-3 py-2 mb-4
               text-center text-xl font-mono"
        placeholder="ABCDE"
      />
      <button
        id="btnSubmitManual"
        class="w-full bg-violet-600 hover:bg-violet-700 text-white
               font-bold py-2 rounded"
      >
        Buscar
      </button>
    </div>
    `;
    o.style.display = "flex";

    // 1) Referencias
    const input = o.querySelector("#manualInput");
    const btn = o.querySelector("#btnSubmitManual");

    // 2) Autofocus
    setTimeout(() => input.focus(), 50);

    // 3) Cerrar con la X → dispara onCancel (initCamera)
    o.querySelector("#btnCloseManual")
        .addEventListener("click", () => closeOverlay(onCancel));

    // 4) Cerrar tocando fuera → dispara onCancel
    o.addEventListener("click", e => {
        if (e.target === o) closeOverlay(onCancel);
    });

    // 5) Función de búsqueda
    function submitCode() {
        const code = input.value.trim();
        if (code.length === 5) {
            closeOverlay();      // oculta el modal
            onSubmit(code);      // llama a onScanSuccess(code)
        } else {
            alert("Por favor ingresá 5 caracteres.");
            input.focus();
        }
    }

    // 6) Click en “Buscar”
    btn.addEventListener("click", submitCode);

    // 7) Tecla Enter
    input.addEventListener("keydown", e => {
        if (e.key === "Enter") submitCode();
    });
}
