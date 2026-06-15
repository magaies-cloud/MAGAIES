// js/checks-cycle.js

let currentTab = 'incoming';

// Mock Data representing the Checks Database
let checksDB = [
    { id: 'CHQ-9902', type: 'incoming', partner: 'شريهان أحمد', bank: 'بنك الخرطوم', amount: 45000.00, dueDate: '2026-05-15', status: 'في الخزنة' },
    { id: 'CHQ-9903', type: 'incoming', partner: 'محمد الفاتح', bank: 'بنك أمدرمان الوطني', amount: 12000.50, dueDate: '2026-05-10', status: 'برسم التحصيل' },
    { id: 'CHQ-9904', type: 'incoming', partner: 'شركة الرواد', bank: 'بنك فيصل الإسلامي', amount: 85000.00, dueDate: '2026-05-20', status: 'محصل' },
    { id: 'CHQ-9905', type: 'incoming', partner: 'أحمد كمال', bank: 'بنك الخرطوم', amount: 3000.00, dueDate: '2026-05-01', status: 'مرتجع' },
    
    { id: 'OUT-1001', type: 'outgoing', partner: 'مورد التقنية', bank: 'بنك النيلين', amount: 50000.00, dueDate: '2026-06-01', status: 'غير مسحوب' },
    { id: 'OUT-1002', type: 'outgoing', partner: 'الشركة العالمية', bank: 'بنك فيصل الإسلامي', amount: 120000.00, dueDate: '2026-05-25', status: 'مسحوب' }
];

document.addEventListener('DBReady', () => {
    // Initial Render
    renderTable();
});

function switchTab(tabName) {
    currentTab = tabName;
    
    // Update Tab UI
    document.getElementById('tab-incoming').classList.remove('active');
    document.getElementById('tab-outgoing').classList.remove('active');
    document.getElementById('tab-' + tabName).classList.add('active');

    // Update Headers
    document.getElementById('th-partner').innerText = tabName === 'incoming' ? 'العميل (Customer)' : 'المورد (Supplier)';

    // Update Action Bars
    document.getElementById('incoming-actions').style.display = tabName === 'incoming' ? 'flex' : 'none';
    document.getElementById('outgoing-actions').style.display = tabName === 'outgoing' ? 'flex' : 'none';

    // Reset Filters
    document.getElementById('filter-status').value = 'all';
    document.getElementById('filter-date').value = '';

    renderTable();
}

function getStatusClass(status) {
    switch (status) {
        case 'في الخزنة': case 'غير مسحوب': return 'status-in-safe';
        case 'برسم التحصيل': return 'status-collection';
        case 'محصل': case 'مسحوب': return 'status-cleared';
        case 'مرتجع': return 'status-bounced';
        case 'مظهر': return 'status-endorsed';
        default: return 'status-in-safe';
    }
}

function renderTable() {
    const tbody = document.getElementById('checks-tbody');
    const filterStatus = document.getElementById('filter-status').value;
    const filterDate = document.getElementById('filter-date').value;

    tbody.innerHTML = '';

    let filtered = checksDB.filter(c => c.type === currentTab);

    if (filterStatus !== 'all') {
        filtered = filtered.filter(c => c.status === filterStatus);
    }
    
    if (filterDate) {
        filtered = filtered.filter(c => new Date(c.dueDate) <= new Date(filterDate));
    }

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 30px; color: var(--text-muted);">لا توجد شيكات مطابقة للبحث</td></tr>`;
        return;
    }

    filtered.forEach(check => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="checkbox" class="row-checkbox check-select" data-id="${check.id}"></td>
            <td style="font-weight: 800;">${check.id}</td>
            <td>${check.partner}</td>
            <td>${check.bank}</td>
            <td class="amount-col">${check.amount.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
            <td>${check.dueDate}</td>
            <td><span class="status-badge ${getStatusClass(check.status)}">${check.status}</span></td>
        `;
        tbody.appendChild(tr);
    });

    // Reset select all checkbox
    document.getElementById('selectAll').checked = false;
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.check-select');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

function changeSelectedStatus(newStatus) {
    const selected = Array.from(document.querySelectorAll('.check-select:checked')).map(cb => cb.dataset.id);
    
    if (selected.length === 0) {
        alert('الرجاء تحديد شيك واحد على الأقل أولاً.');
        return;
    }

    if (confirm(`هل أنت متأكد من تغيير حالة الشيكات المحددة إلى "${newStatus}"؟`)) {
        checksDB.forEach(c => {
            if (selected.includes(c.id)) {
                c.status = newStatus;
            }
        });
        
        renderTable();
        // Here you would trigger an alert/toast from app.js if available
        if (typeof showToast === 'function') {
            showToast('success', 'تم تحديث حالة الشيكات بنجاح');
        } else {
            alert('تم تحديث حالة الشيكات بنجاح!');
        }
    }
}
