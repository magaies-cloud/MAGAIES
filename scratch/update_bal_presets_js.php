<?php
$js = file_get_contents('c:/xampp/htdocs/ELSAJANA2/magaies/js/balances-report.js');

// 1. Add LOADED_BALANCES_PRESETS fetcher at the end of the file if not exists
$initCode = "
window.LOADED_BALANCES_PRESETS = {};
function fetchBalancesPresets() {
    fetch('api/get_balances_presets.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.LOADED_BALANCES_PRESETS = data.presets || {};
            }
        })
        .catch(err => console.error('Error fetching balances presets:', err));
}
document.addEventListener('DOMContentLoaded', fetchBalancesPresets);
";
if (strpos($js, 'window.LOADED_BALANCES_PRESETS') === false) {
    $js .= "\n\n" . $initCode;
}

// 2. We need to replace the // --- Presets Implementation --- block.
// It starts around line 2791 and ends around line 2955.
$newPresetsBlock = <<<'JS'
// --- Presets Implementation ---
window.renderBalancesPresets = function() {
    const wrap = document.getElementById('balances-presets-wrap');
    if (!wrap) return;

    wrap.innerHTML = '';

    const presets = [
        { id: '1', name: 'افتراضي 1', lsKey: 'balances_preset_1' },
        { id: '2', name: 'افتراضي 2', lsKey: 'balances_preset_2' },
        { id: '3', name: 'افتراضي 3', lsKey: 'balances_preset_3' },
        { id: '4', name: 'افتراضي 4', lsKey: 'balances_preset_4' },
        { id: 'm1', name: 'نموذج 1', lsKey: 'balances_model_1' },
        { id: 'm2', name: 'نموذج 2', lsKey: 'balances_model_2' }
    ];

    presets.forEach(preset => {
        const btn = document.createElement('button');
        btn.className = 'preset-btn';
        btn.setAttribute('data-slot-id', preset.id);

        const radius = 10;
        const circumference = 2 * Math.PI * radius;

        btn.innerHTML = `
            <svg class="preset-clock-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transform: rotate(-90deg); z-index: 2;">
                <circle cx="12" cy="12" r="${radius}" stroke="rgba(255,255,255,0.2)"></circle>
                <circle class="preset-clock-progress" cx="12" cy="12" r="${radius}" stroke-dasharray="${circumference}" stroke-dashoffset="${circumference}"></circle>
            </svg>
            <span style="z-index: 2;">${preset.name}</span>
            <div class="progress-bar-fill" style="position:absolute; bottom:0; left:0; height:100%; background:rgba(var(--accent-color), 0.25); width:0%; transition: width 0s linear; z-index: 1;"></div>
            <div class="progress-bar-line" style="position:absolute; bottom:0; left:0; height:4px; background:rgb(var(--accent-color)); width:0%; transition: width 0s linear; z-index: 3; box-shadow: 0 0 10px rgb(var(--accent-color));"></div>
        `;

        let startTime;
        let isHolding = false;
        let animationFrame;

        const startPress = (e) => {
            if (e.type === 'mousedown' && e.button !== 0) return; 

            // Check lock before starting for defaults
            let isModel = preset.id.startsWith('m');
            if (!isModel && window.LOADED_BALANCES_PRESETS && window.LOADED_BALANCES_PRESETS[preset.lsKey + '_locked']) {
                if (typeof showToast === 'function') {
                    showToast('عفواً، لا يمكن التعديل على ' + preset.name + ' لأنه محفوظ نهائياً في قاعدة البيانات!', 'error');
                } else {
                    alert('عفواً، لا يمكن التعديل على ' + preset.name + ' لأنه محفوظ نهائياً في قاعدة البيانات!');
                }
                return; // block holding
            }

            isHolding = true;
            startTime = Date.now();
            btn.classList.add('holding');

            const progressCircle = btn.querySelector('.preset-clock-progress');
            const progressFill = btn.querySelector('.progress-bar-fill');
            const progressLine = btn.querySelector('.progress-bar-line');

            const updateProgress = () => {
                if (!isHolding) return;
                const elapsed = Date.now() - startTime;
                let progress = elapsed / 8000; // 8 seconds
                if (progress > 1) progress = 1;

                const offset = circumference - (progress * circumference);
                if (progressCircle) progressCircle.style.strokeDashoffset = offset;
                if (progressFill) progressFill.style.width = (progress * 100) + '%';
                if (progressLine) progressLine.style.width = (progress * 100) + '%';

                if (progress < 1) {
                    animationFrame = requestAnimationFrame(updateProgress);
                } else {
                    // 8 seconds completed!
                    savePresetData(preset.lsKey, preset.name, preset.id);
                    resetButton();
                }
            };

            updateProgress();
        };

        const endPress = (e) => {
            if (!isHolding) return;
            const duration = Date.now() - startTime;

            if (duration < 8000) {
                // It was a click (not a long press)
                loadPresetData(preset.lsKey);
            }

            resetButton();
        };

        const resetButton = () => {
            isHolding = false;
            startTime = null;
            cancelAnimationFrame(animationFrame);
            btn.classList.remove('holding');

            const progressCircle = btn.querySelector('.preset-clock-progress');
            const progressFill = btn.querySelector('.progress-bar-fill');
            const progressLine = btn.querySelector('.progress-bar-line');

            if (progressCircle) progressCircle.style.strokeDashoffset = circumference;
            if (progressFill) progressFill.style.width = '0%';
            if (progressLine) progressLine.style.width = '0%';
        };

        btn.addEventListener('mousedown', startPress);
        btn.addEventListener('touchstart', startPress, {passive: true});

        const handleGlobalEnd = (e) => {
            if (isHolding) endPress(e);
        };

        document.addEventListener('mouseup', handleGlobalEnd);
        document.addEventListener('touchend', handleGlobalEnd);
        document.addEventListener('touchcancel', handleGlobalEnd);

        wrap.appendChild(btn);
    });
};

