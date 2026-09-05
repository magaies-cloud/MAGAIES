/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * نظام المواريث والفرائض الشرعي المتطور (Islamic Inheritance Engine)
 * الإصدار: 2026.1 - متوافق مع الفقه الإسلامي المعتمد وقوانين الأحوال الشخصية
 * ELSAJANA2 / MAGAIES Architecture
 * ═══════════════════════════════════════════════════════════════════════════════
 */

// Math utilities for Islamic fractions
function inhGcd(a, b) {
    a = Math.abs(Math.round(a));
    b = Math.abs(Math.round(b));
    return b === 0 ? a : inhGcd(b, a % b);
}

function inhLcm(a, b) {
    if (a === 0 || b === 0) return 0;
    return Math.abs(Math.round((a * b) / inhGcd(a, b)));
}

// Global state
window.inheritanceData = {
    grossEstate: 0,
    debts: 0,
    funeral: 0,
    bequest: 0,
    currency: 'SAR',
    deceasedGender: 'male',
    lastResult: null
};

// Heir IDs map
const INH_HEIR_KEYS = [
    'husband', 'wives', 'father', 'mother',
    'paternalGrandfather', 'paternalGrandmother', 'maternalGrandmother',
    'sons', 'daughters', 'sonsSons', 'sonsDaughters',
    'fullBrothers', 'fullSisters', 'paternalBrothers', 'paternalSisters',
    'maternalSiblings', 'fullNephews', 'paternalNephews',
    'fullUncles', 'paternalUncles', 'fullCousins', 'paternalCousins'
];

/**
 * Main Calculation Engine based on Classical Sunni Fiqh & Personal Status Laws
 */
