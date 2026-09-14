<?php $f = file_get_contents('js/mainbalances-report.js'); $new = <<<'EOF'
        const isDefaultGroup = idx < 4 || (p.name && p.name.includes('\u0627\u0641\u062a\u0631\u0627\u0636\u064a'));
        if (isDefaultGroup) {
            btn.innerHTML = p.name + (p.isLocked ? ' \uD83D\uDD12' : ' \uD83D\uDD13');
            btn.title = '\u0627\u0636\u063a\u0637 \u0644\u0639\u0631\u0636 \u0627\u0644\u062a\u0642\u0631\u064a\u0631. \u0627\u0636\u063a\u0637 \u0645\u0637\u0648\u0644\u0627\u064b \u0644\u0641\u0643 \u0627\u0644\u0642\u0641\u0644 \u0623\u0648 \u0644\u0644\u062d\u0641\u0638.';

            let pressTimer;
            let isLongPress = false;

            const startPress = (e) => {
                if (e.button === 2) return;
                isLongPress = false;
                pressTimer = setTimeout(() => {
                    isLongPress = true;
                    if (p.isLocked) {
                        const unlockConfirm = confirm('\u0647\u0644 \u062a\u0631\u064a\u062f \u0641\u0643 \u0627\u0644\u0642\u0641\u0644 \u0639\u0646 ' + p.name + ' \u0644\u062a\u0639\u062f\u064a\u0644 \u0627\u0644\u062c\u062f\u0627\u0648\u0644\u061f');
                        if (unlockConfirm) {
                            p.isLocked = false;
                            localStorage.setItem(PRESETS_STORAGE_KEY, JSON.stringify(presets));
                            window.renderBalancesPresets();
                            if (typeof showToast === 'function') showToast('\u062a\u0645 \u0641\u0643 \u0627\u0644\u0642\u0641\u0644\u0021 \u064a\u0645\u0643\u0646\u0643 \u0627\u0644\u0622\u0646 \u0627\u0644\u062a\u0639\u062f\u064a\u0644\u002e', 'info');
                        }
                    } else {
                        const finalConfirm = confirm('\u062a\u0645\u062a \u0625\u0632\u0627\u0644\u0629 \u0627\u0644\u0642\u0641\u0644 \u0645\u0633\u0628\u0642\u0627\u064b\u003a\n\n\u0647\u0644 \u0623\u0646\u062a \u0645\u062a\u0623\u0643\u062f \u0623\u0646\u0643 \u062a\u0631\u064a\u062f \u062d\u0641\u0638 \u0627\u0644\u062a\u0635\u0645\u064a\u0645 \u0627\u0644\u062d\u0627\u0644\u064a \u0643\u0627\u0641\u062a\u0631\u0627\u0636\u064a \u0648\u0625\u0639\u0627\u062f\u0629 \u0642\u0641\u0644\u0647\u061f');
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
                            if (typeof showToast === 'function') showToast('\u062a\u0645 \u062d\u0641\u0638 ' + p.name + ' \u0628\u0646\u062c\u0627\u062d\u0021', 'success');
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
