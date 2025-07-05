import { BrowserMultiFormatReader } from '@zxing/browser';

const video = document.getElementById('camera');
const resultEl = document.getElementById('scanResult');
const startBtn = document.getElementById('startBtn');
const stopBtn = document.getElementById('stopBtn');

// Esta variable la inyectas en tu Blade así:
// <script>window.scannerUrl = "{{ route('admin.scanner.scan') }}";</script>
const scanUrl = window.scannerUrl;

let codeReader = null;

async function startScanner() {
    console.log('[QR] Iniciando scanner…');
    codeReader = new BrowserMultiFormatReader();
    try {
        await codeReader.decodeFromVideoDevice(
      /* deviceId */ null,
      /* video element */ video,
      /* onSuccess */ result => {
                console.log('[QR] Detectado:', result.getText());
                stopScanner();
                validateCode(result.getText());
            }
        );
        console.log('[QR] Scanner activo');
        startBtn.disabled = true;
        stopBtn.disabled = false;
    } catch (e) {
        console.error('[QR] No pude arrancar el scanner:', e);
        alert('No se pudo iniciar el escáner. Verificá permisos.');
    }
}

function stopScanner() {
    console.log('[QR] Parando scanner…');
    if (codeReader) {
        codeReader.reset();
    }
    startBtn.disabled = false;
    stopBtn.disabled = true;
}

async function validateCode(code) {
    console.log('[QR] Validando código en back…', code);
    resultEl.textContent = 'Validando…';
    resultEl.className = 'text-center mb-4 font-semibold';

    const tokenMeta = document.head.querySelector('meta[name="csrf-token"]');
    const csrfToken = tokenMeta ? tokenMeta.content : '';

    try {
        const res = await fetch(scanUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ code }),
        });
        console.log('[QR] fetch terminó, status:', res.status);

        const json = await res.json();
        console.log('[QR] respuesta JSON:', json);

        if (res.ok && json.status === 'success') {
            resultEl.textContent = '✅ ' + json.message;
            resultEl.className = 'text-center mb-4 font-semibold text-green-600';
        } else {
            resultEl.textContent = '❌ ' + json.message;
            resultEl.className = 'text-center mb-4 font-semibold text-red-600';
        }
    } catch (error) {
        console.error('[QR] Error en validación fetch:', error);
        resultEl.textContent = '⚠️ Error en validación';
        resultEl.className = 'text-center mb-4 font-semibold text-yellow-600';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    startBtn.addEventListener('click', startScanner);
    stopBtn.addEventListener('click', stopScanner);
});
