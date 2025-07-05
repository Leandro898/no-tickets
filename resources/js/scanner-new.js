import { Html5Qrcode } from "html5-qrcode";

document.addEventListener('DOMContentLoaded', () => {
    const startBtn = document.getElementById('startBtn');
    const stopBtn = document.getElementById('stopBtn');
    const readerDiv = document.getElementById('reader');
    const scanUrl = window.scannerEndpoint;
    let html5QrCode = null;

    // Overlay visual tipo modal sobre el reader
    function ensureOverlay() {
        let overlay = document.getElementById('scanOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'scanOverlay';
            overlay.className = 'absolute inset-0 flex items-center justify-center';
            overlay.style.display = 'none';
            overlay.style.zIndex = '999';
            readerDiv.appendChild(overlay);
        }
        return overlay;
    }

    startBtn.addEventListener('click', async e => {
        e.preventDefault();
        console.log("Click en iniciar cámara");

        // Limpiar el reader y ocultar overlay
        readerDiv.innerHTML = '';
        ensureOverlay().style.display = 'none';

        html5QrCode = new Html5Qrcode("reader");
        startBtn.disabled = true;
        stopBtn.disabled = false;
        resetBorder();

        try {
            await html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: 250 },
                decodedText => onScanSuccess(decodedText)
            );
        } catch (err) {
            alert("No se pudo iniciar el escáner. Verificá permisos.");
            startBtn.disabled = false;
            stopBtn.disabled = true;
        }
    });

    stopBtn.addEventListener('click', async e => {
        e.preventDefault();
        if (!html5QrCode) return;
        await stopAndDestroy();
    });

    async function stopAndDestroy() {
        if (html5QrCode) {
            try { await html5QrCode.stop(); } catch (e) { }
            try { await html5QrCode.clear(); } catch (e) { }
            html5QrCode = null;
        }
        stopBtn.disabled = true;
        startBtn.disabled = false;
        resetBorder();
        readerDiv.innerHTML = '';
        hideOverlay(); // <-- Esto oculta cualquier mensaje en pantalla
    }

    async function onScanSuccess(decodedText) {
        showOverlay('loading', 'Validando…');
        try {
            await stopAndDestroy();

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

            if (res.ok && json.status === 'success') {
                showOverlay('success', json.message || 'Ticket validado exitosamente');
                readerDiv.classList.remove('border-gray-300', 'border-red-500');
                readerDiv.classList.add('border-green-500');
            } else {
                let msg = json.message || 'Error de validación';
                if (res.status === 422 && msg === 'Error de validación')
                    msg = 'Este ticket ya fue utilizado o no es válido.';
                showOverlay('error', msg);
                readerDiv.classList.remove('border-gray-300', 'border-green-500');
                readerDiv.classList.add('border-red-500');
            }
        } catch (e) {
            showOverlay('error', "Error al validar el ticket");
            readerDiv.classList.remove('border-gray-300', 'border-green-500');
            readerDiv.classList.add('border-red-500');
        } finally {
            startBtn.disabled = false;
        }
    }

    // Muestra overlay tipo modal con branding e ícono
    function showOverlay(type = '', message = '') {
        let overlay = ensureOverlay();
        let icon = '';
        if (type === 'success') icon = '✅';
        else if (type === 'error') icon = '<span style="font-size:2.3rem;vertical-align:middle;">❌ &#9888;</span>';
        else if (type === 'loading') icon = '<span style="font-size:2.3rem;">🔄</span>';

        overlay.innerHTML = `
            <div style="
                background: rgba(124,58,237,0.92); 
                color:#fff; 
                font-size:1.4rem;
                font-weight:600;
                border-radius:1.2rem;
                box-shadow:0 8px 40px 0 rgba(80,29,165,0.25);
                min-width:80%; min-height:80%;
                display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1.2rem;
                border:3px solid #fff;
                text-align:center;
                padding:2rem 1.2rem;">
                <div style="font-size:3rem;line-height:1;">${icon}</div>
                <div style="font-size:1.6rem;font-weight:700;">${message}</div>
            </div>
        `;
        overlay.style.display = 'flex';
    }

    function hideOverlay() {
        let overlay = ensureOverlay();
        overlay.style.display = 'none';
    }
    function resetBorder() {
        readerDiv.classList.remove('border-green-500', 'border-red-500');
        readerDiv.classList.add('border-gray-300');
    }
});