function savePresetData(lsKey, name, id) {
    if(!confirm('هل أنت متأكد أنك تريد حفظ التصميم الحالي في "' + name + '"؟ هذا الإجراء نهائي وسيتم حفظه في قاعدة البيانات.')) {
        return;
    }

    const dataToSave = {
        colConfigs: window.colConfigs,
        colOrder: window.colOrder,
        currentTableTheme: window.currentTableTheme || '',
        currentNumFont: window.currentNumFont || 'num-font-jetbrains'
    };

    fetch('api/save_balances_preset.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, config: dataToSave })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            if (typeof showToast === 'function') {
                showToast(`تم حفظ التخصيصات في ${name} حفظاً نهائياً!`, 'success');
            } else {
                alert(`تم حفظ التخصيصات في ${name} حفظاً نهائياً!`);
            }
            if(!window.LOADED_BALANCES_PRESETS) window.LOADED_BALANCES_PRESETS = {};
            window.LOADED_BALANCES_PRESETS[lsKey] = dataToSave;
            let isModel = id.startsWith('m');
            if (!isModel) {
                window.LOADED_BALANCES_PRESETS[lsKey + '_locked'] = true;
            }
        } else {
            if (typeof showToast === 'function') {
                showToast(data.error || 'حدث خطأ أثناء الحفظ', 'error');
            } else {
                alert(data.error || 'حدث خطأ أثناء الحفظ');
            }
        }
    })
    .catch(err => {
        if (typeof showToast === 'function') showToast('خطأ في الاتصال بالخادم', 'error');
        console.error(err);
    });
}

function loadPresetData(lsKey) {
    if (window.LOADED_BALANCES_PRESETS && window.LOADED_BALANCES_PRESETS[lsKey]) {
        try {
            const data = window.LOADED_BALANCES_PRESETS[lsKey];
            if (data.colConfigs && data.colOrder) {
                window.colConfigs = JSON.parse(JSON.stringify(data.colConfigs));
                window.colOrder = JSON.parse(JSON.stringify(data.colOrder));
                if (data.currentTableTheme !== undefined) {
                    if (typeof changeTableTheme === 'function') changeTableTheme(data.currentTableTheme);
                }
                if (data.currentNumFont !== undefined) {
                    if (typeof changeNumberFont === 'function') changeNumberFont(data.currentNumFont);
                }
                if (typeof applyLayoutToDOM === 'function') applyLayoutToDOM();
                if (typeof renderSidebarConfig === 'function') renderSidebarConfig();
                if (typeof window.runReport === 'function') window.runReport();

                if (typeof showToast === 'function') {
                    showToast('تم استرجاع النموذج بنجاح', 'success');
                }
            }
        } catch(e) {
            console.error('Error parsing preset', e);
        }
    } else {
        if (typeof showToast === 'function') {
            showToast('هذا النموذج فارغ، اضغط لمدة 8 ثوانٍ للحفظ عليه', 'warning');
        } else {
            alert('هذا النموذج فارغ، اضغط لمدة 8 ثوانٍ للحفظ عليه');
        }
    }
}

// Try to render presets immediately if wrap is available
window.renderBalancesPresets();
JS;

$js = preg_replace('/\/\/ \-\-\- Presets Implementation \-\-\-.*?\/\/ Try to render presets immediately if wrap is available\s*window\.renderBalancesPresets\(\);/s', $newPresetsBlock, $js);

file_put_contents('c:/xampp/htdocs/ELSAJANA2/magaies/js/balances-report.js', $js);
echo "balances-report.js updated successfully.";
?>
