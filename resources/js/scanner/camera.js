import { Html5Qrcode } from "html5-qrcode";

export async function startScanner(onSuccess, onError) {
    const html5QrCode = new Html5Qrcode("reader");
    try {
        await html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            decodedText => onSuccess(decodedText),
        );
        return html5QrCode;
    } catch (e) {
        onError(e);
        return null;
    }
}

export async function stopScanner(html5QrCode) {
    if (!html5QrCode) return;

    // 1) detén el scanner
    if (typeof html5QrCode.stop === "function") {
        try {
            await html5QrCode.stop();
        } catch (_) { /* ignoramos fallo */ }
    }

    // 2) limpia el DOM interno sólo si existe clear()
    if (typeof html5QrCode.clear === "function") {
        try {
            await html5QrCode.clear();
        } catch (_) { /* ignoramos fallo */ }
    }
}
