let html5QrcodeScanner = null;

function startCameraScanner(targetInputId) {
    document.getElementById('barcode-scanner-modal').style.display = 'flex';
    document.getElementById('scanner-status').innerText = 'جاري تشغيل الكاميرا...';
    document.getElementById('scanner-status').style.color = '#fbbf24';

    loadScannerLibrary(() => {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(() => {});
        }
        
        html5QrcodeScanner = new Html5Qrcode("scanner-viewport");
        const config = { fps: 15, qrbox: { width: 280, height: 180 } };
        
        html5QrcodeScanner.start(
            { facingMode: "environment" },
            config,
            (decodedText, decodedResult) => {
                const items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
                const matchedItem = items.find(item => {
                    if (item.barcode === decodedText) return true;
                    if (item.units && Array.isArray(item.units)) {
                        return item.units.some(u => u.barcode === decodedText);
                    }
                    return false;
                });
                
                if (matchedItem) {
                    playScanBeep();
                    if (typeof selectQuickItem === 'function') {
                        selectQuickItem(matchedItem.id);
                    } else {
                        const input = document.getElementById(targetInputId);
                        if (input) {
                            input.value = decodedText;
                            input.dispatchEvent(new Event('input'));
                        }
                    }
                    stopCameraScanner();
                } else {
                    playScanBeep();
                    const input = document.getElementById(targetInputId);
                    if (input) {
                        input.value = decodedText;
                        input.dispatchEvent(new Event('input'));
                    }
                    if (typeof window.focusRequiredPurchaseField === 'function') {
                        window.focusRequiredPurchaseField('#' + targetInputId, `⚠️ لم يتم العثور على صنف بالباركود: ${decodedText}`);
                    } else if (typeof showToast === 'function') {
                        showToast(`⚠️ لم يتم العثور على صنف بالباركود: ${decodedText}`, 'warning');
                    }
                    stopCameraScanner();
                }
            },
            (errorMessage) => {}
        ).then(() => {
            document.getElementById('scanner-status').innerText = '🟢 الكاميرا متصلة وتعمل حالياً';
            document.getElementById('scanner-status').style.color = '#34d399';
        }).catch(err => {
            document.getElementById('scanner-status').innerText = '❌ تعذر الوصول للكاميرا: يرجى إعطاء الصلاحية';
            document.getElementById('scanner-status').style.color = '#f43f5e';
            console.error("Camera access failed", err);
        });
    });
}

function stopCameraScanner() {
    document.getElementById('barcode-scanner-modal').style.display = 'none';
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            return html5QrcodeScanner.clear();
        }).catch(err => {
            console.error("Failed to stop scanner", err);
        }).finally(() => {
            html5QrcodeScanner = null;
        });
    }
}

function loadScannerLibrary(callback) {
    if (window.Html5Qrcode) {
        callback();
        return;
    }
    const script = document.createElement('script');
    script.src = 'js/vendor/html5-qrcode.min.js';
    script.onload = callback;
    document.head.appendChild(script);
}

function playScanBeep() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(1000, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.15);
    } catch(e) {
        console.error("Failed to play audio", e);
    }
}
