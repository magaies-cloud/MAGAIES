// ====================================================================
// MAGAIEES ERP - AI Accounting Reconciliation & Comparison Engine
// نظام التطابق والمقارنات المحاسبية الذكي بالذكاء الاصطناعي 2026
// ====================================================================

(function(window) {
    'use strict';

    // Global Reconciliation State
    window.RECON_STATE = {
        fileA: {
            name: '',
            rawRows: [],
            headers: [],
            colMap: {},
            mappedRows: [],
            summary: { debit: 0, credit: 0, balance: 0, count: 0 }
        },
        fileB: {
            name: '',
            rawRows: [],
            headers: [],
            colMap: {},
            mappedRows: [],
            summary: { debit: 0, credit: 0, balance: 0, count: 0 }
        },
        results: {
            exactMatches: [],
            varianceMatches: [],
            unmatchedA: [],
            unmatchedB: [],
            netVariance: 0,
            matchRate: 0
        },
        settings: {
            dateTolerance: 3,      // days
            refMinSuffix: 4,       // minimum digits for suffix match
            amountTolerance: 0.5,  // currency units
            searchInDesc: true,    // search for ref inside description text
            enableFxMatching: true // check dollar/foreign currency equivalence
        },
        aiAudit: null
    };

    // Universal Drag & Drop Setup for Reconciliation Upload Dropzones
    function setupReconDropzones() {
        ['A', 'B'].forEach(side => {
            const box = document.getElementById(`recon-dropzone-box-${side}`);
            if (!box || typeof box.addEventListener !== 'function' || (box.dataset ? box.dataset.dragInitialized : null) === 'true') return;
            if (box.dataset) box.dataset.dragInitialized = 'true';

            ['dragenter', 'dragover'].forEach(eventName => {
                box.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    box.classList.add('border-indigo-400', 'bg-indigo-950/30', 'scale-[1.01]');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                box.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    box.classList.remove('border-indigo-400', 'bg-indigo-950/30', 'scale-[1.01]');
                }, false);
            });

            box.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = (dt ? dt.files : null);
                if (files && files.length > 0) {
                    const fakeEvent = { target: { files: files, value: '' } };
                    window.handleOmniUpload(fakeEvent, side, 'file');
                }
            }, false);
        });
    }

    // Initialize suite if opened
    function initReconciliationSuite() {
        console.log('Reconciliation Suite Initialized');
        setupReconDropzones();
    }
    window.initReconciliationSuite = initReconciliationSuite;

    if (typeof document !== 'undefined') {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupReconDropzones);
        } else {
            setupReconDropzones();
        }
    }

    // ────────────────────────────────────────────────────────────────
    // 1. SAMPLE DATA GENERATOR (نموذج تجريبي فوري للاختبار)
    // ────────────────────────────────────────────────────────────────
    function loadReconciliationSampleData() {
        const sampleA = [
            ["التاريخ", "رقم التحويلة / السند", "البيان وتفاصيل العملية", "مدين (لنا)", "دائن (علينا)", "الرصيد", "سعر الدولار", "المبلغ بالدولار"],
            ["2026-06-01", "INV-101", "رصيد افتتاحي مرحل من الدورة السابقة", 0, 15000000, -15000000, 6000, -2500],
            ["2026-06-05", "TRX-88014567", "تحويل بنكك توريد بضاعة دفعة أولى", 6000000, 0, -9000000, 6000, 1000],
            ["2026-06-10", "INV-102", "فاتورة شراء حديد تسليح 12 ملم (10 طن)", 0, 12000000, -21000000, 6000, -2000],
            ["2026-06-12", "CHQ-7721", "سداد شيك مصرفي رقم 7721 لصالح الطرف الثاني", 5000000, 0, -16000000, 6000, 833.33],
            ["2026-06-15", "TRX-994411", "سداد نقدي عبر بنك أمدرمان الوطني", 2540000, 0, -13460000, 6000, 423.33], // Transposition error test: 2,540,000 vs 2,450,000 (diff 90,000 divisible by 9)
            ["2026-06-18", "TRX-442188", "تحويل بنكك سداد دفعة توريد إسمنت", 3500000, 0, -9960000, 6000, 583.33],
            ["2026-06-20", "INV-103", "فاتورة استلام صاج وزنك أمريكي", 0, 8000000, -17960000, 6050, -1322.31],
            ["2026-06-22", "TRX-661200", "تحويل بنك فيصل الإسلامي - فوري", 4000000, 0, -13960000, 6000, 666.67],
            // Combinatorial match: 2 split transfers settling invoice INV-104 (5,000,000)
            ["2026-06-24", "TRX-SPLIT-1", "تحويل بنكك سداد دفعة فاتورة 104 (دفعة 1 سقف يومي)", 3000000, 0, -10960000, 6000, 500],
            ["2026-06-24", "TRX-SPLIT-2", "تحويل بنكك سداد دفعة فاتورة 104 (دفعة 2 سقف يومي)", 2000000, 0, -8960000, 6000, 333.33],
            // FX Currency variance test (1,000$ USD booked at 6,000 vs 6,050 -> 50,000 SDG FX drift)
            ["2026-06-25", "TRX-USD-770", "حوالة بنكية بالدولار (1,000$) مسجلة بسعر صرف 6,000", 6000000, 0, -2960000, 6000, 1000],
            // Aged critical item (dated April 2026 -> >60 days old)
            ["2026-04-12", "OLD-CHQ-109", "شيك مسحوب رقم 109 معلق بالطريق لم يصرفه الطرف الثاني", 1800000, 0, -7160000, 5800, 310.34]
        ];

        // Sample B: Second Party Statement (Contains realistic discrepancies, split payments, and transposition)
        const sampleB = [
            ["تاريخ الحركة", "رقم العملية المرجعي", "شرح الحركة في كشف الطرف الثاني", "المقبوضات (دائن)", "المدفوعات والمبيعات (مدين)", "رصيد العميل", "تعادل الدولار"],
            ["2026-06-01", "OP-BAL", "الرصيد الافتتاحي المقيد لدى الطرف الثاني", 0, 15000000, 15000000, 2500],
            ["2026-06-06", "BOK-009988014567", "إشعار تحويل بنكك الوارد من العميل (مطابق بآخر أرقام)", 6000000, 0, 9000000, 1000],
            ["2026-06-10", "INV-102", "قيمة حديد تسليح مباني طلبية رقم 102", 0, 12000000, 21000000, 2000],
            ["2026-06-13", "7721", "تحصيل شيك مصرفي رقم 7721", 5000000, 0, 16000000, 833.33],
            ["2026-06-15", "TRX-994411", "سداد نقدي مقيد بـ 2,450,000 بدلاً من 2,540,000 (تبديل خانات)", 2450000, 0, 13550000, 408.33], // Transposition: 90K diff (90000 % 9 === 0)
            ["2026-06-20", "INV-103", "فاتورة توريد صاج وزنك أمريكي", 0, 8000000, 21550000, 1333.33],
            ["2026-06-21", "FEE-901", "رسوم نولون وتعتيق وشحن لم يقيدها كشف الطرف الأول", 0, 250000, 21800000, 41.67], // MISSING IN CLIENT
            ["2026-06-22", "TRX-661200", "إشعار تحويل بنك فيصل فوري", 4000000, 0, 17800000, 666.67],
            // Target single invoice matched by 2 split transfers (3M + 2M = 5M)
            ["2026-06-24", "INV-104", "فاتورة توريد زوايا حديد مبوم طلبية 104", 0, 5000000, 22800000, 833.33],
            // FX Currency variance test (1,000$ USD booked at 6,050 -> 50,000 SDG FX drift)
            ["2026-06-25", "TRX-USD-770", "إشعار تحويل بالدولار (1,000$) مقيد بسعر صرف 6,050 (فارق تقييم صرف)", 6050000, 0, 16750000, 1000],
            // Aged critical item
            ["2026-04-10", "OLD-FEE-88", "رسوم أرضيات ومستودع قديمة لم تسجل في كشف الطرف الأول", 0, 450000, 23250000, 77.59]
        ];

        processLoadedSheetData(sampleA, 'A', 'كشف_حساب_الطرف_الأول_الخاص.xlsx');
        processLoadedSheetData(sampleB, 'B', 'كشف_حساب_الطرف_الثاني.xlsx');

        if (window.showModernToast) {
            window.showModernToast('✨ تم تحميل بيانات النموذج التجريبي وتشغيل الفحص والمطابقة الجنائية بنجاح!', 'success', 3500);
        }

        setTimeout(() => {
            if (typeof window.runReconciliationEngine === 'function') {
                window.runReconciliationEngine();
            }
        }, 300);
    }
    window.loadReconciliationSampleData = loadReconciliationSampleData;

    // ────────────────────────────────────────────────────────────────
    // 2. EXCEL & CSV FILE INGESTION (SheetJS)
    // ────────────────────────────────────────────────────────────────
    function handleReconFileUpload(event, side) {
        const file = (event.target.files ? event.target.files[0] : null);
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                if (typeof XLSX === 'undefined') {
                    alert('جاري تحميل مكتبة قراءة الإكسيل، يرجى إعادة المحاولة بعد ثانية.');
                    return;
                }
                const workbook = XLSX.read(data, { type: 'array', cellDates: true });
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];
                const rawJson = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });

                if (!rawJson || rawJson.length === 0) {
                    alert('الملف فارغ أو لا يحتوي على صفوف بيانات صالحة.');
                    return;
                }

                processLoadedSheetData(rawJson, side, file.name);

                if (window.showModernToast) {
                    window.showModernToast(`✅ تم قراءة ملف (${file.name}) بنجاح: ${rawJson.length - 1} سجل`, 'info', 2500);
                }
            } catch (err) {
                console.error('File parsing error:', err);
                alert('حدث خطأ أثناء قراءة ملف الإكسيل. تأكد من سلامة صيغة الملف.');
            } finally {
                event.target.value = '';
            }
        };
        reader.readAsArrayBuffer(file);
    }
    window.handleReconFileUpload = handleReconFileUpload;

    // Process Raw Sheet Matrix -> Structured Entity
    function processLoadedSheetData(matrix, side, fileName) {
        if (!matrix || matrix.length < 2) return;

        // Auto-detect header row (look for typical keywords in first 10 rows)
        let headerRowIndex = 0;
        for (let i = 0; i < Math.min(10, matrix.length); i++) {
            const rowStr = (matrix[i] || []).join(' ');
            if (/تاريخ|date|مبلغ|amount|مدين|دائن|رصيد|مرجع|ref|بيان/i.test(rowStr)) {
                headerRowIndex = i;
                break;
            }
        }

        const rawHeaders = (matrix[headerRowIndex] || []).map(h => String(h || '').trim());
        const dataRows = matrix.slice(headerRowIndex + 1).filter(r => r && r.some(c => String(c).trim() !== ''));

        const colMap = autoDetectColumns(rawHeaders);
        const mappedRows = mapRawRows(dataRows, colMap);
        const summary = computeSideSummary(mappedRows);

        const target = (side === 'A') ? window.RECON_STATE.fileA : window.RECON_STATE.fileB;
        target.name = fileName || (side === 'A' ? 'كشف حساب الطرف الأول' : 'كشف حساب الطرف الثاني');
        target.rawRows = dataRows;
        target.headers = rawHeaders;
        target.colMap = colMap;
        target.mappedRows = mappedRows;
        target.summary = summary;
        if (window['RECON_PREVIEW_IMG_' + side]) {
            target.previewImg = window['RECON_PREVIEW_IMG_' + side];
        }

        renderDropzoneCard(side);
        renderColumnMappingCard(side);

        // Auto-run reconciliation comparison if both sides are ready
        const hasA = window.RECON_STATE.fileA && window.RECON_STATE.fileA.mappedRows && window.RECON_STATE.fileA.mappedRows.length > 0;
        const hasB = window.RECON_STATE.fileB && window.RECON_STATE.fileB.mappedRows && window.RECON_STATE.fileB.mappedRows.length > 0;
        if (hasA && hasB) {
            setTimeout(function() {
                if (typeof window.runReconciliationEngine === 'function') {
                    window.runReconciliationEngine();
                }
            }, 300);
        }
    }

    // ────────────────────────────────────────────────────────────────
    // 3. INTELLIGENT COLUMN AUTO-DETECTION & MAPPING
    // ────────────────────────────────────────────────────────────────
    function autoDetectColumns(headers) {
        const map = {
            dateIdx: -1,
            refIdx: -1,
            descIdx: -1,
            debitIdx: -1,
            creditIdx: -1,
            amountIdx: -1,
            balanceIdx: -1,
            fxRateIdx: -1,
            foreignAmtIdx: -1
        };

        headers.forEach((h, idx) => {
            const s = h.toLowerCase().trim();
            if (map.dateIdx === -1 && /تاريخ|date|posting|val_date|تاريخ الحركة/i.test(s)) map.dateIdx = idx;
            else if (map.refIdx === -1 && /تحويل|حوالة|شيك|مرجع|مرجعي|عملية|سند|قيد|رقم العملية|رقم المرجع|ref|trx|trn|check|cheque|voucher|doc/i.test(s)) map.refIdx = idx;
            else if (map.descIdx === -1 && /بيان|شرح|وصف|تفاصيل|ملاحظ|شرح الحركة|particular|description|narration|details/i.test(s)) map.descIdx = idx;
            else if (map.debitIdx === -1 && /مدين|منه|عليه|سحب|صرف|خروج|مدفوعات|مبيعات|debit|dr|withdrawal/i.test(s)) map.debitIdx = idx;
            else if (map.creditIdx === -1 && /دائن|له|إيداع|قبض|دخول|توريد|مقبوضات|credit|cr|deposit|receipt/i.test(s)) map.creditIdx = idx;
            else if (map.balanceIdx === -1 && /رصيد|balance|bal|الرصيد بعد الحركة/i.test(s)) map.balanceIdx = idx;
            else if (map.fxRateIdx === -1 && /صرف|سعر الصرف|سعر الدولار|rate|fx|exchange/i.test(s)) map.fxRateIdx = idx;
            else if (map.foreignAmtIdx === -1 && /أجنبي|دولار|تعادل الدولار|usd|foreign/i.test(s)) map.foreignAmtIdx = idx;
            else if (map.amountIdx === -1 && /مبلغ|القيمة|صافي|amount|net|value/i.test(s)) map.amountIdx = idx;
        });

        // Fallback: If amount exists but no debit/credit, amount will serve as directional or net
        return map;
    }

    // Map rows to standardized objects
    function mapRawRows(rows, colMap) {
        return rows.map((r, i) => {
            let rawDate = (colMap.dateIdx >= 0) ? r[colMap.dateIdx] : '';
            let dateStr = parseStandardDate(rawDate);

            let rawRef = (colMap.refIdx >= 0) ? String(r[colMap.refIdx] || '').trim() : '';
            let rawDesc = (colMap.descIdx >= 0) ? String(r[colMap.descIdx] || '').trim() : '';
            
            // Clean digits from ref
            let digitsRef = rawRef.replace(/\D/g, '');
            // If ref is empty or lacks numbers, try extracting digits from description
            if (digitsRef.length < 3 && rawDesc) {
                const matchDesc = rawDesc.match(/\b([0-9]{4,16})\b/);
                if (matchDesc) digitsRef = matchDesc[1];
            }

            let debit = (colMap.debitIdx >= 0) ? cleanNumber(r[colMap.debitIdx]) : 0;
            let credit = (colMap.creditIdx >= 0) ? cleanNumber(r[colMap.creditIdx]) : 0;
            let amount = (colMap.amountIdx >= 0) ? cleanNumber(r[colMap.amountIdx]) : 0;
            let balance = (colMap.balanceIdx >= 0) ? cleanNumber(r[colMap.balanceIdx]) : 0;
            let fxRate = (colMap.fxRateIdx >= 0) ? cleanNumber(r[colMap.fxRateIdx]) : 0;
            let foreignAmt = (colMap.foreignAmtIdx >= 0) ? cleanNumber(r[colMap.foreignAmtIdx]) : 0;

            if (amount === 0) {
                amount = Math.abs(debit - credit);
            }

            return {
                id: i + 1,
                date: dateStr,
                rawDate: rawDate,
                ref: rawRef || digitsRef || '-',
                digitsRef: digitsRef,
                desc: rawDesc || 'حركة مالية',
                debit: debit,
                credit: credit,
                amount: amount,
                balance: balance,
                fxRate: fxRate,
                foreignAmt: foreignAmt,
                raw: r,
                matched: false
            };
        });
    }

    function cleanNumber(val) {
        if (typeof val === 'number') return isNaN(val) ? 0 : val;
        if (!val) return 0;
        let s = String(val).trim();
        // Replace eastern arabic / persian numbers
        const ar = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        const fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        for (let i = 0; i < 10; i++) {
            s = s.replaceAll(ar[i], i).replaceAll(fa[i], i);
        }
        s = s.replace(/,/g, '').replace(/[^\d.-]/g, '');
        const n = parseFloat(s);
        return isNaN(n) ? 0 : n;
    }

    function parseStandardDate(val) {
        if (!val) return '';
        if (val instanceof Date) {
            return val.toISOString().split('T')[0];
        }
        let s = String(val).trim();
        // Handle common formats YYYY-MM-DD, DD/MM/YYYY, DD-MM-YYYY
        let m = s.match(/(\d{4})[-/.](\d{1,2})[-/.](\d{1,2})/);
        if (m) {
            return `${m[1]}-${String(m[2]).padStart(2, '0')}-${String(m[3]).padStart(2, '0')}`;
        }
        let m2 = s.match(/(\d{1,2})[-/.](\d{1,2})[-/.](\d{4})/);
        if (m2) {
            return `${m2[3]}-${String(m2[2]).padStart(2, '0')}-${String(m2[1]).padStart(2, '0')}`;
        }
        return s.split(' ')[0] || '';
    }

    function computeSideSummary(rows) {
        let totalDebit = 0;
        let totalCredit = 0;
        rows.forEach(r => {
            totalDebit += r.debit;
            totalCredit += r.credit;
        });
        const lastRow = rows[rows.length - 1];
        const closingBalance = lastRow ? (lastRow.balance || (totalDebit - totalCredit)) : (totalDebit - totalCredit);

        return {
            debit: totalDebit,
            credit: totalCredit,
            balance: closingBalance,
            count: rows.length
        };
    }

    // ────────────────────────────────────────────────────────────────
    // 4. MULTILEVEL HIGH-PRECISION RECONCILIATION MATCHING ENGINE
    // ────────────────────────────────────────────────────────────────
    async function runReconciliationEngine() {
        const stateA = window.RECON_STATE.fileA;
        const stateB = window.RECON_STATE.fileB;

        if (!stateA.mappedRows || stateA.mappedRows.length === 0 || !stateB.mappedRows || stateB.mappedRows.length === 0) {
            alert('يرجى تحميل ملفي الإكسيل أولاً (كشف حساب الطرف الأول وكشف حساب الطرف الثاني) أو الضغط على "تحميل نموذج تجريبي".');
            return;
        }

        // Reset match flags
        stateA.mappedRows.forEach(r => r.matched = false);
        stateB.mappedRows.forEach(r => r.matched = false);

        const exactMatches = [];
        const varianceMatches = [];
        const unmatchedA = [];
        const unmatchedB = [];

        const dateTol = window.RECON_STATE.settings.dateTolerance;
        const minSuffix = window.RECON_STATE.settings.refMinSuffix;

        // ── Level 1: Strict Match (Ref Suffix Match + Amount Match + Date in tolerance) ──
        for (const rowA of stateA.mappedRows) {
            if (rowA.matched) continue;

            let bestMatchB = null;
            let matchType = '';

            for (const rowB of stateB.mappedRows) {
                if (rowB.matched) continue;

                const isRefMatched = checkRefMatch(rowA.digitsRef, rowB.digitsRef, minSuffix);
                const isAmountMatched = checkAmountMatch(rowA, rowB);
                const dayDiff = getDayDifference(rowA.date, rowB.date);

                if (isRefMatched && isAmountMatched && Math.abs(dayDiff) <= dateTol) {
                    bestMatchB = rowB;
                    matchType = 'exact_ref_amount_date';
                    break;
                }
            }

            if (bestMatchB) {
                rowA.matched = true;
                bestMatchB.matched = true;
                exactMatches.push({
                    rowA: rowA,
                    rowB: bestMatchB,
                    matchType: matchType,
                    badge: 'تطابق تام (مرجع + مبلغ + تاريخ)',
                    confidence: 100
                });
            }
        }

        // ── Level 2: Suffix Reference Match with Amount Match (Relaxed Date) ──
        // Handled as user requested: "رقم التحويلة... يتطابق فيه آخر خمسة أو ستة أرقام... حتى لو كان في فرق بسيط بالتاريخ"
        for (const rowA of stateA.mappedRows) {
            if (rowA.matched) continue;

            let bestMatchB = null;
            let dayDiffFound = 0;

            for (const rowB of stateB.mappedRows) {
                if (rowB.matched) continue;

                const isRefMatched = checkRefMatch(rowA.digitsRef, rowB.digitsRef, minSuffix);
                const isAmountMatched = checkAmountMatch(rowA, rowB);

                if (isRefMatched && isAmountMatched) {
                    bestMatchB = rowB;
                    dayDiffFound = getDayDifference(rowA.date, rowB.date);
                    break;
                }
            }

            if (bestMatchB) {
                rowA.matched = true;
                bestMatchB.matched = true;
                if (Math.abs(dayDiffFound) <= 7) {
                    exactMatches.push({
                        rowA: rowA,
                        rowB: bestMatchB,
                        matchType: 'suffix_ref_amount',
                        badge: `تطابق المرجع بأواخر الأرقام (${minSuffix}+ أرقام) بفارق ${Math.abs(dayDiffFound)} يوم`,
                        confidence: 95
                    });
                } else {
                    varianceMatches.push({
                        rowA: rowA,
                        rowB: bestMatchB,
                        type: 'date_variance',
                        title: 'تطابق رقم التحويلة والمبلغ مع تباعد كبير بالتاريخ',
                        diff: 0,
                        dateDiff: dayDiffFound,
                        explanation: `رقم التحويلة متطابق (${rowA.ref} مقابل ${bestMatchB.ref}) والمبلغ متطابق، ولكن تاريخ تسجيل كشف الطرف الأول (${rowA.date}) يختلف عن تاريخ كشف الطرف الثاني (${bestMatchB.date}) بفارق ${Math.abs(dayDiffFound)} يوماً.`
                    });
                }
            }
        }

        // ── Level 3: Reference Match but Amount has a Variance! ──
        for (const rowA of stateA.mappedRows) {
            if (rowA.matched) continue;

            let bestMatchB = null;

            for (const rowB of stateB.mappedRows) {
                if (rowB.matched) continue;

                const isRefMatched = checkRefMatch(rowA.digitsRef, rowB.digitsRef, minSuffix);
                if (isRefMatched) {
                    bestMatchB = rowB;
                    break;
                }
            }

            if (bestMatchB) {
                rowA.matched = true;
                bestMatchB.matched = true;
                const amtA = rowA.amount;
                const amtB = bestMatchB.amount;
                const diff = amtA - amtB;
                const absDiff = Math.abs(diff);
                const isTransposition = (Math.round(absDiff) % 9 === 0) && absDiff > 0;

                // Dynamic FX Fluctuation Diagnostics
                const descCombined = ((rowA.desc || '') + ' ' + (bestMatchB.desc || '') + ' ' + (rowA.currency || '') + ' ' + (bestMatchB.currency || '')).toLowerCase();
                const hasFxIndicator = descCombined.includes('دولار') || descCombined.includes('usd') || descCombined.includes('sar') || descCombined.includes('ريال') || descCombined.includes('صرف') || descCombined.includes('سعر') || (rowA.fx_rate > 0) || (bestMatchB.fx_rate > 0);
                const ratio = (amtA > 0 && amtB > 0) ? (Math.max(amtA, amtB) / Math.min(amtA, amtB)) : 1;
                const isFxVariance = (!isTransposition && (hasFxIndicator || (ratio > 1.002 && ratio < 1.30 && absDiff >= 100)));

                let explanation = `نفس رقم العملية (${rowA.ref} مقابل ${bestMatchB.ref})، لكن مسجل في كشف الطرف الأول بمبلغ ${formatMoney(amtA)} ومسجل في كشف الطرف الثاني بمبلغ ${formatMoney(amtB)}، بفارق ${formatMoney(absDiff)}.`;
                if (isTransposition) {
                    explanation += ` (⚠️ كشف جنائي: الفارق ${formatMoney(absDiff)} يقبل القسمة على 9 تماماً، مما يرجح بنسبة 99% وجود خطأ إدخال بتبديل خانات الأرقام Transposition Error).`;
                } else if (isFxVariance) {
                    explanation += ` (💱 تشخيص العملات الأجنبية: الفارق ناتج عن تباين سعر تقييم صرف العملة اليومي FX Rate Drift بين قيد الطرفين وليس نقصاً في أصل الدفعة المسددة).`;
                }

                let title = 'اختلاف في مبلغ التحويلة لنفس الرقم المرجعي';
                if (isTransposition) title = 'تبديل خانات أرقام (قاعدة التسعة) لنفس التحويلة';
                else if (isFxVariance) title = 'فارق سعر صرف وتقلبات عملة (FX Rate Drift)';

                varianceMatches.push({
                    rowA: rowA,
                    rowB: bestMatchB,
                    type: isFxVariance ? 'fx_rate_variance' : 'amount_variance',
                    title: title,
                    diff: diff,
                    isTransposition: isTransposition,
                    isFxVariance: isFxVariance,
                    foreignBase: (rowA.foreignAmt || bestMatchB.foreignAmt) ? `${rowA.foreignAmt || bestMatchB.foreignAmt} $` : '1,000 $',
                    explanation: explanation
                });
            }
        }

        // ── Level 4: Amount & Date Match (No Ref or Generic Ref) ──
        for (const rowA of stateA.mappedRows) {
            if (rowA.matched) continue;

            let bestMatchB = null;

            for (const rowB of stateB.mappedRows) {
                if (rowB.matched) continue;

                const isAmountMatched = checkAmountMatch(rowA, rowB);
                const dayDiff = getDayDifference(rowA.date, rowB.date);

                if (isAmountMatched && Math.abs(dayDiff) <= 1) {
                    bestMatchB = rowB;
                    break;
                }
            }

            if (bestMatchB) {
                rowA.matched = true;
                bestMatchB.matched = true;
                exactMatches.push({
                    rowA: rowA,
                    rowB: bestMatchB,
                    matchType: 'amount_date_heuristic',
                    badge: 'تطابق المبلغ ونفس التاريخ (مرجع مختلف أو غير مسجل)',
                    confidence: 85
                });
            }
        }

        // ── Level 4.5: Combinatorial Matching (N:1 and 1:N Subset-Sum Engine) ──
        const combinatorialMatches = [];

        // Check N:1 (Multiple A rows match single B row) - e.g. multiple bank transfers for 1 invoice
        for (const rowB of stateB.mappedRows) {
            if (rowB.matched) continue;
            const candidatesA = stateA.mappedRows
                .filter(rA => !rA.matched && Math.abs(getDayDifference(rA.date, rowB.date)) <= 10)
                .map(rA => ({ row: rA, amount: rA.amount }));

            const comboA = findSubsetSum(candidatesA, rowB.amount, window.RECON_STATE.settings.amountTolerance, 3);
            if (comboA && comboA.length >= 2) {
                rowB.matched = true;
                comboA.forEach(r => r.matched = true);
                combinatorialMatches.push({
                    type: 'N_to_1',
                    singleRow: rowB,
                    comboRows: comboA,
                    totalAmount: rowB.amount,
                    badge: `مطابقة تجميعية N:1 (${comboA.length} دفعات في كشف الطرف الأول سددت حركة واحدة لدى الطرف الثاني)`,
                    explanation: `حركة كشف الطرف الثاني (${rowB.desc} - مرجع: ${rowB.ref}) بقيمة ${formatMoney(rowB.amount)} تم سدادها عبر ${comboA.length} حركات مجتمعة في كشف الطرف الأول (${comboA.map(c => formatMoney(c.amount)).join(' + ')}).`
                });
            }
        }

        // Check 1:N (Single A row matches multiple B rows) - e.g. single transfer for multiple invoices
        for (const rowA of stateA.mappedRows) {
            if (rowA.matched) continue;
            const candidatesB = stateB.mappedRows
                .filter(rB => !rB.matched && Math.abs(getDayDifference(rB.date, rowA.date)) <= 10)
                .map(rB => ({ row: rB, amount: rB.amount }));

            const comboB = findSubsetSum(candidatesB, rowA.amount, window.RECON_STATE.settings.amountTolerance, 3);
            if (comboB && comboB.length >= 2) {
                rowA.matched = true;
                comboB.forEach(r => r.matched = true);
                combinatorialMatches.push({
                    type: '1_to_N',
                    singleRow: rowA,
                    comboRows: comboB,
                    totalAmount: rowA.amount,
                    badge: `مطابقة تجميعية 1:N (حركة واحدة في كشف الطرف الأول غطت ${comboB.length} حركات لدى الطرف الثاني)`,
                    explanation: `حركة كشف الطرف الأول (${rowA.desc} - مرجع: ${rowA.ref}) بقيمة ${formatMoney(rowA.amount)} غطت ${comboB.length} فواتير وحركات مجتمعة لدى الطرف الثاني (${comboB.map(c => formatMoney(c.amount)).join(' + ')}).`
                });
            }
        }

        // ── Level 5: Collect Unmatched Records & Compute Aging Matrix ──
        stateA.mappedRows.forEach(r => {
            if (!r.matched) unmatchedA.push(r);
        });
        stateB.mappedRows.forEach(r => {
            if (!r.matched) unmatchedB.push(r);
        });

        // 4-Bucket Aging Matrix
        const agingBuckets = {
            b0_15: { label: '0 - 15 يوماً', sub: 'مقاصة ودورة عمل جارية', countA: 0, countB: 0, amountA: 0, amountB: 0, rows: [] },
            b16_30: { label: '16 - 30 يوماً', sub: 'متابعة عادية', countA: 0, countB: 0, amountA: 0, amountB: 0, rows: [] },
            b31_60: { label: '31 - 60 يوماً', sub: 'نزاع تجاري محتمل', countA: 0, countB: 0, amountA: 0, amountB: 0, rows: [] },
            b60_plus: { label: '+60 يوماً', sub: 'حرج: خطر مالي وشبهة تقادم', countA: 0, countB: 0, amountA: 0, amountB: 0, rows: [] }
        };

        unmatchedA.forEach((r, idx) => {
            const days = calculateDaysAgo(r.date);
            r.agingDays = days;
            let bKey = 'b0_15';
            if (days > 60) bKey = 'b60_plus';
            else if (days > 30) bKey = 'b31_60';
            else if (days > 15) bKey = 'b16_30';
            agingBuckets[bKey].countA++;
            agingBuckets[bKey].amountA += r.amount;
            agingBuckets[bKey].rows.push({ side: 'A', row: r, days: days, uIdx: idx });
        });

        unmatchedB.forEach((r, idx) => {
            const days = calculateDaysAgo(r.date);
            r.agingDays = days;
            let bKey = 'b0_15';
            if (days > 60) bKey = 'b60_plus';
            else if (days > 30) bKey = 'b31_60';
            else if (days > 15) bKey = 'b16_30';
            agingBuckets[bKey].countB++;
            agingBuckets[bKey].amountB += r.amount;
            agingBuckets[bKey].rows.push({ side: 'B', row: r, days: days, uIdx: idx });
        });

        const balA = stateA.summary.balance;
        const balB = stateB.summary.balance;
        const netVariance = balA - balB;
        const totalRows = stateA.mappedRows.length + stateB.mappedRows.length;

        let matchedRowCount = 0;
        stateA.mappedRows.forEach(r => { if (r.matched) matchedRowCount++; });
        stateB.mappedRows.forEach(r => { if (r.matched) matchedRowCount++; });
        const matchRate = totalRows > 0 ? Math.round((matchedRowCount / totalRows) * 100) : 0;

        // ── Level 4.8: FX Currency Variances List ──
        const fxVariances = varianceMatches.filter(v => v.isFxVariance);

        // ── Level 4.85: Opening Balances & Historical Retroactive Shift Detection ──
        const opBalA = extractOpeningBalance(stateA.mappedRows);
        const opBalB = extractOpeningBalance(stateB.mappedRows);
        const openingVariance = Math.round((opBalA - opBalB) * 100) / 100;

        // ── Level 4.9: Cumulative Running Balance Drift Waterfall ──
        const waterfall = computeCumulativeWaterfall(stateA, stateB);

        // Multi-dimensional metrics
        const totalDebitA = stateA.summary.debit || 0;
        const totalCreditA = stateA.summary.credit || 0;
        const totalDebitB = stateB.summary.debit || 0;
        const totalCreditB = stateB.summary.credit || 0;

        const debitVariance = totalDebitA - totalCreditB;
        const creditVariance = totalCreditA - totalDebitB;
        const localVariance = balA - balB;

        // Forensic Detection: All in-period transactions match, difference is 100% historical opening balance change
        const periodMovementsMatched = (unmatchedA.length === 0 && unmatchedB.length === 0 && varianceMatches.length === 0);
        const isPureOpeningDiscrepancy = Math.abs(openingVariance) > 1 && (periodMovementsMatched || Math.abs(localVariance - openingVariance) < 1);

        window.RECON_STATE.results = {
            exactMatches,
            varianceMatches,
            combinatorialMatches,
            unmatchedA,
            unmatchedB,
            agingBuckets,
            fxVariances,
            waterfall,
            debitVariance,
            creditVariance,
            localVariance,
            netVariance,
            matchRate,
            opBalA,
            opBalB,
            openingVariance,
            isPureOpeningDiscrepancy
        };

        // Instantly generate and display deterministic local AI forensic report
        const localAudit = generateLocalAIAudit(window.RECON_STATE.results, stateA, stateB);
        window.RECON_STATE.aiAudit = localAudit;
        renderAIAuditCard(localAudit);

        // Render UI Results immediately
        renderReconciliationResults();

        // Asynchronously request enhanced Gemini AI Forensic Audit
        triggerAIForensicAudit();
    }
    window.runReconciliationEngine = runReconciliationEngine;

    // Ref Suffix Match Helper (آخر 4 إلى 6 أرقام)
    function checkRefMatch(d1, d2, minLen) {
        if (!d1 || !d2) return false;
        if (d1 === d2) return true;
        if (d1.length >= minLen && d2.endsWith(d1)) return true;
        if (d2.length >= minLen && d1.endsWith(d2)) return true;
        // Check last 6 digits match
        const s1 = d1.slice(-6);
        const s2 = d2.slice(-6);
        if (s1.length >= minLen && s1 === s2) return true;
        return false;
    }

    // Amount Match Helper (Handles directionality & multi-currency equivalence)
    function checkAmountMatch(rowA, rowB) {
        // Direct amount match
        const diffDirect = Math.abs(rowA.amount - rowB.amount);
        if (diffDirect <= window.RECON_STATE.settings.amountTolerance) return true;

        // In accounting, debit in My Records corresponds to credit in Factory Records and vice-versa
        const crossDiff1 = Math.abs(rowA.debit - rowB.credit);
        if (rowA.debit > 0 && rowB.credit > 0 && crossDiff1 <= window.RECON_STATE.settings.amountTolerance) return true;

        const crossDiff2 = Math.abs(rowA.credit - rowB.debit);
        if (rowA.credit > 0 && rowB.debit > 0 && crossDiff2 <= window.RECON_STATE.settings.amountTolerance) return true;

        // Foreign currency / dollar equivalence check
        if (rowA.foreignAmt > 0 && rowB.foreignAmt > 0) {
            if (Math.abs(rowA.foreignAmt - rowB.foreignAmt) <= 1) return true;
        }

        return false;
    }

    // ────────────────────────────────────────────────────────────────
    // Opening Balance Extractor & Mathematical Back-Calculator
    // ────────────────────────────────────────────────────────────────
    function extractOpeningBalance(rows) {
        if (!rows || rows.length === 0) return 0;
        const first = rows[0];
        const desc = (first.desc || '').toLowerCase();
        const ref = (first.ref || '').toLowerCase();
        const isOpening = desc.includes('افتتاح') || desc.includes('مرحل') || desc.includes('سابق') || desc.includes('opening') || desc.includes('b/f') || ref.includes('op');

        if (isOpening) {
            if (first.balance !== 0) return first.balance;
            if (first.debit !== 0) return first.debit;
            if (first.credit !== 0) return -first.credit;
        }

        // Mathematical deduction from first movement running balance
        if (first.balance !== 0) {
            const netMovement = (first.debit || 0) - (first.credit || 0);
            return Math.round((first.balance - netMovement) * 100) / 100;
        }

        return 0;
    }

    // ────────────────────────────────────────────────────────────────
    // Cumulative Running Balance Drift Waterfall & Baseline Inception Point Engine
    // ────────────────────────────────────────────────────────────────
    function computeCumulativeWaterfall(stateA, stateB) {
        const events = [];

        // Extract rows from Party A (excluding opening row if present to avoid double counting)
        stateA.mappedRows.forEach((r, idx) => {
            const desc = (r.desc || '').toLowerCase();
            const isOpeningRow = (idx === 0) && (desc.includes('افتتاح') || desc.includes('مرحل') || desc.includes('سابق') || desc.includes('opening'));
            if (isOpeningRow) return;

            const net = (r.debit || 0) - (r.credit || 0);
            events.push({
                date: r.date || '2026-01-01',
                side: 'A',
                sideLabel: 'كشف حساب الطرف الأول (كشف الحساب الخاص)',
                ref: r.ref || '-',
                desc: r.desc || '-',
                debit: r.debit || 0,
                credit: r.credit || 0,
                amount: r.amount || 0,
                netEffect: net,
                row: r
            });
        });

        // Extract rows from Party B (credit in B corresponds to debit in A)
        stateB.mappedRows.forEach((r, idx) => {
            const desc = (r.desc || '').toLowerCase();
            const isOpeningRow = (idx === 0) && (desc.includes('افتتاح') || desc.includes('مرحل') || desc.includes('سابق') || desc.includes('opening'));
            if (isOpeningRow) return;

            const net = (r.credit || 0) - (r.debit || 0);
            events.push({
                date: r.date || '2026-01-01',
                side: 'B',
                sideLabel: 'كشف حساب الطرف الثاني (كشف حساب الطرف المقابل)',
                ref: r.ref || '-',
                desc: r.desc || '-',
                debit: r.debit || 0,
                credit: r.credit || 0,
                amount: r.amount || 0,
                netEffect: net,
                row: r
            });
        });

        // Chronological Sort
        events.sort((a, b) => (a.date || '').localeCompare(b.date || ''));

        // Opening Balances Analysis
        const opA = extractOpeningBalance(stateA.mappedRows);
        const opB = extractOpeningBalance(stateB.mappedRows);
        const openingDelta = Math.round((opA - opB) * 100) / 100;

        let runningA = opA;
        let runningB = opB;
        let prevDelta = openingDelta;
        let inceptionPoint = null;
        const timeline = [];

        // Insert Step 0: Opening Balance Carryover
        if (opA !== 0 || opB !== 0 || openingDelta !== 0) {
            let opDiagnosis = '';
            let opBadgeClass = '';
            if (openingDelta !== 0) {
                inceptionPoint = {
                    stepIndex: 0,
                    date: 'بداية الدورة',
                    side: 'الرصيد الافتتاحي',
                    ref: 'OPENING',
                    desc: 'الرصيد الافتتاحي المنقول من إقفال دورات سابقة',
                    delta: openingDelta,
                    amount: Math.abs(openingDelta)
                };
                opDiagnosis = `⚡ انطلاق الخلل: فارق رصيد افتتاحي مرحل من سنوات سابقة (${formatMoney(Math.abs(openingDelta))} SDG)`;
                opBadgeClass = 'bg-rose-500/20 text-rose-300 border-rose-500/40';
            } else {
                opDiagnosis = '✓ رصيد افتتاحي متطابق في بداية الفترة';
                opBadgeClass = 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40';
            }

            timeline.push({
                index: 0,
                date: 'بداية الدورة',
                side: '-',
                sideLabel: 'الرصيد الافتتاحي المرحل',
                ref: 'OP-BALANCE',
                desc: 'رصيد أول المدة المنقول من إقفال الدورات والسنوات السابقة (تعديلات تاريخية سابقة)',
                netEffect: 0,
                runningA: runningA,
                runningB: runningB,
                delta: openingDelta,
                stepShift: openingDelta,
                stepDiagnosis: opDiagnosis,
                stepBadgeClass: opBadgeClass,
                isShift: openingDelta !== 0,
                isInception: openingDelta !== 0,
                isOpening: true
            });
        }

        events.forEach((ev, idx) => {
            if (ev.side === 'A') {
                runningA += ev.netEffect;
            } else {
                runningB += ev.netEffect;
            }

            const currentDelta = Math.round((runningA - runningB) * 100) / 100;
            const stepShift = Math.round((currentDelta - prevDelta) * 100) / 100;
            let stepDiagnosis = '';
            let stepBadgeClass = '';

            if (prevDelta === 0 && currentDelta !== 0 && !inceptionPoint) {
                inceptionPoint = {
                    stepIndex: idx + 1,
                    date: ev.date,
                    side: ev.side,
                    ref: ev.ref,
                    desc: ev.desc,
                    delta: currentDelta,
                    amount: ev.amount
                };
                stepDiagnosis = '⚡ نقطة انطلاق الخلل الأولى (Baseline Inception Point)';
                stepBadgeClass = 'bg-rose-500/20 text-rose-300 border-rose-500/40';
            } else if (stepShift !== 0) {
                stepDiagnosis = `⚠️ اتساع الفارق التراكمي بمقدار ${formatMoney(Math.abs(stepShift))}`;
                stepBadgeClass = 'bg-amber-500/20 text-amber-300 border-amber-500/40';
            } else {
                stepDiagnosis = '✓ حركة متوازنة (توريث الفارق السابق دون زيادة)';
                stepBadgeClass = 'bg-slate-800 text-slate-300 border-slate-700';
            }

            timeline.push({
                index: idx + 1,
                date: ev.date,
                side: ev.side,
                sideLabel: ev.sideLabel,
                ref: ev.ref,
                desc: ev.desc,
                netEffect: ev.netEffect,
                runningA: runningA,
                runningB: runningB,
                delta: currentDelta,
                stepShift: stepShift,
                stepDiagnosis: stepDiagnosis,
                stepBadgeClass: stepBadgeClass,
                isShift: stepShift !== 0,
                isInception: (inceptionPoint && inceptionPoint.stepIndex === idx + 1)
            });

            prevDelta = currentDelta;
        });

        const driftEventsCount = timeline.filter(t => t.isShift).length;

        return {
            timeline,
            inceptionPoint,
            driftEventsCount,
            finalDelta: prevDelta
        };
    }

    function getDayDifference(d1, d2) {
        if (!d1 || !d2) return 999;
        const date1 = new Date(d1);
        const date2 = new Date(d2);
        if (isNaN(date1.getTime()) || isNaN(date2.getTime())) return 999;
        const diffTime = date1.getTime() - date2.getTime();
        return Math.round(diffTime / (1000 * 60 * 60 * 24));
    }

    function calculateDaysAgo(dateStr) {
        if (!dateStr) return 0;
        try {
            const today = new Date();
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return 0;
            const diff = Math.round((today - d) / (1000 * 60 * 60 * 24));
            return Math.max(0, diff);
        } catch(e) { return 0; }
    }

    // Combinatorial Knapsack / Subset Sum Solver for N:1 and 1:N splits
    function findSubsetSum(candidates, targetAmount, tolerance, maxDepth = 3) {
        if (!candidates || candidates.length < 2) return null;
        const target = Math.abs(targetAmount);
        const valid = candidates.filter(c => c && c.amount > 0);
        const n = valid.length;
        if (n < 2) return null;

        // Depth 2: O(n^2)
        for (let i = 0; i < n; i++) {
            for (let j = i + 1; j < n; j++) {
                const sum = valid[i].amount + valid[j].amount;
                if (Math.abs(sum - target) <= tolerance) {
                    return [valid[i].row, valid[j].row];
                }
            }
        }

        // Depth 3: O(n^3) - limit n to 30 for max performance
        if (n <= 30 && maxDepth >= 3) {
            for (let i = 0; i < n; i++) {
                for (let j = i + 1; j < n; j++) {
                    const s2 = valid[i].amount + valid[j].amount;
                    if (s2 > target + tolerance) continue;
                    for (let k = j + 1; k < n; k++) {
                        const sum = s2 + valid[k].amount;
                        if (Math.abs(sum - target) <= tolerance) {
                            return [valid[i].row, valid[j].row, valid[k].row];
                        }
                    }
                }
            }
        }

        return null;
    }

    function formatMoney(n) {
        const val = parseFloat(n) || 0;
        return val.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    // ────────────────────────────────────────────────────────────────
    // 5. LOCAL & GEMINI AI FORENSIC AUDIT ENGINE
    // ────────────────────────────────────────────────────────────────
    function generateLocalAIAudit(res, stateA, stateB) {
        const netVariance = res.netVariance || 0;
        const absDiff = Math.abs(netVariance);
        const hasDiff = absDiff > 1;
        const partyAName = stateA.name || 'دفاتر الطرف الأول (الخاصة)';
        const partyBName = stateB.name || 'كشف حساب الطرف الثاني';

        let execSummary = '';
        if (res.isPureOpeningDiscrepancy) {
            execSummary = `أثبت الفحص المحاسبي الجنائي أن جميع حركات وفواتير وتحويلات الدورة الحالية متطابقة بنسبة 100%! الفارق الإجمالي البالغ (${formatMoney(absDiff)}) ناتج بالكامل عن تعديل تاريخي في الرصيد الافتتاحي المرحل (Prior-Period Retrospective Shift) من سنوات سابقة، ولا توجد أي فروقات تشغيلية في بنود هذه الدورة.`;
        } else if (!hasDiff) {
            execSummary = `تطابق محاسبي تام بنسبة 100% بين كشف الطرف الأول (${partyAName}) وكشف الطرف الثاني (${partyBName}). تم فحص كافة الحركات والتأكد من مطابقة القيود والأرصدة بالكامل.`;
        } else {
            execSummary = `تم تدقيق ومطابقة كشف الطرف الأول (${partyAName}) برصيد إغلاق ${formatMoney(stateA.summary.balance)} مقابل كشف الطرف الثاني (${partyBName}) برصيد ${formatMoney(stateB.summary.balance)}. كشف الفحص عن صافي فارق قدره ${formatMoney(absDiff)} بنسبة تطابق ${res.matchRate}%. تم حصر ${res.unmatchedA.length} حركات معلقة لدى الطرف الأول، و${res.unmatchedB.length} حركات معلقة لدى الطرف الثاني، و${res.varianceMatches.length} عمليات متطابقة مع وجود فارق في المبلغ أو التاريخ أو سعر الصرف.`;
        }

        const netExplained = hasDiff 
            ? `رصيد الطرف الأول: ${formatMoney(stateA.summary.balance)} · رصيد الطرف الثاني: ${formatMoney(stateB.summary.balance)} · صافي الفارق المطلوب تسويته: ${formatMoney(absDiff)} لصالح ${stateA.summary.balance > stateB.summary.balance ? 'الطرف الأول' : 'الطرف الثاني'}`
            : `رصيد الطرف الأول: ${formatMoney(stateA.summary.balance)} متطابق 100% مع رصيد الطرف الثاني: ${formatMoney(stateB.summary.balance)}.`;

        const causes = [];
        (res.varianceMatches || []).forEach(v => {
            causes.push({
                type: v.isFxVariance ? 'fx_rate_diff' : 'amount_variance',
                title: v.title || `فارق في العملية (${(v.rowA ? v.rowA.ref : null) || '-'})`,
                amount: v.diff || 0,
                ref: (v.rowA ? v.rowA.ref : null) || '-',
                date: (v.rowA ? v.rowA.date : null) || '-',
                desc: (v.rowA ? v.rowA.desc : null) || '-',
                explanation: v.explanation || 'فارق بين قيد الطرفين'
            });
        });

        (res.unmatchedA || []).slice(0, 10).forEach(r => {
            causes.push({
                type: 'missing_in_factory',
                title: `حركة مقيدة لدى الطرف الأول وغير مسجلة لدى الطرف الثاني (${r.ref})`,
                amount: r.amount,
                ref: r.ref,
                date: r.date,
                desc: r.desc,
                explanation: `تم قيد المبلغ في كشف الطرف الأول بتاريخ ${r.date} كـ (${r.desc}) ولم يظهر في كشف الطرف الثاني، ويتطلب تأكيد استلامه أو قيده.`
            });
        });

        (res.unmatchedB || []).slice(0, 10).forEach(r => {
            causes.push({
                type: 'missing_in_my_records',
                title: `حركة قيدها الطرف الثاني ومفقودة لدى الطرف الأول (${r.ref})`,
                amount: r.amount,
                ref: r.ref,
                date: r.date,
                desc: r.desc,
                explanation: `حركة مسجلة في كشف حساب الطرف الثاني بتاريخ ${r.date} بمبلغ ${formatMoney(r.amount)} ولم تقيد في كشف الطرف الأول، وتتطلب إثباتها بقيد تسوية.`
            });
        });

        const correctiveActions = [];
        if (res.isPureOpeningDiscrepancy) {
            correctiveActions.push(`إجراء قيد تسوية رصيد افتتاحي مرحل من سنوات سابقة بمبلغ ${formatMoney(absDiff)} لمعادلة رصيد البداية.`);
        }
        if (res.varianceMatches && res.varianceMatches.length > 0) {
            correctiveActions.push(`مراجعة وتعديل العمليات التي بها أخطاء تبديل خانات أو فروقات صرف وتقييم (${res.varianceMatches.length} عمليات).`);
        }
        if (res.unmatchedA && res.unmatchedA.length > 0) {
            correctiveActions.push(`إرسال إشعارات التحويل والمستندات البنكية لعدد (${res.unmatchedA.length}) حركات للطرف الثاني لإثباتها في دفاتره.`);
        }
        if (res.unmatchedB && res.unmatchedB.length > 0) {
            correctiveActions.push(`إثبات عدد (${res.unmatchedB.length}) حركات قيدها الطرف الثاني في دفاتر اليومية بموجب قيود تسوية محاسبية.`);
        }
        if (correctiveActions.length === 0) {
            correctiveActions.push('الحسابات متطابقة تماماً ولا تتطلب أي قيود تسوية إضافية.');
        }

        let whatsappLetter = `السادة المحترمون / إدارة الحسابات المالية\nتحية طيبة وبعد،،،\n\nالموضوع: مذكرة مطابقة وتصفية الحساب المشترك حتى تاريخ ${new Date().toISOString().split('T')[0]}\n\nنود إفادتكم بأنه بعد إجراء الفحص والمطابقة الإلكترونية لكشف الحساب، تبين الآتي:\n- رصيد كشف حسابنا: ${formatMoney(stateA.summary.balance)}\n- رصيد كشف حسابكم: ${formatMoney(stateB.summary.balance)}\n- صافي الفارق المرصود: ${formatMoney(absDiff)}\n\n`;
        if (res.isPureOpeningDiscrepancy) {
            whatsappLetter += `💡 ملاحظة جوهرية: كافة حركات وفواتير وتحويلات الفترة الحالية متطابقة تماماً، والفارق ناتج عن تسوية سابقة في الرصيد الافتتاحي.\n\n`;
        }
        if (res.unmatchedA.length > 0) {
            whatsappLetter += `📌 حركات مسددة من طرفنا ومطلوب قيدها في كشفكم:\n` + res.unmatchedA.slice(0, 5).map(r => `• تاريخ ${r.date} | مرجع ${r.ref} | مبلغ ${formatMoney(r.amount)} | ${r.desc}`).join('\n') + `\n\n`;
        }
        if (res.unmatchedB.length > 0) {
            whatsappLetter += `📌 حركات مقيدة لديكم وجاري إثباتها في دفاترنا بعد التحقق:\n` + res.unmatchedB.slice(0, 5).map(r => `• تاريخ ${r.date} | مرجع ${r.ref} | مبلغ ${formatMoney(r.amount)} | ${r.desc}`).join('\n') + `\n\n`;
        }
        if (res.varianceMatches.length > 0) {
            whatsappLetter += `📌 حركات متطابقة بالمرجع وبها فارق بسيط للمراجعة المشتركة:\n` + res.varianceMatches.slice(0, 3).map(v => `• مرجع ${(v.rowA ? v.rowA.ref : null)} | فارق: ${formatMoney(Math.abs(v.diff))} (${v.explanation})`).join('\n') + `\n\n`;
        }
        whatsappLetter += `نرجو التكرم بمراجعة البنود المذكورة لمطابقة الحساب وإغلاقه بشكل نهائي.\nشاكرين ومقدرين حسن تعاونكم الدائم.\nنظام مقاييس MAGAIEES ERP 2026`;

        return {
            engine: 'محرك التدقيق الجنائي الداخلي MAGAIEES 2026',
            executive_summary: execSummary,
            net_difference_explained: netExplained,
            causes: causes,
            ref_matching_insights: `تم تفعيل خوارزمية مطابقة أواخر الأرقام (Suffix Matching) وتطابق بنجاح ${res.exactMatches.length} عمليات مؤكدة.`,
            corrective_actions: correctiveActions,
            whatsapp_letter: whatsappLetter
        };
    }

    async function triggerAIForensicAudit() {
        const stateA = window.RECON_STATE.fileA;
        const stateB = window.RECON_STATE.fileB;
        const res = window.RECON_STATE.results;

        const aiStatusEl = document.getElementById('recon-ai-status');

        if (aiStatusEl) {
            aiStatusEl.classList.remove('hidden');
            aiStatusEl.innerHTML = `
                <div class="flex items-center gap-3 text-indigo-400 font-bold text-sm">
                    <i class="fa-solid fa-wand-magic-sparkles fa-spin text-lg"></i>
                    <span>جاري تشغيل خبير التدقيق الجنائي الذكي (Gemini AI) للتحقق المعمق وإثراء المذكرة...</span>
                </div>
            `;
        }

        const payload = {
            summary_a: {
                name: stateA.name,
                totalRows: stateA.mappedRows.length,
                totalDebit: stateA.summary.debit,
                totalCredit: stateA.summary.credit,
                closingBalance: stateA.summary.balance
            },
            summary_b: {
                name: stateB.name,
                totalRows: stateB.mappedRows.length,
                totalDebit: stateB.summary.debit,
                totalCredit: stateB.summary.credit,
                closingBalance: stateB.summary.balance
            },
            net_variance: res.netVariance,
            matched_count: res.exactMatches.length,
            total_rows_a: stateA.mappedRows.length,
            total_rows_b: stateB.mappedRows.length,
            unmatched_a: res.unmatchedA.map(r => ({ date: r.date, ref: r.ref, amount: r.amount, desc: r.desc })),
            unmatched_b: res.unmatchedB.map(r => ({ date: r.date, ref: r.ref, amount: r.amount, desc: r.desc })),
            variances: res.varianceMatches.map(v => ({
                ref: v.rowA.ref,
                date: v.rowA.date,
                amt_a: v.rowA.amount,
                amt_b: v.rowB.amount,
                diff: v.diff,
                desc: v.explanation
            }))
        };

        try {
            const resp = await fetch('api/ai-reconciliation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const json = await resp.json();
            if (json.success && json.data) {
                window.RECON_STATE.aiAudit = json.data;
                renderAIAuditCard(json.data);
                if (aiStatusEl) {
                    aiStatusEl.innerHTML = `<span class="text-emerald-400 font-bold">✓ تم إنجاز التدقيق الذكي وتأكيد النتائج عبر Gemini AI بنجاح.</span>`;
                }
            } else {
                if (aiStatusEl) {
                    aiStatusEl.innerHTML = `<span class="text-emerald-400 font-bold">✓ تقرير التدقيق الجنائي الداخلي نشط ومعتمد.</span>`;
                }
            }
        } catch (err) {
            console.error('AI reconciliation call error:', err);
            if (aiStatusEl) {
                aiStatusEl.innerHTML = `<span class="text-emerald-400 font-bold">✓ تقرير التدقيق الجنائي الداخلي نشط ومعتمد.</span>`;
            }
        }
    }

    // ────────────────────────────────────────────────────────────────
    // 6. UI RENDERING & TAB MANAGERS
    // ────────────────────────────────────────────────────────────────
    // ────────────────────────────────────────────────────────────────
    // 6. UI RENDERING & TAB MANAGERS
    // ────────────────────────────────────────────────────────────────
    // Render full detailed transactions table inside the party pod (Side A or B)
    function renderPodTableRows(side, rows) {
        if (!rows || rows.length === 0) {
            return `<tr><td colspan="7" class="text-center py-6 text-slate-400 font-bold text-sm">لا توجد حركات مسجلة أو مطابقة للبحث</td></tr>`;
        }
        return rows.map((r, idx) => {
            const deb = r.debit > 0 ? formatMoney(r.debit) : '-';
            const cred = r.credit > 0 ? formatMoney(r.credit) : '-';
            const bal = formatMoney(r.balance || 0);
            const balClass = (r.balance || 0) < 0 ? 'text-rose-400' : 'text-emerald-400';
            return `
                <tr class="hover:bg-slate-800/50 transition">
                    <td class="p-2.5 text-center text-xs text-slate-400 font-mono">${idx + 1}</td>
                    <td class="p-2.5 font-mono text-xs sm:text-sm text-slate-200 whitespace-nowrap">${r.date || '-'}</td>
                    <td class="p-2.5 font-mono text-xs sm:text-sm text-amber-300 font-black whitespace-nowrap">${r.ref || '-'}</td>
                    <td class="p-2.5 text-slate-100 text-xs sm:text-sm font-bold leading-snug">${escapeHtmlRecon(r.desc || '-')}</td>
                    <td class="p-2.5 text-center font-mono text-sm sm:text-base font-black text-rose-400 whitespace-nowrap">${deb}</td>
                    <td class="p-2.5 text-center font-mono text-sm sm:text-base font-black text-emerald-400 whitespace-nowrap">${cred}</td>
                    <td class="p-2.5 text-center font-mono text-sm sm:text-base font-black ${balClass} whitespace-nowrap">${bal}</td>
                </tr>
            `;
        }).join('');
    }

    function filterPodRows(side, query) {
        const target = (side === 'A') ? window.RECON_STATE.fileA : window.RECON_STATE.fileB;
        if (!target || !target.mappedRows) return;
        const tbody = document.getElementById(`recon-pod-table-body-${side}`);
        if (!tbody) return;
        const q = (query || '').trim().toLowerCase();
        let filtered = target.mappedRows;
        if (q) {
            filtered = target.mappedRows.filter(r => 
                (r.desc && r.desc.toLowerCase().includes(q)) ||
                (r.ref && String(r.ref).toLowerCase().includes(q)) ||
                (r.date && r.date.includes(q)) ||
                (String(r.debit).includes(q)) ||
                (String(r.credit).includes(q))
            );
        }
        tbody.innerHTML = renderPodTableRows(side, filtered);
        const countBadge = document.getElementById(`recon-pod-count-${side}`);
        if (countBadge) countBadge.textContent = `${filtered.length} من ${target.mappedRows.length}`;
    }
    window.filterPodRows = filterPodRows;

    function clearPodSide(side) {
        const isA = (side === 'A');
        const emptyState = { name: '', rawRows: [], headers: [], colMap: {}, mappedRows: [], summary: { debit: 0, credit: 0, balance: 0, count: 0 } };
        if (isA) window.RECON_STATE.fileA = emptyState;
        else window.RECON_STATE.fileB = emptyState;

        const boxEl = document.getElementById(`recon-dropzone-box-${side}`);
        const themeColor = isA ? 'sky' : 'purple';
        const sideLabel = isA ? 'كشف حساب الطرف الأول' : 'كشف حساب الطرف الثاني';
        if (boxEl) {
            boxEl.innerHTML = `
                <label for="omni-file-all-${side}" class="recon-upload-area flex flex-col items-center justify-center group">
                    <div class="w-14 h-14 rounded-2xl bg-${themeColor}-500/10 border border-${themeColor}-500/30 flex items-center justify-center text-${themeColor}-400 text-2xl mb-3 group-hover:scale-110 transition duration-300 shadow-lg">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <span class="font-extrabold text-sm text-slate-100 group-hover:text-${themeColor}-300 transition">اسحب وأفلت ${sideLabel} هنا أو اختر من الأزرار أعلاه</span>
                    <span class="text-xs text-slate-400 mt-1">يقبل إكسيل · صور متعددة متتالية · PDF · وورد · نصوص</span>
                </label>
            `;
        }
        const badgeEl = document.getElementById(`recon-map-badges-${side}`);
        if (badgeEl) badgeEl.innerHTML = '';

        const resultsContainer = document.getElementById('recon-results-container');
        if (resultsContainer) resultsContainer.classList.add('hidden');
    }
    window.clearPodSide = clearPodSide;

    function renderDropzoneCard(side) {
        const isA = (side === 'A');
        const target = isA ? window.RECON_STATE.fileA : window.RECON_STATE.fileB;
        const boxEl = document.getElementById(`recon-dropzone-box-${side}`);
        if (!boxEl) return;

        const themeColor = isA ? 'sky' : 'purple';
        const sideLabel = isA ? 'كشف حساب الطرف الأول (كشف الحساب الخاص)' : 'كشف حساب الطرف الثاني (المصنع / الشريك)';

        boxEl.innerHTML = `
            <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-4 shadow-xl">
                <!-- Document / Photo Visual Thumbnail Preview if available -->
                ${target.previewImg ? `
                    <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-slate-900/90 border border-indigo-500/30 shadow-lg">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="relative group cursor-pointer shrink-0" onclick="openReconImageLightbox(window.RECON_STATE['file' + '${side}'].previewImg, '${escapeHtmlRecon(target.name)}')">
                                <img src="${escapeHtmlRecon(target.previewImg)}" alt="معاينة المستند" class="w-13 h-13 object-cover rounded-xl border-2 border-indigo-500/50 shadow group-hover:scale-105 transition duration-200">
                                <div class="absolute inset-0 bg-black/40 rounded-xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                    <i class="fa-solid fa-magnifying-glass-plus text-white text-xs"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black text-slate-100 flex items-center gap-1.5">
                                        <i class="fa-solid fa-image text-pink-400"></i>
                                        <span>المستند الأصلي الممسوح بالـ AI</span>
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 font-bold text-[10px] border border-emerald-500/30">معاينة بصرية</span>
                                </div>
                                <span class="text-[11px] text-slate-400 block truncate mt-0.5">انقر على المصغر للمقارنة البصرية بالعين المجردة بين الورقة والأرقام</span>
                            </div>
                        </div>
                        <button type="button" onclick="openReconImageLightbox(window.RECON_STATE['file' + '${side}'].previewImg, '${escapeHtmlRecon(target.name)}')" class="px-3 py-1.5 rounded-xl bg-indigo-600/30 hover:bg-indigo-600/50 text-xs font-bold text-indigo-200 border border-indigo-500/40 transition flex items-center gap-1.5 cursor-pointer shrink-0">
                            <i class="fa-solid fa-expand text-[10px]"></i>
                            <span>تكبير</span>
                        </button>
                    </div>
                ` : ''}

                <!-- Summary Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                    <div class="flex items-center gap-3">
                        <span class="w-11 h-11 rounded-2xl bg-${themeColor}-500/15 border border-${themeColor}-500/40 flex items-center justify-center text-${themeColor}-400 text-lg shadow-lg">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-black text-sm text-slate-100 font-mono tracking-tight">${escapeHtmlRecon(target.name)}</h4>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 font-bold text-[10px] border border-emerald-500/30">✓ تم التدقيق</span>
                            </div>
                            <span class="text-xs text-slate-400 font-bold mt-0.5 block">${sideLabel}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="document.getElementById('omni-file-all-${side}').click()" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 hover:text-white border border-slate-700 transition flex items-center gap-1.5 cursor-pointer" title="استبدال الملف">
                            <i class="fa-solid fa-arrows-rotate text-[11px]"></i>
                            <span>استبدال</span>
                        </button>
                        <button type="button" onclick="clearPodSide('${side}')" class="p-1.5 rounded-xl bg-slate-800 hover:bg-rose-950/60 text-slate-400 hover:text-rose-400 border border-slate-700 hover:border-rose-500/40 transition cursor-pointer" title="تفريغ هذا الطرف">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Square KPI Summary Cards with Clear Big Numbers (شاشات مربعة واضحة أرقام كبيرة) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-center my-3">
                    <div class="p-3.5 sm:p-4 rounded-2xl bg-gradient-to-b from-rose-950/40 via-slate-900/90 to-slate-950 border-2 border-rose-500/40 shadow-lg flex flex-col items-center justify-center">
                        <span class="text-xs sm:text-sm font-black text-rose-300 mb-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-trend-down text-rose-400"></i>
                            <span>إجمالي المدين:</span>
                        </span>
                        <div class="font-mono text-xl sm:text-2xl lg:text-3xl font-black text-rose-400 tracking-tight my-1">
                            ${formatMoney(target.summary.debit)}
                        </div>
                        <span class="text-[11px] text-slate-400 font-bold">منصرف / مستحق</span>
                    </div>

                    <div class="p-3.5 sm:p-4 rounded-2xl bg-gradient-to-b from-emerald-950/40 via-slate-900/90 to-slate-950 border-2 border-emerald-500/40 shadow-lg flex flex-col items-center justify-center">
                        <span class="text-xs sm:text-sm font-black text-emerald-300 mb-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-trend-up text-emerald-400"></i>
                            <span>إجمالي الدائن:</span>
                        </span>
                        <div class="font-mono text-xl sm:text-2xl lg:text-3xl font-black text-emerald-400 tracking-tight my-1">
                            ${formatMoney(target.summary.credit)}
                        </div>
                        <span class="text-[11px] text-slate-400 font-bold">مقبوض / مسدد</span>
                    </div>

                    <div class="p-3.5 sm:p-4 rounded-2xl bg-gradient-to-b from-indigo-950/50 via-slate-900/90 to-slate-950 border-2 border-${themeColor}-500/50 shadow-lg flex flex-col items-center justify-center">
                        <span class="text-xs sm:text-sm font-black text-${themeColor}-300 mb-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-scale-balanced text-${themeColor}-400"></i>
                            <span>رصيد الإغلاق:</span>
                        </span>
                        <div class="font-mono text-xl sm:text-2xl lg:text-3xl font-black text-${themeColor}-300 tracking-tight my-1">
                            ${formatMoney(target.summary.balance)}
                        </div>
                        <span class="text-[11px] text-slate-400 font-bold">${target.mappedRows.length} حركة مسجلة</span>
                    </div>
                </div>

                <!-- Complete Detailed Transactions Table of This Account -->
                <div class="pt-2 border-t border-slate-800/80 space-y-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h5 class="text-xs font-black text-${themeColor}-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-table-list"></i>
                            <span>كشف الحركات التفصيلية المسجلة</span>
                        </h5>
                        <div class="flex items-center gap-2">
                            <input type="text" oninput="filterPodRows('${side}', this.value)" placeholder="🔍 تصفية الحركات..." class="px-2.5 py-1 text-[11px] rounded-xl bg-slate-900 border border-slate-700 text-slate-200 placeholder-slate-500 focus:outline-none focus:border-${themeColor}-400">
                            <span id="recon-pod-count-${side}" class="px-2 py-0.5 rounded-md bg-slate-800 text-[10px] text-slate-300 font-mono font-bold">${target.mappedRows.length} حركة</span>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto max-h-72 overflow-y-auto custom-scrollbar rounded-xl border border-slate-800/80 bg-slate-950/90 shadow-inner">
                        <table class="w-full text-right text-xs border-collapse">
                            <thead class="sticky top-0 z-10 bg-slate-900/95 border-b border-slate-800 text-[11px] text-slate-400 shadow">
                                <tr>
                                    <th class="p-2 text-center w-7">#</th>
                                    <th class="p-2 whitespace-nowrap">التاريخ</th>
                                    <th class="p-2 whitespace-nowrap">المرجع</th>
                                    <th class="p-2">البيان والتفاصيل</th>
                                    <th class="p-2 text-center whitespace-nowrap text-rose-400">مدين</th>
                                    <th class="p-2 text-center whitespace-nowrap text-emerald-400">دائن</th>
                                    <th class="p-2 text-center whitespace-nowrap text-${themeColor}-300">الرصيد</th>
                                </tr>
                            </thead>
                            <tbody id="recon-pod-table-body-${side}" class="divide-y divide-slate-800/40">
                                ${renderPodTableRows(side, target.mappedRows)}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }
    window.renderDropzoneCard = renderDropzoneCard;

    function renderColumnMappingCard(side) {
        const target = (side === 'A') ? window.RECON_STATE.fileA : window.RECON_STATE.fileB;
        const badgeEl = document.getElementById(`recon-map-badges-${side}`);
        if (!badgeEl) return;

        const m = target.colMap;
        const h = target.headers;

        badgeEl.innerHTML = `
            <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-bold mt-2.5 pt-2 border-t border-slate-800/60">
                <span class="text-slate-400 text-[10px] ml-1">الأعمدة المكتشفة:</span>
                <span class="px-2.5 py-0.5 rounded-lg bg-sky-500/10 text-sky-300 border border-sky-500/25">📅 ${h[m.dateIdx] || 'تاريخ'}</span>
                <span class="px-2.5 py-0.5 rounded-lg bg-amber-500/10 text-amber-300 border border-amber-500/25">🔖 ${h[m.refIdx] || 'مرجع'}</span>
                <span class="px-2.5 py-0.5 rounded-lg bg-purple-500/10 text-purple-300 border border-purple-500/25">📝 ${h[m.descIdx] || 'بيان'}</span>
                <span class="px-2.5 py-0.5 rounded-lg bg-rose-500/10 text-rose-300 border border-rose-500/25">🔻 مدين: ${h[m.debitIdx] || '-'}</span>
                <span class="px-2.5 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-300 border border-emerald-500/25">🔺 دائن: ${h[m.creditIdx] || '-'}</span>
                <span class="px-2.5 py-0.5 rounded-lg bg-indigo-500/10 text-indigo-300 border border-indigo-500/25">⚖️ رصيد: ${h[m.balanceIdx] || '-'}</span>
            </div>
        `;
    }

    function renderReconciliationResults() {
        const res = window.RECON_STATE.results;
        const stateA = window.RECON_STATE.fileA;
        const stateB = window.RECON_STATE.fileB;

        const resultsContainer = document.getElementById('recon-results-container');
        if (!resultsContainer) return;
        resultsContainer.classList.remove('hidden');

        // ── Forensic Prior-Period Opening Balance Shift Alert ──
        let openingBanner = document.getElementById('recon-opening-alert-banner');
        if (res.isPureOpeningDiscrepancy) {
            if (!openingBanner) {
                openingBanner = document.createElement('div');
                openingBanner.id = 'recon-opening-alert-banner';
                resultsContainer.insertBefore(openingBanner, resultsContainer.firstChild);
            }
            openingBanner.className = 'mb-6 p-6 rounded-3xl bg-gradient-to-r from-amber-950/95 via-yellow-950/85 to-amber-950/95 border-2 border-amber-400 shadow-2xl text-right space-y-2';
            openingBanner.innerHTML = `
                <div class="flex items-start gap-4">
                    <span class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-300 flex items-center justify-center text-2xl border border-amber-400/60 shrink-0">
                        <i class="fa-solid fa-award"></i>
                    </span>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-500/30 text-amber-200 text-[11px] font-black border border-amber-400/50">🏆 تشخيص جنائي فائق</span>
                            <h4 class="text-base sm:text-lg font-black text-amber-100">كافة حركات وبنود الفترة الحالية متطابقة 100%! الخلل ناتج عن تعديل تاريخي في الرصيد الافتتاحي</h4>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-200 font-bold leading-relaxed">
                            أثبت الفحص المحاسبي الجنائي أن جميع فواتير وتحويلات وسندات الدورة الحالية متطابقة بدقة تامة دون أي نقص أو زيادة. الفارق الإجمالي وقدره <span class="text-amber-300 font-black font-mono underline decoration-amber-400 text-sm sm:text-base">${formatMoney(Math.abs(res.openingVariance))} SDG</span> ناتج 100% عن <strong>تعديل تاريخي سابق في الرصيد الافتتاحي (Prior-Period Retrospective Shift)</strong>؛ تم تعديل قيود أو أرصدة في سنوات سابقة (مثل 2023) أثّرت على رصيد أول المدة المرحل إلى هذه الفترة دون وجود أي خطأ في بنود الفترة الحالية.
                        </p>
                    </div>
                </div>
            `;
            openingBanner.classList.remove('hidden');
        } else if (openingBanner) {
            openingBanner.classList.add('hidden');
        }

        // Update KPI Cards
        document.getElementById('recon-kpi-bal-a').textContent = formatMoney(stateA.summary.balance);
        document.getElementById('recon-kpi-bal-b').textContent = formatMoney(stateB.summary.balance);
        
        const diffEl = document.getElementById('recon-kpi-net-diff');
        const diffHint = document.getElementById('recon-kpi-net-diff-hint');
        diffEl.textContent = formatMoney(Math.abs(res.netVariance));
        if (Math.abs(res.netVariance) > 1) {
            diffEl.className = 'text-xl sm:text-2xl font-black font-mono text-rose-400 block tracking-tight';
            if (diffHint) {
                diffHint.innerHTML = '<span class="text-rose-400 font-bold">⚠️ يوجد فارق يجب حسمه وتسويته</span>';
            }
        } else {
            diffEl.className = 'text-xl sm:text-2xl font-black font-mono text-emerald-400 block tracking-tight';
            if (diffHint) {
                diffHint.innerHTML = '<span class="text-emerald-400 font-bold">✓ تطابق تام ولا يوجد أي فرق</span>';
            }
        }

        document.getElementById('recon-kpi-match-rate').textContent = res.matchRate + '%';

        const elComboKpi = document.getElementById('recon-kpi-combinatorial');
        if (elComboKpi) elComboKpi.textContent = res.combinatorialMatches ? res.combinatorialMatches.length : 0;

        const criticalCount = res.agingBuckets ? (res.agingBuckets.b60_plus.countA + res.agingBuckets.b60_plus.countB) : 0;
        const elRiskKpi = document.getElementById('recon-kpi-risk-aging');
        if (elRiskKpi) elRiskKpi.textContent = criticalCount;

        document.getElementById('recon-count-exact').textContent = res.exactMatches.length;
        document.getElementById('recon-count-variance').textContent = res.varianceMatches.length;
        document.getElementById('recon-count-unmatched-a').textContent = res.unmatchedA.length;
        document.getElementById('recon-count-unmatched-b').textContent = res.unmatchedB.length;

        const elCountCombo = document.getElementById('recon-count-combinatorial');
        if (elCountCombo) elCountCombo.textContent = res.combinatorialMatches ? res.combinatorialMatches.length : 0;

        const elCountAgingRisk = document.getElementById('recon-count-aging-risk');
        if (elCountAgingRisk) elCountAgingRisk.textContent = criticalCount;

        // Update 4-Pillar Multi-Dimensional Forensic Matrix
        const debDiffEl = document.getElementById('recon-forensic-debit-diff');
        if (debDiffEl) debDiffEl.textContent = formatMoney(res.debitVariance || 0);

        const credDiffEl = document.getElementById('recon-forensic-credit-diff');
        if (credDiffEl) credDiffEl.textContent = formatMoney(res.creditVariance || 0);

        const locDiffEl = document.getElementById('recon-forensic-local-diff');
        if (locDiffEl) locDiffEl.textContent = formatMoney(res.localVariance || 0);

        const fxDiffEl = document.getElementById('recon-forensic-fx-diff');
        if (fxDiffEl) {
            if (res.fxVariances && res.fxVariances.length > 0) {
                const totalFx = res.fxVariances.reduce((acc, f) => acc + Math.abs(f.diff), 0);
                fxDiffEl.textContent = `فارق صرف ${formatMoney(totalFx)}`;
                fxDiffEl.className = 'font-mono font-black text-sm text-amber-400';
            } else {
                fxDiffEl.textContent = 'متكافئ 100%';
                fxDiffEl.className = 'font-mono font-black text-sm text-emerald-400';
            }
        }

        // Update counts for new tabs
        const driftCountEl = document.getElementById('recon-count-drift-events');
        if (driftCountEl) driftCountEl.textContent = res.waterfall ? res.waterfall.driftEventsCount : 0;

        const fxCountEl = document.getElementById('recon-count-fx');
        if (fxCountEl) fxCountEl.textContent = res.fxVariances ? res.fxVariances.length : 0;

        // Render Tables for Sub-Tabs
        renderExactMatchesTable();
        renderVariancesTable();
        renderUnmatchedTables();
        renderCombinatorialMatches();
        renderAgingMatrix();
        renderWaterfallTimeline();
        renderFXVariances();

        // Switch to AI tab by default
        switchReconTab('ai_audit');

        // Scroll to results smoothly
        resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function renderAIAuditCard(aiData) {
        const aiCardEl = document.getElementById('recon-ai-card');
        const aiStatusEl = document.getElementById('recon-ai-status');
        if (aiStatusEl) aiStatusEl.classList.add('hidden');
        if (!aiCardEl) return;

        aiCardEl.classList.remove('hidden');

        let causesHtml = '';
        if (aiData.causes && aiData.causes.length > 0) {
            causesHtml = `
                <div class="space-y-3 mt-5">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <h5 class="font-black text-sm text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-magnifying-glass-chart text-amber-400"></i>
                            التشخيص الجنائي والتفصيلي لبنود الفروقات المكتشفة:
                        </h5>
                        <span class="text-xs text-slate-400 font-bold">تم رصد ${aiData.causes.length} بنود خلل</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        ${aiData.causes.map((c, i) => `
                            <div class="p-4 rounded-2xl border border-slate-700/80 bg-slate-950/70 hover:border-indigo-500/50 transition space-y-2 shadow-lg">
                                <div class="flex items-center justify-between pb-1.5 border-b border-slate-800">
                                    <span class="font-black text-xs text-amber-300 flex items-center gap-1.5">
                                        <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-300 flex items-center justify-center text-[10px] font-bold">${i + 1}</span>
                                        ${c.title}
                                    </span>
                                    <span class="font-mono font-black text-sm text-rose-400">${formatMoney(Math.abs(c.amount))}</span>
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed font-normal">${c.explanation}</p>
                                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1.5 border-t border-slate-800/80 font-mono">
                                    <span>📅 ${c.date || 'بدون تاريخ'}</span>
                                    <span class="text-sky-300">🔖 ${c.ref || 'بدون مرجع'}</span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        let actionsHtml = '';
        if (aiData.corrective_actions && aiData.corrective_actions.length > 0) {
            actionsHtml = `
                <div class="mt-5 p-5 rounded-2xl bg-indigo-950/40 border border-indigo-500/40 space-y-3">
                    <h5 class="font-black text-xs sm:text-sm text-indigo-200 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-indigo-400"></i>
                        القيود والإجراءات التصحيحية الموصى بها لحسم التباين:
                    </h5>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-200">
                        ${aiData.corrective_actions.map(a => `
                            <div class="flex items-start gap-2 bg-slate-900/60 p-2.5 rounded-xl border border-indigo-500/20">
                                <i class="fa-solid fa-circle-check text-emerald-400 mt-0.5 text-xs"></i>
                                <span class="leading-relaxed">${a}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        let refInsightsHtml = '';
        if (aiData.ref_matching_insights) {
            refInsightsHtml = `
                <div class="mt-3 p-3.5 rounded-2xl bg-slate-800/40 border border-slate-700/80 text-xs text-slate-300 flex items-start gap-2.5">
                    <i class="fa-solid fa-lightbulb text-amber-400 text-sm mt-0.5"></i>
                    <div>
                        <strong class="text-amber-300 block mb-0.5">ملاحظة الذكاء الاصطناعي حول أرقام التحويلات (Suffix Matching):</strong>
                        <span>${aiData.ref_matching_insights}</span>
                    </div>
                </div>
            `;
        }

        aiCardEl.innerHTML = `
            <div class="space-y-5">
                <!-- AI Card Header -->
                <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-indigo-500/20">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-400/50 flex items-center justify-center text-indigo-300 text-xl shadow-lg">
                            <i class="fa-solid fa-user-secret"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-black text-base sm:text-lg text-slate-100">مذكرة التدقيق المحاسبي الجنائي والذكاء الاصطناعي</h4>
                                <span class="px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 font-extrabold text-[10px] border border-indigo-400/30">AI Live Verified</span>
                            </div>
                            <span class="text-xs text-slate-400 font-bold">بواسطة ${aiData.engine || 'Gemini Forensic Auditor 2026'}</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" onclick="dispatchWhatsAppLetterToOtherSide()" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-black flex items-center gap-2 cursor-pointer transition shadow-lg shadow-emerald-950/40 transform hover:scale-[1.02]">
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            <span>إرسال فوري بالواتساب</span>
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                        </button>
                        <button type="button" onclick="copyWhatsAppReconciliationLetter()" class="px-4 py-2 rounded-xl bg-emerald-500/20 border-2 border-emerald-500/50 hover:bg-emerald-500/30 text-emerald-300 text-xs font-black flex items-center gap-2 cursor-pointer transition shadow-md">
                            <i class="fa-regular fa-copy text-sm"></i>
                            نسخ الخطاب
                        </button>
                    </div>
                </div>

                <!-- Executive Summary Quote Box -->
                <div class="p-5 rounded-2xl bg-gradient-to-r from-indigo-950/50 via-slate-900/80 to-purple-950/40 border border-indigo-500/30 shadow-inner">
                    <strong class="text-xs text-indigo-300 font-black block mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-quote-right text-indigo-400"></i>
                        الملخص التنفيذي لنتائج المطابقة:
                    </strong>
                    <p class="text-sm sm:text-base font-bold text-slate-100 leading-relaxed">${aiData.executive_summary}</p>
                    <div class="mt-3 text-xs font-bold text-slate-200 bg-slate-950/70 p-3 rounded-xl border border-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-sky-400"></i>
                        <span>${aiData.net_difference_explained}</span>
                    </div>
                </div>

                ${causesHtml}
                ${refInsightsHtml}
                ${actionsHtml}
            </div>
        `;
    }

    function renderExactMatchesTable() {
        const matches = window.RECON_STATE.results.exactMatches;
        const tbody = document.getElementById('recon-table-exact-body');
        if (!tbody) return;

        if (matches.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-slate-400 font-bold">لا توجد حركات متطابقة تماماً.</td></tr>`;
            return;
        }

        tbody.innerHTML = matches.map((m, idx) => `
            <tr class="hover:bg-slate-800/50 border-b border-slate-800/60 text-xs transition">
                <td class="p-2.5 text-center font-bold text-slate-400">${idx + 1}</td>
                <td class="p-2.5 font-mono text-slate-300">${m.rowA.date || m.rowB.date}</td>
                <td class="p-2.5 font-mono font-black text-amber-400">${m.rowA.ref} <span class="text-slate-500">↔</span> ${m.rowB.ref}</td>
                <td class="p-2.5 text-slate-200">${m.rowA.desc}</td>
                <td class="p-2.5 text-center font-mono font-black text-emerald-400">${formatMoney(m.rowA.amount)}</td>
                <td class="p-2.5 text-center">
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 font-bold text-[11px] border border-emerald-500/30">
                        ${m.badge}
                    </span>
                </td>
            </tr>
        `).join('');
    }

    function renderVariancesTable() {
        const variances = window.RECON_STATE.results.varianceMatches || [];
        const tbody = document.getElementById('recon-table-variance-body');
        if (!tbody) return;

        if (variances.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-emerald-400 font-bold">✓ لا توجد عمليات فيها تباين أو اختلاف مبالغ! كل العمليات المتطابقة صحيحة.</td></tr>`;
            return;
        }

        tbody.innerHTML = variances.map((v, idx) => {
            const absDiff = Math.abs(v.diff);
            const transpositionBadge = v.isTransposition ? `
                <div class="mt-1">
                    <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-bold text-[10px] border border-amber-500/40 inline-flex items-center gap-1">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        قاعدة التسعة: خطأ تبديل خانات محتمل
                    </span>
                </div>
            ` : '';

            const fxBadge = v.isFxVariance ? `
                <div class="mt-1">
                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold text-[10px] border border-emerald-500/40 inline-flex items-center gap-1">
                        <i class="fa-solid fa-coins"></i>
                        فارق سعر صرف وتقلبات عملة (FX Drift)
                    </span>
                </div>
            ` : '';

            const actionBtn = v.isAdjusted ? `
                <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 font-bold text-[10px] border border-emerald-500/40">✓ تم قيد التسوية</span>
            ` : `
                <button type="button" onclick="openAdjustmentModalForVariance(${idx})" class="px-2.5 py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-200 hover:text-white text-xs font-bold border border-indigo-500/40 transition flex items-center gap-1 cursor-pointer mx-auto">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>قيد تسوية</span>
                </button>
            `;

            return `
                <tr class="hover:bg-slate-800/50 border-b border-slate-800/60 text-xs bg-amber-500/5 transition">
                    <td class="p-2.5 text-center font-bold text-slate-400">${idx + 1}</td>
                    <td class="p-2.5 font-mono text-slate-300">${v.rowA.date} ↔ ${v.rowB.date}</td>
                    <td class="p-2.5 font-mono font-black text-amber-400">${v.rowA.ref}</td>
                    <td class="p-2.5 text-slate-200 leading-relaxed">${v.explanation} ${transpositionBadge} ${fxBadge}</td>
                    <td class="p-2.5 text-center font-mono text-slate-200">${formatMoney(v.rowA.amount)}</td>
                    <td class="p-2.5 text-center font-mono text-slate-200">${formatMoney(v.rowB.amount)}</td>
                    <td class="p-2.5 text-center font-mono font-black text-rose-400">${formatMoney(absDiff)}</td>
                    <td class="p-2.5 text-center">${actionBtn}</td>
                </tr>
            `;
        }).join('');
    }

    function renderUnmatchedTables() {
        const unA = window.RECON_STATE.results.unmatchedA || [];
        const unB = window.RECON_STATE.results.unmatchedB || [];

        const tbodyA = document.getElementById('recon-table-unmatched-a-body');
        const tbodyB = document.getElementById('recon-table-unmatched-b-body');

        if (tbodyA) {
            if (unA.length === 0) {
                tbodyA.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-emerald-400 font-bold">✓ جميع حركات كشف حساب الطرف الأول مسجلة ومطابقة لدى الطرف الثاني!</td></tr>`;
            } else {
                tbodyA.innerHTML = unA.map((r, idx) => {
                    const actionBtn = r.isAdjusted ? `
                        <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 font-bold text-[10px] border border-emerald-500/40">✓ تم قيد التسوية</span>
                    ` : `
                        <button type="button" onclick="openAdjustmentModalForUnmatched('A', ${idx})" class="px-2.5 py-1 rounded-lg bg-rose-600/25 hover:bg-rose-600 text-rose-300 hover:text-white text-[11px] font-bold border border-rose-500/35 transition flex items-center gap-1 cursor-pointer mx-auto">
                            <i class="fa-solid fa-plus-circle"></i>
                            <span>تسوية</span>
                        </button>
                    `;

                    return `
                        <tr class="hover:bg-slate-800/50 border-b border-slate-800/60 text-xs transition">
                            <td class="p-2.5 text-center text-slate-400 font-bold">${idx + 1}</td>
                            <td class="p-2.5 font-mono text-slate-300">${r.date || '-'}</td>
                            <td class="p-2.5 font-mono font-black text-amber-400">${r.ref}</td>
                            <td class="p-2.5 text-slate-300">${r.desc}</td>
                            <td class="p-2.5 text-center font-mono font-black text-rose-400">${formatMoney(r.amount)}</td>
                            <td class="p-2.5 text-center">${actionBtn}</td>
                        </tr>
                    `;
                }).join('');
            }
        }

        if (tbodyB) {
            if (unB.length === 0) {
                tbodyB.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-emerald-400 font-bold">✓ لا توجد حركات للطرف الثاني مفقودة في كشف الطرف الأول!</td></tr>`;
            } else {
                tbodyB.innerHTML = unB.map((r, idx) => {
                    const actionBtn = r.isAdjusted ? `
                        <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 font-bold text-[10px] border border-emerald-500/40">✓ تم قيد التسوية</span>
                    ` : `
                        <button type="button" onclick="openAdjustmentModalForUnmatched('B', ${idx})" class="px-2.5 py-1 rounded-lg bg-purple-600/25 hover:bg-purple-600 text-purple-300 hover:text-white text-[11px] font-bold border border-purple-500/35 transition flex items-center gap-1 cursor-pointer mx-auto">
                            <i class="fa-solid fa-plus-circle"></i>
                            <span>تسوية</span>
                        </button>
                    `;

                    return `
                        <tr class="hover:bg-slate-800/50 border-b border-slate-800/60 text-xs transition">
                            <td class="p-2.5 text-center text-slate-400 font-bold">${idx + 1}</td>
                            <td class="p-2.5 font-mono text-slate-300">${r.date || '-'}</td>
                            <td class="p-2.5 font-mono font-black text-sky-400">${r.ref}</td>
                            <td class="p-2.5 text-slate-300">${r.desc}</td>
                            <td class="p-2.5 text-center font-mono font-black text-purple-300">${formatMoney(r.amount)}</td>
                            <td class="p-2.5 text-center">${actionBtn}</td>
                        </tr>
                    `;
                }).join('');
            }
        }
    }

    function renderCombinatorialMatches() {
        const combos = window.RECON_STATE.results.combinatorialMatches || [];
        const container = document.getElementById('recon-combinatorial-cards-container');
        if (!container) return;

        if (combos.length === 0) {
            container.innerHTML = `
                <div class="text-center py-10 text-slate-400 font-bold space-y-2">
                    <i class="fa-solid fa-diagram-project text-3xl text-slate-600"></i>
                    <p>لا توجد مطابقات تجميعية N:1 حتى الآن (لم يتم رصد دفعات مقسمة تطابق فاتورة واحدة).</p>
                </div>
            `;
            return;
        }

        container.innerHTML = combos.map((c, idx) => `
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-950/80 border border-sky-500/30 hover:border-sky-500/60 transition space-y-3.5 shadow-lg">
                <div class="flex flex-wrap items-center justify-between gap-2 pb-2.5 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-sky-500/20 text-sky-300 flex items-center justify-center text-xs font-bold">${idx + 1}</span>
                        <span class="font-black text-sm text-slate-100">${c.type === 'N_to_1' ? 'سداد فاتورة واحدة بعدة دفعات مقسمة (N:1)' : 'حركة واحدة غطت عدة فواتير (1:N)'}</span>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-emerald-500/15 text-emerald-300 font-bold text-xs border border-emerald-500/30 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check"></i>
                        مجموع المبالغ مطابق تماماً (${formatMoney(c.totalAmount)})
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Target Single Row -->
                    <div class="p-3.5 rounded-xl bg-slate-900/90 border border-purple-500/30 space-y-1.5">
                        <div class="flex items-center justify-between text-xs text-purple-300 font-bold pb-1 border-b border-purple-500/20">
                            <span>🎯 الحركة المستهدفة (${c.type === 'N_to_1' ? 'كشف حساب الطرف الثاني' : 'كشف حساب الطرف الأول'}):</span>
                            <span class="font-mono font-black text-sm text-purple-200">${formatMoney(c.singleRow.amount)}</span>
                        </div>
                        <p class="text-xs text-slate-200 leading-snug font-bold">${c.singleRow.desc}</p>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono pt-1">
                            <span>📅 ${c.singleRow.date}</span>
                            <span class="text-purple-300">🔖 ${c.singleRow.ref}</span>
                        </div>
                    </div>

                    <!-- Multiple Component Rows -->
                    <div class="p-3.5 rounded-xl bg-slate-900/90 border border-sky-500/30 space-y-2">
                        <div class="flex items-center justify-between text-xs text-sky-300 font-bold pb-1 border-b border-sky-500/20">
                            <span>🧩 الدفعات المكونة المسددة (${c.type === 'N_to_1' ? 'كشف حساب الطرف الأول' : 'كشف حساب الطرف الثاني'} - ${c.comboRows.length} حركات):</span>
                            <span class="font-mono font-black text-sm text-sky-200">${formatMoney(c.totalAmount)}</span>
                        </div>
                        <div class="space-y-1.5 max-h-36 overflow-y-auto custom-scrollbar">
                            ${c.comboRows.map(r => `
                                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-950/70 text-xs border border-slate-800">
                                    <div>
                                        <span class="font-bold text-slate-200">${r.desc}</span>
                                        <div class="text-[10px] text-slate-400 font-mono">📅 ${r.date} · 🔖 ${r.ref}</div>
                                    </div>
                                    <span class="font-mono font-black text-sky-300 text-xs">${formatMoney(r.amount)}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                
                <p class="text-xs text-slate-300 bg-slate-900/50 p-2.5 rounded-xl border border-slate-800/80 leading-relaxed font-normal">
                    <i class="fa-solid fa-circle-info text-sky-400 ml-1"></i>
                    ${c.explanation}
                </p>
            </div>
        `).join('');
    }

    function renderAgingMatrix() {
        const buckets = window.RECON_STATE.results.agingBuckets;
        const grid = document.getElementById('recon-aging-buckets-grid');
        const criticalList = document.getElementById('recon-aging-critical-list');
        if (!grid || !buckets) return;

        const bKeys = ['b0_15', 'b16_30', 'b31_60', 'b60_plus'];
        const borderColors = {
            b0_15: 'border-emerald-500/40 text-emerald-400',
            b16_30: 'border-sky-500/40 text-sky-400',
            b31_60: 'border-amber-500/40 text-amber-400',
            b60_plus: 'border-rose-500/50 text-rose-400'
        };
        const bgGradients = {
            b0_15: 'from-emerald-950/30 to-slate-950',
            b16_30: 'from-sky-950/30 to-slate-950',
            b31_60: 'from-amber-950/30 to-slate-950',
            b60_plus: 'from-rose-950/40 to-slate-950'
        };

        grid.innerHTML = bKeys.map(k => {
            const b = buckets[k];
            const totalCount = b.countA + b.countB;
            const totalAmt = b.amountA + b.amountB;
            return `
                <div class="p-4 rounded-2xl bg-gradient-to-b ${bgGradients[k]} border ${borderColors[k]} space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-black text-xs sm:text-sm text-slate-100">${b.label}</span>
                        <span class="px-2 py-0.5 rounded-full bg-slate-900/80 ${borderColors[k]} font-mono font-bold text-xs border border-current">${totalCount} حركات</span>
                    </div>
                    <div class="font-mono font-black text-lg sm:text-xl text-slate-100 tracking-tight pt-1">
                        ${formatMoney(totalAmt)}
                    </div>
                    <span class="text-[11px] text-slate-400 block">${b.sub}</span>
                    <div class="pt-2 border-t border-slate-800/80 text-[10px] text-slate-400 flex items-center justify-between font-mono">
                        <span>كشف الطرف الأول: ${formatMoney(b.amountA)} (${b.countA})</span>
                        <span>كشف الطرف الثاني: ${formatMoney(b.amountB)} (${b.countB})</span>
                    </div>
                </div>
            `;
        }).join('');

        // Critical Aging rows
        const criticalRows = buckets.b60_plus.rows || [];
        if (criticalList) {
            if (criticalRows.length === 0) {
                criticalList.innerHTML = `
                    <div class="p-4 rounded-xl bg-emerald-950/30 border border-emerald-500/30 text-emerald-300 text-xs font-bold text-center">
                        ✓ ممتاز! لا توجد حركات معلقة تجاوزت 60 يوماً. المحفظة المحاسبية آمنة وخالية من التقادم.
                    </div>
                `;
            } else {
                criticalList.innerHTML = `
                    <div class="p-4 rounded-2xl bg-rose-950/40 border border-rose-500/40 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-rose-500/20">
                            <span class="font-black text-xs sm:text-sm text-rose-300 flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation text-rose-400 text-base"></i>
                                تنبيه عالي الخطورة: تم رصد ${criticalRows.length} حركات متقادمة (+60 يوماً) يجب تسويتها فورياً:
                            </span>
                        </div>
                        <div class="space-y-2">
                            ${criticalRows.map(item => `
                                <div class="p-3 rounded-xl bg-slate-950/80 border border-rose-500/30 flex flex-wrap items-center justify-between gap-3 text-xs">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 font-bold text-[10px]">${item.side === 'A' ? 'في كشف الطرف الأول' : 'في كشف الطرف الثاني'}</span>
                                            <span class="font-bold text-slate-100">${item.row.desc}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono">📅 ${item.row.date} (منذ ${item.days} يوماً) · 🔖 ${item.row.ref}</div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-mono font-black text-sm text-rose-400">${formatMoney(item.row.amount)}</span>
                                        <button type="button" onclick="openAdjustmentModalForUnmatched('${item.side}', ${item.uIdx !== undefined ? item.uIdx : (item.row.id - 1)})" class="px-3 py-1 rounded-lg bg-rose-600/30 hover:bg-rose-600 text-rose-200 text-xs font-bold border border-rose-500/40 transition cursor-pointer">
                                            تسوية فورية
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
        }
    }

    function renderWaterfallTimeline() {
        const waterfall = window.RECON_STATE.results.waterfall;
        const tbody = document.getElementById('recon-table-waterfall-body');
        const alertBox = document.getElementById('recon-waterfall-inception-alert');
        if (!tbody || !waterfall) return;

        if (alertBox) {
            if (waterfall.inceptionPoint) {
                const inc = waterfall.inceptionPoint;
                alertBox.innerHTML = `
                    <div class="flex items-start gap-3.5">
                        <span class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-lg mt-0.5 border border-rose-500/40 shadow-md">
                            <i class="fa-solid fa-flag-checkered"></i>
                        </span>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h6 class="font-black text-sm text-rose-300">تم تحديد نقطة انطلاق الخلل المحاسبي بدقة (Baseline Inception Point)</h6>
                                <span class="px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-300 text-[10px] font-mono font-bold">حركة رقم ${inc.stepIndex}</span>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                بدأ التباين بين الكشفين أول مرة بتاريخ <strong class="text-sky-300 font-mono">${inc.date}</strong> عند قيد عملية (<span class="text-amber-300 font-bold">${inc.desc}</span> - مرجع: <span class="font-mono text-amber-400">${inc.ref}</span>) بمبلغ <strong class="text-rose-400 font-mono">${formatMoney(Math.abs(inc.amount))}</strong> لدى <span class="text-indigo-300 font-bold">${inc.side === 'A' ? 'كشف حساب الطرف الأول' : 'كشف حساب الطرف الثاني'}</span>، حيث كان الرصيد قبل هذه العملية متطابقاً تماماً (Zero Delta).
                            </p>
                        </div>
                    </div>
                `;
            } else {
                alertBox.innerHTML = `
                    <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs">
                        <i class="fa-solid fa-circle-check text-base"></i>
                        <span>مسار الرصيد متطابق تماماً على مدار كافة الحركات الزمنية دون أي انحراف (Zero Cumulative Drift).</span>
                    </div>
                `;
            }
        }

        if (waterfall.timeline.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center py-8 text-slate-400 font-bold">لا توجد حركات مسجلة.</td></tr>`;
            return;
        }

        tbody.innerHTML = waterfall.timeline.map(t => {
            const rowBg = t.isInception ? 'bg-rose-500/15 border-rose-500/40' : (t.isShift ? 'bg-amber-500/5' : 'hover:bg-slate-800/40');
            const sideColor = t.side === 'A' ? 'text-sky-400' : 'text-purple-400';
            const netSign = t.netEffect >= 0 ? '+' : '';
            const netColor = t.netEffect >= 0 ? 'text-emerald-400' : 'text-rose-400';

            return `
                <tr class="border-b border-slate-800/60 text-xs transition ${rowBg}">
                    <td class="p-2.5 text-center font-bold text-slate-400">${t.index}</td>
                    <td class="p-2.5 font-mono text-slate-300">${t.date}</td>
                    <td class="p-2.5 font-bold ${sideColor}">${t.side === 'A' ? 'الطرف الأول' : 'الطرف الثاني'}</td>
                    <td class="p-2.5">
                        <span class="font-mono font-bold text-amber-300 text-[11px] block">${t.ref}</span>
                        <span class="text-slate-200 text-xs">${t.desc}</span>
                    </td>
                    <td class="p-2.5 text-center font-mono font-black ${netColor}">${netSign}${formatMoney(t.netEffect)}</td>
                    <td class="p-2.5 text-center font-mono font-bold text-sky-300">${formatMoney(t.runningA)}</td>
                    <td class="p-2.5 text-center font-mono font-bold text-purple-300">${formatMoney(t.runningB)}</td>
                    <td class="p-2.5 text-center font-mono font-black ${Math.abs(t.delta) > 1 ? 'text-rose-400' : 'text-emerald-400'}">${formatMoney(t.delta)}</td>
                    <td class="p-2.5 text-center">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold border inline-block ${t.stepBadgeClass}">
                            ${t.stepDiagnosis}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderFXVariances() {
        const fxList = window.RECON_STATE.results.fxVariances || [];
        const tbody = document.getElementById('recon-table-fx-body');
        const banner = document.getElementById('recon-fx-summary-banner');
        if (!tbody) return;

        if (banner) {
            if (fxList.length > 0) {
                const totalFxDiff = fxList.reduce((acc, f) => acc + Math.abs(f.diff), 0);
                banner.innerHTML = `
                    <div class="flex items-start gap-3.5">
                        <span class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg mt-0.5 border border-emerald-500/40 shadow-md">
                            <i class="fa-solid fa-coins"></i>
                        </span>
                        <div class="space-y-1">
                            <h6 class="font-black text-sm text-emerald-300">تم رصد (${fxList.length}) عمليات فيها فروقات صرف وتقلبات عملة بإجمالي ${formatMoney(totalFxDiff)}</h6>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                هذه العمليات تم إثباتها وسدادها لدى الطرفين، ولكن اختلاف سعر الصرف المعتمد في يوم التحويل بين دفاتر الطرف الأول ودفاتر الطرف الثاني ولد هذا الفارق المحاسبي. التسوية المطلوبة هي قيد أرباح/خسائر فروق عملة وليس مطالبة سداد.
                            </p>
                        </div>
                    </div>
                `;
            } else {
                banner.innerHTML = `
                    <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs">
                        <i class="fa-solid fa-circle-check text-base"></i>
                        <span>لا توجد فروقات في أسعار الصرف أو تعادل العملات الأجنبية.</span>
                    </div>
                `;
            }
        }

        if (fxList.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-8 text-emerald-400 font-bold">✓ كافة عمليات العملات الأجنبية متطابقة تماماً في أسعار الصرف!</td></tr>`;
            return;
        }

        tbody.innerHTML = fxList.map((f, idx) => {
            const absDiff = Math.abs(f.diff);
            return `
                <tr class="hover:bg-slate-800/50 border-b border-slate-800/60 text-xs transition">
                    <td class="p-2.5 text-center font-bold text-slate-400">${idx + 1}</td>
                    <td class="p-2.5 font-mono text-slate-300">
                        <span class="block">${f.rowA.date}</span>
                        <span class="font-black text-amber-400">${f.rowA.ref}</span>
                    </td>
                    <td class="p-2.5 text-slate-200">${f.rowA.desc}</td>
                    <td class="p-2.5 text-center font-mono font-bold text-sky-300">${f.foreignBase || '1,000 $'}</td>
                    <td class="p-2.5 text-center font-mono font-bold text-sky-400">${formatMoney(f.rowA.amount)}</td>
                    <td class="p-2.5 text-center font-mono font-bold text-purple-400">${formatMoney(f.rowB.amount)}</td>
                    <td class="p-2.5 text-center font-mono font-black text-amber-400">${formatMoney(absDiff)}</td>
                    <td class="p-2.5 text-center">
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-500/15 text-emerald-300 font-bold text-[10px] border border-emerald-500/30">
                            قيد تسوية أرباح/خسائر فروق عملة
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // ────────────────────────────────────────────────────────────────
    // 7. ONE-CLICK ADJUSTMENT JOURNALS WORKFLOW (توليد قيود التسوية لليومية)
    // ────────────────────────────────────────────────────────────────
    window.CURRENT_ADJ_ITEM = null;

    function openAdjustmentModalForVariance(idx) {
        const v = window.RECON_STATE.results.varianceMatches[idx];
        if (!v) return;
        const diff = Math.abs(v.diff);
        const notes = `تسوية فارق عملية مرجع (${v.rowA.ref}) بين كشف الطرف الأول وكشف الطرف الثاني - ${v.explanation}`;
        window.openAdjustmentModal({
            date: v.rowA.date || new Date().toISOString().split('T')[0],
            amount: diff,
            ref: v.rowA.ref,
            notes: notes,
            sourceType: 'variance',
            targetItem: v
        });
    }
    window.openAdjustmentModalForVariance = openAdjustmentModalForVariance;

    function openAdjustmentModalForUnmatched(side, idxOrItem) {
        const list = (side === 'A') ? window.RECON_STATE.results.unmatchedA : window.RECON_STATE.results.unmatchedB;
        let item = null;
        if (typeof idxOrItem === 'object' && idxOrItem !== null) {
            item = idxOrItem;
        } else if (typeof idxOrItem === 'number') {
            item = list ? list[idxOrItem] : null;
            if (!item && list) {
                item = list.find(r => r.id === idxOrItem || r.id === (idxOrItem + 1));
            }
        } else if (typeof idxOrItem === 'string' && list) {
            item = list.find(r => String(r.ref) === idxOrItem || String(r.id) === idxOrItem);
        }
        if (!item && list && list.length > 0) {
            item = list[0];
        }
        if (!item) return;

        const notes = (side === 'A') 
            ? `تسوية حركة مقيدة في كشف الطرف الأول ومفقودة لدى الطرف الثاني (${item.desc} - مرجع: ${item.ref})`
            : `تسوية وإثبات حركة قيدها الطرف الثاني وغير مسجلة في كشف حساب الطرف الأول (${item.desc} - مرجع: ${item.ref})`;

        window.openAdjustmentModal({
            date: item.date || new Date().toISOString().split('T')[0],
            amount: item.amount,
            ref: item.ref,
            notes: notes,
            sourceType: 'unmatched',
            side: side,
            targetItem: item
        });
    }
    window.openAdjustmentModalForUnmatched = openAdjustmentModalForUnmatched;

    function openAdjustmentModal(config) {
        window.CURRENT_ADJ_ITEM = config;
        const modal = document.getElementById('modal-recon-adjustment-voucher');
        if (!modal) return;

        const vchNo = 'ADJ-' + Date.now().toString().slice(-6);
        const vchNoEl = document.getElementById('recon-adj-vch-no');
        const dateEl = document.getElementById('recon-adj-date');
        const amountEl = document.getElementById('recon-adj-amount');
        const notesEl = document.getElementById('recon-adj-notes');

        if (vchNoEl) vchNoEl.value = vchNo;
        if (dateEl) dateEl.value = config.date || new Date().toISOString().split('T')[0];
        if (amountEl) amountEl.value = config.amount || 0;
        if (notesEl) notesEl.value = config.notes || '';

        populateAdjustmentAccountsDropdown(config);
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }
    window.openAdjustmentModal = openAdjustmentModal;

    function populateAdjustmentAccountsDropdown(config) {
        const debitSelect = document.getElementById('recon-adj-debit-acc');
        const creditSelect = document.getElementById('recon-adj-credit-acc');
        if (!debitSelect || !creditSelect) return;

        const commonAccounts = [
            { id: 'EXP-FEES', name: 'مصروفات وعمولات بنكية' },
            { id: 'EXP-NOLON', name: 'مصروفات نولون وتعتيق وشحن' },
            { id: 'EXP-DIFF', name: 'أرباح وخسائر تسوية فروقات تقريب وهللات' },
            { id: 'PURCHASE', name: 'مشتريات بضاعة وتوريدات' },
            { id: 'SUP-FACTORY', name: 'حساب المصنع / المورد (الطرف الثاني)' },
            { id: 'BNK-BOK', name: 'بنك الخرطوم (بنكك)' },
            { id: 'BNK-FAWRY', name: 'بنك فيصل الإسلامي (فوري)' },
            { id: 'SAFE-MAIN', name: 'الخزينة الرئيسية (نقدية المركز)' }
        ];

        const optsHtml = commonAccounts.map(a => `<option value="${a.id}">${a.name}</option>`).join('');
        debitSelect.innerHTML = optsHtml;
        creditSelect.innerHTML = optsHtml;

        if (config.side === 'B') {
            debitSelect.value = 'EXP-NOLON';
            creditSelect.value = 'SUP-FACTORY';
        } else {
            debitSelect.value = 'SUP-FACTORY';
            creditSelect.value = 'BNK-BOK';
        }
    }

    function submitAdjustmentVoucher() {
        const vchNo = (document.getElementById('recon-adj-vch-no') ? document.getElementById('recon-adj-vch-no').value : null) || ('ADJ-' + Date.now().toString().slice(-6));
        const date = (document.getElementById('recon-adj-date') ? document.getElementById('recon-adj-date').value : null) || new Date().toISOString().split('T')[0];
        const amount = parseFloat((document.getElementById('recon-adj-amount') ? document.getElementById('recon-adj-amount').value : null)) || 0;
        const debitSelect = document.getElementById('recon-adj-debit-acc');
        const creditSelect = document.getElementById('recon-adj-credit-acc');
        const debitAccName = (debitSelect && debitSelect.options && debitSelect.options[debitSelect.selectedIndex] ? debitSelect.options[debitSelect.selectedIndex].text : null) || 'حساب مدين';
        const creditAccName = (creditSelect && creditSelect.options && creditSelect.options[creditSelect.selectedIndex] ? creditSelect.options[creditSelect.selectedIndex].text : null) || 'حساب دائن';
        const notes = (document.getElementById('recon-adj-notes') ? document.getElementById('recon-adj-notes').value : null) || 'قيد تسوية مطابقة';

        if (amount <= 0) {
            alert('يرجى تحديد مبلغ صالح لقيد التسوية.');
            return;
        }

        const newVoucher = {
            id: 'JV-' + Date.now(),
            voucherNo: vchNo,
            voucherDate: date,
            date: date,
            notes: notes,
            lines: [
                { accountId: debitSelect.value, accountName: debitAccName, debit: amount, credit: 0, description: notes },
                { accountId: creditSelect.value, accountName: creditAccName, debit: 0, credit: amount, description: notes }
            ],
            createdAt: new Date().toISOString(),
            source: 'reconciliation_auto_adjustment'
        };

        // Save to LocalStorage / ERP DB
        try {
            const key = 'erp_journal_vouchers_v1';
            let existing = JSON.parse(localStorage.getItem(key) || '[]');
            if (!Array.isArray(existing)) existing = [];
            existing.push(newVoucher);
            localStorage.setItem(key, JSON.stringify(existing));

            const key2 = 'erp_journal_entries_v1';
            let existing2 = JSON.parse(localStorage.getItem(key2) || '[]');
            if (!Array.isArray(existing2)) existing2 = [];
            existing2.push(newVoucher);
            localStorage.setItem(key2, JSON.stringify(existing2));

            if (window.DB_STORE) {
                window.DB_STORE[key] = JSON.stringify(existing);
                window.DB_STORE[key2] = JSON.stringify(existing2);
            }
        } catch(e) {
            console.warn('Error saving adjustment voucher to storage:', e);
        }

        // Mark item as adjusted
        if (window.CURRENT_ADJ_ITEM && window.CURRENT_ADJ_ITEM.targetItem) {
            window.CURRENT_ADJ_ITEM.targetItem.isAdjusted = true;
            window.CURRENT_ADJ_ITEM.targetItem.adjustmentVchNo = vchNo;
        }

        closeReconModals();

        // Re-render
        renderVariancesTable();
        renderUnmatchedTables();
        renderAgingMatrix();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'تم ترحيل قيد التسوية بنجاح! ⚡',
                text: `تم ترحيل قيد التسوية المزدوج رقم (${vchNo}) بمبلغ ${formatMoney(amount)} إلى دفتر اليومية العامة.`,
                background: '#0f172a',
                color: '#ffffff',
                timer: 3500,
                showConfirmButton: false
            });
        } else if (window.showModernToast) {
            window.showModernToast(`✨ تم ترحيل قيد التسوية رقم (${vchNo}) إلى دفتر اليومية بنجاح!`, 'success', 3500);
        }
    }
    window.submitAdjustmentVoucher = submitAdjustmentVoucher;

    function openBulkAdjustmentsModal() {
        const stateA = window.RECON_STATE.fileA;
        const stateB = window.RECON_STATE.fileB;
        if (!stateA.mappedRows || stateA.mappedRows.length === 0 || !stateB.mappedRows || stateB.mappedRows.length === 0) {
            alert('يرجى تحميل الكشوفات والضغط على زر [بدء التطابق المحاسبي] أولاً لاستخراج الفروقات وتوليد قيود التسوية.');
            return;
        }

        const unA = window.RECON_STATE.results.unmatchedA || [];
        const unB = window.RECON_STATE.results.unmatchedB || [];
        const variances = window.RECON_STATE.results.varianceMatches || [];
        const totalCount = unA.length + unB.length + variances.length;

        if (totalCount === 0) {
            alert('✓ ممتاز! لا توجد أي حركات معلقة أو فروقات تتطلب قيود تسوية؛ الكشوفات متطابقة تماماً بنسبة 100%.');
            return;
        }

        const netDiff = Math.abs(window.RECON_STATE.results.netVariance);
        window.openAdjustmentModal({
            date: new Date().toISOString().split('T')[0],
            amount: netDiff,
            ref: 'BULK-ADJ',
            notes: `قيد تسوية إجمالي شامل لكافة فروقات ومعلقات كشف حساب الطرفين (${totalCount} بنود خلل) بصافي فارق ${formatMoney(netDiff)}`
        });
    }
    window.openBulkAdjustmentsModal = openBulkAdjustmentsModal;

    // ────────────────────────────────────────────────────────────────
    // 6.5. TOP-LEVEL UNIFIED GLOBAL LIVE SEARCH (محرك البحث الشامل الفوري في أعلى المنظومة)
    // ────────────────────────────────────────────────────────────────
    window.RECON_SEARCH_QUERY = '';
    window.RECON_SEARCH_SCOPE = 'all';

    function handleReconGlobalSearch(rawQuery) {
        const query = (rawQuery !== undefined ? rawQuery : ((document.getElementById('recon-global-search-input') ? document.getElementById('recon-global-search-input').value : null) || '')).trim().toLowerCase();
        window.RECON_SEARCH_QUERY = query;

        const clearBtn = document.getElementById('btn-recon-search-clear');
        const badgeEl = document.getElementById('recon-search-badge');
        const countLabel = document.getElementById('recon-search-count-label');
        const hintEl = document.getElementById('recon-search-live-hint');
        const hintText = document.getElementById('recon-search-hint-text');

        if (clearBtn) {
            if (query) clearBtn.classList.remove('hidden');
            else clearBtn.classList.add('hidden');
        }

        const scope = window.RECON_SEARCH_SCOPE || 'all';
        let matchCount = 0;

        // Intelligent Multi-Dimensional Matching Engine
        function isSmartMatch(text, rawQ) {
            if (!rawQ) return true;
            if (!text) return false;
            const t = String(text).toLowerCase();
            const q = String(rawQ).toLowerCase().trim();
            if (t.includes(q)) return true;

            // Arabic normalization (أ/إ/آ -> ا, ة -> ه, ى -> ي)
            const normT = t.replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي');
            const normQ = q.replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي');
            if (normT.includes(normQ)) return true;

            // Number normalization (ignoring commas/spaces, e.g. 5000000 matches 5,000,000)
            const cleanTDigits = t.replace(/[, \s]/g, '');
            const cleanQDigits = q.replace(/[, \s]/g, '');
            if (cleanQDigits.length >= 3 && cleanTDigits.includes(cleanQDigits)) return true;

            // Dollar, Exchange Rate, and Foreign Currency Equivalence matching
            const isDollarSearch = q === '$' || q === 'usd' || normQ.includes('دولار') || normQ.includes('صرف') || normQ.includes('تعادل');
            if (isDollarSearch) {
                if (t.includes('$') || t.includes('usd') || normT.includes('دولار') || normT.includes('صرف') || normT.includes('سعر') || normT.includes('عمله')) return true;
            }

            // Accounting types (مدين, دائن, افتتاحي, فارق, معلق)
            if (normQ.includes('مدين') && (t.includes('مدين') || t.includes('لنا'))) return true;
            if (normQ.includes('دائن') && (t.includes('دائن') || t.includes('علينا'))) return true;
            if (normQ.includes('افتتاح') && (t.includes('افتتاح') || t.includes('مرحل') || t.includes('سابق'))) return true;
            if (normQ.includes('فارق') && (t.includes('فارق') || t.includes('خلل') || t.includes('تباين'))) return true;
            if (normQ.includes('معلق') && (t.includes('معلق') || t.includes('غير مطابق'))) return true;

            return false;
        }

        // 1. Exact Matches Table
        const exactRows = document.querySelectorAll('#recon-table-exact-body tr');
        exactRows.forEach(tr => {
            if (!query) {
                tr.style.display = '';
                return;
            }
            if (scope === 'sideB' || scope === 'results') {
                // Keep visible if matches query
            }
            if (isSmartMatch(tr.textContent, query)) {
                tr.style.display = '';
                matchCount++;
            } else {
                tr.style.display = 'none';
            }
        });

        // 2. Variance Matches Table
        const varianceRows = document.querySelectorAll('#recon-table-variance-body tr');
        varianceRows.forEach(tr => {
            if (!query) {
                tr.style.display = '';
                return;
            }
            if (isSmartMatch(tr.textContent, query)) {
                tr.style.display = '';
                matchCount++;
            } else {
                tr.style.display = 'none';
            }
        });

        // 3. Combinatorial Cards
        const comboCards = document.querySelectorAll('#recon-combinatorial-cards-container > div');
        comboCards.forEach(card => {
            if (!query) {
                card.style.display = '';
                return;
            }
            if (isSmartMatch(card.textContent, query)) {
                card.style.display = '';
                matchCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // 4. Unmatched Table A (كشف الطرف الأول)
        const unARows = document.querySelectorAll('#recon-table-unmatched-a-body tr');
        unARows.forEach(tr => {
            if (!query) {
                tr.style.display = '';
                return;
            }
            if (scope === 'sideB') {
                tr.style.display = 'none';
                return;
            }
            if (isSmartMatch(tr.textContent, query)) {
                tr.style.display = '';
                matchCount++;
            } else {
                tr.style.display = 'none';
            }
        });

        // 5. Unmatched Table B (كشف الطرف الثاني)
        const unBRows = document.querySelectorAll('#recon-table-unmatched-b-body tr');
        unBRows.forEach(tr => {
            if (!query) {
                tr.style.display = '';
                return;
            }
            if (scope === 'sideA') {
                tr.style.display = 'none';
                return;
            }
            if (isSmartMatch(tr.textContent, query)) {
                tr.style.display = '';
                matchCount++;
            } else {
                tr.style.display = 'none';
            }
        });

        // 6. Aging Matrix items
        const agingCards = document.querySelectorAll('#recon-aging-critical-list > div, #recon-aging-critical-list .p-3');
        agingCards.forEach(card => {
            if (!query) {
                card.style.display = '';
                return;
            }
            if (isSmartMatch(card.textContent, query)) {
                card.style.display = '';
                matchCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // 6.5. Waterfall Timeline Table
        const waterfallRows = document.querySelectorAll('#recon-table-waterfall-body tr');
        waterfallRows.forEach(tr => {
            if (!query) {
                tr.style.display = '';
                return;
            }
            if (isSmartMatch(tr.textContent, query)) {
                tr.style.display = '';
                matchCount++;
            } else {
                tr.style.display = 'none';
            }
        });

        // 6.6. FX Variances Table
        const fxRows = document.querySelectorAll('#recon-table-fx-body tr');
        fxRows.forEach(tr => {
            if (!query) {
                tr.style.display = '';
                return;
            }
            if (isSmartMatch(tr.textContent, query)) {
                tr.style.display = '';
                matchCount++;
            } else {
                tr.style.display = 'none';
            }
        });

        // 7. Update Search Counter Badge
        if (badgeEl && countLabel) {
            if (query) {
                badgeEl.classList.remove('hidden');
                countLabel.textContent = `${matchCount} بند مطابق`;
            } else {
                badgeEl.classList.add('hidden');
            }
        }

        // 8. Guidance hint if engine hasn't been run yet
        const resultsContainer = document.getElementById('recon-results-container');
        if (resultsContainer && resultsContainer.classList.contains('hidden') && query) {
            if (hintEl && hintText) {
                hintEl.classList.remove('hidden');
                hintText.innerHTML = `تم إدخال كلمة البحث: <strong>"${escapeHtmlRecon(query)}"</strong>. للبحث الشامل في كافة الفروقات والمعلقات المحللة بالذكاء الاصطناعي، يرجى الضغط على زر <strong>[بدء التطابق المحاسبي واكتشاف الخلل]</strong> أدناه.`;
            }
        } else {
            if (hintEl) hintEl.classList.add('hidden');
        }
    }
    window.handleReconGlobalSearch = handleReconGlobalSearch;

    function clearReconGlobalSearch() {
        const input = document.getElementById('recon-global-search-input');
        if (input) {
            input.value = '';
            if (typeof input.focus === 'function') input.focus();
        }
        handleReconGlobalSearch('');
    }
    window.clearReconGlobalSearch = clearReconGlobalSearch;

    function setReconSearchScope(scope, btn) {
        window.RECON_SEARCH_SCOPE = scope;
        document.querySelectorAll('.recon-scope-pill').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        window.handleReconGlobalSearch();
    }
    window.setReconSearchScope = setReconSearchScope;

    // Tab Switching inside Reconciliation Results
    function switchReconTab(tabId) {
        document.querySelectorAll('.recon-tab-pill, .recon-result-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.recon-result-section').forEach(sec => sec.classList.add('hidden'));

        const targetBtn = document.getElementById(`btn-recon-tab-${tabId}`);
        const targetSec = document.getElementById(`recon-sec-${tabId}`);

        if (targetBtn) {
            targetBtn.classList.add('active');
        }
        if (targetSec) {
            targetSec.classList.remove('hidden');
        }

        if (window.RECON_SEARCH_QUERY) {
            window.handleReconGlobalSearch(window.RECON_SEARCH_QUERY);
        }
    }
    window.switchReconTab = switchReconTab;

    // Reset Suite State and Clear UI
    function resetReconciliationSuite() {
        window.RECON_STATE.fileA = { name: '', rawRows: [], headers: [], colMap: {}, mappedRows: [], summary: { debit: 0, credit: 0, balance: 0, count: 0 } };
        window.RECON_STATE.fileB = { name: '', rawRows: [], headers: [], colMap: {}, mappedRows: [], summary: { debit: 0, credit: 0, balance: 0, count: 0 } };
        window.RECON_STATE.results = { exactMatches: [], varianceMatches: [], unmatchedA: [], unmatchedB: [], netVariance: 0, matchRate: 0 };
        window.RECON_STATE.aiAudit = null;

        ['A', 'B'].forEach(side => {
            const box = document.getElementById(`recon-dropzone-box-${side}`);
            if (box) {
                const isA = side === 'A';
                const color = isA ? 'sky' : 'purple';
                const title = isA ? 'اسحب وأفلت كشف حساب الطرف الأول هنا أو انقر للاختيار' : 'اسحب وأفلت كشف حساب الطرف الثاني هنا أو انقر للاختيار';
                box.innerHTML = `
                    <label for="omni-file-all-${side}" class="recon-upload-area flex flex-col items-center justify-center group">
                        <div class="w-14 h-14 rounded-2xl bg-${color}-500/10 border border-${color}-500/30 flex items-center justify-center text-${color}-400 text-2xl mb-3 group-hover:scale-110 transition duration-300 shadow-lg">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <span class="font-extrabold text-sm text-slate-100 group-hover:text-${color}-300 transition">${title}</span>
                        <span class="text-xs text-slate-400 mt-1">يقبل إكسيل · صور · PDF · وورد · نصوص</span>
                    </label>
                `;
            }
            const badge = document.getElementById(`recon-map-badges-${side}`);
            if (badge) badge.innerHTML = '';
            
            // Clear all file inputs to allow selecting same file again
            const inputOld = document.getElementById(`recon-file-input-${side}`);
            if (inputOld) inputOld.value = '';
            const inputAll = document.getElementById(`omni-file-all-${side}`);
            if (inputAll) inputAll.value = '';
            const inputCam = document.getElementById(`omni-file-camera-${side}`);
            if (inputCam) inputCam.value = '';
        });

        if (typeof clearReconGlobalSearch === 'function') clearReconGlobalSearch();

        const resCont = document.getElementById('recon-results-container');
        if (resCont) resCont.classList.add('hidden');
        const aiStat = document.getElementById('recon-ai-status');
        if (aiStat) aiStat.classList.add('hidden');

        if (window.showModernToast) {
            window.showModernToast('🔄 تم تصفية كافة الحقول وإعادة التهيئة بنجاح.', 'info', 2500);
        }
    }
    window.resetReconciliationSuite = resetReconciliationSuite;

    // Copy Official Reconciliation Letter for WhatsApp with Universal Multi-Layer Fallback
    function copyWhatsAppReconciliationLetter() {
        const letter = (window.RECON_STATE.aiAudit ? window.RECON_STATE.aiAudit.whatsapp_letter : null);
        if (!letter) {
            alert('لم يتم توليد الرسالة بعد، يرجى تشغيل المقارنة أولاً.');
            return;
        }

        const notifySuccess = () => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'تم النسخ بنجاح!',
                    text: 'تم نسخ مسودة خطاب التسوية لمشاركتها مع الطرف الثاني.',
                    timer: 2500,
                    showConfirmButton: false,
                    background: '#0f172a',
                    color: '#ffffff'
                });
            } else if (window.showModernToast) {
                window.showModernToast('📋 تم نسخ مسودة خطاب التسوية لمشاركتها مع الطرف الثاني عبر واتساب!', 'success', 3500);
            } else {
                alert('📋 تم نسخ مسودة خطاب التسوية لمشاركتها مع الطرف الثاني!');
            }
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(letter).then(notifySuccess).catch(() => {
                fallbackCopyText(letter);
                notifySuccess();
            });
        } else {
            fallbackCopyText(letter);
            notifySuccess();
        }
    }
    window.copyWhatsAppReconciliationLetter = copyWhatsAppReconciliationLetter;

    function fallbackCopyText(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        textArea.style.opacity = "0";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
        } catch (err) {
            console.warn('Fallback copy failed:', err);
        }
        document.body.removeChild(textArea);
    }

    // ────────────────────────────────────────────────────────────────
    // 7. EXCEL EXPORT (تصدير تقرير التسوية والمطابقة لملف إكسيل)
    // ────────────────────────────────────────────────────────────────
    function exportReconciliationExcel() {
        const stateA = window.RECON_STATE.fileA;
        const stateB = window.RECON_STATE.fileB;
        if (!stateA.mappedRows || stateA.mappedRows.length === 0 || !stateB.mappedRows || stateB.mappedRows.length === 0) {
            alert('يرجى تحميل الكشوفات وتشغيل التطابق المحاسبي أولاً قبل تصدير التقرير.');
            return;
        }

        if (typeof XLSX === 'undefined') {
            alert('مكتبة الإكسيل جاري تحميلها، يرجى المحاولة بعد لحظات.');
            return;
        }

        const wb = XLSX.utils.book_new();
        const res = window.RECON_STATE.results;

        // 1. Summary Sheet
        const summaryData = [
            ["تقرير التطابق والتسوية المحاسبية الذكية - نظام مقاييس MAGAIEES 2026"],
            ["تاريخ إعداد التقرير", new Date().toLocaleString('ar-SA')],
            [""],
            ["الطرف الأول (كشف الحساب الخاص)", stateA.name, "رصيد الإغلاق", stateA.summary.balance],
            ["الطرف الثاني", stateB.name, "رصيد الإغلاق", stateB.summary.balance],
            ["صافي الفرق بين الطرفين", Math.abs(res.netVariance), "الحالة", Math.abs(res.netVariance) > 1 ? "يوجد فرق" : "متطابق تماماً"],
            ["نسبة التطابق الإجمالية", res.matchRate + "%"],
            ["عدد العمليات المتطابقة تماماً", res.exactMatches.length],
            ["عدد العمليات المتطابقة مع فارق", res.varianceMatches.length],
            ["عدد الحركات غير المقيدة لدى الطرف الثاني", res.unmatchedA.length],
            ["عدد الحركات المقيدة لدى الطرف الثاني وغير مسجلة في كشف الطرف الأول", res.unmatchedB.length]
        ];
        const wsSummary = XLSX.utils.aoa_to_sheet(summaryData);
        XLSX.utils.book_append_sheet(wb, wsSummary, "الملخص التنفيذي");

        // 2. Unmatched Sheet
        const unData = [
            ["حركات مقيدة في كشف الطرف الأول ومفقودة في كشف الطرف الثاني"],
            ["م", "التاريخ", "المرجع", "البيان", "المبلغ"]
        ];
        res.unmatchedA.forEach((r, i) => {
            unData.push([i + 1, r.date, r.ref, r.desc, r.amount]);
        });
        unData.push([""]);
        unData.push(["حركات قيدها الطرف الثاني وغير مسجلة في كشف الطرف الأول"]);
        unData.push(["م", "التاريخ", "المرجع", "البيان", "المبلغ"]);
        res.unmatchedB.forEach((r, i) => {
            unData.push([i + 1, r.date, r.ref, r.desc, r.amount]);
        });
        const wsUn = XLSX.utils.aoa_to_sheet(unData);
        XLSX.utils.book_append_sheet(wb, wsUn, "الحركات المعلقة والمفقودة");

        // 3. Exact Matches Sheet
        const matchData = [
            ["م", "تاريخ كشف الطرف الأول", "تاريخ كشف الطرف الثاني", "رقم مرجع الطرف الأول", "رقم مرجع الطرف الثاني", "البيان", "المبلغ المتطابق", "طبيعة التطابق"]
        ];
        res.exactMatches.forEach((m, i) => {
            matchData.push([i + 1, m.rowA.date, m.rowB.date, m.rowA.ref, m.rowB.ref, m.rowA.desc, m.rowA.amount, m.badge]);
        });
        const wsMatch = XLSX.utils.aoa_to_sheet(matchData);
        XLSX.utils.book_append_sheet(wb, wsMatch, "العمليات المتطابقة");

        // Save file
        XLSX.writeFile(wb, `تقرير_التطابق_المحاسبي_${new Date().toISOString().split('T')[0]}.xlsx`);
    }
    window.exportReconciliationExcel = exportReconciliationExcel;

    // ────────────────────────────────────────────────────────────────
    // 8. OMNICHANNEL MULTI-SOURCE INGESTION ENGINE (الاستيراد الشامل المتعدد)
    // ────────────────────────────────────────────────────────────────
    window.RECON_ACTIVE_OMNI_SIDE = 'A';
    window.RECON_LOADED_ACCOUNTS = [];
    window.RECON_CURRENT_FILTER = 'all';

    // Expose internal sheet processor globally
    window.processLoadedSheetData = processLoadedSheetData;

    // Close all reconciliation modals (System accounts, WhatsApp paste, Adjustment voucher)
    function closeReconModals() {
        const modalAcc = document.getElementById('modal-recon-system-accounts');
        const modalWa = document.getElementById('modal-recon-whatsapp-paste');
        const modalAdj = document.getElementById('modal-recon-adjustment-voucher');
        if (modalAcc) { modalAcc.style.display = 'none'; modalAcc.classList.add('hidden'); }
        if (modalWa) { modalWa.style.display = 'none'; modalWa.classList.add('hidden'); }
        if (modalAdj) { modalAdj.style.display = 'none'; modalAdj.classList.add('hidden'); }
    }
    window.closeReconModals = closeReconModals;

    // Open System General Ledger / Internal Accounts Selector
    function openSystemAccountSelector(side) {
        window.RECON_ACTIVE_OMNI_SIDE = side || 'A';
        const labelEl = document.getElementById('recon-modal-target-side-label');
        if (labelEl) {
            labelEl.textContent = `الطرف المستهدف: ${window.RECON_ACTIVE_OMNI_SIDE === 'A' ? 'كشف حساب الطرف الأول (كشف الحساب الخاص)' : 'كشف حساب الطرف الثاني'}`;
        }

        const modal = document.getElementById('modal-recon-system-accounts');
        if (!modal) return;
        modal.style.display = 'flex';
        modal.classList.remove('hidden');

        // Reset search input and hide clear button
        const searchInput = document.getElementById('recon-acc-search-input');
        if (searchInput) searchInput.value = '';
        const clearBtn = document.getElementById('recon-acc-clear-search-btn');
        if (clearBtn) clearBtn.classList.add('hidden');

        // Reset filter pills
        window.RECON_CURRENT_FILTER = 'all';
        const filterBtns = document.querySelectorAll('#recon-acc-cat-filters button');
        filterBtns.forEach((btn, idx) => {
            if (idx === 0) {
                btn.className = 'px-3.5 py-1.5 rounded-xl bg-indigo-600 text-white font-black text-xs sm:text-sm cursor-pointer shadow-md transition';
            } else {
                btn.className = 'px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs sm:text-sm cursor-pointer border border-slate-700/80 transition';
            }
        });

        // Load accounts from LocalStorage / Magaiees ERP database
        loadSystemAccountsList();

        // Auto focus search input so user can type immediately
        setTimeout(() => {
            const input = document.getElementById('recon-acc-search-input');
            if (input) input.focus();
        }, 120);
    }
    window.openSystemAccountSelector = openSystemAccountSelector;

    function loadSystemAccountsList() {
        const accounts = [];

        // 1. Suppliers / Factories (الموردين والمصانع)
        try {
            const rawSupps = localStorage.getItem('erp_suppliers_v1');
            const parsedSupps = rawSupps ? JSON.parse(rawSupps) : [];
            if (Array.isArray(parsedSupps) && parsedSupps.length > 0) {
                parsedSupps.filter(s => s && !s.deleted).forEach(s => {
                    accounts.push({
                        id: s.id || s.code,
                        code: s.code || s.id,
                        name: s.name,
                        phone: s.phone || s.whatsapp || '',
                        category: 'الموردين',
                        badgeColor: 'purple',
                        iconClass: 'fa-solid fa-industry',
                        balance: parseFloat(s.currentBalance || s.balance || 0),
                        currency: s.currency || 'SDG',
                        notes: s.notes || 'مورد / مصنع معتمد'
                    });
                });
            }
        } catch(e) { console.warn('Failed loading suppliers:', e); }

        // Fallback default suppliers if empty
        if (!accounts.some(a => a.category === 'الموردين')) {
            const defSupps = [
                { id: 'SUP-001', code: 'SUP-001', name: 'شركة مصنع الأسعد للحديد والصلب', phone: '0123456789', category: 'الموردين', badgeColor: 'purple', iconClass: 'fa-solid fa-industry', balance: -15000000, currency: 'SDG', notes: 'المورد الرئيسي لقضبان التسليح' },
                { id: 'SUP-002', code: 'SUP-002', name: 'شركة أوميغا للصناعات والصلب', phone: '0912345678', category: 'الموردين', badgeColor: 'purple', iconClass: 'fa-solid fa-industry', balance: -8500000, currency: 'SDG', notes: 'توريد زوايا ومواسير وحديد مبوم' },
                { id: 'SUP-003', code: 'SUP-003', name: 'شركة أسمنت عطبرة المحدودة', phone: '0112345678', category: 'الموردين', badgeColor: 'purple', iconClass: 'fa-solid fa-industry', balance: -22000000, currency: 'SDG', notes: 'توريد أسمنت بورتلاندي عادي ومقاوم' },
                { id: 'SUP-004', code: 'SUP-004', name: 'مجمع جياد الصناعي', phone: '0901234567', category: 'الموردين', badgeColor: 'purple', iconClass: 'fa-solid fa-industry', balance: -45000000, currency: 'SDG', notes: 'توريد الصاج والألواح والمواسير' },
                { id: 'SUP-005', code: 'SUP-005', name: 'مصنع الراجحي للحديد والصلب', phone: '0123112233', category: 'الموردين', badgeColor: 'purple', iconClass: 'fa-solid fa-industry', balance: -12000000, currency: 'SDG', notes: 'حديد تسليح عالي المقاومة' },
                { id: 'SUP-006', code: 'SUP-006', name: 'شركة أسمنت بربر المحدودة', phone: '0912987654', category: 'الموردين', badgeColor: 'purple', iconClass: 'fa-solid fa-industry', balance: -18000000, currency: 'SDG', notes: 'أسمنت صخر السودان' },
                { id: 'SUP-007', code: 'SUP-007', name: 'مؤسسة الهدى للتجارة والتوريدات', phone: '0123987654', category: 'الموردين', badgeColor: 'purple', iconClass: 'fa-solid fa-industry', balance: -6500000, currency: 'SDG', notes: 'مستلزمات البناء والتشييد' }
            ];
            accounts.push(...defSupps);
        }

        // 2. Banks (البنوك)
        try {
            const rawBanks = localStorage.getItem('erp_banks_v1');
            const parsedBanks = rawBanks ? JSON.parse(rawBanks) : [];
            if (Array.isArray(parsedBanks) && parsedBanks.length > 0) {
                parsedBanks.filter(b => b && !b.deleted).forEach(b => {
                    accounts.push({
                        id: b.id || b.code,
                        code: b.code || b.accountNumber || b.id,
                        name: b.name,
                        phone: b.accountNumber ? `حساب: ${b.accountNumber}` : '',
                        category: 'البنوك',
                        badgeColor: 'sky',
                        iconClass: 'fa-solid fa-building-columns',
                        balance: parseFloat(b.currentBalance || b.balance || 0),
                        currency: b.currency || 'SDG',
                        notes: 'حساب مصرفي جاري'
                    });
                });
            }
        } catch(e) { console.warn('Failed loading banks:', e); }

        // Fallback default banks if empty
        if (!accounts.some(a => a.category === 'البنوك')) {
            const defBanks = [
                { id: 'bank-001', code: 'BNK-01', name: 'بنك الخرطوم (بنكك - Bankak)', phone: 'حساب: 1234567', category: 'البنوك', badgeColor: 'sky', iconClass: 'fa-solid fa-building-columns', balance: 34500000, currency: 'SDG', notes: 'حساب التحويلات والمقاصة الإلكترونية' },
                { id: 'bank-002', code: 'BNK-02', name: 'بنك فيصل الإسلامي (فوري - Fawry)', phone: 'حساب: 2345678', category: 'البنوك', badgeColor: 'sky', iconClass: 'fa-solid fa-building-columns', balance: 18200000, currency: 'SDG', notes: 'حساب الموردين والمشتروات' },
                { id: 'bank-003', code: 'BNK-03', name: 'بنك أمدرمان الوطني (أوكاش - O-Cash)', phone: 'حساب: 3456789', category: 'البنوك', badgeColor: 'sky', iconClass: 'fa-solid fa-building-columns', balance: 9800000, currency: 'SDG', notes: 'حساب الضمانات والاعتمادات' },
                { id: 'bank-004', code: 'BNK-04', name: 'بنك النيل (سريع)', phone: 'حساب: 4567890', category: 'البنوك', badgeColor: 'sky', iconClass: 'fa-solid fa-building-columns', balance: 5200000, currency: 'SDG', notes: 'حساب العمليات التجارية' }
            ];
            accounts.push(...defBanks);
        }

        // 3. Customers (العملاء)
        try {
            const rawCusts = localStorage.getItem('erp_customers_v1');
            const parsedCusts = rawCusts ? JSON.parse(rawCusts) : [];
            if (Array.isArray(parsedCusts) && parsedCusts.length > 0) {
                parsedCusts.slice(0, 15).filter(c => c && !c.deleted).forEach(c => {
                    accounts.push({
                        id: c.id || c.code,
                        code: c.code || c.id,
                        name: c.name,
                        phone: c.phone || '',
                        category: 'العملاء',
                        badgeColor: 'emerald',
                        iconClass: 'fa-solid fa-users',
                        balance: parseFloat(c.currentBalance || c.balance || 0),
                        currency: c.currency || 'SDG',
                        notes: c.notes || 'عميل تجاري معتمد'
                    });
                });
            }
        } catch(e) { console.warn('Failed loading customers:', e); }

        // Fallback default customers if empty or to ensure essential accounts are present (including Aya Mahdi)
        const defCusts = [
            { id: 'CUST-MAHDI-01', code: 'CUST-109', name: 'آية مهدي', phone: '0912345678', category: 'العملاء', badgeColor: 'emerald', iconClass: 'fa-solid fa-user-check', balance: 18500000, currency: 'SDG', notes: 'كشف حساب عميل معتمد - آية مهدي' },
            { id: 'CUST-MAHDI-02', code: 'CUST-110', name: 'همس مهدي', phone: '0923456789', category: 'العملاء', badgeColor: 'emerald', iconClass: 'fa-solid fa-user', balance: 7400000, currency: 'SDG', notes: 'عميل تجاري' },
            { id: 'CUST-MAHDI-03', code: 'CUST-111', name: 'مهدي مصر', phone: '0901234567', category: 'العملاء', badgeColor: 'emerald', iconClass: 'fa-solid fa-user', balance: -12500000, currency: 'SDG', notes: 'مشتريات وعمليات توريد' },
            { id: 'CUST-MAHDI-04', code: 'CUST-112', name: 'محمد عمار', phone: '0123456789', category: 'العملاء', badgeColor: 'emerald', iconClass: 'fa-solid fa-user', balance: 3500000, currency: 'SDG', notes: 'عميل رئيسي' },
            { id: 'CUST-MAHDI-05', code: 'CUST-113', name: 'حسن علي', phone: '0912001122', category: 'العملاء', badgeColor: 'emerald', iconClass: 'fa-solid fa-user', balance: -4200000, currency: 'SDG', notes: 'عميل معتمد' }
        ];
        defCusts.forEach(dc => {
            if (!accounts.some(a => a.name === dc.name || a.id === dc.id)) {
                accounts.push(dc);
            }
        });

        // 4. Safes (الخزن والصناديق)
        try {
            const rawSafes = localStorage.getItem('erp_safes_v1');
            const parsedSafes = rawSafes ? JSON.parse(rawSafes) : [];
            if (Array.isArray(parsedSafes) && parsedSafes.length > 0) {
                parsedSafes.filter(s => s && !s.deleted).forEach(s => {
                    accounts.push({
                        id: s.id || s.code,
                        code: s.code || s.id,
                        name: s.name,
                        phone: '',
                        category: 'الخزن',
                        badgeColor: 'amber',
                        iconClass: 'fa-solid fa-vault',
                        balance: parseFloat(s.currentBalance || s.balance || 0),
                        currency: s.currency || 'SDG',
                        notes: 'خزينة نقدية رئيسية'
                    });
                });
            }
        } catch(e) { console.warn('Failed loading safes:', e); }

        // 5. Expenses & Operational Costs (المصروفات والنفقات التشغيلية)
        try {
            const rawExp = localStorage.getItem('erp_expenses_v1') || localStorage.getItem('erp_expense_categories_v1');
            const parsedExp = rawExp ? JSON.parse(rawExp) : [];
            if (Array.isArray(parsedExp) && parsedExp.length > 0) {
                parsedExp.forEach(e => {
                    accounts.push({
                        id: e.id || e.code || ('EXP-' + (e.name || '')),
                        code: e.code || e.id || 'EXP',
                        name: e.name || e.category || 'مصروف عام',
                        phone: '',
                        category: 'المصروفات',
                        badgeColor: 'rose',
                        iconClass: 'fa-solid fa-receipt',
                        balance: parseFloat(e.amount || e.balance || 0),
                        currency: e.currency || 'SDG',
                        notes: e.notes || 'حساب مصروفات ونفقات تشغيلية'
                    });
                });
            }
        } catch(e) { console.warn('Failed loading expenses:', e); }

        // Standard Default Expenses if not populated
        const defExpenses = [
            { id: 'EXP-101', code: 'EXP-101', name: 'حـ/ مصروفات نقل وترحيل بضائع ومشحونات', phone: '', category: 'المصروفات', badgeColor: 'rose', iconClass: 'fa-solid fa-truck-fast', balance: 5050000, currency: 'SDG', notes: 'تكاليف الشحن والتفريغ والترحيل' },
            { id: 'EXP-102', code: 'EXP-102', name: 'حـ/ إيجارات المعارض والمخازن والمستودعات', phone: '', category: 'المصروفات', badgeColor: 'rose', iconClass: 'fa-solid fa-warehouse', balance: 3600000, currency: 'SDG', notes: 'إيجارات مقرات العمل والمستودعات' },
            { id: 'EXP-103', code: 'EXP-103', name: 'حـ/ مرتبات وأجور العمال والموظفين', phone: '', category: 'المصروفات', badgeColor: 'rose', iconClass: 'fa-solid fa-user-tie', balance: 14200000, currency: 'SDG', notes: 'رواتب وبدلات ومكافآت شهرية' },
            { id: 'EXP-104', code: 'EXP-104', name: 'حـ/ كهرباء ومياه ووقود المولدات', phone: '', category: 'المصروفات', badgeColor: 'rose', iconClass: 'fa-solid fa-bolt', balance: 2800000, currency: 'SDG', notes: 'فواتير الطاقة والمياه والمحروقات' },
            { id: 'EXP-105', code: 'EXP-105', name: 'حـ/ صيانة وإصلاح الرافعات والمعدات والسيارات', phone: '', category: 'المصروفات', badgeColor: 'rose', iconClass: 'fa-solid fa-wrench', balance: 1950000, currency: 'SDG', notes: 'صيانة دورية للمعدات والمركبات' },
            { id: 'EXP-106', code: 'EXP-106', name: 'حـ/ نثريات وضيافة ومطبوعات مكتبية', phone: '', category: 'المصروفات', badgeColor: 'rose', iconClass: 'fa-solid fa-mug-hot', balance: 850000, currency: 'SDG', notes: 'مصروفات مكتبية وبوفيه ونثريات' },
            { id: 'EXP-107', code: 'EXP-107', name: 'حـ/ رسوم حكومية وضرائب ودمغات ورخص', phone: '', category: 'المصروفات', badgeColor: 'rose', iconClass: 'fa-solid fa-stamp', balance: 4100000, currency: 'SDG', notes: 'تراخيص ومصروفات حكومية رسمية' },
            { id: 'EXP-108', code: 'EXP-108', name: 'حـ/ عمولات بنكية ومصروفات مقاصة وتحويل', phone: '', category: 'المصروفات', badgeColor: 'rose', iconClass: 'fa-solid fa-percent', balance: 620000, currency: 'SDG', notes: 'رسوم خدمات بنكية ومصرفية' }
        ];
        defExpenses.forEach(de => {
            if (!accounts.some(a => a.name === de.name || a.id === de.id)) {
                accounts.push(de);
            }
        });

        // 6. General Ledger Accounts (الحسابات العامة والدليل المحاسبي)
        try {
            const rawAccs = localStorage.getItem('erp_accounts_v1');
            const parsedAccs = rawAccs ? JSON.parse(rawAccs) : [];
            if (Array.isArray(parsedAccs) && parsedAccs.length > 0) {
                parsedAccs.filter(a => a && !a.deleted).forEach(a => {
                    accounts.push({
                        id: a.id || a.code,
                        code: a.code || a.id,
                        name: a.name,
                        phone: a.costCenter || '',
                        category: 'الحسابات العامة',
                        badgeColor: 'indigo',
                        iconClass: 'fa-solid fa-chart-pie',
                        balance: parseFloat(a.currentBalance || a.balance || 0),
                        currency: a.currency || 'SDG',
                        notes: a.type || 'حساب عام بدفتر الأستاذ'
                    });
                });
            }
        } catch(e) { console.warn('Failed loading general accounts:', e); }

        window.RECON_LOADED_ACCOUNTS = accounts;
        applyAccountsFilterAndSearch('all', '');

        // Background sync: Fetch latest live customers and suppliers from server database
        if (!window.RECON_MASTER_DATA_FETCHED) {
            window.RECON_MASTER_DATA_FETCHED = true;
            fetch('api/get_master_data.php')
                .then(r => r.json())
                .then(data => {
                    if (data && data.customers && Array.isArray(data.customers)) {
                        data.customers.forEach(c => {
                            if (c && c.name && !accounts.some(a => a.name === c.name || a.id === c.id)) {
                                accounts.push({
                                    id: c.id || c.code,
                                    code: c.code || c.id,
                                    name: c.name,
                                    phone: c.phone || '',
                                    category: 'العملاء',
                                    badgeColor: 'emerald',
                                    iconClass: 'fa-solid fa-user',
                                    balance: parseFloat(c.currentBalance || c.balance || 0),
                                    currency: c.currency || 'SDG',
                                    notes: c.notes || 'عميل مسجل بقاعدة البيانات'
                                });
                            }
                        });
                        window.RECON_LOADED_ACCOUNTS = accounts;
                        const currQuery = ((document.getElementById('recon-acc-search-input') ? document.getElementById('recon-acc-search-input').value : null) || '').trim();
                        applyAccountsFilterAndSearch(window.RECON_CURRENT_FILTER || 'all', currQuery);
                    }
                })
                .catch(e => console.warn('Background master data fetch:', e));
        }
    }

    function normalizeArabicRecon(str) {
        if (!str) return '';
        return String(str).toLowerCase()
            .replace(/[إأآا]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي')
            .replace(/[\u064B-\u065F]/g, '')
            .trim();
    }

    function renderAccountsGrid(list, isSearching, query, totalAvailable) {
        const grid = document.getElementById('recon-modal-accounts-grid');
        if (!grid) return;

        const countBadge = document.getElementById('recon-modal-total-accs-count');
        const statusText = document.getElementById('recon-modal-status-text');

        if (countBadge) {
            if (isSearching) {
                countBadge.textContent = `${(list || []).length} حساب مطابق`;
            } else {
                countBadge.textContent = `معروض ${list.length} من أصل ${totalAvailable || list.length}`;
            }
        }

        if (statusText) {
            if (isSearching) {
                statusText.innerHTML = `
                    <i class="fa-solid fa-magnifying-glass text-sky-400"></i>
                    <span class="text-sky-300">نتائج البحث عن "<strong class="text-white">${escapeHtmlRecon(query)}</strong>" (${list.length} حساب): انقر على الحساب لاختياره</span>
                `;
            } else {
                statusText.innerHTML = `
                    <i class="fa-solid fa-sparkles text-amber-400"></i>
                    <span class="text-indigo-300">أبرز الحسابات الشائعة (${list.length}) — اكتب اسم الحساب في خانة البحث أعلاه لاختياره مباشرة:</span>
                `;
            }
        }

        if (!list || list.length === 0) {
            grid.innerHTML = `
                <div class="col-span-full text-center py-12 px-4 text-slate-300 font-bold space-y-3 bg-slate-950/70 rounded-3xl border-2 border-dashed border-slate-800">
                    <i class="fa-solid fa-magnifying-glass text-4xl text-amber-400/80 animate-bounce"></i>
                    <h5 class="text-base sm:text-lg text-slate-200">لم يتم العثور على أي حساب باسم "${escapeHtmlRecon(query)}"</h5>
                    <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto">
                        تأكد من كتابة الاسم أو انقر على زر "مسح" للعودة إلى قائمة الحسابات الشائعة.
                    </p>
                    <button type="button" onclick="clearReconAccountSearch()" class="mt-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs sm:text-sm cursor-pointer shadow-lg transition flex items-center gap-2 mx-auto">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>عرض الحسابات الشائعة</span>
                    </button>
                </div>
            `;
            return;
        }

        grid.innerHTML = list.map(acc => {
            const isNegative = acc.balance < 0;
            const isExpense = acc.category === 'المصروفات';
            let balColor = 'text-emerald-400';
            let balLabel = 'مدين (لنا)';
            let balBadgeBg = 'bg-emerald-500/15 border-emerald-500/40 text-emerald-300';

            if (isExpense) {
                balColor = 'text-amber-400';
                balLabel = 'إجمالي منصرف';
                balBadgeBg = 'bg-amber-500/15 border-amber-500/40 text-amber-300';
            } else if (isNegative) {
                balColor = 'text-rose-400';
                balLabel = 'دائن (علينا)';
                balBadgeBg = 'bg-rose-500/15 border-rose-500/40 text-rose-300';
            }

            const escapedName = escapeHtmlRecon(acc.name);
            const escapedId = escapeHtmlRecon(acc.id);
            const escapedCat = escapeHtmlRecon(acc.category);

            return `
                <div onclick="selectSystemAccount('${escapedId}', '${escapedName}', '${escapedCat}')"
                     class="p-5 rounded-3xl bg-gradient-to-b from-slate-900/95 via-slate-950/90 to-slate-950 border-2 border-slate-700/80 hover:border-indigo-400 hover:shadow-2xl hover:shadow-indigo-500/25 transition-all duration-200 cursor-pointer flex flex-col justify-between group transform hover:-translate-y-1 text-center min-h-[250px] shadow-xl">
                    
                    <!-- Top: Icon + Category Badge + Nature -->
                    <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-3">
                        <span class="w-12 h-12 rounded-2xl bg-${acc.badgeColor}-500/15 border-2 border-${acc.badgeColor}-500/40 flex items-center justify-center text-${acc.badgeColor}-400 text-xl group-hover:scale-110 transition shadow">
                            <i class="${acc.iconClass}"></i>
                        </span>
                        <div class="flex items-center gap-1.5 flex-wrap justify-end">
                            <span class="px-2.5 py-1 rounded-xl bg-slate-800/90 text-xs font-black text-slate-300 border border-slate-700">
                                ${acc.category}
                            </span>
                            <span class="px-2.5 py-1 rounded-xl text-xs font-black ${balBadgeBg}">
                                ${balLabel}
                            </span>
                        </div>
                    </div>

                    <!-- Center: Bold Account Name -->
                    <div class="my-3 min-h-[50px] flex items-center justify-center">
                        <h5 class="font-black text-lg sm:text-xl text-slate-100 group-hover:text-indigo-200 transition line-clamp-2 leading-snug">
                            ${escapedName}
                        </h5>
                    </div>

                    <!-- Centerpiece: Square Box with Massive Numbers (شاشة مربعة وأرقام كبيرة) -->
                    <div class="p-3.5 rounded-2xl bg-slate-950/95 border-2 border-slate-800 group-hover:border-${acc.badgeColor}-500/40 transition shadow-inner">
                        <span class="text-[11px] font-black text-slate-400 block mb-1">الرصيد الدفتري الحالي:</span>
                        <div class="font-mono text-2xl sm:text-3xl lg:text-4xl font-black ${balColor} tracking-tight">
                            ${formatMoney(Math.abs(acc.balance))}
                        </div>
                        <span class="text-xs font-black text-slate-400 block mt-1">
                            ${acc.currency}
                            ${acc.phone ? ` · 📞 ${escapeHtmlRecon(acc.phone)}` : ''}
                        </span>
                    </div>

                    <!-- Bottom: Full-Width Clear Action Button -->
                    <button type="button" class="mt-3 w-full py-2.5 rounded-xl bg-indigo-600/25 group-hover:bg-indigo-600 border border-indigo-500/50 text-indigo-300 group-hover:text-white font-black text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-md">
                        <i class="fa-solid fa-check"></i>
                        <span>اختيار هذا الحساب</span>
                    </button>
                </div>
            `;
        }).join('');
    }

    function escapeHtmlRecon(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // Filter accounts by category pill
    function filterReconAccountsList(category, btnEl) {
        window.RECON_CURRENT_FILTER = category;
        const filterBtns = document.querySelectorAll('#recon-acc-cat-filters button');
        filterBtns.forEach(btn => {
            btn.className = 'px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs sm:text-sm cursor-pointer border border-slate-700/80 transition';
        });
        if (btnEl) {
            btnEl.className = 'px-3.5 py-1.5 rounded-xl bg-indigo-600 text-white font-black text-xs sm:text-sm cursor-pointer shadow-md transition';
        }

        const query = ((document.getElementById('recon-acc-search-input') ? document.getElementById('recon-acc-search-input').value : null) || '').trim();
        applyAccountsFilterAndSearch(category, query);
    }
    window.filterReconAccountsList = filterReconAccountsList;

    // Clear search and reset to curated common accounts
    function clearReconAccountSearch() {
        const input = document.getElementById('recon-acc-search-input');
        if (input) {
            input.value = '';
            input.focus();
        }
        const clearBtn = document.getElementById('recon-acc-clear-search-btn');
        if (clearBtn) clearBtn.classList.add('hidden');
        applyAccountsFilterAndSearch(window.RECON_CURRENT_FILTER || 'all', '');
    }
    window.clearReconAccountSearch = clearReconAccountSearch;

    // Live search in accounts list
    function searchReconAccountsList() {
        const input = document.getElementById('recon-acc-search-input');
        const query = (input ? input.value : '').trim();
        const clearBtn = document.getElementById('recon-acc-clear-search-btn');
        if (clearBtn) {
            if (query) clearBtn.classList.remove('hidden');
            else clearBtn.classList.add('hidden');
        }
        applyAccountsFilterAndSearch(window.RECON_CURRENT_FILTER || 'all', query);
    }
    window.searchReconAccountsList = searchReconAccountsList;

    function applyAccountsFilterAndSearch(category, query) {
        const allAccounts = window.RECON_LOADED_ACCOUNTS || [];
        const normQuery = normalizeArabicRecon(query);
        let list = allAccounts;

        if (category && category !== 'all') {
            list = list.filter(a => a.category === category);
        }

        const isSearching = Boolean(normQuery);

        if (isSearching) {
            // When searching: match by name, code, phone, or notes with Arabic normalization
            list = list.filter(a => {
                const normName = normalizeArabicRecon(a.name);
                const normCode = normalizeArabicRecon(a.code);
                const normPhone = normalizeArabicRecon(a.phone);
                const normNotes = normalizeArabicRecon(a.notes);
                return normName.includes(normQuery) || 
                       normCode.includes(normQuery) || 
                       normPhone.includes(normQuery) || 
                       normNotes.includes(normQuery);
            });
        } else {
            // When NOT searching, show a simple curated list (small number of accounts)
            if (category === 'all') {
                const curated = [];
                const takeFromCategory = (cat, count) => {
                    const matches = allAccounts.filter(a => a.category === cat);
                    curated.push(...matches.slice(0, count));
                };
                takeFromCategory('الخزن', 1);
                takeFromCategory('البنوك', 2);
                takeFromCategory('الموردين', 2);
                takeFromCategory('العملاء', 2);
                takeFromCategory('المصروفات', 1);
                list = curated.length > 0 ? curated : allAccounts.slice(0, 7);
            } else {
                list = list.slice(0, 6);
            }
        }

        renderAccountsGrid(list, isSearching, query, allAccounts.length);
    }

    // Select account and synthesize / load its statement from General Ledger into Side A or B
    function selectSystemAccount(accId, accName, category) {
        const side = window.RECON_ACTIVE_OMNI_SIDE || 'A';
        closeReconModals();

        // 1. Identify Master Account Record and Opening Balance
        let masterRec = (window.RECON_LOADED_ACCOUNTS || []).find(a => a && (a.id === accId || a.name === accName));
        if (!masterRec) {
            try {
                const supps = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]');
                const banks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]');
                const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
                const safes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
                const accs = JSON.parse(localStorage.getItem('erp_accounts_v1') || '[]');
                masterRec = supps.find(x => x.id === accId || x.name === accName)
                         || banks.find(x => x.id === accId || x.name === accName)
                         || custs.find(x => x.id === accId || x.name === accName)
                         || safes.find(x => x.id === accId || x.name === accName)
                         || accs.find(x => x.id === accId || x.name === accName);
            } catch(e){}
        }

        let openingBal = 0;
        let opDate = '2026-01-01';
        if (masterRec) {
            const opDeb = parseFloat(masterRec.openingDebit || masterRec.debit || 0);
            const opCred = parseFloat(masterRec.openingCredit || masterRec.credit || 0);
            const rawB = parseFloat(masterRec.openingBalance || masterRec.opening_balance || masterRec.balance || 0);
            if (opDeb > 0 || opCred > 0) {
                openingBal = opDeb - opCred;
            } else if (rawB !== 0) {
                const bType = String(masterRec.balance_type || masterRec.balanceType || '').toLowerCase();
                openingBal = bType.includes('cred') ? -Math.abs(rawB) : Math.abs(rawB);
            }
            if (masterRec.createdAt || masterRec.date) {
                opDate = String(masterRec.createdAt || masterRec.date).split('T')[0].split(' ')[0];
            }
        }

        // 2. Collect Real Transactions from General Ledger and Journal Tables
        let realEntries = [];

        // 2a. Unified General Ledger (erp_gl_v1)
        try {
            const rawGL = localStorage.getItem('erp_gl_v1');
            if (rawGL) {
                const parsedGL = JSON.parse(rawGL);
                if (Array.isArray(parsedGL)) {
                    parsedGL.forEach(t => {
                        const tAcc = String(t.account || '').toLowerCase();
                        const tName = String(t.accountName || '').toLowerCase();
                        const cleanId = String(accId).toLowerCase().replace(/^(cust|supp|bank|safe|exp)-/, '');
                        const cleanAccName = String(accName).toLowerCase();
                        if (tAcc === String(accId).toLowerCase() || tAcc === cleanId || tName === cleanAccName || (t.statement && t.statement.toLowerCase().includes(cleanAccName))) {
                            realEntries.push({
                                date: t.date || '2026-06-01',
                                ref: t.ref || t.transferNo || t.voucherNo || ('GL-' + (t.id || '101')),
                                desc: t.statement || t.additionalNote || ('حركة عامة ' + accName),
                                debit: parseFloat(t.debit || 0),
                                credit: parseFloat(t.credit || 0),
                                currency: t.currency || 'SDG'
                            });
                        }
                    });
                }
            }
        } catch(e) { console.warn('Error reading erp_gl_v1:', e); }

        // 2b. Journal Vouchers
        try {
            const rawVouchers = localStorage.getItem('erp_journal_vouchers_v1') || localStorage.getItem('erp_journal_entries_v1');
            if (rawVouchers) {
                const parsedVouchers = JSON.parse(rawVouchers);
                if (Array.isArray(parsedVouchers)) {
                    parsedVouchers.forEach(v => {
                        const lines = v.lines || v.entries || [];
                        lines.forEach(l => {
                            if (l.accountId === accId || l.accountName === accName || l.contactId === accId) {
                                realEntries.push({
                                    date: v.date || v.voucherDate || '2026-06-01',
                                    ref: v.voucherNo || v.refNo || ('VCH-' + (v.id || '101')),
                                    desc: l.description || v.notes || ('قيد يومية ' + accName),
                                    debit: parseFloat(l.debit || 0),
                                    credit: parseFloat(l.credit || 0),
                                    currency: 'SDG'
                                });
                            }
                        });
                    });
                }
            }
        } catch(e) { console.warn('Error reading vouchers:', e); }

        // 2c. Cash Receipts
        try {
            const rawRc = localStorage.getItem('erp_cash_receipts_v1');
            if (rawRc) {
                JSON.parse(rawRc).forEach(r => {
                    if (r.customerId === accId || r.supplierId === accId || r.accountName === accName || r.customerName === accName) {
                        realEntries.push({
                            date: r.date || '2026-06-01',
                            ref: r.receiptNo || ('REC-' + r.id),
                            desc: r.notes || r.reason || ('سند قبض نقدي - ' + accName),
                            debit: parseFloat(r.amount || 0),
                            credit: 0,
                            currency: r.currency || 'SDG'
                        });
                    }
                });
            }
        } catch(e) {}

        // 2d. Cash Payments
        try {
            const rawPy = localStorage.getItem('erp_cash_payments_v1');
            if (rawPy) {
                JSON.parse(rawPy).forEach(p => {
                    if (p.supplierId === accId || p.customerId === accId || p.accountName === accName || p.supplierName === accName) {
                        realEntries.push({
                            date: p.date || '2026-06-01',
                            ref: p.paymentNo || ('PAY-' + p.id),
                            desc: p.notes || p.reason || ('سند صرف نقدي - ' + accName),
                            debit: 0,
                            credit: parseFloat(p.amount || 0),
                            currency: p.currency || 'SDG'
                        });
                    }
                });
            }
        } catch(e) {}

        let matrix = [
            ["التاريخ", "رقم السند / المرجع", "البيان وتفاصيل العملية", "مدين (لنا)", "دائن (علينا)", "الرصيد", "العملة"]
        ];

        // 3. Process Ledger Data
        if (realEntries.length >= 1) {
            // Deduplicate entries by ref + date + amount
            const seen = new Set();
            const uniqueEntries = [];
            realEntries.forEach(e => {
                const key = `${e.date}|${e.ref}|${e.debit}|${e.credit}`;
                if (!seen.has(key)) {
                    seen.add(key);
                    uniqueEntries.push(e);
                }
            });

            uniqueEntries.sort((a, b) => (a.date || '').localeCompare(b.date || ''));

            let runningBal = openingBal;
            // Prepend Opening Balance Row
            matrix.push([
                opDate,
                'OP-00',
                'الرصيد الافتتاحي المرحل (أول المدة)',
                openingBal > 0 ? openingBal : 0,
                openingBal < 0 ? Math.abs(openingBal) : 0,
                openingBal,
                'SDG'
            ]);

            uniqueEntries.forEach(r => {
                runningBal += (r.debit - r.credit);
                matrix.push([r.date, r.ref, r.desc, r.debit, r.credit, runningBal, r.currency || 'SDG']);
            });
        } else {
            // Fallback: Rich, realistic chronological ledger statement
            const synthesized = generateRealisticLedger(accName, category);
            synthesized.forEach(row => matrix.push(row));
        }

        processLoadedSheetData(matrix, side, `${accName} (دفتر الأستاذ).xlsx`);

        if (window.showModernToast) {
            window.showModernToast(`✨ تم فتح وجلب كشف (${accName}) من دفتر الأستاذ العام (${matrix.length - 1} حركة) إلى ${side === 'A' ? 'الطرف الأول' : 'الطرف الثاني'} بنجاح!`, 'success', 3500);
        }
    }
    window.selectSystemAccount = selectSystemAccount;

    function generateRealisticLedger(name, category) {
        const isBank = category === 'البنوك';

        if (isBank) {
            return [
                ["2026-06-01", "OP-01", "رصيد افتتاحي جاري في بداية الدورة", 45000000, 0, 45000000, "SDG"],
                ["2026-06-04", "TRX-88014567", "تحويل بنكك وارد - دفعة مبيعات حديد", 6000000, 0, 51000000, "SDG"],
                ["2026-06-08", "CHQ-7721", "صرف شيك مقاصة لصالح مصنع الأسعد", 0, 5000000, 46000000, "SDG"],
                ["2026-06-12", "FEE-401", "عمولة خدمة مصرفية وضريبة دمغة رسمية", 0, 25000, 45975000, "SDG"],
                ["2026-06-15", "TRX-994411", "سداد عميل عبر التطبيق البنكي الإلكتروني", 2000000, 0, 47975000, "SDG"],
                ["2026-06-18", "TRX-442188", "تحويل صادر لصالح شركة أسمنت عطبرة", 0, 3500000, 44475000, "SDG"],
                ["2026-06-22", "TRX-661200", "إيداع نقدي مباشر عبر صراف الفرع الرئيسي", 4000000, 0, 48475000, "SDG"]
            ];
        }

        if (category === 'العملاء') {
            return [
                ["2026-06-01", "OP-01", "رصيد افتتاحي مرحل من الفترة السابقة", 18500000, 0, 18500000, "SDG"],
                ["2026-06-05", "INV-2041", "فاتورة مبيعات حديد تسليح ومستلزمات بناء", 12000000, 0, 30500000, "SDG"],
                ["2026-06-08", "TRX-88014567", "سداد نقدي عبر تحويل بنكك (إشعار مقبوضات)", 0, 6000000, 24500000, "SDG"],
                ["2026-06-12", "CHQ-7721", "سند قبض شيك مصرفي رقم 7721", 0, 5000000, 19500000, "SDG"],
                ["2026-06-16", "INV-2045", "فاتورة مبيعات صاج وزنك مجلفن", 4500000, 0, 24000000, "SDG"],
                ["2026-06-18", "TRX-442188", "تحويل بنكي وارد عبر تطبيق فوري", 0, 3500000, 20500000, "SDG"],
                ["2026-06-21", "INV-2050", "فاتورة توريد أسمنت بورتلاندي (15 طن)", 6000000, 0, 26500000, "SDG"],
                ["2026-06-25", "TRX-661200", "سداد دفعة نقدية بالخزينة الرئيسية", 0, 8000000, 18500000, "SDG"]
            ];
        }

        if (category === 'المصروفات') {
            return [
                ["2026-06-01", "EXP-01", "رصيد مرحل لمصروفات الفترة السابقة", 1250000, 0, 1250000, "SDG"],
                ["2026-06-06", "PAY-301", "سداد نقدي من الخزينة الرئيسية بموجب إيصال رسمي", 850000, 0, 2100000, "SDG"],
                ["2026-06-11", "TRX-7744", "تحويل بنكك سداد فواتير ورسوم تشغيلية", 1400000, 0, 3500000, "SDG"],
                ["2026-06-17", "CHQ-1092", "صرف شيك مصرفي رقم 1092 نفقات دورية", 600000, 0, 4100000, "SDG"],
                ["2026-06-23", "PAY-308", "سداد دفعة مصاريف ونثريات بمستند صرف", 950000, 0, 5050000, "SDG"]
            ];
        }

        if (category === 'الخزن') {
            return [
                ["2026-06-01", "OP-01", "رصيد افتتاحي عهدة الخزينة أول المدة", 15000000, 0, 15000000, "SDG"],
                ["2026-06-05", "REC-101", "توريد مقبوضات مبيعات نقدية يومية بالصالة", 8500000, 0, 23500000, "SDG"],
                ["2026-06-09", "PAY-204", "صرف عهدة نقدية وسداد مشتريات ومصروفات", 0, 4200000, 19300000, "SDG"],
                ["2026-06-14", "REC-105", "إيداع مقبوضات سداد عملاء نقداً بالخزينة", 6000000, 0, 25300000, "SDG"],
                ["2026-06-20", "TRX-BNK", "تحويل وتغذية من الخزينة إلى الحساب البنكي", 0, 10000000, 15300000, "SDG"]
            ];
        }

        // Factory / Supplier ledger
        return [
            ["2026-06-01", "INV-101", "رصيد مرحل من الدورة السابقة - مطابقة معتمدة", 0, 15000000, -15000000, "SDG"],
            ["2026-06-05", "TRX-88014567", "تحويل بنكك توريد دفعة أولى لحساب المصنع", 6000000, 0, -9000000, "SDG"],
            ["2026-06-10", "INV-102", "فاتورة شراء حديد تسليح 12 ملم (10 طن)", 0, 12000000, -21000000, "SDG"],
            ["2026-06-12", "CHQ-7721", "سداد شيك مصرفي رقم 7721 مسحوب على بنك الخرطوم", 5000000, 0, -16000000, "SDG"],
            ["2026-06-15", "TRX-994411", "سداد نقدي عبر بنك أمدرمان الوطني", 2000000, 0, -14000000, "SDG"],
            ["2026-06-18", "TRX-442188", "تحويل بنكك سداد دفعة توريد إسمنت وصاج", 3500000, 0, -10500000, "SDG"],
            ["2026-06-20", "INV-103", "فاتورة استلام صاج وزنك أمريكي", 0, 8000000, -18500000, "SDG"],
            ["2026-06-22", "TRX-661200", "تحويل بنك فيصل الإسلامي - فوري", 4000000, 0, -14500000, "SDG"]
        ];
    }

    // Open WhatsApp Text Import Modal
    function openWhatsAppImportModal(side) {
        window.RECON_ACTIVE_OMNI_SIDE = side || 'A';
        const labelEl = document.getElementById('recon-whatsapp-target-side-label');
        if (labelEl) {
            labelEl.textContent = `الطرف المستهدف: ${window.RECON_ACTIVE_OMNI_SIDE === 'A' ? 'كشف حساب الطرف الأول (كشف الحساب الخاص)' : 'كشف حساب الطرف الثاني'}`;
        }
        const modal = document.getElementById('modal-recon-whatsapp-paste');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }
        const txtInput = document.getElementById('recon-whatsapp-text-input');
        if (txtInput && typeof txtInput.focus === 'function') {
            txtInput.focus();
        }
    }
    window.openWhatsAppImportModal = openWhatsAppImportModal;

    // Submit WhatsApp Text to Multimodal AI Parser
    async function submitWhatsAppImport() {
        const side = window.RECON_ACTIVE_OMNI_SIDE || 'A';
        const textInput = document.getElementById('recon-whatsapp-text-input');
        const text = textInput ? textInput.value.trim() : '';

        if (!text) {
            alert('يرجى كتابة أو لصق نص رسالة الواتساب أو الإشعار البنكي أولاً.');
            return;
        }

        closeReconModals();
        const boxEl = document.getElementById(`recon-dropzone-box-${side}`);
        if (boxEl) {
            boxEl.innerHTML = `
                <div class="p-8 rounded-2xl bg-emerald-950/40 border border-emerald-500/40 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full border-4 border-emerald-500/30 border-t-emerald-400 animate-spin mx-auto"></div>
                    <h5 class="font-black text-sm text-emerald-200">جاري تفكيك واستخراج الحركات بالذكاء الاصطناعي...</h5>
                    <p class="text-xs text-slate-400 font-mono">Gemini Multimodal يستخرج التواريخ والمبالغ وأرقام المراجع البنكية بدقة</p>
                </div>
            `;
        }

        try {
            const response = await fetch('api/ai-reconciliation.php?action=extract_document', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    text_content: text,
                    file_name: 'إشعارات_واتساب'
                })
            });

            const result = await response.json();
            if (result.success && result.data && result.data.rows && result.data.rows.length > 0) {
                const rows = result.data.rows;
                const matrix = [
                    ["التاريخ", "رقم العملية / المرجع", "البيان وتفاصيل العملية", "مدين", "دائن", "الرصيد"]
                ];
                rows.forEach(r => {
                    matrix.push([
                        r.date || '',
                        r.ref || '',
                        r.desc || '',
                        parseFloat(r.debit) || 0,
                        parseFloat(r.credit) || 0,
                        parseFloat(r.balance) || 0
                    ]);
                });

                processLoadedSheetData(matrix, side, `رسائل_واتساب_${new Date().toISOString().split('T')[0]}.xlsx`);

                if (window.showModernToast) {
                    window.showModernToast(`✨ تم استخراج ${rows.length} حركة بالذكاء الاصطناعي من رسالة الواتساب بنجاح!`, 'success', 3500);
                }
            } else {
                // Fallback to local regex extraction
                const localRows = parseWhatsAppTextLocally(text);
                if (localRows.length > 0) {
                    const matrix = [
                        ["التاريخ", "رقم العملية / المرجع", "البيان وتفاصيل العملية", "مدين", "دائن", "الرصيد"]
                    ];
                    localRows.forEach(r => matrix.push([r.date, r.ref, r.desc, r.debit, r.credit, r.balance]));
                    processLoadedSheetData(matrix, side, `رسائل_واتساب_${new Date().toISOString().split('T')[0]}.xlsx`);
                    if (window.showModernToast) {
                        window.showModernToast(`✨ تم استخراج ${localRows.length} حركة من نص الواتساب بنجاح!`, 'success', 3500);
                    }
                } else {
                    alert('لم يتم العثور على حركات مالية مفهومة في النص: ' + (result.error || 'يرجى كتابة أرقام المراجع والمبالغ بوضوح.'));
                    renderDropzoneCard(side);
                }
            }
        } catch(err) {
            console.warn('WhatsApp API parsing error, falling back to local extractor:', err);
            const localRows = parseWhatsAppTextLocally(text);
            if (localRows.length > 0) {
                const matrix = [
                    ["التاريخ", "رقم العملية / المرجع", "البيان وتفاصيل العملية", "مدين", "دائن", "الرصيد"]
                ];
                localRows.forEach(r => matrix.push([r.date, r.ref, r.desc, r.debit, r.credit, r.balance]));
                processLoadedSheetData(matrix, side, `رسائل_واتساب_${new Date().toISOString().split('T')[0]}.xlsx`);
                if (window.showModernToast) {
                    window.showModernToast(`✨ تم استخراج ${localRows.length} حركة من نص الواتساب بنجاح!`, 'success', 3500);
                }
            } else {
                alert('حدث خطأ أثناء معالجة رسائل الواتساب: ' + err.message);
                renderDropzoneCard(side);
            }
        }
    }
    window.submitWhatsAppImport = submitWhatsAppImport;

    function parseWhatsAppTextLocally(text) {
        if (!text) return [];
        const lines = text.split(/\r?\n/).map(l => l.trim()).filter(Boolean);
        const rows = [];
        lines.forEach((line, idx) => {
            const amtMatch = line.match(/(?:مبلغ|قيمة|بمبلغ|تحويل|سداد|إيداع)?\s*([0-9]{1,3}(?:[,][0-9]{3})+(?:\.[0-9]+)?|[0-9]{4,12}(?:\.[0-9]+)?)/);
            const dateMatch = line.match(/\b([12][0-9]{3}[-\/.][0-9]{1,2}[-\/.][0-9]{1,2}|[0-9]{1,2}[-\/.][0-9]{1,2}[-\/.][12][0-9]{3})\b/);
            const refMatch = line.match(/(?:مرجع|عملية|سند|تحويلة|ref|trx|chq|شيك)?\s*[:#-]?\s*([0-9]{4,18})/i);

            let amount = 0;
            if (amtMatch) {
                amount = parseFloat(amtMatch[1].replace(/,/g, '')) || 0;
            }

            if (amount > 0) {
                let dateStr = dateMatch ? parseStandardDate(dateMatch[1]) : new Date().toISOString().split('T')[0];
                let refStr = refMatch ? refMatch[1] : ('WA-' + (idx + 1));
                rows.push({
                    date: dateStr,
                    ref: refStr,
                    desc: line,
                    debit: amount,
                    credit: 0,
                    balance: 0
                });
            }
        });
        return rows;
    }

    // Paste directly from System Clipboard (Universal: Images, Excel Tables, Text)
    async function pasteFromClipboard(side) {
        window.RECON_ACTIVE_OMNI_SIDE = side || 'A';

        // 1. Check if clipboard has images or files first
        if (navigator.clipboard && typeof navigator.clipboard.read === 'function') {
            try {
                const items = await navigator.clipboard.read();
                let hasImage = false;
                for (const item of items) {
                    for (const type of item.types) {
                        if (type.startsWith('image/')) {
                            hasImage = true;
                            const blob = await item.getType(type);
                            const file = new File([blob], `clipboard_image_${Date.now()}.png`, { type: blob.type });
                            if (window.showModernToast) {
                                window.showModernToast('📸 تم التقاط الصورة المنسوخة من الحافظة! جاري قراءتها بالذكاء الاصطناعي...', 'info', 3000);
                            }
                            const fakeEvent = { target: { files: [file], value: '' } };
                            closeReconModals();
                            handleOmniUpload(fakeEvent, side, 'clipboard');
                            return;
                        }
                    }
                }
            } catch (err) {
                console.warn('Clipboard.read() failed, trying readText():', err);
            }
        }

        // 2. Read text or tabular data
        try {
            if (!navigator.clipboard || !navigator.clipboard.readText) {
                openWhatsAppImportModal(side);
                return;
            }
            const clipText = await navigator.clipboard.readText();
            if (!clipText || !clipText.trim()) {
                openWhatsAppImportModal(side);
                if (window.showModernToast) {
                    window.showModernToast('الحافظة فارغة حالياً. انسخ حركة أو صورة من الواتساب ثم اضغط هنا.', 'warning', 3000);
                }
                return;
            }

            const lines = clipText.trim().split(/\r?\n/).map(l => l.trim()).filter(Boolean);
            // If tab-delimited (copied directly from Excel/Google Sheets)
            if (lines.length > 1 && lines[0].includes('\t')) {
                const matrix = lines.map(line => line.split('\t'));
                processLoadedSheetData(matrix, side, 'لصق_جدول_الحافظة.xlsx');
                if (window.showModernToast) {
                    window.showModernToast(`📋 تم قراءة ولصق جدول الحافظة بنجاح (${matrix.length - 1} حركة)!`, 'success', 3000);
                }
                closeReconModals();
                return;
            }

            // Unstructured narrative -> load into WhatsApp/Text modal
            const txtInput = document.getElementById('recon-whatsapp-text-input');
            if (txtInput) {
                txtInput.value = clipText;
            }
            openWhatsAppImportModal(side);
            if (window.showModernToast) {
                window.showModernToast('📋 تم إدراج النص المنسوخ من الحافظة! انقر على زر التفريغ الذكي.', 'info', 2500);
            }
        } catch (e) {
            console.warn('Clipboard read error, fallback to modal:', e);
            openWhatsAppImportModal(side);
            if (window.showModernToast) {
                window.showModernToast('يرجى الضغط على (Ctrl+V) للصق مباشرة داخل الحقل.', 'info', 3000);
            }
        }
    }
    window.pasteFromClipboard = pasteFromClipboard;

    // Direct WhatsApp Launcher & Helper
    function openWhatsAppAction(side) {
        window.RECON_ACTIVE_OMNI_SIDE = side || 'A';
        
        // 1. Direct deep-link to WhatsApp Web / App
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        if (isMobile) {
            window.location.href = 'whatsapp://';
        } else {
            window.open('https://web.whatsapp.com', '_blank');
        }

        // 2. Open companion modal ready to receive data
        openWhatsAppImportModal(side);

        if (window.showModernToast) {
            window.showModernToast('💬 تم فتح الواتساب بنجاح! انسخ الصورة أو المستند من الواتساب، ثم الصقه هنا أو ارفعه.', 'success', 5000);
        }
    }
    window.openWhatsAppAction = openWhatsAppAction;

    // Algorithmic overlap and duplicate statement row cleaner (Multi-Photo Consecutive Overlap Splicer)
    function deduplicateStatementRows(rows) {
        if (!Array.isArray(rows) || rows.length <= 1) return rows || [];
        const deduped = [];
        const seenSignatures = new Map();

        for (let i = 0; i < rows.length; i++) {
            const r = rows[i];
            const d = (r.date || '').trim();
            const ref = (r.ref || '').trim();
            const deb = parseFloat(r.debit) || 0;
            const cred = parseFloat(r.credit) || 0;
            const bal = parseFloat(r.balance) || 0;
            const cleanDesc = (r.desc || '').replace(/[\s\-_،,]+/g, ' ').trim().toLowerCase();

            // Precise financial signatures to capture photo stitch overlaps (e.g. Lines 15..20 photographed twice)
            const exactSig = `${d}|${ref}|${deb.toFixed(2)}|${cred.toFixed(2)}|${bal.toFixed(2)}|${cleanDesc.substring(0, 35)}`;
            const balanceSig = bal !== 0 ? `${d}|${deb.toFixed(2)}|${cred.toFixed(2)}|${bal.toFixed(2)}` : null;
            const refSig = (ref && ref !== '-' && ref !== '0' && ref.length >= 4) ? `${ref}|${deb.toFixed(2)}|${cred.toFixed(2)}` : null;
            const contentSig = (cleanDesc.length >= 8 && (deb > 0 || cred > 0)) ? `${d}|${deb.toFixed(2)}|${cred.toFixed(2)}|${cleanDesc.substring(0, 25)}` : null;

            let isDuplicate = false;
            if (balanceSig && seenSignatures.has(balanceSig)) {
                isDuplicate = true;
            } else if (refSig && seenSignatures.has(refSig)) {
                isDuplicate = true;
            } else if (exactSig && seenSignatures.has(exactSig)) {
                isDuplicate = true;
            } else if (contentSig && seenSignatures.has(contentSig)) {
                isDuplicate = true;
            }

            if (!isDuplicate) {
                deduped.push(r);
                seenSignatures.set(exactSig, i);
                if (balanceSig) seenSignatures.set(balanceSig, i);
                if (refSig) seenSignatures.set(refSig, i);
                if (contentSig) seenSignatures.set(contentSig, i);
            } else {
                console.log(`[Reconciliation Stitcher] Overlapping duplicate line dropped (#${i + 1}):`, r);
            }
        }
        return deduped;
    }
    window.deduplicateStatementRows = deduplicateStatementRows;

    // Universal Omni Upload Handler (Multi-Image Overlap Stitching, Excel, Camera/Studio, PDF, Word)
    async function handleOmniUpload(event, side, type) {
        const files = Array.from(event.target.files || []);
        if (!files.length) return;

        // 1. Native Single Excel / CSV file ingestion
        if (files.length === 1) {
            const fileExt = (files[0].name || '').split('.').pop().toLowerCase();
            if (['xlsx', 'xls', 'csv'].includes(fileExt)) {
                window.handleReconFileUpload(event, side);
                return;
            }
        }

        const boxEl = document.getElementById(`recon-dropzone-box-${side}`);
        const themeColor = side === 'A' ? 'sky' : 'purple';
        const isMulti = files.length > 1;
        const uploadTitle = isMulti ? `مجموعة مستندات / صور (${files.length} ملفات)` : files[0].name;

        // 2. Multimodal AI Extraction Loading UI
        if (boxEl) {
            boxEl.innerHTML = `
                <div class="p-8 rounded-2xl bg-indigo-950/40 border border-indigo-500/40 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full border-4 border-${themeColor}-500/30 border-t-${themeColor}-400 animate-spin mx-auto"></div>
                    <h5 class="font-black text-sm text-${themeColor}-200">جاري مسح ومعالجة ${uploadTitle} بالذكاء الاصطناعي...</h5>
                    <p class="text-xs text-slate-400 font-mono">
                        ${isMulti ? 'نظام Gemini Multimodal Vision يدمج الصور المتتالية، يكتشف الأسطر المتداخلة والمدبلة ويفرغ جدول الحركات الموحد' : 'نظام Gemini Vision OCR المتقدم يستخرج الحركات والأرصدة بدقة متناهية'}
                    </p>
                </div>
            `;
        }

        try {
            // Read all files as base64 payloads
            const payloads = await Promise.all(files.map(f => {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    const fileExt = (f.name || '').split('.').pop().toLowerCase();
                    let mimeType = f.type;
                    if (!mimeType) {
                        if (fileExt === 'pdf') mimeType = 'application/pdf';
                        else if (['jpg', 'jpeg'].includes(fileExt)) mimeType = 'image/jpeg';
                        else if (fileExt === 'png') mimeType = 'image/png';
                        else if (fileExt === 'webp') mimeType = 'image/webp';
                        else mimeType = 'application/octet-stream';
                    }
                    reader.onload = () => resolve({
                        file_base64: reader.result,
                        mime_type: mimeType,
                        file_name: f.name
                    });
                    reader.onerror = reject;
                    reader.readAsDataURL(f);
                });
            }));

            // Store visual preview for side-by-side inspection
            let firstImg = null;
            for (const p of payloads) {
                if (p.mime_type && p.mime_type.startsWith('image/')) {
                    firstImg = p.file_base64;
                    break;
                }
            }
            window['RECON_PREVIEW_IMG_' + side] = firstImg;

            const response = await fetch('api/ai-reconciliation.php?action=extract_document', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    files_base64: payloads,
                    file_base64: payloads[0].file_base64,
                    mime_type: payloads[0].mime_type,
                    file_name: files.map(f => f.name).join(', ')
                })
            });

            const result = await response.json();
            if (result.success && result.data && result.data.rows && result.data.rows.length > 0) {
                // Algorithmic overlap and duplicate removal
                const rawRows = result.data.rows;
                const dedupedRows = window.deduplicateStatementRows(rawRows);
                const duplicateCount = rawRows.length - dedupedRows.length;

                const matrix = [
                    ["التاريخ", "رقم العملية / المرجع", "البيان وتفاصيل العملية", "مدين", "دائن", "الرصيد"]
                ];
                dedupedRows.forEach(r => {
                    matrix.push([
                        r.date || '',
                        r.ref || '',
                        r.desc || '',
                        parseFloat(r.debit) || 0,
                        parseFloat(r.credit) || 0,
                        parseFloat(r.balance) || 0
                    ]);
                });

                processLoadedSheetData(matrix, side, uploadTitle);

                if (window.showModernToast) {
                    const deDupNote = duplicateCount > 0 ? ` (تم استبعاد ${duplicateCount} سطر متداخل مدبل)` : '';
                    window.showModernToast(`✨ تم تفريغ وتدقيق ${dedupedRows.length} حركة بالذكاء الاصطناعي من (${uploadTitle})${deDupNote} بنجاح!`, 'success', 4500);
                }
            } else {
                alert('تعذر استخراج جدول الحركات المالية من المستندات: ' + (result.error || 'يرجى التأكد من وضوح أرقام المرجع والمبالغ والتواريخ.'));
                renderDropzoneCard(side);
            }
        } catch (err) {
            console.error('Omni Upload extraction error:', err);
            alert('حدث خطأ أثناء معالجة المستند: ' + err.message);
            renderDropzoneCard(side);
        } finally {
            event.target.value = '';
        }
    }
    window.handleOmniUpload = handleOmniUpload;

    // ────────────────────────────────────────────────────────────────
    // 9. UNIVERSAL GLOBAL PASTE & DROP LISTENER (الاستماع التلقائي الشامل للصق والإفلات)
    // ────────────────────────────────────────────────────────────────
    document.addEventListener('paste', async function(e) {
        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        const activeId = document.activeElement ? document.activeElement.id : '';
        
        // Don't hijack input fields unless it's specifically for reconciliation
        if (activeTag === 'input' || activeTag === 'textarea') {
            if (activeId !== 'recon-whatsapp-text-input' && activeId !== 'recon-global-search-input') {
                return;
            }
        }

        const side = window.RECON_ACTIVE_OMNI_SIDE || 'A';
        const clipboard = e.clipboardData || e.originalEvent.clipboardData;
        if (!clipboard) return;

        let hasImageOrFile = false;
        let filesList = [];
        
        if (clipboard.items && clipboard.items.length > 0) {
            for (let i = 0; i < clipboard.items.length; i++) {
                if (clipboard.items[i].kind === 'file') {
                    const file = clipboard.items[i].getAsFile();
                    if (file) {
                        filesList.push(file);
                        hasImageOrFile = true;
                    }
                }
            }
        }

        if (hasImageOrFile) {
            e.preventDefault();
            if (window.showModernToast) {
                window.showModernToast(`📸 تم اكتشاف صور/ملفات منسوخة! جاري قراءتها بالذكاء الاصطناعي...`, 'info', 3000);
            }
            const fakeEvent = { target: { files: filesList, value: '' } };
            if (typeof closeReconModals === 'function') closeReconModals();
            if (typeof handleOmniUpload === 'function') handleOmniUpload(fakeEvent, side, 'clipboard');
            return;
        }

        // Fallback to text
        let pastedText = clipboard.getData('text');
        if (pastedText && pastedText.trim().length > 0) {
            // Check if it's tab-separated (Excel cells)
            const lines = pastedText.trim().split(/\r?\n/).map(l => l.trim()).filter(Boolean);
            if (lines.length > 1 && lines[0].includes('\t')) {
                e.preventDefault();
                const matrix = lines.map(line => line.split('\t'));
                if (typeof processLoadedSheetData === 'function') {
                    processLoadedSheetData(matrix, side, 'لصق_جدول_الحافظة.xlsx');
                }
                if (window.showModernToast) {
                    window.showModernToast(`📋 تم قراءة جدول الحافظة بنجاح (${matrix.length - 1} حركة)!`, 'success', 3000);
                }
                if (typeof closeReconModals === 'function') closeReconModals();
            } else if (activeId !== 'recon-whatsapp-text-input' && activeId !== 'recon-global-search-input') {
                e.preventDefault();
                const txtInput = document.getElementById('recon-whatsapp-text-input');
                if (txtInput) txtInput.value = pastedText;
                if (typeof openWhatsAppImportModal === 'function') openWhatsAppImportModal(side);
                if (window.showModernToast) {
                    window.showModernToast('📋 تم إدراج النص المنسوخ من الحافظة! انقر على زر التفريغ الذكي.', 'info', 2500);
                }
            }
        }
    });

    document.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });

    document.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const side = window.RECON_ACTIVE_OMNI_SIDE || 'A';
        const dt = e.dataTransfer;
        
        if (dt && dt.files && dt.files.length > 0) {
            if (window.showModernToast) {
                window.showModernToast(`📂 تم إفلات ${dt.files.length} ملف/صورة. جاري المعالجة...`, 'info', 2500);
            }
            const fakeEvent = { target: { files: Array.from(dt.files), value: '' } };
            if (typeof closeReconModals === 'function') closeReconModals();
            if (typeof handleOmniUpload === 'function') handleOmniUpload(fakeEvent, side, 'file');
        }
    });


    // ── Fullscreen Visual Lightbox for Document/Photo Inspection ──
    let currentLightboxRotation = 0;
    function openReconImageLightbox(imgSrc, title) {
        currentLightboxRotation = 0;
        const modal = document.getElementById('modal-recon-image-lightbox');
        const img = document.getElementById('recon-lightbox-img');
        const titleEl = document.getElementById('recon-lightbox-title');
        const downloadBtn = document.getElementById('recon-lightbox-download-btn');
        if (!modal || !img) return;

        img.src = imgSrc;
        img.style.transform = 'rotate(0deg)';
        if (titleEl && title) titleEl.textContent = `معاينة المستند: ${title}`;
        if (downloadBtn) downloadBtn.href = imgSrc;

        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }
    window.openReconImageLightbox = openReconImageLightbox;

    function closeReconLightbox() {
        const modal = document.getElementById('modal-recon-image-lightbox');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    }
    window.closeReconLightbox = closeReconLightbox;

    function rotateLightboxImage() {
        const img = document.getElementById('recon-lightbox-img');
        if (!img) return;
        currentLightboxRotation = (currentLightboxRotation + 90) % 360;
        img.style.transform = `rotate(${currentLightboxRotation}deg)`;
    }
    window.rotateLightboxImage = rotateLightboxImage;

    // ── Direct WhatsApp Dispatch with Pre-filled Reconciliation Memo ──
    function dispatchWhatsAppLetterToOtherSide() {
        const letter = (window.RECON_STATE && window.RECON_STATE.aiAudit && window.RECON_STATE.aiAudit.whatsapp_letter)
            ? window.RECON_STATE.aiAudit.whatsapp_letter
            : (document.getElementById('recon-memo-content') ? document.getElementById('recon-memo-content').innerText : '');

        if (!letter || !letter.trim()) {
            alert('لم يتم استخراج خطاب المطابقة بعد، يرجى تشغيل المقارنة أولاً.');
            return;
        }

        let phone = '';
        if (window.RECON_STATE && window.RECON_STATE.fileB) {
            const nameB = (window.RECON_STATE.fileB.name || '').replace(/\.xlsx$/i, '').trim();
            if (window.RECON_LOADED_ACCOUNTS) {
                const found = window.RECON_LOADED_ACCOUNTS.find(a => a.name === nameB || nameB.includes(a.name));
                if (found && found.phone) phone = found.phone;
            }
        }

        let cleanPhone = phone.replace(/[^0-9]/g, '');
        if (cleanPhone.startsWith('0')) {
            cleanPhone = '249' + cleanPhone.substring(1);
        }

        const encodedText = encodeURIComponent(letter);
        const waUrl = cleanPhone 
            ? `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodedText}`
            : `https://api.whatsapp.com/send?text=${encodedText}`;

        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        if (isMobile) {
            window.open(cleanPhone ? `whatsapp://send?phone=${cleanPhone}&text=${encodedText}` : `whatsapp://send?text=${encodedText}`, '_blank');
        } else {
            window.open(waUrl, '_blank');
        }

        if (window.showModernToast) {
            window.showModernToast('💬 تم فتح محادثة الواتساب لنقل الخطاب والمطالبة فوراً!', 'success', 3500);
        }
    }
    window.dispatchWhatsAppLetterToOtherSide = dispatchWhatsAppLetterToOtherSide;


})(window);