function calculateIslamicInheritance(input) {
    const estate = Math.max(0, Number(input.grossEstate || 0));
    const funeral = Math.max(0, Number(input.funeral || 0));
    const debts = Math.max(0, Number(input.debts || 0));
    const bequestReq = Math.max(0, Number(input.bequest || 0));
    const currency = input.currency || 'SAR';

    // Deduct funeral & debts first (الحقوق المتعلقة بالتركة)
    let netBeforeBequest = Math.max(0, estate - funeral - debts);
    
    // Bequest (الوصية) cannot exceed 1/3 of net estate without heirs' unanimous permission
    const maxBequest = netBeforeBequest / 3;
    const actualBequest = Math.min(bequestReq, maxBequest);
    const bequestTruncated = bequestReq > maxBequest;
    const distributableEstate = netBeforeBequest - actualBequest;

    const deceasedGender = input.deceasedGender || 'male';

    const heirs = {
        husband: deceasedGender === 'female' ? Math.min(1, Math.max(0, parseInt(input.husband || 0))) : 0,
        wives: deceasedGender === 'male' ? Math.min(4, Math.max(0, parseInt(input.wives || 0))) : 0,
        father: Math.min(1, Math.max(0, parseInt(input.father || 0))),
        mother: Math.min(1, Math.max(0, parseInt(input.mother || 0))),
        paternalGrandfather: Math.min(1, Math.max(0, parseInt(input.paternalGrandfather || 0))),
        paternalGrandmother: Math.min(1, Math.max(0, parseInt(input.paternalGrandmother || 0))),
        maternalGrandmother: Math.min(1, Math.max(0, parseInt(input.maternalGrandmother || 0))),
        sons: Math.max(0, parseInt(input.sons || 0)),
        daughters: Math.max(0, parseInt(input.daughters || 0)),
        sonsSons: Math.max(0, parseInt(input.sonsSons || 0)),
        sonsDaughters: Math.max(0, parseInt(input.sonsDaughters || 0)),
        fullBrothers: Math.max(0, parseInt(input.fullBrothers || 0)),
        fullSisters: Math.max(0, parseInt(input.fullSisters || 0)),
        paternalBrothers: Math.max(0, parseInt(input.paternalBrothers || 0)),
        paternalSisters: Math.max(0, parseInt(input.paternalSisters || 0)),
        maternalSiblings: Math.max(0, parseInt(input.maternalSiblings || 0)),
        fullNephews: Math.max(0, parseInt(input.fullNephews || 0)),
        paternalNephews: Math.max(0, parseInt(input.paternalNephews || 0)),
        fullUncles: Math.max(0, parseInt(input.fullUncles || 0)),
        paternalUncles: Math.max(0, parseInt(input.paternalUncles || 0)),
        fullCousins: Math.max(0, parseInt(input.fullCousins || 0)),
        paternalCousins: Math.max(0, parseInt(input.paternalCousins || 0)),
    };

    const hasMaleBranch = heirs.sons > 0 || heirs.sonsSons > 0;
    const hasFemaleBranch = heirs.daughters > 0 || heirs.sonsDaughters > 0;
    const hasBranch = hasMaleBranch || hasFemaleBranch;
    
    const totalSiblingsCount = heirs.fullBrothers + heirs.fullSisters + heirs.paternalBrothers + 
                               heirs.paternalSisters + heirs.maternalSiblings;

    // Rules of Exclusion (قواعد الحجب الشرعية)
    const blocked = {};

    // Son exclusions:
    if (heirs.sons > 0) {
        if (heirs.sonsSons > 0) blocked.sonsSons = 'محجوب بابن المتوفى المباشر';
        if (heirs.sonsDaughters > 0) blocked.sonsDaughters = 'محجوبة بابن المتوفى المباشر';
        if (heirs.fullBrothers > 0) blocked.fullBrothers = 'محجوب بالفرع الوارث المذكر (الابن)';
        if (heirs.fullSisters > 0) blocked.fullSisters = 'محجوبة بالفرع الوارث المذكر (الابن)';
        if (heirs.paternalBrothers > 0) blocked.paternalBrothers = 'محجوب بالفرع الوارث المذكر';
        if (heirs.paternalSisters > 0) blocked.paternalSisters = 'محجوبة بالفرع الوارث المذكر';
        if (heirs.maternalSiblings > 0) blocked.maternalSiblings = 'محجوبون بالفرع الوارث';
        if (heirs.fullNephews > 0) blocked.fullNephews = 'محجوب بالابن';
        if (heirs.paternalNephews > 0) blocked.paternalNephews = 'محجوب بالابن';
        if (heirs.fullUncles > 0) blocked.fullUncles = 'محجوب بالابن';
        if (heirs.paternalUncles > 0) blocked.paternalUncles = 'محجوب بالابن';
        if (heirs.fullCousins > 0) blocked.fullCousins = 'محجوب بالابن';
        if (heirs.paternalCousins > 0) blocked.paternalCousins = 'محجوب بالابن';
    } else if (heirs.sonsSons > 0) {
        if (heirs.fullBrothers > 0) blocked.fullBrothers = 'محجوب بابن الابن';
        if (heirs.fullSisters > 0) blocked.fullSisters = 'محجوبة بابن الابن';
        if (heirs.paternalBrothers > 0) blocked.paternalBrothers = 'محجوب بابن الابن';
        if (heirs.paternalSisters > 0) blocked.paternalSisters = 'محجوبة بابن الابن';
        if (heirs.maternalSiblings > 0) blocked.maternalSiblings = 'محجوبون بالفرع الوارث المذكر';
        if (heirs.fullNephews > 0) blocked.fullNephews = 'محجوب بابن الابن';
        if (heirs.paternalNephews > 0) blocked.paternalNephews = 'محجوب بابن الابن';
        if (heirs.fullUncles > 0) blocked.fullUncles = 'محجوب بابن الابن';
        if (heirs.paternalUncles > 0) blocked.paternalUncles = 'محجوب بابن الابن';
        if (heirs.fullCousins > 0) blocked.fullCousins = 'محجوب بابن الابن';
        if (heirs.paternalCousins > 0) blocked.paternalCousins = 'محجوب بابن الابن';
    }

    if (hasFemaleBranch && !hasMaleBranch) {
        if (heirs.maternalSiblings > 0) blocked.maternalSiblings = 'محجوبون بوجود الفرع الوارث (البنات)';
    }

    // 2+ Daughters exclude Son's Daughters (unless male agnate grandson equal or lower makes them asaba)
    if (heirs.daughters >= 2 && heirs.sonsDaughters > 0 && heirs.sonsSons === 0 && !blocked.sonsDaughters) {
        blocked.sonsDaughters = 'محجوبة لاستغراق البنات فرض الثلثين لعدم وجود معصب مساوٍ';
    }

    // Father exclusions:
    if (heirs.father > 0) {
        if (heirs.paternalGrandfather > 0) blocked.paternalGrandfather = 'محجوب بالأب (الأصل المذكر الأقرب)';
        if (heirs.paternalGrandmother > 0) blocked.paternalGrandmother = 'محجوبة بالأب (عند جمهور الفقهاء)';
        if (heirs.fullBrothers > 0) blocked.fullBrothers = 'محجوب بالأب (الأصل الوارث المذكر)';
        if (heirs.fullSisters > 0) blocked.fullSisters = 'محجوبة بالأب';
        if (heirs.paternalBrothers > 0) blocked.paternalBrothers = 'محجوب بالأب';
        if (heirs.paternalSisters > 0) blocked.paternalSisters = 'محجوبة بالأب';
        if (heirs.maternalSiblings > 0) blocked.maternalSiblings = 'محجوبون بالأب';
        if (heirs.fullNephews > 0) blocked.fullNephews = 'محجوب بالأب';
        if (heirs.paternalNephews > 0) blocked.paternalNephews = 'محجوب بالأب';
        if (heirs.fullUncles > 0) blocked.fullUncles = 'محجوب بالأب';
        if (heirs.paternalUncles > 0) blocked.paternalUncles = 'محجوب بالأب';
        if (heirs.fullCousins > 0) blocked.fullCousins = 'محجوب بالأب';
        if (heirs.paternalCousins > 0) blocked.paternalCousins = 'محجوب بالأب';
    }

    // Grandfather exclusions:
    if (heirs.father === 0 && heirs.paternalGrandfather > 0) {
        if (heirs.maternalSiblings > 0) blocked.maternalSiblings = 'محجوبون بالجد لأب (الأصل المذكر)';
        if (heirs.fullNephews > 0) blocked.fullNephews = 'محجوب بالجد';
        if (heirs.paternalNephews > 0) blocked.paternalNephews = 'محجوب بالجد';
        if (heirs.fullUncles > 0) blocked.fullUncles = 'محجوب بالجد';
        if (heirs.paternalUncles > 0) blocked.paternalUncles = 'محجوب بالجد';
        if (heirs.fullCousins > 0) blocked.fullCousins = 'محجوب بالجد';
        if (heirs.paternalCousins > 0) blocked.paternalCousins = 'محجوب بالجد';
    }

    // Mother excludes all grandmothers:
    if (heirs.mother > 0) {
        if (heirs.paternalGrandmother > 0) blocked.paternalGrandmother = 'محجوبة بالأم (الأم تحجب جميع الجدات إجماعاً)';
        if (heirs.maternalGrandmother > 0) blocked.maternalGrandmother = 'محجوبة بالأم (الأم تحجب جميع الجدات إجماعاً)';
    }

    // Full Sister as Asaba with daughters (العصبة مع الغير):
    const fullSisterAsabaWithDaughters = (heirs.fullSisters > 0 && !blocked.fullSisters && heirs.fullBrothers === 0 && 
                                          heirs.father === 0 && !hasMaleBranch && hasFemaleBranch);
    
    if ((heirs.fullBrothers > 0 && !blocked.fullBrothers) || fullSisterAsabaWithDaughters) {
        const reason = fullSisterAsabaWithDaughters ? 'محجوب بالأخت الشقيقة التي صارت عصبة مع البنات' : 'محجوب بالأخ الشقيق';
        if (heirs.paternalBrothers > 0) blocked.paternalBrothers = reason;
        if (heirs.paternalSisters > 0) blocked.paternalSisters = reason;
        if (heirs.fullNephews > 0) blocked.fullNephews = reason;
        if (heirs.paternalNephews > 0) blocked.paternalNephews = reason;
        if (heirs.fullUncles > 0) blocked.fullUncles = reason;
        if (heirs.paternalUncles > 0) blocked.paternalUncles = reason;
        if (heirs.fullCousins > 0) blocked.fullCousins = reason;
        if (heirs.paternalCousins > 0) blocked.paternalCousins = reason;
    }

    // 2+ Full sisters exclude paternal sisters if no paternal brother
    if (heirs.fullSisters >= 2 && !blocked.fullSisters && heirs.fullBrothers === 0 && 
        heirs.paternalSisters > 0 && heirs.paternalBrothers === 0 && !blocked.paternalSisters) {
        blocked.paternalSisters = 'محجوبة لاستغراق الأخوات الشقيقات فرض الثلثين';
    }

    const paternalSisterAsabaWithDaughters = (heirs.paternalSisters > 0 && !blocked.paternalSisters && 
                                             heirs.paternalBrothers === 0 && heirs.father === 0 && 
                                             heirs.fullBrothers === 0 && heirs.fullSisters === 0 && 
                                             !hasMaleBranch && hasFemaleBranch);

    if ((heirs.paternalBrothers > 0 && !blocked.paternalBrothers) || paternalSisterAsabaWithDaughters) {
        const reason = paternalSisterAsabaWithDaughters ? 'محجوب بالأخت لأب العصبة مع البنات' : 'محجوب بالأخ لأب';
        if (heirs.fullNephews > 0) blocked.fullNephews = reason;
        if (heirs.paternalNephews > 0) blocked.paternalNephews = reason;
        if (heirs.fullUncles > 0) blocked.fullUncles = reason;
        if (heirs.paternalUncles > 0) blocked.paternalUncles = reason;
        if (heirs.fullCousins > 0) blocked.fullCousins = reason;
        if (heirs.paternalCousins > 0) blocked.paternalCousins = reason;
    }

    if (heirs.fullNephews > 0 && !blocked.fullNephews) {
        if (heirs.paternalNephews > 0) blocked.paternalNephews = 'محجوب بابن الأخ الشقيق';
        if (heirs.fullUncles > 0) blocked.fullUncles = 'محجوب بابن الأخ الشقيق';
        if (heirs.paternalUncles > 0) blocked.paternalUncles = 'محجوب بابن الأخ الشقيق';
        if (heirs.fullCousins > 0) blocked.fullCousins = 'محجوب بابن الأخ الشقيق';
        if (heirs.paternalCousins > 0) blocked.paternalCousins = 'محجوب بابن الأخ الشقيق';
    }

    if (heirs.paternalNephews > 0 && !blocked.paternalNephews) {
        if (heirs.fullUncles > 0) blocked.fullUncles = 'محجوب بابن الأخ لأب';
        if (heirs.paternalUncles > 0) blocked.paternalUncles = 'محجوب بابن الأخ لأب';
        if (heirs.fullCousins > 0) blocked.fullCousins = 'محجوب بابن الأخ لأب';
        if (heirs.paternalCousins > 0) blocked.paternalCousins = 'محجوب بابن الأخ لأب';
    }

    if (heirs.fullUncles > 0 && !blocked.fullUncles) {
        if (heirs.paternalUncles > 0) blocked.paternalUncles = 'محجوب بالعم الشقيق';
        if (heirs.fullCousins > 0) blocked.fullCousins = 'محجوب بالعم الشقيق';
        if (heirs.paternalCousins > 0) blocked.paternalCousins = 'محجوب بالعم الشقيق';
    }

    if (heirs.paternalUncles > 0 && !blocked.paternalUncles) {
        if (heirs.fullCousins > 0) blocked.fullCousins = 'محجوب بالعم لأب';
        if (heirs.paternalCousins > 0) blocked.paternalCousins = 'محجوب بالعم لأب';
    }

    if (heirs.fullCousins > 0 && !blocked.fullCousins) {
        if (heirs.paternalCousins > 0) blocked.paternalCousins = 'محجوب بابن العم الشقيق';
    }

    // Active counts
    const active = {};
    for (const k in heirs) {
        active[k] = blocked[k] ? 0 : heirs[k];
    }

    // Check Umariyyatan (العمريتان):
    const isUmariyyaHusband = (active.husband === 1 && active.mother === 1 && active.father === 1 && 
                               !hasBranch && totalSiblingsCount === 0 &&
                               Object.keys(active).filter(k => active[k] > 0 && !['husband','mother','father'].includes(k)).length === 0);
    const isUmariyyaWife = (active.wives >= 1 && active.mother === 1 && active.father === 1 && 
                            !hasBranch && totalSiblingsCount === 0 &&
                            Object.keys(active).filter(k => active[k] > 0 && !['wives','mother','father'].includes(k)).length === 0);

    const results = [];
    let caseType = 'عادل';
    let base = 1;

    function addShare(role, name, count, num, den, type, reason, dalil) {
        results.push({
            role,
            name,
            count,
            shareFraction: `${num}/${den}`,
            num,
            den,
            type,
            reason,
            dalil,
            totalAmount: 0,
            unitAmount: 0,
            percentage: 0
        });
    }

    if (isUmariyyaHusband) {
        caseType = 'المسألة العمرية الأولى (الغراء)';
        base = 6;
        addShare('husband', 'الزوج', 1, 1, 2, 'فرض', 'النصف لعدم وجود الفرع الوارث للمتوفاة', 'سورة النساء: 12');
        addShare('mother', 'الأم', 1, 1, 6, 'فرض (ثلث الباقي)', 'ثلث الباقي بعد نصيب الزوج (المسألة العمرية الأولى)', 'قضاء عمر بن الخطاب وإجماع الصحابة');
        addShare('father', 'الأب', 1, 2, 6, 'عصبة', 'الباقي تعصيباً (ضعف نصيب الأم)', '«ألحقوا الفرائض بأهلها فما بقي فلأولى رجل ذكر»');
    } else if (isUmariyyaWife) {
        caseType = 'المسألة العمرية الثانية (الغراء)';
        base = 4;
        addShare('wives', active.wives > 1 ? `الزوجات (${active.wives})` : 'الزوجة', active.wives, 1, 4, 'فرض', 'الربع لعدم وجود الفرع الوارث للمتوفى', 'سورة النساء: 12');
        addShare('mother', 'الأم', 1, 1, 4, 'فرض (ثلث الباقي)', 'ثلث الباقي بعد نصيب الزوجة (المسألة العمرية الثانية)', 'قضاء عمر بن الخطاب وإجماع الصحابة');
        addShare('father', 'الأب', 1, 1, 2, 'عصبة', 'الباقي تعصيباً (ضعف نصيب الأم)', '«ألحقوا الفرائض بأهلها فما بقي فلأولى رجل ذكر»');
    } else {
        // Standard Fard allocations
        if (active.husband > 0) {
            if (hasBranch) {
                addShare('husband', 'الزوج', 1, 1, 4, 'فرض', 'الربع لوجود الفرع الوارث للمتوفاة', 'سورة النساء: 12');
            } else {
                addShare('husband', 'الزوج', 1, 1, 2, 'فرض', 'النصف لعدم وجود الفرع الوارث', 'سورة النساء: 12');
            }
        }

        if (active.wives > 0) {
            const title = active.wives > 1 ? `الزوجات (${active.wives})` : 'الزوجة';
            if (hasBranch) {
                addShare('wives', title, active.wives, 1, 8, 'فرض', 'الثمن لوجود الفرع الوارث للمتوفى (يشتركن فيه بالتساوي)', 'سورة النساء: 12');
            } else {
                addShare('wives', title, active.wives, 1, 4, 'فرض', 'الربع لعدم وجود الفرع الوارث (يشتركن فيه بالتساوي)', 'سورة النساء: 12');
            }
        }

        if (active.mother > 0) {
            if (hasBranch || totalSiblingsCount >= 2) {
                const r = hasBranch ? 'السدس لوجود الفرع الوارث' : 'السدس لوجود جمع من الإخوة (اثنان فأكثر)';
                addShare('mother', 'الأم', 1, 1, 6, 'فرض', r, 'سورة النساء: 11');
            } else {
                addShare('mother', 'الأم', 1, 1, 3, 'فرض', 'الثلث لعدم وجود الفرع الوارث وعدم وجود جمع من الإخوة', 'سورة النساء: 11');
            }
        }

        const activeGrandmothers = (active.paternalGrandmother > 0 ? 1 : 0) + (active.maternalGrandmother > 0 ? 1 : 0);
        if (activeGrandmothers > 0) {
            const name = activeGrandmothers === 2 ? 'الجدتان (أم الأب وأم الأم)' : (active.paternalGrandmother ? 'الجدة لأب' : 'الجدة لأم');
            addShare('grandmothers', name, activeGrandmothers, 1, 6, 'فرض', 'السدس لعدم وجود الأم (يشتركن فيه بالتساوي إن تعددن)', 'السنة النبوية وقضاء أبي بكر الصديق رضي الله عنه');
        }

        if (active.father > 0) {
            if (hasMaleBranch) {
                addShare('father', 'الأب', 1, 1, 6, 'فرض', 'السدس فرضاً لوجود الفرع الوارث المذكر (الابن أو ابن الابن)', 'سورة النساء: 11');
            } else if (hasFemaleBranch) {
                addShare('father', 'الأب', 1, 1, 6, 'فرض وتعصيب', 'السدس فرضاً لوجود الفرع المؤنث + الباقي تعصيباً إن وُجد', 'سورة النساء: 11 وحديث «ألحقوا الفرائض بأهلها»');
            } else {
                addShare('father', 'الأب', 1, 0, 1, 'عصبة', 'الباقي تعصيباً بالنفس لعدم وجود الفرع الوارث', '«ألحقوا الفرائض بأهلها فما بقي فلأولى رجل ذكر»');
            }
        }

        if (active.paternalGrandfather > 0) {
            if (hasMaleBranch) {
                addShare('paternalGrandfather', 'الجد لأب', 1, 1, 6, 'فرض', 'السدس فرضاً لوجود الفرع الوارث المذكر وعدم الأب', 'إجماع الصحابة كالأب عند فقده');
            } else if (hasFemaleBranch) {
                addShare('paternalGrandfather', 'الجد لأب', 1, 1, 6, 'فرض وتعصيب', 'السدس فرضاً لوجود الفرع المؤنث + الباقي تعصيباً', 'إجماع الصحابة كالأب عند فقده');
            } else {
                addShare('paternalGrandfather', 'الجد لأب', 1, 0, 1, 'عصبة', 'الباقي تعصيباً لعدم وجود الفرع الوارث والأب', 'إجماع الصحابة كالأب عند فقده');
            }
        }

        if (active.maternalSiblings > 0) {
            const name = active.maternalSiblings === 1 ? 'الأخ/الأخت لأم' : `الإخوة لأم (${active.maternalSiblings})`;
            if (active.maternalSiblings === 1) {
                addShare('maternalSiblings', name, 1, 1, 6, 'فرض', 'السدس للمنفرد لعدم وجود الأصل المذكر والفرع الوارث', 'سورة النساء: 12 (وإن كان رجل يورث كلالة)');
            } else {
                addShare('maternalSiblings', name, active.maternalSiblings, 1, 3, 'فرض', 'الثلث للاثنين فأكثر يشتركون فيه بالتساوي (الذكر والأنثى سواء)', 'سورة النساء: 12 (فهم شركاء في الثلث)');
            }
        }

        if (active.sons === 0 && active.daughters > 0) {
            if (active.daughters === 1) {
                addShare('daughters', 'البنت الواحدة', 1, 1, 2, 'فرض', 'النصف للمنفردة لعدم وجود المعصب (الابن)', 'سورة النساء: 11');
            } else {
                addShare('daughters', `البنات (${active.daughters})`, active.daughters, 2, 3, 'فرض', 'الثلثان للجمع من البنات يشتركن فيه بالتساوي', 'سورة النساء: 11');
            }
        }

        if (active.sons === 0 && active.sonsSons === 0 && active.sonsDaughters > 0) {
            if (active.daughters === 0) {
                if (active.sonsDaughters === 1) {
                    addShare('sonsDaughters', 'بنت الابن', 1, 1, 2, 'فرض', 'النصف للمنفردة لعدم وجود البنات والمعصب', 'سورة النساء: 11 كالبنت عند فقدها');
                } else {
                    addShare('sonsDaughters', `بنات الابن (${active.sonsDaughters})`, active.sonsDaughters, 2, 3, 'فرض', 'الثلثان للجمع عند عدم وجود البنات والمعصب', 'سورة النساء: 11');
                }
            } else if (active.daughters === 1) {
                const name = active.sonsDaughters === 1 ? 'بنت الابن' : `بنات الابن (${active.sonsDaughters})`;
                addShare('sonsDaughters', name, active.sonsDaughters, 1, 6, 'فرض', 'السدس تكملة للثلثين مع البنت الواحدة صاحبة النصف', 'قضاء رسول الله ﷺ (حديث ابن مسعود في صحيح البخاري)');
            }
        }

        if (active.fullBrothers === 0 && active.fullSisters > 0 && active.father === 0 && !hasBranch) {
            if (active.fullSisters === 1) {
                addShare('fullSisters', 'الأخت الشقيقة', 1, 1, 2, 'فرض', 'النصف للمنفردة لعدم وجود المعصب والفرع والأب', 'سورة النساء: 176');
            } else {
                addShare('fullSisters', `الأخوات الشقيقات (${active.fullSisters})`, active.fullSisters, 2, 3, 'فرض', 'الثلثان للجمع يشتركن فيه بالتساوي', 'سورة النساء: 176');
            }
        }

        if (active.fullBrothers === 0 && active.fullSisters === 0 && active.paternalBrothers === 0 && 
            active.paternalSisters > 0 && active.father === 0 && !hasBranch) {
            if (active.paternalSisters === 1) {
                addShare('paternalSisters', 'الأخت لأب', 1, 1, 2, 'فرض', 'النصف للمنفردة لعدم وجود الأشقاء والمعصب', 'سورة النساء: 176 كالشقيقة');
            } else {
                addShare('paternalSisters', `الأخوات لأب (${active.paternalSisters})`, active.paternalSisters, 2, 3, 'فرض', 'الثلثان للجمع عند عدم الأشقاء والمعصب', 'سورة النساء: 176');
            }
        } else if (active.fullSisters === 1 && active.fullBrothers === 0 && active.paternalBrothers === 0 && 
                   active.paternalSisters > 0 && active.father === 0 && !hasBranch) {
            const name = active.paternalSisters === 1 ? 'الأخت لأب' : `الأخوات لأب (${active.paternalSisters})`;
            addShare('paternalSisters', name, active.paternalSisters, 1, 6, 'فرض', 'السدس تكملة للثلثين مع الأخت الشقيقة صاحبة النصف', 'إجماع الفقهاء قياساً على بنت الابن مع البنت');
        }

        // Asaba identification
        let asabaGroup = null;

        if (active.sons > 0) {
            asabaGroup = {
                type: 'sons_daughters',
                sonsCount: active.sons,
                daughtersCount: active.daughters,
                name: active.daughters > 0 ? `الأبناء (${active.sons}) والبنات (${active.daughters})` : `الأبناء (${active.sons})`,
                dalil: 'سورة النساء: 11 (يوصيكم الله في أولادكم للذكر مثل حظ الأنثيين)',
                reason: active.daughters > 0 ? 'عصبة بالغير (للذكر مثل حظ الأنثيين)' : 'عصبة بالنفس (يحوزون الباقي)'
            };
        } else if (active.sonsSons > 0) {
            asabaGroup = {
                type: 'grandsons_granddaughters',
                sonsCount: active.sonsSons,
                daughtersCount: active.sonsDaughters,
                name: active.sonsDaughters > 0 ? `أبناء الابن (${active.sonsSons}) وبنات الابن (${active.sonsDaughters})` : `أبناء الابن (${active.sonsSons})`,
                dalil: 'السنة النبوية وقاعدة القياس على الأبناء',
                reason: active.sonsDaughters > 0 ? 'عصبة بالغير (للذكر مثل حظ الأنثيين)' : 'عصبة بالنفس'
            };
        } else if (active.father > 0 && !hasMaleBranch) {
            asabaGroup = {
                type: 'father',
                name: 'الأب (عصبة)',
                dalil: 'حديث «ألحقوا الفرائض بأهلها فما بقي فلأولى رجل ذكر»',
                reason: 'عصبة بالنفس يحوز الباقي بعد أصحاب الفروض'
            };
        } else if (active.paternalGrandfather > 0 && !hasMaleBranch && active.father === 0) {
            asabaGroup = {
                type: 'grandfather',
                name: 'الجد لأب (عصبة)',
                dalil: 'حديث «ألحقوا الفرائض بأهلها فما بقي فلأولى رجل ذكر»',
                reason: 'عصبة بالنفس كالأب عند فقده'
            };
        } else if (active.fullBrothers > 0) {
            asabaGroup = {
                type: 'full_siblings',
                sonsCount: active.fullBrothers,
                daughtersCount: active.fullSisters,
                name: active.fullSisters > 0 ? `الإخوة الأشقاء (${active.fullBrothers}) والشقيقات (${active.fullSisters})` : `الإخوة الأشقاء (${active.fullBrothers})`,
                dalil: 'سورة النساء: 176 (وإن كانوا إخوة رجالاً ونساء فللذكر مثل حظ الأنثيين)',
                reason: active.fullSisters > 0 ? 'عصبة بالغير (للذكر مثل حظ الأنثيين)' : 'عصبة بالنفس'
            };
        } else if (fullSisterAsabaWithDaughters) {
            const name = active.fullSisters === 1 ? 'الأخت الشقيقة (عصبة مع الغير)' : `الأخوات الشقيقات (${active.fullSisters}) (عصبة مع الغير)`;
            asabaGroup = {
                type: 'full_sisters_with_daughters',
                sistersCount: active.fullSisters,
                name,
                dalil: 'حديث رسول الله ﷺ: «اجعلوا الأخوات مع البنات عصبة» (صحيح البخاري)',
                reason: 'عصبة مع الغير تأخذ الباقي بعد فروض البنات'
            };
        } else if (active.paternalBrothers > 0) {
            asabaGroup = {
                type: 'paternal_siblings',
                sonsCount: active.paternalBrothers,
                daughtersCount: active.paternalSisters,
                name: active.paternalSisters > 0 ? `الإخوة لأب (${active.paternalBrothers}) والأخوات لأب (${active.paternalSisters})` : `الإخوة لأب (${active.paternalBrothers})`,
                dalil: 'سورة النساء: 176 والقياس على الأشقاء',
                reason: active.paternalSisters > 0 ? 'عصبة بالغير' : 'عصبة بالنفس'
            };
        } else if (paternalSisterAsabaWithDaughters) {
            const name = active.paternalSisters === 1 ? 'الأخت لأب (عصبة مع الغير)' : `الأخوات لأب (${active.paternalSisters}) (عصبة مع الغير)`;
            asabaGroup = {
                type: 'paternal_sisters_with_daughters',
                sistersCount: active.paternalSisters,
                name,
                dalil: 'حديث رسول الله ﷺ: «اجعلوا الأخوات مع البنات عصبة»',
                reason: 'عصبة مع الغير تأخذ الباقي بعد فروض البنات'
            };
        } else if (active.fullNephews > 0) {
            asabaGroup = { type: 'agnate', role: 'fullNephews', name: `أبناء الأخ الشقيق (${active.fullNephews})`, count: active.fullNephews, dalil: 'حديث «ألحقوا الفرائض بأهلها فما بقي فلأولى رجل ذكر»', reason: 'عصبة بالنفس' };
        } else if (active.paternalNephews > 0) {
            asabaGroup = { type: 'agnate', role: 'paternalNephews', name: `أبناء الأخ لأب (${active.paternalNephews})`, count: active.paternalNephews, dalil: 'حديث «ألحقوا الفرائض بأهلها»', reason: 'عصبة بالنفس' };
        } else if (active.fullUncles > 0) {
            asabaGroup = { type: 'agnate', role: 'fullUncles', name: `الأعمام الأشقاء (${active.fullUncles})`, count: active.fullUncles, dalil: 'حديث «ألحقوا الفرائض بأهلها»', reason: 'عصبة بالنفس' };
        } else if (active.paternalUncles > 0) {
            asabaGroup = { type: 'agnate', role: 'paternalUncles', name: `الأعمام لأب (${active.paternalUncles})`, count: active.paternalUncles, dalil: 'حديث «ألحقوا الفرائض بأهلها»', reason: 'عصبة بالنفس' };
        } else if (active.fullCousins > 0) {
            asabaGroup = { type: 'agnate', role: 'fullCousins', name: `أبناء العم الشقيق (${active.fullCousins})`, count: active.fullCousins, dalil: 'حديث «ألحقوا الفرائض بأهلها»', reason: 'عصبة بالنفس' };
        } else if (active.paternalCousins > 0) {
            asabaGroup = { type: 'agnate', role: 'paternalCousins', name: `أبناء العم لأب (${active.paternalCousins})`, count: active.paternalCousins, dalil: 'حديث «ألحقوا الفرائض بأهلها»', reason: 'عصبة بالنفس' };
        }
        
        const fixedShares = results.filter(r => r.den > 1);
        fixedShares.forEach(r => {
            base = inhLcm(base, r.den);
        });

        let sumShares = 0;
        fixedShares.forEach(r => {
            r.shares = (base / r.den) * r.num;
            sumShares += r.shares;
        });

        let finalBase = base;
        let remainderShares = base - sumShares;

        if (sumShares > base) {
            caseType = 'عول (زيادة السهام عن أصل المسألة ونقص الأنصباء بنسبة عادلة)';
            finalBase = sumShares;
            remainderShares = 0;
            fixedShares.forEach(r => {
                r.finalFraction = `${r.shares}/${finalBase}`;
                r.ratio = r.shares / finalBase;
                r.totalAmount = distributableEstate * r.ratio;
                r.unitAmount = r.totalAmount / r.count;
                r.percentage = (r.ratio * 100).toFixed(2);
            });
        } else if (sumShares === base) {
            caseType = 'عادل (استغراق التركة بالكامل لأصحاب الفروض)';
            fixedShares.forEach(r => {
                r.finalFraction = `${r.shares}/${base}`;
                r.ratio = r.shares / base;
                r.totalAmount = distributableEstate * r.ratio;
                r.unitAmount = r.totalAmount / r.count;
                r.percentage = (r.ratio * 100).toFixed(2);
            });
        } else if (sumShares < base && asabaGroup) {
            caseType = 'عصبة (استحقاق العصبة للباقي بعد الفروض)';
            const asabaRatio = remainderShares / base;
            const asabaTotalAmount = distributableEstate * asabaRatio;

            fixedShares.forEach(r => {
                r.finalFraction = `${r.shares}/${base}`;
                r.ratio = r.shares / base;
                r.totalAmount = distributableEstate * r.ratio;
                r.unitAmount = r.totalAmount / r.count;
                r.percentage = (r.ratio * 100).toFixed(2);
            });

            if (asabaGroup.type === 'sons_daughters' || asabaGroup.type === 'grandsons_granddaughters' || asabaGroup.type === 'full_siblings' || asabaGroup.type === 'paternal_siblings') {
                const maleHeads = asabaGroup.sonsCount * 2;
                const femaleHeads = asabaGroup.daughtersCount * 1;
                const totalHeads = maleHeads + femaleHeads;
                const portionValue = totalHeads > 0 ? asabaTotalAmount / totalHeads : 0;

                if (asabaGroup.sonsCount > 0) {
                    const sonsTotal = portionValue * maleHeads;
                    const name = asabaGroup.type === 'sons_daughters' ? `الأبناء (${asabaGroup.sonsCount})` :
                                 asabaGroup.type === 'grandsons_granddaughters' ? `أبناء الابن (${asabaGroup.sonsCount})` :
                                 asabaGroup.type === 'full_siblings' ? `الإخوة الأشقاء (${asabaGroup.sonsCount})` : `الإخوة لأب (${asabaGroup.sonsCount})`;
                    results.push({
                        role: 'asaba_male',
                        name,
                        count: asabaGroup.sonsCount,
                        shareFraction: 'عصبة بالغير',
                        type: 'عصبة',
                        reason: asabaGroup.reason,
                        dalil: asabaGroup.dalil,
                        totalAmount: sonsTotal,
                        unitAmount: sonsTotal / asabaGroup.sonsCount,
                        percentage: distributableEstate > 0 ? ((sonsTotal / distributableEstate) * 100).toFixed(2) : 0
                    });
                }
                if (asabaGroup.daughtersCount > 0) {
                    const daughtersTotal = portionValue * femaleHeads;
                    const name = asabaGroup.type === 'sons_daughters' ? `البنات مع الأبناء (${asabaGroup.daughtersCount})` :
                                 asabaGroup.type === 'grandsons_granddaughters' ? `بنات الابن مع أبناء الابن (${asabaGroup.daughtersCount})` :
                                 asabaGroup.type === 'full_siblings' ? `الشقيقات مع الأشقاء (${asabaGroup.daughtersCount})` : `الأخوات لأب مع الإخوة لأب (${asabaGroup.daughtersCount})`;
                    results.push({
                        role: 'asaba_female',
                        name,
                        count: asabaGroup.daughtersCount,
                        shareFraction: 'عصبة بالغير',
                        type: 'عصبة',
                        reason: 'عصبة بالغير (للذكر مثل حظ الأنثيين مع أخيها)',
                        dalil: asabaGroup.dalil,
                        totalAmount: daughtersTotal,
                        unitAmount: daughtersTotal / asabaGroup.daughtersCount,
                        percentage: distributableEstate > 0 ? ((daughtersTotal / distributableEstate) * 100).toFixed(2) : 0
                    });
                }
            } else if (asabaGroup.type === 'father') {
                const fatherRes = results.find(r => r.role === 'father');
                if (fatherRes) {
                    fatherRes.totalAmount += asabaTotalAmount;
                    fatherRes.unitAmount = fatherRes.totalAmount;
                    fatherRes.percentage = distributableEstate > 0 ? ((fatherRes.totalAmount / distributableEstate) * 100).toFixed(2) : 0;
                    fatherRes.shareFraction = `1/6 فرضاً + الباقي تعصيباً`;
                } else {
                    results.push({
                        role: 'father',
                        name: 'الأب',
                        count: 1,
                        shareFraction: 'عصبة بالنفس',
                        type: 'عصبة',
                        reason: asabaGroup.reason,
                        dalil: asabaGroup.dalil,
                        totalAmount: asabaTotalAmount,
                        unitAmount: asabaTotalAmount,
                        percentage: distributableEstate > 0 ? ((asabaTotalAmount / distributableEstate) * 100).toFixed(2) : 0
                    });
                }
            } else if (asabaGroup.type === 'grandfather') {
                const gfRes = results.find(r => r.role === 'paternalGrandfather');
                if (gfRes) {
                    gfRes.totalAmount += asabaTotalAmount;
                    gfRes.unitAmount = gfRes.totalAmount;
                    gfRes.percentage = distributableEstate > 0 ? ((gfRes.totalAmount / distributableEstate) * 100).toFixed(2) : 0;
                    gfRes.shareFraction = `1/6 فرضاً + الباقي تعصيباً`;
                } else {
                    results.push({
                        role: 'paternalGrandfather',
                        name: 'الجد لأب',
                        count: 1,
                        shareFraction: 'عصبة بالنفس',
                        type: 'عصبة',
                        reason: asabaGroup.reason,
                        dalil: asabaGroup.dalil,
                        totalAmount: asabaTotalAmount,
                        unitAmount: asabaTotalAmount,
                        percentage: distributableEstate > 0 ? ((asabaTotalAmount / distributableEstate) * 100).toFixed(2) : 0
                    });
                }
            } else if (asabaGroup.type === 'full_sisters_with_daughters' || asabaGroup.type === 'paternal_sisters_with_daughters') {
                results.push({
                    role: asabaGroup.type,
                    name: asabaGroup.name,
                    count: asabaGroup.sistersCount,
                    shareFraction: 'عصبة مع الغير',
                    type: 'عصبة مع الغير',
                    reason: asabaGroup.reason,
                    dalil: asabaGroup.dalil,
                    totalAmount: asabaTotalAmount,
                    unitAmount: asabaTotalAmount / asabaGroup.sistersCount,
                    percentage: distributableEstate > 0 ? ((asabaTotalAmount / distributableEstate) * 100).toFixed(2) : 0
                });
            } else if (asabaGroup.type === 'agnate') {
                results.push({
                    role: asabaGroup.role,
                    name: asabaGroup.name,
                    count: asabaGroup.count,
                    shareFraction: 'عصبة بالنفس',
                    type: 'عصبة بالنفس',
                    reason: asabaGroup.reason,
                    dalil: asabaGroup.dalil,
                    totalAmount: asabaTotalAmount,
                    unitAmount: asabaTotalAmount / asabaGroup.count,
                    percentage: distributableEstate > 0 ? ((asabaTotalAmount / distributableEstate) * 100).toFixed(2) : 0
                });
            }
        } else if (sumShares < base && !asabaGroup) {
            caseType = 'رد (رد الفائض من التركة على أصحاب الفروض بنسبة سهامهم)';
            const spouseShare = results.find(r => r.role === 'husband' || r.role === 'wives');
            const nonSpouseShares = results.filter(r => r.role !== 'husband' && r.role !== 'wives');

            if (spouseShare && nonSpouseShares.length > 0) {
                spouseShare.ratio = spouseShare.shares / base;
                spouseShare.totalAmount = distributableEstate * spouseShare.ratio;
                spouseShare.unitAmount = spouseShare.totalAmount / spouseShare.count;
                spouseShare.percentage = (spouseShare.ratio * 100).toFixed(2);
                spouseShare.finalFraction = spouseShare.shareFraction;

                const estateForNonSpouses = distributableEstate - spouseShare.totalAmount;
                const nonSpouseSumShares = nonSpouseShares.reduce((s, r) => s + r.shares, 0);

                nonSpouseShares.forEach(r => {
                    r.raddRatio = r.shares / nonSpouseSumShares;
                    r.totalAmount = estateForNonSpouses * r.raddRatio;
                    r.unitAmount = r.totalAmount / r.count;
                    r.percentage = distributableEstate > 0 ? ((r.totalAmount / distributableEstate) * 100).toFixed(2) : 0;
                    r.finalFraction = `${r.shareFraction} + رداً`;
                    r.reason += ' (مع زيادة الرد لعدم وجود عصبة)';
                });
            } else if (!spouseShare && nonSpouseShares.length > 0) {
                finalBase = sumShares;
                nonSpouseShares.forEach(r => {
                    r.ratio = r.shares / finalBase;
                    r.totalAmount = distributableEstate * r.ratio;
                    r.unitAmount = r.totalAmount / r.count;
                    r.percentage = (r.ratio * 100).toFixed(2);
                    r.finalFraction = `${r.shares}/${finalBase} (فرضاً ورداً)`;
                    r.reason += ' (مع زيادة الرد لعدم وجود عصبة)';
                });
            } else if (spouseShare && nonSpouseShares.length === 0) {
                spouseShare.totalAmount = distributableEstate;
                spouseShare.unitAmount = distributableEstate / spouseShare.count;
                spouseShare.percentage = '100.00';
                spouseShare.finalFraction = `${spouseShare.shareFraction} فرضاً والباقي رداً`;
                spouseShare.reason += ' (فرضاً ورداً لانفراد الزوجية وعدم وجود وارث آخر وفق قوانين الأحوال الشخصية المعاصرة)';
            }
        }
    }

    if (isUmariyyaHusband || isUmariyyaWife) {
        results.forEach(r => {
            r.totalAmount = distributableEstate * (r.num / r.den);
            r.unitAmount = r.totalAmount / r.count;
            r.percentage = ((r.num / r.den) * 100).toFixed(2);
        });
    }

    // Sort results logically: Spouses, Parents/Grandparents, Children/Grandchildren, Siblings, Agnates
    const rolePriority = {
        'husband': 1, 'wives': 1,
        'father': 2, 'mother': 3, 'paternalGrandfather': 4, 'grandmothers': 5,
        'daughters': 6, 'sonsDaughters': 7,
        'asaba_male': 8, 'asaba_female': 9,
        'maternalSiblings': 10, 'fullSisters': 11, 'paternalSisters': 12,
        'full_sisters_with_daughters': 13, 'paternal_sisters_with_daughters': 14
    };

    results.sort((a, b) => (rolePriority[a.role] || 20) - (rolePriority[b.role] || 20));

    return {
        financials: {
            grossEstate: estate,
            funeral,
            debts,
            bequestReq,
            actualBequest,
            bequestTruncated,
            distributableEstate,
            currency
        },
        caseType,
        base,
        results,
        blocked
    };
}

