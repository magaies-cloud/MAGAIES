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
                requestAnimationFrame(() => {
                    modal.style.opacity = '1';
                });
            }
        },

        closeKeyModal() {
            const modal = document.getElementById('financial-ocr-key-modal');
            if (modal) {
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 250);
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
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 250);
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
        openTrainer() {
            const modal = document.getElementById('financial-ocr-trainer-modal');
            const textarea = document.getElementById('financial-ocr-custom-guidelines');
            if (textarea) {
                textarea.value = localStorage.getItem('erp_financial_ocr_guidelines') || '';
            }
            if (modal) {
                modal.style.display = 'flex';
                requestAnimationFrame(() => {
                    modal.style.opacity = '1';
                });
            }
        },

        closeTrainer() {
            const modal = document.getElementById('financial-ocr-trainer-modal');
            if (modal) {
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 250);
            }
        },

        saveCustomGuidelines() {
            const textarea = document.getElementById('financial-ocr-custom-guidelines');
            const statusSpan = document.getElementById('financial-ocr-save-status');
            if (textarea) {
                localStorage.setItem('erp_financial_ocr_guidelines', textarea.value.trim());
                if (statusSpan) {
                    statusSpan.style.display = 'inline';
                    setTimeout(() => {
                        statusSpan.style.display = 'none';
                    }, 3500);
                }
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
