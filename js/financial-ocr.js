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
        batchItems: [], // Array of { id, file, fileName, base64, data, applied }
        currentBatchIndex: 0,
        previewRotation: 0,
        previewZoom: {
            scale: 1.0,
            translateX: 0,
            translateY: 0,
            isDragging: false,
            startX: 0,
            startY: 0,
            hasMoved: false
        },
        _previewZoomBound: false,
        loadingTimerInterval: null,
        loadingStartTime: 0,
        isStreamingInBackground: false,
        totalFilesCount: 0,

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

        // ═══ WhatsApp AI Hybrid Ingest System ═══
        whatsAppSelectedFiles: [],
        _globalPasteBound: false,

        openWhatsAppIngest(options = {}) {
            this.activeContext = Object.assign({
                target: 'multi',
                blockId: null,
                docHint: 'auto'
            }, options);

            this.whatsAppSelectedFiles = [];
            this.renderWhatsAppTray();

            const modal = document.getElementById('financial-ocr-whatsapp-modal');
            if (modal) {
                modal.style.display = 'flex';
                this.initGlobalPasteListener();
            } else {
                this.openScanner(options);
            }
        },

        closeWhatsAppIngest() {
            const modal = document.getElementById('financial-ocr-whatsapp-modal');
            if (modal) {
                modal.style.display = 'none';
            }
            this.whatsAppSelectedFiles = [];
        },

        openWhatsAppWebPopup() {
            const width = Math.min(1000, window.screen.availWidth - 100);
            const height = Math.min(800, window.screen.availHeight - 100);
            const left = Math.max(20, (window.screen.availWidth - width) / 2);
            const top = Math.max(20, (window.screen.availHeight - height) / 2);

            const popup = window.open('https://web.whatsapp.com', 'WhatsAppWebPopup', `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes,status=no`);
            if (popup) {
                try { popup.focus(); } catch (e) {}
                this.showToast('💡 تم فتح واتساب ويب! يمكنك نسخ صورة الإشعار ولصقها هنا بـ Ctrl+V أو سحبها مباشرة إلى هذا المربع.', 'info');
            } else {
                window.open('https://web.whatsapp.com', '_blank');
                this.showToast('💡 تم فتح واتساب ويب في تبويب جديد! انسخ الإشعار ثم اضغط Ctrl+V هنا.', 'info');
            }
        },

        async pasteFromClipboard() {
            try {
                if (navigator.clipboard && navigator.clipboard.read) {
                    const items = await navigator.clipboard.read();
                    const newFiles = [];
                    for (const item of items) {
                        for (const type of item.types) {
                            if (type.startsWith('image/')) {
                                const blob = await item.getType(type);
                                const ext = type.split('/')[1] || 'png';
                                const file = new File([blob], `whatsapp_pasted_${Date.now()}_${newFiles.length + 1}.${ext}`, { type: blob.type });
                                newFiles.push(file);
                            }
                        }
                    }
                    if (newFiles.length > 0) {
                        this.handleWhatsAppFiles(newFiles);
                        this.showToast(`✅ تم لصق ${newFiles.length} ${newFiles.length === 1 ? 'إشعار' : 'إشعارات'} بنجاح من الحافظة!`, 'success');
                        return;
                    }
                }
            } catch (err) {
                console.warn('Clipboard read error:', err);
            }

            // Fallback instruction
            this.showToast('💡 قم بنسخ صورة الإشعار من الواتساب (Copy Image) ثم اضغط [Ctrl + V] في الكيبورد للصقها فوراً!', 'info', 6000);
        },

        async pasteFromClipboardDirect() {
            try {
                const newFiles = [];
                if (navigator.clipboard && navigator.clipboard.read) {
                    const items = await navigator.clipboard.read();
                    for (const item of items) {
                        for (const type of item.types) {
                            if (type.startsWith('image/')) {
                                const blob = await item.getType(type);
                                const ext = type.split('/')[1] || 'png';
                                const file = new File([blob], `whatsapp_pasted_${Date.now()}_${newFiles.length + 1}.${ext}`, { type: blob.type });
                                newFiles.push(file);
                            }
                        }
                    }
                }
                if (newFiles.length > 0) {
                    this.activeContext = Object.assign({ target: 'multi', blockId: null, docHint: 'auto' }, this.activeContext || {});
                    await this.handleFileSelected({ files: newFiles });
                    return;
                }
            } catch (err) {
                console.warn('Clipboard direct read error:', err);
            }

            this.showToast('💡 قم بنسخ صورة الإشعار من الواتساب (Copy Image) ثم اضغط [Ctrl + V] في الكيبورد للصقها وتفريغها فوراً!', 'info', 6000);
        },

        handleWhatsAppDirectFiles(filesList) {
            if (!filesList || filesList.length === 0) return;
            this.activeContext = Object.assign({ target: 'multi', blockId: null, docHint: 'auto' }, this.activeContext || {});
            this.handleFileSelected({ files: Array.from(filesList) });
        },

        handleWhatsAppDirectDrop(event) {
            event.preventDefault();
            event.stopPropagation();
            const dock = document.getElementById('mcp-whatsapp-ingest-dock');
            if (dock) {
                dock.style.borderColor = '#25D366';
                dock.style.background = 'linear-gradient(135deg, rgba(37,211,102,0.12), rgba(15,23,42,0.75))';
            }
            const files = event.dataTransfer ? event.dataTransfer.files : [];
            if (files && files.length > 0) {
                this.activeContext = Object.assign({ target: 'multi', blockId: null, docHint: 'auto' }, this.activeContext || {});
                this.handleFileSelected({ files: Array.from(files) });
            } else {
                this.handleWhatsAppDrop(event);
            }
        },

        initGlobalPasteListener() {
            if (this._globalPasteBound) return;
            this._globalPasteBound = true;

            window.addEventListener('paste', (e) => {
                const items = (e.clipboardData || e.originalEvent?.clipboardData)?.items;
                const files = (e.clipboardData || e.originalEvent?.clipboardData)?.files;
                
                let hasImage = false;
                const newFiles = [];

                // Check clipboard items
                if (items && items.length > 0) {
                    for (let i = 0; i < items.length; i++) {
                        if (items[i].type && items[i].type.indexOf('image') !== -1) {
                            hasImage = true;
                            const blob = items[i].getAsFile();
                            if (blob) {
                                const ext = blob.type.split('/')[1] || 'png';
                                const file = new File([blob], `whatsapp_pasted_${Date.now()}_${i + 1}.${ext}`, { type: blob.type });
                                newFiles.push(file);
                            }
                        }
                    }
                }

                // Check clipboard files fallback
                if (newFiles.length === 0 && files && files.length > 0) {
                    for (let i = 0; i < files.length; i++) {
                        if (files[i].type && files[i].type.startsWith('image/')) {
                            hasImage = true;
                            newFiles.push(files[i]);
                        }
                    }
                }

                if (hasImage && newFiles.length > 0) {
                    e.preventDefault();
                    
                    const whatsappModal = document.getElementById('financial-ocr-whatsapp-modal');
                    if (whatsappModal && whatsappModal.style.display === 'flex') {
                        this.handleWhatsAppFiles(newFiles);
                        this.showToast(`✅ تم التقاط ${newFiles.length} ${newFiles.length === 1 ? 'إشعار' : 'إشعارات'} من الواتساب عبر الحافظة (Paste) بنجاح!`, 'success');
                        return;
                    }

                    // Direct scan & auto-fill
                    this.activeContext = Object.assign({ target: 'multi', blockId: null, docHint: 'auto' }, this.activeContext || {});
                    this.handleFileSelected({ files: newFiles });
                    this.showToast(`✅ تم التقاط ${newFiles.length} ${newFiles.length === 1 ? 'إشعار' : 'إشعارات'} من الواتساب وبدء التفريغ الفوري!`, 'success');
                }
            });
        },

        async handleWhatsAppDrop(event) {
            event.preventDefault();
            event.stopPropagation();
            const dropzone = document.getElementById('whatsapp-dropzone');
            if (dropzone) {
                dropzone.style.background = 'rgba(37,211,102,0.06)';
                dropzone.style.borderColor = '#25D366';
            }

            // 1. Direct files from Windows Explorer / Desktop / Downloads
            if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length > 0) {
                this.handleWhatsAppFiles(event.dataTransfer.files);
                return;
            }

            // 2. Items with file/blob kind
            if (event.dataTransfer && event.dataTransfer.items && event.dataTransfer.items.length > 0) {
                const itemFiles = [];
                for (let i = 0; i < event.dataTransfer.items.length; i++) {
                    const item = event.dataTransfer.items[i];
                    if (item.kind === 'file') {
                        const f = item.getAsFile();
                        if (f) itemFiles.push(f);
                    }
                }
                if (itemFiles.length > 0) {
                    this.handleWhatsAppFiles(itemFiles);
                    return;
                }
            }

            // 3. HTML with <img> tags dragged directly from WhatsApp Web tabs
            const html = event.dataTransfer ? event.dataTransfer.getData('text/html') : '';
            if (html) {
                try {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const img = doc.querySelector('img');
                    if (img && img.src && (img.src.startsWith('http') || img.src.startsWith('data:image'))) {
                        const res = await fetch(img.src);
                        const blob = await res.blob();
                        const ext = blob.type.split('/')[1] || 'png';
                        const file = new File([blob], `whatsapp_dragged_${Date.now()}.${ext}`, { type: blob.type });
                        this.handleWhatsAppFiles([file]);
                        return;
                    }
                } catch (err) {
                    console.warn('Failed to parse dragged img src:', err);
                }
            }

            // 4. URL string
            const url = event.dataTransfer ? (event.dataTransfer.getData('URL') || event.dataTransfer.getData('text/uri-list')) : '';
            if (url && (url.startsWith('http') || url.startsWith('data:image'))) {
                try {
                    const res = await fetch(url);
                    const blob = await res.blob();
                    const ext = blob.type.split('/')[1] || 'png';
                    const file = new File([blob], `whatsapp_dragged_${Date.now()}.${ext}`, { type: blob.type });
                    this.handleWhatsAppFiles([file]);
                    return;
                } catch (err) {
                    console.warn('Failed to fetch dragged URL:', err);
                }
            }

            this.showToast('💡 لنسخ صورة من واتساب ويب: اضغط كليك يمين على الصورة في الواتساب واختر Copy Image ثم اضغط [📋 لصق من الحافظة] أو Ctrl+V هنا!', 'info', 6000);
        },

        handleWhatsAppFiles(filesList) {
            if (!filesList || filesList.length === 0) return;
            const incoming = Array.from(filesList).filter(f => f.type.startsWith('image/') || f.name.match(/\.(png|jpe?g|webp|gif|bmp|pdf)$/i));
            if (incoming.length === 0) {
                this.showToast('⚠️ يرجى اختيار ملفات صور لإشعارات التحويل (PNG, JPG, JPEG)', 'warning');
                return;
            }

            this.whatsAppSelectedFiles.push(...incoming);
            this.renderWhatsAppTray();
            this.playSuccessSound();
            this.showToast(`📥 تم تجميع ${incoming.length} إشعار جديد (الإجمالي: ${this.whatsAppSelectedFiles.length} إشعار جاهز للمسح)`, 'info');
        },

        removeWhatsAppFile(idx) {
            if (idx >= 0 && idx < this.whatsAppSelectedFiles.length) {
                this.whatsAppSelectedFiles.splice(idx, 1);
                this.renderWhatsAppTray();
            }
        },

        clearWhatsAppTray() {
            this.whatsAppSelectedFiles = [];
            this.renderWhatsAppTray();
        },

        renderWhatsAppTray() {
            const wrapper = document.getElementById('whatsapp-tray-wrapper');
            const gallery = document.getElementById('whatsapp-tray-gallery');
            const countBadge = document.getElementById('whatsapp-tray-count-badge');
            const startBtn = document.getElementById('btn-start-whatsapp-scan');
            const startBtnText = document.getElementById('btn-start-whatsapp-scan-text');
            const clearBtn = document.getElementById('btn-clear-whatsapp-tray');

            const count = this.whatsAppSelectedFiles.length;

            if (count > 0) {
                if (wrapper) wrapper.style.display = 'block';
                if (clearBtn) clearBtn.style.display = 'inline-flex';
                if (countBadge) countBadge.textContent = `🖼️ الإشعارات الجاهزة للمسح: ( ${count} ${count === 1 ? 'إشعار' : 'إشعارات'} )`;
                if (startBtn) {
                    startBtn.disabled = false;
                    startBtn.style.opacity = '1';
                    startBtn.style.cursor = 'pointer';
                }
                if (startBtnText) startBtnText.textContent = `🚀 ابدأ المسح وتفريغ البيانات في السند (${count} ${count === 1 ? 'إشعار' : 'إشعارات'})`;

                if (gallery) {
                    gallery.innerHTML = '';
                    this.whatsAppSelectedFiles.forEach((file, idx) => {
                        const card = document.createElement('div');
                        card.style.cssText = 'position:relative; flex:0 0 105px; width:105px; height:125px; background:#1e293b; border:1.5px solid #25D366; border-radius:10px; overflow:hidden; display:flex; flex-direction:column; box-shadow:0 4px 12px rgba(0,0,0,0.5);';

                        const delBtn = document.createElement('button');
                        delBtn.type = 'button';
                        delBtn.innerHTML = '✕';
                        delBtn.style.cssText = 'position:absolute; top:4px; right:4px; z-index:5; width:22px; height:22px; border-radius:50%; background:rgba(239,68,68,0.9); color:#fff; border:none; font-size:0.75rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.5);';
                        delBtn.onclick = (e) => {
                            e.stopPropagation();
                            FinancialOCR.removeWhatsAppFile(idx);
                        };

                        const badge = document.createElement('div');
                        badge.style.cssText = 'position:absolute; top:4px; left:4px; z-index:5; background:rgba(15,23,42,0.85); color:#86efac; border:1px solid #25D366; font-size:0.65rem; font-weight:900; padding:1px 5px; border-radius:6px;';
                        badge.textContent = `#${idx + 1}`;

                        const imgWrap = document.createElement('div');
                        imgWrap.style.cssText = 'flex:1; width:100%; background:#0f172a; display:flex; align-items:center; justify-content:center; overflow:hidden;';

                        const img = document.createElement('img');
                        img.style.cssText = 'width:100%; height:100%; object-fit:cover;';
                        const objectUrl = URL.createObjectURL(file);
                        img.src = objectUrl;

                        const nameLbl = document.createElement('div');
                        nameLbl.style.cssText = 'padding:3px 6px; background:rgba(15,23,42,0.95); color:#e2e8f0; font-size:0.65rem; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-align:center; direction:ltr;';
                        nameLbl.textContent = file.name || `إشعار #${idx + 1}`;

                        card.appendChild(delBtn);
                        card.appendChild(badge);
                        imgWrap.appendChild(img);
                        card.appendChild(imgWrap);
                        card.appendChild(nameLbl);
                        gallery.appendChild(card);
                    });
                }
            } else {
                if (wrapper) wrapper.style.display = 'none';
                if (clearBtn) clearBtn.style.display = 'none';
                if (countBadge) countBadge.textContent = '🖼️ الإشعارات الجاهزة للمسح: ( 0 إشعار )';
                if (startBtn) {
                    startBtn.disabled = true;
                    startBtn.style.opacity = '0.5';
                    startBtn.style.cursor = 'not-allowed';
                }
                if (startBtnText) startBtnText.textContent = 'ابدأ المسح وتفريغ البيانات في السند (0 إشعار)';
                if (gallery) gallery.innerHTML = '';
            }
        },

        async startWhatsAppScan() {
            if (!this.whatsAppSelectedFiles || this.whatsAppSelectedFiles.length === 0) {
                this.showToast('⚠️ يرجى إضافة إشعار واحد على الأقل قبل بدء المسح', 'warning');
                return;
            }

            const filesToProcess = [...this.whatsAppSelectedFiles];
            this.closeWhatsAppIngest();
            await this.handleFileSelected({ files: filesToProcess });
        },

        // Navigation between batch items (Arrows and Tabs)
        prevItem() {
            if (!this.batchItems || this.batchItems.length <= 1) return;
            this.syncCurrentItemFromForm();
            const newIdx = (this.currentBatchIndex > 0) ? this.currentBatchIndex - 1 : this.batchItems.length - 1;
            this.selectBatchItem(newIdx);
        },

        nextItem() {
            if (!this.batchItems || this.batchItems.length <= 1) return;
            this.syncCurrentItemFromForm();
            const newIdx = (this.currentBatchIndex < this.batchItems.length - 1) ? this.currentBatchIndex + 1 : 0;
            this.selectBatchItem(newIdx);
        },

        // Update Image Preview Transform (Smooth Scale, Pan, and Rotation)
        updatePreviewTransform() {
            const img = document.getElementById('ocr-preview-img');
            const container = document.getElementById('ocr-preview-container');
            const badge = document.getElementById('ocr-preview-zoom-badge');
            if (!img) return;

            const z = this.previewZoom;
            img.style.transform = `translate(${z.translateX}px, ${z.translateY}px) scale(${z.scale}) rotate(${this.previewRotation || 0}deg)`;

            if (badge) {
                badge.textContent = `${Math.round(z.scale * 100)}%`;
                if (Math.abs(z.scale - 1) > 0.05 || (this.previewRotation % 360 !== 0)) {
                    badge.style.background = 'linear-gradient(135deg, #0284c7, #0369a1)';
                    badge.style.borderColor = '#38bdf8';
                    badge.style.color = '#ffffff';
                    badge.style.boxShadow = '0 0 10px rgba(56,189,248,0.5)';
                } else {
                    badge.style.background = 'rgba(56,189,248,0.22)';
                    badge.style.borderColor = '#38bdf8';
                    badge.style.color = '#e0f2fe';
                    badge.style.boxShadow = 'none';
                }
            }

            if (container) {
                if (z.isDragging) {
                    container.style.cursor = 'grabbing';
                } else if (z.scale > 1.05) {
                    container.style.cursor = 'grab';
                } else {
                    container.style.cursor = 'zoom-in';
                }
            }

            const banner = document.getElementById('ocr-preview-status-banner');
            if (banner) {
                if (z.scale > 1.05 || z.isDragging) {
                    banner.style.opacity = '0.15';
                    banner.style.pointerEvents = 'none';
                } else if (banner.style.display === 'flex') {
                    banner.style.opacity = '1';
                    banner.style.pointerEvents = 'auto';
                }
            }

            this.updateFullSizeButtonState();
        },

        zoomInPreview() {
            this.previewZoom.scale = Math.min(this.previewZoom.scale * 1.25, 6.0);
            this.updatePreviewTransform();
        },

        zoomOutPreview() {
            this.previewZoom.scale = Math.max(this.previewZoom.scale / 1.25, 0.5);
            if (this.previewZoom.scale <= 1.02) {
                this.previewZoom.scale = 1.0;
                this.previewZoom.translateX = 0;
                this.previewZoom.translateY = 0;
            }
            this.updatePreviewTransform();
        },

        // Focus specifically on the vital data region (Transaction ref down to amount)
        focusDataRegion() {
            this.previewZoom.scale = 1.62; // تصغير الحجم بنسبة 5%
            this.previewZoom.translateX = 0;
            this.previewZoom.translateY = 25; // تنزيل الإشعار لأسفل لتركيز منطقة البيانات كاملة
            this.previewZoom.isDragging = false;
            this.previewZoom.hasMoved = false;
            this.updatePreviewTransform();
        },

        resetToFullSize() {
            this.previewZoom.scale = 1.0;
            this.previewZoom.translateX = 0;
            this.previewZoom.translateY = 0;
            this.previewZoom.isDragging = false;
            this.previewZoom.hasMoved = false;
            this.updatePreviewTransform();
        },

        toggleFullSizeView() {
            if (this.previewZoom.scale > 1.05) {
                this.resetToFullSize();
            } else {
                this.focusDataRegion();
            }
        },

        updateFullSizeButtonState() {
            const btn = document.getElementById('btn-full-size-preview');
            const textSpan = document.getElementById('btn-full-size-text');
            if (!btn || !textSpan) return;

            if (this.previewZoom.scale > 1.05) {
                textSpan.textContent = 'الحجم الكامل';
                btn.title = 'عرض المستند بالحجم الكامل (100%)';
                btn.style.borderColor = '#10b981';
                btn.style.color = '#a7f3d0';
            } else {
                textSpan.textContent = 'تركيز البيانات';
                btn.title = 'التركيز على منطقة البيانات (المبلغ ورقم التحويلة)';
                btn.style.borderColor = '#38bdf8';
                btn.style.color = '#7dd3fc';
            }
        },

        resetPreviewZoom() {
            this.previewZoom.scale = 1.0;
            this.previewZoom.translateX = 0;
            this.previewZoom.translateY = 0;
            this.previewZoom.isDragging = false;
            this.previewZoom.hasMoved = false;
            this.updatePreviewTransform();
        },

        // Rotate Image Preview 90 degrees
        rotatePreviewImage() {
            this.previewRotation = ((this.previewRotation || 0) + 90) % 360;
            this.updatePreviewTransform();
        },

        // Bind Mouse Wheel (الرولا) and Pan Events on Preview Container
        bindPreviewZoomEvents() {
            if (this._previewZoomBound) return;
            const container = document.getElementById('ocr-preview-container');
            if (!container) return;

            // 1. Mouse Wheel Zoom (عجلة الفأرة / الرولا)
            container.addEventListener('wheel', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const factor = (e.deltaY < 0) ? 1.18 : (1 / 1.18);
                const currentScale = this.previewZoom.scale;
                const newScale = Math.min(Math.max(currentScale * factor, 0.6), 6.0);

                if (newScale <= 1.02 && factor < 1) {
                    this.previewZoom.scale = 1.0;
                    this.previewZoom.translateX = 0;
                    this.previewZoom.translateY = 0;
                } else {
                    const rect = container.getBoundingClientRect();
                    const mouseX = e.clientX - (rect.left + rect.width / 2);
                    const mouseY = e.clientY - (rect.top + rect.height / 2);

                    const scaleRatio = newScale / currentScale;
                    this.previewZoom.translateX = mouseX - (mouseX - this.previewZoom.translateX) * scaleRatio;
                    this.previewZoom.translateY = mouseY - (mouseY - this.previewZoom.translateY) * scaleRatio;
                    this.previewZoom.scale = newScale;
                }

                this.updatePreviewTransform();
            }, { passive: false });

            // 2. Mouse Drag / Pan (التحريك بالسحب بالماوس)
            container.addEventListener('mousedown', (e) => {
                if (e.button !== 0) return; // Left button only
                this.previewZoom.isDragging = true;
                this.previewZoom.hasMoved = false;
                this.previewZoom.startX = e.clientX - this.previewZoom.translateX;
                this.previewZoom.startY = e.clientY - this.previewZoom.translateY;
                container.style.cursor = 'grabbing';
            });

            window.addEventListener('mousemove', (e) => {
                if (!this.previewZoom.isDragging) return;
                const newX = e.clientX - this.previewZoom.startX;
                const newY = e.clientY - this.previewZoom.startY;
                if (Math.abs(newX - this.previewZoom.translateX) > 3 || Math.abs(newY - this.previewZoom.translateY) > 3) {
                    this.previewZoom.hasMoved = true;
                }
                this.previewZoom.translateX = newX;
                this.previewZoom.translateY = newY;
                this.updatePreviewTransform();
            });

            window.addEventListener('mouseup', () => {
                if (!this.previewZoom.isDragging) return;
                this.previewZoom.isDragging = false;
                container.style.cursor = (this.previewZoom.scale > 1.05) ? 'grab' : 'zoom-in';
            });

            // 3. Double Click to toggle between 100% and 220%
            container.addEventListener('dblclick', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (this.previewZoom.scale > 1.2) {
                    this.resetPreviewZoom();
                } else {
                    const rect = container.getBoundingClientRect();
                    const mouseX = e.clientX - (rect.left + rect.width / 2);
                    const mouseY = e.clientY - (rect.top + rect.height / 2);
                    this.previewZoom.scale = 2.2;
                    this.previewZoom.translateX = -mouseX * 0.7;
                    this.previewZoom.translateY = -mouseY * 0.7;
                    this.updatePreviewTransform();
                }
            });

            this._previewZoomBound = true;
        },

        // 2. Client-Side Image Compression (High-precision 1600px / 0.85 quality for crisp Arabic OCR & check handwriting)
        compressImage(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;
                        const maxDim = 1600; // Optimal 1600px: ultra-sharp for small Bankak fonts, account numbers & MICR checks

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
                        if (ctx) {
                            ctx.imageSmoothingEnabled = true;
                            ctx.imageSmoothingQuality = 'high';
                            ctx.drawImage(img, 0, 0, width, height);
                        }

                        const compressedBase64 = canvas.toDataURL('image/jpeg', 0.85);
                        resolve({
                            file: file,
                            fileName: file.name || 'مستند مالي',
                            base64: compressedBase64
                        });
                    };
                    img.onerror = () => {
                        // In case image format isn't directly supported by Canvas (or PDF), pass raw base64
                        resolve({
                            file: file,
                            fileName: file.name || 'مستند مالي',
                            base64: e.target.result
                        });
                    };
                    img.src = e.target.result;
                };
                reader.onerror = () => reject(new Error('فشل قراءة الملف'));
                reader.readAsDataURL(file);
            });
        },

        showStreamingIndicator(show, currentCount, totalCount) {
            const ind = document.getElementById('ocr-streaming-indicator');
            const badge = document.getElementById('ocr-streaming-badge');
            const text = document.getElementById('ocr-streaming-text');
            if (!ind) return;

            if (show) {
                ind.style.display = 'flex';
                if (badge) badge.textContent = `${currentCount} / ${totalCount}`;
                if (text) text.textContent = `جاري استكمال مسح بقية المستندات في الخلفية (${currentCount} من ${totalCount})... ⚡`;
            } else {
                if (badge) badge.textContent = `${totalCount} / ${totalCount}`;
                if (text) text.innerHTML = `✅ اكتمل مسح جميع المستندات (${totalCount} من ${totalCount}) بنجاح!`;
                setTimeout(() => {
                    if (ind && !this.isStreamingInBackground) {
                        ind.style.display = 'none';
                    }
                }, 4000);
            }
        },

        updateStreamingProgress(currentCount, totalCount) {
            const badge = document.getElementById('ocr-streaming-badge');
            const text = document.getElementById('ocr-streaming-text');
            if (badge) badge.textContent = `${currentCount} / ${totalCount}`;
            if (text) text.textContent = `جاري استكمال مسح بقية المستندات في الخلفية (${currentCount} من ${totalCount})... ⚡`;
        },

        // 3. Handle File Selected (Option B: First 4 immediate + remaining background streaming)
        async handleFileSelected(input) {
            const files = input.files ? Array.from(input.files) : [];
            try { input.value = ''; } catch (e) {} // Reset so selecting the same file again triggers onchange
            if (files.length === 0) return;

            const total = files.length;
            this.totalFilesCount = total;
            const initialBatchSize = Math.min(4, total);

            this.showLoading(true, `جاري مسح أول ${initialBatchSize} ${initialBatchSize === 1 ? 'مستند' : 'مستندات'} بالرؤية الحاسوبية فائقة السرعة... ⚡`, 0, initialBatchSize);

            try {
                // Step 1: Immediately compress ONLY the initial batch (up to 4) for super-fast screen opening!
                // Compressing 27 files upfront locked the browser UI thread; compressing 4 files takes only ~150ms.
                const firstFiles = files.slice(0, initialBatchSize);
                const remainingFiles = files.slice(initialBatchSize);

                const firstChunk = await Promise.all(firstFiles.map(f => this.compressImage(f)));

                // Step 2: Scan the first 4 documents with an optimized rapid stagger delay (300ms)
                let completedCount = 0;
                const scanPromises = firstChunk.map(async (item, idx) => {
                    if (idx > 0) {
                        await new Promise(r => setTimeout(r, idx * 300));
                    }
                    const extracted = await this.requestOCRData(item.base64);
                    completedCount++;
                    const percent = Math.round((completedCount / initialBatchSize) * 100);
                    this.updateLoadingProgress(percent, completedCount, initialBatchSize);
                    return {
                        id: 'doc_' + Date.now() + '_' + idx + '_' + Math.random().toString(36).substring(2, 5),
                        fileName: item.fileName,
                        base64: item.base64,
                        data: extracted,
                        originalData: JSON.parse(JSON.stringify(extracted)),
                        applied: false
                    };
                });

                const firstResults = await Promise.all(scanPromises);

                // Step 3: Immediately open the review modal for the first batch!
                this.showLoading(false);

                const isModalOpen = (document.getElementById('financial-ocr-review-modal')?.style.display === 'flex');
                if (this.batchItems && this.batchItems.length > 0 && isModalOpen) {
                    this.batchItems.push(...firstResults);
                    this.currentBatchIndex = this.batchItems.length - firstResults.length;
                } else {
                    this.batchItems = firstResults;
                    this.currentBatchIndex = 0;
                }

                if (this.batchItems.length > 0) {
                    this.selectBatchItem(this.currentBatchIndex);
                    this.playSuccessSound();

                    // Check if any initial items had unclear amounts after auto-refresh
                    const unclearInitial = firstResults.map((it, i) => it.data.is_unclear_amount ? (i + 1) : null).filter(Boolean);
                    if (unclearInitial.length > 0) {
                        this.showToast(`⚠️ تنبيه: المستند رقم #${unclearInitial.join(' و #')} المبلغ غير واضح، يرجى إدخاله يدوياً.`, 'warning');
                    } else if (total > initialBatchSize) {
                        this.showToast(`⚡ تم مسح أول ${firstResults.length} مستندات بنجاح والشاشة مفتوحة الآن، والبقية تنزل تلقائياً في الخلفية!`, 'success');
                    } else {
                        this.showToast(`⚡ تم فحص ${this.batchItems.length} ${this.batchItems.length === 1 ? 'مستند' : 'مستندات'} بنجاح!`, 'success');
                        this.checkBatchAuditAlerts();
                    }
                }

                // Step 4: Background Stream the remaining documents (5 to N) smoothly with lazy compression
                if (remainingFiles.length > 0) {
                    this.isStreamingInBackground = true;
                    this.showStreamingIndicator(true, firstResults.length, total);

                    let processedCount = firstResults.length;
                    const queue = [...remainingFiles];

                    const backgroundWorker = async () => {
                        while (queue.length > 0) {
                            const fileOrItem = queue.shift();
                            const itemIdx = total - queue.length - 1;
                            try {
                                await new Promise(r => setTimeout(r, 1100)); // Smooth 1.1s cadence
                                // Compress lazily on-demand right before sending
                                const item = fileOrItem.base64 ? fileOrItem : await this.compressImage(fileOrItem);
                                const extracted = await this.requestOCRData(item.base64);
                                const docObj = {
                                    id: 'doc_' + Date.now() + '_' + itemIdx + '_' + Math.random().toString(36).substring(2, 5),
                                    fileName: item.fileName,
                                    base64: item.base64,
                                    data: extracted,
                                    originalData: JSON.parse(JSON.stringify(extracted)),
                                    applied: false
                                };
                                this.batchItems.push(docObj);
                                processedCount++;
                                this.updateStreamingProgress(processedCount, total);
                                this.renderBatchTabs();

                                if (extracted.is_unclear_amount) {
                                    this.showToast(`⚠️ المستند رقم #${itemIdx + 1}: المبلغ غير واضح في الصورة، يرجى إدخاله يدوياً.`, 'warning');
                                }
                            } catch (itemErr) {
                                console.error('Background OCR item error:', itemErr);
                                processedCount++;
                                this.updateStreamingProgress(processedCount, total);
                            }
                        }
                    };

                    backgroundWorker().then(() => {
                        this.isStreamingInBackground = false;
                        this.showStreamingIndicator(false, total, total);
                        this.renderBatchTabs();
                        this.playSuccessSound();
                        this.showToast(`⚡ اكتمل مسح جميع الـ ${total} مستندات في الخلفية بنجاح!`, 'success');
                        this.checkBatchAuditAlerts();
                    }).catch(streamErr => {
                        this.isStreamingInBackground = false;
                        this.showStreamingIndicator(false, total, total);
                        this.renderBatchTabs();
                    });
                }
            } catch (err) {
                this.showLoading(false);
                console.error('Batch OCR Processing Error:', err);
                this.showToast('حدث خطأ أثناء معالجة المستندات: ' + err.message, 'error');
            }
        },

        // 4. Request OCR Data for a single base64 string (with auto-retry on 429 and auto-retry if amount is 0)
        async requestOCRData(base64Data, retryCount = 0, amountRetryCount = 0, isManualRefresh = false) {
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
                        doc_hint: this.activeContext.docHint || 'auto',
                        retry_amount: (amountRetryCount > 0),
                        is_refresh: isManualRefresh
                    })
                });

                const result = await response.json();

                // 1. If Gemini hit rate limit (429 or quota exceeded), automatically backoff and retry up to 3 times
                //    with exponential backoff to allow quota to recover
                if (!result.success && (result.is_quota_exceeded || response.status === 429) && retryCount < 3) {
                    const delayMs = (retryCount + 1) * (retryCount + 1) * 4000; // 4s, 16s, 36s exponential
                    console.warn(`[OCR Rate Limit] Quota exceeded. Retrying in ${delayMs}ms (attempt ${retryCount + 1}/3)...`);
                    this.showToast(`⏳ ضغط على الخادم... إعادة المحاولة تلقائياً بعد ${Math.round(delayMs/1000)} ثانية (${retryCount + 1}/3)`, 'warning');
                    await new Promise(r => setTimeout(r, delayMs));
                    return await this.requestOCRData(base64Data, retryCount + 1, amountRetryCount, isManualRefresh);
                }

                if (result.success && result.data) {
                    if (typeof result.data.amount === 'string') {
                        result.data.amount = parseFloat(result.data.amount.replace(/[^0-9.]/g, '')) || 0;
                    }

                    // 2. AUTOMATIC REFRESH IF AMOUNT IS ZERO (طلب المستخدم الصريح: إذا كان رقم صفري يعمل Refresh لوحده تلقائياً أكثر من مرة)
                    const parsedAmt = parseFloat(result.data.amount) || 0;
                    if (parsedAmt <= 0 && amountRetryCount < 2) {
                        console.warn(`[OCR Zero Amount] Amount was 0. Auto-retrying OCR scan (pass ${amountRetryCount + 1}/2)...`);
                        await new Promise(r => setTimeout(r, 1500 * (amountRetryCount + 1)));
                        return await this.requestOCRData(base64Data, retryCount, amountRetryCount + 1, isManualRefresh);
                    }

                    // If after auto-retrying amount is still zero:
                    if (parsedAmt <= 0) {
                        result.data.amount = 0.0;
                        result.data.is_unclear_amount = true;
                        result.data.unclear_msg = "المبلغ غير واضح في الصورة (يرجى إدخاله يدوياً)";
                    } else {
                        result.data.is_unclear_amount = false;
                    }

                    return result.data;
                } else {
                    const errorMsg = result.error || 'تعذر استخراج البيانات من المستند المالي';
                    const isQuotaIssue = !!(result.is_quota_exceeded || response.status === 429);

                    if (isQuotaIssue) {
                        this.showToast('⚠️ تم استنفاد حد الطلبات مؤقتاً، يرجى الانتظار ثوانٍ أو إدخال مفتاحك الخاص', 'warning');
                    }

                    // CRITICAL FIX: Distinguish API/quota errors from actual unclear documents.
                    // When API fails, mark as scan_error + is_quota_exceeded so the UI shows
                    // BLUE "retry" banner instead of GOLDEN "unclear document" banner.
                    return {
                        doc_type: (this.activeContext.docHint === 'cheque') ? 'cheque' : 'bank_transfer',
                        bank_name: '',
                        app_name: '',
                        transaction_ref: '',
                        cheque_number: '',
                        amount: 0.0,
                        is_unclear_amount: false,
                        is_quota_exceeded: isQuotaIssue,
                        date: new Date().toISOString().split('T')[0],
                        due_date: new Date().toISOString().split('T')[0],
                        sender_name: '',
                        receiver_name: '',
                        notes: '',
                        summary: errorMsg,
                        scan_error: errorMsg
                    };
                }
            } catch (e) {
                if (retryCount < 3) {
                    const backoff = (retryCount + 1) * 2500;
                    console.warn(`[OCR Network Error] Retrying in ${backoff}ms (attempt ${retryCount + 1}/3):`, e.message);
                    await new Promise(r => setTimeout(r, backoff));
                    return await this.requestOCRData(base64Data, retryCount + 1, amountRetryCount, isManualRefresh);
                }
                console.warn('OCR fetch failed after all retries, using clean empty template:', e);
                return {
                    doc_type: (this.activeContext.docHint === 'cheque') ? 'cheque' : 'bank_transfer',
                    bank_name: '',
                    app_name: '',
                    transaction_ref: '',
                    cheque_number: '',
                    amount: 0.0,
                    is_unclear_amount: false,
                    date: new Date().toISOString().split('T')[0],
                    due_date: new Date().toISOString().split('T')[0],
                    sender_name: '',
                    receiver_name: '',
                    notes: '',
                    summary: 'تعذر الاتصال بمحرك الفحص، اضغط 🔄 لإعادة المحاولة',
                    scan_error: e.message
                };
            }
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
                doc_type: 'cheque',
                bank_name: 'بنك الخرطوم',
                app_name: '',
                transaction_ref: '',
                cheque_number: 'CHQ-894215',
                amount: 250000.0,
                currency: 'SDG',
                date: new Date().toISOString().split('T')[0],
                due_date: new Date().toISOString().split('T')[0],
                sender_name: 'شركة الوادي للتجارة',
                sender_account: '284****',
                receiver_name: 'مؤسسة السجانة التجارية',
                receiver_account: '195****',
                notes: 'شيك سداد نقدي للمشتريات',
                confidence_score: 95,
                summary: 'شيك مصرفي بنك الخرطوم بمبلغ 250,000 جنيه'
            };
            this.batchItems = [{
                id: 'sim_' + Date.now(),
                fileName: 'نموذج_تجريبي.jpg',
                base64: this.currentImageBase64 || '',
                data: dataToUse,
                applied: false
            }];
            this.currentBatchIndex = 0;
            this.selectBatchItem(0);
        },

        // Backward-compatible single send
        async sendToOCR(base64Data) {
            this.showLoading(true, 'جاري تحليل المستند عبر الرؤية الحاسوبية فائقة السرعة... ⚡', 50, 100);
            const extracted = await this.requestOCRData(base64Data);
            this.showLoading(false);
            this.batchItems = [{
                id: 'doc_' + Date.now(),
                fileName: 'مستند مالي',
                base64: base64Data,
                data: extracted,
                originalData: JSON.parse(JSON.stringify(extracted)),
                applied: false
            }];
            this.originalExtractedData = JSON.parse(JSON.stringify(extracted));
            this.currentBatchIndex = 0;
            this.selectBatchItem(0);
        },

        // 5. Select & Display an Item from the Batch
        selectBatchItem(index) {
            if (!this.batchItems || !this.batchItems[index]) return;
            this.currentBatchIndex = index;
            const item = this.batchItems[index];
            if (!item.originalData && item.data) {
                item.originalData = JSON.parse(JSON.stringify(item.data));
            }
            this.currentImageBase64 = item.base64;
            this.lastExtractedData = item.data;
            this.originalExtractedData = item.originalData;
            this.previewRotation = 0;
            this.focusDataRegion();

            this.showReviewModal(item.data);
            this.updateReferenceValidationUI(item.data);
            this.renderBatchTabs();
        },

        // Reference Number Validation: Minimum 6 digits, and non-duplication of last 6 digits
        validateReference(ref, currentIndex = -1) {
            const clean = (ref || '').toString().trim();
            if (!clean) {
                return {
                    valid: false,
                    isShort: true,
                    isDuplicate: false,
                    last6: '',
                    message: 'رقم التحويلة / العملية مطلوب (يجب ألا يقل عن 6 أرقام).'
                };
            }

            // Rule 1: Length must not be less than 6 digits
            if (clean.length < 6) {
                return {
                    valid: false,
                    isShort: true,
                    isDuplicate: false,
                    last6: clean,
                    message: `⚠️ رقم التحويلة (${clean}) يتكون من ${clean.length} أرقام فقط! يجب ألا يقل عن 6 أرقام.`
                };
            }

            const last6 = clean.slice(-6);

            // Rule 2: Intra-batch duplicate check (last 6 digits must NOT duplicate within the batch)
            if (this.batchItems && this.batchItems.length > 1) {
                for (let i = 0; i < this.batchItems.length; i++) {
                    if (i === currentIndex) continue;
                    const otherRef = (this.batchItems[i]?.data?.transaction_ref || this.batchItems[i]?.data?.cheque_number || '').toString().trim();
                    if (otherRef.length >= 6 && otherRef.slice(-6) === last6) {
                        return {
                            valid: false,
                            isShort: false,
                            isDuplicate: true,
                            duplicateType: 'batch',
                            otherIndex: i,
                            last6: last6,
                            message: `⚠️ تحذير: آخر 6 أرقام (${last6}) مكررة مع المستند رقم #${i + 1} في هذه الدفعة!`
                        };
                    }
                }
            }

            // Rule 3: System-wide duplicate check (against system vouchers & saved invoices)
            try {
                if (typeof window.isBankReferenceUnique === 'function') {
                    const isUnique = window.isBankReferenceUnique(clean, 'bank');
                    if (!isUnique) {
                        return {
                            valid: false,
                            isShort: false,
                            isDuplicate: true,
                            duplicateType: 'system',
                            last6: last6,
                            message: `⚠️ تحذير: رقم التحويلة ينتهي بـ (${last6}) وهو مسجل مسبقاً في سندات النظام!`
                        };
                    }
                }

                // Check stored purchase invoices in localStorage safely
                const purchasesRaw = (typeof localStorage !== 'undefined' && localStorage.getItem) ? localStorage.getItem('erp_purchase_invoices_v1') : null;
                if (purchasesRaw) {
                    const purchases = JSON.parse(purchasesRaw);
                    if (Array.isArray(purchases)) {
                        for (const pur of purchases) {
                            if (Array.isArray(pur.bankTransfers)) {
                                for (const bt of pur.bankTransfers) {
                                    const btRef = (bt.reference || bt.transferNo || '').toString().trim();
                                    if (btRef.length >= 6 && btRef.slice(-6) === last6) {
                                        return {
                                            valid: false,
                                            isShort: false,
                                            isDuplicate: true,
                                            duplicateType: 'purchase',
                                            last6: last6,
                                            message: `⚠️ تحذير: رقم التحويلة ينتهي بـ (${last6}) ومسجل بفاتورة مشتريات #${pur.invoiceNo || ''}!`
                                        };
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (e) {
                console.warn('Reference uniqueness check error:', e);
            }

            return {
                valid: true,
                isShort: false,
                isDuplicate: false,
                last6: last6,
                message: ''
            };
        },

        // Detailed interactive explanation when user clicks on [🚨 مكرر]
        showDuplicateAlert(idx) {
            const items = this.batchItems || (this.lastExtractedData ? [{ data: this.lastExtractedData }] : []);
            if (!items[idx]) return;
            const item = items[idx];
            const refVal = (item.data.transaction_ref || item.data.cheque_number || '').trim();
            const res = this.validateReference(refVal, idx);

            // Select this card so its image and form are displayed
            this.syncCurrentItemFromForm();
            this.selectBatchItem(idx);
            this.playWarningBeep();

            // Focus and glow the reference field in the form
            const refInput = document.getElementById('ocr-field-ref-no');
            if (refInput) {
                refInput.focus();
                refInput.style.borderColor = '#ef4444';
                refInput.style.backgroundColor = 'rgba(239, 68, 68, 0.2)';
                refInput.style.boxShadow = '0 0 25px rgba(239, 68, 68, 0.9)';
            }

            const amtFormatted = (parseFloat(item.data.amount) || 0).toLocaleString('en-US');
            let alertMsg = `🚨 تفاصيل تحذير ازدواجية رقم المعاملة:\n\n`;
            alertMsg += `📄 المستند رقم: #${idx + 1} (${item.data.bank_name || 'البنك'})\n`;
            alertMsg += `💰 المبلغ المالي: ${amtFormatted} SDG\n`;
            alertMsg += `🔖 رقم التحويلة / الشيك: ${refVal}\n\n`;
            alertMsg += `⚠️ سبب التنبيه المالي:\n${res.message}\n\n`;
            alertMsg += `🛡️ الإجراءات المحاسبية المتاحة:\n`;
            alertMsg += `1. تم تحديد خانة "رقم العملية" باللون الأحمر لتعديل أي رقم ملتبس يدوياً.\n`;
            alertMsg += `2. يمكنك الضغط على زر 🔄 لإعادة قراءة الإشعار بالذكاء الاصطناعي.\n`;
            alertMsg += `3. يمكنك الضغط على ✕ لاستبعاد هذا الإشعار المكرر من الدفعة.`;

            alert(alertMsg);
        },

        // Detailed interactive explanation when user clicks on [⚠️] for golden unapproved / unclear / non-financial documents
        showGoldenAlert(idx) {
            const items = this.batchItems || (this.lastExtractedData ? [{ data: this.lastExtractedData }] : []);
            if (!items[idx]) return;
            const item = items[idx];
            const amt = parseFloat(item.data.amount) || 0;
            const isUnapproved = !!(item.data.is_unapproved || item.data.is_approved_template === false || item.data.doc_type === 'unapproved');
            const isNonFin = !!(item.data.is_non_financial || item.data.doc_type === 'non_financial');

            this.syncCurrentItemFromForm();
            this.selectBatchItem(idx);
            this.playWarningBeep();

            // Focus problematic field
            if (amt === 0) {
                const amtInput = document.getElementById('ocr-field-amount');
                if (amtInput) {
                    amtInput.focus();
                    amtInput.style.borderColor = '#f59e0b';
                    amtInput.style.boxShadow = '0 0 25px rgba(245,158,11,0.9)';
                }
            }

            let alertMsg = `⚠️ تنبيه المستند الذهبي (محظور من الاعتماد نهائياً):\n\n`;
            alertMsg += `📄 المستند رقم: #${idx + 1} (${item.data.bank_name || 'البنك'})\n\n`;
            alertMsg += `⚠️ تشخيص الذكاء الاصطناعي:\n${item.data.rejection_reason || (amt === 0 ? 'المستند غير واضح، المبلغ غير ظاهر (0.00 SDG).' : 'مستند غير معتمد في النماذج المصرفية أو صورة سيارة.')}\n\n`;
            alertMsg += `🚫 قرار صمام الأمان المحاسبي:\n`;
            alertMsg += `• زر الاعتماد لا يعتمد هذا المستند نهائياً لحماية القيود المالية ومنع إدخال بيانات غير مكتملة أو غير معتمدة.\n`;
            alertMsg += `• تم استبعاده تلقائياً من مجموع السندات المعتمدة.\n\n`;
            alertMsg += `🛡️ الإجراءات المتاحة:\n`;
            alertMsg += `1. يمكنك الضغط على ✕ لاستبعاد هذا المستند نهائياً.\n`;
            alertMsg += `2. إذا كان إشعاراً مصرفياً حقيقياً، يمكنك الضغط على 🔄 لإعادة قراءته، أو إدخال المبلغ وتصحيح البيانات يدوياً في النموذج.\n`;
            alertMsg += `3. يمكنك فحص وتكبير المستند عبر زر 🔍.`;

            alert(alertMsg);
        },

        showUnclearAlert(idx) {
            return this.showGoldenAlert(idx);
        },

        showNonFinancialAlert(idx) {
            return this.showGoldenAlert(idx);
        },

        // Rescan All Items in the Batch (Refresh All)
        async rescanAllBatchItems() {
            const items = this.batchItems;
            if (!items || items.length === 0) {
                return this.rescanCurrentItem();
            }

            if (!confirm(`هل ترغب في إعادة فحص وقراءة جميع مستندات الدفعة (${items.length} مستند) بالذكاء الاصطناعي؟`)) {
                return;
            }

            this.showLoading(true, 'جاري إعادة فحص وتدقيق كافة المستندات بالذكاء الاصطناعي...', 1, items.length);

            for (let i = 0; i < items.length; i++) {
                this.updateLoadingProgress(Math.round(((i + 1) / items.length) * 100), i + 1, items.length);
                const item = items[i];
                if (!item.base64) continue;

                try {
                    const clientKey = localStorage.getItem('gemini_api_key') || '';
                    const res = await fetch('api/ocr-financial.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            image: item.base64,
                            api_key: clientKey,
                            custom_guidelines: localStorage.getItem('erp_financial_ocr_guidelines') || '',
                            is_refresh: true
                        })
                    });
                    const json = await res.json();
                    if (json && json.success && json.data) {
                        item.data = json.data;
                        item.originalData = JSON.parse(JSON.stringify(json.data));
                    }
                } catch (err) {
                    console.error('Error rescanning item ' + (i + 1), err);
                }

                // Small delay to prevent burst
                await new Promise(r => setTimeout(r, 650));
            }

            this.showLoading(false);
            this.renderBatchTabs();
            if (this.batchItems[this.currentBatchIndex]) {
                this.populateForm(this.batchItems[this.currentBatchIndex].data);
            }
            this.playSuccessSound();
            this.showToast(`✅ تم اكتمال إعادة تدقيق كافة المستندات (${items.length}) بنجاح!`, 'success');

            // Open audit modal to show summary of results
            this.checkBatchAuditAlerts(true);
        },

        // Centered Batch Audit Alerts Modal (الرسائل التحذيرية التلخيصية الكبرى في منتصف الشاشة)
        checkBatchAuditAlerts(forceOpen = false) {
            const items = this.batchItems || (this.lastExtractedData ? [{ data: this.lastExtractedData }] : []);
            if (items.length === 0) return;

            let dupCount = 0;
            let unclearCount = 0;
            let nonFinCount = 0;
            let cleanCount = 0;

            items.forEach((item, idx) => {
                if (!item.data.bank_name || item.data.bank_name === 'غير محدد' || item.data.bank_name === 'غير متوفر') {
                    item.data.bank_name = (item.data.app_name && item.data.app_name.includes('فوري')) ? 'بنك فيصل الإسلامي' : 'بنك الخرطوم';
                }
                const refVal = (item.data.transaction_ref || item.data.cheque_number || '').trim();
                const valRes = this.validateReference(refVal, idx);
                const amt = parseFloat(item.data.amount) || 0;
                const isNonFin = !!(item.data.is_non_financial || item.data.doc_type === 'non_financial');

                if (isNonFin) {
                    nonFinCount++;
                } else if (!valRes.valid && valRes.isDuplicate) {
                    dupCount++;
                } else if (amt <= 0 || Boolean(item.data.scan_error)) {
                    unclearCount++;
                } else {
                    cleanCount++;
                }
            });

            const hasIssues = (dupCount > 0 || unclearCount > 0 || nonFinCount > 0);
            if (!hasIssues && !forceOpen) return;

            const modal = document.getElementById('financial-ocr-batch-alert-modal');
            const body = document.getElementById('ocr-batch-alert-body');
            if (!modal || !body) return;

            let cardsHtml = '';

            // 1. Clean count badge
            cardsHtml += `
                <div style="background:rgba(16,185,129,0.15); border:1.5px solid #10b981; border-radius:10px; padding:10px 14px; display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:1.3rem;">🟢</span>
                        <div>
                            <strong style="color:#34d399; font-size:0.95rem;">إشعارات سليمة ومطابقة 100% (مار طوالي):</strong>
                            <div style="color:#a7f3d0; font-size:0.8rem;">مكتملة الأرقام والمبالغ وجاهزة للحفظ والاعتماد الفوري.</div>
                        </div>
                    </div>
                    <span style="background:#10b981; color:#fff; padding:4px 12px; border-radius:20px; font-weight:900; font-size:1rem; font-family:'Consolas',monospace;">${cleanCount}</span>
                </div>
            `;

            // 2. Duplicate count badge
            if (dupCount > 0) {
                cardsHtml += `
                    <div style="background:rgba(239,68,68,0.18); border:1.5px solid #ef4444; border-radius:10px; padding:10px 14px; display:flex; align-items:center; justify-content:space-between; animation:ocrEmergencyGlow 0.9s infinite alternate;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:1.3rem;">🚨</span>
                            <div>
                                <strong style="color:#f87171; font-size:0.95rem;">تنبيه خطر: إشعارات مكررة في النظام:</strong>
                                <div style="color:#fca5a5; font-size:0.8rem;">يوجد ${dupCount} إشعار يحمل رقم معاملة مسجل مسبقاً في النظام أو متكرر بالدفعة!</div>
                            </div>
                        </div>
                        <span style="background:#ef4444; color:#fff; padding:4px 12px; border-radius:20px; font-weight:900; font-size:1rem; font-family:'Consolas',monospace;">${dupCount}</span>
                    </div>
                `;
            }

            // 3. Unclear count badge
            if (unclearCount > 0) {
                cardsHtml += `
                    <div style="background:rgba(245,158,11,0.18); border:1.5px solid #f59e0b; border-radius:10px; padding:10px 14px; display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:1.3rem;">⚠️</span>
                            <div>
                                <strong style="color:#fbbf24; font-size:0.95rem;">إشعارات غير واضحة أو المبلغ صفري:</strong>
                                <div style="color:#fde68a; font-size:0.8rem;">يوجد ${unclearCount} مستند يحتاج مراجعة لتصحيح الأرقام أو إعادة قراءة بالـ 🔄.</div>
                            </div>
                        </div>
                        <span style="background:#f59e0b; color:#fff; padding:4px 12px; border-radius:20px; font-weight:900; font-size:1rem; font-family:'Consolas',monospace;">${unclearCount}</span>
                    </div>
                `;
            }

            // 4. Non-financial count badge
            if (nonFinCount > 0) {
                cardsHtml += `
                    <div style="background:rgba(71,85,105,0.25); border:1.5px solid #94a3b8; border-radius:10px; padding:10px 14px; display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:1.3rem;">🚫</span>
                            <div>
                                <strong style="color:#cbd5e1; font-size:0.95rem;">صور ليست إشعارات مالية (مثل صورة سيارة أو مستند عام):</strong>
                                <div style="color:#94a3b8; font-size:0.8rem;">تم استبعاد ${nonFinCount} صورة تلقائياً من مجموع السندات حتى لا تعطل المحاسبة.</div>
                            </div>
                        </div>
                        <span style="background:#64748b; color:#fff; padding:4px 12px; border-radius:20px; font-weight:900; font-size:1rem; font-family:'Consolas',monospace;">${nonFinCount}</span>
                    </div>
                `;
            }

            body.innerHTML = cardsHtml;
            modal.style.display = 'flex';
        },

        closeBatchAlertModal() {
            const modal = document.getElementById('financial-ocr-batch-alert-modal');
            if (modal) modal.style.display = 'none';
        },

        // High-Alert Audio Warning for Duplicates
        playWarningBeep() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(520, ctx.currentTime);
                osc.frequency.setValueAtTime(330, ctx.currentTime + 0.12);
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.linearRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.3);
            } catch (e) {}
        },

        // Update Reference Input Border, Emergency Warning Box, and Floating AI Banners
        updateReferenceValidationUI(data) {
            const refNoInput = document.getElementById('ocr-field-ref-no');
            const warningBox = document.getElementById('ocr-ref-warning-box');
            const warningText = document.getElementById('ocr-ref-warning-text');
            const applyBtn = document.getElementById('btn-apply-financial-ocr');
            const applyText = document.getElementById('btn-apply-text');
            const applySingleBtn = document.getElementById('btn-apply-single-financial-ocr');
            const applySingleText = document.getElementById('btn-apply-single-text');

            // Floating Preview Banner elements
            const previewBanner = document.getElementById('ocr-preview-status-banner');
            const previewIcon = document.getElementById('ocr-preview-status-icon');
            const previewTitle = document.getElementById('ocr-preview-status-title');
            const previewDesc = document.getElementById('ocr-preview-status-desc');
            const previewBadge = document.getElementById('ocr-preview-status-badge');

            // Form Diagnostic Box elements
            const formReasonBox = document.getElementById('ocr-ai-reason-box');
            const formReasonIcon = document.getElementById('ocr-ai-reason-icon');
            const formReasonTitle = document.getElementById('ocr-ai-reason-title');
            const formReasonDesc = document.getElementById('ocr-ai-reason-desc');
            const formReasonBadge = document.getElementById('ocr-ai-reason-badge');

            if (!refNoInput) return;

            if (!data.bank_name || data.bank_name === 'غير محدد' || data.bank_name === 'غير متوفر') {
                data.bank_name = (data.app_name && data.app_name.includes('فوري')) ? 'بنك فيصل الإسلامي' : 'بنك الخرطوم';
            }
            const refVal = (data.transaction_ref || data.cheque_number || refNoInput.value || '').trim();
            const res = this.validateReference(refVal, this.currentBatchIndex);
            const amt = parseFloat(data.amount) || 0;
            const isAmtZero = (amt <= 0);
            const isNonFin = !!(data.is_non_financial || data.doc_type === 'non_financial');
            const isDuplicate = !isNonFin && (!res.valid && res.isDuplicate);
            const hasApiError = Boolean(data.scan_error || data.is_quota_exceeded);
            const isGolden = !isDuplicate && (isNonFin || isAmtZero || hasApiError);

            // Compute total clean documents in current batch
            let cleanCount = 0;
            if (this.batchItems && this.batchItems.length > 0) {
                this.batchItems.forEach((it, i) => {
                    const d = it.data;
                    const isNF = !!(d.is_non_financial || d.doc_type === 'non_financial');
                    const a = parseFloat(d.amount) || 0;
                    const r = (d.transaction_ref || d.cheque_number || '').trim();
                    const v = this.validateReference(r, i);
                    if (!isNF && a > 0 && !v.isDuplicate) {
                        cleanCount++;
                    }
                });
            } else if (!isGolden && !isDuplicate) {
                cleanCount = 1;
            }

            // Background streaming lock check
            if (this.isStreamingInBackground) {
                if (applyBtn) {
                    applyBtn.disabled = true;
                    applyBtn.style.background = 'linear-gradient(135deg, #334155, #1e293b)';
                    applyBtn.style.boxShadow = 'none';
                    applyBtn.style.cursor = 'not-allowed';
                    applyBtn.style.border = '1.5px solid #64748b';
                }
                if (applyText) {
                    applyText.innerHTML = `⏳ جاري فحص بقية المستندات (${this.batchItems ? this.batchItems.length : 1} من ${this.totalFilesCount || '...'}) - تم قفل الاعتماد`;
                }
                if (applySingleBtn) {
                    applySingleBtn.disabled = true;
                    applySingleBtn.style.cursor = 'not-allowed';
                    applySingleBtn.style.opacity = '0.5';
                }
                return;
            }

            // 1. Golden Theme: Unapproved in models, Non-financial (car image), Unclear, or Zero Amount
            // "يكون الإشعار باللون الذهبي، ويكتب تنبيه: المستند غير واضح، المبلغ غير ظاهر، أو المبلغ السيارة كذا كذا كذا، وزر اعتماد لا يعتمد، أي لا يعتمد في هذه الحالة نهائيا."
            if (isGolden) {
                if (warningBox) warningBox.style.display = 'none';
                refNoInput.style.borderColor = '#f59e0b';
                refNoInput.style.backgroundColor = '#1e293b';
                refNoInput.style.boxShadow = '0 0 14px rgba(245,158,11,0.35)';

                const isApiErr = Boolean(data.scan_error || data.is_quota_exceeded);
                const alertReason = isApiErr 
                    ? 'تعذر الاتصال بالذكاء الاصطناعي (اضغط 🔄 لإعادة الفحص)'
                    : (data.rejection_reason || (isAmtZero ? 'المستند غير واضح، المبلغ غير ظاهر (0.00 SDG)' : 'مستند غير معتمد في النماذج المصرفية أو صورة سيارة'));

                if (previewBanner) {
                    previewBanner.style.display = 'flex';
                    previewBanner.style.background = isApiErr 
                        ? 'linear-gradient(135deg, rgba(2,132,199,0.92), rgba(3,105,161,0.96))'
                        : 'linear-gradient(135deg, rgba(180,83,9,0.95), rgba(120,53,15,0.98))';
                    previewBanner.style.border = isApiErr ? '1.5px solid #38bdf8' : '1.5px solid #f59e0b';
                    previewBanner.style.color = '#fef3c7';
                    if (previewIcon) previewIcon.textContent = isApiErr ? '🔄' : '⚠️';
                    if (previewTitle) previewTitle.textContent = `تنبيه: ${alertReason}`;
                    if (previewDesc) previewDesc.textContent = isApiErr 
                        ? 'اضغط زر [🔄 فحص] في الأعلى لقراءة المستند فوراً أو أدخل المبلغ يدوياً.' 
                        : `${alertReason} - زر الاعتماد لا يعتمد نهائياً لحماية الحسابات.`;
                    if (previewBadge) {
                        previewBadge.textContent = isApiErr ? 'إعادة فحص 🔄' : 'محظور من الاعتماد ⚠️';
                        previewBadge.style.background = isApiErr ? '#0284c7' : '#b45309';
                        previewBadge.style.color = '#fff';
                    }
                }

                if (formReasonBox) {
                    formReasonBox.style.display = 'block';
                    formReasonBox.style.background = isApiErr ? 'rgba(2,132,199,0.2)' : 'rgba(180,83,9,0.3)';
                    formReasonBox.style.border = isApiErr ? '1.5px solid #38bdf8' : '1.5px solid #f59e0b';
                    formReasonBox.style.color = isApiErr ? '#e0f2fe' : '#fef3c7';
                    if (formReasonIcon) formReasonIcon.textContent = isApiErr ? '🔄' : '⚠️';
                    if (formReasonTitle) formReasonTitle.textContent = isApiErr ? 'تنبيه: تعذر فحص المستند مؤقتاً' : 'تنبيه أمني: مستند غير معتمد أو غير واضح';
                    if (formReasonBadge) {
                        formReasonBadge.textContent = isApiErr ? 'إعادة فحص 🔄' : 'محظور نهائياً';
                        formReasonBadge.style.background = isApiErr ? '#0284c7' : '#d97706';
                        formReasonBadge.style.color = '#fff';
                    }
                    if (formReasonDesc) formReasonDesc.textContent = isApiErr
                        ? 'تعذر قراءة هذا المستند بسبب ضغط الطلبات. اضغط على زر [🔄 فحص] في الشريط العلوي لإعادة قراءته فوراً، أو أدخل المبلغ يدوياً.'
                        : `تشخيص الذكاء الاصطناعي: ${alertReason}. زر الاعتماد لا يعتمد هذا المستند نهائياً لحماية القيود المحاسبية. يرجى استبعاده أو تصحيح المبلغ وبيانات التحويل يدوياً.`;
                }

                // Strictly lock and disable single approval button
                if (applySingleBtn) {
                    applySingleBtn.disabled = true;
                    applySingleBtn.style.cursor = 'not-allowed';
                    if (isApiErr) {
                        // API error: blue theme with retry message
                        applySingleBtn.style.background = 'linear-gradient(135deg, #0c4a6e, #082f49)';
                        applySingleBtn.style.border = '1.5px solid #38bdf8';
                        applySingleBtn.style.color = '#e0f2fe';
                    } else {
                        // Unclear/unapproved: amber theme
                        applySingleBtn.style.background = 'linear-gradient(135deg, #78350f, #451a03)';
                        applySingleBtn.style.border = '1.5px solid #d97706';
                        applySingleBtn.style.color = '#fef3c7';
                    }
                    applySingleBtn.style.boxShadow = 'none';
                    applySingleBtn.style.opacity = '0.9';
                }
                if (applySingleText) {
                    applySingleText.textContent = isApiErr 
                        ? '🔄 تعذر الفحص مؤقتاً — اضغط فحص أعلاه لإعادة المحاولة'
                        : '⚠️ محظور من الاعتماد (غير معتمد / ناقص)';
                }

                // Batch Save Button State
                if (cleanCount === 0) {
                    if (applyBtn) {
                        applyBtn.disabled = true;
                        applyBtn.style.cursor = 'not-allowed';
                        applyBtn.style.background = 'linear-gradient(135deg, #78350f, #451a03)';
                        applyBtn.style.border = '1.5px solid #d97706';
                        applyBtn.style.color = '#fef3c7';
                        applyBtn.style.boxShadow = 'none';
                        applyBtn.style.opacity = '0.85';
                    }
                    if (applyText) applyText.textContent = '⚠️ زر الاعتماد لا يعتمد (لا توجد سندات معتمدة)';
                } else {
                    if (applyBtn) {
                        applyBtn.disabled = false;
                        applyBtn.style.cursor = 'pointer';
                        applyBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                        applyBtn.style.border = 'none';
                        applyBtn.style.boxShadow = '0 4px 18px rgba(16,185,129,0.4)';
                        applyBtn.style.opacity = '1';
                    }
                    if (applyText) applyText.textContent = `حفظ الاعتمادات (${cleanCount} سند)`;
                }
                return;
            }

            // 2. Duplicate Document
            if (isDuplicate) {
                if (warningBox) {
                    warningBox.style.display = 'flex';
                }
                if (warningText) {
                    warningText.textContent = res.message;
                }
                refNoInput.style.borderColor = '#ef4444';
                refNoInput.style.backgroundColor = 'rgba(239, 68, 68, 0.15)';
                refNoInput.style.boxShadow = '0 0 20px rgba(239, 68, 68, 0.7)';

                if (previewBanner) {
                    previewBanner.style.display = 'flex';
                    previewBanner.style.background = 'linear-gradient(135deg, rgba(220,38,38,0.95), rgba(153,27,27,0.98))';
                    previewBanner.style.border = '1.5px solid #ef4444';
                    previewBanner.style.color = '#fff';
                    if (previewIcon) previewIcon.textContent = '🚨';
                    if (previewTitle) previewTitle.textContent = 'تحذير مالي: رقم المعاملة مكرر مسجل مسبقاً!';
                    if (previewDesc) previewDesc.textContent = `${res.message} - محظور من الحفظ لحماية الحسابات.`;
                    if (previewBadge) {
                        previewBadge.textContent = 'مكرر محظور 🚨';
                        previewBadge.style.background = '#b91c1c';
                        previewBadge.style.color = '#fff';
                    }
                }

                if (formReasonBox) {
                    formReasonBox.style.display = 'block';
                    formReasonBox.style.background = 'rgba(220,38,38,0.3)';
                    formReasonBox.style.border = '1.5px solid #ef4444';
                    formReasonBox.style.color = '#fecaca';
                    if (formReasonIcon) formReasonIcon.textContent = '🚨';
                    if (formReasonTitle) formReasonTitle.textContent = 'تحذير ازدواجية رقم التحويلة';
                    if (formReasonBadge) {
                        formReasonBadge.textContent = 'مكرر بالنظام';
                        formReasonBadge.style.background = '#dc2626';
                        formReasonBadge.style.color = '#fff';
                    }
                    if (formReasonDesc) formReasonDesc.textContent = `${res.message} - سيتم استبعاد هذا السند تلقائياً عند الضغط على "حفظ الاعتمادات" منعاً لتكرار الصرف.`;
                }

                // Strictly lock and disable single approval button
                if (applySingleBtn) {
                    applySingleBtn.disabled = true;
                    applySingleBtn.style.cursor = 'not-allowed';
                    applySingleBtn.style.background = 'linear-gradient(135deg, #7f1d1d, #450a0a)';
                    applySingleBtn.style.border = '1.5px solid #ef4444';
                    applySingleBtn.style.color = '#fee2e2';
                    applySingleBtn.style.boxShadow = 'none';
                    applySingleBtn.style.opacity = '0.9';
                }
                if (applySingleText) {
                    applySingleText.textContent = '🚨 محظور: مكرر مسجل مسبقاً';
                }

                this.playWarningBeep();
                if (cleanCount === 0) {
                    if (applyBtn) {
                        applyBtn.disabled = true;
                        applyBtn.style.cursor = 'not-allowed';
                        applyBtn.style.background = 'linear-gradient(135deg, #78350f, #451a03)';
                        applyBtn.style.border = '1.5px solid #d97706';
                        applyBtn.style.color = '#fef3c7';
                        applyBtn.style.boxShadow = 'none';
                        applyBtn.style.opacity = '0.85';
                    }
                    if (applyText) applyText.textContent = '⚠️ زر الاعتماد لا يعتمد (لا توجد سندات معتمدة)';
                } else {
                    if (applyBtn) {
                        applyBtn.disabled = false;
                        applyBtn.style.cursor = 'pointer';
                        applyBtn.style.background = 'linear-gradient(135deg, #059669 0%, #10b981 50%, #047857 100%)';
                        applyBtn.style.border = 'none';
                        applyBtn.style.boxShadow = '0 4px 18px rgba(16,185,129,0.45)';
                        applyBtn.style.opacity = '1';
                    }
                    if (applyText) applyText.textContent = `حفظ الاعتمادات (${cleanCount} سند)`;
                }
                return;
            }

            // 3. Clean Document (سليم 100% مار طوالي)
            if (warningBox) warningBox.style.display = 'none';
            refNoInput.style.borderColor = '#38bdf8';
            refNoInput.style.backgroundColor = '#1e293b';
            refNoInput.style.boxShadow = 'none';

            if (previewBanner) {
                previewBanner.style.display = 'flex';
                previewBanner.style.background = 'linear-gradient(135deg, rgba(6,78,59,0.92), rgba(4,120,87,0.95))';
                previewBanner.style.border = '1.5px solid #10b981';
                previewBanner.style.color = '#ecfdf5';
                if (previewIcon) previewIcon.textContent = '✓';
                if (previewTitle) previewTitle.textContent = 'إشعار مالي سليم ومكتمل بنسبة 100%';
                if (previewDesc) previewDesc.textContent = 'جميع البيانات مطابقة ومحققة وجاهزة للاعتماد الفوري (مار طوالي).';
                if (previewBadge) {
                    previewBadge.textContent = 'سليم 100% ✓';
                    previewBadge.style.background = '#059669';
                    previewBadge.style.color = '#fff';
                }
            }

            if (formReasonBox) {
                formReasonBox.style.display = 'none';
            }

            // Enable single approval button
            if (applySingleBtn) {
                applySingleBtn.disabled = false;
                applySingleBtn.style.cursor = 'pointer';
                applySingleBtn.style.background = 'linear-gradient(135deg, rgba(14,165,233,0.35), rgba(2,132,199,0.5))';
                applySingleBtn.style.border = '1.5px solid #38bdf8';
                applySingleBtn.style.color = '#ffffff';
                applySingleBtn.style.boxShadow = '0 2px 10px rgba(56,189,248,0.25)';
                applySingleBtn.style.opacity = '1';
            }
            if (applySingleText) {
                applySingleText.textContent = 'حفظ هذا السند فقط';
            }

            // Batch save button enabled
            if (applyBtn) {
                applyBtn.disabled = false;
                applyBtn.style.cursor = 'pointer';
                applyBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                applyBtn.style.border = 'none';
                applyBtn.style.boxShadow = '0 4px 18px rgba(16,185,129,0.4)';
                applyBtn.style.opacity = '1';
            }
            if (applyText) {
                applyText.textContent = `حفظ الاعتمادات (${cleanCount} سند)`;
            }
        },

        // Sync inputs back to active batch item whenever modified
        syncCurrentItemFromForm() {
            if (!this.batchItems || !this.batchItems[this.currentBatchIndex]) return;
            const item = this.batchItems[this.currentBatchIndex];
            const data = item.data;

            const docTypeSelect = document.getElementById('ocr-field-doc-type');
            if (docTypeSelect) data.doc_type = docTypeSelect.value;

            const amountInput = document.getElementById('ocr-field-amount');
            if (amountInput) {
                const cleanAmt = parseFloat((amountInput.value || '').replace(/[^0-9.]/g, '')) || 0;
                data.amount = cleanAmt;
                if (cleanAmt > 0) data.is_unclear_amount = false;
            }

            const bankNameInput = document.getElementById('ocr-field-bank-name');
            if (bankNameInput) data.bank_name = bankNameInput.value.trim();

            const refNoInput = document.getElementById('ocr-field-ref-no');
            if (refNoInput) {
                data.transaction_ref = refNoInput.value.trim();
                data.cheque_number = refNoInput.value.trim();
            }

            const dateInput = document.getElementById('ocr-field-date');
            if (dateInput) data.date = dateInput.value;

            const dueDateInput = document.getElementById('ocr-field-due-date');
            if (dueDateInput) data.due_date = dueDateInput.value;

            const senderAccInput = document.getElementById('ocr-field-sender-acc');
            if (senderAccInput) data.sender_account = senderAccInput.value.trim();

            const receiverAccInput = document.getElementById('ocr-field-receiver-acc');
            if (receiverAccInput) data.receiver_account = receiverAccInput.value.trim();

            const senderInput = document.getElementById('ocr-field-sender');
            if (senderInput) data.sender_name = senderInput.value.trim();

            const receiverInput = document.getElementById('ocr-field-receiver');
            if (receiverInput) data.receiver_name = receiverInput.value.trim();

            const phoneInput = document.getElementById('ocr-field-phone');
            if (phoneInput) {
                let pVal = phoneInput.value.trim();
                if (/^(n\/?a|null|none|غير\s*متوفر|لا\s*يوجد|-)$/i.test(pVal)) pVal = '';
                data.receiver_phone = pVal;
            }

            const notesInput = document.getElementById('ocr-field-notes');
            if (notesInput) {
                let nVal = notesInput.value.trim();
                if (/^(n\/?a|null|none|-)$/i.test(nVal)) nVal = '';
                data.notes = nVal;
            }

            this.updateReferenceValidationUI(data);
            this.renderBatchTabs();
        },

        // Delete Current Scanned Document from the batch
        deleteCurrentItem() {
            if (!this.batchItems || this.batchItems.length === 0) return;
            
            this.batchItems.splice(this.currentBatchIndex, 1);
            if (this.batchItems.length === 0) {
                this.closeReviewModal();
                this.showToast('تم إفراغ قائمة المستندات الممسوحة', 'info');
                return;
            }

            this.currentBatchIndex = Math.max(0, Math.min(this.currentBatchIndex, this.batchItems.length - 1));
            this.selectBatchItem(this.currentBatchIndex);
            this.showToast('تم استبعاد المستند بنجاح', 'info');
        },

        // Dedicated Action: Purge All Duplicate Items from the Batch (استبعاد المكرر بنقرة واحدة)
        purgeDuplicateItems() {
            if (!this.batchItems || this.batchItems.length === 0) {
                this.showToast('لا توجد مستندات في الدفعة', 'info');
                return;
            }

            const originalCount = this.batchItems.length;
            const cleanItems = [];
            const removedItems = [];

            this.batchItems.forEach((item, idx) => {
                const isNonFin = !!(item.data.is_non_financial || item.data.doc_type === 'non_financial');
                const ref = (item.data.transaction_ref || item.data.cheque_number || '').trim();
                const res = this.validateReference(ref, idx);
                const isDup = !isNonFin && (!res.valid && res.isDuplicate);
                if (isDup) {
                    removedItems.push(item);
                } else {
                    cleanItems.push(item);
                }
            });

            if (removedItems.length === 0) {
                this.playSuccessSound();
                this.showToast('✅ لا توجد أي إشعارات مكررة في الدفعة الحالية، جميع السندات سليمة!', 'success');
                return;
            }

            this.batchItems = cleanItems;
            if (this.batchItems.length === 0) {
                this.closeReviewModal();
                this.showToast(`🚨 تم استبعاد كافة الإشعارات (${originalCount}) لأنها كانت جميعها مكررة!`, 'warning');
                return;
            }

            this.currentBatchIndex = 0;
            this.selectBatchItem(0);
            this.renderBatchTabs();
            this.playSuccessSound();
            this.showToast(`🚨 تم استبعاد (${removedItems.length}) إشعار مكرر بنجاح! الدفعة الآن خالية تماماً من التكرار.`, 'success');
        },

        // Dedicated Action: Purge All Items from the Batch (استبعاد الكل والبدء من جديد)
        purgeAllItems() {
            const count = (this.batchItems && this.batchItems.length) || 1;
            if (!confirm(`هل ترغب في استبعاد وإلغاء كافة مستندات هذه الدفعة (${count} مستند) بالكامل والبدء من جديد؟`)) {
                return;
            }
            this.batchItems = [];
            this.currentBatchIndex = 0;
            this.closeReviewModal();
            this.showToast('🗑️ تم استبعاد وإلغاء الدفعة بالكامل', 'info');
        },

        // Re-scan a specific item by its batch index (Instant Refresh for any notice/cheque card)
        async rescanItemByIndex(idx) {
            if (!this.batchItems || !this.batchItems[idx]) return;

            // If user clicked refresh on a different card, switch preview and form to that card first
            if (this.currentBatchIndex !== idx) {
                this.syncCurrentItemFromForm();
                this.selectBatchItem(idx);
            }

            const item = this.batchItems[idx];
            const cardRefreshBtn = document.getElementById(`card-refresh-btn-${idx}`);
            const toolbarBtn = document.getElementById('btn-rescan-current-ocr');
            const toolbarIcon = document.getElementById('rescan-icon');
            const toolbarText = document.getElementById('rescan-text');
            const inlineAmtBtn = document.getElementById('btn-inline-rescan-amount');
            const inlineRefBtn = document.getElementById('btn-inline-rescan-ref');

            if (cardRefreshBtn) {
                cardRefreshBtn.innerHTML = '<span>⏳</span> <span style="font-size:0.62rem; font-weight:900;">جاري القراءة...</span>';
                cardRefreshBtn.style.pointerEvents = 'none';
            }
            if (toolbarBtn) {
                toolbarBtn.disabled = true;
                toolbarBtn.style.opacity = '0.7';
            }
            if (toolbarIcon) toolbarIcon.innerHTML = '⏳';
            if (toolbarText) toolbarText.textContent = 'جاري القراءة...';
            if (inlineAmtBtn) inlineAmtBtn.innerHTML = '<span>⏳</span> جاري...';
            if (inlineRefBtn) inlineRefBtn.innerHTML = '<span>⏳</span> جاري...';

            this.showToast(`🔄 جاري إعادة قراءة بيانات المستند #${idx + 1} بالذكاء الاصطناعي...`, 'info');

            try {
                const extracted = await this.requestOCRData(item.base64, 0, 0, true);
                item.data = extracted;
                item.originalData = JSON.parse(JSON.stringify(extracted));
                this.lastExtractedData = extracted;
                this.originalExtractedData = item.originalData;
                this.showReviewModal(extracted);
                this.updateReferenceValidationUI(extracted);
                this.renderBatchTabs();
                this.playSuccessSound();

                const newAmt = (parseFloat(extracted.amount) || 0).toLocaleString('en-US');
                const newRef = extracted.transaction_ref || extracted.cheque_number || '---';
                this.showToast(`✅ تمت إعادة قراءة المستند #${idx + 1} بنجاح! المبلغ: ${newAmt} SDG | الرقم: ${newRef}`, 'success');
            } catch (err) {
                console.error('Item Rescan Error:', err);
                this.showToast(`تعذر إعادة قراءة المستند #${idx + 1}: ` + err.message, 'error');
            } finally {
                if (toolbarBtn) {
                    toolbarBtn.disabled = false;
                    toolbarBtn.style.opacity = '1';
                }
                if (toolbarIcon) toolbarIcon.innerHTML = '🔄';
                if (toolbarText) toolbarText.textContent = 'إعادة فحص';
                if (inlineAmtBtn) inlineAmtBtn.innerHTML = '<span>🔄</span> Refresh للمبلغ';
                if (inlineRefBtn) inlineRefBtn.innerHTML = '<span>🔄</span> قراءة ثانية';
                this.renderBatchTabs();
            }
        },

        // Re-scan Current Document with AI
        rescanCurrentItem() {
            return this.rescanItemByIndex(this.currentBatchIndex);
        },

        // Render Batch Navigation Tabs / Pills
        renderBatchTabs() {
            const container = document.getElementById('ocr-batch-container');
            const tabsList = document.getElementById('ocr-batch-tabs');
            const counterBadge = document.getElementById('ocr-batch-counter-badge');
            const sumBadge = document.getElementById('ocr-batch-sum-badge');
            const deleteBtn = document.getElementById('btn-delete-current-ocr-item');
            const applySingleBtn = document.getElementById('btn-apply-single-financial-ocr');
            const applyBtn = document.getElementById('btn-apply-financial-ocr');
            const applyText = document.getElementById('btn-apply-text');
            const indexBadge = document.getElementById('ocr-current-doc-index-badge');

            // Calculate total sum of all items (exclude non-financial images)
            let totalAmount = 0;
            if (this.batchItems && this.batchItems.length > 0) {
                this.batchItems.forEach(it => {
                    const isNonFin = !!(it.data.is_non_financial || it.data.doc_type === 'non_financial');
                    if (!isNonFin) {
                        totalAmount += (parseFloat(it.data.amount) || 0);
                    }
                });
            } else if (this.lastExtractedData) {
                const isNonFin = !!(this.lastExtractedData.is_non_financial || this.lastExtractedData.doc_type === 'non_financial');
                if (!isNonFin) {
                    totalAmount = parseFloat(this.lastExtractedData.amount) || 0;
                }
            }

            if (sumBadge) sumBadge.textContent = `${Math.round(totalAmount).toLocaleString('en-US')} SDG`;

            let itemsCount = (this.batchItems && this.batchItems.length > 0) ? this.batchItems.length : 1;

            if (indexBadge) {
                indexBadge.textContent = `${this.currentBatchIndex + 1} / ${itemsCount}`;
            }

            const singleActionsBar = document.getElementById('ocr-single-actions-bar');
            if (container) container.style.display = 'flex';
            if (deleteBtn) deleteBtn.style.display = (itemsCount > 1) ? 'flex' : 'none';
            if (applySingleBtn) applySingleBtn.style.display = (itemsCount > 1) ? 'flex' : 'none';
            if (singleActionsBar) singleActionsBar.style.display = (itemsCount > 1) ? 'flex' : 'none';

            // Compute total clean documents in current batch
            let cleanCount = 0;
            if (this.batchItems && this.batchItems.length > 0) {
                this.batchItems.forEach((it, i) => {
                    const d = it.data;
                    const isNF = !!(d.is_non_financial || d.doc_type === 'non_financial');
                    const a = parseFloat(d.amount) || 0;
                    const r = (d.transaction_ref || d.cheque_number || '').trim();
                    const v = this.validateReference(r, i);
                    if (!isNF && a > 0 && !v.isDuplicate) {
                        cleanCount++;
                    }
                });
            } else if (this.lastExtractedData) {
                const d = this.lastExtractedData;
                const isNF = !!(d.is_non_financial || d.doc_type === 'non_financial');
                const a = parseFloat(d.amount) || 0;
                const r = (d.transaction_ref || d.cheque_number || '').trim();
                const v = this.validateReference(r, 0);
                if (!isNF && a > 0 && !v.isDuplicate) cleanCount = 1;
            }

            // -------------------------------------------------------------
            // Accounting Lock Enforcement (صمام الأمان المحاسبي لمنع الاعتماد الناقص)
            // -------------------------------------------------------------
            if (this.isStreamingInBackground) {
                if (counterBadge) {
                    counterBadge.innerHTML = `<span>⏳ إجمالي السندات (${itemsCount} من ${this.totalFilesCount || itemsCount} سند):</span>`;
                }
                if (applyBtn) {
                    applyBtn.disabled = true;
                    applyBtn.style.background = 'linear-gradient(135deg, #334155, #1e293b)';
                    applyBtn.style.border = '1.5px solid #64748b';
                    applyBtn.style.boxShadow = 'none';
                    applyBtn.style.cursor = 'not-allowed';
                    applyBtn.style.opacity = '0.92';
                }
                if (applySingleBtn) {
                    applySingleBtn.disabled = true;
                    applySingleBtn.style.cursor = 'not-allowed';
                    applySingleBtn.style.opacity = '0.5';
                }
                if (applyText) {
                    applyText.innerHTML = `⏳ جاري فحص بقية المستندات (${itemsCount} من ${this.totalFilesCount || itemsCount})... تم قفل الاعتماد لحماية القيد`;
                }
            } else {
                if (counterBadge) {
                    counterBadge.textContent = `إجمالي السندات (${itemsCount} سند):`;
                }
                if (cleanCount === 0) {
                    // All documents are unapproved, unclear/zero, or duplicate -> Block approval button completely!
                    if (applyBtn) {
                        applyBtn.disabled = true;
                        applyBtn.style.cursor = 'not-allowed';
                        applyBtn.style.opacity = '0.85';
                        applyBtn.style.border = '1.5px solid #d97706';
                        applyBtn.style.background = 'linear-gradient(135deg, #78350f, #451a03)';
                        applyBtn.style.boxShadow = 'none';
                    }
                    if (applyText) {
                        applyText.textContent = '⚠️ زر الاعتماد لا يعتمد (لا توجد سندات معتمدة)';
                    }
                } else {
                    if (applyBtn) {
                        applyBtn.disabled = false;
                        applyBtn.style.cursor = 'pointer';
                        applyBtn.style.opacity = '1';
                        applyBtn.style.border = 'none';
                        applyBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                        applyBtn.style.boxShadow = '0 4px 18px rgba(16,185,129,0.4)';
                    }
                    if (applyText) {
                        applyText.textContent = 'حفظ الاعتمادات';
                    }
                }
            }

            const displayItems = (this.batchItems && this.batchItems.length > 0)
                ? this.batchItems
                : (this.lastExtractedData ? [{ base64: this.currentImageBase64, data: this.lastExtractedData }] : []);

            if (tabsList) {
                tabsList.innerHTML = '';
                displayItems.forEach((item, idx) => {
                    if (!item.data.bank_name || item.data.bank_name === 'غير محدد' || item.data.bank_name === 'غير متوفر') {
                        item.data.bank_name = (item.data.app_name && item.data.app_name.includes('فوري')) ? 'بنك فيصل الإسلامي' : 'بنك الخرطوم';
                    }
                    const isSelected = (idx === this.currentBatchIndex);
                    const isCheque = (item.data.doc_type === 'cheque');
                    const amt = (parseFloat(item.data.amount) || 0);
                    const isAmtZero = (amt <= 0);
                    const isNonFinancial = !!(item.data.is_non_financial || item.data.doc_type === 'non_financial');

                    const refVal = (item.data.transaction_ref || item.data.cheque_number || '').trim();
                    const valRes = this.validateReference(refVal, idx);

                    // 1. Classification into distinct functional tiers:
                    // - Tier 1: Duplicate (خطر الازدواجية) -> Deep Crimson Red + Hazard Lamp 🚨
                    // - Tier 2: Golden Warning -> Non-Financial / Zero Amount (غير واضح أو المبلغ غير ظاهر) / Scan Error -> Amber Golden + Warning Lamp ⚠️
                    // - Tier 3: Clean/Valid (سليم 100% مار طوالي) -> Emerald Green + Checkmark ✓
                    const isDuplicate = !isNonFinancial && (!valRes.valid && valRes.isDuplicate);
                    const isGolden = !isDuplicate && (isNonFinancial || isAmtZero || Boolean(item.data.scan_error));
                    const isClean = !isDuplicate && !isGolden;

                    // Status Indicator: Square hazard lamp / golden warning button / clean checkmark badge
                    // وضع علامة صح على العلامة الملونة وإنزالها إلى تحت قليلاً
                    let statusSquareBtnHtml = '';
                    if (isDuplicate) {
                        // Square Hazard Light Button (مربع لمبة الخطر المضيئة - بدون أي كلمة "مكرر")
                        statusSquareBtnHtml = `
                            <button type="button" 
                                    onclick="event.stopPropagation(); FinancialOCR.showDuplicateAlert(${idx})" 
                                    style="width:22px; height:22px; min-width:22px; border-radius:5px; 
                                           background:linear-gradient(135deg, #ef4444, #991b1b); 
                                           border:1.2px solid #fca5a5; 
                                           color:#ffffff; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; 
                                           font-size:0.90rem; padding:0; line-height:1; margin-top:2px; 
                                           box-shadow:0 0 10px rgba(239,68,68,0.95); animation:ocrEmergencyGlow 0.8s infinite alternate;" 
                                    title="⚠️ تنبيه خطر: رقم المعاملة موجود مسبقاً! انقر لعرض التفاصيل">
                                🚨
                            </button>
                        `;
                    } else if (isGolden) {
                        // Square Golden Warning Button (مربع التنبيه الذهبي للمستندات غير المعتمدة أو غير الواضحة أو صور السيارات)
                        statusSquareBtnHtml = `
                            <button type="button" 
                                    onclick="event.stopPropagation(); FinancialOCR.showUnclearAlert(${idx})" 
                                    style="width:22px; height:22px; min-width:22px; border-radius:5px; 
                                           background:linear-gradient(135deg, #f59e0b, #b45309); 
                                           border:1.2px solid #fde68a; 
                                           color:#ffffff; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; 
                                           font-size:0.85rem; padding:0; line-height:1; margin-top:2px; 
                                           box-shadow:0 0 8px rgba(245,158,11,0.85); animation:ocrWarningPulse 1.1s infinite alternate;" 
                                    title="⚠️ تنبيه 🚫: مستند غير معتمد في النماذج أو غير واضح أو صورة سيارة (محظور من الاعتماد نهائياً)">
                                ⚠️
                            </button>
                        `;
                    } else {
                        // Clean Document Square Badge (سليم ومكتمل 100% مار طوالي - علامة صح بيضاء واضحة جداً ومنزلة قليلاً)
                        statusSquareBtnHtml = `
                            <span style="width:22px; height:22px; min-width:22px; border-radius:5px; 
                                         background:linear-gradient(135deg, #10b981, #059669); border:1.2px solid #6ee7b7; 
                                         color:#ffffff; display:inline-flex; align-items:center; justify-content:center; 
                                         font-size:0.95rem; font-weight:900; line-height:1; margin-top:2px; 
                                         box-shadow:0 0 6px rgba(16,185,129,0.5);" 
                                  title="✓ مستند سليم ومكتمل 100% (مار طوالي)">
                                ✓
                            </span>
                        `;
                    }

                    // Individual Square Refresh Button - ملموم ومصغر وبداخله أيقونة صغيرة
                    const refreshBtnHtml = `
                        <button type="button" 
                                onclick="event.stopPropagation(); FinancialOCR.rescanItemByIndex(${idx})" 
                                id="card-refresh-btn-${idx}" 
                                style="width:24px; height:24px; min-width:24px; border-radius:5px; 
                                       background:${isAmtZero ? 'linear-gradient(135deg, rgba(245,158,11,0.5), rgba(217,119,6,0.7))' : 'rgba(56,189,248,0.22)'}; 
                                       border:1.2px solid ${isAmtZero ? '#fbbf24' : 'rgba(56,189,248,0.5)'}; 
                                       color:${isAmtZero ? '#fef08a' : '#7dd3fc'}; 
                                       cursor:pointer; display:inline-flex; align-items:center; justify-content:center; 
                                       font-size:0.78rem; padding:0; transition:all 0.15s ease; 
                                       box-shadow:${isAmtZero ? '0 0 8px rgba(251,191,36,0.6)' : 'none'};" 
                                title="🔄 إعادة فحص وقراءة هذا المستند بالذكاء الاصطناعي (Refresh)">
                            🔄
                        </button>
                    `;

                    // Delete button - ملموم ومصغر وبداخله أيقونة صغيرة
                    const deleteBtnHtml = (itemsCount > 1) ? `
                        <button type="button" 
                                onclick="event.stopPropagation(); FinancialOCR.deleteItemByIndex(${idx})" 
                                style="width:24px; height:24px; min-width:24px; border-radius:5px; 
                                       background:rgba(239,68,68,0.25); border:1.2px solid rgba(239,68,68,0.5); 
                                       color:#fca5a5; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; 
                                       font-size:0.78rem; font-weight:900; padding:0; transition:all 0.15s ease;" 
                                title="استبعاد هذا المستند من الدفعة">
                            ✕
                        </button>
                    ` : '';

                    // Distinct Functional Color Themes for Cards:
                    // 1. Duplicate: Deep Crimson Red
                    // 2. Golden Warning: Amber Golden Theme (غير معتمد في النماذج / صورة سيارة / غير واضح / صفر)
                    // 3. Clean: Emerald Green (سليم مار طوالي)
                    let cardBg = '';
                    let cardBorderColor = '';
                    let baseShadow = '';

                    if (isDuplicate) {
                        cardBg = 'linear-gradient(135deg, rgba(185,28,28,0.58), rgba(127,29,29,0.75))';
                        cardBorderColor = '#ef4444';
                        baseShadow = '0 0 14px rgba(239,68,68,0.55)';
                    } else if (isGolden) {
                        // اللون الذهبي الأنيق للمستندات غير المعتمدة في النماذج أو غير الواضحة أو صور السيارات
                        cardBg = 'linear-gradient(135deg, rgba(180,83,9,0.58), rgba(120,53,15,0.78))';
                        cardBorderColor = '#f59e0b';
                        baseShadow = '0 0 14px rgba(245,158,11,0.55)';
                    } else {
                        // سليم مار طوالي (أخضر زمردي)
                        cardBg = 'linear-gradient(135deg, rgba(6,78,59,0.58), rgba(4,120,87,0.48))';
                        cardBorderColor = 'rgba(16,185,129,0.85)';
                        baseShadow = '0 2px 8px rgba(0,0,0,0.35)';
                    }

                    // Highlight Active/Selected Card: خطين أزرق وفي منتصفهما خط أصفر مشع/مضيء لتمييز فائق واستثنائي
                    let cardOutline = '';
                    let cardShadow = baseShadow;
                    if (isSelected) {
                        cardBorderColor = '#0284c7';
                        cardOutline = 'outline: 2.5px solid #facc15; outline-offset: 1px;';
                        cardShadow = `${baseShadow}, 0 0 0 3.5px #0284c7, 0 0 20px rgba(250,204,21,0.95), 0 0 32px rgba(56,189,248,0.8), inset 0 0 12px rgba(250,204,21,0.25)`;
                    }

                    const card = document.createElement('div');
                    card.style.cssText = `
                        background: ${cardBg};
                        border: 2px solid ${cardBorderColor};
                        ${cardOutline}
                        border-radius: 8px;
                        padding: 4px 6px;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        height: 58px;
                        min-height: 52px;
                        max-height: 58px;
                        box-sizing: border-box;
                        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
                        position: relative;
                        box-shadow: ${cardShadow};
                    `;

                    card.onmouseenter = () => {
                        card.style.transform = 'translateY(-1px) scale(1.01)';
                        if (!isSelected) {
                            card.style.borderColor = isDuplicate ? '#fca5a5' : (isGolden ? '#fde68a' : '#6ee7b7');
                        }
                    };
                    card.onmouseleave = () => {
                        card.style.transform = 'none';
                        if (!isSelected) {
                            card.style.borderColor = cardBorderColor;
                        }
                    };

                    card.onclick = () => {
                        this.syncCurrentItemFromForm();
                        this.selectBatchItem(idx);
                    };
                    card.innerHTML = `
                        <!-- الطرف الأيمن: رقم البطاقة جالس لوحده بدون مربع ومصغر بتباين عالي جداً + تمييز الشيك وشارة الحالة الملونة وأسفلها مباشرة كتابة SDG بلون فاقع وواضح -->
                        <div style="display:flex; align-items:center; gap:3px; flex-shrink:0;">
                            <span style="font-size:0.82rem; font-weight:900; color:#38bdf8; text-shadow:0 0 8px rgba(56,189,248,0.95), 0 1px 2px #000; font-family:'Bahnschrift','Segoe UI','Tajawal',sans-serif; font-variant-numeric:tabular-nums; line-height:1; display:inline-block; flex-shrink:0; pointer-events:none;" title="المستند رقم ${idx + 1}">
                                #${idx + 1}
                            </span>
                            ${isCheque ? '<span style="font-size:0.80rem;" title="شيك مصرفي">✍️</span>' : ''}
                            
                            <!-- شارة الحالة الملونة وأسفلها تماماً كتابة SDG بلون فاقع ومشع واضح جداً -->
                            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:1px; line-height:1; flex-shrink:0;">
                                ${statusSquareBtnHtml}
                                <span style="font-size:0.55rem; font-weight:900; font-family:'Tajawal',sans-serif; text-transform:uppercase; letter-spacing:0.5px; color:${isSelected ? '#fef08a' : '#ffffff'}; text-shadow:${isSelected ? '0 0 8px rgba(250,204,21,0.95), 0 1px 2px #000' : '0 0 8px rgba(16,185,129,0.95), 0 1px 2px #000'}; line-height:1; margin-top:2px;" title="العملة: SDG">
                                    SDG
                                </span>
                            </div>
                        </div>

                        <!-- المنتصف: كل المساحة المتبقية من علامة الصح إلى رقم التحويلة مخصصة بالكامل للمبلغ بحجم كبير وواضح جداً، ورقم التحويلة مكبر درجة إضافية -->
                        <div style="display:flex; align-items:center; justify-content:space-between; flex:1; min-width:0; padding:0 2px; gap:4px; overflow:hidden;">
                            
                            <!-- المبلغ: يكبر ويشغل كل المساحة ما بين رقم التحويلة وبين مربع الصح بحجم كبير وواضح وبارز -->
                            <div style="display:flex; align-items:center; justify-content:center; flex:1; min-width:0; line-height:1; margin:0 2px; padding:0; overflow:hidden;">
                                ${ (isNonFinancial || isUnapproved) ? `
                                    <span style="color:#fef08a; background:rgba(217,119,6,0.35); border:1px solid #f59e0b; padding:2px 6px; border-radius:4px; font-size:0.75rem; font-weight:800; white-space:nowrap;">
                                        ${(item.data.rejection_reason && item.data.rejection_reason.includes('سيارة')) ? 'صورة سيارة ⚠️' : (isNonFinancial ? 'غير مالي 🚫' : 'غير معتمد ⚠️')}
                                    </span>
                                ` : (isAmtZero ? `
                                    <span style="color:#fca5a5; font-size:1.58rem; font-weight:900; font-family:'Bahnschrift','Segoe UI','Tajawal',sans-serif; font-variant-numeric:tabular-nums; line-height:1; letter-spacing:-0.4px; white-space:nowrap;" title="0.00 SDG ⚠️ المبلغ غير ظاهر - صفر">
                                        0.00
                                    </span>
                                ` : `
                                    <span style="color:#fef08a; font-size:${Math.round(amt).toLocaleString('en-US').length > 9 ? '1.50rem' : '1.70rem'}; font-weight:900; font-family:'Bahnschrift Condensed','Bahnschrift','Segoe UI','Tajawal',sans-serif; font-variant-numeric:tabular-nums; line-height:1; letter-spacing:-0.5px; text-shadow:0 0 10px rgba(251,191,36,0.7), 0 1px 2px #000; white-space:nowrap;" title="المبلغ المالي">
                                        ${Math.round(amt).toLocaleString('en-US')}
                                    </span>
                                `)}
                            </div>

                            <!-- رقم التحويلة: يبدأ مباشرة من عند أيقونة ريفريش على اليسار، ومكبر درجة إضافية ويتسع لـ 12 رقماً كاملاً بدون أي بتر -->
                            <div style="display:flex; align-items:center; justify-content:flex-start; flex-shrink:0; min-width:0; overflow:hidden; direction:ltr; margin-left:2px;">
                                ${isGolden ? `
                                    <span style="background:rgba(120,53,15,0.45); border:1.2px solid rgba(245,158,11,0.5); border-radius:5px; padding:2px 6px; color:#fef3c7; font-size:0.75rem; font-weight:800; font-family:'Tajawal',sans-serif; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%; direction:rtl;" title="${item.data.rejection_reason || (isAmtZero ? 'المستند غير واضح، المبلغ غير ظاهر' : 'المستند غير معتمد في النماذج')}">
                                        تنبيه: ${item.data.rejection_reason || (isAmtZero ? 'المستند غير واضح، المبلغ غير ظاهر' : 'المستند غير معتمد في النماذج')}
                                    </span>
                                ` : `
                                    <span style="background:rgba(15,23,42,0.70); border:1.2px solid ${isSelected ? '#38bdf8' : 'rgba(56,189,248,0.35)'}; border-radius:5px; padding:2px 5px; color:${isSelected ? '#7dd3fc' : '#38bdf8'}; font-size:${(refVal && refVal.length > 13) ? '1.04rem' : (refVal && refVal.length > 11) ? '1.15rem' : '1.22rem'}; font-weight:800; font-family:'Bahnschrift','Segoe UI','Tajawal',sans-serif; font-variant-numeric:tabular-nums; line-height:1.15; letter-spacing:-0.25px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%; direction:ltr; text-align:left; box-shadow:${isSelected ? '0 0 8px rgba(56,189,248,0.5)' : 'none'};" title="رقم العملية / الشيك: ${refVal || '---'}">
                                        ${refVal || '---'}
                                    </span>
                                `}
                            </div>

                        </div>

                        <!-- الطرف الأيسر: أيقونة ريفريش وبجوارها زر الحذف (رقم الإشعار يبدأ مباشرة من عند أيقونة ريفريش) -->
                        <div style="display:flex; align-items:center; gap:3px; flex-shrink:0;">
                            ${refreshBtnHtml}
                            ${deleteBtnHtml}
                        </div>
                    `;

                    tabsList.appendChild(card);
                });
            }
        },

        deleteItemByIndex(idx) {
            if (!this.batchItems || !this.batchItems[idx]) return;
            this.batchItems.splice(idx, 1);
            if (this.batchItems.length === 0) {
                this.closeReviewModal();
                this.showToast('تم إفراغ قائمة المستندات الممسوحة', 'info');
                return;
            }
            this.currentBatchIndex = Math.max(0, Math.min(this.currentBatchIndex, this.batchItems.length - 1));
            this.selectBatchItem(this.currentBatchIndex);
        },

        // 6. Show Review & Confirmation Modal
        showReviewModal(data) {
            const modal = document.getElementById('financial-ocr-review-modal');
            if (!modal) return;

            // Set preview image
            const previewImg = document.getElementById('ocr-preview-img');
            if (previewImg && this.currentImageBase64) {
                previewImg.src = this.currentImageBase64;
                this.updatePreviewTransform();
            }
            this.bindPreviewZoomEvents();

            // Populate form fields
            const docTypeSelect = document.getElementById('ocr-field-doc-type');
            if (docTypeSelect) {
                docTypeSelect.value = (data.doc_type === 'cheque') ? 'cheque' : 'bank_transfer';
            }
            const badgeDocType = document.getElementById('ocr-badge-doc-type');
            if (badgeDocType) {
                badgeDocType.textContent = (data.doc_type === 'cheque') ? 'الشيك:' : 'السند:';
            }

            const amountInput = document.getElementById('ocr-field-amount');
            const unclearBadge = document.getElementById('ocr-amount-unclear-badge');
            if (amountInput) {
                let amt = data.amount;
                if (typeof amt === 'string') {
                    amt = parseFloat(amt.replace(/[^0-9.]/g, ''));
                }
                const isZeroOrUnclear = (amt === undefined || amt === null || isNaN(amt) || amt <= 0 || data.is_unclear_amount);
                if (isZeroOrUnclear) {
                    amountInput.value = '';
                    amountInput.placeholder = 'المبلغ غير واضح - أدخله يدوياً';
                    amountInput.style.background = '#000000';
                    amountInput.style.borderColor = '#f59e0b';
                    amountInput.style.color = '#fca5a5';
                    amountInput.style.boxShadow = 'none';
                    amountInput.style.textShadow = 'none';
                    if (unclearBadge) {
                        unclearBadge.style.display = 'inline-flex';
                    }
                } else {
                    const amtProps = (this.savedFieldProps && this.savedFieldProps['amount']) ? this.savedFieldProps['amount'] : {};
                    const formattedAmt = this.formatFieldValue('amount', amt, amtProps);
                    amountInput.value = formattedAmt;
                    amountInput.placeholder = '0';
                    amountInput.style.background = '#000000';
                    amountInput.style.borderColor = (amtProps && amtProps.color) ? amtProps.color : '#fbbf24';
                    amountInput.style.color = (amtProps && amtProps.color) ? amtProps.color : '#fef08a';
                    amountInput.style.boxShadow = '0 0 16px rgba(251,191,36,0.35)';
                    amountInput.style.textShadow = '0 0 10px rgba(251,191,36,0.4)';
                    if (unclearBadge) {
                        unclearBadge.style.display = 'none';
                    }
                }
                this.adjustAmountFontSize(amountInput);
            }

            const bankNameInput = document.getElementById('ocr-field-bank-name');
            if (bankNameInput) bankNameInput.value = data.bank_name || '';

            const refNoInput = document.getElementById('ocr-field-ref-no');
            if (refNoInput) {
                const rawRef = (data.doc_type === 'cheque' && data.cheque_number) ? data.cheque_number : (data.transaction_ref || '');
                const refProps = (this.savedFieldProps && this.savedFieldProps['ref-no']) ? this.savedFieldProps['ref-no'] : {};
                refNoInput.value = this.formatFieldValue('ref-no', rawRef, refProps);
            }

            const dateInput = document.getElementById('ocr-field-date');
            if (dateInput) {
                dateInput.value = data.date || new Date().toISOString().split('T')[0];
            }

            const dueDateInput = document.getElementById('ocr-field-due-date');
            if (dueDateInput) {
                dueDateInput.value = data.due_date || data.date || '';
            }

            const senderAccInput = document.getElementById('ocr-field-sender-acc');
            if (senderAccInput) {
                const rawSender = data.sender_account || '';
                const senderProps = (this.savedFieldProps && this.savedFieldProps['sender-acc']) ? this.savedFieldProps['sender-acc'] : {};
                senderAccInput.value = this.formatFieldValue('sender-acc', rawSender, senderProps);
            }

            const receiverAccInput = document.getElementById('ocr-field-receiver-acc');
            if (receiverAccInput) {
                const rawReceiver = data.receiver_account || '';
                const receiverProps = (this.savedFieldProps && this.savedFieldProps['receiver-acc']) ? this.savedFieldProps['receiver-acc'] : {};
                receiverAccInput.value = this.formatFieldValue('receiver-acc', rawReceiver, receiverProps);
            }

            const senderInput = document.getElementById('ocr-field-sender');
            if (senderInput) senderInput.value = data.sender_name || '';

            const receiverInput = document.getElementById('ocr-field-receiver');
            if (receiverInput) receiverInput.value = data.receiver_name || '';

            const phoneInput = document.getElementById('ocr-field-phone');
            if (phoneInput) {
                let p = (data.receiver_phone || data.phone || '').trim();
                if (/^(n\/?a|null|none|غير\s*متوفر|لا\s*يوجد|-)$/i.test(p)) {
                    p = '';
                }
                phoneInput.value = p;
                phoneInput.placeholder = '';
            }

            const notesInput = document.getElementById('ocr-field-notes');
            if (notesInput) {
                let noteText = (data.notes || '').trim();
                if (/^(n\/?a|null|none|-)$/i.test(noteText)) {
                    noteText = '';
                }
                notesInput.value = noteText;
                notesInput.placeholder = '';
            }

            // Summary text & Cheque Arabic Words Display
            const summaryElem = document.getElementById('ocr-doc-summary');
            if (summaryElem) {
                let sumText = data.summary || 'تم التعرف على المستند بنجاح بدقة عالية.';
                if (data.amount_in_words) {
                    sumText = '✍️ التفقيط: ' + data.amount_in_words + ' | ' + sumText;
                }
                summaryElem.textContent = sumText;
                summaryElem.title = sumText;
            }

            this.handleTypeChange(docTypeSelect ? docTypeSelect.value : data.doc_type);
            this.updateReferenceValidationUI(data);

            const savedSide = localStorage.getItem('erp_ocr_layout_side') || 'standard';
            this.applyLayoutSide(savedSide);

            // 1. Relocate all modal elements directly under document.body to guarantee they break free of any parent overflow/transform containers
            const modalIds = [
                'financial-ocr-file-input',
                'financial-ocr-loading',
                'financial-ocr-review-modal',
                'financial-ocr-zoom-modal',
                'financial-ocr-key-modal',
                'financial-ocr-trainer-modal'
            ];
            modalIds.forEach(id => {
                const el = document.getElementById(id);
                if (el && el.parentElement !== document.body) {
                    document.body.appendChild(el);
                }
            });

            // 2. Total Screen Domination: Hide all background page elements completely
            document.body.classList.add('ocr-modal-active');
            document.documentElement.classList.add('ocr-modal-active');
            document.body.style.overflow = 'hidden';
            // 3. Apply saved persistent field styles (خصائص الخط واللون المحفوظة مسبقاً)
            this.initFieldProps();
            this.applySavedFieldPreferences();

            // 4. Display modal
            modal.style.display = 'flex';
            requestAnimationFrame(() => {
                modal.style.opacity = '1';
            });
        },

        // Fullscreen toggle: Take over entire computer monitor
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                const elem = document.documentElement;
                if (elem.requestFullscreen) {
                    elem.requestFullscreen().catch(() => {});
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                } else if (elem.msRequestFullscreen) {
                    elem.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(() => {});
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
            }
        },

        // Swap sender and receiver accounts if inverted (تبديل الحسابين فوراً بنقرة واحدة)
        swapAccounts() {
            const senderAcc = document.getElementById('ocr-field-sender-acc');
            const receiverAcc = document.getElementById('ocr-field-receiver-acc');
            if (senderAcc && receiverAcc) {
                const tmp = senderAcc.value;
                senderAcc.value = receiverAcc.value;
                receiverAcc.value = tmp;

                // Also update notes if they contain default bank transfer account text
                const notesInput = document.getElementById('ocr-field-notes');
                if (notesInput && notesInput.value && (notesInput.value.includes('إلى حساب') || notesInput.value.includes('تحويل بنكي'))) {
                    const item = this.batchItems ? this.batchItems[this.currentBatchIndex] : null;
                    const data = item ? item.data : {};
                    let accPart = receiverAcc.value ? ' إلى حساب ' + receiverAcc.value : '';
                    notesInput.value = 'تحويل بنكي عبر ' + (data.app_name || data.bank_name || 'البنك') + (data.sender_name ? ' من ' + data.sender_name : '') + accPart;
                }

                this.syncCurrentItemFromForm();
                this.showToast('⇄ تم عكس وتبديل رقمي الحسابين (من ⇆ إلى) بنجاح!', 'info');
                if (typeof this.playSuccessSound === 'function') {
                    this.playSuccessSound();
                }
            }
        },

        // Toggle layout sides: Image is ALWAYS in the center; side columns swap between Right and Left
        toggleLayoutSide() {
            const current = localStorage.getItem('erp_ocr_layout_side') || 'standard';
            const next = (current === 'standard') ? 'swapped' : 'standard';
            localStorage.setItem('erp_ocr_layout_side', next);
            this.applyLayoutSide(next);
            this.showToast(next === 'standard' ? 'الدفعة باليمين • الشيك بالوسط • التفاصيل باليسار' : 'التفاصيل باليمين • الشيك بالوسط • الدفعة باليسار', 'info');
        },

        applyLayoutSide(side) {
            const grid = document.querySelector('.ocr-responsive-grid');
            const imgCol = document.getElementById('ocr-col-image');
            const midCol = document.getElementById('ocr-col-middle');
            const formCol = document.getElementById('ocr-col-form');
            const indicator = document.getElementById('ocr-layout-side-indicator');
            if (!grid || !imgCol || !midCol || !formCol) return;

            grid.style.direction = 'rtl';
            imgCol.style.direction = 'rtl';
            midCol.style.direction = 'rtl';
            formCol.style.direction = 'rtl';

            // Image is ALWAYS strictly in the center (order: 2)
            imgCol.style.order = '2';

            if (side === 'swapped' || side === 'image-left') {
                formCol.style.order = '1'; // Details on Right
                midCol.style.order = '3';  // Batch on Left
                grid.style.gridTemplateColumns = 'minmax(0, 0.765fr) minmax(0, 1.255fr) minmax(0, 1.98fr)';
                if (indicator) indicator.textContent = '📋 البيانات باليمين • 📷 الشيك بالوسط • 💳 الدفعة باليسار';
            } else {
                midCol.style.order = '1';  // Batch on Right
                formCol.style.order = '3'; // Details on Left
                grid.style.gridTemplateColumns = 'minmax(0, 1.98fr) minmax(0, 1.255fr) minmax(0, 0.765fr)';
                if (indicator) indicator.textContent = '💳 الدفعة باليمين • 📷 الشيك بالوسط • 📋 البيانات باليسار';
            }
        },

        handleTypeChange(type) {
            const badge = document.getElementById('ocr-doc-badge');
            const badgeText = document.getElementById('ocr-doc-badge-text');
            const headerTitle = document.getElementById('ocr-header-title');
            const refLabel = document.getElementById('ocr-label-ref-no');
            const dueDateBox = document.getElementById('ocr-box-due-date');

            if (headerTitle) headerTitle.textContent = '';

            if (type === 'cheque') {
                if (badge) {
                    badge.style.background = 'rgba(168, 85, 247, 0.2)';
                    badge.style.borderColor = '#a855f7';
                    badge.style.color = '#d8b4fe';
                }
                if (badgeText) badgeText.textContent = 'شيك مصرفي';
                if (refLabel) refLabel.textContent = 'الرقم:';
                if (dueDateBox) dueDateBox.style.display = 'flex';
            } else {
                if (badge) {
                    badge.style.background = 'rgba(16, 185, 129, 0.2)';
                    badge.style.borderColor = '#10b981';
                    badge.style.color = '#34d399';
                }
                if (badgeText) badgeText.textContent = 'تحويل بنكي';
                if (refLabel) refLabel.textContent = 'الرقم:';
                if (dueDateBox) dueDateBox.style.display = 'none';
            }
            this.applySavedFieldPreferences();
        },

        // Dynamic Font Fitting for Amount (-2 degrees: 2.30rem) to fill the allocated space up to 8 digits with golden dominance
        adjustAmountFontSize(input) {
            if (!input) input = document.getElementById('ocr-field-amount');
            if (!input) return;

            // CRITICAL: When user saved a custom font size for amount, NEVER override it!
            if (this.savedFieldProps && this.savedFieldProps['amount'] && this.savedFieldProps['amount'].fontSize) {
                input.style.setProperty('font-size', this.savedFieldProps['amount'].fontSize, 'important');
                if (this.savedFieldProps['amount'].color) {
                    input.style.setProperty('color', this.savedFieldProps['amount'].color, 'important');
                    input.style.setProperty('border-color', this.savedFieldProps['amount'].color, 'important');
                }
                const remVal = parseFloat(this.savedFieldProps['amount'].fontSize);
                if (remVal >= 2.4) {
                    input.style.setProperty('height', Math.round(remVal * 16.5) + 'px', 'important');
                }
                return;
            }

            const len = (input.value || '').trim().length;
            let size = '2.30rem';
            if (len > 13) {
                size = '1.75rem';
            } else if (len > 11) {
                size = '1.95rem';
            } else if (len > 9) { // 8-9 digits with commas (e.g. 99,999,999)
                size = '2.15rem';
            } else { // up to 8 digits
                size = '2.30rem';
            }
            input.style.setProperty('font-size', size, 'important');
            input.style.setProperty('height', '44px', 'important');
        },

        // Handle Amount Direct Input & Live Sync
        handleAmountInput(input) {
            this.adjustAmountFontSize(input);
            this.syncCurrentItemFromForm();
            this.applySavedFieldPreferences();
        },

        // Format Amount Field with Thousand Separator on Blur (بخط واضح تظهر فيه الفاصلة الألفية)
        formatAmountField(input) {
            if (!input) return;
            const props = (this.tempPropState && this.activePropFieldKey === 'amount')
                ? this.tempPropState
                : ((this.savedFieldProps && this.savedFieldProps['amount']) ? this.savedFieldProps['amount'] : {});
            const raw = (input.value || '').replace(/[^0-9.]/g, '');
            if (!raw) return;
            const num = parseFloat(raw);
            if (!isNaN(num) && num > 0) {
                input.value = this.formatFieldValue('amount', num, props);
            }
            this.adjustAmountFontSize(input);
            this.syncCurrentItemFromForm();
            this.applySavedFieldPreferences();
        },

        // Restore Original AI Extracted Data (الرجوع للأصل)
        restoreOriginalItemData() {
            const item = (this.batchItems && this.batchItems[this.currentBatchIndex])
                ? this.batchItems[this.currentBatchIndex]
                : (this.lastExtractedData ? { data: this.lastExtractedData, originalData: this.originalExtractedData } : null);
            if (!item) return;

            const orig = item.originalData || item.rawExtractedData || this.originalExtractedData;
            if (orig) {
                item.data = JSON.parse(JSON.stringify(orig));
                this.lastExtractedData = item.data;
                this.showReviewModal(item.data);
                this.updateReferenceValidationUI(item.data);
                this.renderBatchTabs();
                this.showToast('↺ تم استعادة البيانات الأصلية المستخرجة بالذكاء الاصطناعي بنجاح!', 'info');
                if (typeof this.playSuccessSound === 'function') {
                    this.playSuccessSound();
                }
            } else {
                this.showToast('⚠️ لا توجد بيانات أصلية محفوظة لهذا المستند.', 'warning');
            }
        },

        // -----------------------------------------------------------------
        // Left Column Custom Field Properties & Context Menu Management
        // خصائص وتخصيص الخط واللون للحقول الأربعة مع الحفظ الدائم في قاعدة البيانات
        // -----------------------------------------------------------------
        fieldDefs: {
            'date': {
                elId: 'ocr-field-date',
                nameAr: 'التاريخ',
                defaultSize: '1.25rem',
                defaultColor: '#ffffff',
                defaultWeight: '900',
                minSize: 0.8,
                maxSize: 4.0,
                hasSeparators: false,
                hasDecimals: false
            },
            'amount': {
                elId: 'ocr-field-amount',
                nameAr: 'المبلغ',
                defaultSize: '2.30rem',
                defaultColor: '#fef08a',
                defaultWeight: '900',
                minSize: 1.2,
                maxSize: 8.5,
                hasSeparators: true,
                hasDecimals: true,
                defaultThousandsSep: true,
                defaultDecimals: 0
            },
            'ref-no': {
                elId: 'ocr-field-ref-no',
                nameAr: 'الرقم',
                defaultSize: '1.55rem',
                defaultColor: '#38bdf8',
                defaultWeight: '900',
                minSize: 0.8,
                maxSize: 5.0,
                hasSeparators: true,
                hasDecimals: false,
                defaultThousandsSep: false
            },
            'sender-acc': {
                elId: 'ocr-field-sender-acc',
                nameAr: 'حساب المرسل (من)',
                defaultSize: '1.06rem',
                defaultColor: '#bae6fd',
                defaultWeight: '900',
                minSize: 0.7,
                maxSize: 3.0,
                hasSeparators: true,
                hasDecimals: false,
                defaultThousandsSep: false
            },
            'receiver-acc': {
                elId: 'ocr-field-receiver-acc',
                nameAr: 'حساب المستلم (إلى)',
                defaultSize: '1.06rem',
                defaultColor: '#a7f3d0',
                defaultWeight: '900',
                minSize: 0.7,
                maxSize: 3.0,
                hasSeparators: true,
                hasDecimals: false,
                defaultThousandsSep: false
            },
            'receiver': {
                elId: 'ocr-field-receiver',
                nameAr: 'اسم المستلم (شركة الأسمنت)',
                defaultSize: '0.94rem',
                defaultColor: '#ffffff',
                defaultWeight: '800',
                minSize: 0.7,
                maxSize: 3.0,
                hasSeparators: false,
                hasDecimals: false
            },
            'bank': {
                elId: 'ocr-field-bank-name',
                nameAr: 'اسم البنك',
                defaultSize: '1.08rem',
                defaultColor: '#ffffff',
                defaultWeight: '800',
                minSize: 0.8,
                maxSize: 3.0,
                hasSeparators: false,
                hasDecimals: false
            }
        },

        savedFieldProps: null,
        activePropFieldKey: null,
        tempPropState: null,
        isPropsEventsBound: false,

        initFieldProps() {
            const TARGET_VERSION = 'v7_amount_no_decimals_calibrated';
            let dbProps = window.DB_OCR_PROPS || {};
            let localProps = {};
            try {
                localProps = JSON.parse(localStorage.getItem('ocr_field_preferences')) || {};
            } catch(e) {}

            const currentVer = localStorage.getItem('ocr_props_version');
            if (currentVer !== TARGET_VERSION) {
                const newDefaults = {
                    'date': { fontSize: '1.25rem', color: '#ffffff', fontWeight: '900', thousandsSep: false, decimals: 0 },
                    'ref-no': { fontSize: '1.55rem', color: '#38bdf8', fontWeight: '900', thousandsSep: false, decimals: 0 },
                    'amount': { fontSize: '2.30rem', color: '#fef08a', fontWeight: '900', thousandsSep: true, decimals: 0 },
                    'sender-acc': { fontSize: '1.06rem', color: '#bae6fd', fontWeight: '900', thousandsSep: false, decimals: 0 },
                    'receiver-acc': { fontSize: '1.06rem', color: '#a7f3d0', fontWeight: '900', thousandsSep: false, decimals: 0 },
                    'receiver': { fontSize: '0.94rem', color: '#ffffff', fontWeight: '800', thousandsSep: false, decimals: 0 }
                };
                localProps = Object.assign({}, localProps, newDefaults);
                try {
                    localStorage.setItem('ocr_field_preferences', JSON.stringify(localProps));
                    localStorage.setItem('ocr_props_version', TARGET_VERSION);
                } catch(e) {}
            }

            this.savedFieldProps = Object.assign({}, dbProps, localProps);

            if (!this.isPropsEventsBound) {
                this.bindPropsContextMenuEvents();
                this.isPropsEventsBound = true;
            }

            this.applySavedFieldPreferences();
        },

        bindPropsContextMenuEvents() {
            const handleContextMenu = (e, fieldKey) => {
                e.preventDefault();
                e.stopPropagation();
                if (e.stopImmediatePropagation) e.stopImmediatePropagation();
                this.openPropsMenuFor(fieldKey, e);
            };

            for (const [key, def] of Object.entries(this.fieldDefs)) {
                const el = document.getElementById(def.elId);
                if (el) {
                    el.addEventListener('contextmenu', (e) => handleContextMenu(e, key));
                    const box = el.closest('.ocr-field-box');
                    if (box) {
                        box.addEventListener('contextmenu', (e) => {
                            if (e.target && e.target !== el && e.target.tagName === 'INPUT' && e.target.id !== def.elId) {
                                return;
                            }
                            handleContextMenu(e, key);
                        });
                    }
                }
            }

            // Global delegation within left column
            const formCol = document.getElementById('ocr-col-form');
            if (formCol) {
                formCol.addEventListener('contextmenu', (e) => {
                    const t = e.target;
                    if (!t) return;
                    for (const [key, def] of Object.entries(this.fieldDefs)) {
                        if (t.id === def.elId || (t.closest && t.closest('#' + def.elId))) {
                            handleContextMenu(e, key);
                            return;
                        }
                    }
                });
            }

            // Click outside to dismiss menu
            document.addEventListener('click', (e) => {
                const menu = document.getElementById('ocr-custom-props-menu');
                if (menu && menu.style.display === 'block' && !menu.contains(e.target)) {
                    this.closePropsMenu();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closePropsMenu();
                }
            });
        },

        openPropsMenuFor(fieldKey, event) {
            const def = this.fieldDefs[fieldKey];
            if (!def) return;
            this.activePropFieldKey = fieldKey;

            const menu = document.getElementById('ocr-custom-props-menu');
            if (!menu) return;

            if (!this.savedFieldProps) this.initFieldProps();

            const curProps = (this.savedFieldProps && this.savedFieldProps[fieldKey])
                ? { ...this.savedFieldProps[fieldKey] }
                : {
                    fontSize: def.defaultSize,
                    color: def.defaultColor,
                    fontWeight: def.defaultWeight,
                    thousandsSep: def.defaultThousandsSep !== undefined ? def.defaultThousandsSep : (fieldKey === 'amount'),
                    decimals: def.defaultDecimals !== undefined ? def.defaultDecimals : 2
                  };

            this.tempPropState = { ...curProps };

            const nameBadge = document.getElementById('ocr-prop-field-name-badge');
            if (nameBadge) nameBadge.textContent = def.nameAr;

            const sizeBadge = document.getElementById('ocr-prop-size-badge');
            if (sizeBadge) sizeBadge.textContent = this.tempPropState.fontSize || def.defaultSize;

            const sizeRange = document.getElementById('ocr-prop-size-range');
            if (sizeRange) {
                const numericSize = parseFloat(this.tempPropState.fontSize) || parseFloat(def.defaultSize);
                sizeRange.min = def.minSize;
                sizeRange.max = def.maxSize;
                sizeRange.value = numericSize;
            }

            const colorCode = document.getElementById('ocr-prop-color-code');
            if (colorCode) colorCode.textContent = this.tempPropState.color || def.defaultColor;

            const colorPreview = document.getElementById('ocr-prop-color-preview-circle');
            if (colorPreview) colorPreview.style.background = this.tempPropState.color || def.defaultColor;

            const colorPicker = document.getElementById('ocr-prop-color-picker');
            if (colorPicker) colorPicker.value = this.tempPropState.color || def.defaultColor;

            // Section 3: Thousands separator setup
            const thousandsRow = document.getElementById('ocr-prop-thousands-row');
            if (thousandsRow) {
                if (def.hasSeparators !== false) {
                    thousandsRow.style.display = 'flex';
                    const isSep = (this.tempPropState.thousandsSep !== undefined)
                        ? this.tempPropState.thousandsSep
                        : (def.defaultThousandsSep !== undefined ? def.defaultThousandsSep : (fieldKey === 'amount'));
                    this.tempPropState.thousandsSep = isSep;

                    const sepIcon = document.getElementById('ocr-prop-sep-icon');
                    const sepText = document.getElementById('ocr-prop-sep-text');
                    const sepBtn = document.getElementById('ocr-prop-sep-btn');
                    if (sepIcon) sepIcon.textContent = isSep ? '✓' : '✕';
                    if (sepText) sepText.textContent = isSep ? 'مفعل (مثال: 20,000)' : 'معطل (مثال: 20000)';
                    if (sepBtn) {
                        sepBtn.style.background = isSep ? 'rgba(16,185,129,0.25)' : 'rgba(239,68,68,0.2)';
                        sepBtn.style.borderColor = isSep ? '#10b981' : '#ef4444';
                        sepBtn.style.color = isSep ? '#34d399' : '#fca5a5';
                    }
                } else {
                    thousandsRow.style.display = 'none';
                }
            }

            // Section 4: Decimals setup
            const decimalsRow = document.getElementById('ocr-prop-decimals-row');
            if (decimalsRow) {
                if (def.hasDecimals === true) {
                    decimalsRow.style.display = 'flex';
                    const dec = (this.tempPropState.decimals !== undefined)
                        ? this.tempPropState.decimals
                        : (def.defaultDecimals !== undefined ? def.defaultDecimals : 2);
                    this.tempPropState.decimals = dec;

                    const dec0 = document.getElementById('ocr-prop-dec-0-btn');
                    const dec2 = document.getElementById('ocr-prop-dec-2-btn');
                    if (dec0 && dec2) {
                        if (dec === 0) {
                            dec0.style.border = '1.8px solid #38bdf8';
                            dec0.style.color = '#7dd3fc';
                            dec0.style.background = 'rgba(56,189,248,0.25)';
                            dec2.style.border = '1px solid rgba(255,255,255,0.2)';
                            dec2.style.color = '#cbd5e1';
                            dec2.style.background = '#1e293b';
                        } else {
                            dec2.style.border = '1.8px solid #38bdf8';
                            dec2.style.color = '#7dd3fc';
                            dec2.style.background = 'rgba(56,189,248,0.25)';
                            dec0.style.border = '1px solid rgba(255,255,255,0.2)';
                            dec0.style.color = '#cbd5e1';
                            dec0.style.background = '#1e293b';
                        }
                    }
                } else {
                    decimalsRow.style.display = 'none';
                }
            }

            // Section 5: Font weight setup
            const curWeight = String(this.tempPropState.fontWeight || def.defaultWeight || '900');
            ['600', '800', '900'].forEach(w => {
                const btn = document.getElementById('ocr-prop-weight-' + w);
                if (btn) {
                    if (w === curWeight) {
                        btn.style.background = 'rgba(56,189,248,0.25)';
                        btn.style.border = '1.5px solid #38bdf8';
                        btn.style.color = '#7dd3fc';
                        btn.style.fontWeight = '900';
                    } else {
                        btn.style.background = '#1e293b';
                        btn.style.border = '1px solid rgba(255,255,255,0.2)';
                        btn.style.color = '#cbd5e1';
                        btn.style.fontWeight = 'normal';
                    }
                }
            });

            const saveBtnText = document.getElementById('ocr-prop-save-text');
            if (saveBtnText) saveBtnText.textContent = 'حفظ الخصائص وتثبيتها';
            const saveBtn = document.getElementById('ocr-prop-save-btn');
            if (saveBtn) {
                saveBtn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                saveBtn.style.borderColor = '#34d399';
            }

            menu.style.display = 'block';
            let x = event ? (event.clientX || event.pageX) : 250;
            let y = event ? (event.clientY || event.pageY) : 250;

            const menuWidth = 330;
            const menuHeight = 440;

            if (x + menuWidth > window.innerWidth) {
                x = Math.max(10, window.innerWidth - menuWidth - 20);
            }
            if (y + menuHeight > window.innerHeight) {
                y = Math.max(10, window.innerHeight - menuHeight - 20);
            }

            menu.style.left = x + 'px';
            menu.style.top = y + 'px';
        },

        onPropSizeSlider(val) {
            if (!this.activePropFieldKey || !this.tempPropState) return;
            const sizeStr = parseFloat(val).toFixed(2) + 'rem';
            this.tempPropState.fontSize = sizeStr;

            const sizeBadge = document.getElementById('ocr-prop-size-badge');
            if (sizeBadge) sizeBadge.textContent = sizeStr;

            const def = this.fieldDefs[this.activePropFieldKey];
            if (def) {
                const el = document.getElementById(def.elId);
                if (el) {
                    el.style.setProperty('font-size', sizeStr, 'important');
                    if (this.activePropFieldKey === 'amount') {
                        const remVal = parseFloat(val);
                        if (remVal > 5.2) {
                            el.style.setProperty('height', Math.round(remVal * 17.5) + 'px', 'important');
                        }
                    }
                }
            }
        },

        stepPropFontSize(step) {
            const sizeRange = document.getElementById('ocr-prop-size-range');
            if (!sizeRange) return;
            let cur = parseFloat(sizeRange.value) + step;
            cur = Math.max(parseFloat(sizeRange.min), Math.min(parseFloat(sizeRange.max), cur));
            sizeRange.value = cur.toFixed(2);
            this.onPropSizeSlider(sizeRange.value);
        },

        setPropColor(colorHex) {
            if (!this.activePropFieldKey || !this.tempPropState) return;
            this.tempPropState.color = colorHex;

            const colorCode = document.getElementById('ocr-prop-color-code');
            if (colorCode) colorCode.textContent = colorHex;

            const colorPreview = document.getElementById('ocr-prop-color-preview-circle');
            if (colorPreview) colorPreview.style.background = colorHex;

            const colorPicker = document.getElementById('ocr-prop-color-picker');
            if (colorPicker) colorPicker.value = colorHex;

            const def = this.fieldDefs[this.activePropFieldKey];
            if (def) {
                const el = document.getElementById(def.elId);
                if (el) {
                    el.style.setProperty('color', colorHex, 'important');
                    if (this.activePropFieldKey === 'amount') {
                        el.style.setProperty('border-color', colorHex, 'important');
                        el.style.setProperty('text-shadow', `0 0 14px ${colorHex}88`, 'important');
                    } else if (this.activePropFieldKey === 'ref-no') {
                        el.style.setProperty('border-color', colorHex, 'important');
                        el.style.setProperty('box-shadow', `0 0 14px ${colorHex}66`, 'important');
                    }
                }
            }
        },

        togglePropThousandsSep() {
            if (!this.activePropFieldKey || !this.tempPropState) return;
            this.tempPropState.thousandsSep = !this.tempPropState.thousandsSep;
            const isSep = this.tempPropState.thousandsSep;

            const sepIcon = document.getElementById('ocr-prop-sep-icon');
            const sepText = document.getElementById('ocr-prop-sep-text');
            const sepBtn = document.getElementById('ocr-prop-sep-btn');
            if (sepIcon) sepIcon.textContent = isSep ? '✓' : '✕';
            if (sepText) sepText.textContent = isSep ? 'مفعل (مثال: 20,000)' : 'معطل (مثال: 20000)';
            if (sepBtn) {
                sepBtn.style.background = isSep ? 'rgba(16,185,129,0.25)' : 'rgba(239,68,68,0.2)';
                sepBtn.style.borderColor = isSep ? '#10b981' : '#ef4444';
                sepBtn.style.color = isSep ? '#34d399' : '#fca5a5';
            }

            const def = this.fieldDefs[this.activePropFieldKey];
            if (def) {
                const el = document.getElementById(def.elId);
                if (el) {
                    this.formatElementValue(this.activePropFieldKey, el, this.tempPropState);
                }
            }
        },

        setPropDecimals(dec) {
            if (!this.activePropFieldKey || !this.tempPropState) return;
            this.tempPropState.decimals = parseInt(dec, 10);

            const dec0 = document.getElementById('ocr-prop-dec-0-btn');
            const dec2 = document.getElementById('ocr-prop-dec-2-btn');
            if (dec0 && dec2) {
                if (this.tempPropState.decimals === 0) {
                    dec0.style.border = '1.8px solid #38bdf8';
                    dec0.style.color = '#7dd3fc';
                    dec0.style.background = 'rgba(56,189,248,0.25)';
                    dec2.style.border = '1px solid rgba(255,255,255,0.2)';
                    dec2.style.color = '#cbd5e1';
                    dec2.style.background = '#1e293b';
                } else {
                    dec2.style.border = '1.8px solid #38bdf8';
                    dec2.style.color = '#7dd3fc';
                    dec2.style.background = 'rgba(56,189,248,0.25)';
                    dec0.style.border = '1px solid rgba(255,255,255,0.2)';
                    dec0.style.color = '#cbd5e1';
                    dec0.style.background = '#1e293b';
                }
            }

            const def = this.fieldDefs[this.activePropFieldKey];
            if (def) {
                const el = document.getElementById(def.elId);
                if (el) {
                    this.formatElementValue(this.activePropFieldKey, el, this.tempPropState);
                }
            }
        },

        setPropWeight(weight) {
            if (!this.activePropFieldKey || !this.tempPropState) return;
            this.tempPropState.fontWeight = weight;

            ['600', '800', '900'].forEach(w => {
                const btn = document.getElementById('ocr-prop-weight-' + w);
                if (btn) {
                    if (w === String(weight)) {
                        btn.style.background = 'rgba(56,189,248,0.25)';
                        btn.style.border = '1.5px solid #38bdf8';
                        btn.style.color = '#7dd3fc';
                        btn.style.fontWeight = '900';
                    } else {
                        btn.style.background = '#1e293b';
                        btn.style.border = '1px solid rgba(255,255,255,0.2)';
                        btn.style.color = '#cbd5e1';
                        btn.style.fontWeight = 'normal';
                    }
                }
            });

            const def = this.fieldDefs[this.activePropFieldKey];
            if (def) {
                const el = document.getElementById(def.elId);
                if (el) {
                    el.style.setProperty('font-weight', weight, 'important');
                }
            }
        },

        formatFieldValue(key, val, props) {
            if (val === undefined || val === null) return '';
            val = String(val);
            if (!val.trim()) return '';

            if (!props) {
                props = (this.savedFieldProps && this.savedFieldProps[key]) ? this.savedFieldProps[key] : {};
            }

            if (key === 'amount') {
                const clean = val.replace(/[^0-9.]/g, '');
                if (!clean) return val;
                const num = parseFloat(clean);
                if (isNaN(num)) return val;

                const isSep = (props.thousandsSep !== undefined) ? props.thousandsSep : true;
                const dec = (props.decimals !== undefined) ? parseInt(props.decimals, 10) : 0;

                if (isSep) {
                    return num.toLocaleString('en-US', {
                        minimumFractionDigits: dec,
                        maximumFractionDigits: dec
                    });
                } else {
                    return dec > 0 ? num.toFixed(dec) : Math.round(num).toString();
                }
            }

            // Numeric code fields: ref-no, sender-acc, receiver-acc
            if (key === 'ref-no' || key === 'sender-acc' || key === 'receiver-acc') {
                if (props.thousandsSep === undefined) return val;
                const isSep = Boolean(props.thousandsSep);
                const clean = val.replace(/,/g, '');
                if (isSep) {
                    if (/^\d+(\.\d+)?$/.test(clean)) {
                        const parts = clean.split('.');
                        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        return parts.join('.');
                    } else {
                        return clean.replace(/\d{4,}/g, (match) => match.replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    }
                } else {
                    return clean;
                }
            }

            return val;
        },

        formatElementValue(key, el, props) {
            if (!el) return;
            if (!props) {
                props = (this.savedFieldProps && this.savedFieldProps[key]) ? this.savedFieldProps[key] : {};
            }
            const currentVal = el.value;
            if (currentVal !== undefined && currentVal !== null && String(currentVal).trim() !== '') {
                const newVal = this.formatFieldValue(key, currentVal, props);
                if (newVal !== undefined && newVal !== currentVal) {
                    el.value = newVal;
                }
            }
        },

        saveFieldPreferences() {
            if (!this.activePropFieldKey || !this.tempPropState) return;
            if (!this.savedFieldProps) this.savedFieldProps = {};

            this.savedFieldProps[this.activePropFieldKey] = { ...this.tempPropState };

            // 1. Permanent LocalStorage save
            try {
                localStorage.setItem('ocr_field_preferences', JSON.stringify(this.savedFieldProps));
            } catch(e) {}

            // 2. Permanent MySQL database save
            const fd = new FormData();
            fd.append('action', 'save_ocr_props');
            fd.append('ocr_props', JSON.stringify(this.savedFieldProps));

            fetch(window.location.href, {
                method: 'POST',
                body: fd
            }).catch(() => {});

            // Fallback to API endpoint
            fetch('api/ocr-financial.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'save_ocr_props', ocr_props: this.savedFieldProps })
            }).catch(() => {});

            // 3. Update Save Button with success badge
            const saveBtnText = document.getElementById('ocr-prop-save-text');
            const saveBtn = document.getElementById('ocr-prop-save-btn');
            if (saveBtnText) saveBtnText.textContent = 'تم الحفظ والتثبيت بنجاح ✅';
            if (saveBtn) {
                saveBtn.style.background = 'linear-gradient(135deg, #059669, #047857)';
                saveBtn.style.borderColor = '#10b981';
            }

            // 4. Force apply styles permanently
            this.applySavedFieldPreferences();

            if (typeof this.showToast === 'function') {
                this.showToast('✅ تم تثبيت خصائص الحقل بنجاح! لن تتغير مجدداً إلا عند تعديلها.', 'success');
            }

            // 5. Close menu smoothly after 550ms
            setTimeout(() => {
                this.closePropsMenu();
            }, 550);
        },

        resetCurrentFieldProp() {
            if (!this.activePropFieldKey || !this.savedFieldProps) return;
            const def = this.fieldDefs[this.activePropFieldKey];
            delete this.savedFieldProps[this.activePropFieldKey];

            try {
                localStorage.setItem('ocr_field_preferences', JSON.stringify(this.savedFieldProps));
            } catch(e) {}

            const fd = new FormData();
            fd.append('action', 'save_ocr_props');
            fd.append('ocr_props', JSON.stringify(this.savedFieldProps));
            fetch(window.location.href, { method: 'POST', body: fd }).catch(() => {});

            if (def) {
                const el = document.getElementById(def.elId);
                if (el) {
                    el.style.removeProperty('font-size');
                    el.style.removeProperty('color');
                    el.style.removeProperty('font-weight');
                }
            }

            this.applySavedFieldPreferences();
            this.closePropsMenu();
        },

        closePropsMenu() {
            const menu = document.getElementById('ocr-custom-props-menu');
            if (menu) menu.style.display = 'none';
            this.activePropFieldKey = null;
            this.tempPropState = null;
        },

        applySavedFieldPreferences() {
            if (!this.savedFieldProps) {
                let dbProps = window.DB_OCR_PROPS || {};
                let localProps = {};
                try {
                    localProps = JSON.parse(localStorage.getItem('ocr_field_preferences')) || {};
                } catch(e) {}
                this.savedFieldProps = Object.assign({}, dbProps, localProps);
            }

            for (const [key, def] of Object.entries(this.fieldDefs)) {
                const props = this.savedFieldProps[key];
                const el = document.getElementById(def.elId);
                if (!el) continue;

                if (props && props.fontSize) {
                    el.style.setProperty('font-size', props.fontSize, 'important');
                    if (key === 'amount') {
                        const remVal = parseFloat(props.fontSize);
                        if (remVal > 2.4) {
                            el.style.setProperty('height', Math.round(remVal * 16.5) + 'px', 'important');
                        } else {
                            el.style.setProperty('height', '44px', 'important');
                        }
                    } else if (key === 'date') {
                        el.style.setProperty('height', '40px', 'important');
                    } else if (key === 'ref-no') {
                        el.style.setProperty('height', '40px', 'important');
                    } else if (key === 'sender-acc' || key === 'receiver-acc' || key === 'receiver') {
                        el.style.setProperty('height', '36px', 'important');
                    }
                }

                if (props && props.color) {
                    el.style.setProperty('color', props.color, 'important');
                    if (key === 'amount') {
                        el.style.setProperty('border-color', props.color, 'important');
                        el.style.setProperty('text-shadow', `0 0 14px ${props.color}88`, 'important');
                    } else if (key === 'ref-no') {
                        el.style.setProperty('border-color', props.color, 'important');
                        el.style.setProperty('box-shadow', `0 0 14px ${props.color}66`, 'important');
                    }
                }

                if (props && props.fontWeight) {
                    el.style.setProperty('font-weight', props.fontWeight, 'important');
                }

                if (props && (props.thousandsSep !== undefined || props.decimals !== undefined)) {
                    this.formatElementValue(key, el, props);
                }
            }
        },

        closeReviewModal() {
            const modal = document.getElementById('financial-ocr-review-modal');
            if (modal) {
                modal.style.display = 'none';
            }
            // Restore background screen completely
            document.body.classList.remove('ocr-modal-active');
            document.documentElement.classList.remove('ocr-modal-active');
            document.body.style.overflow = '';
            document.documentElement.style.overflow = '';
            if (document.fullscreenElement && document.exitFullscreen) {
                document.exitFullscreen().catch(() => {});
            }
            this.batchItems = [];
            this.currentBatchIndex = 0;
            this.previewRotation = 0;
            this.resetPreviewZoom();
        },

        // Helper to get active form data
        getCurrentFormData() {
            const docType = document.getElementById('ocr-field-doc-type')?.value || 'bank_transfer';
            const rawAmt = document.getElementById('ocr-field-amount')?.value || '';
            const amount = parseFloat(String(rawAmt).replace(/[^0-9.]/g, '')) || 0;
            const bankName = (document.getElementById('ocr-field-bank-name')?.value || '').trim();
            const refNo = (document.getElementById('ocr-field-ref-no')?.value || '').trim();
            const date = document.getElementById('ocr-field-date')?.value || '';
            const dueDate = document.getElementById('ocr-field-due-date')?.value || date;
            const senderAcc = (document.getElementById('ocr-field-sender-acc')?.value || '').trim();
            const receiverAcc = (document.getElementById('ocr-field-receiver-acc')?.value || '').trim();
            const sender = (document.getElementById('ocr-field-sender')?.value || '').trim();
            const receiver = (document.getElementById('ocr-field-receiver')?.value || '').trim();
            const phone = (document.getElementById('ocr-field-phone')?.value || '').trim();
            const notes = (document.getElementById('ocr-field-notes')?.value || '').trim();
            return {
                doc_type: docType,
                amount: amount,
                bank_name: bankName,
                transaction_ref: refNo,
                cheque_number: refNo,
                date: date,
                due_date: dueDate,
                sender_account: senderAcc,
                receiver_account: receiverAcc,
                sender_name: sender,
                receiver_name: receiver,
                receiver_phone: phone,
                notes: notes
            };
        },

        // 7. Apply Extracted Data to Active Screen (Safe Clean-Only Batch Commit)
        applyToActiveScreen() {
            this.syncCurrentItemFromForm();

            // Strict Accounting Safety Lock: Strictly block premature commits while background scan is in progress
            if (this.isStreamingInBackground) {
                this.playWarningBeep();
                this.showToast(`🛡️ صمام الأمان المحاسبي مفعل: لا يمكن اعتماد أو تفريغ السند حتى اكتمال فحص جميع المستندات في الخلفية (${this.batchItems.length} من ${this.totalFilesCount || this.batchItems.length}) لمنع أي إسقاط مالي!`, 'warning');
                return;
            }

            const allItems = (this.batchItems && this.batchItems.length > 0)
                ? this.batchItems
                : [{ data: this.getCurrentFormData() }];

            // Strict Accounting Safety Filter: Strictly segregate clean items from duplicates, unapproved, unclear, and non-financial docs
            const cleanItems = [];
            const duplicateItems = [];
            const unapprovedOrUnclearItems = [];
            const nonFinancialItems = unapprovedOrUnclearItems;

            allItems.forEach((it, idx) => {
                const doc = it.data;
                const isNonFin = !!(doc.is_non_financial || doc.doc_type === 'non_financial');
                const amt = parseFloat(doc.amount) || 0;
                const isAmtZero = (amt <= 0);
                const ref = (doc.transaction_ref || doc.cheque_number || '').trim();
                const res = this.validateReference(ref, idx);

                if (isNonFin || isAmtZero) {
                    unapprovedOrUnclearItems.push({
                        doc,
                        reason: doc.rejection_reason || (isAmtZero ? 'المستند غير واضح، المبلغ غير ظاهر (0.00 SDG)' : 'مستند غير معتمد أو صورة غير مالية')
                    });
                } else if (!res.valid && res.isDuplicate) {
                    duplicateItems.push({ doc, reason: res.message });
                } else {
                    cleanItems.push(doc);
                }
            });

            // Absolute Accounting Blocker: If NO clean items exist in batch, prevent save
            // "وزر اعتماد لا يعتمد، أي لا يعتمد في هذه الحالة نهائيا"
            if (cleanItems.length === 0) {
                this.playWarningBeep();
                let haltMsg = `🚫 حظر محاسبي قطعي (زر الاعتماد لا يعتمد نهائياً):\n\n`;
                if (unapprovedOrUnclearItems.length > 0) {
                    haltMsg += `• تم رصد (${unapprovedOrUnclearItems.length}) مستند غير واضح والمبلغ غير ظاهر (0.00 SDG)، أو صورة سيارة.\n`;
                }
                if (duplicateItems.length > 0) {
                    haltMsg += `• تم رصد (${duplicateItems.length}) إشعار مكرر مسجل مسبقاً بالنظام.\n`;
                }
                haltMsg += `\n❌ زر الاعتماد لا يعتمد هذه المستندات نهائياً لحماية الحسابات المالية ومنع ترحيل قيود غير مكتملة أو مكررة.\nيرجى استبعاد المستندات غير المعتمدة أو تصحيح المبالغ يدوياً للمتابعة.`;
                alert(haltMsg);
                return;
            }

            // If some duplicates or unapproved/unclear items were detected, alert the accountant that only clean items are saved
            if (duplicateItems.length > 0 || unapprovedOrUnclearItems.length > 0) {
                let infoMsg = `🛡️ صمام الأمان المحاسبي:\n\n`;
                infoMsg += `✅ سيتم حفظ واعتماد (${cleanItems.length}) سند مالي سليم ومعتمد فقط.\n\n`;
                if (unapprovedOrUnclearItems.length > 0) {
                    infoMsg += `⚠️ تم استبعاد وحظر (${unapprovedOrUnclearItems.length}) مستند (المبلغ غير ظاهر أو صورة سيارة) - زر الاعتماد لا يعتمده نهائياً.\n`;
                }
                if (duplicateItems.length > 0) {
                    infoMsg += `🚨 تم استبعاد وحظر (${duplicateItems.length}) سند مكرر حمايةً للحسابات ومنعاً لتكرار الصرف.\n`;
                }
                infoMsg += `\nهل تريد تأكيد اعتماد السندات السليمة واستبعاد غير المعتمد والمكرر تلقائياً؟`;
                if (!confirm(infoMsg)) {
                    return;
                }
            }

            const target = this.activeContext.target || 'purchase';

            if (target === 'purchase') {
                this.applyBatchToPurchase(cleanItems);
            } else if (target === 'sales') {
                this.applyBatchToDirectSales(cleanItems);
            } else if (target === 'single') {
                this.applyBatchToSinglePayment(cleanItems);
            } else if (target === 'multi') {
                this.applyBatchToMultiPayment(cleanItems);
            }

            this.closeReviewModal();
            this.playSuccessSound();
            let toastMsg = `✅ تم حفظ واعتماد (${cleanItems.length}) سنداً سليماً بنجاح!`;
            if (duplicateItems.length > 0) {
                toastMsg += ` (تم استبعاد وحظر ${duplicateItems.length} مكرر)`;
            }
            if (unapprovedOrUnclearItems.length > 0) {
                toastMsg += ` (تم استبعاد وحظر ${unapprovedOrUnclearItems.length} غير معتمد)`;
            }
            this.showToast(toastMsg, 'success');
        },

        // Apply only the currently viewed item in the batch
        applyCurrentItemOnly() {
            if (this.isStreamingInBackground) {
                this.playWarningBeep();
                this.showToast(`🛡️ يرجى الانتظار بضع ثوانٍ حتى اكتمال فحص الدفعة بالكامل قبل اعتماد وتفريغ أي مستند!`, 'warning');
                return;
            }

            this.syncCurrentItemFromForm();
            const currentItem = (this.batchItems && this.batchItems[this.currentBatchIndex])
                ? this.batchItems[this.currentBatchIndex].data
                : this.getCurrentFormData();

            const isNonFin = !!(currentItem.is_non_financial || currentItem.doc_type === 'non_financial');
            const amt = parseFloat(currentItem.amount) || 0;
            const isZeroAmt = (amt <= 0);

            // زر اعتماد لا يعتمد، أي لا يعتمد في هذه الحالة نهائياً
            if (isNonFin || isZeroAmt) {
                this.playWarningBeep();
                const reason = currentItem.rejection_reason || (isZeroAmt ? 'المستند غير واضح، المبلغ غير ظاهر (0.00 SDG)' : 'صورة سيارة أو مستند غير مالي');
                alert(`🚫 حظر محاسبي قطعي:\n\nزر الاعتماد لا يعتمد هذا المستند نهائياً (المبلغ غير ظاهر أو المستند غير مالي) لحماية الحسابات المالية!\n\nسبب الحظر:\n• ${reason}\n\nيجب استبعاد هذا المستند أو إدخال المبلغ وتصحيح البيانات يدوياً في النموذج قبل الاعتماد.`);
                return;
            }

            const ref = (currentItem.transaction_ref || currentItem.cheque_number || '').trim();
            const res = this.validateReference(ref, this.currentBatchIndex);
            if (!res.valid && res.isDuplicate) {
                this.playWarningBeep();
                alert(`🚫 حظر محاسبي قطعي:\n\n${res.message}\n\nزر الاعتماد لا يعتمد هذا السند نهائياً لأنه لا يمكن اعتماد هذا السند لأنه مكرر مسبقاً بالنظام! تم منع الصرف لحماية الحسابات.`);
                return;
            }

            if (!res.valid && res.isShort) {
                if (!confirm(`⚠️ تنبيه:\n${res.message}\n\nهل تريد اعتماد وتفريغ هذا المستند على أي حال؟`)) {
                    return;
                }
            }

            const target = this.activeContext.target || 'purchase';

            if (target === 'purchase') {
                this.applyBatchToPurchase([currentItem]);
            } else if (target === 'sales') {
                this.applyBatchToDirectSales([currentItem]);
            } else if (target === 'single') {
                this.applyBatchToSinglePayment([currentItem]);
            } else if (target === 'multi') {
                this.applyBatchToMultiPayment([currentItem]);
            }

            this.deleteCurrentItem();
            this.playSuccessSound();
        },

        // Purchase Invoice Handler (Creates N Separate Distinct Rows in Purchase Screen)
        applyBatchToPurchase(items) {
            if (!Array.isArray(items)) items = [items];
            let chequeCount = 0;
            let bankCount = 0;
            let totalAmount = 0;

            const activateSection = (secName) => {
                const btn = document.getElementById('toggle-' + secName);
                if (btn && !btn.classList.contains('active')) {
                    btn.click();
                }
            };

            const cheques = items.filter(d => d.doc_type === 'cheque');
            const transfers = items.filter(d => d.doc_type !== 'cheque');

            // 1. Discharge Bank Transfers into separate rows
            if (transfers.length > 0) {
                activateSection('bank');
                const btList = document.getElementById('bank-transfers-list');
                if (btList) {
                    // Remove any blank placeholder rows
                    const existingRows = Array.from(btList.querySelectorAll('.payment-row-item'));
                    existingRows.forEach(row => {
                        const amtIn = row.querySelector('.bt-amount-input');
                        const val = amtIn ? parseFloat(amtIn.value) : 0;
                        const refIn = row.querySelector('.bt-ref-input');
                        if ((!val || val <= 0) && (!refIn || !refIn.value.trim())) {
                            row.remove();
                        }
                    });

                    // Append each transfer into its own separate row
                    transfers.forEach(doc => {
                        bankCount++;
                        const amt = parseFloat(doc.amount) || 0;
                        totalAmount += amt;

                        if (typeof window.addBankTransferRow === 'function') {
                            window.addBankTransferRow({
                                amount: amt,
                                bankName: doc.bank_name || '',
                                reference: doc.transaction_ref || '',
                                notes: doc.notes || ''
                            });

                            const lastRow = btList.querySelector('.payment-row-item:last-child');
                            if (lastRow) {
                                const bSel = lastRow.querySelector('.bt-bank-select');
                                if (bSel && doc.bank_name) this.selectBankOptionByName(bSel, doc.bank_name);
                            }
                        }
                    });
                }
            }

            // 2. Discharge Cheques into separate rows
            if (cheques.length > 0) {
                activateSection('cheque');
                const chList = document.getElementById('cheques-list');
                if (chList) {
                    // Remove any blank placeholder rows
                    const existingRows = Array.from(chList.querySelectorAll('.payment-row-item'));
                    existingRows.forEach(row => {
                        const amtIn = row.querySelector('.ch-amount-input');
                        const val = amtIn ? parseFloat(amtIn.value) : 0;
                        const noIn = row.querySelector('.ch-no-input');
                        if ((!val || val <= 0) && (!noIn || !noIn.value.trim())) {
                            row.remove();
                        }
                    });

                    // Append each cheque into its own separate row
                    cheques.forEach(doc => {
                        chequeCount++;
                        const amt = parseFloat(doc.amount) || 0;
                        totalAmount += amt;

                        if (typeof window.addChequeRow === 'function') {
                            window.addChequeRow({
                                amount: amt,
                                bankName: doc.bank_name || '',
                                chequeNo: doc.cheque_number || doc.transaction_ref || '',
                                dueDate: doc.due_date || doc.date || '',
                                notes: doc.notes || ''
                            });

                            const lastRow = chList.querySelector('.payment-row-item:last-child');
                            if (lastRow) {
                                const bSel = lastRow.querySelector('.ch-bank-select');
                                if (bSel && doc.bank_name) this.selectBankOptionByName(bSel, doc.bank_name);
                            }
                        }
                    });
                }
            }

            if (typeof window.onPaymentAmountInput === 'function') {
                window.onPaymentAmountInput();
            }
            if (typeof window.recalcPaymentSummary === 'function') {
                window.recalcPaymentSummary();
            }

            const summaryParts = [];
            if (chequeCount > 0) summaryParts.push(`${chequeCount} شيك`);
            if (bankCount > 0) summaryParts.push(`${bankCount} تحويل بنكي`);
            const summaryStr = summaryParts.join(' و ') || `${items.length} مستند`;

            this.showToast(`✅ تم تفريغ ${summaryStr} في ${items.length} أسطر منفصلة بنجاح (الإجمالي: ${Math.round(totalAmount).toLocaleString('en-US')} ج.س)`, 'success');
        },

        applyToPurchase(doc) {
            this.applyBatchToPurchase([doc]);
        },

        // Direct Sales Handler (Creates N Separate Distinct Rows in Direct Sales Screen)
        applyBatchToDirectSales(items) {
            if (!Array.isArray(items)) items = [items];
            let chequeCount = 0;
            let bankCount = 0;
            let totalAmount = 0;

            const activateSection = (secName) => {
                if (typeof window.togglePaymentSection === 'function') {
                    window.togglePaymentSection(secName);
                }
            };

            const cheques = items.filter(d => d.doc_type === 'cheque');
            const transfers = items.filter(d => d.doc_type !== 'cheque');

            // 1. Bank Transfers
            if (transfers.length > 0) {
                activateSection('bank');
                const btList = document.getElementById('bank-transfers-list');
                if (btList) {
                    const existingRows = Array.from(btList.querySelectorAll('.payment-row-item'));
                    existingRows.forEach(row => {
                        const amtIn = row.querySelector('.bt-amount-input');
                        const val = amtIn ? parseFloat(amtIn.value) : 0;
                        const refIn = row.querySelector('.bt-ref-input');
                        if ((!val || val <= 0) && (!refIn || !refIn.value.trim())) {
                            row.remove();
                        }
                    });

                    transfers.forEach(doc => {
                        bankCount++;
                        const amt = parseFloat(doc.amount) || 0;
                        totalAmount += amt;

                        if (typeof window.addBankTransferRow === 'function') {
                            window.addBankTransferRow({
                                amount: amt,
                                bankName: doc.bank_name || '',
                                reference: doc.transaction_ref || '',
                                notes: doc.notes || ''
                            });

                            const lastRow = btList.querySelector('.payment-row-item:last-child');
                            if (lastRow) {
                                const bSel = lastRow.querySelector('.bt-bank-select');
                                if (bSel && doc.bank_name) this.selectBankOptionByName(bSel, doc.bank_name);
                            }
                        }
                    });
                }
            }

            // 2. Cheques
            if (cheques.length > 0) {
                activateSection('cheque');
                const chList = document.getElementById('cheques-list');
                if (chList) {
                    const existingRows = Array.from(chList.querySelectorAll('.payment-row-item'));
                    existingRows.forEach(row => {
                        const amtIn = row.querySelector('.ch-amount-input');
                        const val = amtIn ? parseFloat(amtIn.value) : 0;
                        const noIn = row.querySelector('.ch-no-input');
                        if ((!val || val <= 0) && (!noIn || !noIn.value.trim())) {
                            row.remove();
                        }
                    });

                    cheques.forEach(doc => {
                        chequeCount++;
                        const amt = parseFloat(doc.amount) || 0;
                        totalAmount += amt;

                        if (typeof window.addChequeRow === 'function') {
                            window.addChequeRow({
                                amount: amt,
                                bankName: doc.bank_name || '',
                                chequeNo: doc.cheque_number || doc.transaction_ref || '',
                                dueDate: doc.due_date || doc.date || '',
                                notes: doc.notes || ''
                            });

                            const lastRow = chList.querySelector('.payment-row-item:last-child');
                            if (lastRow) {
                                const bSel = lastRow.querySelector('.ch-bank-select');
                                if (bSel && doc.bank_name) this.selectBankOptionByName(bSel, doc.bank_name);
                            }
                        }
                    });
                }
            }

            if (typeof window.onPaymentAmountInput === 'function') {
                window.onPaymentAmountInput();
            }
            if (typeof window.recalcPaymentSummary === 'function') {
                window.recalcPaymentSummary();
            }

            const summaryParts = [];
            if (chequeCount > 0) summaryParts.push(`${chequeCount} شيك`);
            if (bankCount > 0) summaryParts.push(`${bankCount} تحويل بنكي`);
            const summaryStr = summaryParts.join(' و ') || `${items.length} مستند`;

            this.showToast(`✅ تم تفريغ ${summaryStr} في ${items.length} أسطر منفصلة بنجاح (الإجمالي: ${Math.round(totalAmount).toLocaleString('en-US')} ج.س)`, 'success');
        },

        applyToDirectSales(doc) {
            this.applyBatchToDirectSales([doc]);
        },

        // Single Cash Payment Handler (Creates N Separate Distinct Rows in Voucher)
        applyBatchToSinglePayment(items) {
            if (!Array.isArray(items)) items = [items];
            let count = 0;
            let totalAmount = 0;

            const cheques = items.filter(d => d.doc_type === 'cheque');
            const transfers = items.filter(d => d.doc_type !== 'cheque');

            // 1. Transfers
            if (transfers.length > 0) {
                if (typeof window.togglePaymentSection === 'function') {
                    window.togglePaymentSection('bank');
                }
                const container = document.getElementById('transfers-container');
                if (container) {
                    const rows = Array.from(container.querySelectorAll('.transfer-row'));
                    rows.forEach(r => {
                        const amtIn = r.querySelector('.transfer-amount-input');
                        const val = amtIn ? parseFloat(amtIn.value) : 0;
                        const noIn = r.querySelector('.transfer-no-input');
                        if ((!val || val <= 0) && (!noIn || !noIn.value.trim())) {
                            r.remove();
                        }
                    });
                }

                transfers.forEach(doc => {
                    count++;
                    const amt = parseFloat(doc.amount) || 0;
                    totalAmount += amt;
                    if (typeof window.addTransferRow === 'function') {
                        window.addTransferRow(doc.transaction_ref || '', doc.bank_name || '', amt, doc.notes || '');
                    }
                });
            }

            // 2. Cheques
            if (cheques.length > 0) {
                if (typeof window.togglePaymentSection === 'function') {
                    window.togglePaymentSection('cheque');
                }
                const container = document.getElementById('cheques-container');
                if (container) {
                    const rows = Array.from(container.querySelectorAll('.cheque-row'));
                    rows.forEach(r => {
                        const amtIn = r.querySelector('.cheque-amount-input');
                        const val = amtIn ? parseFloat(amtIn.value) : 0;
                        const noIn = r.querySelector('.cheque-no-input');
                        if ((!val || val <= 0) && (!noIn || !noIn.value.trim())) {
                            r.remove();
                        }
                    });
                }

                cheques.forEach(doc => {
                    count++;
                    const amt = parseFloat(doc.amount) || 0;
                    totalAmount += amt;
                    if (typeof window.addChequeRow === 'function') {
                        window.addChequeRow(doc.bank_name || '', doc.cheque_number || doc.transaction_ref || '', doc.due_date || doc.date || '', amt, doc.notes || '');
                    }
                });
            }

            if (typeof window.recalcPaymentSummary === 'function') {
                window.recalcPaymentSummary();
            }

            this.showToast(`✅ تم تفريغ ${count} أسطر في السند بنجاح (الإجمالي: ${Math.round(totalAmount).toLocaleString('en-US')} ج.س)`, 'success');
        },

        applyToSinglePayment(doc) {
            this.applyBatchToSinglePayment([doc]);
        },

        // Multi Cash Payment Handler (Creates N Separate Distinct Rows in Block)
        applyBatchToMultiPayment(items) {
            if (!Array.isArray(items)) items = [items];
            const container = document.getElementById('accounts-blocks-container');
            if (!container) return;

            let targetBlockId = this.activeContext.blockId;
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

            let totalAmount = 0;
            items.forEach(doc => {
                const amt = parseFloat(doc.amount) || 0;
                totalAmount += amt;
                const isCheque = (doc.doc_type === 'cheque');

                if (isCheque) {
                    const chBtn = document.getElementById('pm-cheque-' + targetBlockId);
                    if (chBtn && !chBtn.classList.contains('active')) chBtn.click();

                    if (typeof window.addChequeRow === 'function') {
                        window.addChequeRow(targetBlockId);
                        const chContainer = document.getElementById('cheque-rows-' + targetBlockId);
                        const lastRow = chContainer?.querySelector('.cheque-row:last-child');
                        if (lastRow) {
                            const amtIn = lastRow.querySelector('.block-cheque-amount');
                            const bankIn = lastRow.querySelector('.block-cheque-bank-input');
                            const noIn = lastRow.querySelector('.block-cheque-no-input');
                            const dateIn = lastRow.querySelector('.block-cheque-date-input');

                            if (amtIn) amtIn.value = Math.round(amt).toLocaleString('en-US');
                            if (bankIn) bankIn.value = doc.bank_name || '';
                            if (noIn) noIn.value = doc.cheque_number || doc.transaction_ref || '';
                            if (dateIn) dateIn.value = doc.due_date || doc.date || '';
                        }
                    }
                } else {
                    const bBtn = document.getElementById('pm-bank-' + targetBlockId);
                    if (bBtn && !bBtn.classList.contains('active')) bBtn.click();

                    if (typeof window.addBankRow === 'function') {
                        window.addBankRow(targetBlockId);
                        const bContainer = document.getElementById('bank-rows-' + targetBlockId);
                        const lastRow = bContainer?.querySelector('.bank-row:last-child');
                        if (lastRow) {
                            const amtIn = lastRow.querySelector('.block-bank-amount');
                            const bankIn = lastRow.querySelector('.block-bank-name-input');
                            const noIn = lastRow.querySelector('.block-transfer-no-input');
                            const descIn = lastRow.querySelector('.block-bank-desc-input');

                            if (amtIn) amtIn.value = Math.round(amt).toLocaleString('en-US');
                            if (bankIn) bankIn.value = doc.bank_name || '';
                            if (noIn) noIn.value = doc.transaction_ref || '';
                            if (descIn) descIn.value = doc.notes || '';
                        }
                    }
                }
            });

            if (typeof window.recalcBlockTotal === 'function') {
                window.recalcBlockTotal(targetBlockId);
            }
            if (typeof window.calcTotal === 'function') {
                window.calcTotal();
            }

            this.showToast(`✅ تم تفريغ ${items.length} أسطر في كتلة الحساب بنجاح (الإجمالي: ${Math.round(totalAmount).toLocaleString('en-US')} ج.س)`, 'success');
        },

        applyToMultiPayment(doc) {
            this.applyBatchToMultiPayment([doc]);
        },

        // Helper to match bank in dropdown by name or partial text
        selectBankOptionByName(selectElem, bankName) {
            if (!selectElem || !bankName) return;
            const clean = bankName.replace(/(بنك|مصرف|اسلامي|الاسلامي|التجاري|الوطني)/g, '').trim().toLowerCase();
            for (let i = 0; i < selectElem.options.length; i++) {
                const opt = selectElem.options[i];
                const optText = opt.textContent.toLowerCase();
                if (optText.includes(bankName.toLowerCase()) || (clean.length >= 3 && optText.includes(clean))) {
                    selectElem.selectedIndex = i;
                    break;
                }
            }
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

        // 7. Loading Overlay Helper with Timer & Progress
        showLoading(show, message = '', current = 0, total = 0) {
            const loadingElem = document.getElementById('financial-ocr-loading');
            const msgElem = document.getElementById('financial-ocr-loading-msg');
            const countElem = document.getElementById('financial-ocr-loading-count');
            const bar = document.getElementById('financial-ocr-loading-progress-bar');
            const timerElem = document.getElementById('financial-ocr-loading-timer');
            if (!loadingElem) return;

            if (show) {
                if (loadingElem.parentElement !== document.body) {
                    document.body.appendChild(loadingElem);
                }
                document.body.classList.add('ocr-modal-active');
                document.documentElement.classList.add('ocr-modal-active');

                if (msgElem && message) msgElem.textContent = message;
                if (countElem) countElem.textContent = total > 1 ? `${current} / ${total}` : '1 / 1';
                if (bar) bar.style.width = '10%';

                this.loadingStartTime = performance.now();
                if (this.loadingTimerInterval) clearInterval(this.loadingTimerInterval);
                this.loadingTimerInterval = setInterval(() => {
                    const elapsed = ((performance.now() - this.loadingStartTime) / 1000).toFixed(1);
                    if (timerElem) timerElem.textContent = elapsed + 's';
                }, 100);

                loadingElem.style.display = 'flex';
            } else {
                if (this.loadingTimerInterval) {
                    clearInterval(this.loadingTimerInterval);
                    this.loadingTimerInterval = null;
                }
                loadingElem.style.display = 'none';
                const reviewModal = document.getElementById('financial-ocr-review-modal');
                if (!reviewModal || reviewModal.style.display !== 'flex') {
                    document.body.classList.remove('ocr-modal-active');
                    document.documentElement.classList.remove('ocr-modal-active');
                }
            }
        },

        updateLoadingProgress(percent, current, total) {
            const bar = document.getElementById('financial-ocr-loading-progress-bar');
            const countElem = document.getElementById('financial-ocr-loading-count');
            const msgElem = document.getElementById('financial-ocr-loading-msg');
            if (bar) bar.style.width = Math.max(10, percent) + '%';
            if (countElem) countElem.textContent = `${current} / ${total}`;
            if (msgElem) msgElem.textContent = `تم فحص ${current} من ${total} بالرؤية الحاسوبية فائقة السرعة... ⚡`;
        },

        // 8. Interactive High-Resolution Image Zoom & Pan Lightbox
        zoomState: {
            scale: 1,
            translateX: 0,
            translateY: 0,
            rotation: 0,
            isContrast: false,
            isDragging: false,
            startX: 0,
            startY: 0
        },

        openZoomViewer() {
            const modal = document.getElementById('financial-ocr-zoom-modal');
            const img = document.getElementById('ocr-zoom-img');
            const titleElem = document.getElementById('ocr-zoom-title');
            const metaElem = document.getElementById('ocr-zoom-meta');
            if (!modal || !img) return;

            const currentSrc = (this.batchItems && this.batchItems[this.currentBatchIndex]?.base64) 
                || this.currentImageBase64;

            if (!currentSrc) {
                this.showToast('لا توجد صورة شيك لعرضها', 'warning');
                return;
            }

            img.src = currentSrc;

            if (this.batchItems && this.batchItems.length > 0) {
                const current = this.batchItems[this.currentBatchIndex];
                const isCheque = current.data.doc_type === 'cheque';
                const amt = parseFloat(current.data.amount) || 0;
                if (titleElem) titleElem.textContent = `تدقيق صورة ${isCheque ? 'الشيك المصرفي' : 'إشعار التحويل'} - ${current.data.bank_name || ''}`;
                if (metaElem) metaElem.textContent = `المستند ${this.currentBatchIndex + 1} من ${this.batchItems.length} • المبلغ: ${amt > 0 ? Math.round(amt).toLocaleString('en-US') + ' SDG' : 'غير محدد'}`;
            } else {
                if (titleElem) titleElem.textContent = 'تدقيق وتكبير صورة الشيك / الإشعار';
                if (metaElem) metaElem.textContent = 'المستند المالي الممسوح';
            }

            // Ensure modal is directly under document.body to ensure correct stacking context
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.style.zIndex = '2147483647';
            modal.style.display = 'flex';

            this.zoomState.scale = 1;
            this.zoomState.translateX = 0;
            this.zoomState.translateY = 0;
            this.zoomState.rotation = this.previewRotation || 0;
            this.zoomState.isContrast = false;
            this.zoomState.isDragging = false;
            this.updateZoomTransform();

            this.bindZoomEvents();
        },

        closeZoomViewer() {
            const modal = document.getElementById('financial-ocr-zoom-modal');
            if (modal) modal.style.display = 'none';
            this.unbindZoomEvents();
        },

        zoomIn() {
            this.zoomState.scale = Math.min(this.zoomState.scale * 1.3, 6);
            this.updateZoomTransform();
        },

        zoomOut() {
            this.zoomState.scale = Math.max(this.zoomState.scale / 1.3, 0.4);
            this.updateZoomTransform();
        },

        zoomReset() {
            this.zoomState.scale = 1;
            this.zoomState.translateX = 0;
            this.zoomState.translateY = 0;
            this.updateZoomTransform();
        },

        zoomFit() {
            this.zoomState.scale = 0.9;
            this.zoomState.translateX = 0;
            this.zoomState.translateY = 0;
            this.updateZoomTransform();
        },

        rotateImage() {
            this.zoomState.rotation = (this.zoomState.rotation + 90) % 360;
            this.updateZoomTransform();
        },

        toggleContrastFilter() {
            this.zoomState.isContrast = !this.zoomState.isContrast;
            const btn = document.getElementById('btn-toggle-contrast');
            if (btn) {
                if (this.zoomState.isContrast) {
                    btn.style.background = '#8b5cf6';
                    btn.style.color = '#fff';
                    btn.textContent = '✓ إيضاح نشط';
                } else {
                    btn.style.background = 'rgba(168,85,247,0.15)';
                    btn.style.color = '#d8b4fe';
                    btn.textContent = '🌓 تباين عالي';
                }
            }
            this.updateZoomTransform();
        },

        updateZoomTransform() {
            const img = document.getElementById('ocr-zoom-img');
            if (!img) return;
            const filter = this.zoomState.isContrast ? 'contrast(190%) brightness(95%) grayscale(25%) drop-shadow(0 15px 35px rgba(0,0,0,0.8))' : 'drop-shadow(0 15px 35px rgba(0,0,0,0.8))';
            img.style.filter = filter;
            img.style.transform = `translate(${this.zoomState.translateX}px, ${this.zoomState.translateY}px) scale(${this.zoomState.scale}) rotate(${this.zoomState.rotation}deg)`;
            
            const badge = document.getElementById('ocr-zoom-scale-badge');
            if (badge) {
                badge.textContent = `${Math.round(this.zoomState.scale * 100)}%`;
            }
        },

        _zoomHandlers: null,

        bindZoomEvents() {
            const viewport = document.getElementById('ocr-zoom-viewport');
            if (!viewport) return;

            this.unbindZoomEvents();

            const onWheel = (e) => {
                e.preventDefault();
                e.stopPropagation();
                const factor = (e.deltaY < 0) ? 1.18 : (1 / 1.18);
                const currentScale = this.zoomState.scale;
                const newScale = Math.min(Math.max(currentScale * factor, 0.4), 8.0);

                const rect = viewport.getBoundingClientRect();
                const mouseX = e.clientX - (rect.left + rect.width / 2);
                const mouseY = e.clientY - (rect.top + rect.height / 2);

                const scaleRatio = newScale / currentScale;
                this.zoomState.translateX = mouseX - (mouseX - this.zoomState.translateX) * scaleRatio;
                this.zoomState.translateY = mouseY - (mouseY - this.zoomState.translateY) * scaleRatio;
                this.zoomState.scale = newScale;

                this.updateZoomTransform();
            };

            const onMouseDown = (e) => {
                if (e.button !== 0) return;
                this.zoomState.isDragging = true;
                this.zoomState.startX = e.clientX - this.zoomState.translateX;
                this.zoomState.startY = e.clientY - this.zoomState.translateY;
                viewport.style.cursor = 'grabbing';
            };

            const onMouseMove = (e) => {
                if (!this.zoomState.isDragging) return;
                this.zoomState.translateX = e.clientX - this.zoomState.startX;
                this.zoomState.translateY = e.clientY - this.zoomState.startY;
                this.updateZoomTransform();
            };

            const onMouseUp = () => {
                this.zoomState.isDragging = false;
                viewport.style.cursor = 'grab';
            };

            const onDblClick = (e) => {
                e.preventDefault();
                if (this.zoomState.scale > 1.2) {
                    this.zoomReset();
                } else {
                    this.zoomState.scale = 2.5;
                    this.updateZoomTransform();
                }
            };

            const onKeyDown = (e) => {
                if (e.key === 'Escape') this.closeZoomViewer();
                if (e.key === '+' || e.key === '=') this.zoomIn();
                if (e.key === '-') this.zoomOut();
                if (e.key === 'r' || e.key === 'R') this.rotateImage();
            };

            viewport.addEventListener('wheel', onWheel, { passive: false });
            viewport.addEventListener('mousedown', onMouseDown);
            viewport.addEventListener('dblclick', onDblClick);
            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('mouseup', onMouseUp);
            window.addEventListener('keydown', onKeyDown);

            this._zoomHandlers = { onWheel, onMouseDown, onMouseMove, onMouseUp, onDblClick, onKeyDown };
        },

        unbindZoomEvents() {
            const viewport = document.getElementById('ocr-zoom-viewport');
            if (this._zoomHandlers) {
                if (viewport) {
                    viewport.removeEventListener('wheel', this._zoomHandlers.onWheel);
                    viewport.removeEventListener('mousedown', this._zoomHandlers.onMouseDown);
                    if (this._zoomHandlers.onDblClick) {
                        viewport.removeEventListener('dblclick', this._zoomHandlers.onDblClick);
                    }
                }
                window.removeEventListener('mousemove', this._zoomHandlers.onMouseMove);
                window.removeEventListener('mouseup', this._zoomHandlers.onMouseUp);
                window.removeEventListener('keydown', this._zoomHandlers.onKeyDown);
                this._zoomHandlers = null;
            }
        },

        zoomImage(src) {
            this.openZoomViewer();
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
            toast.className = 'ocr-toast';
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

    // Global Keyboard Navigation for Review Modal (ArrowRight = Previous in RTL, ArrowLeft = Next in RTL)
    window.addEventListener('keydown', function(e) {
        const reviewModal = document.getElementById('financial-ocr-review-modal');
        if (!reviewModal || reviewModal.style.display !== 'flex') return;

        // Ignore when user is actively typing in inputs
        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        if (activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select') return;

        if (e.key === 'ArrowRight') {
            FinancialOCR.prevItem();
        } else if (e.key === 'ArrowLeft') {
            FinancialOCR.nextItem();
        } else if (e.key === 'Escape') {
            const zoomModal = document.getElementById('financial-ocr-zoom-modal');
            if (zoomModal && zoomModal.style.display === 'flex') {
                FinancialOCR.closeZoomViewer();
            } else {
                FinancialOCR.closeReviewModal();
            }
        }
    });

    window.FinancialOCR = FinancialOCR;

    // Initialize custom field properties, contextmenu bindings, and global paste listener on load
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof FinancialOCR.initFieldProps === 'function') {
            FinancialOCR.initFieldProps();
        }
        if (typeof FinancialOCR.initGlobalPasteListener === 'function') {
            FinancialOCR.initGlobalPasteListener();
        }
    });
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        if (typeof FinancialOCR.initFieldProps === 'function') {
            FinancialOCR.initFieldProps();
        }
        if (typeof FinancialOCR.initGlobalPasteListener === 'function') {
            FinancialOCR.initGlobalPasteListener();
        }
    }

})(window);