/**
 * UI Controller and Interactive Methods
 */
function setDeceasedGender(gender) {
    window.inheritanceData.deceasedGender = gender;
    const btnMale = document.getElementById('inh-btn-gender-male');
    const btnFemale = document.getElementById('inh-btn-gender-female');
    const wivesBox = document.getElementById('inh-card-wives') || document.getElementById('inh-box-wives');
    const husbandBox = document.getElementById('inh-card-husband') || document.getElementById('inh-box-husband');

    if (gender === 'male') {
        if (btnMale) {
            btnMale.className = 'gender-toggle-card gender-active-male p-6 rounded-3xl border-2 border-emerald-500 bg-emerald-500/20 flex items-center justify-center gap-4 shadow-xl';
            const checkIcon = btnMale.querySelector('i');
            if (checkIcon) checkIcon.classList.remove('hidden');
        }
        if (btnFemale) {
            btnFemale.className = 'gender-toggle-card p-6 rounded-3xl border-2 border-white/10 bg-slate-900/90 flex items-center justify-center gap-4 shadow-xl text-slate-400';
            const checkIcon = btnFemale.querySelector('i');
            if (checkIcon) checkIcon.classList.add('hidden');
        }
        if (wivesBox) wivesBox.classList.remove('hidden');
        if (husbandBox) {
            husbandBox.classList.add('hidden');
            const hInp = document.getElementById('inh-inp-husband');
            if (hInp) hInp.value = 0;
            highlightCounterCard('husband');
        }
    } else {
        if (btnFemale) {
            btnFemale.className = 'gender-toggle-card gender-active-female p-6 rounded-3xl border-2 border-rose-500 bg-rose-500/20 flex items-center justify-center gap-4 shadow-xl';
            const checkIcon = btnFemale.querySelector('i');
            if (checkIcon) checkIcon.classList.remove('hidden');
        }
        if (btnMale) {
            btnMale.className = 'gender-toggle-card p-6 rounded-3xl border-2 border-white/10 bg-slate-900/90 flex items-center justify-center gap-4 shadow-xl text-slate-400';
            const checkIcon = btnMale.querySelector('i');
            if (checkIcon) checkIcon.classList.add('hidden');
        }
        if (husbandBox) husbandBox.classList.remove('hidden');
        if (wivesBox) {
            wivesBox.classList.add('hidden');
            const wInp = document.getElementById('inh-inp-wives');
            if (wInp) wInp.value = 0;
            highlightCounterCard('wives');
        }
    }
}

