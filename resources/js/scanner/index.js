// resources/js/scanner/index.js
import { startScanner, stopScanner } from "./camera.js";
import { showOverlay, closeOverlay } from "./overlay.js";
import { fetchTicket } from "./api.js";
import { showManualEntry } from "./manualEntry.js";

document.addEventListener("DOMContentLoaded", () => {
    const readerDiv = document.getElementById("reader");
    const buscarUrl = window.buscarTicketEndpoint;
    const validarUrl = window.validarTicketEndpoint;
    let scanner = null;
    let lastData = null;

    // 1) Inicializa cámara y escáner
    async function initCamera() {
        readerDiv.innerHTML = "";
        closeOverlay();
        scanner = await startScanner(onScanSuccess, () => {
            alert("No se pudo iniciar el escáner");
        });
        // CORRECCIÓN: llamadas por separado
        readerDiv.classList.remove("border-red-500", "border-green-500");
        readerDiv.classList.add("border-violet-500");
    }

    // 2) Callback tras escaneo o ingreso manual
    async function onScanSuccess(code) {
        showOverlay('<div class="text-white text-lg">🔄 Buscando…</div>', initCamera);
        await stopScanner(scanner);

        try {
            const json = await fetchTicket(buscarUrl, code.trim());
            lastData = json.data;

            if (json.status === "valid") {
                showResult("success", json.message, lastData, true);
                // CORRECCIÓN: llamadas por separado
                readerDiv.classList.remove("border-red-500", "border-violet-500");
                readerDiv.classList.add("border-green-500");
            } else {
                showResult("error", json.message, lastData, false);
                // CORRECCIÓN: llamadas por separado
                readerDiv.classList.remove("border-green-500", "border-violet-500");
                readerDiv.classList.add("border-red-500");
            }
        } catch {
            showOverlay('<div class="text-white text-lg">⚠️ Código no reconocido<br>Por favor verificá e intentá de nuevo.</div>', initCamera);
        }
    }

    // 3) Muestra popup de resultado
    function showResult(type, msg, data, canValidate) {
        const color = type === "success" ? "#16a34a" : "#dc2626";
        const icon = type === "success" ? "✅" : "⚠️";

        showOverlay(`
          <button id="close" style="
            position:absolute; top:1rem; right:1rem;
            background:none; border:none; color:white;
            font-size:1.5rem; cursor:pointer;
          ">&times;</button>
          <div style="text-align:center; margin-bottom:1rem;">
            <span style="font-size:2rem;">${icon}</span>
            <h2 style="display:inline-block; margin-left:.5rem; color:white;">
              ${type === "success" ? "ENTRADA VÁLIDA" : "ERROR"}
            </h2>
          </div>
          <p style="color:${color}; font-weight:600; margin-bottom:1rem;">${msg}</p>
          <div style="text-align:left; line-height:1.4; margin-bottom:1.5rem; color:#ddd;">
            <b>Tipo:</b> ${data?.tipo || '-'}<br>
            <b>Precio:</b> $${data?.precio || '-'}<br>
            <b>Evento:</b> ${data?.evento || '-'}<br>
            <b>Nombre:</b> ${data?.nombre || '-'}<br>
            <b>Email:</b> ${data?.email || '-'}<br>
            <b>DNI:</b> ${data?.dni || '-'}<br>
          </div>
          <div style="display:flex; justify-content:center; gap:1rem;">
            ${canValidate
                ? `<button id="btnValidate" style="
                   padding:.5rem 1rem; background:#7c3aed; border:none;
                   border-radius:.5rem; color:white; font-weight:bold;
                   cursor:pointer;
                 ">Validar</button>`
                : ""
            }
            <button id="btnAgain" style="
              padding:.5rem 1rem; background:#e5e7eb; border:none;
              border-radius:.5rem; color:#4f46e5; font-weight:bold;
              cursor:pointer;
            ">Otro</button>
          </div>
        `);

        const ov = document.getElementById("scanOverlay");
        ov.querySelector("#close").onclick = () => closeOverlay(initCamera);
        ov.querySelector("#btnAgain").onclick = () => closeOverlay(initCamera);
        if (canValidate) {
            ov.querySelector("#btnValidate").onclick = async () => {
                showOverlay('<div style="color:white;">🔄 Validando…</div>');
                const js = await fetchTicket(validarUrl, lastData.unique_code);
                showResult(js.status === "success" ? "success" : "error", js.message, js.data, false);
            };
        }
    }
      

    // 4) Link para ingreso manual
    document
        .getElementById("btnManualInput")
        .addEventListener("click", (e) => {
            e.preventDefault();
            showManualEntry(onScanSuccess, initCamera);
        });

    // 5) Arranca todo
    setTimeout(initCamera, 100);
});
