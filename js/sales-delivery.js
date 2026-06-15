// ==========================================
// PROJECT: SAJANA INTERNATIONAL PROTOCOL
// MODULE: ADAPTIVE DELIVERY & SECURITY LEVELS
// ==========================================

let currentLoadedInvoice = null;
let currentLevel = 1;
let isVerified = false;
let currentAttachment = null;
let verificationMethod = null;
let html5QrCode = null;

function showToast(message, type = 'success', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : '⚠️';
    toast.innerHTML = `<span>${icon}</span><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 400);
    }, duration);
}

// ── SECURITY LEVELS LOGIC ──
function simulateUserRole(levelStr) {
    const level = parseInt(levelStr);
    currentLevel = level;
    renderAuthTabs();
    switchLevel(level);
}

function renderAuthTabs() {
    const container = document.getElementById('auth-tabs-container');
    container.innerHTML = '';
    container.innerHTML += `<button class="level-tab active" data-level="1" onclick="switchLevel(1)" style="padding: 10px 20px; border-radius: 8px; border: 1px solid rgba(56,189,248,0.3); background: rgba(56,189,248,0.1); color: #38bdf8; font-weight: bold; cursor: pointer; transition: all 0.3s;">المستوى 1</button>`;
    if (currentLevel >= 2) {
        container.innerHTML += `<button class="level-tab" data-level="2" onclick="switchLevel(2)" style="padding: 10px 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); color: #94a3b8; font-weight: bold; cursor: pointer; transition: all 0.3s;">المستوى 2</button>`;
    }
    if (currentLevel >= 3) {
        container.innerHTML += `<button class="level-tab" data-level="3" onclick="switchLevel(3)" style="padding: 10px 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); color: #94a3b8; font-weight: bold; cursor: pointer; transition: all 0.3s;">المستوى 3</button>`;
    }
    if (currentLevel >= 4) {
        container.innerHTML += `<button class="level-tab" data-level="4" onclick="switchLevel(4)" style="padding: 10px 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); color: #94a3b8; font-weight: bold; cursor: pointer; transition: all 0.3s;">المستوى 4</button>`;
    }
}

function switchLevel(level) {
    const tabs = document.querySelectorAll('.level-tab');
    tabs.forEach(tab => {
        tab.style.background = 'rgba(0,0,0,0.3)';
        tab.style.color = '#94a3b8';
        tab.style.borderColor = 'rgba(255,255,255,0.1)';
        if (parseInt(tab.dataset.level) === level) {
            tab.style.background = 'rgba(56,189,248,0.1)';
            tab.style.color = '#38bdf8';
            tab.style.borderColor = 'rgba(56,189,248,0.3)';
        }
    });

    const omnibox = document.getElementById('advanced-search-box');
    if (omnibox) omnibox.style.display = level > 1 ? 'block' : 'none';
}

// Initialize on load
document.addEventListener('DBReady', () => {
    simulateUserRole(document.getElementById('demo-role-select').value);
});

// ── CAMERA & QR LOGIC ──
function startCamera() {
    document.getElementById('camera-placeholder').style.display = 'none';
    document.getElementById('qr-reader').style.display = 'block';

    html5QrCode = new Html5Qrcode("qr-reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        (decodedText) => {
            stopCamera();
            setVerified('qr');
            loadInvoiceByNo(decodedText);
            showToast('تم قراءة الباركود بنجاح!', 'success');
        },
        () => {}
    ).catch((err) => {
        showToast('فشل في تشغيل الكاميرا: ' + err, 'error');
        document.getElementById('camera-placeholder').style.display = 'block';
        document.getElementById('qr-reader').style.display = 'none';
    });
}

function stopCamera() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            document.getElementById('qr-reader').style.display = 'none';
            document.getElementById('camera-placeholder').style.display = 'block';
        }).catch(err => console.error(err));
    }
}

// ── IMAGE ATTACHMENT LOGIC ──
// FIX: Removed prompt() - now shows an inline input field instead
function handleImageUpload(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(event) {
        currentAttachment = event.target.result;
        setVerified('photo');

        const previewDiv = document.getElementById('attachment-preview');
        const thumbImg = document.getElementById('attachment-thumb');
        if (previewDiv && thumbImg) {
            thumbImg.src = currentAttachment;
            previewDiv.style.display = 'block';
        }

        // FIX: if no invoice loaded, show inline search box instead of prompt()
        if (!currentLoadedInvoice) {
            const omnibox = document.getElementById('advanced-search-box');
            if (omnibox) {
                omnibox.style.display = 'block';
                const inp = document.getElementById('smart-omnibox');
                if (inp) { inp.focus(); }
            }
            showToast('تم إرفاق الصورة — ابحث الآن عن رقم الإذن لاستدعائه.', 'warning');
        }
    };
    reader.readAsDataURL(file);
}

function setVerified(method) {
    isVerified = true;
    verificationMethod = method;
    const vStatus = document.getElementById('verification-status');
    const vBadge = document.getElementById('verification-badge');
    const btnDeliver = document.getElementById('btn-deliver');

    const successIcon = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left:4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`;

    const labels = {
        'qr':       { status: 'تم التحقق (كاميرا)', badge: 'تم التحقق (QR)' },
        'photo':    { status: 'تم التحقق (مرفق)',   badge: 'تم التحقق (مرفق)' },
        // FIX: handle 'override' method for L4 admin bypass
        'override': { status: 'تم التحقق (تجاوز L4)', badge: 'تجاوز إداري (L4)' },
    };
    const lbl = labels[method] || { status: 'تم التحقق', badge: 'تم التحقق' };

    if (vStatus) {
        vStatus.innerHTML = successIcon + ' ' + lbl.status;
        vStatus.style.color = '#10b981';
        vStatus.style.borderColor = 'rgba(16, 185, 129, 0.4)';
        vStatus.style.background = 'rgba(16, 185, 129, 0.1)';
    }
    if (vBadge) {
        vBadge.innerHTML = successIcon + ' ' + lbl.badge;
        vBadge.style.color = '#10b981';
        vBadge.style.borderColor = 'rgba(16, 185, 129, 0.4)';
        vBadge.style.background = 'rgba(16, 185, 129, 0.1)';
    }

    if (currentLoadedInvoice) {
        btnDeliver.disabled = false;
        btnDeliver.style.opacity = '1';
    }
}