function adjustInhCounter(key, delta) {
    const input = document.getElementById('inh-inp-' + key);
    if (!input) return;
    let val = parseInt(input.value || 0) + delta;
    if (val < 0) val = 0;
    if (key === 'husband' || key === 'father' || key === 'mother' || key === 'paternalGrandfather' || key === 'paternalGrandmother' || key === 'maternalGrandmother') {
        if (val > 1) val = 1;
    }
    if (key === 'wives' && val > 4) val = 4;
    input.value = val;
    highlightCounterCard(key);
}

function highlightCounterCard(key) {
    const card = document.getElementById('inh-card-' + key) || document.getElementById('inh-box-' + key);
    if (!card) return;
    const input = document.getElementById('inh-inp-' + key);
    const val = parseInt(input ? input.value : 0);
    if (val > 0) {
        card.classList.add('active-heir-card');
    } else {
        card.classList.remove('active-heir-card');
    }
}

function resetInheritanceForm() {
    INH_HEIR_KEYS.forEach(k => {
        const inp = document.getElementById('inh-inp-' + k);
        if (inp) inp.value = 0;
        highlightCounterCard(k);
    });
    ['grossEstate', 'funeral', 'debts', 'bequest'].forEach(k => {
        const inp = document.getElementById('inh-inp-' + k);
        if (inp) inp.value = '';
    });
    setDeceasedGender('male');
    const resSec = document.getElementById('inh-results-container');
    if (resSec) resSec.classList.add('hidden');
    window.inheritanceData.lastResult = null;
}

function applyInhPreset(preset) {
    resetInheritanceForm();
    const grossInp = document.getElementById('inh-inp-grossEstate');
    if (grossInp && !grossInp.value) grossInp.value = '1000000';

    if (preset === 'standard_family') {
        setDeceasedGender('male');
        const w = document.getElementById('inh-inp-wives'); if (w) w.value = 1;
        const s = document.getElementById('inh-inp-sons'); if (s) s.value = 2;
        const d = document.getElementById('inh-inp-daughters'); if (d) d.value = 2;
        const m = document.getElementById('inh-inp-mother'); if (m) m.value = 1;
        const f = document.getElementById('inh-inp-father'); if (f) f.value = 1;
    } else if (preset === 'umariyyatan') {
        setDeceasedGender('female');
        const h = document.getElementById('inh-inp-husband'); if (h) h.value = 1;
        const m = document.getElementById('inh-inp-mother'); if (m) m.value = 1;
        const f = document.getElementById('inh-inp-father'); if (f) f.value = 1;
    } else if (preset === 'awl_minbariyya') {
        setDeceasedGender('male');
        const w = document.getElementById('inh-inp-wives'); if (w) w.value = 1;
        const d = document.getElementById('inh-inp-daughters'); if (d) d.value = 2;
        const f = document.getElementById('inh-inp-father'); if (f) f.value = 1;
        const m = document.getElementById('inh-inp-mother'); if (m) m.value = 1;
    } else if (preset === 'radd_case') {
        setDeceasedGender('male');
        const d = document.getElementById('inh-inp-daughters'); if (d) d.value = 1;
        const m = document.getElementById('inh-inp-mother'); if (m) m.value = 1;
        const gm = document.getElementById('inh-inp-maternalGrandmother'); if (gm) gm.value = 0;
    } else if (preset === 'kalalah_siblings') {
        setDeceasedGender('male');
        const fb = document.getElementById('inh-inp-fullBrothers'); if (fb) fb.value = 2;
        const fs = document.getElementById('inh-inp-fullSisters'); if (fs) fs.value = 3;
    }

    INH_HEIR_KEYS.forEach(k => highlightCounterCard(k));
    runIslamicInheritanceCalculation();
}

function runIslamicInheritanceCalculation() {
    const grossVal = parseFloat(document.getElementById('inh-inp-grossEstate')?.value || 0);
    const debtsVal = parseFloat(document.getElementById('inh-inp-debts')?.value || 0);
    const funeralVal = parseFloat(document.getElementById('inh-inp-funeral')?.value || 0);
    const bequestVal = parseFloat(document.getElementById('inh-inp-bequest')?.value || 0);
    const currVal = document.getElementById('inh-inp-currency')?.value || 'SAR';

    const input = {
        grossEstate: grossVal,
        debts: debtsVal,
        funeral: funeralVal,
        bequest: bequestVal,
        currency: currVal,
        deceasedGender: window.inheritanceData.deceasedGender
    };

    INH_HEIR_KEYS.forEach(k => {
        const inp = document.getElementById('inh-inp-' + k);
        input[k] = inp ? parseInt(inp.value || 0) : 0;
    });

    // Check if any heir was selected
    const totalHeirsCount = INH_HEIR_KEYS.reduce((sum, k) => sum + (input[k] || 0), 0);
    if (totalHeirsCount === 0) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'تنبيه',
                text: 'يرجى تحديد الورثة المستحقين بإدخال عددهم أولاً.',
                confirmButtonColor: '#d97706'
            });
        } else {
            alert('يرجى تحديد الورثة المستحقين بإدخال عددهم أولاً.');
        }
        return;
    }

    const output = calculateIslamicInheritance(input);
    window.inheritanceData.lastResult = output;
    renderInheritanceResults(output);
}

