document.addEventListener('DBReady', () => {
    if (!localStorage.getItem('authToken')) {
        window.location.href = 'login.php';
        return;
    }
    loadItems();
});

let ITEMS = [];

function loadItems() {
    ITEMS = (JSON.parse(localStorage.getItem('erp_items_v1')) || []).filter(x => !x.deleted);
    const select = document.getElementById('item-select');
    
    ITEMS.forEach(item => {
        if(item.barcode && item.barcode.trim() !== '') {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${item.name} (${item.barcode})`;
            select.appendChild(option);
        }
    });

    if(select.options.length === 1) {
        select.innerHTML += `<option disabled>لا توجد أصناف بباركود مسجل</option>`;
    }
}

function generatePreview() {
    const select = document.getElementById('item-select');
    const count = parseInt(document.getElementById('label-count').value) || 1;
    const preview = document.getElementById('print-preview');
    
    if (!select.value) {
        clearPreview();
        return;
    }

    const item = ITEMS.find(i => i.id === select.value);
    if (!item || !item.barcode) return;

    // Get company name from user data or fallback
    const userData = JSON.parse(localStorage.getItem('user') || '{}');
    const companyName = userData.company_name || localStorage.getItem('companyName') || 'مؤسستي';

    let html = '';
    for (let i = 0; i < count; i++) {
        html += `
            <div class="barcode-sticker">
                <div class="company-name">${companyName}</div>
                <div class="item-name">${item.name}</div>
                <svg id="barcode-${i}"></svg>
                <div class="item-price">${item.price ? Number(item.price).toLocaleString() + ' SDG' : ''}</div>
            </div>
        `;
    }

    preview.innerHTML = html;

    // Generate barcodes using JsBarcode
    for (let i = 0; i < count; i++) {
        try {
            JsBarcode(`#barcode-${i}`, item.barcode, {
                format: "CODE128",
                width: 1.5,
                height: 40,
                displayValue: true,
                fontSize: 12,
                margin: 2
            });
        } catch(e) {
            console.error("Barcode generation error", e);
        }
    }
}

function clearPreview() {
    document.getElementById('item-select').value = '';
    document.getElementById('print-preview').innerHTML = `<div style="width:100%; text-align:center; color:var(--text-secondary); margin-top:100px;">يرجى اختيار صنف لمعاينة ملصقات الباركود</div>`;
}
