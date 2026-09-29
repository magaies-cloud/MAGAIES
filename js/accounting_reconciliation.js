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

    // Initialize suite if opened
    window.initReconciliationSuite = function() {
        console.log('Reconciliation Suite Initialized');
    };

    // ────────────────────────────────────────────────────────────────
    // 1. SAMPLE DATA GENERATOR (نموذج تجريبي فوري للاختبار)
    // ────────────────────────────────────────────────────────────────
    window.loadReconciliationSampleData = function() {
        const sampleA = [
            ["التاريخ", "رقم التحويلة / السند", "البيان وتفاصيل العملية", "مدين (لنا)", "دائن (علينا)", "الرصيد", "سعر الدولار", "المبلغ بالدولار"],
            ["2026-06-01", "INV-101", "رصيد افتتاحي مرحل من الدورة السابقة", 0, 15000000, -15000000, 6000, -2500],
            ["2026-06-05", "TRX-88014567", "تحويل بنكك توريد بضاعة دفعة أولى", 6000000, 0, -9000000, 6000, 1000],
            ["2026-06-10", "INV-102", "فاتورة شراء حديد تسليح 12 ملم (10 طن)", 0, 12000000, -21000000, 6000, -2000],
            ["2026-06-12", "CHQ-7721", "سداد شيك مصرفي رقم 7721 لصالح المصنع", 5000000, 0, -16000000, 6000, 833.33],
            ["2026-06-15", "TRX-994411", "سداد نقدي عبر بنك أمدرمان الوطني", 2000000, 0, -14000000, 6000, 333.33],
            ["2026-06-18", "TRX-442188", "تحويل بنكك سداد دفعة توريد إسمنت", 3500000, 0, -10500000, 6000, 583.33],
            ["2026-06-20", "INV-103", "فاتورة استلام صاج وزنك أمريكي", 0, 8000000, -18500000, 6050, -1322.31],
            ["2026-06-22", "TRX-661200", "تحويل بنك فيصل الإسلامي - فوري", 4000000, 0, -14500000, 6000, 666.67]
        ];

        // Sample B: Factory Statement (Contains realistic discrepancies)
        const sampleB = [
            ["تاريخ الحركة", "رقم العملية المرجعي", "شرح الحركة في المصنع", "المقبوضات (دائن)", "المدفوعات والمبيعات (مدين)", "رصيد العميل", "تعادل الدولار"],
            ["2026-06-01", "OP-BAL", "الرصيد الافتتاحي المقيد لدى المصنع", 0, 15000000, 15000000, 2500],
            ["2026-06-06", "BOK-009988014567", "إشعار تحويل بنكك الوارد من العميل (مطابق بآخر أرقام)", 6000000, 0, 9000000, 1000],
            ["2026-06-10", "INV-102", "قيمة حديد تسليح مباني طلبية رقم 102", 0, 12000000, 21000000, 2000],
            ["2026-06-13", "7721", "تحصيل شيك مصرفي رقم 7721", 5000000, 0, 16000000, 833.33],
            ["2026-06-15", "TRX-994411", "سداد نقدي مقيد بالخطأ بـ 2.5 مليون بدلاً من 2 مليون", 2500000, 0, 13500000, 416.67], // DIFFERENCE: 500K variance
            // Note: TRX-442188 (3,500,000) was NOT entered by the factory! (MISSING IN FACTORY)
            ["2026-06-20", "INV-103", "فاتورة توريد صاج وزنك أمريكي", 0, 8000000, 21500000, 1333.33],
            ["2026-06-21", "FEE-901", "رسوم نولون وتعتيق وشحن لم يقيدها العميل", 0, 250000, 21750000, 41.67], // MISSING IN CLIENT
            ["2026-06-22", "TRX-661200", "إشعار تحويل بنك فيصل فوري", 4000000, 0, 17750000, 666.67]
        ];

        processLoadedSheetData(sampleA, 'A', 'حساباتي_الخاصة_يونيو_2026.xlsx');
        processLoadedSheetData(sampleB, 'B', 'كشف_حساب_المصنع_يونيو_2026.xlsx');

        if (window.showModernToast) {
            window.showModernToast('✨ تم تحميل بيانات النموذج التجريبي بنجاح! اضغط على زر تشغيل التطابق لمشاهدة التحليل.', 'success', 3500);
        }
    };

    // ────────────────────────────────────────────────────────────────
    // 2. EXCEL & CSV FILE INGESTION (SheetJS)
    // ────────────────────────────────────────────────────────────────
    window.handleReconFileUpload = function(event, side) {
        const file = event.target.files?.[0];
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
    };

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
        target.name = fileName || (side === 'A' ? 'كشف حساباتي' : 'كشف حساب المصنع');
        target.rawRows = dataRows;
        target.headers = rawHeaders;
        target.colMap = colMap;
        target.mappedRows = mappedRows;
        target.summary = summary;

        renderDropzoneCard(side);
        renderColumnMappingCard(side);
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
    window.runReconciliationEngine = async function() {
        const stateA = window.RECON_STATE.fileA;
        const stateB = window.RECON_STATE.fileB;

        if (!stateA.mappedRows || stateA.mappedRows.length === 0 || !stateB.mappedRows || stateB.mappedRows.length === 0) {
            alert('يرجى تحميل ملفي الإكسيل أولاً (حساباتي وكشف المصنع) أو الضغط على "تحميل نموذج تجريبي".');
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
                        explanation: `رقم التحويلة متطابق (${rowA.ref} مقابل ${bestMatchB.ref}) والمبلغ متطابق، ولكن تاريخ تسجيلك (${rowA.date}) يختلف عن تاريخ المصنع (${bestMatchB.date}) بفارق ${Math.abs(dayDiffFound)} يوماً.`
                    });
                }
            }
        }

        // ── Level 3: Reference Match but Amount has a Variance! ──
        // (e.g. Client entered 2,000,000 but factory entered 2,500,000 for the same transfer number)
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
                varianceMatches.push({
                    rowA: rowA,
                    rowB: bestMatchB,
                    type: 'amount_variance',
                    title: 'اختلاف في مبلغ التحويلة لنفس الرقم المرجعي',
                    diff: diff,
                    explanation: `نفس رقم العملية (${rowA.ref} مقابل ${bestMatchB.ref})، لكن مسجل في كشفك بمبلغ ${formatMoney(amtA)} ومسجل في كشف المصنع بمبلغ ${formatMoney(amtB)}، بفارق ${formatMoney(Math.abs(diff))}.`
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

        // ── Level 5: Collect Unmatched Records ──
        stateA.mappedRows.forEach(r => {
            if (!r.matched) unmatchedA.push(r);
        });
        stateB.mappedRows.forEach(r => {
            if (!r.matched) unmatchedB.push(r);
        });

        const balA = stateA.summary.balance;
        const balB = stateB.summary.balance;
        const netVariance = balA - balB;
        const totalRows = stateA.mappedRows.length + stateB.mappedRows.length;
        const matchRate = totalRows > 0 ? Math.round(((exactMatches.length * 2) / totalRows) * 100) : 0;

        window.RECON_STATE.results = {
            exactMatches,
            varianceMatches,
            unmatchedA,
            unmatchedB,
            netVariance,
            matchRate
        };

        // Render UI Results
        renderReconciliationResults();

        // Automatically trigger AI Forensic Audit
        triggerAIForensicAudit();
    };

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

    function getDayDifference(d1, d2) {
        if (!d1 || !d2) return 999;
        const date1 = new Date(d1);
        const date2 = new Date(d2);
        if (isNaN(date1.getTime()) || isNaN(date2.getTime())) return 999;
        const diffTime = date1.getTime() - date2.getTime();
        return Math.round(diffTime / (1000 * 60 * 60 * 24));
    }

    function formatMoney(n) {
        const val = parseFloat(n) || 0;
        return val.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    // ────────────────────────────────────────────────────────────────
    // 5. CALL GEMINI AI FORENSIC AUDIT (api/ai-reconciliation.php)
    // ────────────────────────────────────────────────────────────────
    async function triggerAIForensicAudit() {
        const stateA = window.RECON_STATE.fileA;
        const stateB = window.RECON_STATE.fileB;
        const res = window.RECON_STATE.results;

        const aiStatusEl = document.getElementById('recon-ai-status');
        const aiCardEl = document.getElementById('recon-ai-card');
        const aiContentEl = document.getElementById('recon-ai-content');

        if (aiStatusEl) {
            aiStatusEl.classList.remove('hidden');
            aiStatusEl.innerHTML = `
                <div class="flex items-center gap-3 text-indigo-400 font-bold text-sm">
                    <i class="fa-solid fa-wand-magic-sparkles fa-spin text-lg"></i>
                    <span>جاري تشغيل خبير التدقيق الجنائي الذكي (Gemini AI) لتفكيك الفرق واستخراج أسباب الخلل...</span>
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
            } else {
                if (aiStatusEl) {
                    aiStatusEl.innerHTML = `<span class="text-amber-400 font-bold">⚠️ تم الاعتماد على المحرك الحسابي الداخلي لاحتساب الفروقات.</span>`;
                }
            }
        } catch (err) {
            console.error('AI reconciliation call error:', err);
            if (aiStatusEl) {
                aiStatusEl.innerHTML = `<span class="text-amber-400 font-bold">⚠️ المحرك الخوارزمي يعمل محلياً بنجاح.</span>`;
            }
        }
    }

    // ────────────────────────────────────────────────────────────────
    // 6. UI RENDERING & TAB MANAGERS
    // ────────────────────────────────────────────────────────────────
    // ────────────────────────────────────────────────────────────────
    // 6. UI RENDERING & TAB MANAGERS
    // ────────────────────────────────────────────────────────────────
    function renderDropzoneCard(side) {
        const isA = (side === 'A');
        const target = isA ? window.RECON_STATE.fileA : window.RECON_STATE.fileB;
        const boxEl = document.getElementById(`recon-dropzone-box-${side}`);
        if (!boxEl) return;

        const themeColor = isA ? 'sky' : 'purple';

        boxEl.innerHTML = `
            <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-12 h-12 rounded-2xl bg-${themeColor}-500/15 border border-${themeColor}-500/40 flex items-center justify-center text-${themeColor}-400 text-xl shadow-lg">
                            <i class="fa-solid fa-file-excel"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-black text-sm text-slate-100 font-mono tracking-tight">${target.name}</h4>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 font-bold text-[10px] border border-emerald-500/30">✓ تم التدقيق</span>
                            </div>
                            <span class="text-xs text-slate-400 font-bold mt-0.5 block">تم استخراج <strong>${target.mappedRows.length}</strong> حركة مالية بنجاح</span>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('recon-file-input-${side}').click()" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 hover:text-white border border-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-arrows-rotate text-[11px]"></i>
                        استبدال الملف
                    </button>
                </div>
                
                <div class="grid grid-cols-3 gap-2.5 pt-3 border-t border-slate-800 text-center text-xs">
                    <div class="bg-slate-900/80 p-2.5 rounded-xl border border-rose-500/20">
                        <span class="text-slate-400 block font-bold text-[11px] mb-0.5">إجمالي المدين:</span>
                        <strong class="text-rose-400 font-mono text-sm block">${formatMoney(target.summary.debit)}</strong>
                    </div>
                    <div class="bg-slate-900/80 p-2.5 rounded-xl border border-emerald-500/20">
                        <span class="text-slate-400 block font-bold text-[11px] mb-0.5">إجمالي الدائن:</span>
                        <strong class="text-emerald-400 font-mono text-sm block">${formatMoney(target.summary.credit)}</strong>
                    </div>
                    <div class="bg-slate-900/80 p-2.5 rounded-xl border border-${themeColor}-500/20">
                        <span class="text-slate-400 block font-bold text-[11px] mb-0.5">رصيد الإغلاق:</span>
                        <strong class="text-${themeColor}-300 font-mono text-sm block">${formatMoney(target.summary.balance)}</strong>
                    </div>
                </div>
            </div>
        `;
    }

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

        // Update KPI Cards
        document.getElementById('recon-kpi-bal-a').textContent = formatMoney(stateA.summary.balance);
        document.getElementById('recon-kpi-bal-b').textContent = formatMoney(stateB.summary.balance);
        
        const diffEl = document.getElementById('recon-kpi-net-diff');
        const diffHint = document.getElementById('recon-kpi-net-diff-hint');
        diffEl.textContent = formatMoney(Math.abs(res.netVariance));
        if (Math.abs(res.netVariance) > 1) {
            diffEl.className = 'text-2xl sm:text-3xl font-black font-mono text-rose-400 block tracking-tight';
            if (diffHint) {
                diffHint.innerHTML = '<span class="text-rose-400 font-bold">⚠️ يوجد فارق يجب حسمه وتسويته</span>';
            }
        } else {
            diffEl.className = 'text-2xl sm:text-3xl font-black font-mono text-emerald-400 block tracking-tight';
            if (diffHint) {
                diffHint.innerHTML = '<span class="text-emerald-400 font-bold">✓ تطابق تام ولا يوجد أي فرق</span>';
            }
        }

        document.getElementById('recon-kpi-match-rate').textContent = res.matchRate + '%';
        document.getElementById('recon-count-exact').textContent = res.exactMatches.length;
        document.getElementById('recon-count-variance').textContent = res.varianceMatches.length;
        document.getElementById('recon-count-unmatched-a').textContent = res.unmatchedA.length;
        document.getElementById('recon-count-unmatched-b').textContent = res.unmatchedB.length;

        // Render Tables for Sub-Tabs
        renderExactMatchesTable();
        renderVariancesTable();
        renderUnmatchedTables();

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
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="copyWhatsAppReconciliationLetter()" class="px-4 py-2 rounded-xl bg-emerald-500/20 border-2 border-emerald-500/50 hover:bg-emerald-500/30 text-emerald-300 text-xs font-black flex items-center gap-2 cursor-pointer transition shadow-md">
                            <i class="fa-brands fa-whatsapp text-base"></i>
                            نسخ خطاب الواتساب للمصنع
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
        const variances = window.RECON_STATE.results.varianceMatches;
        const tbody = document.getElementById('recon-table-variance-body');
        if (!tbody) return;

        if (variances.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-10 text-emerald-400 font-bold">✓ لا توجد عمليات فيها تباين أو اختلاف مبالغ! كل العمليات المتطابقة صحيحة.</td></tr>`;
            return;
        }

        tbody.innerHTML = variances.map((v, idx) => `
            <tr class="hover:bg-slate-800/50 border-b border-slate-800/60 text-xs bg-amber-500/5 transition">
                <td class="p-2.5 text-center font-bold text-slate-400">${idx + 1}</td>
                <td class="p-2.5 font-mono text-slate-300">${v.rowA.date} ↔ ${v.rowB.date}</td>
                <td class="p-2.5 font-mono font-black text-amber-400">${v.rowA.ref}</td>
                <td class="p-2.5 text-slate-200 leading-relaxed">${v.explanation}</td>
                <td class="p-2.5 text-center font-mono text-slate-200">${formatMoney(v.rowA.amount)}</td>
                <td class="p-2.5 text-center font-mono text-slate-200">${formatMoney(v.rowB.amount)}</td>
                <td class="p-2.5 text-center font-mono font-black text-rose-400">${formatMoney(Math.abs(v.diff))}</td>
            </tr>
        `).join('');
    }

    function renderUnmatchedTables() {
        const unA = window.RECON_STATE.results.unmatchedA;
        const unB = window.RECON_STATE.results.unmatchedB;

        const tbodyA = document.getElementById('recon-table-unmatched-a-body');
        const tbodyB = document.getElementById('recon-table-unmatched-b-body');

        if (tbodyA) {
            if (unA.length === 0) {
                tbodyA.innerHTML = `<tr><td colspan="5" class="text-center py-8 text-emerald-400 font-bold">✓ جميع حركاتك مسجلة ومطابقة لدى المصنع!</td></tr>`;
            } else {
                tbodyA.innerHTML = unA.map((r, idx) => `
                    <tr class="hover:bg-slate-800/50 border-b border-slate-800/60 text-xs transition">
                        <td class="p-2.5 text-center text-slate-400 font-bold">${idx + 1}</td>
                        <td class="p-2.5 font-mono text-slate-300">${r.date || '-'}</td>
                        <td class="p-2.5 font-mono font-black text-amber-400">${r.ref}</td>
                        <td class="p-2.5 text-slate-300">${r.desc}</td>
                        <td class="p-2.5 text-center font-mono font-black text-rose-400">${formatMoney(r.amount)}</td>
                    </tr>
                `).join('');
            }
        }

        if (tbodyB) {
            if (unB.length === 0) {
                tbodyB.innerHTML = `<tr><td colspan="5" class="text-center py-8 text-emerald-400 font-bold">✓ لا توجد حركات للمصنع مفقودة في كشفك!</td></tr>`;
            } else {
                tbodyB.innerHTML = unB.map((r, idx) => `
                    <tr class="hover:bg-slate-800/50 border-b border-slate-800/60 text-xs transition">
                        <td class="p-2.5 text-center text-slate-400 font-bold">${idx + 1}</td>
                        <td class="p-2.5 font-mono text-slate-300">${r.date || '-'}</td>
                        <td class="p-2.5 font-mono font-black text-sky-400">${r.ref}</td>
                        <td class="p-2.5 text-slate-300">${r.desc}</td>
                        <td class="p-2.5 text-center font-mono font-black text-purple-300">${formatMoney(r.amount)}</td>
                    </tr>
                `).join('');
            }
        }
    }

    // Tab Switching inside Reconciliation Results
    window.switchReconTab = function(tabId) {
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
    };

    // Reset Suite State and Clear UI
    window.resetReconciliationSuite = function() {
        window.RECON_STATE.fileA = { name: '', rawRows: [], headers: [], colMap: {}, mappedRows: [], summary: { debit: 0, credit: 0, balance: 0, count: 0 } };
        window.RECON_STATE.fileB = { name: '', rawRows: [], headers: [], colMap: {}, mappedRows: [], summary: { debit: 0, credit: 0, balance: 0, count: 0 } };
        window.RECON_STATE.results = { exactMatches: [], varianceMatches: [], unmatchedA: [], unmatchedB: [], netVariance: 0, matchRate: 0 };
        window.RECON_STATE.aiAudit = null;

        ['A', 'B'].forEach(side => {
            const box = document.getElementById(`recon-dropzone-box-${side}`);
            if (box) {
                const isA = side === 'A';
                const color = isA ? 'sky' : 'purple';
                const title = isA ? 'اسحب وأفلت ملف إكسيل حساباتك هنا أو انقر للاختيار' : 'اسحب وأفلت ملف إكسيل المصنع هنا أو انقر للاختيار';
                box.innerHTML = `
                    <label for="recon-file-input-${side}" class="recon-upload-area flex flex-col items-center justify-center group">
                        <div class="w-14 h-14 rounded-2xl bg-${color}-500/10 border border-${color}-500/30 flex items-center justify-center text-${color}-400 text-2xl mb-3 group-hover:scale-110 transition duration-300 shadow-lg">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <span class="font-extrabold text-sm text-slate-100 group-hover:text-${color}-300 transition">${title}</span>
                        <span class="text-xs text-slate-400 mt-1">يدعم ملفات (.xlsx, .xls, .csv)</span>
                    </label>
                `;
            }
            const badge = document.getElementById(`recon-map-badges-${side}`);
            if (badge) badge.innerHTML = '';
            const input = document.getElementById(`recon-file-input-${side}`);
            if (input) input.value = '';
        });

        const resCont = document.getElementById('recon-results-container');
        if (resCont) resCont.classList.add('hidden');
        const aiStat = document.getElementById('recon-ai-status');
        if (aiStat) aiStat.classList.add('hidden');
    };

    // Copy Official Reconciliation Letter for WhatsApp
    window.copyWhatsAppReconciliationLetter = function() {
        const letter = window.RECON_STATE.aiAudit?.whatsapp_letter;
        if (!letter) {
            alert('لم يتم توليد الرسالة بعد، يرجى تشغيل المقارنة أولاً.');
            return;
        }

        navigator.clipboard.writeText(letter).then(() => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'تم النسخ بنجاح!',
                    text: 'تم نسخ مسودة خطاب التسوية لمشاركتها عبر واتساب المصنع.',
                    timer: 2500,
                    showConfirmButton: false,
                    background: '#0f172a',
                    color: '#ffffff'
                });
            } else {
                alert('📋 تم نسخ مسودة خطاب التسوية لمشاركتها عبر واتساب المصنع!');
            }
        }).catch(() => {
            alert('يرجى نسخ النص يدوياً.');
        });
    };

    // ────────────────────────────────────────────────────────────────
    // 7. EXCEL EXPORT (تصدير تقرير التسوية والمطابقة لملف إكسيل)
    // ────────────────────────────────────────────────────────────────
    window.exportReconciliationExcel = function() {
        if (typeof XLSX === 'undefined') {
            alert('مكتبة الإكسيل غير محملة.');
            return;
        }

        const wb = XLSX.utils.book_new();
        const res = window.RECON_STATE.results;
        const stateA = window.RECON_STATE.fileA;
        const stateB = window.RECON_STATE.fileB;

        // 1. Summary Sheet
        const summaryData = [
            ["تقرير التطابق والتسوية المحاسبية الذكية - نظام مقاييس MAGAIEES 2026"],
            ["تاريخ إعداد التقرير", new Date().toLocaleString('ar-SA')],
            [""],
            ["الطرف الأول (حساباتي)", stateA.name, "رصيد الإغلاق", stateA.summary.balance],
            ["الطرف الثاني (المصنع)", stateB.name, "رصيد الإغلاق", stateB.summary.balance],
            ["صافي الفرق بين الطرفين", Math.abs(res.netVariance), "الحالة", Math.abs(res.netVariance) > 1 ? "يوجد فرق" : "متطابق تماماً"],
            ["نسبة التطابق الإجمالية", res.matchRate + "%"],
            ["عدد العمليات المتطابقة تماماً", res.exactMatches.length],
            ["عدد العمليات المتطابقة مع فارق", res.varianceMatches.length],
            ["عدد الحركات غير المقيدة لدى المصنع", res.unmatchedA.length],
            ["عدد الحركات المقيدة لدى المصنع وغير مسجلة عندي", res.unmatchedB.length]
        ];
        const wsSummary = XLSX.utils.aoa_to_sheet(summaryData);
        XLSX.utils.book_append_sheet(wb, wsSummary, "الملخص التنفيذي");

        // 2. Unmatched Sheet
        const unData = [
            ["حركات مسجلة عندي ومفقودة في كشف المصنع"],
            ["م", "التاريخ", "المرجع", "البيان", "المبلغ"]
        ];
        res.unmatchedA.forEach((r, i) => {
            unData.push([i + 1, r.date, r.ref, r.desc, r.amount]);
        });
        unData.push([""]);
        unData.push(["حركات مقيدة لدى المصنع وغير مسجلة عندي"]);
        unData.push(["م", "التاريخ", "المرجع", "البيان", "المبلغ"]);
        res.unmatchedB.forEach((r, i) => {
            unData.push([i + 1, r.date, r.ref, r.desc, r.amount]);
        });
        const wsUn = XLSX.utils.aoa_to_sheet(unData);
        XLSX.utils.book_append_sheet(wb, wsUn, "الحركات المعلقة والمفقودة");

        // 3. Exact Matches Sheet
        const matchData = [
            ["م", "تاريخ حركتي", "تاريخ المصنع", "رقم المرجع الخاص بي", "رقم مرجع المصنع", "البيان", "المبلغ المتطابق", "طبيعة التطابق"]
        ];
        res.exactMatches.forEach((m, i) => {
            matchData.push([i + 1, m.rowA.date, m.rowB.date, m.rowA.ref, m.rowB.ref, m.rowA.desc, m.rowA.amount, m.badge]);
        });
        const wsMatch = XLSX.utils.aoa_to_sheet(matchData);
        XLSX.utils.book_append_sheet(wb, wsMatch, "العمليات المتطابقة");

        // Save file
        XLSX.writeFile(wb, `تقرير_التطابق_المحاسبي_${new Date().toISOString().split('T')[0]}.xlsx`);
    };

})(window);