function renderInheritanceResults(out) {
    const resSec = document.getElementById('inh-results-container');
    if (!resSec) return;
    resSec.classList.remove('hidden');

    const curr = out.financials.currency;
    const formatMoney = (n) => {
        return Math.round(n).toLocaleString('ar-SA') + ' ' + curr;
    };

    // Financial Cards
    const elGross = document.getElementById('inh-res-gross');
    if (elGross) elGross.innerText = formatMoney(out.financials.grossEstate);
    
    const elDeduct = document.getElementById('inh-res-deductions');
    const totalDeductions = out.financials.funeral + out.financials.debts + out.financials.actualBequest;
    if (elDeduct) elDeduct.innerText = formatMoney(totalDeductions);

    const elDist = document.getElementById('inh-res-distributable');
    if (elDist) elDist.innerText = formatMoney(out.financials.distributableEstate);

    const elCase = document.getElementById('inh-res-casetype');
    if (elCase) elCase.innerText = out.caseType;

    const elBase = document.getElementById('inh-res-base');
    if (elBase) elBase.innerText = out.base || 1;

    // Visual Distribution Multi-Bar
    const barContainer = document.getElementById('inh-distribution-bar');
    if (barContainer) {
        const colors = [
            'bg-amber-500', 'bg-emerald-500', 'bg-blue-500', 'bg-purple-500', 
            'bg-rose-500', 'bg-teal-500', 'bg-indigo-500', 'bg-cyan-500', 'bg-orange-500'
        ];
        let barHtml = '';
        out.results.forEach((r, idx) => {
            const c = colors[idx % colors.length];
            const p = parseFloat(r.percentage);
            if (p > 0) {
                barHtml += `<div class="${c} h-full transition-all duration-500 flex items-center justify-center text-[10px] text-white font-bold truncate px-1" style="width: ${p}%;" title="${r.name}: ${p}%">${p >= 8 ? r.name + ' ' + p + '%' : ''}</div>`;
            }
        });
        barContainer.innerHTML = barHtml;
    }

    // Results Table
    const tbody = document.getElementById('inh-table-body');
    if (tbody) {
        let rowsHtml = '';
        out.results.forEach((r, idx) => {
            const badgeClass = r.type.includes('فرض') ? 'bg-amber-500/20 text-amber-300 border-amber-500/40' :
                               r.type.includes('عصبة') ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' :
                               'bg-blue-500/20 text-blue-300 border-blue-500/40';

            rowsHtml += `
            <tr class="border-b border-white/10 hover:bg-white/[0.04] transition-colors">
                <td class="p-4 font-semibold text-slate-100 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-slate-800 border border-amber-500/30 flex items-center justify-center text-sm text-amber-300 font-black shadow-sm">${idx + 1}</span>
                    <div>
                        <div class="text-base sm:text-lg font-black text-white">${r.name}</div>
                        <div class="text-xs text-amber-300/80 font-bold">${r.count > 1 ? 'العدد: ' + r.count + ' ورثة' : 'مستحق منفرد'}</div>
                    </div>
                </td>
                <td class="p-4 text-center">
                    <span class="inline-block px-3.5 py-1 rounded-xl text-sm font-black border ${badgeClass}">${r.finalFraction || r.shareFraction}</span>
                    <div class="text-xs text-slate-300 font-bold mt-1">${r.type}</div>
                </td>
                <td class="p-4 text-center font-black text-emerald-400 font-mono text-base sm:text-lg">${r.percentage}%</td>
                <td class="p-4 text-center font-black text-amber-300 font-mono text-lg sm:text-2xl">${formatMoney(r.totalAmount)}</td>
                <td class="p-4 text-center font-black text-cyan-300 font-mono text-base sm:text-lg">
                    ${r.count > 1 ? formatMoney(r.unitAmount) : '—'}
                </td>
                <td class="p-4 text-right">
                    <div class="text-xs sm:text-sm font-bold text-slate-200">${r.reason}</div>
                    <div class="text-xs text-amber-300/90 mt-1.5 flex items-center gap-1.5 font-serif font-bold"><i class="fa-solid fa-book-quran text-amber-400"></i> ${r.dalil}</div>
                </td>
            </tr>`;
        });
        tbody.innerHTML = rowsHtml;
    }

    // Excluded / Blocked Heirs Alert Box
    const blockedBox = document.getElementById('inh-blocked-container');
    const blockedList = document.getElementById('inh-blocked-list');
    if (blockedBox && blockedList) {
        const blockedKeys = Object.keys(out.blocked);
        if (blockedKeys.length > 0) {
            blockedBox.classList.remove('hidden');
            let bHtml = '';
            const heirNamesAr = {
                sonsSons: 'أبناء الابن', sonsDaughters: 'بنات الابن',
                paternalGrandfather: 'الجد لأب', paternalGrandmother: 'الجدة لأب', maternalGrandmother: 'الجدة لأم',
                fullBrothers: 'الإخوة الأشقاء', fullSisters: 'الأخوات الشقيقات',
                paternalBrothers: 'الإخوة لأب', paternalSisters: 'الأخوات لأب',
                maternalSiblings: 'الإخوة لأم', fullNephews: 'أبناء الأخ الشقيق',
                paternalNephews: 'أبناء الأخ لأب', fullUncles: 'الأعمام الأشقاء',
                paternalUncles: 'الأعمام لأب', fullCousins: 'أبناء العم الشقيق', paternalCousins: 'أبناء العم لأب'
            };

            blockedKeys.forEach(k => {
                const name = heirNamesAr[k] || k;
                bHtml += `
                <div class="flex items-start gap-2 bg-rose-950/30 border border-rose-800/40 p-2.5 rounded-xl text-xs text-rose-200">
                    <i class="fa-solid fa-ban text-rose-400 mt-0.5"></i>
                    <div>
                        <span class="font-bold text-rose-300">${name}:</span>
                        <span class="text-slate-300">${out.blocked[k]}</span>
                    </div>
                </div>`;
            });
            blockedList.innerHTML = bHtml;
        } else {
            blockedBox.classList.add('hidden');
        }
    }

    // Scroll smoothly to results
    resSec.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Print & Export Formatter
 */
function printIslamicInheritanceReport() {
    if (!window.inheritanceData.lastResult) {
        alert('يرجى حساب الميراث أولاً لتتمكن من الطباعة.');
        return;
    }
    const out = window.inheritanceData.lastResult;
    const curr = out.financials.currency;
    const formatMoney = (n) => Math.round(n).toLocaleString('ar-SA') + ' ' + curr;

    let rowsHtml = '';
    out.results.forEach((r, idx) => {
        rowsHtml += `
        <tr>
            <td style="padding:10px; border:1px solid #ddd; text-align:center; font-weight:bold;">${idx + 1}</td>
            <td style="padding:10px; border:1px solid #ddd; font-weight:bold;">${r.name}</td>
            <td style="padding:10px; border:1px solid #ddd; text-align:center;">${r.finalFraction || r.shareFraction} (${r.type})</td>
            <td style="padding:10px; border:1px solid #ddd; text-align:center; font-weight:bold;">${r.percentage}%</td>
            <td style="padding:10px; border:1px solid #ddd; text-align:center; font-weight:bold; color:#047857;">${formatMoney(r.totalAmount)}</td>
            <td style="padding:10px; border:1px solid #ddd; text-align:center;">${r.count > 1 ? formatMoney(r.unitAmount) : '—'}</td>
            <td style="padding:10px; border:1px solid #ddd; font-size:12px; color:#4b5563;">${r.reason} - <span style="color:#b45309; font-weight:bold;">${r.dalil}</span></td>
        </tr>`;
    });

    let blockedHtml = '';
    const blockedKeys = Object.keys(out.blocked);
    if (blockedKeys.length > 0) {
        blockedHtml += '<div style="margin-top:20px; padding:12px; background:#fff1f2; border:1px solid #fecdd3; border-radius:8px;">';
        blockedHtml += '<h4 style="margin:0 0 8px 0; color:#be123c;">⛔ الورثة المحجوبون شرعاً وسبب الحجب:</h4><ul style="margin:0; padding-right:20px; font-size:13px; color:#4c0519;">';
        blockedKeys.forEach(k => {
            blockedHtml += `<li><strong>${k}:</strong> ${out.blocked[k]}</li>`;
        });
        blockedHtml += '</ul></div>';
    }

    const printWin = window.open('', '_blank');
    printWin.document.write(`
    <!DOCTYPE html>
    <html dir="rtl" lang="ar">
    <head>
        <meta charset="UTF-8">
        <title>وثيقة حصر وتوزيع التركة الشرعية</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 25px; color: #1e293b; line-height: 1.6; }
            .header { text-align: center; border-bottom: 3px double #d97706; padding-bottom: 15px; margin-bottom: 20px; }
            .header h1 { margin: 0; color: #b45309; font-size: 24px; }
            .header p { margin: 5px 0 0 0; color: #64748b; font-size: 14px; }
            .stat-box { display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
            th { background: #f1f5f9; padding: 10px; border: 1px solid #cbd5e1; text-align: center; }
            .footer { margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 15px; display: flex; justify-content: space-between; font-size: 12px; color: #64748b; }
            @media print {
                body { padding: 0; }
                button { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div style="font-size:20px; margin-bottom:5px;">﷽</div>
            <h1>وثيقة حصر وتوزيع التركة الشرعية</h1>
            <p>مستخرج رسمي وتوثيقي وفق أحكام الشريعة الإسلامية وعلم الفرائض والمواريث</p>
            <p>تاريخ الاستخراج: ${new Date().toLocaleDateString('ar-SA')} | نظام السجانة لإدارة الأعمال (ELSAJANA2)</p>
        </div>

        <div class="stat-box">
            <div><strong>إجمالي التركة:</strong> ${formatMoney(out.financials.grossEstate)}</div>
            <div><strong>مؤن التجهيز والديون والوصية:</strong> ${formatMoney(out.financials.funeral + out.financials.debts + out.financials.actualBequest)}</div>
            <div><strong>صافي التركة الموزعة:</strong> <span style="color:#047857; font-weight:bold;">${formatMoney(out.financials.distributableEstate)}</span></div>
            <div><strong>نوع المسألة:</strong> ${out.caseType}</div>
            <div><strong>أصل المسألة:</strong> ${out.base}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الوارث المستحق</th>
                    <th>الفرض الشرعي</th>
                    <th>النسبة %</th>
                    <th>المبلغ الإجمالي المستحق</th>
                    <th>نصيب الفرد</th>
                    <th>السند والدليل الشرعي</th>
                </tr>
            </thead>
            <tbody>
                ${rowsHtml}
            </tbody>
        </table>

        ${blockedHtml}

        <div class="footer">
            <div>التوقيع / المصادق الشرعي: .......................................</div>
            <div>الختم الرسمي: .......................................</div>
        </div>

        <script>
            window.onload = function() {
                setTimeout(function() { window.print(); }, 500);
            };
        </script>
    </body>
    </html>`);
    printWin.document.close();
}

/**
 * WhatsApp Formatter
 */
function shareIslamicInheritanceWhatsApp() {
    if (!window.inheritanceData.lastResult) {
        alert('يرجى حساب الميراث أولاً.');
        return;
    }
    const out = window.inheritanceData.lastResult;
    const curr = out.financials.currency;
    const formatMoney = (n) => Math.round(n).toLocaleString('ar-SA') + ' ' + curr;

    let text = `*⚖️ تقرير توزيع الميراث الشرعي - نظام السجانة ⚖️*\n\n`;
    text += `💰 *إجمالي التركة:* ${formatMoney(out.financials.grossEstate)}\n`;
    text += `📉 *الديون والوصية والمؤن:* ${formatMoney(out.financials.funeral + out.financials.debts + out.financials.actualBequest)}\n`;
    text += `💎 *صافي التركة للتوزيع:* ${formatMoney(out.financials.distributableEstate)}\n`;
    text += `📜 *تكييف المسألة:* ${out.caseType} (الأصل: ${out.base})\n\n`;
    text += `*قائمة الورثة والأنصبة الشرعية:*\n`;

    out.results.forEach((r, idx) => {
        text += `${idx + 1}. *${r.name}* (${r.finalFraction || r.shareFraction})\n`;
        text += `   ▫️ النسبة: ${r.percentage}%\n`;
        text += `   ▫️ المبلغ الإجمالي: ${formatMoney(r.totalAmount)}\n`;
        if (r.count > 1) {
            text += `   ▫️ نصيب الفرد الواحد: ${formatMoney(r.unitAmount)}\n`;
        }
        text += `   ▫️ الدليل: ${r.dalil}\n\n`;
    });

    const blockedKeys = Object.keys(out.blocked);
    if (blockedKeys.length > 0) {
        text += `*⛔ الورثة المحجوبون شرعاً:*\n`;
        blockedKeys.forEach(k => {
            text += `• ${k}: ${out.blocked[k]}\n`;
        });
        text += '\n';
    }

    text += `_تم استخراج هذا التوزيع بدقة علمية وفق فقه المواريث المعتمد._`;

    const encoded = encodeURI(text);
    window.open(`https://api.whatsapp.com/send?text=${encoded}`, '_blank');
}

/**
 * Copy Summary to Clipboard
 */
function copyIslamicInheritanceSummary() {
    if (!window.inheritanceData.lastResult) {
        alert('يرجى حساب الميراث أولاً.');
        return;
    }
    const out = window.inheritanceData.lastResult;
    const curr = out.financials.currency;
    const formatMoney = (n) => Math.round(n).toLocaleString('ar-SA') + ' ' + curr;

    let text = `⚖️ ملخص توزيع الميراث الشرعي ⚖️\n\n`;
    text += `إجمالي التركة: ${formatMoney(out.financials.grossEstate)}\n`;
    text += `الصافي الموزع: ${formatMoney(out.financials.distributableEstate)}\n\n`;
    out.results.forEach((r, idx) => {
        text += `${idx + 1}. ${r.name}: ${formatMoney(r.totalAmount)} (${r.percentage}%) - الفرض: ${r.finalFraction || r.shareFraction}\n`;
    });

    navigator.clipboard.writeText(text).then(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'تم النسخ بنجاح!',
                text: 'تم نسخ ملخص الميراث إلى الحافظة.',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert('تم نسخ الملخص بنجاح!');
        }
    }).catch(err => {
        alert('تعذر النسخ: ' + err);
    });
}