// ── SMART OMNIBOX SEARCH ──
function filterSmartSearch(query) {
    if (currentLevel === 1) return;
    const list = document.getElementById('smart-search-dropdown');
    if (!list) return;
    if (!query) {
        list.style.display = 'none';
        return;
    }

    const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    const q = query.toLowerCase().trim();
    const results = allSales.filter(inv => {
        const invNo = String(inv.invoiceNo || '').toLowerCase();
        const cName = String(inv.customerName || '').toLowerCase();
        const cId   = String(inv.customerId  || '').toLowerCase();
        const phone = String(inv.phone || '').toLowerCase();
        const date  = String(inv.date  || '').toLowerCase();
        return invNo.includes(q) || cName.includes(q) || cId.includes(q) || phone.includes(q) || date.includes(q);
    }).slice(0, 10);

    if (results.length > 0) {
        list.innerHTML = results.map(r => `
            <div class="custom-dropdown-item" onclick="selectSmartResult('${r.invoiceNo}')" style="padding: 10px; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <strong>${r.invoiceNo}</strong> - <span style="color:#34d399;">${r.customerName}</span><br>
                <small style="color:var(--text-muted)">${r.date}</small>
            </div>
        `).join('');
        list.style.display = 'block';
    } else {
        list.innerHTML = `<div style="padding:10px; color:var(--text-muted); font-size:0.85rem; text-align:center;">لا توجد نتائج</div>`;
        list.style.display = 'block';
    }
}

function selectSmartResult(invNo) {
    document.getElementById('smart-omnibox').value = invNo;
    document.getElementById('smart-search-dropdown').style.display = 'none';
    loadInvoiceByNo(invNo);
}

function handleSmartSearch(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        loadInvoiceByNo(e.target.value.trim());
        document.getElementById('smart-search-dropdown').style.display = 'none';
    }
}

