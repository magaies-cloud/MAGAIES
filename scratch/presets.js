const DEFAULT_PRESETS_GL = {
    1: [
        { id: 'date', visible: true, size: '0.85rem', bold: false },
        { id: 'docNo', visible: true, size: '0.85rem', bold: true },
        { id: 'account', visible: true, size: '0.9rem', bold: true },
        { id: 'statement', visible: true, size: '0.85rem', bold: false },
        { id: 'debit', visible: true, size: '1rem', bold: true },
        { id: 'credit', visible: true, size: '1rem', bold: true },
        { id: 'balance', visible: true, size: '1rem', bold: true }
    ],
    2: [
        { id: 'date', visible: true, size: '0.8rem', bold: false },
        { id: 'docNo', visible: true, size: '0.8rem', bold: false },
        { id: 'account', visible: true, size: '0.85rem', bold: false },
        { id: 'statement', visible: true, size: '0.85rem', bold: false },
        { id: 'debit', visible: true, size: '0.9rem', bold: false },
        { id: 'credit', visible: true, size: '0.9rem', bold: false },
        { id: 'balance', visible: true, size: '0.9rem', bold: false }
    ],
    3: [
        { id: 'date', visible: true, size: '0.95rem', bold: true },
        { id: 'docNo', visible: true, size: '0.95rem', bold: true },
        { id: 'account', visible: true, size: '1.1rem', bold: true },
        { id: 'statement', visible: true, size: '1rem', bold: false },
        { id: 'debit', visible: true, size: '1.25rem', bold: true },
        { id: 'credit', visible: true, size: '1.25rem', bold: true },
        { id: 'balance', visible: true, size: '1.25rem', bold: true }
    ],
    4: [
        { id: 'date', visible: true },
        { id: 'docNo', visible: true },
        { id: 'account', visible: true },
        { id: 'statement', visible: true },
        { id: 'debit', visible: true },
        { id: 'credit', visible: true },
        { id: 'balance', visible: true },
        { id: 'currency', visible: true },
        { id: 'exRate', visible: true },
        { id: 'debitF', visible: true },
        { id: 'creditF', visible: true },
        { id: 'balanceF', visible: true }
    ]
};

let modelSaveTimer = null;

function applyPresetGL(id) {
    document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
    let btn = document.getElementById('btn-preset-' + id);
    if(btn) btn.classList.add('active');

    let layout = null;
    if (['1','2','3','4'].includes(String(id))) {
        layout = DEFAULT_PRESETS_GL[id];
    } else {
        let saved = JSON.parse(localStorage.getItem('sgl_models_v1') || '{}');
        layout = saved[id];
        if (!layout) {
            showToast('هذا النموذج فارغ، اضغط عليه 8 ثوانٍ لحفظ التنسيق الحالي كنموذج.', 'error');
            return;
        }
    }

    if (layout && layout.length > 0) {
        colConfig.forEach(c => {
            let found = layout.find(x => x.id === c.id);
            if (found) {
                c.visible = found.visible !== false;
                if(found.size) c.size = found.size;
                if(found.bold !== undefined) c.bold = found.bold;
            } else {
                c.visible = false;
            }
        });
        localStorage.setItem('sgl_col_config_v1', JSON.stringify(colConfig));
        if (typeof previewColSettingsGL === 'function') previewColSettingsGL();
        else {
            applyConfigToHeaders();
            runReport();
        }
        showToast('تم تطبيق التنسيق بنجاح', 'success');
    }
}

function startModelSave(id) {
    modelSaveTimer = setTimeout(() => {
        let saved = JSON.parse(localStorage.getItem('sgl_models_v1') || '{}');
        saved[id] = JSON.parse(JSON.stringify(colConfig));
        localStorage.setItem('sgl_models_v1', JSON.stringify(saved));
        showToast('تم حفظ التنسيق الحالي في هذا النموذج بنجاح!', 'success');
        
        let btn = document.getElementById('btn-preset-' + id);
        if(btn) {
            btn.style.background = '#22c55e';
            btn.style.color = '#fff';
            setTimeout(() => {
                btn.style.background = '';
                btn.style.color = '';
            }, 1000);
        }
    }, 8000);
}

function stopModelSave() {
    if (modelSaveTimer) {
        clearTimeout(modelSaveTimer);
        modelSaveTimer = null;
    }
}