// Initialization function called when suite is selected
function initInheritanceEngine() {
    console.log('⚖️ Islamic Inheritance Engine initialized.');
    setDeceasedGender(window.inheritanceData.deceasedGender || 'male');
    const grossInp = document.getElementById('inh-inp-grossEstate');
    if (grossInp && !grossInp.hasAttribute('data-inh-listener')) {
        grossInp.setAttribute('data-inh-listener', 'true');
        grossInp.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') runIslamicInheritanceCalculation();
        });
    }
    // Update counter card highlights on init
    if (typeof INH_HEIR_KEYS !== 'undefined') {
        INH_HEIR_KEYS.forEach(k => highlightCounterCard(k));
    }
}

const HEIR_EVIDENCES = {
    husband: {
        title: "ميراث الزوج",
        icon: '<i class="fa-solid fa-ring text-emerald-300"></i>',
        fard: "النصف أو الربع فرضاً (لا يُحجب حجب حرمان أبداً)",
        quranVerse: "وَلَكُمْ نِصْفُ مَا تَرَكَ أَزْوَاجُكُمْ إِنْ لَمْ يَكُنْ لَهُنَّ وَلَدٌ فَإِنْ كَانَ لَهُنَّ وَلَدٌ فَلَكُمُ الرُّبُعُ مِمَّا تَرَكْنَ مِنْ بَعْدِ وَصِيَّةٍ يُوصِينَ بِهَا أَوْ دَيْنٍ",
        quranRef: "سورة النساء: الآية 12",
        hadithText: "«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لأَوْلَى رَجُلٍ ذَكَرٍ»",
        hadithSource: "صحيح البخاري (6732)، صحيح مسلم (1614) من حديث عبد الله بن عباس رضي الله عنهما",
        fiqhRules: [
            "النصف (1/2) فرضاً: عند عدم وجود الفرع الوارث للزوجة المتوفاة مطلقاً (سواء منه أو من غيره).",
            "الربع (1/4) فرضاً: عند وجود الفرع الوارث للزوجة (الابن، البنت، ابن الابن، بنت الابن وإن نزلوا).",
            "الزوج وارث بالفرض فقط، ولا يحجب حجب حرمان أبداً بإجماع علماء المسلمين."
        ]
    },
    wives: {
        title: "ميراث الزوجة / الزوجات",
        icon: '<i class="fa-solid fa-gem text-emerald-300"></i>',
        fard: "الربع أو الثمن فرضاً (يقتسمنه بالسوية عند التعدد)",
        quranVerse: "وَلَهُنَّ الرُّبُعُ مِمَّا تَرَكْتُمْ إِنْ لَمْ يَكُنْ لَكُمْ وَلَدٌ فَإِنْ كَانَ لَكُمْ وَلَدٌ فَلَهُنَّ الثُّمُنُ مِمَّا تَرَكْتُمْ مِنْ بَعْدِ وَصِيَّةٍ تُوصُونَ بِهَا أَوْ دَيْنٍ",
        quranRef: "سورة النساء: الآية 12",
        hadithText: "«أَعْطِ ابْنَتَيْ سَعْدٍ الثُّلُثَيْنِ، وَأَعْطِ أُمَّهُمَا الثُّمُنَ، وَمَا بَقِيَ فَهُوَ لَكَ»",
        hadithSource: "صحيح البخاري، سنن أبي داود (2892)، سنن الترمذي (2092) وصححه الألباني",
        fiqhRules: [
            "الربع (1/4) فرضاً: عند عدم وجود الفرع الوارث للزوج المتوفى مطلقاً (سواء منها أو من غيرها).",
            "الثمن (1/8) فرضاً: عند وجود الفرع الوارث للزوج المتوفى (ابن، بنت، ابن ابن، بنت ابن).",
            "الزوجات يشتركن في الربع أو الثمن بالتساوي، ولا تزيد حصتهن بزيادة عددهن بإجماع الفقهاء."
        ]
    },
    father: {
        title: "ميراث الأب",
        icon: '<i class="fa-solid fa-tree text-amber-300"></i>',
        fard: "السدس فرضاً، أو السدس مع التعصيب، أو التعصيب المحض",
        quranVerse: "وَلأَبَوَيْهِ لِكُلِّ وَاحِدٍ مِنْهُمَا السُّدُسُ مِمَّا تَرَكَ إِنْ كَانَ لَهُ وَلَدٌ فَإِنْ لَمْ يَكُنْ لَهُ وَلَدٌ وَوَرِثَهُ أَبَوَاهُ فَلأُمِّهِ الثُّلُثُ",
        quranRef: "سورة النساء: الآية 11",
        hadithText: "«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لأَوْلَى رَجُلٍ ذَكَرٍ»",
        hadithSource: "صحيح البخاري (6732)، صحيح مسلم (1614) من حديث عبد الله بن عباس رضي الله عنهما",
        fiqhRules: [
            "السدس (1/6) فرضاً فقط: عند وجود الفرع الوارث المذكر (الابن أو ابن الابن).",
            "السدس فرضاً + الباقي تعصيباً: عند وجود الفرع الوارث المؤنث فقط (البنت أو بنت الابن).",
            "التعصيب المحض (العصبة بالنفس): يحوز كامل التركة أو ما بقي بعد الفروض عند انعدام الفرع الوارث تماماً.",
            "الأب يحجب جميع الأجداد والجدات من جهته وجميع الإخوة والأخوات حجب حرمان تام بإجماع الأمة."
        ]
    },
    mother: {
        title: "ميراث الأم",
        icon: '<i class="fa-solid fa-heart text-amber-300"></i>',
        fard: "الثلث كاملاً، أو السدس، أو ثلث الباقي (المسألة الغراوية / العمريتان)",
        quranVerse: "فَإِنْ لَمْ يَكُنْ لَهُ وَلَدٌ وَوَرِثَهُ أَبَوَاهُ فَلأُمِّهِ الثُّلُثُ فَإِنْ كَانَ لَهُ إِخْوَةٌ فَلأُمِّهِ السُّدُسُ مِنْ بَعْدِ وَصِيَّةٍ يُوصِي بِهَا أَوْ دَيْنٍ",
        quranRef: "سورة النساء: الآية 11",
        hadithText: "«قضى عمر بن الخطاب وعثمان بن عفان وعلي بن أبي طالب وزيد بن ثابت وعبد الله بن مسعود رضي الله عنهم للأم بثلث الباقي في العمريتين»",
        hadithSource: "مصنف ابن أبي شيبة، السنن الكبرى للبيهقي، إجماع الصحابة",
        fiqhRules: [
            "الثلث (1/3) كاملاً: عند انعدام الفرع الوارث وانعدام الجمع من الإخوة (اثنان فأكثر).",
            "السدس (1/6): عند وجود الفرع الوارث أو وجود اثنين فأكثر من الإخوة والأخوات مطلقاً.",
            "ثلث الباقي (المسألتان العمريتان): إذا انحصر الورثة في (زوج + أم + أب) أو (زوجة + أم + أب) تأخذ ثلث ما بقي بعد نصيب أحد الزوجين ليأخذ الأب ضعفيها.",
            "الأم لا تُحجب حجب حرمان أبداً، وتَحجب جميع الجدات من كل الجهات."
        ]
    },
    paternalGrandfather: {
        title: "ميراث الجد لأب (أبو الأب)",
        icon: '<i class="fa-solid fa-landmark text-amber-300"></i>',
        fard: "السدس فرضاً أو التعصيب (ينزل منزلة الأب عند عدم وجود الأب)",
        quranVerse: "يُوصِيكُمُ اللَّهُ فِي أَوْلادِكُمْ لِلذَّكَرِ مِثْلُ حَظِّ الأُنْثَيَيْنِ ... وَلأَبَوَيْهِ لِكُلِّ وَاحِدٍ مِنْهُمَا السُّدُسُ",
        quranRef: "سورة النساء: الآية 11 (الجد أب عند عدم الأب)",
        hadithText: "«أَنَّ رَجُلاً أَتَى النَّبِيَّ ﷺ فَقَالَ: إِنَّ ابْنَ ابْنِي مَاتَ فَمَا لِي مِنْ مِيرَاثِهِ؟ فَقَالَ: لَكَ السُّدُسُ، فَلَمَّا وَلَّى دَعَاهُ فَقَالَ: لَكَ سُدُسٌ آخَرُ، فَلَمَّا وَلَّى دَعَاهُ فَقَالَ: إِنَّ السُّدُسَ الآخَرَ طُعْمَةٌ لَكَ»",
        hadithSource: "سنن أبي داود (2896)، سنن الترمذي (2098) وصححه الألباني",
        fiqhRules: [
            "يرث كالأب تماماً عند عدم وجود الأب: السدس مع الفرع المذكر، السدس + التعصيب مع الفرع المؤنث، التعصيب المحض عند عدم الفرع الوارث.",
            "يُحجب حجب حرمان تام بوجود الأب أو بوجود جد أقرب منه للمتوفى."
        ]
    },
    paternalGrandmother: {
        title: "ميراث الجدة لأب (أم الأب)",
        icon: '<i class="fa-solid fa-building-columns text-amber-300"></i>',
        fard: "السدس فرضاً (تقتسمه مع الجدة لأم عند الاجتماع)",
        quranVerse: "«السنة قاضية على بيان الفروض المجملة للجدات بالسدس»",
        quranRef: "السنة النبوية وإجماع الصحابة",
        hadithText: "«جَاءَتِ الْجَدَّةُ إِلَى أَبِي بَكْرٍ الصِّدِّيقِ تَسْأَلُهُ مِيرَاثَهَا، فَقَالَ: مَا لَكِ فِي كِتَابِ اللَّهِ شَيْءٌ، حَتَّى شَهِدَ الْمُغِيرَةُ بْنُ شُعْبَةَ وَمُحَمَّدُ بْنُ مَسْلَمَةَ أَنَّ رَسُولَ اللَّهِ ﷺ أَعْطَاهَا السُّدُسَ فَأَنْفَذَهُ لَهَا»",
        hadithSource: "سنن أبي داود (2894)، سنن الترمذي (2101) وصححه",
        fiqhRules: [
            "ترث السدس فرضاً إذا كانت غير محجوبة.",
            "تُحجب بالأم حجب حرمان باتفاق، وتُحجب بالأب (ابنها)، وبالجد الأدنى منها."
        ]
    },
    maternalGrandmother: {
        title: "ميراث الجدة لأم (أم الأم)",
        icon: '<i class="fa-solid fa-crown text-amber-300"></i>',
        fard: "السدس فرضاً (أول من قضى لها النبي ﷺ بالسدس)",
        quranVerse: "«قضاء رسول الله ﷺ للجدة بالسدس الثابت بالسنة الصحيحة»",
        quranRef: "صحيح السنة النبوية المطهرة",
        hadithText: "«أَنَّ رَسُولَ اللَّهِ ﷺ قَضَى لِلْجَدَّةِ بِالسُّدُسِ إِذَا لَمْ يَكُنْ دُونَهَا أُمٌّ»",
        hadithSource: "موطأ مالك، سنن أبي داود (2895)، سنن النسائي وصححه الحاكم والذهبي",
        fiqhRules: [
            "ترث السدس فرضاً عند عدم وجود الأم.",
            "تُحجب بالأم فقط ولا تُحجب بالأب بإجماع الصحابة والفقهاء."
        ]
    },
    sons: {
        title: "ميراث الأبناء (الذكور)",
        icon: '<i class="fa-solid fa-shield-halved text-cyan-300"></i>',
        fard: "عصبة بالنفس (أقوى عصبات الميراث على الإطلاق)",
        quranVerse: "يُوصِيكُمُ اللَّهُ فِي أَوْلادِكُمْ لِلذَّكَرِ مِثْلُ حَظِّ الأُنْثَيَيْنِ",
        quranRef: "سورة النساء: الآية 11",
        hadithText: "«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لأَوْلَى رَجُلٍ ذَكَرٍ»",
        hadithSource: "صحيح البخاري (6732)، صحيح مسلم (1614)",
        fiqhRules: [
            "الابن عصبة بالنفس: يأخذ كل التركة عند الانفراد، أو كل ما بقي بعد أصحاب الفروض.",
            "يعصب أخته (البنت الصلبية) فيقتسمون الباقي (للذكر مثل حظ الأنثيين).",
            "الابن يحجب جميع فروع الابن (أبناء وبنات الابن)، وجميع الإخوة والأخوات، وجميع الأعمام وبنيهم حجب حرمان تام."
        ]
    },
    daughters: {
        title: "ميراث البنات (الإناث الصلبيات)",
        icon: '<i class="fa-solid fa-star text-cyan-300"></i>',
        fard: "النصف للواحدة، والثلثان للاثنتين فأكثر، أو عصبة بالغير مع الابن",
        quranVerse: "فَإِنْ كُنَّ نِسَاءً فَوْقَ اثْنَتَيْنِ فَلَهُنَّ ثُلُثَا مَا تَرَكَ وَإِنْ كَانَتْ وَاحِدَةً فَلَهَا النِّصْفُ",
        quranRef: "سورة النساء: الآية 11",
        hadithText: "«أَعْطِ ابْنَتَيْ سَعْدٍ الثُّلُثَيْنِ، وَأَعْطِ أُمَّهُمَا الثُّمُنَ، وَمَا بَقِيَ فَهُوَ لَكَ»",
        hadithSource: "صحيح البخاري، سنن أبي داود (2892)، سنن الترمذي (2092) وصححه الألباني",
        fiqhRules: [
            "النصف (1/2) فرضاً: للبنت الواحدة المنفردة عن المعصب وعن الأخت المماثلة.",
            "الثلثان (2/3) فرضاً: للاثنتين فصاعداً بالسوية عند عدم وجود الابن المعصب.",
            "عصبة بالغير: إذا كان معهن ابن صلب فيقتسمون الباقي تعصيباً (للذكر مثل حظ الأنثيين)."
        ]
    },
    sonsSons: {
        title: "ميراث أبناء الابن (ذكور فروع الابن)",
        icon: '<i class="fa-solid fa-seedling text-cyan-300"></i>',
        fard: "عصبة بالنفس (ينزلون منزلة الابن عند عدم وجوده)",
        quranVerse: "يُوصِيكُمُ اللَّهُ فِي أَوْلادِكُمْ لِلذَّكَرِ مِثْلُ حَظِّ الأُنْثَيَيْنِ",
        quranRef: "سورة النساء: الآية 11 (ولد الابن ولد)",
        hadithText: "«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لأَوْلَى رَجُلٍ ذَكَرٍ»",
        hadithSource: "صحيح البخاري (6732)، صحيح مسلم (1614)",
        fiqhRules: [
            "يأخذ حكم الابن الصلبي تماماً عند عدم وجود الابن المباشر.",
            "يُحجب حجب حرمان تام بالابن المباشر الصلبي أو بابن ابن أعلى منه درجة."
        ]
    },
    sonsDaughters: {
        title: "ميراث بنات الابن (إناث فروع الابن)",
        icon: '<i class="fa-solid fa-certificate text-cyan-300"></i>',
        fard: "النصف، أو الثلثان، أو السدس تكملة الثلثين، أو عصبة بالغير",
        quranVerse: "«قضاء عبد الله بن مسعود المستند إلى قضاء رسول الله ﷺ في بنت الابن»",
        quranRef: "صحيح البخاري: كتاب الفرائض",
        hadithText: "«قَضَى النَّبِيُّ ﷺ لِلابْنَةِ النِّصْفَ، وَلابْنَةِ الابْنِ السُّدُسَ تَكْمِلَةَ الثُّلُثَيْنِ، وَمَا بَقِيَ فَلِلأُخْتِ»",
        hadithSource: "صحيح البخاري (6736) من حديث عبد الله بن مسعود رضي الله عنه",
        fiqhRules: [
            "النصف (1/2): للواحدة عند انعدام البنت الصلبية والابن وابن الابن المماثل.",
            "الثلثان (2/3): للاثنتين فصاعداً عند عدم وجود البنت والابن وابن الابن.",
            "السدس (1/6) تكملة الثلثين: إذا كانت معها بنت صلبية واحدة صاحبة نصف.",
            "تُحجب بالابن الصلبي، أو بجمع من البنات الصلبيات (اثنتان فأكثر) إلا إذا عصبها ابن ابن مبارك."
        ]
    },
    fullBrothers: {
        title: "ميراث الإخوة الأشقاء (ذكور)",
        icon: '<i class="fa-solid fa-users text-indigo-300"></i>',
        fard: "عصبة بالنفس (أقوى عصبات الحواشي)",
        quranVerse: "يَسْتَفْتُونَكَ قُلِ اللَّهُ يُفْتِيكُمْ فِي الْكَلالَةِ إِنِ امْرُؤٌ هَلَكَ لَيْسَ لَهُ وَلَدٌ وَلَهُ أُخْتٌ فَلَهَا نِصْفُ مَا تَرَكَ وَهُوَ يَرِثُهَا إِنْ لَمْ يَكُنْ لَهَا وَلَدٌ",
        quranRef: "سورة النساء: الآية 176 (آية الصيف)",
        hadithText: "«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لأَوْلَى رَجُلٍ ذَكَرٍ»",
        hadithSource: "صحيح البخاري (6732)، صحيح مسلم (1614)",
        fiqhRules: [
            "عصبة بالنفس: يحوزون التركة كاملة عند الانفراد، أو ما بقي بعد أصحاب الفروض.",
            "يعصبون الأخوات الشقيقات (للذكر مثل حظ الأنثيين).",
            "يُحجبون حجب حرمان تام بالفرع الوارث المذكر (الابن وابن الابن) وبالأب باتفاق، وبالجد عند الجمهور."
        ]
    },
    fullSisters: {
        title: "ميراث الأخوات الشقيقات (إناث)",
        icon: '<i class="fa-solid fa-feather text-indigo-300"></i>',
        fard: "النصف، الثلثان، عصبة بالغير مع الأخ، أو عصبة مع الغير مع البنات",
        quranVerse: "فَإِنْ كَانَتَا اثْنَتَيْنِ فَلَهُمَا الثُّلُثَانِ مِمَّا تَرَكَ وَإِنْ كَانُوا إِخْوَةً رِجَالاً وَنِسَاءً فَلِلذَّكَرِ مِثْلُ حَظِّ الأُنْثَيَيْنِ",
        quranRef: "سورة النساء: الآية 176",
        hadithText: "«اجْعَلُوا الأَخَوَاتِ مَعَ الْبَنَاتِ عَصَبَةً»",
        hadithSource: "صحيح البخاري (6742)، سنن أبي داود من قضاء عبد الله بن مسعود رضي الله عنه",
        fiqhRules: [
            "النصف (1/2): للواحدة المنفردة عند عدم الفرع الوارث والأصل المذكر والأخ الشقيق.",
            "الثلثان (2/3): للاثنتين فصاعداً عند عدم المعصب والفرع والأصل.",
            "عصبة بالغير: مع الأخ الشقيق (للذكر مثل حظ الأنثيين).",
            "عصبة مع الغير: مع البنات أو بنات الابن تأخذ ما بقي بعد فروضهن وتصير كالأخ الشقيق تحجب بقية الحواشي."
        ]
    },
    paternalBrothers: {
        title: "ميراث الإخوة لأب (ذكور)",
        icon: '<i class="fa-solid fa-layer-group text-indigo-300"></i>',
        fard: "عصبة بالنفس (ينزلون منزلة الأخ الشقيق عند عدمه)",
        quranVerse: "وَهُوَ يَرِثُهَا إِنْ لَمْ يَكُنْ لَهَا وَلَدٌ",
        quranRef: "سورة النساء: الآية 176",
        hadithText: "«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لأَوْلَى رَجُلٍ ذَكَرٍ»",
        hadithSource: "صحيح البخاري (6732)، صحيح مسلم (1614)",
        fiqhRules: [
            "عصبة بالنفس عند انعدام الإخوة الأشقاء تماماً.",
            "يُحجب حجب حرمان تام بالأب وبالفرع الوارث المذكر وبالأخ الشقيق وبالأخت الشقيقة إذا صارت عصبة مع الغير."
        ]
    },
    paternalSisters: {
        title: "ميراث الأخوات لأب (إناث)",
        icon: '<i class="fa-solid fa-scroll text-indigo-300"></i>',
        fard: "النصف، الثلثان، السدس تكملة الثلثين، أو عصبة بالغير أو مع الغير",
        quranVerse: "«القياس الجلي على ميراث بنات الابن مع البنت الصلبية بالسدس تكملة الثلثين»",
        quranRef: "إجماع الصحابة وقضاء الخلفاء الراشدين",
        hadithText: "«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لأَوْلَى رَجُلٍ ذَكَرٍ»",
        hadithSource: "صحيح البخاري ومسلم، وإجماع الأمة",
        fiqhRules: [
            "النصف (1/2): للمنفردة عند عدم الشقيق والشقيقة والأخ لأب.",
            "الثلثان (2/3): للاثنتين فأكثر عند عدم من يحجبهن أو يعصبهن.",
            "السدس (1/6) تكملة الثلثين: مع الأخت الشقيقة الواحدة صاحبة النصف.",
            "تُحجب بالأخ الشقيق وبالأختين الشقيقتين (ما لم يعصبها أخ لأب) وبالأب والفرع المذكر."
        ]
    },
    maternalSiblings: {
        title: "ميراث الإخوة والأخوات لأم",
        icon: '<i class="fa-solid fa-diagram-project text-indigo-300"></i>',
        fard: "السدس للواحد، والثلث للاثنين فصاعداً (بالسوية بين الذكر والأنثى)",
        quranVerse: "وَإِنْ كَانَ رَجُلٌ يُورَثُ كَلالَةً أَوِ امْرَأَةٌ وَلَهُ أَخٌ أَوْ أُخْتٌ فَلِكُلِّ وَاحِدٍ مِنْهُمَا السُّدُسُ فَإِنْ كَانُوا أَكْثَرَ مِنْ ذَلِكَ فَهُمْ شُرَكَاءُ فِي الثُّلُثِ مِنْ بَعْدِ وَصِيَّةٍ يُوصَى بِهَا أَوْ دَيْنٍ غَيْرَ مُضَارٍّ",
        quranRef: "سورة النساء: الآية 12 (إخوة الأخياف)",
        hadithText: "«أجمع المسلمون على أن الآية 12 من سورة النساء في الإخوة لأم، وأن ذكرهم وأنثاهم في القسمة سواء»",
        hadithSource: "تفسير الطبري، القرطبي، إجماع علماء المسلمين",
        fiqhRules: [
            "السدس (1/6): للواحد المنفرد (ذكراً كان أو أنثى).",
            "الثلث (1/3): للاثنين فصاعداً يقتسمونه بالسوية تماماً (لا يفضل الذكر على الأنثى).",
            "يُحجبون حجب حرمان تام بالأصل المذكر (الأب والجد) وبالفرع الوارث مطلقاً (ذكوراً وإناثاً)."
        ]
    },
    fullNephews: {
        title: "أبناء الأخ الشقيق (ذكور)",
        icon: '<i class="fa-solid fa-award text-rose-300"></i>',
        fard: "عصبة بالنفس (يأخذون ما بقي بعد أصحاب الفروض)",
        quranVerse: "«أولوا الأرحام والقرابات مقدمون حسب الأقرب درجة»",
        quranRef: "سورة الأنفال: الآية 75",
        hadithText: "«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لأَوْلَى رَجُلٍ ذَكَرٍ»",
        hadithSource: "صحيح البخاري (6732)، صحيح مسلم (1614)",
        fiqhRules: [
            "عصبة بالنفس بعد الإخوة مباشرة، ولا ترث بنات الأخ شيئاً (من ذوي الأرحام).",
            "يُحجبون بالفرع المذكر والأصل المذكر والإخوة الأشقاء والإخوة لأب."
        ]
    },
    paternalNephews: {
        title: "أبناء الأخ لأب (ذكور)",
        icon: '<i class="fa-solid fa-ribbon text-rose-300"></i>',
        fard: "عصبة بالنفس (يلي درجة أبناء الأخ الشقيق)",
        quranVerse: "«قاعدة تقديم الأقوى قرابة عند استواء الدرجة»",
        quranRef: "القواعد الفقهية المستنبطة من الكتاب والسنة",
        hadithText: "«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لأَوْلَى رَجُلٍ ذَكَرٍ»",
        hadithSource: "صحيح البخاري (6732)، صحيح مسلم (1614)",
        fiqhRules: [
            "عصبة بالنفس عند انعدام ابن الأخ الشقيق.",
            "يُحجب بكل من يحجب ابن الأخ الشقيق بالإضافة إلى ابن الأخ الشقيق نفسه."
        ]
    },
    fullUncles: {
        title: "الأعمام الأشقاء (أعمام المتوفى)",
        icon: '<i class="fa-solid fa-gem text-rose-300"></i>',
        fard: "عصبة بالنفس عند عدم وجود الإخوة وبنيهم",
        quranVerse: "وَأُولُو الأَرْحَامِ بَعْضُهُمْ أَوْلَى بِبَعْضٍ فِي كِتَابِ اللَّهِ",
        quranRef: "سورة الأنفال: الآية 75",
        hadithText: "«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لأَوْلَى رَجُلٍ ذَكَرٍ»",
        hadithSource: "صحيح البخاري (6732)، صحيح مسلم (1614)",
        fiqhRules: [
            "عصبة بالنفس من جهة العمومة، ولا ترث العمة شيئاً (من ذوي الأرحام).",
            "يُحجب بالأصل المذكر وبالفرع المذكر وبالإخوة وبنيهم جميعاً."
        ]
    },
    paternalUncles: {
        title: "الأعمام لأب",
        icon: '<i class="fa-solid fa-compass text-rose-300"></i>',
        fard: "عصبة بالنفس عند عدم وجود العم الشقيق",
        quranVerse: "وَأُولُو الأَرْحَامِ بَعْضُهُمْ أَوْلَى بِبَعْضٍ",
        quranRef: "سورة الأحزاب: الآية 6",
        hadithText: "«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لأَوْلَى رَجُلٍ ذَكَرٍ»",
        hadithSource: "صحيح البخاري (6732)، صحيح مسلم (1614)",
        fiqhRules: [
            "عصبة بالنفس يلي العم الشقيق في الترتيب.",
            "يُحجب بالعم الشقيق وبكل من يحجب العم الشقيق."
        ]
    },
    fullCousins: {
        title: "أبناء العم الشقيق (ذكور)",
        icon: '<i class="fa-solid fa-shapes text-rose-300"></i>',
        fard: "عصبة بالنفس بعد الأعمام",
        quranVerse: "«أولى رجل ذكر حسب ترتيب العصبات»",
        quranRef: "السنة النبوية وإجماع الصحابة",
        hadithText: "«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لأَوْلَى رَجُلٍ ذَكَرٍ»",
        hadithSource: "صحيح البخاري (6732)، صحيح مسلم (1614)",
        fiqhRules: [
            "عصبة بالنفس، ولا ترث بنات العم شيئاً باتفاق الفقهاء.",
            "يُحجب بالأعمام وبجميع المراتب السابقة في جهات العصبة."
        ]
    },
    paternalCousins: {
        title: "أبناء العم لأب (ذكور)",
        icon: '<i class="fa-solid fa-cubes text-rose-300"></i>',
        fard: "عصبة بالنفس (آخر درجات عصبات الحواشي)",
        quranVerse: "«آخر مراتب العصبات النسبية بالنفس قبل بيت المال»",
        quranRef: "الفقه الإسلامي المعتمد",
        hadithText: "«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لأَوْلَى رَجُلٍ ذَكَرٍ»",
        hadithSource: "صحيح البخاري (6732)، صحيح مسلم (1614)",
        fiqhRules: [
            "عصبة بالنفس عند انعدام جميع من سبقه من العصبات.",
            "يُحجب بابن العم الشقيق وبكل من قبله في جدول العصبات."
        ]
    }
};

