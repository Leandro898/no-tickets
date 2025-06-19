console.log('scanner.js cargado ✅');

const video = document.getElementById('camera');

if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    alert("Tu navegador no soporta acceso a la cámara.");
} else {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
        .then(stream => {
            video.srcObject = stream;
            video.play();
            console.log("Cámara iniciada correctamente 🎥");
        })
        .catch(error => {
            console.error("No se pudo acceder a la cámara:", error);
            alert("No se pudo acceder a la cámara. Verificá permisos.");
        });
}
