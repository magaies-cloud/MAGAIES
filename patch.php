<?php $f = file_get_contents('js/mainbalances-report.js'); $new = <<<'EOF'
        const isDefaultGroup = idx < 4 || (p.name && p.name.includes('ÇÝÊÑÇÖí'));
        if (isDefaultGroup) {
            btn.innerHTML = p.name + (p.isLocked ? ' ??' : ' ??');
            btn.title = 'ÇÖÛØ áÚÑÖ ÇáÊÞÑíÑ. ÇÖÛØ ãØæáÇð áÝß ÇáÞÝá Ãæ ááÍÝÙ.';

            let pressTimer;
            let isLongPress = false;

            const startPress = (e) => {
                if (e.button === 2) return;
                isLongPress = false;
                pressTimer = setTimeout(() => {
                    isLongPress = true;
                    if (p.isLocked) {
                        const unlockConfirm = confirm(åá ÊÑíÏ Ýß ÇáÞÝá Úä  áÊÚÏíá ÇáÌÏÇæá¿);
                        if (unlockConfirm) {
                            p.isLocked = false;
                            localStorage.setItem(PRESETS_STORAGE_KEY, JSON.stringify(presets));
                            window.renderBalancesPresets();
                            if (typeof showToast === 'function') showToast('Êã Ýß ÇáÞÝá! íãßäß ÇáÂä ÇáÊÚÏíá.', 'info');
                        }
                    } else {
                        const finalConfirm = confirm(åá ÃäÊ ãÊÃßÏ Ãäß ÊÑíÏ ÍÝÙ ÇáÊÕãíã ÇáÍÇáí ßÇÝÊÑÇÖí æÅÚÇÏÉ ÞÝáå¿);
                        if (finalConfirm) {
                            p.isLocked = true;
                            p.updatedAt = new Date().toISOString();
                            localStorage.setItem(PRESETS_STORAGE_KEY, JSON.stringify(presets));
                            const token = localStorage.getItem('erp_token') || '';
                            const headers = { 'Content-Type': 'application/json' };
                            if (token) headers['Authorization'] = 'Bearer ' + token;
                            fetch('api_index.php/report-layout', {
                                method: 'POST',
                                headers: headers,
                                body: JSON.stringify({ reportKey: PRESETS_STORAGE_KEY, layout: presets })
                            }).catch(e => {});
                            if (typeof showToast === 'function') showToast(Êã ÍÝÙ  ÈäÌÇÍ!, 'success');
                            window.renderBalancesPresets();
                        }
                    }
                }, 2000);
            };

            const cancelPress = () => { clearTimeout(pressTimer); };

            btn.addEventListener('mousedown', startPress);
            btn.addEventListener('touchstart', startPress, {passive: true});
            btn.addEventListener('mouseup', cancelPress);
            btn.addEventListener('mouseleave', cancelPress);
            btn.addEventListener('touchend', cancelPress);

            btn.onclick = (e) => {
                if (e) e.preventDefault();
                if (isLongPress) return;
                window.openPresetModal(idx, p.name);
            };
        } else {
EOF;
$f = preg_replace('/const isDefaultGroup.*?\} else \{/s', $new, $f, 1); file_put_contents('js/mainbalances-report.js', $f); ?>