// ── INVOICE LOADING ──
function loadInvoiceByNo(queryVal) {
    const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    const q = String(queryVal).trim().toLowerCase();

    // FIX: null-safe hash comparison — avoid matching String(null) === "null"
    let inv = allSales.find(i =>
        String(i.invoiceNo || '').toLowerCase() === q ||
        (i.hash && String(i.hash).toLowerCase() === q)
    );

    if (!inv) {
        inv = allSales.find(i =>
            (i.customerId && String(i.customerId).toLowerCase() === q) ||
            (i.phone && String(i.phone).toLowerCase() === q)
        );
    }
    if (!inv) {
        inv = allSales.find(i => String(i.customerName || '').toLowerCase().includes(q));
    }

    if (!inv) {
        showToast(`لا توجد فاتورة بهذا الرقم أو الاسم: ${queryVal}`, 'error');
        resetUI();
        return;
    }

    currentLoadedInvoice = inv;
    renderInvoiceDetails();
    renderGrid();

    if (isVerified) {
        const btnDeliver = document.getElementById('btn-deliver');
        if (inv.deliveryStatus !== 'delivered' && inv.deliveryStatus !== 'Admin_Closed') {
            btnDeliver.disabled = false;
            btnDeliver.style.opacity = '1';
        }
    } else if (currentLevel >= 4) {
        // L4 admin override — auto-verify without camera
        setVerified('override');
    }
}

function resetUI() {
    currentLoadedInvoice = null;
    isVerified = false;
    currentAttachment = null;
    verificationMethod = null;

    // Hide review notes panel
    const reviewPanel = document.getElementById('review-notes-panel');
    if (reviewPanel) reviewPanel.style.display = 'none';
    const warehouseNotes = document.getElementById('warehouse-notes');
    if (warehouseNotes) warehouseNotes.value = '';
    // FIX: personal notes also cleared on full reset
    const personalNotes = document.getElementById('personal-notes');
    if (personalNotes) personalNotes.value = '';

    document.getElementById('lbl-inv-no').textContent = '---';
    document.getElementById('lbl-customer').textContent = '---';
    document.getElementById('lbl-date').textContent = '---';
    document.getElementById('lbl-status').textContent = '---';
    document.getElementById('lbl-admin-status').textContent = 'بانتظار الاستدعاء';
    document.getElementById('lbl-hash').textContent = '---';

    document.getElementById('inv-info').style.opacity = '0.4';
    document.getElementById('delivery-tbody').innerHTML = '<tr><td colspan="7" class="empty-state">يرجى التحقق لاستدعاء الإذن.</td></tr>';

    const btnDeliver = document.getElementById('btn-deliver');
    btnDeliver.disabled = true;
    btnDeliver.style.opacity = '0.5';
    document.getElementById('btn-partial').disabled = true;

    // FIX: Reset badge/status back to warning yellow
    const vStatus = document.getElementById('verification-status');
    if (vStatus) {
        vStatus.innerHTML = '⚠️ بانتظار التحقق المرئي (كاميرا أو مرفق)';
        vStatus.style.color = '#fbbf24';
        vStatus.style.borderColor = 'rgba(245,158,11,0.3)';
        vStatus.style.background = 'rgba(245,158,11,0.1)';
    }

    const vBadge = document.getElementById('verification-badge');
    if (vBadge) {
        vBadge.innerHTML = 'غير متحقق';
        vBadge.style.color = '#94a3b8';
        vBadge.style.borderColor = 'rgba(148,163,184,0.3)';
        vBadge.style.background = 'rgba(148,163,184,0.1)';
    }

    const preview = document.getElementById('attachment-preview');
    if (preview) preview.style.display = 'none';
}

