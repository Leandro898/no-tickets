// resources/js/scanner/index.js
import { startScanner, stopScanner } from "./camera.js";
import { showOverlay, closeOverlay, ensureOverlay } from "./overlay.js";
import { fetchTicket } from "./api.js";
import { showManualEntry } from "./manualEntry.js";

document.addEventListener("DOMContentLoaded", () => {
  const readerDiv = document.getElementById("reader");
  const buscarUrl = window.buscarTicketEndpoint;
  const validarUrl = window.validarTicketEndpoint;
  let scanner;

  // 1) Inicializa el escáner
  async function initCamera() {
    readerDiv.innerHTML = "";
    closeOverlay();
    scanner = await startScanner(onScanSuccess, onScanError);
    readerDiv.classList.replace("border-red-500", "border-violet-500");
    readerDiv.classList.replace("border-green-500", "border-violet-500");
  }

  // 2) Al leer un código
  async function onScanSuccess(code) {
    await stopScanner(scanner);

    // Mostrar “Buscando…” sin callback
    showOverlay('<div class="text-white text-lg">🔄 Buscando…</div>');

    let json;
    try {
      json = await fetchTicket(buscarUrl, code.trim());
      console.log("fetchTicket →", json);
    } catch (err) {
      console.error("Error en fetchTicket:", err);
      closeOverlay();
      showOverlay(
        '<div class="text-white text-lg">⚠️ Error de red o código no reconocido.<br>Intentá de nuevo.</div>',
        initCamera
      );
      return;
    }

    closeOverlay();

    const valid = json.status === "valid";
    readerDiv.classList.toggle("border-green-500", valid);
    readerDiv.classList.toggle("border-red-500", !valid);
    readerDiv.classList.remove("border-violet-500");

    try {
      showResult(
        valid ? "success" : "error",
        json.message,
        json.data,
        valid
      );
    } catch (uiErr) {
      console.error("Error en showResult:", uiErr);
      initCamera();
    }
  }

  // 3) Error al iniciar la cámara
  function onScanError(err) {
    console.error("startScanner falló:", err);
    showOverlay(
      '<div class="text-white text-lg">⚠️ No se pudo iniciar el escáner.</div>',
      initCamera
    );
  }

  // 4) Genera el modal de resultado y validación
  function showResult(type, msg, data, canValidate) {
    const color = type === "success" ? "#16a34a" : "#dc2626";
    const icon = type === "success" ? "✅" : "⚠️";

    const html = `
      <div style="position:relative; padding:1rem; min-width:320px; color:#fff;">
        <div style="text-align:center; margin-bottom:1rem;">
          <span style="font-size:2rem;">${icon}</span>
          <h2 style="display:inline-block; margin-left:.5rem;">
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
          <b>DNI:</b> ${data?.dni || '-'}
        </div>
        <div style="display:flex; justify-content:center; gap:1rem;">
          ${canValidate
        ? `<button id="btnValidate" style="
                 padding:.5rem 1rem; background:#7c3aed; border:none;
                 border-radius:.5rem; color:white; font-weight:bold; cursor:pointer;">
                 Validar
               </button>`
        : ""}
          <button id="btnAgain" style="
            padding:.5rem 1rem; background:#e5e7eb; border:none;
            border-radius:.5rem; color:#4f46e5; font-weight:bold; cursor:pointer;">
            Otro
          </button>
        </div>
      </div>
    `;

    // Mostrar modal con callback para reiniciar cámara al cerrar
    showOverlay(html, initCamera);

    const ov = ensureOverlay();
    ov.querySelector("#btnAgain").onclick = () => closeOverlay(initCamera);

    if (canValidate) {
      ov.querySelector("#btnValidate").onclick = async () => {
        showOverlay('<div class="text-white text-lg">🔄 Validando…</div>');
        let res;
        try {
          res = await fetchTicket(validarUrl, data.unique_code);
          console.log("validar →", res);
        } catch (err) {
          console.error("Error al validar:", err);
          closeOverlay();
          showOverlay(
            '<div class="text-white text-lg">⚠️ Error al validar. Intentá nuevamente.</div>',
            initCamera
          );
          return;
        }
        closeOverlay();
        const ok = res.status === "success";
        showResult(ok ? "success" : "error", res.message, res.data, false);
      };
    }
  }

  // 5) Ingreso manual
  document.getElementById("btnManualInput")
    .addEventListener("click", (e) => {
      e.preventDefault();
      showManualEntry(onScanSuccess, initCamera);
    });

  // 6) Arranca todo
  setTimeout(initCamera, 100);
});
