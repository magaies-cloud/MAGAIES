// =============================================================
// MAGAIEES ERP - Unified Financial OCR Engine (Frontend)
// ماسح إشعارات التحويلات والشيكات بالذكاء الاصطناعي
// =============================================================

(function (window) {
    'use strict';

    const FinancialOCR = {
        activeContext: {
            target: 'single', // 'single' | 'multi' | 'sales' | 'purchase'
            blockId: null,
            docHint: 'auto'
        },
        currentImageBase64: null,
        lastExtractedData: null,

        // 1. Open Scanner
        openScanner(options = {}) {
            this.activeContext = Object.assign({
                target: 'single',
                blockId: null,
                docHint: 'auto'
            }, options);

            const fileInput = document.getElementById('financial-ocr-file-input');
            if (fileInput) {
                fileInput.value = '';
                fileInput.click();
            } else {
                console.error('financial-ocr-file-input element not found');
            }
        },

        triggerUploadAgain() {
            const fileInput = document.getElementById('financial-ocr-file-input');
            if (fileInput) {
                fileInput.value = '';
                fileInput.click();
            }
        },

        // 2. Handle File Selected & Client Compression
        handleFileSelected(input) {
            const file = input.files && input.files[0];
            if (!file) return;

            // Show loading overlay immediately
            this.showLoading(true, 'جاري ضغط وقراءة صورة المستند المالي...');

            // Compress image via Canvas to max 1600px
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    const maxDim = 1600;

                    if (width > maxDim || height > maxDim) {
                        if (width > height) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        } else {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    const compressedBase64 = canvas.toDataURL('image/jpeg', 0.85);
                    this.currentImageBase64 = compressedBase64;

                    // Send to OCR API
                    this.sendToOCR(compressedBase64);
                };
                img.onerror = () => {
                    this.showLoading(false);
                    this.showToast('تعذر تحميل الصورة المختارة', 'error');
                };
                img.src = e.target.result;
            };
            reader.onerror = () => {
                this.showLoading(false);
                this.showToast('فشل قراءة ملف الصورة', 'error');
            };
            reader.readAsDataURL(file);
        },

        pendingMockData: null,

        // Open API Key Setup Modal
        openKeyModal(mockData = null) {
            this.pendingMockData = mockData;
            const modal = document.getElementById('financial-ocr-key-modal');
            const keyInput = document.getElementById('gemini-api-key-input');
            if (keyInput) {
                keyInput.value = localStorage.getItem('gemini_api_key') || '';
            }
            if (modal) {
                modal.style.display = 'flex';
                modal.style.opacity = '1';
            }
        },

        closeKeyModal() {
            const modal = document.getElementById('financial-ocr-key-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        },

        async saveAndActivateKey() {
            const keyInput = document.getElementById('gemini-api-key-input');
            const key = keyInput ? keyInput.value.trim() : '';
            const statusSpan = document.getElementById('gemini-key-save-status');

            if (!key) {
                this.showToast('يرجى إدخال مفتاح Gemini API Key صالح', 'warning');
                return;
            }

            try {
                const res = await fetch('api/save-gemini-key.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ api_key: key })
                });
                const data = await res.json();
                if (data.success) {
                    localStorage.setItem('gemini_api_key', key);
                    if (statusSpan) statusSpan.style.display = 'block';
                    this.showToast('✅ تم حفظ وتفعيل مفتاح الذكاء الاصطناعي بنجاح!', 'success');
                    setTimeout(() => {
                        this.closeKeyModal();
                        if (this.currentImageBase64) {
                            this.sendToOCR(this.currentImageBase64);
                        }
                    }, 800);
                } else {
                    this.showToast(data.error || 'فشل حفظ المفتاح', 'error');
                }
            } catch (e) {
                localStorage.setItem('gemini_api_key', key);
                this.showToast('✅ تم حفظ المفتاح في المتصفح بنجاح!', 'success');
                this.closeKeyModal();
                if (this.currentImageBase64) {
                    this.sendToOCR(this.currentImageBase64);
                }
            }
        },

        useSimulationMode() {
            this.closeKeyModal();
            const dataToUse = this.pendingMockData || {
                doc_type: 'bank_transfer',
                bank_name: 'بنك الخرطوم (تجريبي)',
                app_name: 'بنكك (Bankak)',
                transaction_ref: 'TRX-58492014',
                amount: 150000.0,
                currency: 'SDG',
                date: new Date().toISOString().split('T')[0],
                due_date: new Date().toISOString().split('T')[0],
                sender_name: 'محمد أحمد عبد الله',
                sender_account: '284****',
                receiver_name: 'مؤسسة السجانة التجارية',
                receiver_account: '195****',
                notes: 'سداد دفعة نقدية بالتحويل (تجريبي)',
                confidence_score: 90,
                summary: 'تحويل بنكي تجريبي بمبلغ 150,000 جنيه عبر بنكك'
            };
            this.lastExtractedData = dataToUse;
            this.showReviewModal(dataToUse);
        },

        // 3. Send to Server API
        async sendToOCR(base64Data) {
            this.showLoading(true, 'جاري تحليل المستند واستخراج البيانات عبر Gemini AI... 🔍');

            const customGuidelines = localStorage.getItem('erp_financial_ocr_guidelines') || '';
            const clientApiKey = localStorage.getItem('gemini_api_key') || '';

            try {
                const response = await fetch('api/ocr-financial.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        image: base64Data,
                        api_key: clientApiKey,
                        custom_guidelines: customGuidelines,
                        doc_hint: this.activeContext.docHint || 'auto'
                    })
                });

                const result = await response.json();
                this.showLoading(false);

                if (result.success && result.data) {
                    this.lastExtractedData = result.data;
                    this.showReviewModal(result.data);
                } else if (result.is_mock_fallback && result.mock_data) {
                    // Open key configuration modal so user can enter key or try simulation
                    this.lastExtractedData = result.mock_data;
                    this.openKeyModal(result.mock_data);
                } else {
                    this.showToast(result.error || 'فشل استخراج بيانات المستند', 'error');
                }
            } catch (err) {
                this.showLoading(false);
                console.error('OCR Fetch Error:', err);
                this.showToast('تعذر الاتصال بخدمة الذكاء الاصطناعي: ' + err.message, 'error');
            }
        },

        // 4. Show Review & Confirmation Modal
        showReviewModal(data) {
            const modal = document.getElementById('financial-ocr-review-modal');
            if (!modal) return;

            // Set preview image
            const previewImg = document.getElementById('ocr-preview-img');
            if (previewImg && this.currentImageBase64) {
                previewImg.src = this.currentImageBase64;
            }

            // Populate form fields
            const docTypeSelect = document.getElementById('ocr-field-doc-type');
            if (docTypeSelect) {
                docTypeSelect.value = (data.doc_type === 'cheque') ? 'cheque' : 'bank_transfer';
            }

            const amountInput = document.getElementById('ocr-field-amount');
            if (amountInput) amountInput.value = data.amount || '';

            const bankNameInput = document.getElementById('ocr-field-bank-name');
            if (bankNameInput) bankNameInput.value = data.bank_name || '';

            const refNoInput = document.getElementById('ocr-field-ref-no');
            if (refNoInput) {
                refNoInput.value = (data.doc_type === 'cheque' && data.cheque_number) ? data.cheque_number : (data.transaction_ref || '');
            }

            const dateInput = document.getElementById('ocr-field-date');
            if (dateInput) {
                dateInput.value = data.date || new Date().toISOString().split('T')[0];
            }

            const dueDateInput = document.getElementById('ocr-field-due-date');
            if (dueDateInput) {
                dueDateInput.value = data.due_date || data.date || '';
            }

            const senderInput = document.getElementById('ocr-field-sender');
            if (senderInput) senderInput.value = data.sender_name || '';

            const receiverInput = document.getElementById('ocr-field-receiver');
            if (receiverInput) receiverInput.value = data.receiver_name || '';

            const notesInput = document.getElementById('ocr-field-notes');
            if (notesInput) {
                let noteText = data.notes || '';
                if (!noteText) {
                    if (data.doc_type === 'cheque') {
                        noteText = 'شيك مسحوب على ' + (data.bank_name || '') + (data.sender_name ? ' من ' + data.sender_name : '');
                    } else {
                        noteText = 'تحويل بنكي عبر ' + (data.app_name || data.bank_name || 'البنك') + (data.sender_name ? ' من ' + data.sender_name : '');
                    }
                }
                notesInput.value = noteText;
            }

            // Summary text
            const summaryElem = document.getElementById('ocr-doc-summary');
            if (summaryElem) {
                summaryElem.textContent = data.summary || 'تم التعرف على المستند بنجاح بدقة عالية.';
            }

            this.handleTypeChange(docTypeSelect ? docTypeSelect.value : data.doc_type);

            // Display modal
            modal.style.display = 'flex';
            requestAnimationFrame(() => {
                modal.style.opacity = '1';
            });
        },

        handleTypeChange(type) {
            const badge = document.getElementById('ocr-doc-badge');
            const badgeText = document.getElementById('ocr-doc-badge-text');
            const refLabel = document.getElementById('ocr-label-ref-no');
            const dueDateBox = document.getElementById('ocr-box-due-date');

            if (type === 'cheque') {
                if (badge) {
                    badge.style.background = 'rgba(168, 85, 247, 0.2)';
                    badge.style.borderColor = '#a855f7';
                    badge.style.color = '#d8b4fe';
                }
                if (badgeText) badgeText.textContent = 'شيك مصرفي ورقي / إلكتروني';
                if (refLabel) refLabel.textContent = 'رقم الشيك المصرفي *';
                if (dueDateBox) dueDateBox.style.display = 'block';
            } else {
                if (badge) {
                    badge.style.background = 'rgba(16, 185, 129, 0.2)';
                    badge.style.borderColor = '#10b981';
                    badge.style.color = '#34d399';
                }
                if (badgeText) badgeText.textContent = 'إشعار تحويل بنكي (بنكك / فوري / أوكاش)';
                if (refLabel) refLabel.textContent = 'رقم العملية / رقم التحويلة *';
                if (dueDateBox) dueDateBox.style.display = 'none';
            }
        },

        closeReviewModal() {
            const modal = document.getElementById('financial-ocr-review-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        },

        // 5. Apply Extracted Data to Active Screen
        applyToActiveScreen() {
            const docType = document.getElementById('ocr-field-doc-type')?.value || 'bank_transfer';
            const amount = parseFloat(document.getElementById('ocr-field-amount')?.value) || 0;
            const bankName = (document.getElementById('ocr-field-bank-name')?.value || '').trim();
            const refNo = (document.getElementById('ocr-field-ref-no')?.value || '').trim();
            const date = document.getElementById('ocr-field-date')?.value || '';
            const dueDate = document.getElementById('ocr-field-due-date')?.value || date;
            const sender = (document.getElementById('ocr-field-sender')?.value || '').trim();
            const receiver = (document.getElementById('ocr-field-receiver')?.value || '').trim();
            const notes = (document.getElementById('ocr-field-notes')?.value || '').trim();

            if (amount <= 0) {
                this.showToast('يرجى تحديد مبلغ صالح للمستند المالي', 'warning');
                return;
            }

            const target = this.activeContext.target || 'single';

            if (target === 'single') {
                this.applyToSinglePayment({ docType, amount, bankName, refNo, date, dueDate, notes });
            } else if (target === 'multi') {
                this.applyToMultiPayment({ docType, amount, bankName, refNo, date, dueDate, notes, blockId: this.activeContext.blockId });
            } else if (target === 'sales') {
                this.applyToDirectSales({ docType, amount, bankName, refNo, date, dueDate, notes });
            } else if (target === 'purchase') {
                this.applyToPurchase({ docType, amount, bankName, refNo, date, dueDate, notes });
            }

            this.closeReviewModal();
            this.playSuccessSound();
        },

        // Single Payment Handler
        applyToSinglePayment({ docType, amount, bankName, refNo, date, dueDate, notes }) {
            if (docType === 'cheque') {
                if (typeof window.togglePaymentSection === 'function') {
                    window.togglePaymentSection('cheque');
                }
                if (typeof window.addChequeRow === 'function') {
                    window.addChequeRow(bankName, refNo, dueDate || date, amount, notes);
                }
            } else {
                if (typeof window.togglePaymentSection === 'function') {
                    window.togglePaymentSection('bank');
                }
                if (typeof window.addTransferRow === 'function') {
                    window.addTransferRow(refNo, bankName, amount, notes);
                }
            }

            if (typeof window.recalcPaymentSummary === 'function') {
                window.recalcPaymentSummary();
            }

            this.showToast('✅ تم تفريغ بيانات ' + (docType === 'cheque' ? 'الشيك' : 'التحويل') + ' في السند بنجاح', 'success');
        },

        // Multi Payment Handler
        applyToMultiPayment({ docType, amount, bankName, refNo, date, dueDate, notes, blockId }) {
            const container = document.getElementById('accounts-blocks-container');
            if (!container) return;

            let targetBlockId = blockId;
            if (!targetBlockId) {
                const firstBlock = container.querySelector('.account-block');
                if (firstBlock) {
                    targetBlockId = firstBlock.getAttribute('data-block-id');
                } else if (typeof window.addAccountBlock === 'function') {
                    window.addAccountBlock();
                    const newBlock = container.querySelector('.account-block:last-child');
                    if (newBlock) targetBlockId = newBlock.getAttribute('data-block-id');
                }
            }

            if (!targetBlockId) {
                this.showToast('تعذر العثور على كتلة حساب لإدراج البيانات فيها', 'error');
                return;
            }

            // Set total amount field of this block
            const totalAmtInput = document.getElementById('amount-' + targetBlockId);
            if (totalAmtInput) {
                totalAmtInput.value = Math.round(amount).toLocaleString('en-US');
            }

            const statementInput = document.getElementById('statement-' + targetBlockId);
            if (statementInput && notes) {
                statementInput.value = notes;
            }

            if (docType === 'cheque') {
                // Toggle Cheque
                const chBtn = document.getElementById('pm-cheque-' + targetBlockId);
                if (chBtn && !chBtn.classList.contains('active')) {
                    chBtn.click();
                }

                const chContainer = document.getElementById('cheque-rows-' + targetBlockId);
                if (chContainer) {
                    const row = chContainer.querySelector('.cheque-row');
                    if (row) {
                        const amtInput = row.querySelector('.block-cheque-amount');
                        const bankInput = row.querySelector('.block-cheque-bank-input');
                        const noInput = row.querySelector('.block-cheque-no-input');
                        const dateInput = row.querySelector('.block-cheque-date-input');

                        if (amtInput) amtInput.value = Math.round(amount).toLocaleString('en-US');
                        if (bankInput) bankInput.value = bankName;
                        if (noInput) noInput.value = refNo;
                        if (dateInput) dateInput.value = dueDate || date;
                    }
                }
            } else {
                // Toggle Bank
                const bBtn = document.getElementById('pm-bank-' + targetBlockId);
                if (bBtn && !bBtn.classList.contains('active')) {
                    bBtn.click();
                }

                const bContainer = document.getElementById('bank-rows-' + targetBlockId);
                if (bContainer) {
                    const row = bContainer.querySelector('.bank-row');
                    if (row) {
                        const amtInput = row.querySelector('.block-bank-amount');
                        const bankInput = row.querySelector('.block-bank-name-input');
                        const noInput = row.querySelector('.block-transfer-no-input');
                        const descInput = row.querySelector('.block-bank-desc-input');

                        if (amtInput) amtInput.value = Math.round(amount).toLocaleString('en-US');
                        if (bankInput) bankInput.value = bankName;
                        if (noInput) noInput.value = refNo;
                        if (descInput) descInput.value = notes;
                    }
                }
            }

            if (typeof window.calcTotal === 'function') {
                window.calcTotal();
            }

            this.showToast('✅ تم تفريغ بيانات ' + (docType === 'cheque' ? 'الشيك' : 'التحويل') + ' في كتلة الحساب بنجاح', 'success');
        },

        // Direct Sales Handler
        applyToDirectSales({ docType, amount, bankName, refNo, date, dueDate, notes }) {
            if (docType === 'cheque') {
                if (typeof window.togglePaymentSection === 'function') {
                    window.togglePaymentSection('cheque');
                }
                if (typeof window.addChequeRow === 'function') {
                    window.addChequeRow({
                        amount: amount,
                        bankName: bankName,
                        chequeNo: refNo,
                        dueDate: dueDate || date,
                        notes: notes
                    });
                }
            } else {
                if (typeof window.togglePaymentSection === 'function') {
                    window.togglePaymentSection('bank');
                }
                if (typeof window.addBankTransferRow === 'function') {
                    window.addBankTransferRow({
                        amount: amount,
                        bankName: bankName,
                        reference: refNo,
                        notes: notes
                    });
                }
            }

            if (typeof window.onPaymentAmountInput === 'function') {
                window.onPaymentAmountInput();
            }

            this.showToast('✅ تم تفريغ بيانات الدفع في فاتورة المبيعات بنجاح', 'success');
        },

        // Purchase Invoice Handler
        applyToPurchase({ docType, amount, bankName, refNo, date, dueDate, notes }) {
            if (docType === 'cheque') {
                if (typeof window.togglePaymentSection === 'function') {
                    window.togglePaymentSection('cheque');
                }
                if (typeof window.addChequeRow === 'function') {
                    window.addChequeRow({
                        amount: amount,
                        bankName: bankName,
                        chequeNo: refNo,
                        dueDate: dueDate || date,
                        notes: notes
                    });
                }
            } else {
                if (typeof window.togglePaymentSection === 'function') {
                    window.togglePaymentSection('bank');
                }
                if (typeof window.addBankTransferRow === 'function') {
                    window.addBankTransferRow({
                        amount: amount,
                        bankName: bankName,
                        reference: refNo,
                        notes: notes
                    });
                }
            }

            if (typeof window.onPaymentAmountInput === 'function') {
                window.onPaymentAmountInput();
            }

            this.showToast('✅ تم تفريغ بيانات الدفع في فاتورة المشتريات بنجاح', 'success');
        },

        // 6. AI Trainer Modal Methods
        trainerTemplates: [],

        async openTrainer() {
            const modal = document.getElementById('financial-ocr-trainer-modal');
            if (modal) {
                modal.style.display = 'flex';
                modal.style.opacity = '1';
            }
            this.switchTrainerTab('templates');
            await this.loadTrainerData();
        },

        closeTrainer() {
            const modal = document.getElementById('financial-ocr-trainer-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        },

        switchTrainerTab(tabName) {
            const tabTemplates = document.getElementById('trainer-tab-templates');
            const tabAdd = document.getElementById('trainer-tab-add');
            const tabGuidelines = document.getElementById('trainer-tab-guidelines');

            const btnTemplates = document.getElementById('tab-btn-templates');
            const btnAdd = document.getElementById('tab-btn-add-template');
            const btnGuidelines = document.getElementById('tab-btn-guidelines');

            if (tabTemplates) tabTemplates.style.display = (tabName === 'templates') ? 'flex' : 'none';
            if (tabAdd) tabAdd.style.display = (tabName === 'add') ? 'flex' : 'none';
            if (tabGuidelines) tabGuidelines.style.display = (tabName === 'guidelines') ? 'flex' : 'none';

            // Active / Inactive Tab Button Styles
            const activeStyle = 'background:rgba(168,85,247,0.25); border:1px solid #c084fc; color:#fff; padding:8px 18px; border-radius:12px; font-weight:900; font-size:0.95rem; cursor:pointer; display:flex; align-items:center; gap:8px;';
            const inactiveStyle = 'background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:#94a3b8; padding:8px 18px; border-radius:12px; font-weight:800; font-size:0.95rem; cursor:pointer; display:flex; align-items:center; gap:8px;';

            if (btnTemplates) btnTemplates.style.cssText = (tabName === 'templates') ? activeStyle : inactiveStyle;
            if (btnAdd) btnAdd.style.cssText = (tabName === 'add') ? activeStyle : inactiveStyle;
            if (btnGuidelines) btnGuidelines.style.cssText = (tabName === 'guidelines') ? activeStyle : inactiveStyle;
        },

        async loadTrainerData() {
            try {
                const res = await fetch('api/manage-ocr-templates.php?action=get_all');
                const data = await res.json();
                if (data && data.success) {
                    this.trainerTemplates = data.templates || [];
                    this.renderTrainerTemplates();

                    const textarea = document.getElementById('financial-ocr-custom-guidelines');
                    if (textarea) {
                        textarea.value = data.guidelines || localStorage.getItem('erp_financial_ocr_guidelines') || '';
                    }
                }
            } catch (err) {
                console.error('Failed to load OCR templates:', err);
            }
        },

        renderTrainerTemplates() {
            const grid = document.getElementById('trainer-templates-grid');
            const countBadge = document.getElementById('trainer-templates-count');
            if (countBadge) {
                countBadge.textContent = this.trainerTemplates.length;
            }
            if (!grid) return;

            if (this.trainerTemplates.length === 0) {
                grid.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#94a3b8; padding:30px; background:rgba(255,255,255,0.02); border-radius:12px;">لا توجد نماذج مضافة بعد. اضغط على "شرح وإضافة نموذج بنك جديد" للبدء.</div>';
                return;
            }

            const escapeHtml = (str) => {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            };

            let html = '';
            this.trainerTemplates.forEach(tmpl => {
                const isBankTransfer = tmpl.type === 'bank_transfer';
                const isCheque = tmpl.type === 'cheque';
                const typeLabel = isCheque ? '✍️ شيك مصرفي' : (isBankTransfer ? '🏦 تحويل بنكي' : '📄 إيداع نقدي');
                const badgeColor = isCheque ? '#c084fc' : (isBankTransfer ? '#38bdf8' : '#34d399');
                const badgeBg = isCheque ? 'rgba(168,85,247,0.15)' : (isBankTransfer ? 'rgba(56,189,248,0.15)' : 'rgba(52,211,153,0.15)');

                html += `
                <div style="background:rgba(30,41,59,0.7); border:1px solid rgba(255,255,255,0.1); border-radius:14px; padding:14px; display:flex; flex-direction:column; gap:10px; transition:border-color 0.2s;" onmouseenter="this.style.borderColor='#a855f7'" onmouseleave="this.style.borderColor='rgba(255,255,255,0.1)'">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                        <div style="font-weight:900; color:#fff; font-size:1rem;">
                            ${escapeHtml(tmpl.name)}
                        </div>
                        <div style="display:flex; gap:6px;">
                            <button type="button" onclick="FinancialOCR.editTemplate('${tmpl.id}')" style="background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3); color:#7dd3fc; border-radius:6px; padding:2px 8px; font-size:0.75rem; cursor:pointer; font-weight:700;" title="تعديل وشرح إضافي لهذا النموذج">✏️ تعديل</button>
                            <button type="button" onclick="FinancialOCR.deleteTemplate('${tmpl.id}')" style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#f87171; border-radius:6px; padding:2px 8px; font-size:0.75rem; cursor:pointer; font-weight:700;" title="حذف هذا النموذج">🗑️ حذف</button>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <span style="background:${badgeBg}; color:${badgeColor}; padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:800;">
                            ${typeLabel}
                        </span>
                        <span style="background:rgba(255,255,255,0.05); color:#cbd5e1; padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:700;">
                            🏛️ ${escapeHtml(tmpl.bank)}
                        </span>
                    </div>
                    <div style="background:rgba(15,23,42,0.6); border-radius:8px; padding:10px; font-size:0.85rem; color:#cbd5e1; line-height:1.5; white-space:pre-wrap; max-height:110px; overflow-y:auto; border:1px solid rgba(255,255,255,0.05);">${escapeHtml(tmpl.rules)}</div>
                    <div style="display:flex; justify-content:flex-end; margin-top:2px;">
                        <button type="button" onclick="FinancialOCR.closeTrainer(); FinancialOCR.openScanner({ docHint: '${tmpl.type || 'auto'}' });" style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.35); color:#34d399; font-size:0.8rem; font-weight:800; padding:4px 10px; border-radius:6px; cursor:pointer; display:flex; align-items:center; gap:4px;">
                            <span>📷</span> تجربة فحص إشعار بهذا النموذج
                        </button>
                    </div>
                </div>
                `;
            });

            grid.innerHTML = html;
        },

        applyBankSuggestion(bankName, docType, hintRules) {
            const nameInput = document.getElementById('new-template-name');
            const bankInput = document.getElementById('new-template-bank');
            const typeSelect = document.getElementById('new-template-type');
            const rulesTextarea = document.getElementById('new-template-rules');

            if (nameInput) nameInput.value = (docType === 'cheque' ? 'شيك ' : 'إشعار ') + bankName;
            if (bankInput) bankInput.value = bankName;
            if (typeSelect) typeSelect.value = docType;
            if (rulesTextarea) rulesTextarea.value = hintRules;
        },

        editTemplate(id) {
            const tmpl = this.trainerTemplates.find(t => t.id === id);
            if (!tmpl) return;

            const nameInput = document.getElementById('new-template-name');
            const bankInput = document.getElementById('new-template-bank');
            const typeSelect = document.getElementById('new-template-type');
            const rulesTextarea = document.getElementById('new-template-rules');
            const idInput = document.getElementById('edit-template-id');

            if (nameInput) nameInput.value = tmpl.name || '';
            if (bankInput) bankInput.value = tmpl.bank || '';
            if (typeSelect) typeSelect.value = tmpl.type || 'bank_transfer';
            if (rulesTextarea) rulesTextarea.value = tmpl.rules || '';
            if (idInput) idInput.value = tmpl.id;

            this.switchTrainerTab('add');
        },

        async submitNewTemplate() {
            const nameInput = document.getElementById('new-template-name');
            const bankInput = document.getElementById('new-template-bank');
            const typeSelect = document.getElementById('new-template-type');
            const rulesTextarea = document.getElementById('new-template-rules');
            const idInput = document.getElementById('edit-template-id');

            const name = nameInput ? nameInput.value.trim() : '';
            const bank = bankInput ? bankInput.value.trim() : '';
            const type = typeSelect ? typeSelect.value : 'bank_transfer';
            const rules = rulesTextarea ? rulesTextarea.value.trim() : '';
            const editId = idInput ? idInput.value.trim() : '';

            if (!name || !bank || !rules) {
                this.showToast('يرجى ملء جميع الحقول المطلوبة (اسم النموذج، اسم البنك، وقواعد الفحص)', 'warning');
                return;
            }

            try {
                this.showLoading(true, 'جاري تدريب وتثبيت النموذج في عقل الذكاء الاصطناعي...');
                const res = await fetch('api/manage-ocr-templates.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'save_template',
                        template: {
                            id: editId || ('tmpl_' + Date.now()),
                            name: name,
                            bank: bank,
                            type: type,
                            rules: rules
                        }
                    })
                });
                const data = await res.json();
                this.showLoading(false);

                if (data && data.success) {
                    this.trainerTemplates = data.templates || [];
                    this.renderTrainerTemplates();
                    this.playSuccessSound();
                    this.showToast('✅ تم تدريب وحفظ النموذج بنجاح في عقل الذكاء الاصطناعي!', 'success');

                    // Reset form
                    if (nameInput) nameInput.value = '';
                    if (bankInput) bankInput.value = '';
                    if (rulesTextarea) rulesTextarea.value = '';
                    if (idInput) idInput.value = '';

                    // Return to templates tab
                    this.switchTrainerTab('templates');
                } else {
                    this.showToast((data && data.error) || 'فشل حفظ النموذج', 'error');
                }
            } catch (err) {
                this.showLoading(false);
                this.showToast('حدث خطأ أثناء حفظ النموذج في السيرفر', 'error');
                console.error(err);
            }
        },

        async deleteTemplate(id) {
            if (!confirm('هل أنت متأكد من حذف هذا النموذج التدريبي؟ لن يتمكن الذكاء الاصطناعي من الاعتماد على معالمه بعد الآن.')) {
                return;
            }

            try {
                this.showLoading(true, 'جاري حذف النموذج...');
                const res = await fetch('api/manage-ocr-templates.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'delete_template',
                        id: id
                    })
                });
                const data = await res.json();
                this.showLoading(false);

                if (data && data.success) {
                    this.trainerTemplates = data.templates || [];
                    this.renderTrainerTemplates();
                    this.showToast('تم حذف النموذج التدريبي بنجاح', 'info');
                } else {
                    this.showToast((data && data.error) || 'تعذر حذف النموذج', 'error');
                }
            } catch (err) {
                this.showLoading(false);
                this.showToast('حدث خطأ أثناء حذف النموذج', 'error');
                console.error(err);
            }
        },

        async saveCustomGuidelines() {
            const textarea = document.getElementById('financial-ocr-custom-guidelines');
            const statusSpan = document.getElementById('financial-ocr-save-status');
            const guidelines = textarea ? textarea.value.trim() : '';

            localStorage.setItem('erp_financial_ocr_guidelines', guidelines);

            try {
                const res = await fetch('api/manage-ocr-templates.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'save_guidelines',
                        guidelines: guidelines
                    })
                });
                const data = await res.json();
                if (data && data.success) {
                    if (statusSpan) {
                        statusSpan.style.display = 'inline';
                        setTimeout(() => {
                            statusSpan.style.display = 'none';
                        }, 3500);
                    }
                    this.showToast('✅ تم حفظ القواعد العامة للذكاء الاصطناعي بنجاح!', 'success');
                }
            } catch (e) {
                this.showToast('تم الحفظ محلياً بنجاح', 'warning');
            }
        },

        // 7. Loading Overlay Helper
        showLoading(show, message = '') {
            const loadingElem = document.getElementById('financial-ocr-loading');
            const msgElem = document.getElementById('financial-ocr-loading-msg');
            if (!loadingElem) return;

            if (show) {
                if (msgElem && message) msgElem.textContent = message;
                loadingElem.style.display = 'flex';
            } else {
                loadingElem.style.display = 'none';
            }
        },

        // 8. Image Zoom Modal
        zoomImage(src) {
            window.open(src, '_blank');
        },

        // 9. Sound Feedback
        playSuccessSound() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15); // A5
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.3);
            } catch (e) {}
        },

        // 10. Universal Toast
        showToast(msg, type = 'info', duration = 4000) {
            if (typeof window.showToast === 'function') {
                window.showToast(msg, type);
                return;
            }
            // Fallback lightweight toast
            const toast = document.createElement('div');
            toast.textContent = msg;
            toast.style.cssText = `
                position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%);
                background: ${type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#10b981'};
                color: #fff; padding: 12px 24px; border-radius: 30px; font-weight: 800;
                font-family: 'Tajawal', sans-serif; font-size: 1rem; z-index: 10000000;
                box-shadow: 0 10px 25px rgba(0,0,0,0.5); direction: rtl;
                transition: opacity 0.3s ease;
            `;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
    };

    window.FinancialOCR = FinancialOCR;

})(window);