/**
 * دالة تبديل وتفعيل الدليل الشرعي لكل وارث (فتح النافذة المربعة المكبرة الفاخرة ذات الغباش الخلفي)
 */
function toggleHeirEvidence(heirKey) {
    console.log('toggleHeirEvidence called for:', heirKey);
    showHeirEvidenceModal(heirKey);
}

/**
 * دالة استدعاء وعرض نافذة الدليل الشرعي المربعة الفاخرة (Square Sharia Modal Dialog)
 */
function showHeirEvidenceModal(heirKey) {
    console.log('showHeirEvidenceModal called for:', heirKey);
    var data = (typeof HEIR_EVIDENCES !== 'undefined' && HEIR_EVIDENCES[heirKey]) ? HEIR_EVIDENCES[heirKey] : null;
    if (!data) {
        console.warn('Evidence data not found for key:', heirKey);
        return;
    }

    var modal = document.getElementById('inh-evidence-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'inh-evidence-modal';
        document.body.appendChild(modal);
    }

    // Modal background: Dense Blur + Deep Dark Backdrop (غباش وخلفية سينمائية)
    modal.style.cssText = 'position: fixed; inset: 0; z-index: 99999999; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(3, 7, 18, 0.94); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);';

    var rulesHtml = data.fiqhRules.map(function(r) {
        return '<li class="flex items-start gap-3 text-slate-100 text-sm sm:text-base leading-relaxed"><span class="w-6 h-6 rounded-full bg-amber-500/25 text-amber-300 flex items-center justify-center text-xs flex-shrink-0 mt-0.5 border border-amber-400/50 font-black">✓</span><span class="font-medium">' + r + '</span></li>';
    }).join('');

    var htmlParts = [];
    // Square Proportioned Luxury Dialog (شاشة مربعة فخمة متناسقة)
    htmlParts.push('<div class="relative w-full max-w-2xl max-h-[88vh] overflow-y-auto rounded-3xl bg-gradient-to-b from-slate-900 via-slate-950 to-slate-900 border-2 border-amber-500/80 shadow-2xl p-6 sm:p-8 space-y-6 animate-scaleIn text-right" dir="rtl" style="box-shadow: 0 0 60px rgba(245, 158, 11, 0.45); border-radius: 28px;">');
    
    // 1. Header (الأيقونة والعنوان وزر الرجوع للشاشة الرئيسية)
    htmlParts.push('  <div class="flex items-center justify-between pb-4 border-b border-amber-500/40">');
    htmlParts.push('    <div class="flex items-center gap-3.5">');
    htmlParts.push('      <span class="w-14 h-14 rounded-2xl bg-amber-500/20 text-3xl flex items-center justify-center border-2 border-amber-400/70 shadow-inner flex-shrink-0">' + data.icon + '</span>');
    htmlParts.push('      <div>');
    htmlParts.push('        <h3 class="text-2xl sm:text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-yellow-200 to-amber-500 font-sans tracking-wide">' + data.title + '</h3>');
    htmlParts.push('        <div class="text-xs sm:text-sm font-black text-emerald-300 flex items-center gap-1.5 mt-0.5"><i class="fa-solid fa-certificate text-emerald-400"></i><span>الدليل الشرعي القطعي من القرآن وصحيح السنة</span></div>');
    htmlParts.push('      </div>');
    htmlParts.push('    </div>');
    htmlParts.push('    <button type="button" onclick="closeHeirEvidenceModal()" class="px-3.5 py-2 rounded-2xl bg-rose-950/60 hover:bg-rose-900 border-2 border-rose-500/60 text-rose-200 hover:text-white flex items-center gap-2 transition-all cursor-pointer shadow-md active:scale-95 text-xs sm:text-sm font-black" title="إغلاق والرجوع للشاشة الرئيسية"><i class="fa-solid fa-xmark text-lg"></i><span>إغلاق</span></button>');
    htmlParts.push('  </div>');

    // 2. Fiqh Fard Badge (الصفة الفقهية)
    htmlParts.push('  <div class="p-3.5 rounded-2xl bg-amber-500/15 border-2 border-amber-500/40 flex flex-wrap items-center gap-2.5">');
    htmlParts.push('    <span class="px-3 py-1 rounded-xl bg-amber-500/30 text-amber-200 font-black text-xs sm:text-sm border border-amber-400/60 flex items-center gap-1.5"><i class="fa-solid fa-scale-balanced"></i><span>الصفة الفقهية:</span></span>');
    htmlParts.push('    <span class="text-sm sm:text-base font-black text-amber-100">' + data.fard + '</span>');
    htmlParts.push('  </div>');

    // 3. Quranic Evidence Box (خط عريض وواضح جداً)
    htmlParts.push('  <div class="p-5 sm:p-6 rounded-3xl bg-slate-900/95 border-2 border-amber-500/50 shadow-xl space-y-3">');
    htmlParts.push('    <div class="flex items-center justify-between">');
    htmlParts.push('      <div class="text-base sm:text-lg font-black text-amber-300 flex items-center gap-2"><i class="fa-solid fa-book-quran text-amber-400 text-xl"></i><span>الدليل الصريح من القرآن الكريم:</span></div>');
    htmlParts.push('      <span class="px-3 py-1 rounded-xl bg-amber-500/25 text-amber-200 font-black text-xs sm:text-sm border border-amber-400/50 font-mono">' + data.quranRef + '</span>');
    htmlParts.push('    </div>');
    htmlParts.push('    <div class="p-4 sm:p-5 rounded-2xl bg-slate-950/90 border border-amber-500/40 text-xl sm:text-2xl text-amber-100 font-serif leading-loose text-center shadow-inner" style="font-family: Amiri, Cairo, serif; line-height: 2.1;">« ' + data.quranVerse + ' »</div>');
    htmlParts.push('  </div>');

    // 4. Sahih Sunnah Evidence Box (خط عريض وتخريج البخاري ومسلم)
    htmlParts.push('  <div class="p-5 sm:p-6 rounded-3xl bg-slate-900/95 border-2 border-emerald-500/50 shadow-xl space-y-3">');
    htmlParts.push('    <div class="flex items-center justify-between">');
    htmlParts.push('      <div class="text-base sm:text-lg font-black text-emerald-300 flex items-center gap-2"><i class="fa-solid fa-scroll text-emerald-400 text-xl"></i><span>الدليل من السنة النبوية المطهرة:</span></div>');
    htmlParts.push('      <span class="px-3 py-1 rounded-xl bg-emerald-500/25 text-emerald-200 font-black text-xs sm:text-sm border border-emerald-400/50 flex items-center gap-1.5"><i class="fa-solid fa-check-double text-emerald-400"></i><span>حديث صحيح محكم</span></span>');
    htmlParts.push('    </div>');
    htmlParts.push('    <div class="p-4 sm:p-5 rounded-2xl bg-slate-950/90 border border-emerald-500/40 text-lg sm:text-xl text-emerald-100 font-serif leading-relaxed text-center shadow-inner" style="font-family: Amiri, Cairo, serif; line-height: 1.9;">' + data.hadithText + '</div>');
    htmlParts.push('    <div class="text-xs sm:text-sm text-emerald-300 font-mono font-bold text-left pt-1" dir="ltr">' + data.hadithSource + '</div>');
    htmlParts.push('  </div>');

    // 5. Fiqh Conditions List (الأحكام والضوابط)
    htmlParts.push('  <div class="p-5 sm:p-6 rounded-3xl bg-slate-900/95 border-2 border-sky-500/40 shadow-xl space-y-3">');
    htmlParts.push('    <div class="text-base sm:text-lg font-black text-sky-300 flex items-center gap-2"><i class="fa-solid fa-scale-unbalanced-flip text-sky-400 text-xl"></i><span>الأحكام الفقهية وضوابط الاستحقاق والحجب:</span></div>');
    htmlParts.push('    <ul class="space-y-2.5 pr-1">' + rulesHtml + '</ul>');
    htmlParts.push('  </div>');

    // 6. Action Footer (نسخ الدليل + رجوع للشاشة الرئيسية)
    htmlParts.push('  <div class="flex items-center justify-between pt-4 border-t border-white/15 gap-4">');
    htmlParts.push('    <button type="button" onclick="copyHeirEvidenceText(\'' + heirKey + '\')" class="px-5 py-3 rounded-2xl bg-amber-500/25 hover:bg-amber-500/35 border-2 border-amber-400/60 text-amber-200 font-black text-xs sm:text-sm flex items-center gap-2 transition-all cursor-pointer shadow-md active:scale-95"><i class="fa-regular fa-copy text-base"></i><span id="copy-evidence-text">نسخ الدليل الشرعي</span></button>');
    htmlParts.push('    <button type="button" onclick="closeHeirEvidenceModal()" class="px-6 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 border-2 border-slate-600 text-white font-black text-xs sm:text-sm flex items-center gap-2 transition-all cursor-pointer shadow-md active:scale-95"><span>رجوع للشاشة الرئيسية</span><i class="fa-solid fa-arrow-left"></i></button>');
    htmlParts.push('  </div>');

    htmlParts.push('</div>');

    modal.innerHTML = htmlParts.join('\n');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Click outside backdrop to close
    modal.onclick = function(e) {
        if (e.target === modal) {
            closeHeirEvidenceModal();
        }
    };
}

