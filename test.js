

        // --- Column-Specific Grid Customization ---
        let backupGrnGridPrefs = null;
        let grnGridPrefs = {
            decimals: 2,
            thousands: true,
            cols: {
                item: { size: 14, color: '#ffffff', bold: false },
                unit: { size: 12, color: '#94a3b8', bold: false },
                qty: { size: 14, color: '#ffffff', bold: false },
                rcvQty: { size: 14, color: '#ffffff', bold: false },
                warehouse: { size: 13, color: '#ffffff', bold: false }
            }
        };

        try {
            const stored = localStorage.getItem('erp_grn_col_prefs');
            if (stored) {
                const parsed = JSON.parse(stored);
                grnGridPrefs = { ...grnGridPrefs, ...parsed };
            }
        } catch(e) {} updateGrnStyles();

        
        function updateGrnStyles() {
            let styleEl = document.getElementById('grn-dynamic-styles');
            if (!styleEl) {
                styleEl = document.createElement('style');
                styleEl.id = 'grn-dynamic-styles';
                document.head.appendChild(styleEl);
            }
            
            let css = '';
            const cols = grnGridPrefs.cols;
            if(cols.item) css += `.grn-col-item { font-size: ${cols.item.size}px !important; color: ${cols.item.color} !important; ${cols.item.bold ? 'font-weight: 900 !important;' : ''} }\n`;
            if(cols.unit) css += `.grn-col-unit { font-size: ${cols.unit.size}px !important; color: ${cols.unit.color} !important; ${cols.unit.bold ? 'font-weight: 900 !important;' : ''} }\n`;
            if(cols.qty) css += `.grn-col-qty { font-size: ${cols.qty.size}px !important; color: ${cols.qty.color} !important; ${cols.qty.bold ? 'font-weight: 900 !important;' : ''} }\n`;
            if(cols.rcvQty) css += `.grn-col-rcvQty { font-size: ${cols.rcvQty.size}px !important; color: ${cols.rcvQty.color} !important; ${cols.rcvQty.bold ? 'font-weight: 900 !important;' : ''} }\n`;
            if(cols.warehouse) css += `.grn-col-warehouse { font-size: ${cols.warehouse.size}px !important; color: ${cols.warehouse.color} !important; ${cols.warehouse.bold ? 'font-weight: 900 !important;' : ''} }\n`;
            
            styleEl.innerHTML = css;
        }

        function openGrnSettings() {
            document.getElementById('grn-settings-modal').style.display = 'flex';
            document.getElementById('grn-global-thousands').checked = grnGridPrefs.thousands;
            document.getElementById('grn-global-decimals').value = grnGridPrefs.decimals;
            loadColSettings();
        }

        function closeGrnSettings() {
            document.getElementById('grn-settings-modal').style.display = 'none';
            if (backupGrnGridPrefs) {
                grnGridPrefs = JSON.parse(JSON.stringify(backupGrnGridPrefs));
                backupGrnGridPrefs = null;
                updateGrnStyles();
                renderReceiptLines();
            }
        }

                function loadColSettings() {
            const col = document.getElementById('grn-col-select').value;
            const prefs = grnGridPrefs.cols[col] || { size: 14, color: '#ffffff', bold: false };
            document.getElementById('grn-col-size-val').textContent = prefs.size;
            document.getElementById('grn-col-color').value = prefs.color;
            const boldBtn = document.getElementById('grn-col-bold-btn');
            if (boldBtn) {
                if (prefs.bold) {
                    boldBtn.style.background = 'rgba(56, 189, 248, 0.3)';
                    boldBtn.style.color = '#fff';
                    boldBtn.style.borderColor = '#38bdf8';
                } else {
                    boldBtn.style.background = '';
                    boldBtn.style.color = '';
                    boldBtn.style.borderColor = '';
                }
            }
        }

        
        function toggleSelectedColBold() {
            const col = document.getElementById('grn-col-select').value;
            if (grnGridPrefs.cols[col].bold === undefined) grnGridPrefs.cols[col].bold = false;
            grnGridPrefs.cols[col].bold = !grnGridPrefs.cols[col].bold;
            loadColSettings(); // update button ui
            updateGrnStyles();
        }

        function changeSelectedColSize(delta) {
            const col = document.getElementById('grn-col-select').value;
            let currentSize = grnGridPrefs.cols[col].size;
            currentSize = Math.max(10, Math.min(30, currentSize + delta));
            grnGridPrefs.cols[col].size = currentSize;
            document.getElementById('grn-col-size-val').textContent = currentSize;
            updateGrnStyles();
            renderReceiptLines();
        }

        function updateSelectedColColor() {
            const col = document.getElementById('grn-col-select').value;
            grnGridPrefs.cols[col].color = document.getElementById('grn-col-color').value;
            updateGrnStyles();
            renderReceiptLines();
        }

        function updateGrnGlobalPrefs() {
            grnGridPrefs.thousands = document.getElementById('grn-global-thousands').checked;
            grnGridPrefs.decimals = parseInt(document.getElementById('grn-global-decimals').value) || 0;
            renderReceiptLines();
        }

        function saveGrnSettings() {
            localStorage.setItem('erp_grn_col_prefs', JSON.stringify(grnGridPrefs));
            closeGrnSettings();
            if (typeof showToast === 'function') {
                showToast('تم حفظ إعدادات الجدول بنجاح!', 'success');
            } else {
                alert('تم حفظ إعدادات الجدول بنجاح!');
            }
        }
        // ------------------------------------------

