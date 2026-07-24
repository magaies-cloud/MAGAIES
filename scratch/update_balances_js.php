<?php
$filePath = 'js/balances-report.js';
$content = file_get_contents($filePath);
$content = str_replace("\r\n", "\n", $content);

// 1. Replace fmtColNum
$old_fmt = 'function fmtColNum(n, origIdx) {
    const conf = (window.colConfigs && window.colConfigs[origIdx]) ? window.colConfigs[origIdx] : {};
    const showDec = (conf.showDecimals !== false);
    const d = showDec ? ((conf.decimals !== undefined) ? parseInt(conf.decimals) : 2) : 0;
    const isThousands = (conf.thousands !== false);
    const val = parseFloat(n);
    if (isNaN(val)) return String(n || \'\');

    let formatted = val.toFixed(d);
    if (isThousands) {
        const parts = formatted.split(\'.\');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        formatted = parts.join(\'.\');
    }
    return formatted;
}';

$new_fmt = 'function fmtColNum(n, origIdx) {
    const conf = (window.colConfigs && window.colConfigs[origIdx]) ? window.colConfigs[origIdx] : {};
    const showDec = (conf.showDecimals !== false);
    const d = showDec ? ((conf.decimals !== undefined) ? parseInt(conf.decimals) : ((conf.dec !== undefined) ? parseInt(conf.dec) : 2)) : 0;
    const isThousands = (conf.thousands !== false);
    const thouSep = (conf.thousandsSep !== undefined) ? conf.thousandsSep : \',\';
    const decSep = (conf.decSep !== undefined) ? conf.decSep : \'.\';
    const roundMode = conf.roundMode || \'round\';

    const val = parseFloat(n);
    if (isNaN(val)) return String(n || \'\');

    let roundedVal = val;
    const factor = Math.pow(10, d);
    if (d >= 0) {
        if (roundMode === \'floor\') {
            roundedVal = Math.floor(val * factor) / factor;
        } else if (roundMode === \'ceil\') {
            roundedVal = Math.ceil(val * factor) / factor;
        } else if (roundMode === \'trunc\') {
            roundedVal = Math.trunc(val * factor) / factor;
        } else {
            roundedVal = Math.round(val * factor) / factor;
        }
    }

    let formatted = roundedVal.toFixed(d);
    const parts = formatted.split(\'.\');
    
    if (isThousands && thouSep !== \'none\') {
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thouSep);
    }
    
    if (d > 0) {
        return parts[0] + decSep + (parts[1] || \'\');
    } else {
        return parts[0];
    }
}';

if (str_contains($content, $old_fmt)) {
    $content = str_replace($old_fmt, $new_fmt, $content);
    echo "fmtColNum updated successfully!\n";
} else {
    echo "ERROR: fmtColNum still not matched\n";
}

// 2. Replace setters
$old_setters = 'window.setColDecimals = function(origIdx, visualIndex, d) {
    if (origIdx !== undefined && window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].decimals = parseInt(d);
        if (window.lastResults && window.lastResults.length > 0) {
            renderTable(window.lastResults);
        }
        updateColDOM(visualIndex, origIdx);
        saveLayout();
        bindContextMenuList(visualIndex);
    }
};';

$new_setters = 'window.setColDecimals = function(origIdx, visualIndex, d) {
    if (origIdx !== undefined && window.colConfigs && window.colConfigs[origIdx]) {
        const parsedD = parseInt(d);
        window.colConfigs[origIdx].decimals = parsedD;
        window.colConfigs[origIdx].dec = parsedD;
        if (window.lastResults && window.lastResults.length > 0 && typeof renderTable === \'function\') {
            renderTable(window.lastResults);
        }
        if (typeof updateColDOM === \'function\') updateColDOM(visualIndex, origIdx);
        saveLayout();
        if (typeof bindContextMenuList === \'function\') bindContextMenuList(visualIndex);
    }
};

window.setColThousandsSep = function(origIdx, visualIndex, sep) {
    if (origIdx !== undefined && window.colConfigs && window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].thousandsSep = sep;
        if (window.lastResults && window.lastResults.length > 0 && typeof renderTable === \'function\') {
            renderTable(window.lastResults);
        }
        if (typeof updateColDOM === \'function\') updateColDOM(visualIndex, origIdx);
        saveLayout();
        if (typeof bindContextMenuList === \'function\') bindContextMenuList(visualIndex);
    }
};

window.setColDecSep = function(origIdx, visualIndex, sep) {
    if (origIdx !== undefined && window.colConfigs && window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].decSep = sep;
        if (window.lastResults && window.lastResults.length > 0 && typeof renderTable === \'function\') {
            renderTable(window.lastResults);
        }
        if (typeof updateColDOM === \'function\') updateColDOM(visualIndex, origIdx);
        saveLayout();
        if (typeof bindContextMenuList === \'function\') bindContextMenuList(visualIndex);
    }
};

window.setColRoundMode = function(origIdx, visualIndex, mode) {
    if (origIdx !== undefined && window.colConfigs && window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].roundMode = mode;
        if (window.lastResults && window.lastResults.length > 0 && typeof renderTable === \'function\') {
            renderTable(window.lastResults);
        }
        if (typeof updateColDOM === \'function\') updateColDOM(visualIndex, origIdx);
        saveLayout();
        if (typeof bindContextMenuList === \'function\') bindContextMenuList(visualIndex);
    }
};';

if (str_contains($content, $old_setters)) {
    $content = str_replace($old_setters, $new_setters, $content);
    echo "Setters updated successfully!\n";
} else {
    echo "ERROR: Setters still not matched\n";
}

file_put_contents($filePath, $content);
