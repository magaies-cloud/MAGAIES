<?php
$js = file_get_contents('c:/xampp/htdocs/ELSAJANA2/magaies/js/general-ledger.js');

// 1. Empty DEFAULT_PRESETS_GL
$js = preg_replace('/const DEFAULT_PRESETS_GL = \{.*?^\};/ms', 'const DEFAULT_PRESETS_GL = { 1: [], 2: [], 3: [], 4: [] };', $js);

// 2. Add window.LOADED_GL_PRESETS
$initCode = "
window.LOADED_GL_PRESETS = {};
function fetchGLPresets() {
    fetch('api/get_gl_presets.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.LOADED_GL_PRESETS = data.presets || {};
            }
        })
        .catch(err => console.error('Error fetching presets:', err));
}
document.addEventListener('DOMContentLoaded', fetchGLPresets);
";
if (strpos($js, 'window.LOADED_GL_PRESETS') === false) {
    $js .= "\n\n" . $initCode;
}

// 3. Replace startPresetSave
$newStartPresetSave = "function startPresetSave(id) {
    presetSaveTimers[id] = setTimeout(() => {
        let presetKey = 'gl_preset_' + id;
        if (window.LOADED_GL_PRESETS && window.LOADED_GL_PRESETS[presetKey + '_locked']) {
            showToast('عفواً، لا يمكن تعديل افتراضي ' + id + ' لأنه محفوظ نهائياً في قاعدة البيانات!', 'error');
            return;
        }

        if(confirm('هل أنت متأكد أنك تريد حفظ التصميم الحالي في \"افتراضي ' + id + '\"؟ هذا الإجراء نهائي ولا يمكن تغييره لاحقاً وسيتم حفظه في قاعدة البيانات.')) {
            
            let config = {
                colConfig: JSON.parse(JSON.stringify(colConfig)),
                theme: currentTableTheme,
                font: currentNumFont
            };

            fetch('api/save_gl_preset.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, config: config })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showToast('تم حفظ التصميم في افتراضي ' + id + ' بشكل نهائي من الألف إلى الياء!', 'success');
                    if(!window.LOADED_GL_PRESETS) window.LOADED_GL_PRESETS = {};
                    window.LOADED_GL_PRESETS[presetKey] = config;
                    window.LOADED_GL_PRESETS[presetKey + '_locked'] = true;
                    
                    let btn = document.getElementById('btn-preset-' + id);
                    if(btn) {
                        btn.style.background = '#10b981';
                        btn.style.color = '#fff';
                        setTimeout(() => {
                            btn.style.background = '';
                            btn.style.color = '';
                        }, 1500);
                    }
                } else {
                    showToast(data.error || 'حدث خطأ أثناء الحفظ', 'error');
                }
            })
            .catch(err => showToast('خطأ في الاتصال بالخادم', 'error'));
        }
    }, 8000); // 8 seconds hold
}";
$js = preg_replace('/function startPresetSave\(id\) \{.*?\n\}/ms', $newStartPresetSave, $js, 1);

// 4. Replace applyPresetGL
$newApplyPresetGL = "function applyPresetGL(id) {
    document.querySelectorAll('.preset-btn, .magic-btn').forEach(b => b.classList.remove('active'));
    let btn = document.getElementById('btn-preset-' + id);
    if (!btn) btn = document.getElementById('btn-preset-m' + String(id).replace('m', ''));
    if(btn) btn.classList.add('active');

    let pConfig = null;
    let layout = null;

    if (['1', '2', '3', '4', 1, 2, 3, 4].includes(id)) {
        let presetKey = 'gl_preset_' + id;
        if (window.LOADED_GL_PRESETS && window.LOADED_GL_PRESETS[presetKey]) {
            pConfig = window.LOADED_GL_PRESETS[presetKey];
        } else {
            showToast('هذا الافتراضي فارغ حالياً. اضغط مطولاً لمدة 8 ثوانٍ للحفظ.', 'warning');
            return;
        }
    } else {
        // Models m1, m2
        let modelKey = 'gl_model_' + String(id).replace('m', '');
        if (window.LOADED_GL_PRESETS && window.LOADED_GL_PRESETS[modelKey]) {
            pConfig = window.LOADED_GL_PRESETS[modelKey];
        } else {
            showToast('هذا النموذج فارغ، اضغط عليه 8 ثوانٍ لحفظ التنسيق الحالي كنموذج.', 'error');
            return;
        }
    }

    if (pConfig) {
        if (pConfig.colConfig) {
            colConfig = JSON.parse(JSON.stringify(pConfig.colConfig));
        }

        if (pConfig.theme !== undefined) {
            currentTableTheme = pConfig.theme;
            if (typeof applyTableTheme === 'function') applyTableTheme(currentTableTheme);
            if (typeof updateCrystalButtons === 'function') updateCrystalButtons(currentTableTheme);
        }

        if (pConfig.font !== undefined) {
            currentNumFont = pConfig.font;
            if (typeof changeNumberFont === 'function') changeNumberFont(currentNumFont);
            else {
                document.querySelectorAll('.num-cell-wrapper').forEach(el => {
                    el.classList.remove('num-font-roboto', 'num-font-inter', 'num-font-jetbrains', 'num-font-poppins', 'num-font-tahoma', 'num-font-arial', 'num-font-consolas');
                    el.classList.add(currentNumFont);
                });
            }
        }

        saveColumnConfig(); // Ensure SaaS sync picks up the loaded config

        if (typeof previewColSettingsGL === 'function') previewColSettingsGL();
        else {
            applyConfigToHeaders();
            runReport();
        }
        showToast('تم تطبيق التصميم المحفوظ بنجاح', 'success');
    }
}";
$js = preg_replace('/function applyPresetGL\(id\) \{.*?\n\}/ms', $newApplyPresetGL, $js, 1);

// 5. Replace startModelSave
$newStartModelSave = "function startModelSave(id) {
    modelSaveTimer = setTimeout(() => {
        let config = {
            colConfig: JSON.parse(JSON.stringify(colConfig)),
            theme: currentTableTheme,
            font: currentNumFont
        };

        fetch('api/save_gl_preset.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, config: config })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                let modelKey = 'gl_model_' + String(id).replace('m', '');
                if(!window.LOADED_GL_PRESETS) window.LOADED_GL_PRESETS = {};
                window.LOADED_GL_PRESETS[modelKey] = config;
                
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
            } else {
                showToast(data.error || 'حدث خطأ أثناء الحفظ', 'error');
            }
        })
        .catch(err => showToast('خطأ في الاتصال بالخادم', 'error'));
    }, 8000);
}";
$js = preg_replace('/function startModelSave\(id\) \{.*?\n\}/ms', $newStartModelSave, $js, 1);


file_put_contents('c:/xampp/htdocs/ELSAJANA2/magaies/js/general-ledger.js', $js);
echo "general-ledger.js modified successfully.";
?>
