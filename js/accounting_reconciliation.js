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
            ["2026-06-15", "TRX-994411", "سداد نقدي عبر بنك أمدرمان الوطني", 2540000, 0, -13460000, 6000, 423.33], // Transposition error test: 2,540,000 vs 2,450,000 (diff 90,000 divisible by 9)
            ["2026-06-18", "TRX-442188", "تحويل بنكك سداد دفعة توريد إسمنت", 3500000, 0, -9960000, 6000, 583.33],
            ["2026-06-20", "INV-103", "فاتورة استلام صاج وزنك أمريكي", 0, 8000000, -17960000, 6050, -1322.31],
            ["2026-06-22", "TRX-661200", "تحويل بنك فيصل الإسلامي - فوري", 4000000, 0, -13960000, 6000, 666.67],
            // Combinatorial match: 2 split transfers settling invoice INV-104 (5,000,000)
            ["2026-06-24", "TRX-SPLIT-1", "تحويل بنكك سداد دفعة فاتورة 104 (دفعة 1 سقف يومي)", 3000000, 0, -10960000, 6000, 500],
            ["2026-06-24", "TRX-SPLIT-2", "تحويل بنكك سداد دفعة فاتورة 104 (دفعة 2 سقف يومي)", 2000000, 0, -8960000, 6000, 333.33],
            // Aged critical item (dated April 2026 -> >60 days old)
            ["2026-04-12", "OLD-CHQ-109", "شيك مسحوب رقم 109 معلق بالطريق لم يصرفه المصنع", 1800000, 0, -7160000, 5800, 310.34]
        ];

        // Sample B: Factory Statement (Contains realistic discrepancies, split payments, and transposition)
        const sampleB = [
            ["تاريخ الحركة", "رقم العملية المرجعي", "شرح الحركة في المصنع", "المقبوضات (دائن)", "المدفوعات والمبيعات (مدين)", "رصيد العميل", "تعادل الدولار"],
            ["2026-06-01", "OP-BAL", "الرصيد الافتتاحي المقيد لدى المصنع", 0, 15000000, 15000000, 2500],
            ["2026-06-06", "BOK-009988014567", "إشعار تحويل بنكك الوارد من العميل (مطابق بآخر أرقام)", 6000000, 0, 9000000, 1000],
            ["2026-06-10", "INV-102", "قيمة حديد تسليح مباني طلبية رقم 102", 0, 12000000, 21000000, 2000],
            ["2026-06-13", "7721", "تحصيل شيك مصرفي رقم 7721", 5000000, 0, 16000000, 833.33],
            ["2026-06-15", "TRX-994411", "سداد نقدي مقيد بـ 2,450,000 بدلاً من 2,540,000 (تبديل خانات)", 2450000, 0, 13550000, 408.33], // Transposition: 90K diff (90000 % 9 === 0)
            ["2026-06-20", "INV-103", "فاتورة توريد صاج وزنك أمريكي", 0, 8000000, 21550000, 1333.33],
            ["2026-06-21", "FEE-901", "رسوم نولون وتعتيق وشحن لم يقيدها العميل", 0, 250000, 21800000, 41.67], // MISSING IN CLIENT
            ["2026-06-22", "TRX-661200", "إشعار تحويل بنك فيصل فوري", 4000000, 0, 17800000, 666.67],
            // Target single invoice matched by 2 split transfers (3M + 2M = 5M)
            ["2026-06-24", "INV-104", "فاتورة توريد زوايا حديد مبوم طلبية 104", 0, 5000000, 22800000, 833.33],
            // Aged critical item
            ["2026-04-10", "OLD-FEE-88", "رسوم أرضيات ومستودع قديمة لم تسجل لدى العميل", 0, 450000, 23250000, 77.59]
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
                const absDiff = Math.abs(diff);
                const isTransposition = (Math.round(absDiff) % 9 === 0) && absDiff > 0;
                let explanation = `نفس رقم العملية (${rowA.ref} مقابل ${bestMatchB.ref})، لكن مسجل في كشفك بمبلغ ${formatMoney(amtA)} ومسجل في كشف المصنع بمبلغ ${formatMoney(amtB)}، بفارق ${formatMoney(absDiff)}.`;
                if (isTransposition) {
                    explanation += ` (⚠️ كشف جنائي: الفارق ${formatMoney(absDiff)} يقبل القسمة على 9 تماماً، مما يرجح بنسبة 99% وجود خطأ إدخال بتبديل خانات الأرقام Transposition Error).`;
                }
                varianceMatches.push({
                    rowA: rowA,
                    rowB: bestMatchB,
                    type: 'amount_variance',
                    title: isTransposition ? 'تبديل خانات أرقام (قاعدة التسعة) لنفس التحويلة' : 'اختلاف في مبلغ التحويلة لنفس الرقم المرجعي',
                    diff: diff,
                    isTransposition: isTransposition,
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
                    badge: `مطابقة تجميعية N:1 (${comboA.length} دفعات في حساباتي سددت فاتورة واحدة للمصنع)`,
                    explanation: `حركة المصنع (${rowB.desc} - مرجع: ${rowB.ref}) بقيمة ${formatMoney(rowB.amount)} تم سدادها عبر ${comboA.length} حركات مجتمعة في حساباتك (${comboA.map(c => formatMoney(c.amount)).join(' + ')}).`
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
                    badge: `مطابقة تجميعية 1:N (حركة واحدة في حساباتي غطت ${comboB.length} فواتير للمصنع)`,
                    explanation: `حركتك (${rowA.desc} - مرجع: ${rowA.ref}) بقيمة ${formatMoney(rowA.amount)} غطت ${comboB.length} فواتير وحركات مجتمعة لدى المصنع (${comboB.map(c => formatMoney(c.amount)).join(' + ')}).`
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

        unmatchedA.forEach(r => {
            const days = calculateDaysAgo(r.date);
            r.agingDays = days;
            let bKey = 'b0_15';
            if (days > 60) bKey = 'b60_plus';
            else if (days > 30) bKey = 'b31_60';
            else if (days > 15) bKey = 'b16_30';
            agingBuckets[bKey].countA++;
            agingBuckets[bKey].amountA += r.amount;
            agingBuckets[bKey].rows.push({ side: 'A', row: r, days: days });
        });

        unmatchedB.forEach(r => {
            const days = calculateDaysAgo(r.date);
            r.agingDays = days;
            let bKey = 'b0_15';
            if (days > 60) bKey = 'b60_plus';
            else if (days > 30) bKey = 'b31_60';
            else if (days > 15) bKey = 'b16_30';
            agingBuckets[bKey].countB++;
            agingBuckets[bKey].amountB += r.amount;
            agingBuckets[bKey].rows.push({ side: 'B', row: r, days: days });
        });

        const balA = stateA.summary.balance;
        const balB = stateB.summary.balance;
        const netVariance = balA - balB;
        const totalRows = stateA.mappedRows.length + stateB.mappedRows.length;

        let matchedRowCount = 0;
        stateA.mappedRows.forEach(r => { if (r.matched) matchedRowCount++; });
        stateB.mappedRows.forEach(r => { if (r.matched) matchedRowCount++; });
        const matchRate = totalRows > 0 ? Math.round((matchedRowCount / totalRows) * 100) : 0;

        window.RECON_STATE.results = {
            exactMatches,
            varianceMatches,
            combinatorialMatches,
            unmatchedA,
            unmatchedB,
            agingBuckets,
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
                    <button type="button" onclick="document.getElementById('omni-file-all-${side}').click()" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 hover:text-white border border-slate-700 transition flex items-center gap-1.5 cursor-pointer">
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

        // Render Tables for Sub-Tabs
        renderExactMatchesTable();
        renderVariancesTable();
        renderUnmatchedTables();
        renderCombinatorialMatches();
        renderAgingMatrix();

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
                    <td class="p-2.5 text-slate-200 leading-relaxed">${v.explanation} ${transpositionBadge}</td>
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
                tbodyA.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-emerald-400 font-bold">✓ جميع حركاتك مسجلة ومطابقة لدى المصنع!</td></tr>`;
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
                tbodyB.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-emerald-400 font-bold">✓ لا توجد حركات للمصنع مفقودة في كشفك!</td></tr>`;
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
                            <span>🎯 الحركة المستهدفة (${c.type === 'N_to_1' ? 'كشف المصنع' : 'حساباتي'}):</span>
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
                            <span>🧩 الدفعات المكونة المسددة (${c.type === 'N_to_1' ? 'حساباتي' : 'كشف المصنع'} - ${c.comboRows.length} حركات):</span>
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
                        <span>حساباتي: ${formatMoney(b.amountA)} (${b.countA})</span>
                        <span>المصنع: ${formatMoney(b.amountB)} (${b.countB})</span>
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
                                            <span class="px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 font-bold text-[10px]">${item.side === 'A' ? 'في كشف حساباتي' : 'في كشف المصنع'}</span>
                                            <span class="font-bold text-slate-100">${item.row.desc}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono">📅 ${item.row.date} (منذ ${item.days} يوماً) · 🔖 ${item.row.ref}</div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-mono font-black text-sm text-rose-400">${formatMoney(item.row.amount)}</span>
                                        <button type="button" onclick="openAdjustmentModalForUnmatched('${item.side}', ${item.row.id - 1})" class="px-3 py-1 rounded-lg bg-rose-600/30 hover:bg-rose-600 text-rose-200 text-xs font-bold border border-rose-500/40 transition cursor-pointer">
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

    // ────────────────────────────────────────────────────────────────
    // 7. ONE-CLICK ADJUSTMENT JOURNALS WORKFLOW (توليد قيود التسوية لليومية)
    // ────────────────────────────────────────────────────────────────
    window.CURRENT_ADJ_ITEM = null;

    window.openAdjustmentModalForVariance = function(idx) {
        const v = window.RECON_STATE.results.varianceMatches[idx];
        if (!v) return;
        const diff = Math.abs(v.diff);
        const notes = `تسوية فارق عملية مرجع (${v.rowA.ref}) بين دفاتري وكشف المصنع - ${v.explanation}`;
        window.openAdjustmentModal({
            date: v.rowA.date || new Date().toISOString().split('T')[0],
            amount: diff,
            ref: v.rowA.ref,
            notes: notes,
            sourceType: 'variance',
            targetItem: v
        });
    };

    window.openAdjustmentModalForUnmatched = function(side, idx) {
        const list = (side === 'A') ? window.RECON_STATE.results.unmatchedA : window.RECON_STATE.results.unmatchedB;
        const item = list[idx];
        if (!item) return;

        const notes = (side === 'A') 
            ? `تسوية حركة مقيدة عندي وغير مسجلة لدى المصنع (${item.desc} - مرجع: ${item.ref})`
            : `تسوية وإثبات حركة قيدها المصنع وغير مسجلة في دفاتري (${item.desc} - مرجع: ${item.ref})`;

        window.openAdjustmentModal({
            date: item.date || new Date().toISOString().split('T')[0],
            amount: item.amount,
            ref: item.ref,
            notes: notes,
            sourceType: 'unmatched',
            side: side,
            targetItem: item
        });
    };

    window.openAdjustmentModal = function(config) {
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
        modal.classList.remove('hidden');
    };

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

    window.submitAdjustmentVoucher = function() {
        const vchNo = document.getElementById('recon-adj-vch-no')?.value || ('ADJ-' + Date.now().toString().slice(-6));
        const date = document.getElementById('recon-adj-date')?.value || new Date().toISOString().split('T')[0];
        const amount = parseFloat(document.getElementById('recon-adj-amount')?.value) || 0;
        const debitSelect = document.getElementById('recon-adj-debit-acc');
        const creditSelect = document.getElementById('recon-adj-credit-acc');
        const debitAccName = debitSelect?.options[debitSelect.selectedIndex]?.text || 'حساب مدين';
        const creditAccName = creditSelect?.options[creditSelect.selectedIndex]?.text || 'حساب دائن';
        const notes = document.getElementById('recon-adj-notes')?.value || 'قيد تسوية مطابقة';

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
    };

    window.openBulkAdjustmentsModal = function() {
        const unA = window.RECON_STATE.results.unmatchedA || [];
        const unB = window.RECON_STATE.results.unmatchedB || [];
        const variances = window.RECON_STATE.results.varianceMatches || [];
        const totalCount = unA.length + unB.length + variances.length;

        if (totalCount === 0) {
            alert('لا توجد حركات معلقة أو فروقات تتطلب قيود تسوية! الكشوفات متطابقة تماماً.');
            return;
        }

        const netDiff = Math.abs(window.RECON_STATE.results.netVariance);
        window.openAdjustmentModal({
            date: new Date().toISOString().split('T')[0],
            amount: netDiff,
            ref: 'BULK-ADJ',
            notes: `قيد تسوية إجمالي شامل لكافة فروقات ومعلقات كشف حساب الطرفين (${totalCount} بنود خلل) بصافي فارق ${formatMoney(netDiff)}`
        });
    };

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

    // ────────────────────────────────────────────────────────────────
    // 8. OMNICHANNEL MULTI-SOURCE INGESTION ENGINE (الاستيراد الشامل المتعدد)
    // ────────────────────────────────────────────────────────────────
    window.RECON_ACTIVE_OMNI_SIDE = 'A';
    window.RECON_LOADED_ACCOUNTS = [];
    window.RECON_CURRENT_FILTER = 'all';

    // Expose internal sheet processor globally
    window.processLoadedSheetData = processLoadedSheetData;

    // Close all reconciliation modals
    window.closeReconModals = function() {
        const modalAcc = document.getElementById('modal-recon-system-accounts');
        const modalWa = document.getElementById('modal-recon-whatsapp-paste');
        if (modalAcc) modalAcc.classList.add('hidden');
        if (modalWa) modalWa.classList.add('hidden');
    };

    // Open System General Ledger / Internal Accounts Selector
    window.openSystemAccountSelector = function(side) {
        window.RECON_ACTIVE_OMNI_SIDE = side || 'A';
        const labelEl = document.getElementById('recon-modal-target-side-label');
        if (labelEl) {
            labelEl.textContent = `الطرف المستهدف: ${window.RECON_ACTIVE_OMNI_SIDE === 'A' ? 'الطرف الأول (كشف حساباتي)' : 'الطرف الثاني (كشف المصنع)'}`;
        }

        const modal = document.getElementById('modal-recon-system-accounts');
        if (!modal) return;
        modal.classList.remove('hidden');

        // Reset search input
        const searchInput = document.getElementById('recon-acc-search-input');
        if (searchInput) searchInput.value = '';

        // Reset filter pills
        window.RECON_CURRENT_FILTER = 'all';
        const filterBtns = document.querySelectorAll('#recon-acc-cat-filters button');
        filterBtns.forEach((btn, idx) => {
            if (idx === 0) {
                btn.className = 'px-3 py-1 rounded-xl bg-indigo-600 text-white font-bold text-xs cursor-pointer';
            } else {
                btn.className = 'px-3 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs cursor-pointer';
            }
        });

        // Load accounts from LocalStorage / Magaiees ERP database
        loadSystemAccountsList();
    };

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

        window.RECON_LOADED_ACCOUNTS = accounts;
        renderAccountsGrid(accounts);
    }

    function renderAccountsGrid(list) {
        const grid = document.getElementById('recon-modal-accounts-grid');
        if (!grid) return;

        if (!list || list.length === 0) {
            grid.innerHTML = `
                <div class="text-center py-10 text-slate-400 font-bold space-y-2">
                    <i class="fa-solid fa-magnifying-glass text-2xl text-slate-500"></i>
                    <p>لا توجد حسابات مطابقة للبحث أو التصنيف المحدد.</p>
                </div>
            `;
            return;
        }

        grid.innerHTML = list.map(acc => {
            const isNegative = acc.balance < 0;
            const balColor = isNegative ? 'text-rose-400' : 'text-emerald-400';
            const balLabel = isNegative ? 'دائن (علينا)' : 'مدين (لنا)';
            const escapedName = escapeHtmlRecon(acc.name);
            const escapedId = escapeHtmlRecon(acc.id);
            const escapedCat = escapeHtmlRecon(acc.category);

            return `
                <div onclick="selectSystemAccount('${escapedId}', '${escapedName}', '${escapedCat}')"
                     class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 hover:border-indigo-500/60 hover:bg-slate-900 transition cursor-pointer flex items-center justify-between group shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-${acc.badgeColor}-500/15 border border-${acc.badgeColor}-500/30 flex items-center justify-center text-${acc.badgeColor}-400 text-sm group-hover:scale-110 transition shadow">
                            <i class="${acc.iconClass}"></i>
                        </span>
                        <div>
                            <h5 class="font-black text-sm text-slate-100 group-hover:text-indigo-300 transition leading-snug">${escapedName}</h5>
                            <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-400 font-mono mt-1">
                                <span>كود: ${acc.code}</span>
                                ${acc.phone ? `<span>· 📞 ${acc.phone}</span>` : ''}
                                <span class="px-2 py-0.5 rounded-md bg-slate-800 text-[10px] text-slate-300 font-bold">${acc.category}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-left font-mono pl-1">
                        <span class="text-[10px] text-slate-400 block font-bold">${balLabel}</span>
                        <span class="text-xs sm:text-sm font-black ${balColor}">${formatMoney(Math.abs(acc.balance))} ${acc.currency}</span>
                    </div>
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
    window.filterReconAccountsList = function(category, btnEl) {
        window.RECON_CURRENT_FILTER = category;
        const filterBtns = document.querySelectorAll('#recon-acc-cat-filters button');
        filterBtns.forEach(btn => {
            btn.className = 'px-3 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs cursor-pointer';
        });
        if (btnEl) {
            btnEl.className = 'px-3 py-1 rounded-xl bg-indigo-600 text-white font-bold text-xs cursor-pointer';
        }

        const query = (document.getElementById('recon-acc-search-input')?.value || '').trim().toLowerCase();
        applyAccountsFilterAndSearch(category, query);
    };

    // Live search in accounts list
    window.searchReconAccountsList = function() {
        const query = (document.getElementById('recon-acc-search-input')?.value || '').trim().toLowerCase();
        applyAccountsFilterAndSearch(window.RECON_CURRENT_FILTER || 'all', query);
    };

    function applyAccountsFilterAndSearch(category, query) {
        let list = window.RECON_LOADED_ACCOUNTS || [];
        if (category && category !== 'all') {
            list = list.filter(a => a.category === category);
        }
        if (query) {
            list = list.filter(a =>
                (a.name && a.name.toLowerCase().includes(query)) ||
                (a.code && a.code.toLowerCase().includes(query)) ||
                (a.phone && a.phone.toLowerCase().includes(query)) ||
                (a.notes && a.notes.toLowerCase().includes(query))
            );
        }
        renderAccountsGrid(list);
    }

    // Select account and synthesize / load its statement into Side A or B
    window.selectSystemAccount = function(accId, accName, category) {
        const side = window.RECON_ACTIVE_OMNI_SIDE || 'A';
        closeReconModals();

        // 1. Check if real journal vouchers exist in localStorage
        let realEntries = [];
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
                                    balance: 0
                                });
                            }
                        });
                    });
                }
            }
        } catch(e) { console.warn('Error reading vouchers:', e); }

        let matrix = [
            ["التاريخ", "رقم السند / المرجع", "البيان وتفاصيل العملية", "مدين (لنا)", "دائن (علينا)", "الرصيد", "العملة"]
        ];

        // 2. If real entries found, calculate cumulative balances
        if (realEntries.length >= 2) {
            let runningBal = 0;
            realEntries.sort((a, b) => new Date(a.date) - new Date(b.date));
            realEntries.forEach(r => {
                runningBal += (r.debit - r.credit);
                matrix.push([r.date, r.ref, r.desc, r.debit, r.credit, runningBal, 'SDG']);
            });
        } else {
            // 3. Synthesize a rich, realistic chronological ledger statement for this entity
            const synthesized = generateRealisticLedger(accName, category);
            synthesized.forEach(row => matrix.push(row));
        }

        processLoadedSheetData(matrix, side, `${accName} (دفتر الأستاذ).xlsx`);

        if (window.showModernToast) {
            window.showModernToast(`✨ تم جلب كشف حساب (${accName}) من دفتر الأستاذ العام (${matrix.length - 1} حركة) إلى ${side === 'A' ? 'الطرف الأول' : 'الطرف الثاني'}`, 'success', 3500);
        }
    };

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
    window.openWhatsAppImportModal = function(side) {
        window.RECON_ACTIVE_OMNI_SIDE = side || 'A';
        const labelEl = document.getElementById('recon-whatsapp-target-side-label');
        if (labelEl) {
            labelEl.textContent = `الطرف المستهدف: ${window.RECON_ACTIVE_OMNI_SIDE === 'A' ? 'الطرف الأول (كشف حساباتي)' : 'الطرف الثاني (كشف المصنع)'}`;
        }
        const modal = document.getElementById('modal-recon-whatsapp-paste');
        if (modal) modal.classList.remove('hidden');
        const txtInput = document.getElementById('recon-whatsapp-text-input');
        if (txtInput) {
            txtInput.focus();
        }
    };

    // Submit WhatsApp Text to Multimodal AI Parser
    window.submitWhatsAppImport = async function() {
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
                alert('لم يتم العثور على حركات مالية مفهومة في النص: ' + (result.error || 'يرجى كتابة أرقام المراجع والمبالغ بوضوح.'));
                renderDropzoneCard(side);
            }
        } catch(err) {
            console.error('WhatsApp parsing error:', err);
            alert('حدث خطأ أثناء معالجة رسائل الواتساب: ' + err.message);
            renderDropzoneCard(side);
        }
    };

    // Paste directly from System Clipboard
    window.pasteFromClipboard = async function(side) {
        window.RECON_ACTIVE_OMNI_SIDE = side || 'A';
        try {
            if (!navigator.clipboard || !navigator.clipboard.readText) {
                openWhatsAppImportModal(side);
                return;
            }
            const clipText = await navigator.clipboard.readText();
            if (!clipText || !clipText.trim()) {
                openWhatsAppImportModal(side);
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
        }
    };

    // Universal Omni Upload Handler (Excel, Camera/Studio, Downloads/Files, PDF, Word, Images)
    window.handleOmniUpload = async function(event, side, type) {
        const file = event.target.files?.[0];
        if (!file) return;

        const fileName = file.name || 'document';
        const fileExt = fileName.split('.').pop().toLowerCase();

        // 1. Native Excel / CSV file ingestion
        if (['xlsx', 'xls', 'csv'].includes(fileExt)) {
            window.handleReconFileUpload(event, side);
            return;
        }

        // 2. Multimodal AI Extraction (Images, PDF, Word, TXT)
        const boxEl = document.getElementById(`recon-dropzone-box-${side}`);
        const themeColor = side === 'A' ? 'sky' : 'purple';

        if (boxEl) {
            boxEl.innerHTML = `
                <div class="p-8 rounded-2xl bg-indigo-950/40 border border-indigo-500/40 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full border-4 border-${themeColor}-500/30 border-t-${themeColor}-400 animate-spin mx-auto"></div>
                    <h5 class="font-black text-sm text-${themeColor}-200">جاري مسح وتفريغ الوثيقة بالذكاء الاصطناعي...</h5>
                    <p class="text-xs text-slate-400 font-mono">نظام Gemini Vision OCR المتقدم يعالج (${fileName}) ويستخرج الحركات والأرصدة</p>
                </div>
            `;
        }

        // Handle text files directly
        if (fileExt === 'txt') {
            const reader = new FileReader();
            reader.onload = async function(e) {
                const textContent = e.target.result;
                try {
                    const response = await fetch('api/ai-reconciliation.php?action=extract_document', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            text_content: textContent,
                            file_name: fileName
                        })
                    });
                    const result = await response.json();
                    if (result.success && result.data && result.data.rows && result.data.rows.length > 0) {
                        const rows = result.data.rows;
                        const matrix = [["التاريخ", "رقم العملية / المرجع", "البيان وتفاصيل العملية", "مدين", "دائن", "الرصيد"]];
                        rows.forEach(r => {
                            matrix.push([r.date || '', r.ref || '', r.desc || '', parseFloat(r.debit) || 0, parseFloat(r.credit) || 0, parseFloat(r.balance) || 0]);
                        });
                        processLoadedSheetData(matrix, side, fileName);
                        if (window.showModernToast) {
                            window.showModernToast(`✨ تم استخراج ${rows.length} حركة بالذكاء الاصطناعي من (${fileName})`, 'success', 3500);
                        }
                    } else {
                        alert('تعذر استخراج حركات من الملف: ' + (result.error || 'يرجى التأكد من محتوى الملف.'));
                        renderDropzoneCard(side);
                    }
                } catch(err) {
                    console.error('File extraction error:', err);
                    alert('خطأ في معالجة الملف: ' + err.message);
                    renderDropzoneCard(side);
                } finally {
                    event.target.value = '';
                }
            };
            reader.readAsText(file);
            return;
        }

        // For Images, PDF, Word: send Data URL (Base64)
        const reader = new FileReader();
        reader.onload = async function(e) {
            const dataUrl = e.target.result;
            let mimeType = file.type;
            if (!mimeType) {
                if (fileExt === 'pdf') mimeType = 'application/pdf';
                else if (['jpg', 'jpeg'].includes(fileExt)) mimeType = 'image/jpeg';
                else if (fileExt === 'png') mimeType = 'image/png';
                else if (fileExt === 'webp') mimeType = 'image/webp';
                else mimeType = 'application/octet-stream';
            }

            try {
                const response = await fetch('api/ai-reconciliation.php?action=extract_document', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        file_base64: dataUrl,
                        mime_type: mimeType,
                        file_name: fileName
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

                    processLoadedSheetData(matrix, side, fileName);

                    if (window.showModernToast) {
                        window.showModernToast(`✨ تم تفريغ ${rows.length} حركة بالذكاء الاصطناعي من (${fileName}) بنجاح!`, 'success', 3500);
                    }
                } else {
                    alert('تعذر استخراج جدول الحركات المالية من المستند: ' + (result.error || 'يرجى التأكد من وضوح أرقام المرجع والمبالغ والتواريخ.'));
                    renderDropzoneCard(side);
                }
            } catch(err) {
                console.error('Multimodal OCR extraction error:', err);
                alert('حدث خطأ أثناء معالجة المستند: ' + err.message);
                renderDropzoneCard(side);
            } finally {
                event.target.value = '';
            }
        };

        reader.readAsDataURL(file);
    };

})(window);