function closeHeirEvidenceModal() {
    var modal = document.getElementById('inh-evidence-modal');
    if (modal) {
        modal.style.display = 'none';
    }
    document.body.style.overflow = '';
}

function copyHeirEvidenceText(heirKey) {
    const data = HEIR_EVIDENCES[heirKey];
    if (!data) return;
    const txt = data.title + ' في الشريعة الإسلامية:\n\n• الصفة: ' + data.fard + '\n• الدليل القرآني [' + data.quranRef + ']: «' + data.quranVerse + '»\n• دليل السنة النبوية: ' + data.hadithText + ' [' + data.hadithSource + ']\n\nالأحكام والضوابط:\n' + data.fiqhRules.map(r => '- ' + r).join('\n') + '\n\n(المصدر: نظام المواريث والفرائض الشرعية - السجانة)';
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(txt).then(() => {
            const btnSpan = document.getElementById('copy-evidence-text');
            if (btnSpan) {
                btnSpan.innerText = 'تم النسخ بنجاح ✓';
                setTimeout(() => { btnSpan.innerText = 'نسخ الدليل الشرعي'; }, 2500);
            }
        });
    }
}

function onHeirCountChanged(key, val) {
    highlightCounterCard(key);
}

// Global escape key listener
if (typeof window !== 'undefined' && typeof window.addEventListener === 'function') {
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeHeirEvidenceModal();
        }
    });
}

// Expose functions globally
if (typeof window !== 'undefined') {
    window.calculateIslamicInheritance = calculateIslamicInheritance;
    window.setDeceasedGender = setDeceasedGender;
    window.adjustInhCounter = adjustInhCounter;
    window.resetInheritanceForm = resetInheritanceForm;
    window.applyInhPreset = applyInhPreset;
    window.runIslamicInheritanceCalculation = runIslamicInheritanceCalculation;
    window.printIslamicInheritanceReport = printIslamicInheritanceReport;
    window.shareIslamicInheritanceWhatsApp = shareIslamicInheritanceWhatsApp;
    window.copyIslamicInheritanceSummary = copyIslamicInheritanceSummary;
    window.initInheritanceEngine = initInheritanceEngine;

    window.showHeirEvidenceModal = showHeirEvidenceModal;
    window.closeHeirEvidenceModal = closeHeirEvidenceModal;
    window.copyHeirEvidenceText = copyHeirEvidenceText;
    window.toggleHeirEvidence = toggleHeirEvidence;
    window.onHeirCountChanged = onHeirCountChanged;
    window.HEIR_EVIDENCES = HEIR_EVIDENCES;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        calculateIslamicInheritance,
        setDeceasedGender,
        adjustInhCounter,
        resetInheritanceForm,
        applyInhPreset,
        HEIR_EVIDENCES
    };
}
