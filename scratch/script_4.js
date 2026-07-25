
    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.getElementById('ledger-tbody');
        const menu = document.getElementById('ledger-context-menu');
        const btnShowDoc = document.getElementById('btn-show-doc');
        const btnToggleReview = document.getElementById('btn-toggle-review');
        const actionText = document.getElementById('doc-action-text');

        let currentType = '';
        let currentRef = '';
        let currentCell = null;

        window.openLedgerContextMenu = function(e, type, ref, cellEl = null) {
            e.preventDefault();
            e.stopPropagation();

            const btnGoTo = document.getElementById('btn-go-to-ledger');
            if (cellEl || !e.target.closest('.counter-acc-cell')) {
                if (btnGoTo) btnGoTo.style.display = 'none';
            }

            if (type === 'opening' && (!btnGoTo || btnGoTo.style.display === 'none')) {
                if (menu) menu.style.display = 'none';
                return;
            }

            currentType = type;
            currentRef = ref;
            currentCell = cellEl;

            // Map type to localized name
            let typeName = 'المستند';
            if (type === 'invoice') typeName = 'فاتورة المبيعات';
            else if (type === 'purchase') typeName = 'فاتورة المشتريات';
            else if (type === 'jv') typeName = 'قيد اليومية';
            else if (type === 'receipt') typeName = 'سند القبض';
            else if (type === 'payment') typeName = 'سند الصرف';

            actionText.textContent = `عرض ${typeName} (#${ref})`;

            // Show/hide toggle review button
            if (btnToggleReview) {
                btnToggleReview.style.display = cellEl ? 'flex' : 'none';
            }

            menu.style.display = 'block';
            
            // Position menu at mouse cursor
            let left = e.clientX;
            let top = e.clientY;
            if (left + 240 > window.innerWidth) left = window.innerWidth - 250;
            const menuHeight = cellEl ? 100 : 50;
            if (top + menuHeight > window.innerHeight) top = window.innerHeight - (menuHeight + 10);

            menu.style.left = left + 'px';
            menu.style.top = top + 'px';
        };

        if (tbody) {
            // Context menu delegation for the row (outside number cells)
            tbody.addEventListener('contextmenu', (e) => {
                // If it was clicked on a number cell wrapper, let the showContextMenu handle it
                if (e.target.closest('.num-cell-wrapper')) return;

                const tr = e.target.closest('tr');
                if (!tr) return;

                const isOpening = tr.getAttribute('data-is-opening') === 'true';
                const type = tr.getAttribute('data-type');
                const ref = tr.getAttribute('data-ref');

                const counterCell = e.target.closest('.counter-acc-cell');
                const btnGoTo = document.getElementById('btn-go-to-ledger');

                if (counterCell) {
                    const accId = counterCell.getAttribute('data-acc-id');
                    const accName = counterCell.getAttribute('data-acc-name');
                    if (accId && accId !== '-' && accId !== '') {
                        e.preventDefault();
                        e.stopPropagation();

                        if (btnGoTo) {
                            btnGoTo.style.display = 'flex';
                            const actionTxt = document.getElementById('ledger-action-text');
                            if (actionTxt) actionTxt.textContent = `دفتر أستاذ: ${accName}`;
                            btnGoTo.onclick = () => {
                                window.selectAccount(accId, accName, '🏷️');
                                if (menu) menu.style.display = 'none';
                            };
                        }

                        window.openLedgerContextMenu(e, type || 'opening', ref || 'OPENING', null);
                        return;
                    }
                }

                if (btnGoTo) btnGoTo.style.display = 'none';

                if (isOpening || !type || !ref) {
                    if (menu) menu.style.display = 'none';
                    return;
                }

                window.openLedgerContextMenu(e, type, ref, null);
            });
        }

        if (btnShowDoc) {
            btnShowDoc.addEventListener('click', () => {
                if (typeof openDrillDown === 'function') {
                    openDrillDown(currentType, currentRef);
                }
                menu.style.display = 'none';
            });
        }

        if (btnToggleReview) {
            btnToggleReview.addEventListener('click', () => {
                if (currentCell) {
                    currentCell.classList.toggle('reviewed-cell');
                }
                menu.style.display = 'none';
            });
        }

        document.addEventListener('click', (e) => {
            if (menu && !menu.contains(e.target)) {
                menu.style.display = 'none';
            }
        });
    });