function renderInvoiceDetails() {
    if (!currentLoadedInvoice) return;
    const inv = currentLoadedInvoice;

    document.getElementById('inv-info').style.opacity = '1';
    document.getElementById('lbl-inv-no').textContent = inv.invoiceNo || '---';
    document.getElementById('lbl-customer').textContent = inv.customerName || '---';
    document.getElementById('lbl-date').textContent = inv.date || '---';

    const statusEl = document.getElementById('lbl-status');
    const adminEl  = document.getElementById('lbl-admin-status');

    const reviewPanel = document.getElementById('review-notes-panel');

    if (inv.deliveryStatus === 'Admin_Closed' || inv.status === 'Admin_Closed') {
        statusEl.textContent = 'مغلق مخزنياً';
        statusEl.className = 'status-badge delivered';
        adminEl.textContent = '✅ مغلق إدارياً (L4)';
        adminEl.style.color = '#34d399';
        // FIX: hide review notes panel for closed invoices
        if (reviewPanel) reviewPanel.style.display = 'none';
        disableDelivery('تم إغلاق هذا الإذن إدارياً بالكامل.');
    } else if (inv.deliveryStatus === 'Delivered_Pending_Admin' || inv.deliveryStatus === 'delivered') {
        statusEl.textContent = 'خرجت البضاعة (Stage 1)';
        statusEl.className = 'status-badge delivered';

        let adminColor = '#f59e0b';
        let adminText  = '⏳ بانتظار المراجعة (L4)';
        if (inv.adminDeadline && Date.now() > inv.adminDeadline) {
            adminColor = '#ef4444';
            adminText  = '🚨 متأخر إدارياً (> 72 ساعة)!';
        }
        adminEl.textContent = adminText;
        adminEl.style.color  = adminColor;
        // FIX: hide review notes panel for already-delivered invoices
        if (reviewPanel) reviewPanel.style.display = 'none';
        disableDelivery('البضاعة خرجت من المخزن مسبقاً، بانتظار اعتماد الإدارة.');
    } else {
        // Open invoice — show review notes panel
        statusEl.textContent = 'قيد التسليم (مفتوح)';
        statusEl.className   = 'status-badge';
        adminEl.textContent  = '---';
        adminEl.style.color  = 'var(--text-muted)';

        document.getElementById('btn-partial').disabled = false;
        showToast('تم استدعاء بيانات الإذن بنجاح.', 'success');

        // Show review panel only for open invoices and reset fields
        if (reviewPanel) reviewPanel.style.display = 'block';
        const wNotes = document.getElementById('warehouse-notes');
        if (wNotes) { wNotes.value = ''; wNotes.style.borderColor = 'rgba(251,191,36,0.35)'; wNotes.style.boxShadow = 'none'; }
        // FIX: do NOT reset personal-notes on every invoice load so user's session notes persist
        const hint = document.getElementById('warehouse-notes-hint');
        if (hint) hint.style.display = 'none';
    }

    document.getElementById('lbl-hash').textContent = inv.hash || 'NO-HASH-GENERATED';
}

function disableDelivery(msg) {
    const btnDeliver = document.getElementById('btn-deliver');
    btnDeliver.disabled = true;
    btnDeliver.style.opacity = '0.5';
    document.getElementById('btn-partial').disabled = true;
    showToast(msg, 'error');
}

function onReviewNotesChange() {
    const wNotes = document.getElementById('warehouse-notes');
    const hint   = document.getElementById('warehouse-notes-hint');
    if (!wNotes) return;
    if (wNotes.value.trim().length > 0) {
        if (hint) hint.style.display = 'none';
        wNotes.style.borderColor = '#10b981';
        wNotes.style.boxShadow   = '0 0 0 3px rgba(16,185,129,0.12)';
    } else {
        wNotes.style.borderColor = 'rgba(251,191,36,0.35)';
        wNotes.style.boxShadow   = 'none';
    }
}

