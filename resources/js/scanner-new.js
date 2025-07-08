import { Html5Qrcode } from "html5-qrcode";

document.addEventListener('DOMContentLoaded', () => {
    const readerDiv = document.getElementById('reader');
    const scanUrl = window.scannerEndpoint;
    let html5QrCode = null;

    // Overlay/modal
    function ensureOverlay() {
        let overlay = document.getElementById('scanOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'scanOverlay';
            overlay.className = 'fixed inset-0 flex items-center justify-center';
            overlay.style.display = 'none';
            overlay.style.zIndex = '9999';
            overlay.style.background = 'rgba(24,21,39,0.94)'; // fondo oscuro tipo modal
            document.body.appendChild(overlay);
        }
        return overlay;
    }

    // Estado del ticket mostrado en overlay
    let ultimoTicket = null;
    let ultimoEstado = null; // 'success', 'error', etc.

    async function iniciarCamara() {
        readerDiv.innerHTML = '';
        ensureOverlay().style.display = 'none';

        html5QrCode = new Html5Qrcode("reader");
        resetBorder();

        try {
            await html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: 250 },
                decodedText => onScanSuccess(decodedText)
            );
        } catch (err) {
            alert("No se pudo iniciar el escáner. Verificá permisos.");
        }
    }

    setTimeout(() => {
        iniciarCamara();
    }, 100);

    async function onScanSuccess(decodedText) {
        showOverlay('loading', 'Validando…');
        try {
            if (html5QrCode) {
                try { await html5QrCode.stop(); } catch (e) { }
                try { await html5QrCode.clear(); } catch (e) { }
                html5QrCode = null;
            }

            const token = document.head.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch(scanUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ code: decodedText }),
            });

            let json = {};
            try { json = await res.json(); }
            catch (e) { json = { message: 'Respuesta inválida', status: 'error' }; }

            // Guarda el estado y datos del ticket
            ultimoTicket = json.data || null;
            ultimoEstado = json.status || 'error';

            if (res.ok && json.status === 'success') {
                showTicketOverlay('success', json.message || 'Ticket validado exitosamente', ultimoTicket, false);
                readerDiv.classList.remove('border-gray-300', 'border-red-500');
                readerDiv.classList.add('border-green-500');
            } else {
                let msg = json.message || 'Error de validación';
                if (res.status === 422 && msg === 'Error de validación')
                    msg = 'Este ticket ya fue utilizado o no es válido.';
                showTicketOverlay('error', msg, ultimoTicket, true);
                readerDiv.classList.remove('border-gray-300', 'border-green-500');
                readerDiv.classList.add('border-red-500');
            }
        } catch (e) {
            showOverlay('error', "Error al validar el ticket");
            readerDiv.classList.remove('border-gray-300', 'border-green-500');
            readerDiv.classList.add('border-red-500');
        }
    }

    // Overlay con info del ticket y acciones
    function showTicketOverlay(type = '', message = '', data = {}, mostrarBotonValidar = false) {
        let overlay = ensureOverlay();

        let color = type === 'success' ? '#16a34a' : '#dc2626'; // verde o rojo
        let icon = type === 'success'
            ? '<span style="font-size:2.3rem;vertical-align:middle;">&#x2705;</span>'
            : '<span style="font-size:2.3rem;vertical-align:middle;">&#x26A0;&#xFE0F;</span>';
        let titulo = type === 'success' ? 'ENTRADA VALIDADA' : 'ENTRADA VALIDADA';

        overlay.innerHTML = `
            <div style="
                background: #191225;
                color: #fff;
                font-size: 1.2rem;
                border-radius: 1.2rem;
                min-width:320px; max-width:90vw;
                padding:2.2rem 1.6rem 1.8rem 1.6rem;
                border-top: 8px solid ${color};
                box-shadow:0 8px 40px 0 rgba(80,29,165,0.25);
                text-align:left;
                position:relative;
            ">
                <!-- Botón cerrar (X) -->
                <button id="btnCerrarOverlay" style="
                    position:absolute;top:12px;right:18px;
                    background:transparent;border:none;color:#fff;
                    font-size:2rem;line-height:1;cursor:pointer;
                    z-index:10;
                ">&times;</button>
                
                <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.2rem;">
                    <div>${icon}</div>
                    <div style="font-size:1.4rem;font-weight:700;letter-spacing:-1px;">${titulo}</div>
                </div>
                <div style="font-size:1.1rem; margin-bottom:.9rem; color:${color}; font-weight:700;">${message}</div>
                <div style="margin-bottom:1rem;">
                    <b>Tipo:</b> ${data?.tipo || '-'}<br>
                    <b>Precio:</b> $${data?.precio || '-'}<br>
                    <b>Evento:</b> ${data?.evento || '-'}<br>
                    <b>Validez:</b> ${data?.validez || '-'}<br>
                    <b>Nombre:</b> ${data?.nombre || '-'}<br>
                    <b>Email:</b> ${data?.email || '-'}<br>
                    <b>DNI:</b> ${data?.dni || '-'}
                </div>
                <div style="display:flex;gap:.7rem;flex-wrap:wrap;">
                    ${mostrarBotonValidar && type !== 'success'
                        ? `<button id="btnValidarTicket" class="px-5 py-2 bg-violet-700 rounded-xl text-white font-bold hover:bg-violet-900" style="font-size:1rem;">Validar</button>`
                        : ''
                    }
                    <button id="btnScanOtro" class="px-5 py-2 bg-gray-200 rounded-xl text-violet-700 font-bold hover:bg-gray-300" style="font-size:1rem;">Escanear otro QR</button>
                </div>
            </div>
        `;
        overlay.style.display = 'flex';

        // Acción cerrar
        document.getElementById('btnCerrarOverlay').onclick = function () {
            overlay.style.display = 'none';
            iniciarCamara();
        };

        // Acción escanear otro
        document.getElementById('btnScanOtro').onclick = function () {
            overlay.style.display = 'none';
            iniciarCamara();
        };

        // Acción validar (sólo si corresponde)
        if (mostrarBotonValidar && type !== 'success') {
            document.getElementById('btnValidarTicket').onclick = function () {
                validarTicket();
            };
        }
    }

    // Si hacés “Validar” desde overlay
    async function validarTicket() {
        if (!ultimoTicket) return;
        showOverlay('loading', 'Validando…');
        // Se puede volver a llamar a onScanSuccess con el mismo código
        await onScanSuccess(ultimoTicket.unique_code);
    }

    function showOverlay(type = '', message = '', title = '') {
        let overlay = ensureOverlay();
        let icon = '';
        if (type === 'success') icon = '✅';
        else if (type === 'error') icon = '<span style="font-size:2.3rem;vertical-align:middle;">❌ &#9888;</span>';
        else if (type === 'loading') icon = '<span style="font-size:2.3rem;">🔄</span>';
    
        overlay.innerHTML = `
            <div style="
                background: rgba(0,0,0,0.95);
                color:#fff;
                font-size:1rem;
                font-weight:600;
                border-radius:1.2rem;
                max-width: 320px;
                width: 90vw;
                box-shadow:0 8px 40px 0 rgba(80,29,165,0.25);
                display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1.2rem;
                border:3px solid #fff;
                text-align:left;
                padding:1rem;
                position:relative;
            ">
                <!-- Botón cerrar (X) -->
                <button id="btnCerrarOverlay" style="
                    position:absolute;top:12px;right:18px;
                    background:transparent;border:none;color:#fff;
                    font-size:2rem;line-height:1;cursor:pointer;
                    z-index:10;
                ">&times;</button>
                <div style="color:#FFA726;font-weight:bold;font-size:1.1rem;margin-bottom:.6rem;">${icon} ${title ? title : ''}</div>
                <div style="font-size:1.1rem;">${message}</div>
            </div>
        `;
        overlay.style.display = 'flex';

        // Acción cerrar
        document.getElementById('btnCerrarOverlay').onclick = function () {
            overlay.style.display = 'none';
            iniciarCamara();
        };
    }

    function resetBorder() {
        readerDiv.classList.remove('border-green-500', 'border-red-500', 'border-gray-300');
        readerDiv.classList.add('border-violet-500');
    }
});
