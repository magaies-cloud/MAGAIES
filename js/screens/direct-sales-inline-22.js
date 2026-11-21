window.initDesktopMirrorSync = (function initDesktopSalesMirrorSync() {
        let isSyncing = false;
        async function syncSalesWithMariaDB() {
            if (isSyncing) return;
            isSyncing = true;
            try {
                if (navigator.onLine && window.MagaiesOffline && typeof MagaiesOffline.flushQueue === 'function') {
                    await MagaiesOffline.flushQueue();
                }
                const res = await fetch('api/mirror_sync.php?action=get_recent_sales&_t=' + Date.now());
                const data = await res.json();
                if (data && data.success && Array.isArray(data.sales)) {
                    let localSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
                    let localMap = new Map();
                    localSales.forEach(inv => {
                        const no = inv.invoiceNo || inv.id;
                        if (no) localMap.set(String(no), inv);
                    });

                    let hasNew = false;
                    data.sales.forEach(serverInv => {
                        const sNo = String(serverInv.invoiceNo || serverInv.id);
                        if (!sNo) return;
                        if (!localMap.has(sNo)) {
                            localStorage.setItem('erp_inv_' + sNo, JSON.stringify(serverInv));
                            localSales.push(serverInv);
                            localMap.set(sNo, serverInv);
                            hasNew = true;
                        } else {
                            const cur = localMap.get(sNo);
                            const idx = localSales.findIndex(x => String(x.invoiceNo || x.id) === sNo);
                            if (idx >= 0) {
                                const srvDelivered = serverInv.isDelivered === true || serverInv.deliveryStatus === 'delivered';
                                const curDelivered = cur.isDelivered === true || cur.deliveryStatus === 'delivered';
                                const statusChanged = (srvDelivered !== curDelivered || serverInv.deliveryStatus !== cur.deliveryStatus);
                                if (statusChanged) {
                                    hasNew = true;
                                }
                                localSales[idx] = Object.assign({}, serverInv, cur, {
                                    isDelivered: srvDelivered || curDelivered,
                                    deliveryStatus: (srvDelivered || curDelivered) ? 'delivered' : (serverInv.deliveryStatus || cur.deliveryStatus || 'pending'),
                                    deliveryNo: serverInv.deliveryNo || cur.deliveryNo || '',
                                    deliveredAt: serverInv.deliveredAt || cur.deliveredAt || '',
                                    items: (Array.isArray(cur.items) && cur.items.some(i => i.deliveredQty > 0)) ? cur.items : ((Array.isArray(serverInv.items) && serverInv.items.length > 0) ? serverInv.items : cur.items)
                                });
                                if (statusChanged) {
                                    localStorage.setItem('erp_inv_' + sNo, JSON.stringify(localSales[idx]));
                                }
                            }
                        }
                    });

                    if (hasNew) {
                        localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(localSales));
                        localStorage.setItem('magaies_local_sales_invoices_v1', JSON.stringify(localSales));
                        if (typeof loadHubTransactions === 'function' && typeof currentSalesView !== 'undefined' && currentSalesView === 'hub') {
                            loadHubTransactions();
                        }
                        const delViewEl = document.getElementById('delivery-hub-view');
                        if (delViewEl && delViewEl.style.display !== 'none' && typeof window.loadDeliveryTransactions === 'function') {
                            window.loadDeliveryTransactions();
                        }
                    }

                    const invInput = document.getElementById('hdr-inv-no');
                    const invSpan = document.getElementById('inv-number');
                    if (data.nextInvoiceNo) {
                        window._mirrorNextSalesNo = data.nextInvoiceNo;
                    }
                    const isCreatingNew = !window.currentViewedInvoiceNo || window.currentViewedInvoiceNo === '---';
                    const bestNextNo = (typeof peekNextInvoiceNumber === 'function') ? peekNextInvoiceNumber() : (data.nextInvoiceNo || '');
                    if (isCreatingNew && bestNextNo && invInput && invSpan) {
                        if (invInput.value !== bestNextNo) {
                            invInput.value = bestNextNo;
                            invSpan.textContent = bestNextNo;
                        }
                    }
                }
            } catch(e) {
                console.warn('Desktop sales mirror sync check:', e);
            } finally {
                isSyncing = false;
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(syncSalesWithMariaDB, 600);
        });
        setInterval(() => { if (!document.hidden && navigator.onLine) syncSalesWithMariaDB(); }, 45000);
        window.addEventListener('focus', syncSalesWithMariaDB);
        window.addEventListener('online', syncSalesWithMariaDB);
        return syncSalesWithMariaDB;
    })();
