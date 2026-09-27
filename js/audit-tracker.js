/**
 * ═════════════════════════════════════════════════════════════════════
 * 🛡️ AUDIT TRACKER & USER ACCOUNTABILITY ENGINE (نظام تتبع نشاط المستخدمين)
 * السجانة سمارت ERP - مقاييس (MAGAIEES)
 * ═════════════════════════════════════════════════════════════════════
 */

(function() {
    'use strict';

    // 1. Get current authenticated user details
    window.getCurrentUserInfo = function() {
        let u = null;
        try {
            u = JSON.parse(localStorage.getItem('user') || '{}');
        } catch(e) {}
        
        const fallbackName = (document.getElementById('header-user-name')?.innerText || '').trim();
        const id = u?.id || u?.userId || u?.username || 'user_default';
        const username = u?.username || u?.name || 'user';
        const fullName = u?.fullName || u?.name || (fallbackName && fallbackName !== 'مستخدم' ? fallbackName : (u?.username || 'مدير النظام'));
        const role = u?.role || u?.userType || 'admin';

        return { id, username, fullName, role };
    };

    // 2. Global System Action Logger (Client & Server Synchronized)
    window.logSystemAction = function(actionType, moduleName, description, docInfo = null) {
        const user = window.getCurrentUserInfo();
        let logs = [];
        try {
            logs = JSON.parse(localStorage.getItem('erp_audit_logs') || '[]');
        } catch(e) {}

        const prevHash = (logs.length > 0 && logs[0].hash) 
            ? logs[0].hash 
            : 'GENESIS_HASH_MAGAIEES_ROOT_00000000000000000000000000000000';

        const timestamp = Date.now();
        const logId = 'log_' + timestamp + '_' + Math.random().toString(36).substring(2, 7);

        const newLog = {
            id: logId,
            userId: user.id,
            userName: user.fullName,
            userRole: user.role,
            actionType: actionType || 'حركة',
            module: moduleName || 'عام',
            description: description || '',
            timestamp: timestamp,
            date: new Date(timestamp).toISOString(),
            dateFormatted: new Date(timestamp).toLocaleString('ar-EG'),
            prevHash: prevHash,
            docId: docInfo?.docId || docInfo?.invoiceNo || docInfo?.voucherNo || null,
            docType: docInfo?.docType || null,
            amount: docInfo?.amount || null
        };

        // Calculate cryptographic verification hash if available
        if (typeof window.calculateAuditHash === 'function') {
            newLog.hash = window.calculateAuditHash(newLog, prevHash);
        } else {
            // Simple fast hash
            let hStr = `${newLog.id}|${newLog.timestamp}|${newLog.userId}|${newLog.actionType}|${newLog.description}|${prevHash}`;
            let h = 0;
            for (let i = 0; i < hStr.length; i++) {
                h = ((h << 5) - h) + hStr.charCodeAt(i);
                h |= 0;
            }
            newLog.hash = 'HASH_' + Math.abs(h).toString(16);
        }

        logs.unshift(newLog);

        // Keep 45 days in local storage
        const fortyFiveDaysMs = 45 * 24 * 60 * 60 * 1000;
        logs = logs.filter(l => (timestamp - l.timestamp) <= fortyFiveDaysMs);

        try {
            localStorage.setItem('erp_audit_logs', JSON.stringify(logs));
            localStorage.setItem('erp_audit_logs_v1', JSON.stringify(logs));
        } catch(e) {}

        // Fire asynchronous background persist to MySQL database
        try {
            fetch('api/log_audit.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(newLog),
                keepalive: true
            }).catch(() => {});
        } catch(e) {}

        // Dispatch Custom Event for live UI watchers
        window.dispatchEvent(new CustomEvent('ERP_AUDIT_LOGGED', { detail: newLog }));
    };

    // 3. User Attribution Badge Component
    window.renderDocumentUserBadge = function(containerOrId, docData = {}, isNew = false) {
        let el = typeof containerOrId === 'string' ? document.getElementById(containerOrId) : containerOrId;
        if (!el) return;

        const curUser = window.getCurrentUserInfo();
        const creatorName = docData.createdByName || docData.createdByUsername || docData.createdBy || curUser.fullName;
        const creationDate = docData.date || (docData.createdAt ? docData.createdAt.split('T')[0] : '') || (docData.timestamp ? new Date(docData.timestamp).toLocaleDateString('en-GB') : '');
        const creationTime = docData.time || (docData.createdAt && docData.createdAt.includes('T') ? docData.createdAt.split('T')[1].substring(0, 5) : '') || '';
        const updatedName = docData.updatedByName || docData.updatedByUsername || docData.updatedBy;
        const updatedAt = docData.updatedAt ? new Date(docData.updatedAt).toLocaleString('ar-EG', { dateStyle: 'short', timeStyle: 'short' }) : null;
        const deliveredBy = docData.deliveredByName;

        let badgeHtml = '';

        if (isNew) {
            badgeHtml = `
                <div class="user-audit-badge new-mode" title="المستخدم الحالي المسؤول عن إنشاء المستند">
                    <span class="uab-icon">👤</span>
                    <span class="uab-label">المستخدم الحالي:</span>
                    <strong class="uab-val">${curUser.fullName}</strong>
                    <span class="uab-tag">مسودة جديدة</span>
                </div>
            `;
        } else {
            badgeHtml = `
                <div class="user-audit-badge saved-mode" onclick="window.openAuditTrackerModal('${docData.invoiceNo || docData.voucherNo || docData.id || ''}')" title="انقر لتتبع سجل النشاط الكامل لهذا المستند">
                    <div class="uab-row">
                        <span class="uab-icon">👤</span>
                        <span class="uab-label">أنشئت بواسطة:</span>
                        <strong class="uab-val">${creatorName}</strong>
                        ${creationDate ? `<span class="uab-time">(${creationDate}${creationTime ? ' ' + creationTime : ''})</span>` : ''}
                    </div>
                    ${updatedName ? `
                        <div class="uab-row uab-edit-row">
                            <span class="uab-edit-icon">✏️</span>
                            <span class="uab-label">آخر تعديل:</span>
                            <strong class="uab-edit-val">${updatedName}</strong>
                            ${updatedAt ? `<span class="uab-time">(${updatedAt})</span>` : ''}
                        </div>
                    ` : ''}
                    ${deliveredBy ? `
                        <div class="uab-row uab-deliv-row">
                            <span class="uab-deliv-icon">🚚</span>
                            <span class="uab-label">التسليم المخزني:</span>
                            <strong class="uab-deliv-val">${deliveredBy}</strong>
                        </div>
                    ` : ''}
                    <div class="uab-hover-hint">🔍 انقر للتتبع الكامل</div>
                </div>
            `;
        }

        el.innerHTML = badgeHtml;
        el.style.display = 'block';
    };

    // 4. Modal / Global Document Audit Tracker
    window.openAuditTrackerModal = function(initialQuery = '') {
        let modal = document.getElementById('global-audit-tracker-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'global-audit-tracker-modal';
            modal.className = 'gat-modal-backdrop';
            modal.innerHTML = `
                <div class="gat-modal-content">
                    <div class="gat-modal-header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="gat-header-icon">🔍</div>
                            <div>
                                <h3 style="margin:0; font-size:1.15rem; color:#fff; font-weight:900;">متعقب المستندات وسجل تتبع المستخدمين</h3>
                                <div style="font-size:0.75rem; color:#94a3b8;">معرفة من أنشأ وعدّل ونفّذ أي فاتورة أو إذن أو سند (مثال: إذن 520)</div>
                            </div>
                        </div>
                        <button class="gat-close-btn" onclick="document.getElementById('global-audit-tracker-modal').style.display='none'">&times;</button>
                    </div>

                    <div class="gat-search-bar-wrap">
                        <input type="text" id="gat-search-input" placeholder="أدخل رقم الإذن أو الفاتورة أو السند (مثال: 520 أو 26070001)..." onkeydown="if(event.key==='Enter') executeAuditSearch();">
                        <button type="button" class="gat-search-btn" onclick="executeAuditSearch()">
                            <span>بحث وتتبع</span> ⚡
                        </button>
                    </div>

                    <div id="gat-results-container" class="gat-results-body">
                        <div class="gat-empty-placeholder">
                            <div style="font-size:2.5rem; margin-bottom:10px;">📋</div>
                            <div style="font-weight:700; color:#cbd5e1;">أدخل أي رقم مستند أعلاه لتتبع من أنشأه وتاريخه وتفاصيل عملياته فوراً.</div>
                            <div style="font-size:0.8rem; color:#64748b; margin-top:6px;">يدعم فواتير المبيعات، المشتريات، أذونات الصرف، سندات القبض، قيود اليومية، وأذونات المخازن.</div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        modal.style.display = 'flex';
        const input = document.getElementById('gat-search-input');
        if (input) {
            if (initialQuery) {
                input.value = initialQuery;
                window.executeAuditSearch(initialQuery);
            }
            setTimeout(() => input.focus(), 100);
        }
    };

    // 5. Execute Document Search Across Client Storage & Server API
    window.executeAuditSearch = async function(customQuery = null) {
        const input = document.getElementById('gat-search-input');
        const q = (customQuery !== null ? customQuery : (input?.value || '')).trim();
        const container = document.getElementById('gat-results-container');
        if (!container) return;

        if (!q) {
            container.innerHTML = `<div class="gat-empty-placeholder"><div style="color:#ef4444;">يرجى كتابة رقم الإذن أو الفاتورة أولاً</div></div>`;
            return;
        }

        container.innerHTML = `<div class="gat-loading"><div class="gat-spinner"></div><div>جاري البحث في قاعدة البيانات وسجلات التتبع...</div></div>`;

        // Clean query
        const cleanQ = q.replace(/[#\s]/g, '').toLowerCase();

        // 1. Local Search in localStorage keys
        let localHits = [];
        try {
            const searchTables = [
                { key: 'erp_sales_invoices_v1', type: 'sales', label: 'فاتورة مبيعات', url: 'direct-sales.php?id=' },
                { key: 'erp_purchase_invoices_v1', type: 'purchase', label: 'فاتورة مشتريات', url: 'purchase.php?id=' },
                { key: 'erp_single_payments_v1', type: 'single_payment', label: 'سند صرف نقدي', url: 'single-cash-payment.php?id=' },
                { key: 'erp_single_receipts_v1', type: 'single_receipt', label: 'سند قبض نقدي', url: 'single-cash-payment.php?type=receipt&id=' },
                { key: 'erp_payments_v1', type: 'multi_payment', label: 'حركة صرف مجمعة', url: 'multi-cash-payment.php?id=' },
                { key: 'erp_receipts_v1', type: 'multi_receipt', label: 'حركة قبض مجمعة', url: 'multi-cash-payment.php?type=receipt&id=' },
                { key: 'erp_journal_vouchers_v1', type: 'journal_voucher', label: 'قيد يومية', url: 'journal-voucher.php?id=' }
            ];

            searchTables.forEach(t => {
                let items = [];
                try { items = JSON.parse(localStorage.getItem(t.key) || '[]'); } catch(e){}
                if (!Array.isArray(items)) items = Object.values(items);

                items.forEach(it => {
                    const docNo = String(it.invoiceNo || it.voucherNo || it.receiptNo || it.paymentNo || it.jvNo || it.id || '');
                    if (docNo && (docNo.toLowerCase().includes(cleanQ) || cleanQ.includes(docNo.toLowerCase()))) {
                        localHits.push({
                            label: t.label,
                            type: t.type,
                            docNo: docNo,
                            creatorName: it.createdByName || it.createdByUsername || it.createdBy || 'غير محدد',
                            date: it.date || it.createdAt || '',
                            time: it.time || '',
                            amount: it.grandTotal || it.totalAmount || it.amountLocal || it.amount || 0,
                            currency: it.currency || 'SDG',
                            party: it.customerName || it.supplierName || it.toAccount || it.fromAccount || '—',
                            status: it.status || 'مكتمل',
                            updatedName: it.updatedByName || null,
                            updatedAt: it.updatedAt || null,
                            deliveredByName: it.deliveredByName || null,
                            url: t.url + encodeURIComponent(docNo),
                            source: 'local'
                        });
                    }
                });
            });
        } catch(e) {
            console.warn('Local search warning:', e);
        }

        // 2. Server Search via api/track_document.php
        let serverHits = [];
        let auditTimeline = [];
        try {
            const resp = await fetch(`api/track_document.php?query=${encodeURIComponent(cleanQ)}`);
            if (resp.ok) {
                const data = await resp.json();
                if (data && data.success) {
                    serverHits = data.results || [];
                    auditTimeline = data.audit_timeline || [];
                }
            }
        } catch(e) {
            console.warn('Server track warning:', e);
        }

        // Merge hits without duplicates
        const seenDocs = new Set();
        const combinedHits = [];

        [...localHits, ...serverHits].forEach(h => {
            const key = h.type + '_' + h.docNo;
            if (!seenDocs.has(key)) {
                seenDocs.add(key);
                combinedHits.push(h);
            }
        });

        // Search in local audit logs as well
        let localAuditLogs = [];
        try {
            const allLogs = JSON.parse(localStorage.getItem('erp_audit_logs') || '[]');
            localAuditLogs = allLogs.filter(l => {
                const desc = (l.description || '').toLowerCase();
                const dId = String(l.docId || '').toLowerCase();
                return desc.includes(cleanQ) || dId.includes(cleanQ);
            });
        } catch(e) {}

        const allTimeline = [...localAuditLogs, ...auditTimeline];
        allTimeline.sort((a, b) => (b.timestamp || 0) - (a.timestamp || 0));

        // Render Results
        renderAuditSearchResults(container, cleanQ, combinedHits, allTimeline);
    };

    function renderAuditSearchResults(container, query, hits, timeline) {
        if (hits.length === 0 && timeline.length === 0) {
            container.innerHTML = `
                <div class="gat-empty-placeholder">
                    <div style="font-size:2.5rem; margin-bottom:10px;">🔎</div>
                    <div style="font-weight:800; color:#f87171; font-size:1.1rem;">لم يتم العثور على أي مستند أو حركة مطابقة للرقم (#${query})</div>
                    <div style="font-size:0.85rem; color:#94a3b8; margin-top:8px;">يرجى التأكد من صحة رقم الفاتورة أو الإذن والمحاولة مرة أخرى.</div>
                </div>
            `;
            return;
        }

        let html = `
            <div style="margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
                <div style="font-weight:800; font-size:0.95rem; color:#38bdf8;">
                    تم العثور على (${hits.length}) مستند مطابق و (${timeline.length}) حركة تدقيق مسجلة
                </div>
            </div>
        `;

        if (hits.length > 0) {
            html += `<div class="gat-cards-grid">`;
            hits.forEach(h => {
                const amountFormatted = (typeof h.amount === 'number' || !isNaN(parseFloat(h.amount))) 
                    ? Number(h.amount).toLocaleString() 
                    : h.amount;

                html += `
                    <div class="gat-card">
                        <div class="gat-card-header">
                            <span class="gat-badge type-${h.type}">${h.label}</span>
                            <span class="gat-doc-no">#${h.docNo}</span>
                        </div>

                        <div class="gat-card-main-info">
                            <div class="gat-user-pill">
                                <span class="gat-icon">👤</span>
                                <div>
                                    <div class="gat-user-title">أنشئت بواسطة المستخدم:</div>
                                    <div class="gat-user-name">${h.creatorName || 'مستخدم غير محدد'}</div>
                                </div>
                            </div>

                            <div class="gat-info-grid">
                                <div>
                                    <div class="gat-info-lbl">التاريخ والوقت:</div>
                                    <div class="gat-info-val">${h.date || '—'} ${h.time || ''}</div>
                                </div>
                                <div>
                                    <div class="gat-info-lbl">المبلغ الإجمالي:</div>
                                    <div class="gat-info-val" style="color:#34d399; font-weight:800;">${amountFormatted} ${h.currency || 'SDG'}</div>
                                </div>
                                <div>
                                    <div class="gat-info-lbl">الطرف / الحساب:</div>
                                    <div class="gat-info-val" title="${h.party}">${h.party || '—'}</div>
                                </div>
                                <div>
                                    <div class="gat-info-lbl">حالة المستند:</div>
                                    <div class="gat-info-val">${h.status || 'معتمد'}</div>
                                </div>
                            </div>

                            ${h.updatedName ? `
                                <div class="gat-edit-note">
                                    <span>✏️ <strong>آخر تعديل:</strong> تم التعديل بواسطة (<strong>${h.updatedName}</strong>) ${h.updatedAt ? 'بتاريخ ' + h.updatedAt : ''}</span>
                                </div>
                            ` : ''}

                            ${h.deliveredByName ? `
                                <div class="gat-deliv-note">
                                    <span>🚚 <strong>التسليم المخزني:</strong> تم تسليم البضاعة بواسطة (<strong>${h.deliveredByName}</strong>)</span>
                                </div>
                            ` : ''}
                        </div>

                        <div class="gat-card-footer">
                            <a href="${h.url}" class="gat-open-btn" target="_blank">
                                <span>فتح ومراجعة المستند</span> ↗
                            </a>
                        </div>
                    </div>
                `;
            });
            html += `</div>`;
        }

        // Render Audit Timeline
        if (timeline.length > 0) {
            html += `
                <div class="gat-timeline-section">
                    <h4 style="margin:16px 0 10px 0; color:#fbbf24; font-size:0.95rem; display:flex; align-items:center; gap:8px;">
                        <span>📜</span> التسلسل الزمني للحركات المرتبطة بهذا الرقم (Audit Trail):
                    </h4>
                    <div class="gat-timeline-list">
            `;

            timeline.forEach(t => {
                const dateStr = t.dateFormatted || (t.timestamp ? new Date(t.timestamp).toLocaleString('ar-EG') : '—');
                html += `
                    <div class="gat-timeline-item">
                        <div class="gat-tl-dot"></div>
                        <div class="gat-tl-content">
                            <div class="gat-tl-header">
                                <span class="gat-tl-user">👤 ${t.userName || t.userId || 'مستخدم'}</span>
                                <span class="gat-tl-action">${t.actionType || 'حركة'}</span>
                                <span class="gat-tl-date">${dateStr}</span>
                            </div>
                            <div class="gat-tl-desc">${t.description || ''}</div>
                        </div>
                    </div>
                `;
            });

            html += `</div></div>`;
        }

        container.innerHTML = html;
    }

    // 6. Inject CSS styles for badges and tracker
    function injectAuditTrackerStyles() {
        if (document.getElementById('audit-tracker-styles')) return;
        const style = document.createElement('style');
        style.id = 'audit-tracker-styles';
        style.textContent = `
            /* Attribution Badge Styles */
            .user-audit-badge {
                display: inline-flex;
                flex-direction: column;
                gap: 4px;
                background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.75));
                border: 1.5px solid rgba(56, 189, 248, 0.35);
                border-radius: 12px;
                padding: 6px 14px;
                color: #f8fafc;
                font-size: 0.82rem;
                font-family: 'Tajawal', sans-serif;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3), inset 0 0 10px rgba(56, 189, 248, 0.1);
                backdrop-filter: blur(10px);
                transition: all 0.25s ease;
                direction: rtl;
                cursor: pointer;
                user-select: none;
            }
            .user-audit-badge:hover {
                border-color: #38bdf8;
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5), 0 0 15px rgba(56, 189, 248, 0.35);
            }
            .user-audit-badge .uab-row {
                display: flex;
                align-items: center;
                gap: 6px;
                flex-wrap: wrap;
            }
            .user-audit-badge .uab-label {
                color: #94a3b8;
                font-size: 0.78rem;
                font-weight: 600;
            }
            .user-audit-badge .uab-val {
                color: #38bdf8;
                font-weight: 800;
                font-size: 0.88rem;
            }
            .user-audit-badge .uab-time {
                color: #64748b;
                font-size: 0.72rem;
                font-variant-numeric: tabular-nums;
            }
            .user-audit-badge .uab-tag {
                background: rgba(16, 185, 129, 0.2);
                border: 1px solid #10b981;
                color: #34d399;
                padding: 2px 6px;
                border-radius: 6px;
                font-size: 0.7rem;
                font-weight: 800;
            }
            .user-audit-badge .uab-edit-row {
                border-top: 1px dashed rgba(255, 255, 255, 0.1);
                padding-top: 4px;
                margin-top: 2px;
            }
            .user-audit-badge .uab-edit-val {
                color: #fbbf24;
                font-weight: 800;
            }
            .user-audit-badge .uab-deliv-val {
                color: #a78bfa;
                font-weight: 800;
            }
            .user-audit-badge .uab-hover-hint {
                font-size: 0.68rem;
                color: #64748b;
                text-align: left;
                margin-top: 2px;
            }

            /* Modal Styles */
            .gat-modal-backdrop {
                position: fixed;
                top: 0; left: 0; width: 100vw; height: 100vh;
                background: rgba(0, 0, 0, 0.82);
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);
                z-index: 9999999;
                display: none;
                align-items: center;
                justify-content: center;
                direction: rtl;
                font-family: 'Tajawal', sans-serif;
                animation: gatFadeIn 0.3s ease;
            }
            @keyframes gatFadeIn { from { opacity: 0; } to { opacity: 1; } }

            .gat-modal-content {
                background: linear-gradient(145deg, #0f172a, #090d16);
                border: 1.5px solid rgba(56, 189, 248, 0.4);
                border-radius: 24px;
                width: 92%;
                max-width: 900px;
                max-height: 88vh;
                display: flex;
                flex-direction: column;
                box-shadow: 0 25px 60px rgba(0,0,0,0.8), 0 0 40px rgba(56, 189, 248, 0.25);
                overflow: hidden;
            }
            .gat-modal-header {
                padding: 18px 24px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: rgba(15, 23, 42, 0.5);
            }
            .gat-header-icon {
                width: 42px; height: 42px;
                border-radius: 12px;
                background: linear-gradient(135deg, #38bdf8, #6366f1);
                display: flex; align-items: center; justify-content: center;
                font-size: 1.25rem;
                box-shadow: 0 4px 15px rgba(56, 189, 248, 0.4);
            }
            .gat-close-btn {
                background: rgba(255, 255, 255, 0.06);
                border: 1px solid rgba(255, 255, 255, 0.15);
                color: #94a3b8;
                font-size: 1.5rem;
                border-radius: 12px;
                width: 38px; height: 38px;
                cursor: pointer;
                transition: all 0.2s;
                display: flex; align-items: center; justify-content: center;
            }
            .gat-close-btn:hover {
                color: #fff;
                background: rgba(239, 68, 68, 0.3);
                border-color: #ef4444;
            }

            .gat-search-bar-wrap {
                padding: 16px 24px;
                background: rgba(0, 0, 0, 0.3);
                border-bottom: 1px solid rgba(255, 255, 255, 0.06);
                display: flex;
                gap: 12px;
            }
            .gat-search-bar-wrap input {
                flex: 1;
                background: rgba(255, 255, 255, 0.06);
                border: 1.5px solid rgba(56, 189, 248, 0.3);
                border-radius: 14px;
                padding: 12px 18px;
                color: #fff;
                font-size: 0.95rem;
                font-family: 'Tajawal', sans-serif;
                outline: none;
                transition: all 0.25s;
            }
            .gat-search-bar-wrap input:focus {
                border-color: #38bdf8;
                box-shadow: 0 0 15px rgba(56, 189, 248, 0.4);
                background: rgba(255, 255, 255, 0.09);
            }
            .gat-search-btn {
                background: linear-gradient(135deg, #0284c7, #38bdf8);
                color: #fff;
                border: none;
                border-radius: 14px;
                padding: 0 24px;
                font-weight: 800;
                font-size: 0.95rem;
                cursor: pointer;
                font-family: 'Tajawal', sans-serif;
                display: flex; align-items: center; gap: 8px;
                box-shadow: 0 4px 15px rgba(56, 189, 248, 0.35);
                transition: all 0.25s;
                white-space: nowrap;
            }
            .gat-search-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(56, 189, 248, 0.55);
            }

            .gat-results-body {
                padding: 20px 24px;
                overflow-y: auto;
                flex: 1;
            }
            .gat-empty-placeholder {
                text-align: center;
                padding: 40px 20px;
                color: #94a3b8;
            }
            .gat-loading {
                text-align: center;
                padding: 40px 20px;
                color: #38bdf8;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 12px;
            }
            .gat-spinner {
                width: 36px; height: 36px;
                border: 3px solid rgba(56, 189, 248, 0.2);
                border-top-color: #38bdf8;
                border-radius: 50%;
                animation: spin 0.8s linear infinite;
            }
            @keyframes spin { to { transform: rotate(360deg); } }

            .gat-cards-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
                gap: 16px;
                margin-bottom: 20px;
            }
            .gat-card {
                background: rgba(30, 41, 59, 0.4);
                border: 1px solid rgba(255, 255, 255, 0.09);
                border-radius: 18px;
                padding: 16px;
                display: flex;
                flex-direction: column;
                gap: 12px;
                transition: all 0.25s;
            }
            .gat-card:hover {
                border-color: rgba(56, 189, 248, 0.4);
                background: rgba(30, 41, 59, 0.65);
                box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            }
            .gat-card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .gat-badge {
                padding: 4px 10px;
                border-radius: 8px;
                font-size: 0.75rem;
                font-weight: 800;
            }
            .gat-badge.type-sales { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }
            .gat-badge.type-purchase { background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); }
            .gat-badge.type-single_payment, .gat-badge.type-multi_payment { background: rgba(244, 63, 94, 0.15); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.3); }
            .gat-badge.type-single_receipt, .gat-badge.type-multi_receipt { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
            .gat-badge.type-journal_voucher { background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); }

            .gat-doc-no {
                font-weight: 900;
                font-size: 1.1rem;
                color: #fff;
                font-family: 'Outfit', sans-serif;
            }

            .gat-user-pill {
                background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(99, 102, 241, 0.1));
                border: 1px solid rgba(56, 189, 248, 0.3);
                border-radius: 12px;
                padding: 10px 14px;
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 10px;
            }
            .gat-user-title {
                font-size: 0.72rem;
                color: #94a3b8;
                font-weight: 600;
            }
            .gat-user-name {
                font-size: 1rem;
                color: #38bdf8;
                font-weight: 900;
            }

            .gat-info-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                background: rgba(0,0,0,0.2);
                border-radius: 12px;
                padding: 10px 12px;
            }
            .gat-info-lbl { font-size: 0.72rem; color: #94a3b8; }
            .gat-info-val { font-size: 0.85rem; color: #fff; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

            .gat-edit-note {
                background: rgba(245, 158, 11, 0.1);
                border: 1px dashed rgba(245, 158, 11, 0.35);
                border-radius: 8px;
                padding: 6px 10px;
                font-size: 0.78rem;
                color: #fbd38d;
                margin-top: 8px;
            }
            .gat-deliv-note {
                background: rgba(168, 85, 247, 0.1);
                border: 1px dashed rgba(168, 85, 247, 0.35);
                border-radius: 8px;
                padding: 6px 10px;
                font-size: 0.78rem;
                color: #d8b4fe;
                margin-top: 6px;
            }

            .gat-card-footer {
                margin-top: 4px;
                border-top: 1px solid rgba(255, 255, 255, 0.06);
                padding-top: 10px;
            }
            .gat-open-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                width: 100%;
                background: rgba(56, 189, 248, 0.12);
                border: 1px solid rgba(56, 189, 248, 0.35);
                color: #38bdf8;
                padding: 8px;
                border-radius: 10px;
                text-decoration: none;
                font-size: 0.85rem;
                font-weight: 800;
                transition: all 0.2s;
            }
            .gat-open-btn:hover {
                background: #38bdf8;
                color: #0f172a;
            }

            /* Timeline */
            .gat-timeline-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
                position: relative;
                padding-right: 18px;
                border-right: 2px solid rgba(255, 255, 255, 0.1);
                margin-right: 6px;
            }
            .gat-timeline-item {
                position: relative;
            }
            .gat-tl-dot {
                position: absolute;
                top: 6px;
                right: -24px;
                width: 10px; height: 10px;
                border-radius: 50%;
                background: #fbbf24;
                box-shadow: 0 0 10px #fbbf24;
            }
            .gat-tl-content {
                background: rgba(255, 255, 255, 0.03);
                border: 1px solid rgba(255, 255, 255, 0.07);
                border-radius: 12px;
                padding: 10px 14px;
            }
            .gat-tl-header {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 4px;
                flex-wrap: wrap;
            }
            .gat-tl-user { color: #38bdf8; font-weight: 800; font-size: 0.85rem; }
            .gat-tl-action { background: rgba(255,255,255,0.08); padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; color: #fff; }
            .gat-tl-date { color: #64748b; font-size: 0.72rem; margin-right: auto; }
            .gat-tl-desc { color: #cbd5e1; font-size: 0.82rem; line-height: 1.5; }
        `;
        document.head.appendChild(style);
    }

    // Auto-inject styles when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', injectAuditTrackerStyles);
    } else {
        injectAuditTrackerStyles();
    }

    // Global Key Shortcut: Alt + T to open Document Tracker from any screen
    window.addEventListener('keydown', function(e) {
        if (e.altKey && (e.key === 't' || e.key === 'T' || e.key === 'ف')) {
            e.preventDefault();
            window.openAuditTrackerModal();
        }
    });

})();
