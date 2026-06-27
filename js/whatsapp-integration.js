/**
 * WhatsApp Integration Logic
 * Requires: html2pdf.bundle.min.js to be loaded in the HTML
 */

const WA_SERVER_URL = 'http://localhost:3001/wa';

// Add WhatsApp Modal to the DOM
function initWhatsAppModal() {
    if (document.getElementById('wa-modal-overlay')) return;

    const modalHTML = `
        <div id="wa-modal-overlay" class="modal-overlay" style="z-index: 100000; background: rgba(0,0,0,0.8);">
            <div class="modal-card" style="max-width: 400px; text-align: center; border-color: #25D366;">
                <div class="modal-header" style="border-bottom-color: rgba(37, 211, 102, 0.2);">
                    <h3 style="color: #25D366; display:flex; align-items:center; gap:8px;">
                        <span style="font-size:1.5rem;">📱</span> ربط واتساب
                    </h3>
                    <button class="modal-close" onclick="closeWAModal()">✕</button>
                </div>
                <div class="modal-body" style="color: #f1f5f9;">
                    <p id="wa-status-text" style="margin-bottom: 15px; font-weight: bold;">جاري التحقق من حالة الاتصال...</p>
                    <div id="wa-qr-container" style="background: white; padding: 15px; border-radius: 12px; display: inline-block; min-width: 250px; min-height: 250px; margin-bottom: 15px;">
                        <img id="wa-qr-image" src="" alt="QR Code" style="width: 100%; display: none;" />
                        <div id="wa-loading-spinner" style="color: #000; margin-top: 100px;">⏳ جاري التوليد...</div>
                    </div>
                    <p style="font-size: 0.85rem; color: #94a3b8;">امسح هذا الرمز باستخدام تطبيق واتساب في هاتفك لربط حسابك بالنظام المحاسبي.</p>
                </div>
                <div class="modal-footer" style="display:flex; justify-content:center; gap:10px; border-top-color: rgba(37, 211, 102, 0.2);">
                    <button class="btn btn-primary" id="wa-logout-btn" style="background: #ef4444; border-color: #ef4444; display: none;" onclick="logoutWhatsApp()">إلغاء الربط (تسجيل خروج)</button>
                    <button class="btn btn-ghost" onclick="checkWAStatus()">تحديث الحالة</button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

function openWAModal() {
    initWhatsAppModal();
    document.getElementById('wa-modal-overlay').classList.add('visible');
    checkWAStatus();
}

function closeWAModal() {
    const modal = document.getElementById('wa-modal-overlay');
    if (modal) modal.classList.remove('visible');
}

async function checkWAStatus() {
    const userId = getWAUserId();
    const statusText = document.getElementById('wa-status-text');
    const qrImg = document.getElementById('wa-qr-image');
    const spinner = document.getElementById('wa-loading-spinner');
    const logoutBtn = document.getElementById('wa-logout-btn');

    statusText.textContent = 'جاري التحقق...';
    spinner.style.display = 'block';
    qrImg.style.display = 'none';

    try {
        const res = await fetch(`${WA_SERVER_URL}/status?userId=${userId}`);
        const data = await res.json();

        if (data.status === 'CONNECTED' || data.status === 'AUTHENTICATED') {
            statusText.textContent = '✅ أنت متصل بحساب واتساب بنجاح.';
            statusText.style.color = '#34d399';
            spinner.style.display = 'none';
            qrImg.style.display = 'none';
            logoutBtn.style.display = 'inline-block';
        } else {
            statusText.textContent = 'يرجى مسح الرمز أدناه للربط:';
            statusText.style.color = '#fbbf24';
            logoutBtn.style.display = 'none';
            fetchWAQRCode();
        }
    } catch (err) {
        statusText.textContent = '❌ الخادم غير متصل. يرجى التأكد من تشغيل خدمة الواتساب.';
        statusText.style.color = '#ef4444';
        spinner.style.display = 'none';
    }
}

async function fetchWAQRCode() {
    const userId = getWAUserId();
    const qrImg = document.getElementById('wa-qr-image');
    const spinner = document.getElementById('wa-loading-spinner');

    try {
        const res = await fetch(`${WA_SERVER_URL}/qr?userId=${userId}`);
        const data = await res.json();

        if (data.qrCode) {
            qrImg.src = data.qrCode;
            qrImg.style.display = 'block';
            spinner.style.display = 'none';
            
            // Poll for status every 5 seconds until connected
            setTimeout(() => {
                if(document.getElementById('wa-modal-overlay') && document.getElementById('wa-modal-overlay').classList.contains('visible')) {
                    checkWAStatus();
                }
            }, 5000);
        } else if (data.status === 'INITIALIZING') {
            // Still loading, try again in 3s
            setTimeout(fetchWAQRCode, 3000);
        }
    } catch (err) {
        console.error('QR fetch error', err);
    }
}

async function logoutWhatsApp() {
    const userId = getWAUserId();
    if (!confirm('هل أنت متأكد من رغبتك في إلغاء ربط رقمك؟')) return;
    try {
        await fetch(`${WA_SERVER_URL}/logout`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ userId })
        });
        checkWAStatus();
    } catch (err) {
        alert('حدث خطأ أثناء تسجيل الخروج');
    }
}

function getWAUserId() {
    // In a real app, this should be the logged-in user's ID.
    let currentUser = localStorage.getItem('erp_currentUser');
    if (!currentUser) {
        currentUser = 'default_user_' + Math.random().toString(36).substr(2, 9);
    }
    return 'wa_user_' + currentUser;
}

/**
 * Convert HTML element to PDF and send via WhatsApp
 * @param {HTMLElement} elementToPrint - The DOM element to convert to PDF
 * @param {String} phoneNumber - Customer's WhatsApp number
 * @param {String} message - Text message to send along with PDF
 */
async function sendPDFViaWhatsApp(elementToPrint, phoneNumber, message) {
    if (!phoneNumber) {
        alert('يرجى إدخال رقم الهاتف (يجب أن يحتوي على المفتاح الدولي).');
        return;
    }
    
    // Check status first
    try {
        const statusRes = await fetch(`${WA_SERVER_URL}/status?userId=${getWAUserId()}`);
        const statusData = await statusRes.json();
        if (statusData.status !== 'CONNECTED' && statusData.status !== 'AUTHENTICATED') {
            alert('يجب ربط الواتساب أولاً قبل إرسال الفاتورة.');
            openWAModal();
            return;
        }
    } catch (err) {
        alert('خادم الواتساب غير متصل حالياً.');
        return;
    }

    // Show loading
    showToast('جاري تجهيز ملف PDF للإرسال...', 'warning');

    try {
        // Generate PDF using html2pdf
        const opt = {
            margin: 10,
            filename: 'invoice.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        const pdfBlob = await html2pdf().set(opt).from(elementToPrint).output('blob');
        const file = new File([pdfBlob], "invoice.pdf", { type: "application/pdf" });

        const formData = new FormData();
        formData.append('userId', getWAUserId());
        formData.append('phone', phoneNumber);
        formData.append('message', message);
        formData.append('pdf', file);

        showToast('جاري إرسال الفاتورة عبر الواتساب...', 'warning');

        const sendRes = await fetch(`${WA_SERVER_URL}/send`, {
            method: 'POST',
            body: formData
        });

        const sendData = await sendRes.json();
        if (sendData.success) {
            showToast('✅ تم إرسال الفاتورة بنجاح!', 'success', 4000);
        } else {
            alert('فشل الإرسال: ' + (sendData.error || 'Unknown error'));
        }
    } catch (err) {
        console.error('Error sending PDF', err);
        alert('حدث خطأ غير متوقع أثناء الإرسال.');
    }
}

function showWAConnectPrompt() {
    return new Promise((resolve) => {
        const overlayId = 'wa-custom-prompt-overlay';
        if (document.getElementById(overlayId)) return resolve(false);

        const html = `
        <div id="${overlayId}" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:999999; display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.3s ease; backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);">
            <div style="background:linear-gradient(145deg, #1e293b, #0f172a); border:1px solid rgba(37,211,102,0.3); border-radius:24px; padding:30px; width:90%; max-width:400px; text-align:center; transform:translateY(30px) scale(0.95); transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
                <div style="width:70px; height:70px; background:rgba(37,211,102,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px auto; color:#25d366;">
                    <svg viewBox="0 0 24 24" width="40" height="40" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <h3 style="color:#f8fafc; font-size:1.4rem; font-family:'Tajawal',sans-serif; margin-bottom:10px; margin-top:0;">الواتساب غير مربوط</h3>
                <p style="color:#94a3b8; font-size:1rem; margin-bottom:25px; line-height:1.6; font-family:'Tajawal',sans-serif;">رقم الواتساب الخاص بك غير مربوط بالنظام حالياً. هل تريد ربط رقمك الآن لتتمكن من إرسال الفواتير؟</p>
                
                <div style="display:flex; gap:15px; justify-content:center;">
                    <button id="wa-prompt-yes" style="background:#25d366; color:#fff; border:none; padding:12px 25px; border-radius:10px; font-weight:bold; font-size:1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.2s; box-shadow:0 4px 15px rgba(37,211,102,0.3);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">نعم، ربط الآن</button>
                    <button id="wa-prompt-no" style="background:rgba(255,255,255,0.05); color:#cbd5e1; border:1px solid rgba(255,255,255,0.1); padding:12px 25px; border-radius:10px; font-weight:bold; font-size:1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">لا، لاحقاً</button>
                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', html);
        
        const overlay = document.getElementById(overlayId);
        const modal = overlay.children[0];
        
        // Animate in
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
            modal.style.transform = 'translateY(0) scale(1)';
        });

        const closePrompt = (result) => {
            overlay.style.opacity = '0';
            modal.style.transform = 'translateY(30px) scale(0.95)';
            setTimeout(() => {
                overlay.remove();
                resolve(result);
            }, 300);
        };

        document.getElementById('wa-prompt-yes').onclick = () => closePrompt(true);
        document.getElementById('wa-prompt-no').onclick = () => closePrompt(false);
    });
}
