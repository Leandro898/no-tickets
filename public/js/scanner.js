console.log('scanner.js cargado ✅');

const video = document.getElementById('camera');
let stream = null;

// Arranca la cámara y muestra el stream en el <video>
async function startScanner() {
    if (!navigator.mediaDevices?.getUserMedia) {
        return alert("Tu navegador no soporta acceso a la cámara.");
    }
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "environment" }
        });
        video.srcObject = stream;
        await video.play();
        console.log("Cámara iniciada correctamente 🎥");
    } catch (err) {
        console.error("No se pudo acceder a la cámara:", err);
        alert("No se pudo acceder a la cámara. Verificá permisos.");
    }
}

// Detiene la cámara
function stopScanner() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        video.srcObject = null;
        console.log("Cámara detenida");
    }
}

// Al cargar el DOM vinculamos los botones
document.addEventListener('DOMContentLoaded', () => {
    const startBtn = document.getElementById('startBtn');
    const stopBtn = document.getElementById('stopBtn');

    startBtn.addEventListener('click', () => {
        startScanner();
        startBtn.disabled = true;
        stopBtn.disabled = false;
    });

    stopBtn.addEventListener('click', () => {
        stopScanner();
        startBtn.disabled = false;
        stopBtn.disabled = true;
    });
});