function renderGrid() {
    if (!currentLoadedInvoice) return;
    const tbody = document.getElementById('delivery-tbody');
    tbody.innerHTML = '';
    const rows = currentLoadedInvoice.items || currentLoadedInvoice.rows || [];
    rows.forEach((row, idx) => {
        const itemName = row.itemName || row.itemCode || row.itemId || '---';
        const whName   = row.warehouseId || currentLoadedInvoice.warehouseId || 'الرئيسي';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><div class="row-num">${idx + 1}</div></td>
            <td>${row.itemId || ''}</td>
            <td>${itemName}</td>
            <td>${whName}</td>
            <td>${row.selectedUnit || row.unit || ''}</td>
            <td class="qty-col">${row.quantity || 0}</td>
            <td>تم التسليم بالكامل</td>
        `;
        tbody.appendChild(tr);
    });
}

// ── EXECUTE PHYSICAL DELIVERY (STAGE 1) ──
function executeDelivery() {
    if (!currentLoadedInvoice) return;
    if (!isVerified && currentLevel < 4) {
        showToast('يجب التحقق أولاً بالكاميرا أو إرفاق الصورة!', 'error');
        return;
    }

    // Validate warehouse notes (mandatory)
    const warehouseNotesEl = document.getElementById('warehouse-notes');
    const hint = document.getElementById('warehouse-notes-hint');
    if (!warehouseNotesEl || !warehouseNotesEl.value.trim()) {
        if (hint) hint.style.display = 'block';
        if (warehouseNotesEl) {
            warehouseNotesEl.focus();
            warehouseNotesEl.style.borderColor = '#f87171';
            warehouseNotesEl.style.boxShadow   = '0 0 0 3px rgba(239,68,68,0.2)';
        }
        showToast('يجب إدخال ملاحظات أمين المخزن أولاً (حقل إلزامي)', 'error');
        return;
    }
    if (hint) hint.style.display = 'none';

    if (!confirm('تأكيد خروج البضاعة بالكامل؟\nسيتم تسجيل العملية وبدء عداد الـ 72 ساعة للإدارة.')) return;

    const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    const invIndex = allSales.findIndex(i => i.invoiceNo === currentLoadedInvoice.invoiceNo);

    if (invIndex === -1) {
        showToast('خطأ: الفاتورة غير موجودة.', 'error');
        return;
    }

    // FIX: Single inventory deduction — only update erp_inventory_v1 (source of truth).
    // erp_items_v1.actualStock is a cache; avoid double-deducting.
    let currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
    const rows = currentLoadedInvoice.items || currentLoadedInvoice.rows || [];

    rows.forEach(row => {
        const baseQtyToDeliver = row.customBaseQty !== undefined ? row.customBaseQty : (row.baseQty || parseFloat(row.quantity) || 0);
        const whId = row.warehouseId || currentLoadedInvoice.warehouseId || 'main';

        let itemInv = currentInvData.find(inv => inv.itemId === row.itemId && inv.warehouse === whId);
        if (itemInv) {
            itemInv.qty = (parseFloat(itemInv.qty) || 0) - baseQtyToDeliver;
            itemInv.reservedQuantity = Math.max(0, (parseFloat(itemInv.reservedQuantity) || 0) - baseQtyToDeliver);
        }
    });

    localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));

    // Capture notes BEFORE updating record
    const warehouseNotesVal = warehouseNotesEl.value.trim();
    const personalNotesEl   = document.getElementById('personal-notes');
    const personalNotesVal  = personalNotesEl ? personalNotesEl.value.trim() : '';

    allSales[invIndex].deliveryStatus      = 'Delivered_Pending_Admin';
    allSales[invIndex].status              = 'Delivered_Pending_Admin';
    allSales[invIndex].deliveryDate        = new Date().toISOString();
    allSales[invIndex].adminDeadline       = Date.now() + (72 * 3600 * 1000);
    allSales[invIndex].verificationMethod  = verificationMethod;
    allSales[invIndex].attachmentBase64    = currentAttachment;
    allSales[invIndex].deliveredByLevel    = currentLevel;
    allSales[invIndex].warehouseNotes      = warehouseNotesVal;
    allSales[invIndex].personalNotes       = personalNotesVal;

    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));

    if (typeof window.logSystemAction === 'function') {
        window.logSystemAction('STAGE1_DELIVERY_EXECUTED', 'warehouse',
            `تسليم مخزني للإذن ${currentLoadedInvoice.invoiceNo} عبر ${verificationMethod} | ملاحظات: ${warehouseNotesVal}`);
    }

    showToast('🎉 تم خروج البضاعة وتفعيل رقابة الإدارة (Stage 1).', 'success');

    // FIX: Update local reference and re-render without wiping notes
    currentLoadedInvoice = allSales[invIndex];
    // Re-render details but DON'T re-show the notes panel (invoice is now delivered)
    renderInvoiceDetails();
}

function reportIssue() {
    showToast('تم الإبلاغ! الرجاء توجيه العميل لمكتب المبيعات لعمل "تسليم جزئي" أو تعديل الفاتورة.', 'error', 5000);
}
