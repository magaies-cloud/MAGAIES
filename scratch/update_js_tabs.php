<?php
$filePath = 'js/balances-report.js';
$content = file_get_contents($filePath);
$content = str_replace("\r\n", "\n", $content);

// Update switchCtxTab
$old_switch = 'window.switchCtxTab = function(tab) {
    const colsTab = document.getElementById(\'ctx-tab-cols\');
    const formattingTab = document.getElementById(\'ctx-tab-formatting\');
    const colsBtn = document.getElementById(\'tab-btn-cols\');
    const formattingBtn = document.getElementById(\'tab-btn-formatting\');
    
    if (!colsTab || !formattingTab) return;
    
    if (tab === \'cols\') {
        colsTab.style.display = \'block\';
        formattingTab.style.display = \'none\';
        colsBtn.style.background = \'#38bdf8\';
        colsBtn.style.color = \'#0f172a\';
        colsBtn.style.fontWeight = \'900\';
        colsBtn.style.boxShadow = \'0 2px 8px rgba(56,189,248,0.3)\';
        
        formattingBtn.style.background = \'transparent\';
        formattingBtn.style.color = \'#94a3b8\';
        formattingBtn.style.fontWeight = \'700\';
        formattingBtn.style.boxShadow = \'none\';
    } else {
        colsTab.style.display = \'none\';
        formattingTab.style.display = \'block\';
        formattingBtn.style.background = \'#38bdf8\';
        formattingBtn.style.color = \'#0f172a\';
        formattingBtn.style.fontWeight = \'900\';
        formattingBtn.style.boxShadow = \'0 2px 8px rgba(56,189,248,0.3)\';
        
        colsBtn.style.background = \'transparent\';
        colsBtn.style.color = \'#94a3b8\';
        colsBtn.style.fontWeight = \'700\';
        colsBtn.style.boxShadow = \'none\';
    }
};';

$new_switch = 'window.switchCtxTab = function(tab) {
    const colsTab = document.getElementById(\'ctx-tab-cols\');
    const numbersTab = document.getElementById(\'ctx-tab-numbers\');
    const formattingTab = document.getElementById(\'ctx-tab-formatting\');
    
    const colsBtn = document.getElementById(\'tab-btn-cols\');
    const numbersBtn = document.getElementById(\'tab-btn-numbers\');
    const formattingBtn = document.getElementById(\'tab-btn-formatting\');
    
    if (!colsTab || !formattingTab) return;

    const resetStyle = (btn) => {
        if (!btn) return;
        btn.style.background = \'transparent\';
        btn.style.color = \'#94a3b8\';
        btn.style.fontWeight = \'700\';
        btn.style.boxShadow = \'none\';
    };
    const setActiveStyle = (btn) => {
        if (!btn) return;
        btn.style.background = \'#38bdf8\';
        btn.style.color = \'#0f172a\';
        btn.style.fontWeight = \'900\';
        btn.style.boxShadow = \'0 2px 10px rgba(56,189,248,0.4)\';
    };

    if (colsTab) colsTab.style.display = \'none\';
    if (numbersTab) numbersTab.style.display = \'none\';
    if (formattingTab) formattingTab.style.display = \'none\';

    resetStyle(colsBtn);
    resetStyle(numbersBtn);
    resetStyle(formattingBtn);

    if (tab === \'cols\') {
        if (colsTab) colsTab.style.display = \'block\';
        setActiveStyle(colsBtn);
    } else if (tab === \'numbers\') {
        if (numbersTab) numbersTab.style.display = \'block\';
        setActiveStyle(numbersBtn);
    } else {
        if (formattingTab) formattingTab.style.display = \'block\';
        setActiveStyle(formattingBtn);
    }
};

window.applyGlobalNumberSettingsFromUI = function() {
    const thActive = document.getElementById(\'global-thousands-active\')?.value === \'1\';
    const thSep = document.getElementById(\'global-thousands-sep\')?.value || \',\';
    const decSep = document.getElementById(\'global-dec-sep\')?.value || \'.\';
    const showDec = document.getElementById(\'global-show-decimals\')?.value === \'1\';
    const decCount = parseInt(document.getElementById(\'global-decimals-count\')?.value || \'2\');
    const roundMode = document.getElementById(\'global-round-mode\')?.value || \'round\';

    if (window.colConfigs) {
        window.colConfigs.forEach(col => {
            col.thousands = thActive;
            col.thousandsSep = thSep;
            col.decSep = decSep;
            col.showDecimals = showDec;
            col.decimals = decCount;
            col.dec = decCount;
            col.roundMode = roundMode;
        });
    }

    if (typeof applyLayoutToDOM === \'function\') applyLayoutToDOM();
    if (typeof window.runReport === \'function\' && window.lastResults) {
        window.runReport();
    }
    if (typeof saveLayout === \'function\') saveLayout();
    if (typeof renderColumnContextMenu === \'function\') renderColumnContextMenu();
    if (typeof showToast === \'function\') showToast(\'⚡ تم تطبيق إعدادات الأرقام والكسور والتقريب على جميع الأعمدة بنجاح\', \'success\');
};';

if (str_contains($content, $old_switch)) {
    $content = str_replace($old_switch, $new_switch, $content);
    echo "Updated switchCtxTab in js/balances-report.js\n";
} else {
    echo "WARNING: old_switch not found\n";
}

file_put_contents($filePath, $content);
