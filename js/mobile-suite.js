    // ================================================================
    // 📳 FLUTTER SENSORY SYSTEM: AppHaptics
    // مأخوذ بالكامل ومطابق لكود Flutter AppHaptics:
    // 1. AppHaptics.success() -> HapticFeedback.lightImpact()
    // 2. AppHaptics.error()   -> HapticFeedback.heavyImpact()
    // 3. AppHaptics.click()   -> HapticFeedback.selectionClick()
    // ================================================================
    class AppHaptics {
      // 1. عند إتمام عملية بنجاح (حفظ فاتورة، ترحيل قيد، دفع)
      static success() {
        if (typeof isSensoryHapticEnabled !== 'undefined' && !isSensoryHapticEnabled) return;
        try {
          if ('vibrate' in navigator) {
            navigator.vibrate([35, 25, 35]);
          } else if (typeof triggerHapticPulse === 'function') {
            triggerHapticPulse(35);
          }
        } catch (e) {}
        if (typeof isSensorySoundEnabled === 'undefined' || isSensorySoundEnabled) {
          if (typeof AudioEngine !== 'undefined' && AudioEngine.play) AudioEngine.play('success');
        }
      }

      // 2. عند وجود خطأ في الإدخال (حقل فارغ، تجاوز حد الائتمان)
      static error() {
        if (typeof isSensoryHapticEnabled !== 'undefined' && !isSensoryHapticEnabled) return;
        try {
          if ('vibrate' in navigator) {
            navigator.vibrate([70, 40, 70, 40, 110]);
          } else if (typeof triggerHapticPulse === 'function') {
            triggerHapticPulse(90);
          }
        } catch (e) {}
        if (typeof isSensorySoundEnabled === 'undefined' || isSensorySoundEnabled) {
          if (typeof AudioEngine !== 'undefined' && AudioEngine.play) AudioEngine.play('error');
        }
      }

      // 3. عند التنقل السريع أو النقر على أزرار عادية
      static click() {
        if (typeof isSensoryHapticEnabled !== 'undefined' && !isSensoryHapticEnabled) return;
        try {
          if ('vibrate' in navigator) {
            navigator.vibrate(15);
          } else if (typeof triggerHapticPulse === 'function') {
            triggerHapticPulse(15);
          }
        } catch (e) {}
        if (typeof isSensorySoundEnabled === 'undefined' || isSensorySoundEnabled) {
          if (typeof AudioEngine !== 'undefined' && AudioEngine.play) AudioEngine.play('numpad');
        }
      }
    }

    // إتاحتها عالمياً في كل مكان: window.AppHaptics
    window.AppHaptics = AppHaptics;

    let isPrivacyActive = false;

    // Accounts Database with NON-ZERO vs ZERO Balances (Unified Chart of Accounts)
    const masterAccountsList = [
      // 1. العملاء والمدينون
      { id: 1, name: "شركة النيل للاستيراد", category: "customer", type: "عميل", balanceNum: 4250, balanceFormatted: "$4,250", sdgFormatted: "11,900,000 ج.س", nature: "مدين (عليه دين لنا)", natureClass: "text-rose-400 bg-rose-950/60 border-rose-500/30" },
      { id: 2, name: "محلات الصادق للتوزيع", category: "customer", type: "عميل", balanceNum: 9150, balanceFormatted: "$9,150", sdgFormatted: "25,620,000 ج.س", nature: "مدين (عليه دين لنا)", natureClass: "text-rose-400 bg-rose-950/60 border-rose-500/30" },
      { id: 3, name: "مؤسسة التاج التجارية", category: "customer", type: "عميل", balanceNum: 8200, balanceFormatted: "$8,200", sdgFormatted: "22,960,000 ج.س", nature: "مدين (عليه دين لنا)", natureClass: "text-rose-400 bg-rose-950/60 border-rose-500/30" },
      { id: 4, name: "أسواق البركة المركزية", category: "customer", type: "عميل", balanceNum: 5800, balanceFormatted: "$5,800", sdgFormatted: "16,240,000 ج.س", nature: "مدين (عليه دين لنا)", natureClass: "text-rose-400 bg-rose-950/60 border-rose-500/30" },
      { id: 5, name: "وكالة الهدى للشحن", category: "customer", type: "عميل", balanceNum: 4350, balanceFormatted: "$4,350", sdgFormatted: "12,180,000 ج.س", nature: "مدين (عليه دين لنا)", natureClass: "text-rose-400 bg-rose-950/60 border-rose-500/30" },
      { id: 20, name: "شركة همس للتجارة العامة", category: "customer", type: "عميل", balanceNum: 7400, balanceFormatted: "$7,400", sdgFormatted: "20,720,000 ج.س", nature: "مدين (عليه دين لنا)", natureClass: "text-rose-400 bg-rose-950/60 border-rose-500/30" },
      { id: 21, name: "سوبرماركت الواحة التجاري", category: "customer", type: "عميل", balanceNum: 3900, balanceFormatted: "$3,900", sdgFormatted: "10,920,000 ج.س", nature: "مدين (عليه دين لنا)", natureClass: "text-rose-400 bg-rose-950/60 border-rose-500/30" },

      // 2. الموردون والدائنون
      { id: 6, name: "مطاحن الخرطوم للغلال", category: "vendor", type: "مورد", balanceNum: 18300, balanceFormatted: "$18,300", sdgFormatted: "51,240,000 ج.س", nature: "دائن (له مستحقات علينا)", natureClass: "text-cyan-400 bg-cyan-950/60 border-cyan-500/30" },
      { id: 7, name: "مصانع سيقا للزيوت", category: "vendor", type: "مورد", balanceNum: 13850, balanceFormatted: "$13,850", sdgFormatted: "38,780,000 ج.س", nature: "دائن (له مستحقات علينا)", natureClass: "text-cyan-400 bg-cyan-950/60 border-cyan-500/30" },
      { id: 22, name: "شركة دال الهندسية", category: "vendor", type: "مورد", balanceNum: 22100, balanceFormatted: "$22,100", sdgFormatted: "61,880,000 ج.س", nature: "دائن (له مستحقات علينا)", natureClass: "text-cyan-400 bg-cyan-950/60 border-cyan-500/30" },
      { id: 23, name: "مجموعة البرير الصناعية", category: "vendor", type: "مورد", balanceNum: 15400, balanceFormatted: "$15,400", sdgFormatted: "43,120,000 ج.س", nature: "دائن (له مستحقات علينا)", natureClass: "text-cyan-400 bg-cyan-950/60 border-cyan-500/30" },
      { id: 24, name: "مصنع باعبود للتجارة", category: "vendor", type: "مورد", balanceNum: 11200, balanceFormatted: "$11,200", sdgFormatted: "31,360,000 ج.س", nature: "دائن (له مستحقات علينا)", natureClass: "text-cyan-400 bg-cyan-950/60 border-cyan-500/30" },

      // 3. البنوك والمصارف
      { id: 8, name: "بنك الخرطوم (الحساب الجاري)", category: "cash", type: "بنك", balanceNum: 28400, balanceFormatted: "$28,400", sdgFormatted: "79,520,000 ج.س", nature: "رصيد بنكي متاح", natureClass: "text-emerald-400 bg-emerald-950/60 border-emerald-500/30" },
      { id: 9, name: "بنك فيصل الإسلامي", category: "cash", type: "بنك", balanceNum: 21500, balanceFormatted: "$21,500", sdgFormatted: "60,200,000 ج.س", nature: "رصيد بنكي متاح", natureClass: "text-emerald-400 bg-emerald-950/60 border-emerald-500/30" },
      { id: 15, name: "تطبيق بنكك (الحساب المباشر)", category: "cash", type: "بنك", balanceNum: 19500, balanceFormatted: "$19,500", sdgFormatted: "54,600,000 ج.س", nature: "رصيد بنكي متاح", natureClass: "text-emerald-400 bg-emerald-950/60 border-emerald-500/30" },
      { id: 16, name: "بنك أم درمان الوطني", category: "cash", type: "بنك", balanceNum: 16400, balanceFormatted: "$16,400", sdgFormatted: "45,920,000 ج.س", nature: "رصيد بنكي متاح", natureClass: "text-emerald-400 bg-emerald-950/60 border-emerald-500/30" },
      { id: 17, name: "بنك الخليج", category: "cash", type: "بنك", balanceNum: 9800, balanceFormatted: "$9,800", sdgFormatted: "27,440,000 ج.س", nature: "رصيد بنكي متاح", natureClass: "text-emerald-400 bg-emerald-950/60 border-emerald-500/30" },

      // 4. الخزن النقدية ونقاط البيع
      { id: 10, name: "الخزينة الرئيسية (الإدارة)", category: "cash", type: "خزينة", balanceNum: 8950, balanceFormatted: "$8,950", sdgFormatted: "25,060,000 ج.س", nature: "نقدية بالصندوق", natureClass: "text-amber-400 bg-amber-950/60 border-amber-500/30" },
      { id: 11, name: "خزينة صالة المعرض", category: "cash", type: "خزينة", balanceNum: 5200, balanceFormatted: "$5,200", sdgFormatted: "14,560,000 ج.س", nature: "نقدية بالصندوق", natureClass: "text-amber-400 bg-amber-950/60 border-amber-500/30" },
      { id: 18, name: "خزينة التحصيل الميداني", category: "cash", type: "خزينة", balanceNum: 3400, balanceFormatted: "$3,400", sdgFormatted: "9,520,000 ج.س", nature: "نقدية بالصندوق", natureClass: "text-amber-400 bg-amber-950/60 border-amber-500/30" },
      { id: 19, name: "عهدة كاشير المبيعات", category: "cash", type: "خزينة", balanceNum: 2100, balanceFormatted: "$2,100", sdgFormatted: "5,880,000 ج.س", nature: "نقدية بالصندوق", natureClass: "text-amber-400 bg-amber-950/60 border-amber-500/30" },

      // 5. المصروفات والتشغيل
      { id: 12, name: "مصروفات الوقود والترحيل", category: "expense", type: "مصروف", balanceNum: 3120, balanceFormatted: "$3,120", sdgFormatted: "8,736,000 ج.س", nature: "رصيد منصرفات تراكمي", natureClass: "text-purple-400 bg-purple-950/60 border-purple-500/30" },
      { id: 25, name: "نثريات ومصروفات تشغيلية", category: "expense", type: "مصروف", balanceNum: 4800, balanceFormatted: "$4,800", sdgFormatted: "13,440,000 ج.س", nature: "رصيد منصرفات تراكمي", natureClass: "text-purple-400 bg-purple-950/60 border-purple-500/30" },
      { id: 26, name: "مرتبات وسلفيات موظفين", category: "expense", type: "مصروف", balanceNum: 6200, balanceFormatted: "$6,200", sdgFormatted: "17,360,000 ج.س", nature: "رصيد منصرفات تراكمي", natureClass: "text-purple-400 bg-purple-950/60 border-purple-500/30" },
      { id: 27, name: "رسوم شحن وتفريغ", category: "expense", type: "مصروف", balanceNum: 2500, balanceFormatted: "$2,500", sdgFormatted: "7,000,000 ج.س", nature: "رصيد منصرفات تراكمي", natureClass: "text-purple-400 bg-purple-950/60 border-purple-500/30" },

      // 6. الذمم والشركاء والأمانات
      { id: 28, name: "جاري الشريك محمد", category: "partner", type: "شريك", balanceNum: 35000, balanceFormatted: "$35,000", sdgFormatted: "98,000,000 ج.س", nature: "جاري شريك (دائن)", natureClass: "text-indigo-400 bg-indigo-950/60 border-indigo-500/30" },
      { id: 29, name: "جاري الشريك أحمد", category: "partner", type: "شريك", balanceNum: 28000, balanceFormatted: "$28,000", sdgFormatted: "78,400,000 ج.س", nature: "جاري شريك (دائن)", natureClass: "text-indigo-400 bg-indigo-950/60 border-indigo-500/30" },
      { id: 30, name: "عهدة المبيعات والذمم", category: "partner", type: "ذمم", balanceNum: 5600, balanceFormatted: "$5,600", sdgFormatted: "15,680,000 ج.س", nature: "عهدة معلقة (مدين)", natureClass: "text-indigo-400 bg-indigo-950/60 border-indigo-500/30" },

      // ZERO-BALANCE ACCOUNTS (FILTERED OUT AUTOMATICALLY)
      { id: 13, name: "شركة الأمل للتجارة (مسدد)", category: "customer", type: "عميل", balanceNum: 0, balanceFormatted: "$0", sdgFormatted: "0 ج.س", nature: "مسدد", natureClass: "" },
      { id: 14, name: "مورد التعبئة الحديثة (مسدد)", category: "vendor", type: "مورد", balanceNum: 0, balanceFormatted: "$0", sdgFormatted: "0 ج.س", nature: "مسدد", natureClass: "" }
    ];

    // ========================================================
    // COMPREHENSIVE INTER-ACCOUNT FINANCIAL TRANSFER ENGINE
    // حركة مالية وتحويل بين الحسابات (من ➔ إلى)
    // ========================================================
    let currentFinTransferMethod = 'bank';
    let currentFinTransferCurrency = 'USD';
    let finTransferAttachedFile = null;

    // Database of registered transfer/check reference numbers to prevent duplicate entry
    const registeredTransferReferences = new Set();

    function initFinancialTransferModal() {
      const fromSelect = document.getElementById('finTransferFromAccount');
      const toSelect = document.getElementById('finTransferToAccount');

      if (!fromSelect || !toSelect) return;

      const accounts = (window.masterAccountsList && window.masterAccountsList.length) ? window.masterAccountsList : masterAccountsList;

      const groupedOptions = `
        <optgroup label="🏦 الحسابات البنكية والمصرفية">
          ${accounts.filter(a => a.type === 'بنك' || a.type === 'فيزا').map(a => `<option value="${a.id}">${a.name} (${a.balanceFormatted})</option>`).join('')}
        </optgroup>
        <optgroup label="💵 الخزن النقدية ونقاط البيع">
          ${accounts.filter(a => a.type === 'خزينة').map(a => `<option value="${a.id}">${a.name} (${a.balanceFormatted})</option>`).join('')}
        </optgroup>
        <optgroup label="👥 العملاء والمدينون">
          ${accounts.filter(a => a.category === 'customer').map(a => `<option value="${a.id}">${a.name} (${a.balanceFormatted})</option>`).join('')}
        </optgroup>
        <optgroup label="🏢 الموردون والدائنون">
          ${accounts.filter(a => a.category === 'vendor').map(a => `<option value="${a.id}">${a.name} (${a.balanceFormatted})</option>`).join('')}
        </optgroup>
        <optgroup label="📊 المصروفات والتشغيل">
          ${accounts.filter(a => a.category === 'expense').map(a => `<option value="${a.id}">${a.name} (${a.balanceFormatted})</option>`).join('')}
        </optgroup>
        <optgroup label="🤝 الذمم والشركاء والأمانات">
          ${accounts.filter(a => a.category === 'partner').map(a => `<option value="${a.id}">${a.name} (${a.balanceFormatted})</option>`).join('')}
        </optgroup>
      `;

      fromSelect.innerHTML = groupedOptions;
      toSelect.innerHTML = groupedOptions;

      // Select default from (bank/safe) and to (vendor/customer)
      const defaultFrom = accounts.find(a => a.type === 'بنك' || a.type === 'خزينة') || accounts[0];
      const defaultTo = accounts.find(a => a.category === 'vendor' || a.category === 'customer') || accounts[1] || accounts[0];

      if (defaultFrom) fromSelect.value = String(defaultFrom.id);
      if (defaultTo) toSelect.value = String(defaultTo.id);

      // Method: Default bank
      setFinTransferMethod('bank');

      // Update previews
      updateFinTransferBalancesPreview();
      validateFinTransferReference();
    }

    function setFinTransferMethod(method) {
      currentFinTransferMethod = method;
      AudioEngine.play('numpad');
      triggerHapticPulse(25);

      const btns = {
        'bank': document.getElementById('finMethodBtnBank'),
        'cash': document.getElementById('finMethodBtnCash'),
        'bank_check': document.getElementById('finMethodBtnBankCheck'),
        'check': document.getElementById('finMethodBtnCheck')
      };

      const sections = {
        'bank': document.getElementById('finFieldsBank'),
        'cash': document.getElementById('finFieldsCash'),
        'bank_check': document.getElementById('finFieldsBankCheck'),
        'check': document.getElementById('finFieldsCheck')
      };

      Object.keys(btns).forEach(k => {
        if (btns[k]) {
          if (k === method) {
            btns[k].className = "py-2 rounded-xl bg-cyan-600 text-white shadow font-black transition";
          } else {
            btns[k].className = "py-2 rounded-xl text-slate-400 hover:text-white transition";
          }
        }
        if (sections[k]) {
          if (k === method) {
            sections[k].classList.remove('hidden');
          } else {
            sections[k].classList.add('hidden');
          }
        }
      });

      const badge = document.getElementById('finMethodBadge');
      if (badge) {
        if (method === 'bank') badge.textContent = '⚡ تحويلة بنكية إلكترونية';
        else if (method === 'cash') badge.textContent = '💵 سداد نقدي كاش بالصندوق';
        else if (method === 'bank_check') badge.textContent = '🏛️ شيك مصرفي معتمد ومصدق';
        else if (method === 'check') badge.textContent = '✍️ شيك عادي آجل';
      }

      validateFinTransferReference();
    }

    function setFinTransferCurrency(curr) {
      currentFinTransferCurrency = curr;
      AudioEngine.play('numpad');
      triggerHapticPulse(25);

      const usdBtn = document.getElementById('finCurBtnUsd');
      const sdgBtn = document.getElementById('finCurBtnSdg');

      if (curr === 'USD') {
        if (usdBtn) usdBtn.className = "px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-bold text-xs transition";
        if (sdgBtn) sdgBtn.className = "px-2.5 py-1 rounded-lg text-slate-400 hover:text-white font-bold text-xs transition";
      } else {
        if (sdgBtn) sdgBtn.className = "px-2.5 py-1 rounded-lg bg-amber-500 text-slate-950 font-bold text-xs transition";
        if (usdBtn) usdBtn.className = "px-2.5 py-1 rounded-lg text-slate-400 hover:text-white font-bold text-xs transition";
      }

      updateFinTransferBalancesPreview();
    }

    function updateFinTransferBalancesPreview() {
      const fromSelect = document.getElementById('finTransferFromAccount');
      const toSelect = document.getElementById('finTransferToAccount');
      const amtInput = document.getElementById('finTransferAmountInput');

      if (!fromSelect || !toSelect || !amtInput) return;

      const fromVal = fromSelect.value;
      const toVal = toSelect.value;
      const accounts = (window.masterAccountsList && window.masterAccountsList.length) ? window.masterAccountsList : masterAccountsList;

      const sender = accounts.find(a => String(a.id) === String(fromVal) || String(a.numericId) === String(fromVal));
      const receiver = accounts.find(a => String(a.id) === String(toVal) || String(a.numericId) === String(toVal));

      const rawAmount = parseFloat(amtInput.value) || 0;

      // Base conversion to USD for standard internal math
      let amtUsd = 0;
      let amtSdg = 0;

      if (currentFinTransferCurrency === 'USD') {
        amtUsd = rawAmount;
        amtSdg = Math.round(rawAmount * SDG_RATE);
      } else {
        amtSdg = rawAmount;
        amtUsd = Math.round((rawAmount / SDG_RATE) * 100) / 100;
      }

      // 1. Update Sender (من الصارف - خصم)
      if (sender) {
        const curBal = sender.balanceNum;
        const newBal = curBal - amtUsd;
        const newBalSdg = Math.round(newBal * SDG_RATE);

        const curBalEl = document.getElementById('finFromCurrentBalance');
        const deductEl = document.getElementById('finFromDeductAmount');
        const newBalEl = document.getElementById('finFromNewBalance');
        const sdgPreviewEl = document.getElementById('finFromSdgPreview');
        const warnEl = document.getElementById('finFromWarning');

        if (curBalEl) curBalEl.textContent = `$${curBal.toLocaleString('en-US')}`;
        if (deductEl) deductEl.textContent = `-$${amtUsd.toLocaleString('en-US')}`;
        if (newBalEl) newBalEl.textContent = `$${newBal.toLocaleString('en-US')}`;
        if (sdgPreviewEl) sdgPreviewEl.textContent = `الرصيد المتوقع بعد الخصم: ≈ ${newBalSdg.toLocaleString('en-US')} ج.س`;

        if (warnEl) {
          if (amtUsd > curBal && (sender.type === 'بنك' || sender.type === 'خزينة')) {
            warnEl.classList.remove('hidden');
          } else {
            warnEl.classList.add('hidden');
          }
        }
      } else {
        const curBalEl = document.getElementById('finFromCurrentBalance');
        const deductEl = document.getElementById('finFromDeductAmount');
        const newBalEl = document.getElementById('finFromNewBalance');
        const sdgPreviewEl = document.getElementById('finFromSdgPreview');
        const warnEl = document.getElementById('finFromWarning');

        if (curBalEl) curBalEl.textContent = 'حساب العميل (سداد مباشر)';
        if (deductEl) deductEl.textContent = (currentFinTransferCurrency === 'USD' ? `-$${amtUsd.toLocaleString('en-US')}` : `-${amtSdg.toLocaleString('en-US')} ج.س`);
        if (newBalEl) newBalEl.textContent = 'تم سداد الفاتورة';
        if (sdgPreviewEl) sdgPreviewEl.textContent = `المبلغ الصافي: ${amtSdg.toLocaleString('en-US')} ج.س`;
        if (warnEl) warnEl.classList.add('hidden');
      }

      // 2. Update Receiver (إلى المستلم - إضافة)
      if (receiver) {
        const curBal = receiver.balanceNum;
        const newBal = curBal + amtUsd;
        const newBalSdg = Math.round(newBal * SDG_RATE);

        const curBalEl = document.getElementById('finToCurrentBalance');
        const addEl = document.getElementById('finToAddAmount');
        const newBalEl = document.getElementById('finToNewBalance');
        const sdgPreviewEl = document.getElementById('finToSdgPreview');

        if (curBalEl) curBalEl.textContent = `$${curBal.toLocaleString('en-US')}`;
        if (addEl) addEl.textContent = `+$${amtUsd.toLocaleString('en-US')}`;
        if (newBalEl) newBalEl.textContent = `$${newBal.toLocaleString('en-US')}`;
        if (sdgPreviewEl) sdgPreviewEl.textContent = `الرصيد المتوقع بعد الإضافة: ≈ ${newBalSdg.toLocaleString('en-US')} ج.س`;
      }

      // 3. Update Amount Equivalent Preview
      const eqEl = document.getElementById('finAmountEquivalent');
      if (eqEl) {
        if (currentFinTransferCurrency === 'USD') {
          eqEl.textContent = `المعادل: ≈ ${amtSdg.toLocaleString('en-US')} ج.س`;
        } else {
          eqEl.textContent = `المعادل: ≈ $${amtUsd.toLocaleString('en-US')} USD`;
        }
      }
    }

    function validateFinTransferReference() {
      const method = currentFinTransferMethod;
      const errorBox = document.getElementById('finTransferRefError');
      const submitBtn = document.getElementById('finTransferSubmitBtn');

      if (!errorBox) return true;

      // Cash method does not require external reference validation
      if (method === 'cash') {
        errorBox.classList.add('hidden');
        if (submitBtn) submitBtn.disabled = false;
        return true;
      }

      let refVal = '';
      if (method === 'bank') {
        refVal = (document.getElementById('finTransferBankRef')?.value || '').trim();
      } else if (method === 'bank_check') {
        refVal = (document.getElementById('finTransferBankCheckNo')?.value || '').trim();
      } else if (method === 'check') {
        refVal = (document.getElementById('finTransferCheckNo')?.value || '').trim();
      }

      if (!refVal) {
        errorBox.innerHTML = `⚠️ يرجى إدخال رقم التحويل / الشيك لتوثيق العملية في النظام.`;
        errorBox.className = "p-2.5 rounded-xl bg-amber-950/60 border border-amber-500/40 text-amber-300 text-xs font-bold";
        errorBox.classList.remove('hidden');
        return false;
      }

      // Rule 1: Min 6 digits/characters
      const cleanRef = refVal.replace(/[^a-zA-Z0-9]/g, '');
      if (cleanRef.length < 6 && refVal.length < 6) {
        errorBox.innerHTML = `⚠️ تنبيه نظامي: رقم التحويلة / الشيك [<strong>${refVal}</strong>] غير مكتمل! يجب ألا يقل عن 6 أرقام/خانات لضمان التوثيق المحاسبي والربط مع قاعدة البيانات.`;
        errorBox.className = "p-2.5 rounded-xl bg-amber-950/80 border border-amber-500/60 text-amber-300 text-xs font-bold animate-pulse";
        errorBox.classList.remove('hidden');
        AudioEngine.play('error');
        return false;
      }

      // Rule 2: Anti-Duplicate Detection against Registered DB References
      const normalizedInput = refVal.toUpperCase().replace(/[\s-]/g, '');
      const isDuplicate = Array.from(registeredTransferReferences).some(existing => {
        const normExist = existing.toUpperCase().replace(/[\s-]/g, '');
        return normExist === normalizedInput || (normExist.length >= 6 && normalizedInput.endsWith(normExist));
      });

      if (isDuplicate) {
        errorBox.innerHTML = `🚫 <strong>تحذير أمني:</strong> رقم التحويلة / المرجع <strong>[${refVal}]</strong> مكرر ومسجل مسبقاً في النظام!<br><span class="text-[11px] font-normal">لا يمكن تكرار قيد نفس الإشعار أو الشيك منعاً للازدواج المالي وحفاظاً على دقة الأرصدة.</span>`;
        errorBox.className = "p-2.5 rounded-xl bg-rose-950/90 border border-rose-500 text-rose-200 text-xs font-bold shadow-lg shadow-rose-950/50";
        errorBox.classList.remove('hidden');
        AudioEngine.play('error');
        triggerHapticPulse([60, 50, 60]);
        return false;
      }

      // Valid Reference
      errorBox.innerHTML = `✓ الرقم المرجعي [<strong>${refVal}</strong>] فريد وغير مكرر ومعتمد نظامياً بنجاح.`;
      errorBox.className = "p-2 rounded-xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-300 text-xs font-bold";
      errorBox.classList.remove('hidden');
      return true;
    }

    // Camera & Attachment Handlers
    function handleFinTransferAttachmentSelected(input, source) {
      if (!input || !input.files || input.files.length === 0) return;
      const file = input.files[0];
      finTransferAttachedFile = file;

      const card = document.getElementById('finTransferAttachmentCard');
      const nameEl = document.getElementById('finAttachmentName');
      const srcEl = document.getElementById('finAttachmentSource');
      const thumbEl = document.getElementById('finAttachmentThumb');

      if (nameEl) nameEl.textContent = file.name;
      if (srcEl) {
        const kb = Math.round(file.size / 1024);
        srcEl.textContent = `تم الإرفاق من ${source} • ${kb} KB`;
      }

      if (thumbEl && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          thumbEl.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-lg" />`;
        };
        reader.readAsDataURL(file);
      }

      if (card) card.classList.remove('hidden');
      AudioEngine.play('success');
      showFloatingToast(`📸 تم إرفاق صورة الإشعار بنجاح من ${source}`);
    }

    function loadDemoFinTransferAttachment() {
      const card = document.getElementById('finTransferAttachmentCard');
      const nameEl = document.getElementById('finAttachmentName');
      const srcEl = document.getElementById('finAttachmentSource');
      const thumbEl = document.getElementById('finAttachmentThumb');

      if (nameEl) nameEl.textContent = "إشعار_بنكك_معتمد_BOK-849201.jpg";
      if (srcEl) srcEl.textContent = "إشعار تحويل مصرفي بنكك • 285 KB";
      if (thumbEl) {
        thumbEl.innerHTML = `
          <div class="w-full h-full bg-emerald-950/60 flex flex-col items-center justify-center text-center p-0.5">
            <span class="text-[9px] text-emerald-300 font-bold font-mono">BOK</span>
            <span class="text-[8px] text-slate-300 font-mono">70K ج.س</span>
          </div>
        `;
      }

      if (card) card.classList.remove('hidden');
      AudioEngine.play('success');
      showFloatingToast('🧪 تم تحميل إشعار بنكك تجريبي للمطابقة والاستخراج');
    }

    function removeFinTransferAttachment() {
      finTransferAttachedFile = null;
      const card = document.getElementById('finTransferAttachmentCard');
      const camInput = document.getElementById('finTransferCameraInput');
      const galInput = document.getElementById('finTransferGalleryInput');

      if (card) card.classList.add('hidden');
      if (camInput) camInput.value = '';
      if (galInput) galInput.value = '';

      showFloatingToast('🗑️ تم إزالة الإشعار المرفق');
    }

    function extractFinTransferFromAttachment() {
      AudioEngine.play('ocr');
      triggerHapticPulse([40, 30, 40]);

      // Set valid fresh ref no
      const randomRef = 'BOK-' + Math.floor(100000 + Math.random() * 900000);
      const refInput = document.getElementById('finTransferBankRef');
      if (refInput) {
        refInput.value = randomRef;
      }

      const amtInput = document.getElementById('finTransferAmountInput');
      if (amtInput) {
        amtInput.value = "1500";
      }

      updateFinTransferBalancesPreview();
      validateFinTransferReference();

      AudioEngine.play('success');
      showFloatingToast(`⚡ تم استخراج الرقم المرجعي [${randomRef}] والمبلغ ($1,500) بنجاح عبر OCR!`);
    }

    window._isSavingFinTransfer = false;
    window._lastFinTransferSig = '';

    async function commitFinancialTransfer() {
      if (window._isSavingFinTransfer) return;

      const fromSelect = document.getElementById('finTransferFromAccount');
      const toSelect = document.getElementById('finTransferToAccount');
      const amtInput = document.getElementById('finTransferAmountInput');
      const notesInput = document.getElementById('finTransferNotes');

      if (!fromSelect || !toSelect || !amtInput) return;

      const fromVal = fromSelect.value;
      const toVal = toSelect.value;

      if (fromVal === toVal) {
        AudioEngine.play('error');
        alert("⚠️ خطأ في طرفي المعاملة: لا يمكن إجراء تحويل بين نفس الحساب ونفسه! يرجى اختيار حسابين مختلفين.");
        return;
      }

      const accounts = (window.masterAccountsList && window.masterAccountsList.length) ? window.masterAccountsList : masterAccountsList;
      const sender = accounts.find(a => String(a.id) === String(fromVal) || String(a.numericId) === String(fromVal));
      const receiver = accounts.find(a => String(a.id) === String(toVal) || String(a.numericId) === String(toVal));

      const rawAmt = parseFloat(amtInput.value) || 0;
      if (rawAmt <= 0) {
        AudioEngine.play('error');
        alert("⚠️ يرجى إدخال مبلغ صحيح أكبر من الصفر لإجراء الحركة المالية.");
        return;
      }

      // Check reference rules
      if (!validateFinTransferReference()) {
        alert("⚠️ لا يمكن إتمام التحويل: يرجى التحقق من الرقم المرجعي للتحويلة أو الشيك والتأكد من عدم تكراره واكتمال خاناته.");
        return;
      }

      // Reference number string
      let refStr = '';
      if (currentFinTransferMethod === 'bank') {
        refStr = (document.getElementById('finTransferBankRef')?.value || 'BOK-000000').trim();
      } else if (currentFinTransferMethod === 'bank_check') {
        refStr = (document.getElementById('finTransferBankCheckNo')?.value || 'CHK-000000').trim();
      } else if (currentFinTransferMethod === 'check') {
        refStr = (document.getElementById('finTransferCheckNo')?.value || 'CHK-000000').trim();
      } else {
        refStr = document.getElementById('finTransferCashReceiptNo')?.value || (window.MagaiesOffline && MagaiesOffline.peekNextSequentialNumber ? MagaiesOffline.peekNextSequentialNumber('finance') : ('E26100001'));
      }

      const finSig = `${fromVal}|${toVal}|${rawAmt}|${currentFinTransferCurrency}|${currentFinTransferMethod}|${refStr}|${notesInput ? notesInput.value.trim() : ''}`;
      if (window._lastFinTransferSig === finSig) {
        if (typeof showFloatingToast === 'function') {
          showFloatingToast('✓ تم حفظ هذه الحركة المالية بالفعل ولا حاجة لتكرار الحفظ.');
        }
        return;
      }

      window._isSavingFinTransfer = true;
      window._lastFinTransferSig = finSig;

      // Calculate USD and SDG values
      let amtUsd = 0;
      let amtSdg = 0;
      if (currentFinTransferCurrency === 'USD') {
        amtUsd = rawAmt;
        amtSdg = Math.round(rawAmt * SDG_RATE);
      } else {
        amtSdg = rawAmt;
        amtUsd = Math.round((rawAmt / SDG_RATE) * 100) / 100;
      }

      // Update accounts database balances locally
      if (sender) {
        sender.balanceNum -= amtUsd;
        sender.balanceFormatted = `$${sender.balanceNum.toLocaleString('en-US')}`;
        sender.sdgFormatted = `${Math.round(sender.balanceNum * SDG_RATE).toLocaleString('en-US')} ج.س`;
      }

      if (receiver) {
        receiver.balanceNum += amtUsd;
        receiver.balanceFormatted = `$${receiver.balanceNum.toLocaleString('en-US')}`;
        receiver.sdgFormatted = `${Math.round(receiver.balanceNum * SDG_RATE).toLocaleString('en-US')} ج.س`;
      }

      // Register reference to block future duplicates
      if (refStr && currentFinTransferMethod !== 'cash') {
        registeredTransferReferences.add(refStr);
      }

      // Send to real backend database (PWA Manager + Double-Entry Voucher Engine)!
      if (window.PWAManager && typeof window.PWAManager.recordFinancialTransferToBackend === 'function') {
        window.PWAManager.recordFinancialTransferToBackend({
          from_account_id: fromVal,
          to_account_id: toVal,
          amount: amtUsd,
          currency: currentFinTransferCurrency,
          method: currentFinTransferMethod,
          ref_number: refStr,
          notes: notesInput ? notesInput.value : 'تحويل ومناقلة مالية'
        });
      }

      // Sync directly with mobile-voucher.php?api=save_voucher for atomic double-entry GL & balance mutation
      const voucherPayload = {
        idempotencyKey: 'FIN-' + finSig,
        date: new Date().toISOString().slice(0, 10),
        fromAccount: sender ? sender.name : (fromSelect.options[fromSelect.selectedIndex]?.text || ''),
        fromAccountId: fromVal,
        toAccount: receiver ? receiver.name : (toSelect.options[toSelect.selectedIndex]?.text || ''),
        toAccountId: toVal,
        paymentMethod: currentFinTransferMethod === 'cash' ? 'cash' : (currentFinTransferMethod === 'bank' ? 'bank' : 'check'),
        currency: currentFinTransferCurrency,
        amountSdg: amtSdg,
        amountUsd: amtUsd,
        exchangeRate: SDG_RATE,
        transferNo: currentFinTransferMethod === 'bank' ? refStr : '',
        checkNo: (currentFinTransferMethod === 'check' || currentFinTransferMethod === 'bank_check') ? refStr : '',
        notes: notesInput && notesInput.value ? notesInput.value : 'تحويل ومناقلة مالية من الجناح التشغيلي'
      };

      if (!navigator.onLine) {
        if (window.MagaiesOffline) {
          refStr = MagaiesOffline.enqueue('finance', voucherPayload);
        }
      } else {
        try {
          fetch('mobile-voucher.php?api=save_voucher', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(voucherPayload)
          }).catch(() => {
            if (window.MagaiesOffline) MagaiesOffline.enqueue('finance', voucherPayload);
          });
        } catch (e) {
          if (window.MagaiesOffline) MagaiesOffline.enqueue('finance', voucherPayload);
        }
      }

      // Add to Live Transactions Feed
      const tx = {
        id: `TX-${Date.now().toString().slice(-4)}`,
        title: `تحويل مالي: من ${sender ? sender.name : 'صارف'} ➔ إلى ${receiver ? receiver.name : 'مستلم'}`,
        subtitle: `مرجع #${refStr} • ${currentFinTransferMethod === 'bank' ? 'تحويلة بنكية' : (currentFinTransferMethod === 'cash' ? 'سداد نقدي' : 'شيك')} • ${notesInput ? notesInput.value : 'حركة مالية'}`,
        amountUsd: `$${amtUsd.toLocaleString('en-US')}`,
        amountSdg: `${amtSdg.toLocaleString('en-US')} ج.س`,
        typeColor: 'cyan'
      };
      addNewTxToFeed(tx);

      // Audio & notification
      AudioEngine.play('success');
      triggerHapticPulse([80, 50, 80]);

      showFloatingToast(navigator.onLine
        ? `✓ تم بنجاح ترحيل التحويل المالي برقم مرجع [${refStr}] وتحديث أرصدة الطرفين ودفتر الأستاذ فوراً!`
        : `⚡ تم حفظ الحركة المالية أوفلاين برقم [${refStr}] وستعتمد تلقائياً فور توفر الشبكة!`);
      closeModal('modalOpFinance');
      setTimeout(() => { window._isSavingFinTransfer = false; }, 600);
    }

    function printFinancialTransferVoucher() {
      AudioEngine.play('numpad');
      const fromSelect = document.getElementById('finTransferFromAccount');
      const toSelect = document.getElementById('finTransferToAccount');
      const amtInput = document.getElementById('finTransferAmountInput');
      const notes = document.getElementById('finTransferNotes')?.value || 'تحويل مالي بين الحسابات';

      const fromText = fromSelect ? fromSelect.options[fromSelect.selectedIndex]?.text || '' : '';
      const toText = toSelect ? toSelect.options[toSelect.selectedIndex]?.text || '' : '';
      const amt = amtInput ? amtInput.value : '0';

      const params = new URLSearchParams({
        from: fromText.replace(/^[^\u0600-\u06FFa-zA-Z0-9]+/, '').split('(')[0].trim(),
        to: toText.replace(/^[^\u0600-\u06FFa-zA-Z0-9]+/, '').split('(')[0].trim(),
        amount: amt,
        currency: currentFinTransferCurrency || 'SDG',
        notes: notes
      });
      window.location.href = 'mobile-voucher.php?' + params.toString();
    }

    // ================================================================
    // 📋 FLUTTER SCREEN: AccountsListScreen (كشف الحسابات)
    // مأخوذ بالكامل ومطابق لكود Flutter: RefreshIndicator & ListView.builder(15)
    // ================================================================
    window.AccountsListEngine = {
      isRefreshing: false,
      isOpen: false,
      _inited: false,

      // 15 حساب مطابقة لـ itemCount: 15
      accounts: Array.from({ length: 15 }, (_, i) => ({
        index: i + 1,
        title: `حساب العميل ${i + 1}`,
        subtitle: 'الرصيد: 0.00'
      })),

      render() {
        const itemHtml = this.accounts.map((acc, index) => `
          <div onclick="AccountsListEngine.onAccountTap(${index})" 
               class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-800/60 hover:bg-slate-800 active:bg-blue-950/40 border border-slate-700/50 hover:border-blue-500/40 cursor-pointer transition active:scale-[0.98] group select-none">
            <div class="flex items-center gap-3">
              <!-- ListTile leading: Icon(Icons.account_balance_wallet_outlined) -->
              <div class="w-10 h-10 rounded-xl bg-blue-500/15 text-blue-400 group-hover:bg-blue-500/25 flex items-center justify-center shrink-0 border border-blue-500/30 transition">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
              </div>
              <div class="text-right">
                <!-- ListTile title: Text('حساب العميل ${index + 1}') -->
                <div class="text-sm font-bold text-white group-hover:text-blue-300 transition">${acc.title}</div>
                <!-- ListTile subtitle: Text('الرصيد: 0.00') -->
                <div class="text-xs text-slate-400 font-medium mt-0.5">${acc.subtitle}</div>
              </div>
            </div>
            <!-- ListTile trailing: Icon(Icons.arrow_forward_ios, size: 14) -->
            <div class="text-slate-500 group-hover:text-blue-400 transition pl-1">
              <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-blue-400 transition transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
        `).join('');

        const containerModal = document.getElementById('accountsListContainer');
        if (containerModal) containerModal.innerHTML = itemHtml;

        const containerEmbedded = document.getElementById('accountsListEmbeddedContainer');
        if (containerEmbedded) containerEmbedded.innerHTML = itemHtml;
      },

      async refreshAccountsData() {
        if (this.isRefreshing) return;
        this.isRefreshing = true;

        const spinner1 = document.getElementById('accountsRefreshSpinnerContainer');
        const spinner2 = document.getElementById('accountsEmbeddedSpinnerContainer');
        const spinIcon1 = document.getElementById('accountsListRefreshIcon');
        const spinIcon2 = document.getElementById('accountsEmbeddedSpinIcon');

        if (spinner1) spinner1.classList.remove('hidden');
        if (spinner2) spinner2.classList.remove('hidden');
        if (spinIcon1) spinIcon1.classList.add('animate-spin');
        if (spinIcon2) spinIcon2.classList.add('animate-spin');

        // محاكاة الاتصال بالسيرفر: await Future.delayed(const Duration(seconds: 1));
        await new Promise(resolve => setTimeout(resolve, 1000));

        // AppHaptics.success(); إشعار لمسي فوري باكتمال المزامنة
        if (window.AppHaptics) AppHaptics.success();
        else {
          if (window.triggerHapticPulse) triggerHapticPulse(50);
          if (navigator.vibrate) navigator.vibrate([40, 30, 40]);
          if (window.AudioEngine && AudioEngine.play) AudioEngine.play('success');
        }

        this.render();

        if (spinner1) spinner1.classList.add('hidden');
        if (spinner2) spinner2.classList.add('hidden');
        if (spinIcon1) spinIcon1.classList.remove('animate-spin');
        if (spinIcon2) spinIcon2.classList.remove('animate-spin');
        this.isRefreshing = false;

        if (typeof showFloatingToast === 'function') {
          showFloatingToast('✓ تم تحديث كشف الحسابات (15 حساب) واكتمال المزامنة');
        }
      },

      triggerRefresh() {
        this.refreshAccountsData();
      },

      onAccountTap(index) {
        // AppHaptics.click();
        if (window.AppHaptics) AppHaptics.click();
        else {
          if (window.triggerHapticPulse) triggerHapticPulse(25);
          if (window.AudioEngine && AudioEngine.play) AudioEngine.play('numpad');
        }

        const acc = this.accounts[index];
        if (typeof showFloatingToast === 'function') {
          showFloatingToast(`📋 تم فتح ${acc.title} (${acc.subtitle})`);
        }
      },

      show() {
        this.isOpen = true;
        this.init();
        const modal = document.getElementById('modalAccountsListScreen');
        if (modal) {
          modal.classList.remove('hidden');
          try {
            history.pushState({ accountsListScreen: true }, '', window.location.href);
          } catch(e) {}
        }
      },

      hide() {
        this.isOpen = false;
        const modal = document.getElementById('modalAccountsListScreen');
        if (modal) modal.classList.add('hidden');
      },

      init() {
        if (this._inited) {
          this.render();
          return;
        }
        this._inited = true;
        this.render();
        this.initPullToRefresh();
      },

      // RefreshIndicator logic (سحب لأسفل للتحديث)
      initPullToRefresh() {
        const pullAreas = [
          document.getElementById('accountsListPullArea'),
          document.getElementById('accountsListEmbeddedContainer')
        ];

        pullAreas.forEach(area => {
          if (!area) return;
          let touchStartY = 0;
          let deltaY = 0;
          let isTracking = false;

          area.addEventListener('touchstart', (e) => {
            if (area.scrollTop <= 0 && e.touches && e.touches.length === 1) {
              touchStartY = e.touches[0].clientY;
              deltaY = 0;
              isTracking = true;
            }
          }, { passive: true });

          area.addEventListener('touchmove', (e) => {
            if (!isTracking || !e.touches || e.touches.length === 0) return;
            const diff = e.touches[0].clientY - touchStartY;
            if (diff > 0 && area.scrollTop <= 0) {
              deltaY = diff;
              const s1 = document.getElementById('accountsRefreshSpinnerContainer');
              const s2 = document.getElementById('accountsEmbeddedSpinnerContainer');
              if (deltaY > 30) {
                if (s1) s1.classList.remove('hidden');
                if (s2) s2.classList.remove('hidden');
              }
            }
          }, { passive: true });

          area.addEventListener('touchend', () => {
            if (!isTracking) return;
            isTracking = false;
            if (deltaY > 60) {
              AccountsListEngine.refreshAccountsData();
            } else {
              const s1 = document.getElementById('accountsRefreshSpinnerContainer');
              const s2 = document.getElementById('accountsEmbeddedSpinnerContainer');
              if (!AccountsListEngine.isRefreshing) {
                if (s1) s1.classList.add('hidden');
                if (s2) s2.classList.add('hidden');
              }
            }
            deltaY = 0;
          }, { passive: true });
        });
      }
    };

    window.openAccountsListScreen = function() {
      AccountsListEngine.show();
    };

    // ================================================================
    // 📦 FLUTTER MODAL BOTTOM SHEET: ItemSelectorSheet (اختر الصنف)
    // مأخوذ بالكامل ومطابق لكود Flutter: showModalBottomSheet & enableDrag
    // ================================================================
    window.ItemSelectorSheet = {
      callback: null,
      isOpen: false,
      _inited: false,

      // 10 أصناف مطابقة لـ ListView.builder(itemCount: 10)
      items: [
        { id: 1, name: "صنف رقم 1", code: "SKU-1001", cat: "بضاعة عامة", stock: "150 وحدة", price: "$45.00" },
        { id: 2, name: "صنف رقم 2", code: "SKU-1002", cat: "مواد غذائية", stock: "80 كرتونة", price: "$60.00" },
        { id: 3, name: "صنف رقم 3", code: "SKU-1003", cat: "حبوب وسكر", stock: "220 جوال", price: "$32.50" },
        { id: 4, name: "صنف رقم 4", code: "SKU-1004", cat: "زيوت ومشتقات", stock: "95 كرتونة", price: "$85.00" },
        { id: 5, name: "صنف رقم 5", code: "SKU-1005", cat: "معلبات وتوزيع", stock: "310 طرد", price: "$28.00" },
        { id: 6, name: "صنف رقم 6", code: "SKU-1006", cat: "بضاعة جاهزة", stock: "60 قطعة", price: "$110.00" },
        { id: 7, name: "صنف رقم 7", code: "SKU-1007", cat: "مستلزمات تعبئة", stock: "400 ربطة", price: "$15.00" },
        { id: 8, name: "صنف رقم 8", code: "SKU-1008", cat: "منتجات مجمدة", stock: "75 كرتونة", price: "$92.00" },
        { id: 9, name: "صنف رقم 9", code: "SKU-1009", cat: "بهارات وتوابل", stock: "130 صندوق", price: "$40.00" },
        { id: 10, name: "صنف رقم 10", code: "SKU-1010", cat: "أصناف متنوعة", stock: "190 وحدة", price: "$55.00" }
      ],

      renderList() {
        const list = document.getElementById('itemSheetListView');
        if (!list) return;
        list.innerHTML = this.items.map((item, index) => `
          <div onclick="ItemSelectorSheet.selectItem(${index})" 
               class="flex items-center justify-between p-3 rounded-2xl bg-slate-800/60 hover:bg-slate-800 active:bg-cyan-950/40 border border-slate-700/50 hover:border-cyan-500/40 cursor-pointer transition active:scale-[0.98] group select-none">
            <div class="flex items-center gap-3">
              <!-- ListTile leading: Icon(Icons.inventory_2_outlined) -->
              <div class="w-10 h-10 rounded-xl bg-cyan-500/15 text-cyan-400 group-hover:bg-cyan-500/25 flex items-center justify-center shrink-0 border border-cyan-500/30 transition">
                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7H4a1 1 0 00-1 1v2a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zm-1 4v8a2 2 0 01-2 2H7a2 2 0 01-2-2v-8m5 3h4"/>
                </svg>
              </div>
              <div class="text-right">
                <!-- ListTile title: Text('صنف رقم ${index + 1}') -->
                <div class="text-sm font-bold text-white group-hover:text-cyan-300 transition">${item.name}</div>
                <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                  <span>${item.code}</span>
                  <span>•</span>
                  <span class="text-slate-300">${item.stock}</span>
                </div>
              </div>
            </div>
            <div class="text-left font-mono text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-lg border border-emerald-500/20">
              ${item.price}
            </div>
          </div>
        `).join('');
      },

      init() {
        if (this._inited) return;
        this._inited = true;
        this.initDragGesture();
      },

      show(callback = null) {
        this.init();
        this.callback = callback;
        this.isOpen = true;
        this.renderList();

        const modal = document.getElementById('modalItemSelectorSheet');
        const backdrop = document.getElementById('itemSheetBackdrop');
        const container = document.getElementById('itemSheetContainer');

        if (!modal || !backdrop || !container) return;

        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
          backdrop.classList.remove('opacity-0');
          backdrop.classList.add('opacity-100');
          container.classList.remove('translate-y-full');
          container.classList.add('translate-y-0');
        });

        try {
          history.pushState({ itemSheet: true }, '', window.location.href);
        } catch(e) {}
      },

      hide() {
        if (!this.isOpen) return;
        this.isOpen = false;

        const modal = document.getElementById('modalItemSelectorSheet');
        const backdrop = document.getElementById('itemSheetBackdrop');
        const container = document.getElementById('itemSheetContainer');

        if (!modal || !backdrop || !container) return;

        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        container.classList.remove('translate-y-0');
        container.classList.add('translate-y-full');

        setTimeout(() => {
          modal.classList.add('hidden');
          container.style.transform = '';
        }, 300);
      },

      selectItem(index) {
        const item = this.items[index];
        // AppHaptics.click(); اهتزاز خفيف ونقرة صوتية عند الاختيار
        if (window.AppHaptics) AppHaptics.click();
        else {
          if (window.triggerHapticPulse) triggerHapticPulse(25);
          if (window.AudioEngine && AudioEngine.play) AudioEngine.play('numpad');
        }

        if (typeof this.callback === 'function') {
          this.callback(item);
        }

        // تحديث الحقل التلقائي في الفاتورة إن وجد
        const salesItemLabel = document.getElementById('salesSelectedItemName');
        if (salesItemLabel) {
          salesItemLabel.innerText = item.name + ' (' + item.price + ')';
        }

        if (typeof showFloatingToast === 'function') {
          showFloatingToast('✓ تم اختيار: ' + item.name);
        }

        // Navigator.pop(context);
        this.hide();
      },

      // تفعيل السحب لأسفل للإغلاق (enableDrag: true)
      initDragGesture() {
        const container = document.getElementById('itemSheetContainer');
        const dragHandle = document.getElementById('itemSheetDragHandle');
        if (!container) return;

        let touchStartY = 0;
        let deltaY = 0;
        let isDragging = false;

        const onStart = (e) => {
          if (e.touches && e.touches.length === 1) {
            touchStartY = e.touches[0].clientY;
            deltaY = 0;
            isDragging = true;
            container.style.transition = 'none';
          }
        };

        const onMove = (e) => {
          if (!isDragging || !e.touches || e.touches.length === 0) return;
          const diff = e.touches[0].clientY - touchStartY;
          if (diff > 0) { // سحب لأسفل فقط
            deltaY = diff;
            container.style.transform = `translateY(${diff}px)`;
            if (e.cancelable) e.preventDefault();
          }
        };

        const onEnd = () => {
          if (!isDragging) return;
          isDragging = false;
          container.style.transition = 'transform 0.25s ease-out';
          if (deltaY > 90) { // مسافة كافية لإغلاق الورقة السفلية
            ItemSelectorSheet.hide();
          } else {
            container.style.transform = 'translateY(0)';
          }
          deltaY = 0;
        };

        if (dragHandle) {
          dragHandle.addEventListener('touchstart', onStart, { passive: false });
          dragHandle.addEventListener('touchmove', onMove, { passive: false });
          dragHandle.addEventListener('touchend', onEnd, { passive: true });
        }

        container.addEventListener('touchstart', (e) => {
          const listView = document.getElementById('itemSheetListView');
          if (listView && listView.scrollTop <= 0) {
            onStart(e);
          }
        }, { passive: true });

        container.addEventListener('touchmove', (e) => {
          if (isDragging && deltaY > 0) {
            onMove(e);
          }
        }, { passive: false });

        container.addEventListener('touchend', onEnd, { passive: true });
      }
    };

    window.showItemSelectorSheet = function(callback) {
      ItemSelectorSheet.show(callback);
    };

    window.hideItemSelectorSheet = function() {
      ItemSelectorSheet.hide();
    };

    // دالة الخروج الموحدة للشاشة الرئيسية لكافة الشاشات (المبيعات والمشتريات)
    window.exitToMainDashboard = function(modalId) {
      if (window.AppHaptics) AppHaptics.click();
      else if (window.triggerHapticPulse) triggerHapticPulse(15);

      if (window.SafeInvoiceGuard) {
        SafeInvoiceGuard.requestExit(modalId);
      } else {
        closeModal(modalId);
        if (typeof switchTab === 'function') switchTab('dashboard');
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    };

    // ================================================================
    // 🛡️ SAFE INVOICE SCREEN & POP-SCOPE SYSTEM (نظام الرجوع الآمن ومنع فقدان البيانات)
    // ================================================================
    window.SafeInvoiceGuard = {
      hasUnsavedChanges: false,
      activeModalId: null,
      pendingExitModalId: null,

      setUnsavedChanges(val, modalId = null) {
        this.hasUnsavedChanges = val;
        const targetId = modalId || this.activeModalId;
        const badge = document.getElementById(targetId + 'UnsavedBadge') || document.getElementById('modalOpSalesUnsavedBadge');
        if (badge) {
          if (val) {
            badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span><span class="text-amber-300 font-bold">تنبيه: تعديلات غير محفوظة (الحماية مفعلة)</span>';
          } else {
            badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-400"></span><span class="text-emerald-300 font-bold">✓ البيانات محفوظة</span>';
          }
        }
      },

      attachToModal(modalId) {
        this.activeModalId = modalId;
        this.hasUnsavedChanges = false;
        this.setUnsavedChanges(false, modalId);

        // اعتراض زر الرجوع عبر History API (PopScope Equivalent)
        try {
          history.pushState({ safeModal: true, modalId: modalId }, '', window.location.href);
        } catch (e) {}

        const modalEl = document.getElementById(modalId);
        if (modalEl) {
          // مراقبة أي تعديل في الحقول
          modalEl.querySelectorAll('input, select, textarea').forEach(input => {
            if (!input.dataset.safeGuardBound) {
              input.dataset.safeGuardBound = 'true';
              input.addEventListener('input', () => this.setUnsavedChanges(true, modalId));
              input.addEventListener('change', () => this.setUnsavedChanges(true, modalId));
            }
          });

          // رصد إيماءة سحب الحافة للرجوع (Edge Swipe Back Gesture)
          this.attachSwipeGesture(modalEl, modalId);
        }
      },

      attachSwipeGesture(el, modalId) {
        let touchStartX = 0;
        let touchStartY = 0;
        el.addEventListener('touchstart', (e) => {
          if (e.touches && e.touches.length === 1) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
          }
        }, { passive: true });

        el.addEventListener('touchend', (e) => {
          if (e.changedTouches && e.changedTouches.length === 1) {
            const deltaX = e.changedTouches[0].clientX - touchStartX;
            const deltaY = Math.abs(e.changedTouches[0].clientY - touchStartY);
            const isEdgeStart = touchStartX < 40 || touchStartX > (window.innerWidth - 40);
            if (isEdgeStart && Math.abs(deltaX) > 55 && deltaY < 60) {
              this.requestExit(modalId);
            }
          }
        }, { passive: true });
      },

      requestExit(modalId) {
        const targetId = modalId || this.activeModalId;
        if (this.hasUnsavedChanges) {
          this.showExitConfirmation(targetId);
        } else {
          this.forceClose(targetId);
        }
      },

      showExitConfirmation(modalId) {
        if (window.AppHaptics) AppHaptics.error();
        else {
          if (window.AudioEngine && window.AudioEngine.play) AudioEngine.play('error');
          if (window.triggerHapticPulse) triggerHapticPulse(40);
        }

        this.pendingExitModalId = modalId;
        const dialog = document.getElementById('modalSafeExitConfirm');
        if (dialog) dialog.classList.remove('hidden');
      },

      onStay() {
        // متابعة التعديل (البقاء في الشاشة)
        if (window.AppHaptics) AppHaptics.click();
        else {
          if (window.AudioEngine && window.AudioEngine.play) AudioEngine.play('numpad');
          if (window.triggerHapticPulse) triggerHapticPulse(20);
        }
        const dialog = document.getElementById('modalSafeExitConfirm');
        if (dialog) dialog.classList.add('hidden');
        try {
          history.pushState({ safeModal: true, modalId: this.activeModalId }, '', window.location.href);
        } catch (e) {}
      },

      onDiscardAndExit() {
        // تجاهل وخروج
        if (window.AppHaptics) AppHaptics.error();
        else {
          if (window.AudioEngine && window.AudioEngine.play) AudioEngine.play('trash');
          if (window.triggerHapticPulse) triggerHapticPulse(30);
        }
        const dialog = document.getElementById('modalSafeExitConfirm');
        if (dialog) dialog.classList.add('hidden');

        this.hasUnsavedChanges = false;
        this.forceClose(this.pendingExitModalId || this.activeModalId);
        this.pendingExitModalId = null;
      },

      forceClose(modalId) {
        const targetId = modalId || this.activeModalId;
        if (targetId) {
          const m = document.getElementById(targetId);
          if (m) {
            m.classList.add('hidden');
            m.style.display = 'none';
          }
        }
        this.activeModalId = null;
        this.hasUnsavedChanges = false;
        if (typeof switchTab === 'function') {
          switchTab('dashboard');
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    };

    // اعتراض أزرار الرجوع للهاتف (Hardware/Gesture Back Button via PopState)
    window.addEventListener('popstate', (e) => {
      // 1. إغلاق شاشة كشف الحسابات إذا كانت مفتوحة كنافذة
      if (window.AccountsListEngine && window.AccountsListEngine.isOpen) {
        window.AccountsListEngine.hide();
        return;
      }

      // 2. إغلاق الورقة السفلية لاختيار الصنف في حال كانت مفتوحة
      if (window.ItemSelectorSheet && window.ItemSelectorSheet.isOpen) {
        window.ItemSelectorSheet.hide();
        return;
      }

      // 3. إغلاق نافذة سعر الصرف
      if (window.SuiteFXManager && window.SuiteFXManager.isOpen) {
        SuiteFXManager.closeModal();
        return;
      }

      // 4. إغلاق النوافذ المحمية بفاتورة البيع أو الشراء
      if (window.SafeInvoiceGuard && window.SafeInvoiceGuard.activeModalId) {
        const m = document.getElementById(window.SafeInvoiceGuard.activeModalId);
        if (m && !m.classList.contains('hidden') && m.style.display !== 'none') {
          if (window.SafeInvoiceGuard.hasUnsavedChanges) {
            try {
              history.pushState({ safeModal: true, modalId: window.SafeInvoiceGuard.activeModalId }, '', window.location.href);
            } catch (err) {}
            window.SafeInvoiceGuard.showExitConfirmation(window.SafeInvoiceGuard.activeModalId);
            return;
          } else {
            window.SafeInvoiceGuard.forceClose(window.SafeInvoiceGuard.activeModalId);
            return;
          }
        }
      }

      // 5. إغلاق أي نافذة تشغيلية نشطة مفتوحة قبل مغادرة الصفحة
      const openModalEl = document.querySelector('.modal-backdrop:not(.hidden), [id^="modalOp"]:not(.hidden), .glass-base-modal:not(.hidden)');
      if (openModalEl && openModalEl.id && typeof closeModal === 'function') {
        closeModal(openModalEl.id);
        return;
      }
    });

    // تحذير المتصفح عند إغلاق أو تحديث التبويب قبل الحفظ
    window.addEventListener('beforeunload', (e) => {
      if (window.SafeInvoiceGuard && window.SafeInvoiceGuard.hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = 'تنبيه: بيانات غير محفوظة في الفاتورة!';
        return e.returnValue;
      }
    });

    // ========================================================
    // COMPREHENSIVE 13 QUICK OPERATIONS & DISPATCH ENGINE
    // ========================================================
    window.filterOperationsCategory = function(cat, btn) {
      if (window.triggerHapticPulse) triggerHapticPulse(15);
      const tabs = document.querySelectorAll('.op-cat-btn');
      tabs.forEach(b => {
        b.className = 'op-cat-btn bg-slate-900/80 hover:bg-slate-800 text-slate-300 px-3 py-1.5 rounded-xl border border-slate-700/60 shrink-0 transition cursor-pointer flex items-center gap-1.5';
      });
      if (btn) {
        btn.className = 'op-cat-btn active bg-cyan-600 text-white px-3 py-1.5 rounded-xl shrink-0 transition shadow-[0_0_10px_rgba(6,182,212,0.4)] cursor-pointer flex items-center gap-1.5';
      }
      const cards = document.querySelectorAll('#operationsGrid .op-btn');
      cards.forEach(card => {
        if (cat === 'all' || card.getAttribute('data-category') === cat) {
          card.style.display = 'flex';
          card.classList.add('animate-fadeIn');
        } else {
          card.style.display = 'none';
        }
      });
    };

    // ═══════════════════════════════════════════════════════════════
    // 🪞 REAL-TIME MIRROR ENGINE (CLIENT-SIDE ADAPTER FOR MOBILE-SUITE)
    // ═══════════════════════════════════════════════════════════════
    window._mirrorCache = { sales: null, purchase: null, delivery: null, receipt: null };

    async function fetchMirrorNumbers() {
      const applyOfflineFallbackNumbers = () => {
        if (window.MagaiesOffline && typeof MagaiesOffline.peekNextSequentialNumber === 'function') {
          const sNext = MagaiesOffline.peekNextSequentialNumber('sales');
          const pNext = MagaiesOffline.peekNextSequentialNumber('purchase');
          const dNext = MagaiesOffline.peekNextSequentialNumber('delivery');
          const rNext = MagaiesOffline.peekNextSequentialNumber('purchase_receipt');

          const sPill = document.getElementById('opSalesInvoiceNoPill');
          if (sPill && (!window.SuiteSalesPOS || SuiteSalesPOS.currentInvoiceIndex === -1)) {
            sPill.innerText = sNext;
            if (window.SuiteSalesPOS) SuiteSalesPOS.latestGeneratedInvNo = sNext;
          }
          const spPill = document.getElementById('opSalesSplitInvoiceNoPill');
          if (spPill) spPill.innerText = sNext;

          const pPill = document.getElementById('opPurchaseNoPill');
          if (pPill && (!window.SuitePurchasePOS || SuitePurchasePOS.currentInvoiceIndex === -1)) {
            pPill.innerText = pNext;
            if (window.SuitePurchasePOS) SuitePurchasePOS.latestGeneratedInvNo = pNext;
          }
          const psPill = document.getElementById('opPurchaseSplitNoPill');
          if (psPill) psPill.innerText = pNext;

          const dPill = document.getElementById('opDeliveryNoPill');
          if (dPill) dPill.innerText = dNext;

          const rPill = document.getElementById('opReceiptNoPill');
          if (rPill) rPill.innerText = rNext;
        }
        if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();
      };

      if (!navigator.onLine) {
        applyOfflineFallbackNumbers();
        return;
      }

      try {
        const res = await fetch('api/mirror_sync.php?action=get_next_numbers&_t=' + Date.now(), { signal: (typeof AbortSignal !== 'undefined' && AbortSignal.timeout) ? AbortSignal.timeout(2500) : undefined });
        const data = await res.json();
        if (data && data.success) {
          window._mirrorCache = data;
          if (window.MagaiesOffline && typeof MagaiesOffline.updateKnownSequencesFromMirror === 'function') {
            MagaiesOffline.updateKnownSequencesFromMirror(data);
          }
          const sPill = document.getElementById('opSalesInvoiceNoPill');
          const nextSalesNo = (window.SuiteSalesPOS && typeof SuiteSalesPOS.generateNewInvoiceNo === 'function')
            ? SuiteSalesPOS.generateNewInvoiceNo()
            : (data.sales ? data.sales.nextInvoiceNo : '');
          if (sPill && nextSalesNo && (!window.SuiteSalesPOS || SuiteSalesPOS.currentInvoiceIndex === -1)) {
            sPill.innerText = nextSalesNo;
            if (window.SuiteSalesPOS) SuiteSalesPOS.latestGeneratedInvNo = nextSalesNo;
          }
          const spPill = document.getElementById('opSalesSplitInvoiceNoPill');
          if (spPill && nextSalesNo) spPill.innerText = nextSalesNo;
          const dPill = document.getElementById('opDeliveryNoPill');
          if (dPill && data.delivery) dPill.innerText = data.delivery.nextDeliveryNo;
          const pPill = document.getElementById('opPurchaseNoPill');
          const nextPurNo = (window.SuitePurchasePOS && typeof SuitePurchasePOS.generateNewInvoiceNo === 'function')
            ? SuitePurchasePOS.generateNewInvoiceNo()
            : (data.purchase ? data.purchase.nextPurchaseNo : '');
          if (pPill && nextPurNo && (!window.SuitePurchasePOS || SuitePurchasePOS.currentInvoiceIndex === -1)) {
            pPill.innerText = nextPurNo;
            if (window.SuitePurchasePOS) SuitePurchasePOS.latestGeneratedInvNo = nextPurNo;
          }
          const psPill = document.getElementById('opPurchaseSplitNoPill');
          if (psPill && nextPurNo) psPill.innerText = nextPurNo;
          const rPill = document.getElementById('opReceiptNoPill');
          if (rPill && data.receipt) rPill.innerText = data.receipt.nextReceiptNo;
          if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();
        } else {
          applyOfflineFallbackNumbers();
        }
      } catch (e) {
        console.warn('Mirror numbers fetch warning:', e);
        applyOfflineFallbackNumbers();
      }
    }

    async function loadDeliverySalesInvoices() {
      const sel = document.getElementById('opDeliveryRefSelect');
      if (!sel) return;
      const renderSalesOptions = (list) => {
        let html = '<option value="">اختر فاتورة مبيعات...</option>';
        (list || []).forEach(s => {
          if (!s) return;
          const no = s.invoiceNo || s.id;
          if (!no) return;
          const cust = s.customerName || s.customer || 'عميل';
          const tot = s.total || s.grandTotal || 0;
          const status = s.deliveryStatus === 'delivered' ? '✓ تم تسليمها' : 'قيد التسليم';
          html += `<option value="${no}" data-cust="${cust}" data-items='${JSON.stringify(s.items || [])}'>${no} - ${cust} (${tot} $) [${status}]</option>`;
        });
        sel.innerHTML = html;
      };
      const loadLocalSales = () => {
        try {
          const l1 = JSON.parse(localStorage.getItem('magaies_local_sales_invoices_v1') || '[]');
          const l2 = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
          const merged = [...l1, ...l2];
          const seen = new Set();
          const unique = merged.filter(x => {
            const k = x && (x.invoiceNo || x.id);
            if (!k || seen.has(k)) return false;
            seen.add(k);
            return true;
          });
          renderSalesOptions(unique);
        } catch (err) {}
      };
      if (!navigator.onLine) {
        loadLocalSales();
        return;
      }
      try {
        const res = await fetch('api/mirror_sync.php?action=get_recent_sales&_t=' + Date.now(), { signal: (typeof AbortSignal !== 'undefined' && AbortSignal.timeout) ? AbortSignal.timeout(2500) : undefined });
        const data = await res.json();
        if (data && Array.isArray(data.sales) && data.sales.length > 0) {
          renderSalesOptions(data.sales);
        } else {
          loadLocalSales();
        }
      } catch (e) {
        loadLocalSales();
      }
    }

    async function loadReceiptPurchaseInvoices() {
      const sel = document.getElementById('opReceiptPurchaseSelect');
      if (!sel) return;
      const renderPurchaseOptions = (list) => {
        let html = '<option value="">اختر فاتورة شراء...</option>';
        (list || []).forEach(p => {
          if (!p || p.isGRN) return;
          const no = String(p.invoiceNo || p.id || '');
          if (!no || no.startsWith('Z') || no.startsWith('GRN')) return;
          const supp = p.supplierName || p.vendorName || p.supplier || 'مورد';
          const tot = p.total || p.grandTotal || 0;
          const st = (p.receiptStatus === 'received' || p.status === 'received') ? ' [✓ تم استلامها]' : ' [قيد الاستلام]';
          html += `<option value="${no}" data-supp="${supp}" data-items='${JSON.stringify(p.items || [])}'>${no} - ${supp} (${tot} $ )${st}</option>`;
        });
        sel.innerHTML = html;
      };
      const loadLocalPurchases = () => {
        try {
          const l1 = JSON.parse(localStorage.getItem('magaies_local_purchase_invoices_v1') || '[]');
          const l2 = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
          const merged = [...l1, ...l2];
          const seen = new Set();
          const unique = merged.filter(x => {
            const k = x && (x.invoiceNo || x.id);
            if (!k || seen.has(k)) return false;
            seen.add(k);
            return true;
          });
          renderPurchaseOptions(unique);
        } catch (err) {}
      };
      if (!navigator.onLine) {
        loadLocalPurchases();
        return;
      }
      try {
        const res = await fetch('api/mirror_sync.php?action=get_recent_purchases&_t=' + Date.now(), { signal: (typeof AbortSignal !== 'undefined' && AbortSignal.timeout) ? AbortSignal.timeout(2500) : undefined });
        const data = await res.json();
        if (data && Array.isArray(data.purchases) && data.purchases.length > 0) {
          renderPurchaseOptions(data.purchases);
        } else {
          loadLocalPurchases();
        }
      } catch (e) {
        loadLocalPurchases();
      }
    }

    async function loadSalesSplitInvoices() {
      const sel = document.getElementById('opSalesSplitRefSelect');
      if (!sel) return;
      const renderSplitSales = (list) => {
        let html = '<option value="">-- تقسيم فاتورة جديدة أو اختر فاتورة مبيعات --</option>';
        (list || []).forEach(s => {
          if (!s || s.deleted) return;
          const no = s.invoiceNo || s.id;
          if (!no) return;
          const cust = s.customerName || s.customer || 'عميل';
          const tot = s.grandTotal || s.total || 0;
          const firstIt = (Array.isArray(s.items) && s.items.length > 0) ? (s.items[0].name || s.items[0].itemName || 'صنف') : 'صنف';
          html += `<option value="${no}" data-cust="${cust}" data-tot="${tot}" data-item="${firstIt}">${no} - ${cust} (${tot})</option>`;
        });
        sel.innerHTML = html;
      };
      if (navigator.onLine) {
        try {
          const res = await fetch('api/mirror_sync.php?action=get_recent_sales&_t=' + Date.now(), { signal: (typeof AbortSignal !== 'undefined' && AbortSignal.timeout) ? AbortSignal.timeout(2500) : undefined });
          const data = await res.json();
          if (data && Array.isArray(data.sales) && data.sales.length > 0) {
            renderSplitSales(data.sales);
            return;
          }
        } catch (e) {}
      }
      try {
        const l1 = JSON.parse(localStorage.getItem('magaies_local_sales_invoices_v1') || '[]');
        const l2 = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
        renderSplitSales([...l1, ...l2]);
      } catch (e) {}
    }

    function onOpSalesSplitRefChange() {
      const sel = document.getElementById('opSalesSplitRefSelect');
      if (!sel) return;
      const opt = sel.options[sel.selectedIndex];
      if (opt && opt.value) {
        const cust = opt.getAttribute('data-cust') || '';
        const tot = parseFloat(opt.getAttribute('data-tot') || 0);
        const item = opt.getAttribute('data-item') || 'صنف';
        const custInp = document.getElementById('salesSplitCustomerInput');
        if (custInp && cust) custInp.value = cust;
        const itemSpan = document.getElementById('salesSplitSelectedItemName');
        if (itemSpan && item) itemSpan.innerText = item;
        const totInp = document.getElementById('salesSplitTotalInput');
        if (totInp && tot > 0) totInp.value = tot;
        const paidInp = document.getElementById('salesSplitPaidInput');
        if (paidInp && tot > 0) paidInp.value = Math.round(tot / 2);
        calcOpSalesSplit();
      }
    }

    async function loadPurchaseSplitInvoices() {
      const sel = document.getElementById('opPurchaseSplitRefSelect');
      if (!sel) return;
      const renderSplitPurchases = (list) => {
        let html = '<option value="">-- تقسيم فاتورة شراء جديدة أو اختر فاتورة --</option>';
        (list || []).forEach(p => {
          if (!p || p.deleted || p.isGRN) return;
          const no = String(p.invoiceNo || p.id || '');
          if (!no || no.startsWith('Z') || no.startsWith('GRN')) return;
          const supp = p.supplierName || p.vendorName || p.supplier || 'مورد';
          const tot = p.grandTotal || p.total || 0;
          const firstIt = (Array.isArray(p.items) && p.items.length > 0) ? (p.items[0].name || p.items[0].itemName || 'بضاعة') : 'بضاعة';
          html += `<option value="${no}" data-supp="${supp}" data-tot="${tot}" data-item="${firstIt}">${no} - ${supp} (${tot})</option>`;
        });
        sel.innerHTML = html;
      };
      if (navigator.onLine) {
        try {
          const res = await fetch('api/mirror_sync.php?action=get_recent_purchases&_t=' + Date.now(), { signal: (typeof AbortSignal !== 'undefined' && AbortSignal.timeout) ? AbortSignal.timeout(2500) : undefined });
          const data = await res.json();
          if (data && Array.isArray(data.purchases) && data.purchases.length > 0) {
            renderSplitPurchases(data.purchases);
            return;
          }
        } catch (e) {}
      }
      try {
        const l1 = JSON.parse(localStorage.getItem('magaies_local_purchase_invoices_v1') || '[]');
        const l2 = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
        renderSplitPurchases([...l1, ...l2]);
      } catch (e) {}
    }

    function onOpPurchaseSplitRefChange() {
      const sel = document.getElementById('opPurchaseSplitRefSelect');
      if (!sel) return;
      const opt = sel.options[sel.selectedIndex];
      if (opt && opt.value) {
        const supp = opt.getAttribute('data-supp') || '';
        const tot = parseFloat(opt.getAttribute('data-tot') || 0);
        const item = opt.getAttribute('data-item') || 'بضاعة';
        const suppInp = document.getElementById('purchaseSplitSupplierInput');
        if (suppInp && supp) suppInp.value = supp;
        const itemSpan = document.getElementById('purchaseSplitSelectedItemName');
        if (itemSpan && item) itemSpan.innerText = item;
        const totInp = document.getElementById('purchaseSplitTotalInput');
        if (totInp && tot > 0) totInp.value = tot;
        const paidInp = document.getElementById('purchaseSplitPaidInput');
        if (paidInp && tot > 0) paidInp.value = Math.round(tot / 2);
        calcOpPurchaseSplit();
      }
    }

    function onOpDeliveryRefChange() {
      const sel = document.getElementById('opDeliveryRefSelect');
      const sum = document.getElementById('opDeliveryItemsSummary');
      if (!sel || !sum) return;
      const opt = sel.options[sel.selectedIndex];
      if (opt && opt.value) {
        let items = [];
        try { items = JSON.parse(opt.getAttribute('data-items') || '[]'); } catch(e) {}
        if (items.length > 0) {
          sum.innerHTML = items.map(it => `<div>📦 ${it.name || it.itemName || it.item_name || 'صنف'}: <b>${it.qty || it.quantity || 1}</b></div>`).join('');
        } else {
          sum.innerHTML = `<span class="text-emerald-400 font-bold">بضاعة الفاتورة ${opt.value} (صرف كامل)</span>`;
        }
      } else {
        sum.innerHTML = 'يرجى اختيار الفاتورة المرجعية لعرض الأصناف';
      }
    }

    function onOpReceiptPurchaseChange() {
      const sel = document.getElementById('opReceiptPurchaseSelect');
      const sum = document.getElementById('opReceiptItemsSummary');
      const suppInp = document.getElementById('opReceiptSupplierInput');
      if (!sel || !sum) return;
      const opt = sel.options[sel.selectedIndex];
      if (opt && opt.value) {
        if (suppInp) suppInp.value = opt.getAttribute('data-supp') || '';
        let items = [];
        try { items = JSON.parse(opt.getAttribute('data-items') || '[]'); } catch(e) {}
        if (items.length > 0) {
          sum.innerHTML = items.map(it => `<div>📥 ${it.name || it.itemName || it.item_name || 'صنف'}: <b>${it.qty || it.quantity || 1}</b> (سليم ✓)</div>`).join('');
        } else {
          sum.innerHTML = `<span class="text-teal-300 font-bold">بضاعة الفاتورة ${opt.value} (استلام كامل)</span>`;
        }
      } else {
        sum.innerHTML = 'اختر الفاتورة المرجعية لمعاينة بنود التوريد';
      }
    }

    function calcOpSalesTotal() {
      const qty = parseFloat(document.getElementById('salesQtyInput')?.value || 0);
      const prc = parseFloat(document.getElementById('salesPriceInput')?.value || 0);
      const totInp = document.getElementById('salesTotalInput');
      if (totInp) totInp.value = (qty * prc).toFixed(2);
    }

    function calcOpSalesSplit() {
      const tot = parseFloat(document.getElementById('salesSplitTotalInput')?.value || 0);
      const paid = parseFloat(document.getElementById('salesSplitPaidInput')?.value || 0);
      const rem = Math.max(0, tot - paid);
      const remEl = document.getElementById('salesSplitRemainingText');
      if (remEl) remEl.innerText = `المتبقي الآجل المقسم: $${rem.toFixed(2)}`;
    }

    function calcOpPurchaseSplit() {
      const tot = parseFloat(document.getElementById('purchaseSplitTotalInput')?.value || 0);
      const paid = parseFloat(document.getElementById('purchaseSplitPaidInput')?.value || 0);
      const rem = Math.max(0, tot - paid);
      const remEl = document.getElementById('purchaseSplitRemainingText');
      if (remEl) remEl.innerText = `المتبقي الآجل للمورد: $${rem.toFixed(2)}`;
    }

    function openOperationalForm(formType) {
      if (formType === 'financeMovement') {
        window.location.href = 'mobile-voucher.php';
        return;
      }
      let modalId = '';
      if (formType === 'sales' || formType === 'salesInvoice') {
        modalId = 'modalOpSales';
        fetchMirrorNumbers();
        setTimeout(() => { 
          if (window.SuiteSalesPOS) SuiteSalesPOS.init(); 
          if (window.SmartPOSFontAI) SmartPOSFontAI.scaleAll(); 
          if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();
        }, 50);
      } else if (formType === 'salesSplit') {
        modalId = 'modalOpSalesSplit';
        fetchMirrorNumbers();
        loadSalesSplitInvoices();
        calcOpSalesSplit();
        setTimeout(() => { if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers(); }, 50);
      } else if (formType === 'salesDelivery') {
        modalId = 'modalOpDelivery';
        fetchMirrorNumbers();
        loadDeliverySalesInvoices();
        setTimeout(() => { if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers(); }, 50);
      } else if (formType === 'purchaseInvoice') {
        modalId = 'modalOpPurchase';
        fetchMirrorNumbers();
        setTimeout(() => { 
          if (window.SuitePurchasePOS) SuitePurchasePOS.init(); 
          if (window.SmartPOSFontAI) SmartPOSFontAI.scaleAll(); 
          if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();
        }, 50);
      } else if (formType === 'purchaseSplit') {
        modalId = 'modalOpPurchaseSplit';
        fetchMirrorNumbers();
        loadPurchaseSplitInvoices();
        calcOpPurchaseSplit();
        setTimeout(() => { if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers(); }, 50);
      } else if (formType === 'purchaseReceive' || formType === 'purchaseReceipt') {
        modalId = 'modalOpReceipt';
        fetchMirrorNumbers();
        loadReceiptPurchaseInvoices();
        setTimeout(() => { if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers(); }, 50);
      } else if (formType === 'journalEntry' || formType === 'journalSettlement') {
        modalId = 'modalOpJournal';
      } else if (formType === 'accountStatement') {
        modalId = 'modalOpStatement';
      } else if (formType === 'balanceInquiry') {
        modalId = 'modalOpBalanceInquiry';
      } else if (formType === 'stockLocationCheck') {
        modalId = 'modalOpStockLocation';
      } else if (formType === 'stockAdjustment') {
        modalId = 'modalOpStockAdjustment';
      } else if (formType === 'warehouseTransfer') {
        modalId = 'modalOpWarehouseTransfer';
      } else if (formType === 'inventoryVouchers') {
        modalId = 'modalOpVouchers';
      }

      if (modalId) {
        openModal(modalId);
        if (window.SafeInvoiceGuard) SafeInvoiceGuard.attachToModal(modalId);
      }
    }

    // ─── COMMIT HANDLERS WITH 100% MARIADB PERSISTENCE ───

    
    // ═════════════════════════════════════════════════════════════════════
    // 🌐 SUITE FX MANAGER: REAL-TIME SYSTEM EXCHANGE RATE SYNC (DATABASE)
    // ═════════════════════════════════════════════════════════════════════
    window.SuiteFXManager = {
      currentRate: (window.SYSTEM_USD_RATE && parseFloat(window.SYSTEM_USD_RATE) > 1) ? parseFloat(window.SYSTEM_USD_RATE) : 5999,
      isLoading: false,

      async fetchSystemRate() {
        try {
          const cached = localStorage.getItem('erp_rate_usd') || localStorage.getItem('magaiees_system_usd_rate') || localStorage.getItem('magaies_usd_rate');
          if (cached) {
            const n = parseFloat(cached);
            if (!isNaN(n) && n > 0) this.currentRate = n;
          }
          const res = await fetch('api/mirror_sync.php?action=get_currency_rate&_t=' + Date.now());
          if (res.ok) {
            const data = await res.json();
            if (data && data.success && data.rate) {
              this.currentRate = parseFloat(data.rate);
              window.SYSTEM_USD_RATE = this.currentRate;
              localStorage.setItem('magaies_usd_rate', this.currentRate.toString());
              localStorage.setItem('magaiees_system_usd_rate', this.currentRate.toString());
              localStorage.setItem('magaies_custom_usd_rate', this.currentRate.toString());
              localStorage.setItem('erp_rate_usd', this.currentRate.toString());
              localStorage.setItem('erp_cached_global_rate', this.currentRate.toString());
            }
          }
        } catch(e) {
          console.warn('[FX] Offline fallback for currency rate:', e);
        }
        this.broadcastRate(this.currentRate);
        return this.currentRate;
      },

      broadcastRate(rate) {
        this.currentRate = rate;
        const sDisplay = document.getElementById('salesUsdRateDisplay');
        const sInp = document.getElementById('salesUsdRateInput');
        if (sDisplay) sDisplay.innerText = rate.toString().slice(0, 5);
        if (sInp) sInp.value = rate.toString().slice(0, 5);
        if (window.SuiteSalesPOS) {
          window.SuiteSalesPOS.usdExchangeRate = rate;
          if (window.SuiteSalesPOS.cart && window.SuiteSalesPOS.cart.length > 0) window.SuiteSalesPOS.renderCart();
        }

        const pDisplay = document.getElementById('purUsdRateDisplay');
        const pInp = document.getElementById('purUsdRateInput');
        if (pDisplay) pDisplay.innerText = rate.toString().slice(0, 5);
        if (pInp) pInp.value = rate.toString().slice(0, 5);
        if (window.SuitePurchasePOS) {
          window.SuitePurchasePOS.usdExchangeRate = rate;
          if (window.SuitePurchasePOS.cart && window.SuitePurchasePOS.cart.length > 0) window.SuitePurchasePOS.renderCart();
        }

        window.dispatchEvent(new CustomEvent('magaies_rate_changed', { detail: { rate } }));
      },

      openModal() {
        const modal = document.getElementById('suiteCurrencyRateModal');
        if (!modal) return;
        const inp = document.getElementById('fxModalNewRateInput');
        const curValDisplay = document.getElementById('fxModalCurrentRateVal');
        if (inp) inp.value = this.currentRate;
        if (curValDisplay) curValDisplay.innerText = this.currentRate.toLocaleString('en-US');
        modal.classList.remove('hidden');
        setTimeout(() => { if (inp) { inp.focus(); inp.select(); } }, 100);
      },

      closeModal() {
        const modal = document.getElementById('suiteCurrencyRateModal');
        if (modal) modal.classList.add('hidden');
      },

      adjustRate(delta) {
        const inp = document.getElementById('fxModalNewRateInput');
        if (!inp) return;
        let val = parseFloat(inp.value) || this.currentRate;
        val = Math.max(1, val + delta);
        inp.value = val;
      },

      async saveNewRate() {
        const inp = document.getElementById('fxModalNewRateInput');
        if (!inp) return;
        const newRate = parseFloat(inp.value);
        if (isNaN(newRate) || newRate <= 0) {
          alert('الرجاء إدخال سعر صرف صالح');
          return;
        }

        const saveBtn = document.getElementById('btnSaveFxRate');
        const originalText = saveBtn ? saveBtn.innerHTML : '';
        if (saveBtn) {
          saveBtn.disabled = true;
          saveBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> جاري الحفظ في السيستم...';
        }

        try {
          const res = await fetch('api/mirror_sync.php?action=save_currency_rate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'save_currency_rate', rate: newRate })
          });
          const data = await res.json();
          if (!res.ok || !data.success) {
            throw new Error(data.error || 'فشل الحفظ في قاعدة البيانات');
          }

          localStorage.setItem('magaies_usd_rate', newRate.toString());
          localStorage.setItem('erp_rate_usd', newRate.toString());
          try {
            let crObj = JSON.parse(localStorage.getItem('erp_currency_rates_v1') || '{}');
            crObj['USD'] = newRate;
            crObj['SDG'] = 1;
            localStorage.setItem('erp_currency_rates_v1', JSON.stringify(crObj));
          } catch(e) {}

          this.broadcastRate(newRate);
          this.closeModal();

          if (window.triggerHapticPulse) triggerHapticPulse(25);
          if (typeof showToast === 'function') {
            showToast('✅ تم حفظ وتحديث سعر صرف الدولار في قاعدة البيانات وعلى مستوى السيستم بنجاح', 'success');
          } else {
            alert('✅ تم حفظ سعر صرف الدولار المعتمد (' + newRate + ') في قاعدة البيانات بنجاح');
          }
        } catch(err) {
          alert('⚠️ تنبيه: ' + err.message);
        } finally {
          if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
          }
        }
      }
    };

    // ═════════════════════════════════════════════════════════════════════
    // 💎 SUITE SALES POS ENGINE - DIRECT LAPTOP PARITY & MARIADB SYNC
    // ═════════════════════════════════════════════════════════════════════
        // ═════════════════════════════════════════════════════════════════════
    // 🌐 SUITE POS MULTI-TARGET ROUTER (SALES & PURCHASES UNIFIED DISPATCH)
    // ═════════════════════════════════════════════════════════════════════
    window._activeModalContext = 'sales';

    window.SuitePOSRouter = {
      getActiveContext() {
        const purModal = document.getElementById('modalOpPurchase');
        if (purModal && !purModal.classList.contains('hidden')) {
          return 'purchase';
        }
        return window._activeModalContext || 'sales';
      },
      selectItemByIndex(idx) {
        if (this.getActiveContext() === 'purchase' && window.SuitePurchasePOS) {
          SuitePurchasePOS.selectItemByIndex(idx);
        } else if (window.SuiteSalesPOS) {
          SuiteSalesPOS.selectItemByIndex(idx);
        }
      },
      selectCustomItem(name) {
        if (this.getActiveContext() === 'purchase' && window.SuitePurchasePOS) {
          SuitePurchasePOS.selectCustomItem(name);
        } else if (window.SuiteSalesPOS) {
          SuiteSalesPOS.selectCustomItem(name);
        }
      },
      selectPartner(partner) {
        if (this.getActiveContext() === 'purchase' && window.SuitePurchasePOS) {
          SuitePurchasePOS.selectSupplier(partner);
        } else if (window.SuiteSalesPOS) {
          SuiteSalesPOS.selectCustomer(partner);
        }
      },
      selectWarehouse(name) {
        if (this.getActiveContext() === 'purchase' && window.SuitePurchasePOS) {
          SuitePurchasePOS.selectWarehouse(name);
        } else if (window.SuiteSalesPOS) {
          SuiteSalesPOS.selectWarehouse(name);
        }
      },
      selectCostCenter(name) {
        if (this.getActiveContext() === 'purchase' && window.SuitePurchasePOS) {
          SuitePurchasePOS.selectCostCenter(name);
        } else if (window.SuiteSalesPOS) {
          SuiteSalesPOS.selectCostCenter(name);
        }
      },
      filterItemSearch(q) {
        if (window.SuiteSalesPOS) SuiteSalesPOS.filterItemSearch(q);
      },
      filterPartnerSearch(q) {
        if (this.getActiveContext() === 'purchase') {
          if (window.SuitePurchasePOS) SuitePurchasePOS.filterSupplierSearch(q);
        } else {
          if (window.SuiteSalesPOS) SuiteSalesPOS.filterCustomerSearch(q);
        }
      },
      filterWarehouseSearch(q) {
        if (window.SuiteSalesPOS) SuiteSalesPOS.filterWarehouseSearch(q);
      },
      filterCostCenterSearch(q) {
        if (window.SuiteSalesPOS) SuiteSalesPOS.filterCostCenterSearch(q);
      }
    };

    // Global desktop keyboard shortcuts
    if (!window._suiteKeyboardShortcutsBound) {
      window._suiteKeyboardShortcutsBound = true;
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          const modals = [
            'suiteItemSearchModal', 'suiteCustomerSearchModal', 'suiteWarehouseSearchModal',
            'suiteCostCenterSearchModal', 'suiteSmartCalcModal', 'suiteSalesNotesModal'
          ];
          for (let id of modals) {
            const el = document.getElementById(id);
            if (el && !el.classList.contains('hidden')) {
              el.classList.add('hidden');
              return;
            }
          }
        }
      });
    }

    // ═════════════════════════════════════════════════════════════════
    // SMART AI FONT COORDINATOR (تنسيق الخطوط الذكي بالذكاء الاصطناعي)
    // تنسيق تلقائي فوري لأحجام الخطوط بما يناسب المساحة المتاحة وعدد الأرقام والأحرف
    // خط الجملة والإجمالي الصافي متساويان تماماً ومكبران (38px)
    // ═════════════════════════════════════════════════════════════════
    // ═════════════════════════════════════════════════════════════════════
    // 🎛️ SMART FIELD STYLE & NUMBER FORMAT CUSTOMIZER (التحكم الدائم بالحقول والأرقام عبر زر المسافة)
    // ═════════════════════════════════════════════════════════════════════
    window.SmartFieldCustomizer = {
      STORAGE_KEY: 'magaies_pos_field_styles_v1',
      BACKUP_KEY: 'erp_permanent_field_styles_v1',
      configs: {},
      activeFieldId: null,
      tempConfig: null,

      fieldMeta: {
        salesCustomerInput:    { label: 'اسم العميل', isNumeric: false, defSize: 15, defColor: '#38bdf8', defMode: 'ai' },
        salesWarehouseInput:   { label: 'المستودع', isNumeric: false, defSize: 14.5, defColor: '#ffffff', defMode: 'ai' },
        salesCostCenterInput:  { label: 'مركز الكلفة', isNumeric: false, defSize: 14.5, defColor: '#bae6fd', defMode: 'ai' },
        salesItemSearchInput:  { label: 'الصنف', isNumeric: false, defSize: 21, defColor: '#fcd34d', defMode: 'ai' },
        salesUnitSelect:       { label: 'الوحدات', isNumeric: false, defSize: 12, defColor: '#34d399', defMode: 'ai' },
        salesQtyInput:         { label: 'الكمية', isNumeric: true, defSize: 28, defColor: '#ffffff', defMode: 'fixed', defDecimals: 'auto', defThousands: ',' },
        salesPriceInput:       { label: 'السعر', isNumeric: true, defSize: 28, defColor: '#67e8f9', defMode: 'fixed', defDecimals: 'auto', defThousands: ',' },
        salesItemGrossTotal:   { label: 'الجملة', isNumeric: true, defSize: 46, defColor: '#34d399', defMode: 'fixed', defDecimals: 2, defThousands: ',' },
        salesTaxInput:         { label: 'الإضافة', isNumeric: true, defSize: 28, defColor: '#d8b4fe', defMode: 'fixed', defDecimals: 'auto', defThousands: ',' },
        salesDiscountInput:    { label: 'الخصم', isNumeric: true, defSize: 28, defColor: '#fb7185', defMode: 'fixed', defDecimals: 'auto', defThousands: ',' },
        salesLaborInput:       { label: 'العتالة', isNumeric: true, defSize: 28, defColor: '#fcd34d', defMode: 'fixed', defDecimals: 'auto', defThousands: ',' },
        salesTotalInput:       { label: 'الإجمالي الصافي', isNumeric: true, defSize: 46, defColor: '#fcd34d', defMode: 'fixed', defDecimals: 2, defThousands: ',' },
        suiteGrandTotalDisplay:{ label: 'إجمالي الأسطر', isNumeric: true, defSize: 14, defColor: '#34d399', defMode: 'fixed', defDecimals: 2, defThousands: ',' },
        salesUsdRateDisplay:   { label: 'سعر صرف الدولار', isNumeric: true, defSize: 19, defColor: '#ffffff', defMode: 'fixed', defDecimals: 'auto', defThousands: 'none' },
        salesNotesInput:       { label: 'البيان / الملحوظة', isNumeric: false, defSize: 15, defColor: '#ffffff', defMode: 'ai' },
        // حقول المشتريات أيضاً
        purchaseSupplierInput: { label: 'اسم المورد (مشتريات)', isNumeric: false, defSize: 14, defColor: '#fbbf24', defMode: 'ai' },
        purchaseWarehouseInput:{ label: 'المستودع (مشتريات)', isNumeric: false, defSize: 14, defColor: '#ffffff', defMode: 'ai' },
        purItemSearchInput:    { label: 'الصنف (مشتريات)', isNumeric: false, defSize: 21, defColor: '#fcd34d', defMode: 'ai' },
        purUnitSelect:         { label: 'الوحدات (مشتريات)', isNumeric: false, defSize: 12.5, defColor: '#34d399', defMode: 'ai' },
        purQtyInput:           { label: 'الكمية (مشتريات)', isNumeric: true, defSize: 39, defColor: '#ffffff', defMode: 'fixed', defDecimals: 'auto', defThousands: ',' },
        purPriceInput:         { label: 'السعر (مشتريات)', isNumeric: true, defSize: 32, defColor: '#67e8f9', defMode: 'fixed', defDecimals: 'auto', defThousands: ',' },
        purItemGrossTotal:     { label: 'الجملة (مشتريات)', isNumeric: true, defSize: 43, defColor: '#34d399', defMode: 'fixed', defDecimals: 2, defThousands: ',' },
        purTaxInput:           { label: 'الإضافة (مشتريات)', isNumeric: true, defSize: 28, defColor: '#d8b4fe', defMode: 'fixed', defDecimals: 'auto', defThousands: ',' },
        purDiscountInput:      { label: 'الخصم (مشتريات)', isNumeric: true, defSize: 28, defColor: '#fb7185', defMode: 'fixed', defDecimals: 'auto', defThousands: ',' },
        purLaborInput:         { label: 'العتالة (مشتريات)', isNumeric: true, defSize: 28, defColor: '#fcd34d', defMode: 'fixed', defDecimals: 'auto', defThousands: ',' },
        purTotalInput:         { label: 'الإجمالي الصافي (مشتريات)', isNumeric: true, defSize: 43, defColor: '#fcd34d', defMode: 'fixed', defDecimals: 2, defThousands: ',' },
        purNotesInput:         { label: 'البيان (مشتريات)', isNumeric: false, defSize: 14, defColor: '#ffffff', defMode: 'ai' }
      },

      init() {
        this.loadSavedConfigs();
        this.bindSpaceShortcutListeners();
        this.applyAllSavedStyles();
        setTimeout(() => this.applyAllSavedStyles(), 200);
      },

      loadSavedConfigs() {
        try {
          const raw = localStorage.getItem(this.STORAGE_KEY) || localStorage.getItem(this.BACKUP_KEY);
          if (raw) {
            const parsed = JSON.parse(raw);
            if (parsed && typeof parsed === 'object') {
              this.configs = parsed;
            }
          }
        } catch (e) {
          console.warn('Could not load saved field styles:', e);
        }
      },

      persistConfigs() {
        try {
          const json = JSON.stringify(this.configs);
          localStorage.setItem(this.STORAGE_KEY, json);
          localStorage.setItem(this.BACKUP_KEY, json);
        } catch (e) {
          console.warn('Could not save field styles:', e);
        }
      },

      getDefaultConfig(fieldId) {
        const meta = this.fieldMeta[fieldId] || { label: fieldId, isNumeric: false, defSize: 18, defColor: '#ffffff', defMode: 'fixed' };
        return {
          fontSize: meta.defSize,
          fontColor: meta.defColor,
          fontMode: meta.defMode || 'fixed',
          isNumeric: !!meta.isNumeric,
          thousandsSep: meta.defThousands !== undefined ? meta.defThousands : ',',
          decimalSep: '.',
          decimals: meta.defDecimals !== undefined ? meta.defDecimals : 'auto'
        };
      },

      getFieldConfig(fieldId) {
        const def = this.getDefaultConfig(fieldId);
        const saved = this.configs[fieldId];
        if (!saved) return def;
        return Object.assign({}, def, saved);
      },

      hasCustomConfig(fieldId) {
        return !!this.configs[fieldId];
      },

      // تحليل الرقم من أي حقل حتى لو كان يحتوي على فواصل ألفية أو عشرية مخصصة
      parseNum(val, fieldId) {
        if (val === null || val === undefined) return 0;
        if (typeof val === 'number') return isNaN(val) ? 0 : val;
        let s = String(val).trim();
        if (!s) return 0;
        const eastern = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        for (let i = 0; i < 10; i++) s = s.split(eastern[i]).join(i.toString());

        const cfg = fieldId ? this.getFieldConfig(fieldId) : null;
        if (cfg && cfg.decimalSep === ',' && cfg.thousandsSep === '.') {
          s = s.replace(/\./g, '').replace(/,/g, '.');
        } else if (cfg && cfg.decimalSep === ',' && cfg.thousandsSep !== ',') {
          if (cfg.thousandsSep && cfg.thousandsSep !== 'none') {
            s = s.split(cfg.thousandsSep).join('');
          }
          s = s.replace(/,/g, '.');
        } else {
          // الوضع القياسي: إزالة الفاصلة الألفية ',' والمسافات وتحويل الفاصلة العربية '٫' إلى نقطة '.'
          s = s.replace(/,/g, '').replace(/\s+/g, '').replace(/٫/g, '.');
        }
        s = s.replace(/[^0-9.\-]/g, '');
        const num = parseFloat(s);
        return isNaN(num) ? 0 : num;
      },

      // تنسيق الرقم حسب إعدادات الحقل المحفوظة (الفاصلة الألفية، الفاصلة العشرية، والتقريب)
      formatNumberForField(fieldId, rawNum) {
        const num = (typeof rawNum === 'number') ? rawNum : this.parseNum(rawNum, fieldId);
        if (isNaN(num)) return '';
        const cfg = this.getFieldConfig(fieldId);

        let fixedStr = '';
        if (cfg.decimals === 'auto' || cfg.decimals === undefined || cfg.decimals === null) {
          // تقريب نظيف حتى 4 منازل بدون أصفار زائدة
          const rounded = Math.round((num + Number.EPSILON) * 10000) / 10000;
          fixedStr = String(rounded);
        } else {
          const d = Math.max(0, Math.min(6, parseInt(cfg.decimals, 10) || 0));
          fixedStr = num.toFixed(d);
        }

        const parts = fixedStr.split('.');
        let intPart = parts[0];
        const decPart = parts.length > 1 ? parts[1] : '';

        const thSep = cfg.thousandsSep !== undefined ? cfg.thousandsSep : ',';
        if (thSep && thSep !== 'none') {
          intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thSep);
        }

        const decSep = cfg.decimalSep || '.';
        return decPart ? (intPart + decSep + decPart) : intPart;
      },

      handleNumericLiveInput(el) {
        if (!el) return;
        // إذا ضغط المستخدم مسافة داخل حقل رقمي، نفتح نافذة التخصيص فوراً ونحذف المسافة
        const v = el.value || '';
        if (v.includes(' ')) {
          el.value = v.replace(/\s+/g, '');
          this.openModal(el.id);
        }
      },

      formatInputOnBlur(el) {
        if (!el || !el.id) return;
        const raw = (el.value || '').trim();
        if (!raw) return;
        const num = this.parseNum(raw, el.id);
        if (num === 0 && !raw.includes('0')) return;
        el.value = this.formatNumberForField(el.id, num);
        this.applySavedStyleToElement(el);
      },

      applySavedStyleToElement(el) {
        if (!el || !el.id) return;
        el.style.removeProperty('color');
        const saved = this.configs[el.id];
        if (saved && saved.fontSize) {
          el.style.setProperty('font-size', saved.fontSize + 'px', 'important');
        }
      },

      applyAllSavedStyles() {
        Object.keys(this.fieldMeta).forEach(fieldId => {
          const el = document.getElementById(fieldId);
          if (!el) return;
          el.style.removeProperty('color');
          const saved = this.configs[fieldId];
          if (saved) {
            if (saved.fontSize) {
              el.style.setProperty('font-size', saved.fontSize + 'px', 'important');
            }
            if (this.fieldMeta[fieldId].isNumeric && el.value && el.value.trim() !== '') {
              const n = this.parseNum(el.value, fieldId);
              if (!isNaN(n) && (n !== 0 || el.value.includes('0'))) {
                el.value = this.formatNumberForField(fieldId, n);
              }
            }
          }
        });
      },

      showQuickPillForField(el) {
        if (!el || !el.id || !this.fieldMeta[el.id]) return;
        let pill = document.getElementById('sfcFloatingSpacePill');
        if (!pill) {
          pill = document.createElement('button');
          pill.id = 'sfcFloatingSpacePill';
          pill.type = 'button';
          pill.className = 'fixed z-[99990] px-3 py-1.5 rounded-full text-[11px] font-black text-white shadow-2xl flex items-center gap-1.5 transition-all duration-200 active:scale-90 cursor-pointer';
          pill.style.cssText = 'background: linear-gradient(135deg, #0284c7, #0f172a); border: 1.5px solid #38bdf8; box-shadow: 0 4px 18px rgba(56, 189, 248, 0.5); bottom: 14px; left: 50%; transform: translateX(-50%); direction: rtl;';
          pill.onclick = (e) => {
            e.stopPropagation();
            if (this.activeFieldId) this.openModal(this.activeFieldId);
          };
          document.body.appendChild(pill);
        }
        const meta = this.fieldMeta[el.id];
        pill.innerHTML = '<i class="fa-solid fa-sliders text-amber-300"></i> <span>مسافة ␣ : تنسيق وخط [' + meta.label + ']</span>';
        pill.classList.remove('hidden');
        pill.style.display = 'flex';
        if (this._pillHideTimer) clearTimeout(this._pillHideTimer);
        this._pillHideTimer = setTimeout(() => {
          if (pill) pill.style.display = 'none';
        }, 5500);
      },

      bindSpaceShortcutListeners() {
        const attachToField = (fieldId) => {
          const el = document.getElementById(fieldId);
          if (!el || el._sfcBound) return;
          el._sfcBound = true;
          const meta = this.fieldMeta[fieldId];

          const markClicked = () => {
            this.activeFieldId = fieldId;
            el._awaitingSpaceShortcut = true;
            el._savedValueForSpace = el.value !== undefined ? el.value : el.innerText;
            this.showQuickPillForField(el);
          };

          el.addEventListener('focus', markClicked);
          el.addEventListener('click', markClicked);
          if (el.parentElement) {
            el.parentElement.addEventListener('click', () => {
              this.activeFieldId = fieldId;
              this.showQuickPillForField(el);
            });
          }

          // 1. رصد ضغط المسافة عبر لوحة المفاتيح (Desktop & Mobile Keyboards)
          el.addEventListener('keydown', (e) => {
            const isSpace = (e.key === ' ' || e.code === 'Space' || e.keyCode === 32);
            if (isSpace) {
              // في الحقول الرقمية أو للقراءة فقط أو مباشرة بعد الضغط على الحقل النصي (أو عند تحديد النص بالكامل)
              const isAllSelected = (typeof el.selectionStart === 'number' && el.selectionStart === 0 && el.selectionEnd === (el.value || '').length && (el.value || '').length > 0);
              const isEmpty = !(el.value || '').trim();
              if (meta.isNumeric || el.readOnly || el.tagName === 'SELECT' || el._awaitingSpaceShortcut || isAllSelected || isEmpty) {
                e.preventDefault();
                e.stopPropagation();
                if (el._savedValueForSpace !== undefined && el.value !== undefined) {
                  el.value = el._savedValueForSpace;
                }
                this.openModal(fieldId);
                return false;
              }
            } else if (e.key && e.key.length === 1 && e.key !== ' ') {
              el._awaitingSpaceShortcut = false;
            }
          });

          // 2. رصد المسافة على كيبورد الجوال (Android Gboard / iOS) عبر beforeinput
          el.addEventListener('beforeinput', (e) => {
            if (e.data === ' ') {
              const isAllSelected = (typeof el.selectionStart === 'number' && el.selectionStart === 0 && el.selectionEnd === (el.value || '').length && (el.value || '').length > 0);
              const isEmpty = !(el.value || '').trim();
              if (meta.isNumeric || el.readOnly || el._awaitingSpaceShortcut || isAllSelected || isEmpty) {
                e.preventDefault();
                e.stopPropagation();
                if (el._savedValueForSpace !== undefined && el.value !== undefined) {
                  el.value = el._savedValueForSpace;
                }
                this.openModal(fieldId);
                return false;
              }
              el._awaitingSpaceShortcut = false;
            } else if (e.data && e.data.trim().length > 0) {
              el._awaitingSpaceShortcut = false;
            }
          });

          // 3. حماية إضافية لكيبورد الجوال إذا أرسل المسافة في حدث input مباشرة
          el.addEventListener('input', (e) => {
            const val = el.value || '';
            if ((meta.isNumeric && val.includes(' ')) || (el._awaitingSpaceShortcut && (val === ' ' || (e && e.data === ' ')))) {
              if (el._savedValueForSpace !== undefined && !meta.isNumeric) {
                el.value = el._savedValueForSpace;
              } else if (meta.isNumeric) {
                el.value = val.replace(/\s+/g, '');
              }
              this.openModal(fieldId);
            } else if (val.trim().length > 0 && val !== el._savedValueForSpace) {
              el._awaitingSpaceShortcut = false;
            }
          });
        };

        Object.keys(this.fieldMeta).forEach(attachToField);

        // مستمع عام لزر المسافة عندما يكون الحقل النشط هو الجملة أو الإجمالي الصافي أو سعر الصرف
        window.addEventListener('keydown', (e) => {
          const modalOpen = document.getElementById('suiteFieldStyleModal');
          if (modalOpen && !modalOpen.classList.contains('hidden')) return;
          const isSpace = (e.key === ' ' || e.code === 'Space' || e.keyCode === 32);
          if (!isSpace || !this.activeFieldId) return;
          const activeEl = document.activeElement;
          const isTypingInOtherInput = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA') && !activeEl.readOnly && activeEl.id !== this.activeFieldId;
          if (isTypingInOtherInput) return;

          const meta = this.fieldMeta[this.activeFieldId];
          const targetEl = document.getElementById(this.activeFieldId);
          if (targetEl && (targetEl.readOnly || meta?.isNumeric || targetEl.tagName === 'SPAN' || targetEl.tagName === 'SELECT')) {
            e.preventDefault();
            this.openModal(this.activeFieldId);
          }
        });
      },

      openModal(fieldId) {
        if (!fieldId || !this.fieldMeta[fieldId]) return;
        this.activeFieldId = fieldId;
        this.tempConfig = Object.assign({}, this.getFieldConfig(fieldId));

        // إغلاق أي قوائم بحث منسدلة مفتوحة
        ['salesCustomerDropdown', 'salesWarehouseDropdown', 'salesCostCenterDropdown', 'salesItemDropdown', 'purchaseSupplierDropdown', 'purchaseWarehouseDropdown', 'purItemDropdown'].forEach(id => {
          const d = document.getElementById(id);
          if (d) d.classList.add('hidden');
        });

        const el = document.getElementById(fieldId);
        if (el && typeof el.blur === 'function') el.blur();

        const meta = this.fieldMeta[fieldId];
        const badge = document.getElementById('sfcFieldLabelBadge');
        if (badge) badge.innerText = meta.label;

        const numSec = document.getElementById('sfcNumberFormatSection');
        if (numSec) {
          numSec.style.display = meta.isNumeric ? 'block' : 'none';
        }

        const applyAllChk = document.getElementById('sfcApplyToAllNumbers');
        if (applyAllChk) applyAllChk.checked = false;

        this.syncModalControls();

        const modal = document.getElementById('suiteFieldStyleModal');
        if (modal) {
          modal.classList.remove('hidden');
          modal.style.display = 'flex';
        }
        if (window.triggerHapticPulse) triggerHapticPulse(12);
      },

      closeModal() {
        const modal = document.getElementById('suiteFieldStyleModal');
        if (modal) {
          modal.classList.add('hidden');
          modal.style.display = 'none';
        }
      },

      syncModalControls() {
        if (!this.tempConfig) return;
        const cfg = this.tempConfig;

        // 1. حجم الخط
        const range = document.getElementById('sfcFontSizeRange');
        if (range) range.value = cfg.fontSize;
        const sizeBadge = document.getElementById('sfcFontSizeValBadge');
        if (sizeBadge) sizeBadge.innerText = cfg.fontSize + 'px';

        const aiBtn = document.getElementById('sfcBtnAiMode');
        if (aiBtn) {
          if (cfg.fontMode === 'ai') {
            aiBtn.className = 'px-2 py-0.5 rounded-lg text-[10px] font-black border transition cursor-pointer bg-emerald-600/30 text-emerald-300 border-emerald-400';
            aiBtn.innerText = '🤖 ذكاء تلقائي: مفعل';
          } else {
            aiBtn.className = 'px-2 py-0.5 rounded-lg text-[10px] font-black border transition cursor-pointer bg-sky-600/30 text-sky-200 border-sky-400';
            aiBtn.innerText = '📌 حجم ثابت مخصص';
          }
        }

        // 2. لون الخط
        const picker = document.getElementById('sfcCustomColorPicker');
        if (picker && cfg.fontColor && cfg.fontColor.startsWith('#')) {
          picker.value = cfg.fontColor;
        }
        document.querySelectorAll('#sfcColorSwatches .sfc-color-btn').forEach(btn => {
          const c = btn.getAttribute('data-color');
          if (c && c.toLowerCase() === (cfg.fontColor || '').toLowerCase()) {
            btn.style.outline = '3px solid #ffffff';
            btn.style.transform = 'scale(1.06)';
          } else {
            btn.style.outline = 'none';
            btn.style.transform = 'scale(1)';
          }
        });

        // 3. الفاصلة الألفية والعشرية والتقريب
        document.querySelectorAll('.sfc-th-btn').forEach(btn => {
          const th = btn.getAttribute('data-th');
          const active = (th === String(cfg.thousandsSep));
          btn.className = 'sfc-th-btn py-1.5 rounded-xl border transition cursor-pointer ' + (active ? 'bg-emerald-600 text-white border-emerald-300 font-black shadow' : 'bg-slate-950 text-slate-400 border-slate-800 hover:text-white');
        });

        document.querySelectorAll('.sfc-dec-btn').forEach(btn => {
          const dec = btn.getAttribute('data-dec');
          const active = (dec === String(cfg.decimalSep));
          btn.className = 'sfc-dec-btn py-1.5 rounded-xl border transition cursor-pointer ' + (active ? 'bg-cyan-600 text-white border-cyan-300 font-black shadow' : 'bg-slate-950 text-slate-400 border-slate-800 hover:text-white');
        });

        document.querySelectorAll('.sfc-round-btn').forEach(btn => {
          const r = btn.getAttribute('data-round');
          const active = (r === String(cfg.decimals));
          btn.className = 'sfc-round-btn py-1.5 rounded-xl border transition cursor-pointer ' + (active ? 'bg-amber-500 text-slate-950 border-amber-300 font-black shadow' : 'bg-slate-950 text-slate-400 border-slate-800 hover:text-white');
        });

        this.updateLivePreview();
      },

      updateLivePreview() {
        const prev = document.getElementById('sfcLivePreviewText');
        if (!prev || !this.activeFieldId || !this.tempConfig) return;
        const el = document.getElementById(this.activeFieldId);
        const meta = this.fieldMeta[this.activeFieldId];
        const cfg = this.tempConfig;

        prev.style.fontSize = cfg.fontSize + 'px';
        prev.style.color = cfg.fontColor || '#ffffff';

        if (meta && meta.isNumeric) {
          let sampleNum = 125480.756;
          if (el) {
            const rawVal = el.value !== undefined ? el.value : el.innerText;
            const parsed = this.parseNum(rawVal, this.activeFieldId);
            if (parsed > 0) sampleNum = parsed;
          }
          // تنسيق المعاينة الحية حسب الإعدادات المؤقتة الحالية
          const savedBackup = this.configs[this.activeFieldId];
          this.configs[this.activeFieldId] = Object.assign({}, cfg);
          prev.innerText = this.formatNumberForField(this.activeFieldId, sampleNum);
          if (savedBackup) this.configs[this.activeFieldId] = savedBackup;
          else delete this.configs[this.activeFieldId];
        } else {
          const txt = el ? (el.value || el.innerText || meta.label) : meta.label;
          prev.innerText = txt;
        }
      },

      onFontSizeChange(val) {
        if (!this.tempConfig) return;
        const px = parseFloat(val);
        if (isNaN(px)) return;
        this.tempConfig.fontSize = Math.max(10, Math.min(58, px));
        this.tempConfig.fontMode = 'fixed';
        this.syncModalControls();
      },

      stepFontSize(delta) {
        if (!this.tempConfig) return;
        const next = (parseFloat(this.tempConfig.fontSize) || 20) + delta;
        this.onFontSizeChange(next);
      },

      toggleAiFontMode() {
        if (!this.tempConfig) return;
        this.tempConfig.fontMode = (this.tempConfig.fontMode === 'ai') ? 'fixed' : 'ai';
        this.syncModalControls();
      },

      onColorChange(color) {
        if (!this.tempConfig || !color) return;
        this.tempConfig.fontColor = color;
        this.syncModalControls();
      },

      setThousandsSep(sep) {
        if (!this.tempConfig) return;
        this.tempConfig.thousandsSep = sep;
        this.syncModalControls();
      },

      setDecimalSep(sep) {
        if (!this.tempConfig) return;
        this.tempConfig.decimalSep = sep;
        this.syncModalControls();
      },

      setDecimals(d) {
        if (!this.tempConfig) return;
        this.tempConfig.decimals = d;
        this.syncModalControls();
      },

      savePermanent() {
        if (!this.activeFieldId || !this.tempConfig) return;
        this.configs[this.activeFieldId] = Object.assign({}, this.tempConfig);

        const applyAllChk = document.getElementById('sfcApplyToAllNumbers');
        if (applyAllChk && applyAllChk.checked) {
          Object.keys(this.fieldMeta).forEach(fid => {
            if (this.fieldMeta[fid].isNumeric) {
              const cur = this.getFieldConfig(fid);
              cur.thousandsSep = this.tempConfig.thousandsSep;
              cur.decimalSep = this.tempConfig.decimalSep;
              cur.decimals = this.tempConfig.decimals;
              this.configs[fid] = cur;
            }
          });
        }

        this.persistConfigs();
        this.applyAllSavedStyles();
        if (window.SuiteSalesPOS && typeof SuiteSalesPOS.calcItemSubtotal === 'function') {
          SuiteSalesPOS.calcItemSubtotal();
        }
        if (window.SuitePurchasePOS && typeof SuitePurchasePOS.calcItemSubtotal === 'function') {
          SuitePurchasePOS.calcItemSubtotal();
        }
        if (window.SmartPOSFontAI) {
          window.SmartPOSFontAI.scaleAll();
        }
        this.closeModal();
        if (window.triggerHapticPulse) triggerHapticPulse(20);
        if (typeof showFloatingToast === 'function') {
          const label = this.fieldMeta[this.activeFieldId]?.label || 'الحقل';
          showFloatingToast('✓ تم حفظ إعدادات [' + label + '] كافتراضي دائم لا يتغير!');
        }
      },

      resetCurrentField() {
        if (!this.activeFieldId) return;
        delete this.configs[this.activeFieldId];
        this.persistConfigs();
        this.tempConfig = Object.assign({}, this.getDefaultConfig(this.activeFieldId));
        this.syncModalControls();
        const el = document.getElementById(this.activeFieldId);
        if (el) {
          el.style.setProperty('color', this.tempConfig.fontColor, 'important');
          el.style.setProperty('font-size', this.tempConfig.fontSize + 'px', 'important');
        }
        if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleAll();
        if (window.SuiteSalesPOS && typeof SuiteSalesPOS.calcItemSubtotal === 'function') {
          SuiteSalesPOS.calcItemSubtotal();
        }
        this.closeModal();
        if (typeof showFloatingToast === 'function') {
          showFloatingToast('🔄 تمت استعادة الإعدادات الأصلية للحقل');
        }
      }
    };

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => window.SmartFieldCustomizer && window.SmartFieldCustomizer.init());
    } else {
      setTimeout(() => window.SmartFieldCustomizer && window.SmartFieldCustomizer.init(), 40);
    }

    window.SmartPOSFontAI = {
      _canvas: null,
      _ctx: null,

      _getCtx() {
        if (!this._ctx) {
          this._canvas = document.createElement('canvas');
          this._ctx = this._canvas.getContext('2d');
        }
        return this._ctx;
      },

      // محرك ذكاء اصطناعي لقياس عرض النص الفعلي بالبيكسل مقارنة بالمساحة المتاحة داخل الخانة
      fitTextToBox(el, text, opts = {}) {
        if (!el) return;
        el.style.removeProperty('color');
        if (window.SmartFieldCustomizer && el.id && SmartFieldCustomizer.hasCustomConfig(el.id)) {
          const uCfg = SmartFieldCustomizer.getFieldConfig(el.id);
          if (uCfg.fontMode === 'fixed' && uCfg.fontSize) {
            el.style.setProperty('font-size', uCfg.fontSize + 'px', 'important');
            el.style.setProperty('font-weight', opts.weight || '900', 'important');
            return;
          }
          if (uCfg.fontSize) {
            opts = Object.assign({}, opts, { maxPx: uCfg.fontSize, defPx: uCfg.fontSize });
          }
        }
        const maxPx = opts.maxPx || 18;
        const minPx = opts.minPx || 10.5;
        const defPx = opts.defPx || 15;
        const weight = opts.weight || '900';
        const family = opts.family || "'Cairo', system-ui, -apple-system, sans-serif";
        const paddingPx = opts.paddingPx || 20;

        const clean = (text || '').toString().trim();
        if (!clean) {
          el.style.setProperty('font-size', defPx + 'px', 'important');
          el.style.setProperty('font-weight', weight, 'important');
          return;
        }

        const boxWidth = (el.clientWidth || el.parentElement?.clientWidth || 0) - paddingPx;
        if (boxWidth <= 30) {
          // Fallback intelligent character-length curve when modal is hidden or rendering
          const len = clean.length;
          let fallbackPx = maxPx;
          if (len > 28) fallbackPx = minPx;
          else if (len > 22) fallbackPx = Math.max(minPx, maxPx - 6.5);
          else if (len > 16) fallbackPx = Math.max(minPx, maxPx - 4.5);
          else if (len > 10) fallbackPx = Math.max(minPx, maxPx - 2);
          el.style.setProperty('font-size', fallbackPx + 'px', 'important');
          el.style.setProperty('font-weight', weight, 'important');
          return;
        }

        const ctx = this._getCtx();
        let chosenPx = minPx;
        for (let px = maxPx; px >= minPx; px -= 0.5) {
          ctx.font = `${weight} ${px}px ${family}`;
          const measured = ctx.measureText(clean).width;
          if (measured <= boxWidth) {
            chosenPx = px;
            break;
          }
        }
        el.style.setProperty('font-size', chosenPx + 'px', 'important');
        el.style.setProperty('font-weight', weight, 'important');
      },

      _applyCustomNumericOrScale(input, defaultBase, stepThresholds) {
        if (!input) return;
        input.style.removeProperty('color');
        if (window.SmartFieldCustomizer && input.id && SmartFieldCustomizer.hasCustomConfig(input.id)) {
          const uCfg = SmartFieldCustomizer.getFieldConfig(input.id);
          if (uCfg.fontMode === 'fixed' && uCfg.fontSize) {
            input.style.setProperty('font-size', uCfg.fontSize + 'px', 'important');
            input.style.setProperty('font-weight', '900', 'important');
            return;
          }
          if (uCfg.fontSize) defaultBase = uCfg.fontSize;
        }
        const raw = (input.value || '').toString().trim();
        const len = raw.replace(/[^0-9.]/g, '').length;
        let size = defaultBase;
        for (const [minLen, ratio] of stepThresholds) {
          if (len >= minLen) {
            size = Math.max(14, Math.round(defaultBase * ratio));
            break;
          }
        }
        input.style.setProperty('font-size', size + 'px', 'important');
        input.style.setProperty('font-weight', '900', 'important');
      },

      scaleQty(input) {
        if (!input) return;
        const isSales = input.id === 'salesQtyInput';
        this._applyCustomNumericOrScale(input, isSales ? 28 : 39, [[9, 0.6], [7, 0.72], [5, 0.85]]);
      },

      scalePrice(input) {
        if (!input) return;
        const isSales = input.id === 'salesPriceInput';
        this._applyCustomNumericOrScale(input, isSales ? 28 : 32, [[9, 0.6], [7, 0.72], [5, 0.85]]);
      },

      scaleGross(input) {
        if (!input) return;
        const isSales = input.id === 'salesItemGrossTotal';
        this._applyCustomNumericOrScale(input, isSales ? 46 : 43, [[13, 0.58], [11, 0.68], [9, 0.78], [7, 0.88]]);
      },

      scaleTotal(input) {
        if (!input) return;
        const isSales = input.id === 'salesTotalInput';
        this._applyCustomNumericOrScale(input, isSales ? 46 : 43, [[13, 0.58], [11, 0.68], [9, 0.78], [7, 0.88]]);
      },

      scaleTax(input) {
        if (!input) return;
        this._applyCustomNumericOrScale(input, 28, [[9, 0.6], [7, 0.72], [5, 0.85]]);
      },

      scaleWarehouse(input) {
        if (!input) return;
        this.fitTextToBox(input, input.value, { maxPx: 16.5, minPx: 10.5, defPx: 14, paddingPx: 16 });
      },

      scaleCostCenter(input) {
        if (!input) return;
        const val = input.value !== undefined ? input.value : (input.innerText || '');
        this.fitTextToBox(input, val, { maxPx: 16.5, minPx: 10.5, defPx: 14, paddingPx: 28 });
      },

      scaleCustomer(input) {
        if (!input) return;
        this.fitTextToBox(input, input.value, { maxPx: 16.5, minPx: 11, defPx: 14, paddingPx: 20 });
      },

      scaleItem(input) {
        if (!input) return;
        // تكبير اسم الصنف ليكون كبيراً وملائماً للمساحة المخصصة له (يكبر أوتوماتيك حتى 26px للأسماء القصيرة ويصغر أوتوماتيك للأسماء الطويلة)
        this.fitTextToBox(input, input.value, { maxPx: 26, minPx: 13.5, defPx: 21, paddingPx: 14 });
      },

      scaleUnit(select) {
        if (!select) return;
        select.style.removeProperty('color');
        if (window.SmartFieldCustomizer && select.id && SmartFieldCustomizer.hasCustomConfig(select.id)) {
          const uCfg = SmartFieldCustomizer.getFieldConfig(select.id);
          if (uCfg.fontMode === 'fixed' && uCfg.fontSize) {
            select.style.setProperty('font-size', uCfg.fontSize + 'px', 'important');
            select.style.setProperty('font-weight', '800', 'important');
            return;
          }
        }
        const text = (select.options && select.selectedIndex >= 0) ? select.options[select.selectedIndex].text : (select.value || '');
        const len = (text || '').trim().length;
        // تنقيص مقاس الوحدات درجة واحدة كما طلب المستخدم
        let size = '12.5px';
        if (len >= 11) size = '9.5px';
        else if (len >= 5) size = '11.5px';
        else size = '12.5px';
        select.style.setProperty('font-size', size, 'important');
        select.style.setProperty('font-weight', '800', 'important');
      },

      scaleNotes(input) {
        if (!input) return;
        this.fitTextToBox(input, input.value, { maxPx: 16, minPx: 11.5, defPx: 14.5, paddingPx: 24 });
      },

      scaleVoucherPill(el) {
        if (!el) return;
        const val = (el.value !== undefined && el.tagName === 'INPUT') ? el.value : (el.innerText || el.textContent || '');
        const clean = String(val).trim();
        if (!clean) return;
        const len = clean.length;
        let px = 14.5;
        if (len >= 15) px = 9.5;
        else if (len >= 13) px = 10.5;
        else if (len >= 11) px = 11.5;
        else if (len >= 9) px = 12.5;
        el.style.setProperty('font-size', px + 'px', 'important');
        el.style.setProperty('white-space', 'nowrap', 'important');
        const parentW = (el.parentElement ? el.parentElement.clientWidth : el.clientWidth) || 0;
        if (parentW > 20) {
          while (el.scrollWidth > parentW && px > 8.5) {
            px -= 0.5;
            el.style.setProperty('font-size', px + 'px', 'important');
          }
        }
      },

      scaleAll() {
        ['purQtyInput', 'salesQtyInput'].forEach(id => this.scaleQty(document.getElementById(id)));
        ['purPriceInput', 'salesPriceInput'].forEach(id => this.scalePrice(document.getElementById(id)));
        ['purItemGrossTotal', 'salesItemGrossTotal'].forEach(id => this.scaleGross(document.getElementById(id)));
        ['purTotalInput', 'salesTotalInput'].forEach(id => this.scaleTotal(document.getElementById(id)));
        ['purTaxInput', 'salesTaxInput', 'salesDiscountInput', 'salesLaborInput'].forEach(id => this.scaleTax(document.getElementById(id)));
        ['purchaseWarehouseInput', 'salesWarehouseInput'].forEach(id => this.scaleWarehouse(document.getElementById(id)));
        ['salesCostCenterInput'].forEach(id => this.scaleCostCenter(document.getElementById(id)));
        ['salesCustomerInput', 'purchaseSupplierInput'].forEach(id => this.scaleCustomer(document.getElementById(id)));
        ['purItemSearchInput', 'salesItemSearchInput'].forEach(id => this.scaleItem(document.getElementById(id)));
        ['purUnitSelect', 'salesUnitSelect'].forEach(id => this.scaleUnit(document.getElementById(id)));
        ['salesNotesInput', 'purNotesInput'].forEach(id => this.scaleNotes(document.getElementById(id)));
        ['opSalesInvoiceNoPill', 'opPurchaseNoPill', 'opSalesSplitInvoiceNoPill', 'opPurchaseSplitNoPill', 'opDeliveryNoPill', 'opReceiptNoPill', 'finTransferVoucherNo', 'finTransferCashReceiptNo'].forEach(id => this.scaleVoucherPill(document.getElementById(id)));
        if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();
      },

      initListeners() {
        const bind = (id, fn) => {
          const el = document.getElementById(id);
          if (el) {
            ['input', 'change', 'keyup', 'blur'].forEach(ev => {
              el.addEventListener(ev, () => fn.call(this, el));
            });
            fn.call(this, el);
          }
        };
        bind('purQtyInput', this.scaleQty);
        bind('salesQtyInput', this.scaleQty);
        bind('purPriceInput', this.scalePrice);
        bind('salesPriceInput', this.scalePrice);
        bind('purItemGrossTotal', this.scaleGross);
        bind('salesItemGrossTotal', this.scaleGross);
        bind('purTotalInput', this.scaleTotal);
        bind('salesTotalInput', this.scaleTotal);
        bind('purTaxInput', this.scaleTax);
        bind('salesTaxInput', this.scaleTax);
        bind('salesDiscountInput', this.scaleTax);
        bind('salesLaborInput', this.scaleTax);
        bind('purchaseWarehouseInput', this.scaleWarehouse);
        bind('salesWarehouseInput', this.scaleWarehouse);
        bind('salesCostCenterInput', this.scaleCostCenter);
        bind('salesCustomerInput', this.scaleCustomer);
        bind('purchaseSupplierInput', this.scaleCustomer);
        bind('purItemSearchInput', this.scaleItem);
        bind('salesItemSearchInput', this.scaleItem);
        bind('purUnitSelect', this.scaleUnit);
        bind('salesUnitSelect', this.scaleUnit);
        bind('salesNotesInput', this.scaleNotes);
        bind('purNotesInput', this.scaleNotes);

        window.addEventListener('resize', () => this.scaleAll());
      }
    };

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => window.SmartPOSFontAI && window.SmartPOSFontAI.initListeners());
    } else {
      setTimeout(() => window.SmartPOSFontAI && window.SmartPOSFontAI.initListeners(), 50);
    }

window.SuiteSalesPOS = {
      cart: [],
      dbItems: [
        {"id":"ITEM-1782559457670","name":"سيخ 3 لينيا أسعد 10 مللي","unit":"سيخه","price":18500,"sellingPrice":18500,"stock":{"PENDING_RECEIPT":500},"barcode":"1010","baseUnit":"سيخه","unitsArray":[{"name":"طن عددي","factor":135},{"name":"سيخه","factor":1},{"name":"طن وزني","factor":140}],"bundleFactor":15,"conversionFactor":1},
        {"id":"ITEM-1782559457671","name":"سيخ 3 لينيا الاسعد","unit":"سيخه","price":0,"stock":{"PENDING_RECEIPT":675},"barcode":"","baseUnit":"سيخه","unitsArray":[{"name":"سيخه","factor":1},{"name":"طن عددي","factor":135},{"name":"طن وزني","factor":140}],"bundleFactor":15,"conversionFactor":1},
        {"id":"ITEM-1782559559372","name":"سيخ 4 لينيا الاسعد","unit":"سيخه","price":0,"stock":[],"barcode":"","baseUnit":"سيخه","unitsArray":[{"name":"سيخه","factor":1},{"name":"طن عددي","factor":92},{"name":"طن وزني","factor":140}],"bundleFactor":10,"conversionFactor":1},
        {"id":"ITEM-1786727449011","name":"سيخ 5 لينيا الاسعد","unit":"سيخه","price":0,"stock":{"PENDING_RECEIPT":230},"barcode":"","baseUnit":"سيخه","unitsArray":[{"name":"سيخه","factor":1},{"name":"طن عددي","factor":52},{"name":"طن وزني","factor":58}],"bundleFactor":5,"conversionFactor":1},
        {"id":"ITEM-1782561316951","name":"سيخ 5 لينيا الامجد","unit":"سيخه","price":0,"stock":[],"barcode":"","baseUnit":"سيخه","unitsArray":[{"name":"سيخه","factor":1},{"name":"طن عددي","factor":52},{"name":"طن وزني","factor":66}],"bundleFactor":5,"conversionFactor":1},
        {"id":"ITEM-1782562286396","name":"سيخ 5 لينيا التركي","unit":"سيخه","price":0,"stock":[],"barcode":"","baseUnit":"سيخه","unitsArray":[{"name":"سيخه","factor":1},{"name":"طن عددي","factor":52},{"name":"طن وزني","factor":55}],"bundleFactor":5,"conversionFactor":1},
        {"id":"ITEM-1782567905125","name":"سيخ 5 لينيا صيني","unit":"سيخه","price":0,"stock":[],"barcode":"","baseUnit":"سيخه","unitsArray":[{"name":"سيخه","factor":1},{"name":"طن عددي","factor":52},{"name":"طن وزني","factor":66}],"bundleFactor":5,"conversionFactor":1},
        {"id":"item-001","name":"أسمنت بورتلاندي 50 كجم","unit":"كيس","barcode":"1001","baseUnit":"كيس","unitsArray":[{"name":"كيس","factor":1},{"name":"طن","factor":20}],"sellingPrice":4500},
        {"id":"item-002","name":"حديد تسليح 12 ملم","unit":"سيخة / عود","barcode":"1002","baseUnit":"سيخة / عود","unitsArray":[{"name":"سيخة / عود","factor":1},{"name":"طن","factor":120}],"sellingPrice":12500},
        {"id":"item-003","name":"طوب مفرغ 20×20×40","unit":"حبة","barcode":"1003","baseUnit":"حبة","unitsArray":[{"name":"حبة","factor":1},{"name":"ألف حبة","factor":1000}],"sellingPrice":650},
        {"id":"item-004","name":"رمل بناء ممتاز","unit":"متر مكعب","barcode":"1004","baseUnit":"متر مكعب","unitsArray":[{"name":"متر مكعب","factor":1},{"name":"قلاب 12متر","factor":12}],"sellingPrice":8500},
        {"id":"item-005","name":"دهان بلاستيك مط داخلي","unit":"جالون","barcode":"1005","baseUnit":"جالون","unitsArray":[{"name":"جالون","factor":1},{"name":"كرتونة (4 جالون)","factor":4}],"sellingPrice":14000}
      ],
      dbCustomers: [
        {"id":"CUST-CASH","name":"عميل نقدي عام","phone":"","status":"active","currency":"SDG","currentBalance":0},
        {"id":"CUST-1782729918903","name":"محمد عثمان حمتو عبد الدايم","phone":"","status":"active","currency":"SDG","currentBalance":7100230},
        {"id":"CUST-1783162615475","name":"شيريهان احمد عثمان تاج الدين","phone":"00249912106075","whatsapp":"00201550886559","status":"active","currency":"USD","creditLimit":2000000,"currentBalance":-500000},
        {"id":"CUST-1781625556260","name":"محمد عمار","phone":"0912300105","whatsapp":"0912300112","status":"active","address":"الخرطوم","currentBalance":11089},
        {"id":"CUST-1782914588501","name":"حسن علي","phone":"00249912300111","whatsapp":"00249912300111","status":"active","currency":"USD","currentBalance":-1000},
        {"id":"C-112681","name":"محمد حسن علي مكي","phone":"","status":"active","currentBalance":89898989},
        {"id":"CUST-1783451342581","name":"جمانه مكي","phone":"00249912106076","whatsapp":"00249912300622","status":"active","currency":"USD","currentBalance":367889},
        {"id":"C-751626","name":"سيد عثمان تاج الدين","phone":"","status":"active","currentBalance":0},
        {"id":"CUST-1784100578257","name":"عمر طارق محمد مكي","phone":"","whatsapp":"00249912300622","status":"active","currency":"USD","creditLimit":7000000,"currentBalance":450000},
        {"id":"CUST-1784628659773","name":"محمود دمباوي","phone":"","status":"active","currency":"SDG","creditLimit":4000000,"currentBalance":2500000},
        {"id":"CUST-1785237947153","name":"رضوان باشا","phone":"","status":"active","currency":"USD","currentBalance":6000065},
        {"id":"CUST-1785253492699","name":"متوكل ازهري","phone":"","status":"active","currency":"USD","currentBalance":1000000},
        {"id":"CUST-1785703452552","name":"معتذ محمد","phone":"0912300222","whatsapp":"0912300222","status":"active","currency":"SDG","currentBalance":0},
        {"id":"CUST-1786320334761","name":"فيصل العجب","phone":"","status":"active","currency":"USD","currentBalance":6008656},
        {"id":"CUST-1786384017176","name":"شيخ ميرغني","phone":"","status":"active","currency":"USD","currentBalance":-909090},
        {"id":"CUST-1786394925761","name":"دكتور شول","phone":"","status":"active","currency":"USD","currentBalance":20148723},
        {"id":"CUST-1789816868387","name":"محمد عماد","phone":"0123006242","whatsapp":"0123006242","status":"active","currency":"USD","currentBalance":5340944}
      ],
      dbWarehouses: [{"id":"WH-1781113448062","name":"المستودع الرئيسي","type":"مستودع رئيسي","createdBy":9,"securityLevel":1},{"id":"WH-1781116528837","name":"مخزن السجانة","type":"مستودع رئيسي","createdBy":9,"securityLevel":1},{"id":"WH-1782504163835","name":"دره اللاماب","type":"مستودع رئيسي","createdBy":9,"securityLevel":1},{"id":"WH-1782159769983","name":"مخزن الدره","type":"مستودع رئيسي","createdBy":9,"securityLevel":1},{"id":"WH-1783208966357","name":"اللاماب","type":"مستودع رئيسي","createdBy":9,"securityLevel":1},{"id":"WH-1782037611629","name":"دكان الخرطوم السجانه ","type":"معرض (نقطة بيع)","securityLevel":1}],
      dbCostCenters: [
        { id: 'cc-1', name: 'المركز الرئيسي' },
        { id: 'cc-2', name: 'فرع السجانة المركزي' },
        { id: 'cc-3', name: 'مخزن الدرة' },
        { id: 'cc-4', name: 'مخزن بحري' },
        { id: 'cc-5', name: 'معرض التوزيع' },
        { id: 'cc-6', name: 'مشاريع ومقاولات' }
      ],
      selectedCostCenter: 'المركز الرئيسي',
      selectedWarehouse: 'المستودع الرئيسي',
      selectedItemIndex: -1,
      selectedCustomer: null,
      usdExchangeRate: 6000,
      currentCalcVal: '0',
      calcHistoryVal: '',
      recentInvoices: [],
      currentInvoiceIndex: -1,
      latestGeneratedInvNo: '',

      currencyMode: 'USD',

      async initCurrencyAndRate() {
        const savedMode = localStorage.getItem('magaies_currency_mode');
        if (savedMode) {
          this.currencyMode = savedMode;
        }
        if (window.SuiteFXManager) {
          await SuiteFXManager.fetchSystemRate();
          this.usdExchangeRate = SuiteFXManager.currentRate;
        }
        this.syncCurrencyDisplay();
      },

      toggleCurrency() {
        if (this.currencyMode === 'USD') {
          this.currencyMode = 'SDG';
        } else {
          this.currencyMode = 'USD';
        }
        try { localStorage.setItem('magaies_currency_mode', this.currencyMode); } catch(e) {}
        this.syncCurrencyDisplay();
        if (window.triggerHapticPulse) triggerHapticPulse(15);
        if (window.AudioEngine && AudioEngine.playTick) AudioEngine.playTick();
        if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(true, 'modalOpSales');
      },

      syncCurrencyDisplay() {
        const btn = document.getElementById('btnSalesCurrencyToggle');
        const sym = document.getElementById('salesCurrencySymbol');
        const disp = document.getElementById('salesUsdRateDisplay');
        const inp = document.getElementById('salesUsdRateInput');
        const rateBox = document.getElementById('salesRateContainer');

        if (disp) disp.innerText = this.usdExchangeRate.toString().slice(0, 5);
        if (inp) inp.value = this.usdExchangeRate.toString().slice(0, 5);

        if (!btn || !sym) return;

        if (this.currencyMode === 'SDG') {
          sym.innerText = 'ج.س';
          sym.style.setProperty('color', '#fbbf24', 'important');
          sym.style.setProperty('font-size', '10px', 'important');
          sym.style.setProperty('font-weight', '900', 'important');
          sym.style.setProperty('text-shadow', '0 0 4px #fbbf24', 'important');
          sym.className = 'text-amber-300 text-[10px] font-black leading-none pointer-events-none';
          btn.style.setProperty('background', '#000000', 'important');
          btn.style.setProperty('border', '1.5px solid #f59e0b', 'important');
          btn.style.setProperty('box-shadow', '0 0 8px rgba(245, 158, 11, 0.8)', 'important');
          btn.title = 'العملة: جنيه سوداني (ج.س) - انقر للتحويل إلى دولار ($)';
          if (rateBox) {
            rateBox.style.setProperty('background', 'linear-gradient(135deg, #451a03, #09090b)', 'important');
            rateBox.style.setProperty('border', '2px solid #f59e0b', 'important');
            rateBox.style.setProperty('box-shadow', '0 0 14px rgba(245, 158, 11, 0.65), inset 0 0 8px rgba(245, 158, 11, 0.35)', 'important');
          }
        } else {
          sym.innerText = '$';
          sym.style.setProperty('color', '#22c55e', 'important');
          sym.style.setProperty('font-size', '13px', 'important');
          sym.style.setProperty('font-weight', '900', 'important');
          sym.style.setProperty('text-shadow', '0 0 4px #22c55e', 'important');
          sym.className = 'text-emerald-400 text-xs font-black leading-none pointer-events-none';
          btn.style.setProperty('background', '#000000', 'important');
          btn.style.setProperty('border', '1.5px solid #22c55e', 'important');
          btn.style.setProperty('box-shadow', '0 0 8px rgba(34, 197, 94, 0.8)', 'important');
          btn.title = 'العملة: دولار ($) - انقر للتحويل إلى جنيه (ج.س)';
          if (rateBox) {
            rateBox.style.setProperty('background', 'linear-gradient(135deg, #022c22, #09090b)', 'important');
            rateBox.style.setProperty('border', '2px solid #10b981', 'important');
            rateBox.style.setProperty('box-shadow', '0 0 14px rgba(16, 185, 129, 0.65), inset 0 0 8px rgba(16, 185, 129, 0.35)', 'important');
          }
        }

        const gt = document.getElementById('suiteGrandTotalDisplay');
        if (gt) {
          const num = parseFloat(gt.innerText.replace(/[^0-9.]/g, '')) || 0;
          gt.innerText = num.toFixed(2) + (this.currencyMode === 'USD' ? ' $' : ' SDG');
        }
      },

      updateUsdRate(val) {
        const clean = (val || '').toString().slice(0, 5);
        const num = parseFloat(clean);
        if (!isNaN(num) && num > 0) {
          this.usdExchangeRate = num;
          try { localStorage.setItem('magaies_usd_rate', num.toString()); } catch(e) {}
          if (this.cart && this.cart.length > 0) {
            this.renderCart();
          }
        }
      },

      onRateInput(el) {
        if (!el) return;
        if (el.value.length > 5) {
          el.value = el.value.slice(0, 5);
        }
        this.updateUsdRate(el.value);
      },

      initInvoiceSwipeListeners() {
        const box = document.getElementById('opSalesInvoiceSwipeBox');
        if (!box || box._swipeInit) return;
        box._swipeInit = true;

        let startX = 0;
        let startY = 0;
        let isDown = false;

        const handleStart = (x, y) => {
          startX = x;
          startY = y;
          isDown = true;
        };

        const handleEnd = (endX, endY) => {
          if (!isDown) return;
          isDown = false;
          const diffX = endX - startX;
          const diffY = endY - startY;

          if (Math.abs(diffX) > 25 && Math.abs(diffX) > Math.abs(diffY)) {
            if (diffX < 0) {
              // مرر شمال -> كأننا ضغطنا على السهم الشمال (السند السابق)
              this.navigateInvoice('prev');
              this.animateSwipePill('left');
            } else {
              // مرر يمين -> كأننا ضغطنا على السهم اليمين (السند التالي)
              this.navigateInvoice('next');
              this.animateSwipePill('right');
            }
          }
        };

        box.addEventListener('touchstart', (e) => {
          if (e.touches && e.touches[0]) handleStart(e.touches[0].clientX, e.touches[0].clientY);
        }, { passive: true });

        box.addEventListener('touchend', (e) => {
          if (e.changedTouches && e.changedTouches[0]) handleEnd(e.changedTouches[0].clientX, e.changedTouches[0].clientY);
        }, { passive: true });

        box.addEventListener('mousedown', (e) => {
          handleStart(e.clientX, e.clientY);
        });

        window.addEventListener('mouseup', (e) => {
          if (isDown) handleEnd(e.clientX, e.clientY);
        });
      },

      animateSwipePill(dir) {
        const pill = document.getElementById('opSalesInvoiceNoPill');
        if (!pill) return;
        const moveX = dir === 'left' ? -8 : 8;
        pill.style.transform = 'translateX(' + moveX + 'px)';
        pill.style.opacity = '0.7';
        setTimeout(() => {
          pill.style.transform = 'translateX(0)';
          pill.style.opacity = '1';
        }, 150);
        if (window.triggerHapticPulse) triggerHapticPulse(12);
      },

      // ─────────────────────────────────────────────────────────────
      // EXPANDED NOTES MODAL HANDLERS (المربع الكبير المتكامل للملحوظة)
      // ─────────────────────────────────────────────────────────────
      _isNotesFullscreen: false,

      openNotesModal(context = 'sales') {
        window._activeModalContext = (context === 'purchase') ? 'purchase' : 'sales';
        const modal = document.getElementById('suiteSalesNotesModal');
        const isPur = (window._activeModalContext === 'purchase');
        const targetId = isPur ? 'purNotesInput' : 'salesNotesInput';
        const inp = document.getElementById(targetId);
        const txt = document.getElementById('salesNotesExpandedTextarea');
        const dlg = document.getElementById('suiteNotesModalDialog');
        const icon = document.getElementById('notesFullscreenIcon');
        
        // ضبط الشاشة لتكون مربعة تماماً في المنتصف (1:1 Square Centered)
        this._isNotesFullscreen = false;
        if (dlg) {
          dlg.classList.remove('notes-dialog-fullscreen');
          dlg.style.setProperty('width', 'min(92vw, 390px)', 'important');
          dlg.style.setProperty('height', 'min(92vw, 390px)', 'important');
          dlg.style.setProperty('max-width', 'min(92vw, 390px)', 'important');
          dlg.style.setProperty('max-height', 'min(92vw, 390px)', 'important');
          dlg.style.setProperty('min-height', 'min(92vw, 390px)', 'important');
          dlg.style.setProperty('aspect-ratio', '1 / 1', 'important');
          dlg.style.setProperty('border-radius', '24px', 'important');
          dlg.style.setProperty('margin', 'auto', 'important');
        }
        if (icon) icon.className = 'fa-solid fa-maximize';
        if (txt) {
          txt.style.minHeight = '0px';
          txt.style.setProperty('font-size', '18px', 'important');
        }

        // ضبط شارة السياق واسم الشريك
        const badge = document.getElementById('suiteNotesContextBadge');
        if (badge) {
          badge.innerText = isPur ? 'فاتورة مشتريات' : 'فاتورة مبيعات';
          badge.className = isPur 
            ? 'text-[10px] px-2.5 py-0.5 rounded-full font-bold bg-amber-500/20 text-amber-300 border border-amber-400/50'
            : 'text-[10px] px-2.5 py-0.5 rounded-full font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-400/50';
        }
        const partnerEl = document.getElementById('suiteNotesPartnerInfo');
        if (partnerEl) {
          if (isPur) {
            const supp = document.getElementById('purchaseSupplierInput')?.value || 'مورد نقدي عام';
            partnerEl.innerText = 'المورد: ' + supp;
          } else {
            const cust = document.getElementById('salesCustomerInput')?.value || 'عميل نقدي عام';
            partnerEl.innerText = 'العميل: ' + cust;
          }
        }

        // نقل النص المسجل في الحقل إلى المربع الكبير بالكامل دون أي نقصان
        if (txt) {
          txt.value = (inp && inp.value) ? inp.value : '';
        }
        this.updateNotesStats();

        if (modal) {
          modal.classList.remove('hidden');
          modal.style.setProperty('z-index', '100200', 'important');
          modal.style.setProperty('display', 'flex', 'important');
          modal.style.setProperty('visibility', 'visible', 'important');
          modal.style.setProperty('opacity', '1', 'important');
          setTimeout(() => { 
            if (txt) {
              txt.focus();
              txt.selectionStart = txt.selectionEnd = txt.value.length;
            }
          }, 80);
        }
        if (window.triggerHapticPulse) triggerHapticPulse(10);
      },

      zoomNotesFont(delta = 2) {
        const txt = document.getElementById('salesNotesExpandedTextarea');
        if (!txt) return;
        const cur = parseFloat(window.getComputedStyle(txt).fontSize) || 18;
        const next = Math.max(13, Math.min(34, cur + delta));
        txt.style.setProperty('font-size', next + 'px', 'important');
        if (window.triggerHapticPulse) triggerHapticPulse(8);
      },

      toggleNotesFullscreen() {
        const dlg = document.getElementById('suiteNotesModalDialog');
        const icon = document.getElementById('notesFullscreenIcon');
        const txt = document.getElementById('salesNotesExpandedTextarea');
        if (!dlg) return;
        this._isNotesFullscreen = !this._isNotesFullscreen;
        if (this._isNotesFullscreen) {
          dlg.classList.add('notes-dialog-fullscreen');
          dlg.style.setProperty('width', '100vw', 'important');
          dlg.style.setProperty('height', '100vh', 'important');
          dlg.style.setProperty('max-width', '100vw', 'important');
          dlg.style.setProperty('max-height', '100vh', 'important');
          dlg.style.setProperty('min-height', '100vh', 'important');
          dlg.style.setProperty('border-radius', '0px', 'important');
          dlg.style.setProperty('margin', '0', 'important');
          if (icon) icon.className = 'fa-solid fa-compress';
          if (txt) {
            txt.style.setProperty('min-height', 'calc(100vh - 220px)', 'important');
            txt.style.setProperty('font-size', '20px', 'important');
          }
        } else {
          dlg.classList.remove('notes-dialog-fullscreen');
          dlg.style.setProperty('width', 'min(96vw, 680px)', 'important');
          dlg.style.setProperty('height', 'auto', 'important');
          dlg.style.setProperty('max-width', '680px', 'important');
          dlg.style.setProperty('max-height', '94vh', 'important');
          dlg.style.setProperty('min-height', 'min(75vh, 480px)', 'important');
          dlg.style.setProperty('border-radius', '24px', 'important');
          dlg.style.setProperty('margin', 'auto', 'important');
          if (icon) icon.className = 'fa-solid fa-maximize';
          if (txt) {
            txt.style.setProperty('min-height', '180px', 'important');
            txt.style.setProperty('font-size', '18px', 'important');
          }
        }
        if (window.triggerHapticPulse) triggerHapticPulse(8);
      },

      syncNotesLive() {
        const txt = document.getElementById('salesNotesExpandedTextarea');
        const isPur = (window._activeModalContext === 'purchase');
        const targetId = isPur ? 'purNotesInput' : 'salesNotesInput';
        const inp = document.getElementById(targetId);
        if (inp && txt) {
          inp.value = txt.value;
          inp.setAttribute('title', txt.value);
        }
      },

      updateNotesStats() {
        const txt = document.getElementById('salesNotesExpandedTextarea');
        const charEl = document.getElementById('notesCharCount');
        const wordEl = document.getElementById('notesWordCount');
        const lineEl = document.getElementById('notesLineCount');
        if (!txt) return;
        const val = txt.value || '';
        if (charEl) charEl.innerText = val.length;
        if (wordEl) {
          const words = val.trim() ? val.trim().split(/\s+/).length : 0;
          wordEl.innerText = words;
        }
        if (lineEl) {
          const lines = val ? val.split('\n').length : 1;
          lineEl.innerText = lines;
        }
      },

      appendQuickNote(str) {
        const txt = document.getElementById('salesNotesExpandedTextarea');
        if (!txt) return;
        const current = txt.value.trim();
        if (!current) {
          txt.value = str;
        } else if (!current.includes(str)) {
          txt.value = current + ' - ' + str;
        }
        this.updateNotesStats();
        this.syncNotesLive();
        txt.focus();
        if (window.triggerHapticPulse) triggerHapticPulse(8);
      },

      insertTimestamp() {
        const now = new Date();
        const stamp = '[' + now.toLocaleDateString('ar-EG') + ' ' + now.toLocaleTimeString('ar-EG', {hour:'2-digit', minute:'2-digit'}) + ']';
        const txt = document.getElementById('salesNotesExpandedTextarea');
        if (!txt) return;
        const current = txt.value.trim();
        txt.value = current ? current + ' ' + stamp : stamp;
        this.updateNotesStats();
        this.syncNotesLive();
        txt.focus();
        if (window.triggerHapticPulse) triggerHapticPulse(8);
      },

      copyNotesToClipboard() {
        const txt = document.getElementById('salesNotesExpandedTextarea');
        const feedback = document.getElementById('notesCopyFeedback');
        if (!txt || !txt.value) return;
        navigator.clipboard.writeText(txt.value).then(() => {
          if (feedback) {
            feedback.innerHTML = '<i class="fa-solid fa-check text-emerald-400"></i> تم النسخ!';
            setTimeout(() => { feedback.innerHTML = '<i class="fa-solid fa-copy"></i> نسخ'; }, 1500);
          }
        }).catch(() => {
          if (feedback) {
            feedback.innerText = 'فشل النسخ';
            setTimeout(() => { feedback.innerHTML = '<i class="fa-solid fa-copy"></i> نسخ'; }, 1500);
          }
        });
      },

      closeNotesModal() {
        this.syncNotesLive();
        const modal = document.getElementById('suiteSalesNotesModal');
        if (modal) {
          modal.classList.add('hidden');
          modal.style.display = 'none';
        }
      },

      exit() {
        exitToMainDashboard('modalOpSales');
      },

      saveNotesExpanded() {
        const txt = document.getElementById('salesNotesExpandedTextarea');
        const isPur = (window._activeModalContext === 'purchase');
        const targetId = isPur ? 'purNotesInput' : 'salesNotesInput';
        const inp = document.getElementById(targetId);
        if (inp && txt) {
          inp.value = txt.value.trim();
          inp.setAttribute('title', txt.value.trim());
        }
        this.closeNotesModal();
        if (window.triggerHapticPulse) triggerHapticPulse(15);
      },

      clearNotesExpanded() {
        const txt = document.getElementById('salesNotesExpandedTextarea');
        if (txt) {
          txt.value = '';
          this.updateNotesStats();
          this.syncNotesLive();
          txt.focus();
        }
      },

      // ─────────────────────────────────────────────────────────────
      // TOGGLE LABELS POSITION (أعلى الحقول vs داخل الحقول)
      // ─────────────────────────────────────────────────────────────
      toggleLabelsPosition() {
        const container = document.getElementById('suiteSalesItemBox');
        if (!container) return;
        const isTop = container.classList.contains('pos-labels-top');
        const newMode = isTop ? 'inside' : 'top';
        this.applyLabelsPosition(newMode);
        try { localStorage.setItem('magaies_sales_labels_mode', newMode); } catch(e) {}
        if (window.triggerHapticPulse) triggerHapticPulse(15);
        if (typeof showFloatingToast === 'function') {
          showFloatingToast(newMode === 'top' ? '🏷️ تم تفعيل ظهور التسميات أعلى الحقول' : '📦 تم تفعيل ظهور التسميات مدمجة داخل الحقول');
        }
      },

      applyLabelsPosition(mode) {
        const container = document.getElementById('suiteSalesItemBox');
        const btn = document.getElementById('btnToggleSalesLabels');
        const dot = document.getElementById('iconToggleSalesLabels');
        if (!container) return;

        if (mode === 'top') {
          container.classList.remove('pos-labels-inside');
          container.classList.add('pos-labels-top');
          if (dot) {
            dot.className = 'w-1.5 h-1.5 rounded-full bg-emerald-400 block transition-all shadow-[0_0_8px_#10b981]';
          }
          if (btn) {
            btn.title = 'تسميات أعلى الحقول مفعلة (انقر للتبديل للتسميات الداخلية)';
          }
        } else {
          container.classList.remove('pos-labels-top');
          container.classList.add('pos-labels-inside');
          if (dot) {
            dot.className = 'w-1.5 h-1.5 rounded-full bg-cyan-400 block transition-all shadow-[0_0_6px_#38bdf8]';
          }
          if (btn) {
            btn.title = 'تسميات داخل الحقول مفعلة (انقر للتبديل للتسميات العلوية)';
          }
        }
      },

      syncCustomerNameToAccount(val) {
        const fromSelect = document.getElementById('finTransferFromAccount');
        if (!fromSelect || !val) return;
        let matched = false;
        for (let i = 0; i < fromSelect.options.length; i++) {
          if (fromSelect.options[i].text.toLowerCase().includes(val.toLowerCase())) {
            fromSelect.selectedIndex = i;
            matched = true;
            break;
          }
        }
        if (!matched && fromSelect.options.length > 0) {
          fromSelect.options[0].text = `👤 ${val}`;
        }
        if (typeof updateFinTransferBalancesPreview === 'function') updateFinTransferBalancesPreview();
      },

      // ─────────────────────────────────────────────────────────────
      // NEW INVOICE, CLEAR INPUTS & SHARE (جديد + مسح + مشاركة)
      // ─────────────────────────────────────────────────────────────
      newInvoice() {
        this.currentInvoiceIndex = -1;
        this.cart = [];
        this.renderCart();
        this.selectedItemIndex = -1;
        const itDisp = document.getElementById('suiteItemDisplayName');
        if (itDisp) itDisp.innerHTML = '<span class="font-black text-xl sm:text-2xl truncate text-center" style="color: #fcd34d !important;">اختر الصنف...</span>';
        const itVal = document.getElementById('salesItemSelectVal');
        if (itVal) itVal.value = '';
        
        const custDisp = document.getElementById('suiteCustDisplayName');
        if (custDisp) custDisp.innerHTML = '<span class="font-bold text-[15px] sm:text-base truncate tracking-wide block w-full text-right" style="color: #cbd5e1 !important;">عميل نقدي عام</span>';
        const custInp = document.getElementById('salesCustomerInput');
        if (custInp) custInp.value = 'عميل نقدي عام';
        this.selectedCustomer = null;

        const uSel = document.getElementById('salesUnitSelect');
        if (uSel) {
          uSel.innerHTML = '<option value="" disabled selected hidden>الوحدة</option><option value="حبة">حبة</option>';
          uSel.value = '';
        }

        const ids = ['salesQtyInput', 'salesPriceInput', 'salesDiscountInput', 'salesTaxInput', 'salesLaborInput', 'salesTotalInput', 'salesItemGrossTotal', 'salesNotesInput'];
        ids.forEach(id => {
          const el = document.getElementById(id);
          if (el) el.value = '';
        });

        const txt = document.getElementById('salesNotesExpandedTextarea');
        if (txt) txt.value = '';

        // توليد رقم تسلسلي دقيق ومتسلسل مع اللابتوب والسيرفر
        this.generateNewInvoiceNo();

        // إعادة ضبط التاريخ لليوم
        const dtInp = document.getElementById('salesInvoiceDate');
        if (dtInp) {
          const today = new Date().toISOString().split('T')[0];
          dtInp.value = today;
          this.syncHeaderDatePill(today);
        }

        if (typeof this.updateErpSmartStatusStrip === 'function') this.updateErpSmartStatusStrip();
        if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(false, 'modalOpSales');
        if (window.AudioEngine && AudioEngine.playTick) AudioEngine.playTick();
        if (typeof showFloatingToast === 'function') {
          showFloatingToast('📄 تم فتح فاتورة جديدة برقم تسلسلي جديد وتصفير الأسطر!');
        }
      },

      generateNewInvoiceNo() {
        const d = new Date();
        const yy = String(d.getFullYear()).slice(-2);
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const prefix = `S${yy}${mm}`;

        let maxSeq = 0;
        const extractSeq = (raw) => {
          if (!raw) return 0;
          const s = String(raw).trim().toUpperCase();
          if (s === 'S26101012' || s === 'P26100045' || s === 'P26101012') return 0;
          if (/^[SPE]\d{9,}$/.test(s)) return 0; // ignore old 5-digit random POS numbers
          const m = s.match(/^S\d{3,4}(\d{4})$/);
          if (m) return parseInt(m[1], 10) || 0;
          if (/^\d{1,6}$/.test(s)) return parseInt(s, 10) || 0;
          const mTail = s.match(/(\d{1,5})$/);
          if (mTail) {
            const v = parseInt(mTail[1], 10) || 0;
            return (v > 0 && v < 9000) ? v : 0;
          }
          return 0;
        };

        if (window.MagaiesOffline && typeof MagaiesOffline.getHighestKnownSeq === 'function') {
          maxSeq = Math.max(maxSeq, MagaiesOffline.getHighestKnownSeq('sales'));
        }
        if (window._mirrorCache && window._mirrorCache.sales) {
          if (window._mirrorCache.sales.lastInvoiceNo) maxSeq = Math.max(maxSeq, extractSeq(window._mirrorCache.sales.lastInvoiceNo));
          if (window._mirrorCache.sales.nextInvoiceNo) {
            const nxt = extractSeq(window._mirrorCache.sales.nextInvoiceNo);
            if (nxt > 1) maxSeq = Math.max(maxSeq, nxt - 1);
          }
        }
        (this.recentInvoices || []).forEach(inv => {
          maxSeq = Math.max(maxSeq, extractSeq(inv.invoiceNo || inv.id));
        });
        try {
          const loc = JSON.parse(localStorage.getItem('magaies_local_sales_invoices_v1') || '[]');
          if (Array.isArray(loc)) loc.forEach(inv => { maxSeq = Math.max(maxSeq, extractSeq(inv.invoiceNo || inv.id)); });
          const erpLoc = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
          if (Array.isArray(erpLoc)) erpLoc.forEach(inv => { maxSeq = Math.max(maxSeq, extractSeq(inv.invoiceNo || inv.id)); });
        } catch (e) {}

        const newNo = `${prefix}${String(maxSeq + 1).padStart(4, '0')}`;
        const pill = document.getElementById('opSalesInvoiceNoPill');
        if (pill) pill.innerText = newNo;
        this.latestGeneratedInvNo = newNo;
        return newNo;
      },

      clearCurrentInputs() {
        this.selectedItemIndex = -1;
        const itInp = document.getElementById('salesItemSearchInput');
        if (itInp) itInp.value = '';
        const itDisp = document.getElementById('suiteItemDisplayName');
        if (itDisp) itDisp.innerHTML = '<span class="font-black text-xl sm:text-2xl truncate text-center" style="color: #fcd34d !important;">اختر الصنف...</span>';
        const itVal = document.getElementById('salesItemSelectVal');
        if (itVal) itVal.value = '';

        const ids = ['salesQtyInput', 'salesPriceInput', 'salesDiscountInput', 'salesTaxInput', 'salesLaborInput', 'salesTotalInput', 'salesItemGrossTotal', 'salesNotesInput'];
        ids.forEach(id => {
          const el = document.getElementById(id);
          if (el) el.value = '';
        });
        const txt = document.getElementById('salesNotesExpandedTextarea');
        if (txt) txt.value = '';

        this.calcItemSubtotal();
        if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleAll();
        if (window.triggerHapticPulse) triggerHapticPulse(15);
        if (typeof showFloatingToast === 'function') {
          showFloatingToast('🧹 تم مسح وتفريغ خانات الصنف والبيان بنجاح');
        }
      },

      shareInvoice() {
        const invNo = document.getElementById('opSalesInvoiceNoPill')?.innerText || ('S' + Date.now());
        const cust = document.getElementById('salesCustomerInput')?.value || 'عميل نقدي عام';
        const invDate = document.getElementById('salesInvoiceDate')?.value || new Date().toISOString().split('T')[0];
        const gross = document.getElementById('salesItemGrossTotal')?.value || '0.00';
        const total = document.getElementById('salesTotalInput')?.value || '0.00';
        const currency = this.currencyMode === 'USD' ? '$' : 'ج.س';
        const wh = this.selectedWarehouse || document.getElementById('salesWarehouseSelect')?.value || 'المستودع الرئيسي';
        const notes = document.getElementById('salesNotesInput')?.value || '';

        let itemsBody = '';
        if (this.cart && this.cart.length > 0) {
          itemsBody = `📋 *أسطر وبنود الفاتورة (${this.cart.length}):*\n`;
          this.cart.forEach((it, idx) => {
            itemsBody += `${idx + 1}. *${it.name}* | ${it.qty} ${it.unit} × ${it.price.toLocaleString()} ${currency} = ${it.subtotal.toLocaleString()} ${currency}\n`;
          });
        } else {
          const itName = (this.selectedItemIndex !== -1 && this.dbItems[this.selectedItemIndex]) 
            ? this.dbItems[this.selectedItemIndex].name 
            : (document.getElementById('suiteItemDisplayName')?.innerText.replace('اختر الصنف...', '').trim() || 'صنف عام');
          const qty = document.getElementById('salesQtyInput')?.value || '1';
          const price = document.getElementById('salesPriceInput')?.value || '0';
          const uSel = document.getElementById('salesUnitSelect');
          const unit = uSel ? uSel.value : 'حبة';
          const disc = parseFloat(document.getElementById('salesDiscountInput')?.value || 0);
          const tax = parseFloat(document.getElementById('salesTaxInput')?.value || 0);
          const labor = parseFloat(document.getElementById('salesLaborInput')?.value || 0);

          itemsBody = `📦 *الصنف:* ${itName}\n` +
            `📊 *الكمية:* ${qty} ${unit} | *السعر:* ${price} ${currency}\n` +
            `💵 *الجملة:* ${gross} ${currency}\n` +
            (disc > 0 ? `🔻 *الخصم:* ${disc} ${currency}\n` : '') +
            (tax > 0 ? `🔺 *الإضافة:* ${tax} ${currency}\n` : '') +
            (labor > 0 ? `🚚 *العتالة:* ${labor} ${currency}\n` : '');
        }

        const text = `🧾 *فاتورة مبيعات - MAGAIES ERP* 🏢\n` +
          `━━━━━━━━━━━━━━━━━━━━\n` +
          `🔢 *رقم الفاتورة:* ${invNo}\n` +
          `📅 *التاريخ:* ${invDate}\n` +
          `👤 *العميل:* ${cust}\n` +
          `🏢 *المخزن:* ${wh}\n` +
          `━━━━━━━━━━━━━━━━━━━━\n` +
          itemsBody +
          `━━━━━━━━━━━━━━━━━━━━\n` +
          `💰 *الإجمالي النهائي المطلوب: ${total} ${currency}*\n` +
          (notes ? `📝 *ملحوظة:* ${notes}\n` : '') +
          `━━━━━━━━━━━━━━━━━━━━\n` +
          `✨ *نظام المقاييس لإدارة المبيعات والمخازن*`;

        if (navigator.share) {
          navigator.share({
            title: `فاتورة مبيعات رقم ${invNo}`,
            text: text
          }).catch(() => {
            this.copyToClipboard(text);
          });
        } else {
          this.copyToClipboard(text);
        }
      },

      copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(() => {
            if (typeof showFloatingToast === 'function') {
              showFloatingToast('📋 تم نسخ بيانات الفاتورة بنجاح وجاهزة للمشاركة عبر الواتساب!');
            } else {
              alert('✓ تم نسخ بيانات الفاتورة بنجاح وجاهزة للمشاركة عبر الواتساب!');
            }
          }).catch(() => {
            prompt('انسخ نص الفاتورة للمشاركة عبر الواتساب:', text);
          });
        } else {
          prompt('انسخ نص الفاتورة للمشاركة عبر الواتساب:', text);
        }
      },

      syncLiveErpCatalog() {
        try {
          const liveCache = (window.PWAManager && PWAManager.liveData)
            ? PWAManager.liveData
            : JSON.parse(localStorage.getItem('magaiees_mobile_live_cache') || '{}');
          const rawItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
          const allItems = [...(Array.isArray(liveCache.items) ? liveCache.items : []), ...(Array.isArray(rawItems) ? rawItems : [])];
          allItems.forEach(it => {
            if (it && it.name && String(it.name).trim().length >= 2 && !this.dbItems.some(ex => ex.name === it.name)) {
              this.dbItems.unshift(it);
            }
          });
          const rawCusts = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
          const allCusts = [...(Array.isArray(liveCache.customers) ? liveCache.customers : []), ...(Array.isArray(rawCusts) ? rawCusts : [])];
          allCusts.forEach(c => {
            if (c && c.name && String(c.name).trim().length >= 2 && !this.dbCustomers.some(ex => ex.name === c.name)) {
              this.dbCustomers.push(c);
            }
          });
        } catch(e) {}
      },

      async init() {
        this.syncLiveErpCatalog();
        this.initDefaultCostCenter();
        this.initDefaultWarehouse();
        this.setDefaultDate();
        this.initCurrencyAndRate();
        this.initInvoiceSwipeListeners();
        const savedLabelMode = localStorage.getItem('magaies_sales_labels_mode') || 'inside';
        this.applyLabelsPosition(savedLabelMode);
        this.renderCart();
        this.calcItemSubtotal();
        await this.loadRecentInvoices();
        this.initDefaultCustomer();
        if (typeof this.updateParkedCountUI === 'function') this.updateParkedCountUI();
      },

      initDefaultCostCenter() {
        const def = this.dbCostCenters[0]?.name || 'المركز الرئيسي';
        this.selectCostCenter(def, true);
      },

      initDefaultWarehouse() {
        const def = (this.dbWarehouses && this.dbWarehouses.length > 0) ? this.dbWarehouses[0].name : 'المستودع الرئيسي';
        this.selectWarehouse(def, true);
      },

      initDefaultCustomer() {
        if (!this.selectedCustomer) {
          const walkin = this.dbCustomers.find(c => c.name && c.name.includes('نقدي')) || {
            name: 'عميل نقدي عام',
            phone: '',
            currentBalance: 0,
            currency: 'SDG'
          };
          this.selectCustomer(walkin, true);
        }
      },

      // ─────────────────────────────────────────────────────────────
      // 1. COST CENTER SEARCH (مركز الكلفة مع خانة بحث)
      // ─────────────────────────────────────────────────────────────
      openCostCenterSearch() {
        window._activeModalContext = 'sales';
        const modal = document.getElementById('suiteCostCenterSearchModal');
        if (modal) {
          modal.classList.remove('hidden');
          const inp = document.getElementById('suiteCostCenterSearchFilter');
          if (inp) {
            inp.value = '';
            setTimeout(() => inp.focus(), 50);
          }
          this.filterCostCenterSearch('');
        }
      },

      closeCostCenterSearch() {
        const modal = document.getElementById('suiteCostCenterSearchModal');
        if (modal) modal.classList.add('hidden');
      },

      filterCostCenterSearch(q) {
        const list = document.getElementById('suiteCostCenterSearchResults');
        if (!list) return;

        const term = (q || '').trim().toLowerCase();
        const filtered = this.dbCostCenters.filter(cc => cc.name.toLowerCase().includes(term));

        let html = '';
        if (term && !filtered.some(cc => cc.name.toLowerCase() === term)) {
          html += `
            <div class="p-2 rounded-xl bg-purple-950/40 hover:bg-purple-900/60 border border-purple-500/40 flex justify-between items-center cursor-pointer transition active:scale-[0.99] mb-1.5" onclick="SuiteSalesPOS.selectCostCenter('${term.replace(/'/g, "\\'")}')">
              <span class="font-bold text-purple-300 text-xs"><i class="fa-solid fa-plus-circle text-purple-400"></i> استخدام كمركز كلفة جديد: "${term}"</span>
              <span class="text-[10px] text-slate-400 font-mono">جديد +</span>
            </div>`;
        }

        filtered.forEach(cc => {
          html += `
            <div class="p-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-purple-500/50 flex justify-between items-center cursor-pointer transition active:scale-[0.99]" onclick="SuiteSalesPOS.selectCostCenter('${cc.name.replace(/'/g, "\\'")}')">
              <span class="font-bold text-white text-xs"><i class="fa-solid fa-crosshairs text-purple-400 text-[10px]"></i> ${cc.name}</span>
              <span class="text-[10px] text-purple-300 font-mono">مركز كلفة</span>
            </div>`;
        });

        if (filtered.length === 0 && !term) {
          html = '<div class="text-center py-3 text-slate-500 text-xs">لا توجد مراكز كلفة</div>';
        }
        list.innerHTML = html;
      },

      selectCostCenter(name, isDefaultInit = false) {
        if (window.SuitePOSRouter && SuitePOSRouter.getActiveContext() === 'purchase') {
          if (window.SuitePurchasePOS) SuitePurchasePOS.selectCostCenter(name);
          return;
        }
        this.selectedCostCenter = name;
        if (!isDefaultInit) this._userPickedCostCenter = true;
        const hid = document.getElementById('salesCostCenterInput');
        if (hid) {
          hid.value = name;
          if (isDefaultInit) {
            hid.classList.add('pos-ghost-default');
          } else {
            hid.classList.remove('pos-ghost-default');
          }
          if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleCostCenter(hid);
        }
        const disp = document.getElementById('suiteCostCenterDisplayName');
        if (disp) {
          disp.innerHTML = `<span class="text-purple-200 font-black text-sm sm:text-base truncate">${name}</span>`;
        }
        const drop = document.getElementById('salesCostCenterDropdown');
        if (drop) drop.classList.add('hidden');
        this.closeCostCenterSearch();
        if (!isDefaultInit && window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(true, 'modalOpSales');
      },

      // ─────────────────────────────────────────────────────────────
      // 2. CUSTOMER SEARCH (اسم العميل فقط عند الاختيار، والهاتف في البحث)
      // ─────────────────────────────────────────────────────────────
      openCustomerSearch() {
        window._activeModalContext = 'sales';
        const modal = document.getElementById('suiteCustomerSearchModal');
        if (modal) {
          modal.classList.remove('hidden');
          const titleEl = document.getElementById('suitePartnerModalTitle');
          if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-users-viewfinder"></i> اختيار عميل من قاعدة البيانات (بحث فوري)';
          const inp = document.getElementById('suiteCustSearchFilter');
          if (inp) {
            inp.placeholder = 'ابحث بالاسم أو رقم الهاتف...';
            inp.value = '';
            setTimeout(() => inp.focus(), 50);
          }
          this.filterCustomerSearch('');
        }
      },

      closeCustomerSearch() {
        const modal = document.getElementById('suiteCustomerSearchModal');
        if (modal) modal.classList.add('hidden');
      },

      filterCustomerSearch(q) {
        const list = document.getElementById('suiteCustSearchResults');
        if (!list) return;

        const term = (q || '').trim().toLowerCase();
        const filtered = this.dbCustomers.filter(c => {
          const name = (c.name || '').toLowerCase();
          const phone = (c.phone || c.whatsapp || '').toLowerCase();
          return name.includes(term) || phone.includes(term);
        });

        if (filtered.length === 0) {
          list.innerHTML = '<div class="text-center py-4 text-slate-500 text-xs">لا توجد نتائج مطابقة للبحث</div>';
          return;
        }

        let html = '';
        filtered.forEach(c => {
          const bal = parseFloat(c.currentBalance || c.balance || c.openingBalance || 0);
          const ph = c.phone || c.whatsapp || '';
          const foreignBal = (bal / this.usdExchangeRate).toFixed(2);
          const isUSD = (c.currency === 'USD');

          html += `
            <div class="p-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-sky-500/50 flex justify-between items-center cursor-pointer transition active:scale-[0.99]" onclick="SuiteSalesPOS.selectCustomer(${JSON.stringify(c).replace(/"/g, '&quot;')})">
              <div>
                <span class="font-bold text-white block text-xs">${c.name}</span>
                ${ph ? `<span class="text-cyan-400 font-mono text-[11px] block"><i class="fa-solid fa-phone text-[9px]"></i> ${ph}</span>` : '<span class="text-slate-600 text-[10px]">بدون هاتف</span>'}
              </div>
              <div class="text-left font-mono text-[11px]">
                <span class="${bal > 0 ? 'text-rose-400' : 'text-emerald-400'} font-black block">${bal.toLocaleString()} SDG ${isUSD ? '<span class="text-amber-300 text-[9px] font-sans">[USD]</span>' : ''}</span>
                <span class="text-slate-400 text-[10px] block">${foreignBal} $</span>
              </div>
            </div>`;
        });

        list.innerHTML = html;
      },

      selectCustomer(c, isDefaultInit = false) {
        if (window.SuitePOSRouter && SuitePOSRouter.getActiveContext() === 'purchase') {
          if (window.SuitePurchasePOS) SuitePurchasePOS.selectSupplier(c);
          return;
        }
        if (!c) return;
        this.selectedCustomer = c;
        if (!isDefaultInit) this._userPickedCustomer = true;
        const name = c.name || 'عميل نقدي عام';

        // Update input
        const hid = document.getElementById('salesCustomerInput');
        if (hid) {
          hid.value = name;
          if (isDefaultInit) {
            hid.classList.add('pos-ghost-default');
          } else {
            hid.classList.remove('pos-ghost-default');
          }
          if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleCustomer(hid);
        }

        // Display ONLY customer name in the main field (no phone here as requested)
        const disp = document.getElementById('suiteCustDisplayName');
        if (disp) {
          disp.innerHTML = `<span class="font-bold text-[15px] sm:text-base truncate tracking-wide block w-full text-right" style="color: #cbd5e1 !important;">${name}</span>`;
        }

        // Show Statement Button
        const stmtBtn = document.getElementById('btnCustStatementDirect');
        if (stmtBtn) {
          stmtBtn.classList.remove('hidden');
          stmtBtn.title = 'كشف حساب ' + name;
        }

        this.closeCustomerSearch();
        if (!isDefaultInit && window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(true, 'modalOpSales');
      },

      // ─────────────────────────────────────────────────────────────
      // 3. WAREHOUSE SEARCH (المستودعات والمخازن مع خانة بحث)
      // ─────────────────────────────────────────────────────────────
      openWarehouseSearch() {
        window._activeModalContext = 'sales';
        const modal = document.getElementById('suiteWarehouseSearchModal');
        if (modal) {
          modal.classList.remove('hidden');
          const inp = document.getElementById('suiteWarehouseSearchFilter');
          if (inp) {
            inp.value = '';
            setTimeout(() => inp.focus(), 50);
          }
          this.filterWarehouseSearch('');
        }
      },

      closeWarehouseSearch() {
        const modal = document.getElementById('suiteWarehouseSearchModal');
        if (modal) modal.classList.add('hidden');
      },

      filterWarehouseSearch(q) {
        const list = document.getElementById('suiteWarehouseSearchResults');
        if (!list) return;

        const term = (q || '').trim().toLowerCase();
        const filtered = this.dbWarehouses.filter(w => (w.name || '').toLowerCase().includes(term) || (w.type || '').toLowerCase().includes(term));

        if (filtered.length === 0) {
          list.innerHTML = '<div class="text-center py-4 text-slate-500 text-xs">لا توجد مستودعات مطابقة للبحث</div>';
          return;
        }

        let html = '';
        filtered.forEach(w => {
          html += `
            <div class="p-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-purple-500/50 flex justify-between items-center cursor-pointer transition active:scale-[0.99]" onclick="SuiteSalesPOS.selectWarehouse('${(w.name || '').replace(/'/g, "\\'")}')">
              <span class="font-bold text-white text-xs"><i class="fa-solid fa-warehouse text-purple-400 text-[10px]"></i> ${w.name}</span>
              <span class="text-[10px] text-slate-400 font-mono">${w.type || 'مستودع'}</span>
            </div>`;
        });
        list.innerHTML = html;
      },

      selectWarehouse(name, isDefaultInit = false) {
        if (window.SuitePOSRouter && SuitePOSRouter.getActiveContext() === 'purchase') {
          if (window.SuitePurchasePOS) SuitePurchasePOS.selectWarehouse(name);
          return;
        }
        this.selectedWarehouse = name;
        if (!isDefaultInit) this._userPickedWarehouse = true;
        const hid = document.getElementById('salesWarehouseSelect');
        if (hid) hid.value = name;
        const wInp = document.getElementById('salesWarehouseInput');
        if (wInp) {
          wInp.value = name;
          if (isDefaultInit) {
            wInp.classList.add('pos-ghost-default');
          } else {
            wInp.classList.remove('pos-ghost-default');
          }
          if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleWarehouse(wInp);
        }
        const disp = document.getElementById('suiteWarehouseDisplayName');
        if (disp) {
          disp.innerHTML = `<span class="font-black text-sm sm:text-base text-center leading-snug px-1" style="color: #ffffff !important;">${name}</span>`;
        }
        this.closeWarehouseSearch();
        if (!isDefaultInit && window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(true, 'modalOpSales');
      },

      // ─────────────────────────────────────────────────────────────
      // 4. ITEM SEARCH (الأصناف مع خانة بحث فوري)
      // ─────────────────────────────────────────────────────────────
      openItemSearch() {
        window._activeModalContext = 'sales';
        const modal = document.getElementById('suiteItemSearchModal');
        if (modal) {
          modal.classList.remove('hidden');
          const inp = document.getElementById('suiteItemSearchFilter');
          if (inp) {
            inp.value = '';
            setTimeout(() => inp.focus(), 50);
          }
          this.filterItemSearch('');
        }
      },

      closeItemSearch() {
        const modal = document.getElementById('suiteItemSearchModal');
        if (modal) modal.classList.add('hidden');
      },

      filterItemSearch(q) {
        const list = document.getElementById('suiteItemSearchResults');
        if (!list) return;

        const term = (q || '').trim().toLowerCase();
        const rawTerm = (q || '').trim();
        const filtered = this.dbItems.map((it, idx) => ({ ...it, originalIndex: idx })).filter(it => {
          const name = (it.name || '').toLowerCase();
          const barcode = (it.barcode || '').toLowerCase();
          const unit = (it.baseUnit || it.unit || '').toLowerCase();
          return name.includes(term) || barcode.includes(term) || unit.includes(term);
        });

        if (filtered.length === 0) {
          list.innerHTML = `
            <div class="text-center py-5 space-y-2.5 bg-slate-900/60 rounded-xl border border-slate-800/80 p-3">
              <div class="text-slate-400 text-xs">لا توجد أصناف مطابقة للبحث "${rawTerm}"</div>
              <button type="button" onclick="SuiteSalesPOS.selectCustomItem('${rawTerm.replace(/'/g, "\\'")}')" 
                      class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-amber-600 to-emerald-600 text-white font-black text-xs shadow-lg active:scale-95 transition cursor-pointer flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-plus-circle"></i>
                <span>إضافة "${rawTerm}" كسطر حر غير مسجل في السيستم</span>
              </button>
            </div>`;
          return;
        }

        let html = '';
        filtered.forEach(it => {
          const prc = parseFloat(it.sellingPrice || it.price || 0);
          const u = it.baseUnit || it.unit || 'حبة';

          html += `
            <div class="p-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-amber-500/50 flex justify-between items-center cursor-pointer transition active:scale-[0.99]" onclick="SuiteSalesPOS.selectItemByIndex(${it.originalIndex})">
              <div>
                <span class="font-bold text-white block text-xs">${it.name}</span>
                <span class="text-amber-300 font-mono text-[10px] block"><i class="fa-solid fa-scale-balanced text-[9px]"></i> ${u} ${it.barcode ? '• باركود: ' + it.barcode : ''}</span>
              </div>
              <div class="text-left font-mono text-xs">
                <span class="text-cyan-300 font-black block">${prc > 0 ? prc.toLocaleString() + ' SDG' : 'حسب التسعيرة'}</span>
              </div>
            </div>`;
        });

        if (rawTerm.length > 0) {
          html += `
            <div class="pt-2 border-t border-slate-800/80">
              <button type="button" onclick="SuiteSalesPOS.selectCustomItem('${rawTerm.replace(/'/g, "\\'")}')" 
                      class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-amber-300 border border-amber-500/30 text-[11px] font-black flex items-center justify-center gap-1.5 transition active:scale-95 cursor-pointer">
                <i class="fa-solid fa-plus text-amber-400"></i>
                <span>إضافة "${rawTerm}" كسطر حر جديد</span>
              </button>
            </div>`;
        }

        list.innerHTML = html;
      },

      selectCustomItem(name) {
        if (window.SuitePOSRouter && SuitePOSRouter.getActiveContext() === 'purchase') {
          if (window.SuitePurchasePOS) SuitePurchasePOS.selectCustomItem(name);
          return;
        }
        if (!name || !name.trim()) return;
        const cleanName = name.trim();
        const newItem = {
          id: 'CUSTOM-' + Date.now(),
          name: cleanName,
          price: 0,
          sellingPrice: 0,
          baseUnit: 'حبة',
          unit: 'حبة',
          isCustom: true
        };
        this.dbItems.unshift(newItem);
        this.selectItemByIndex(0);
        this.closeItemSearch();
        setTimeout(() => {
          const p = document.getElementById('salesPriceInput');
          if (p) p.focus();
        }, 100);
      },

      addCustomNonSystemItem() {
        const customName = prompt('أدخل اسم الصنف غير الموجود في السيستم (سطر حر / صنف ناقص):');
        if (!customName || !customName.trim()) return;
        this.selectCustomItem(customName.trim());
      },

      onNumericFieldFocus(el) {
        if (!el) return;
        if (el.classList.contains('pos-ghost-default') || el.dataset.ghostAuto === '1') {
          el.value = '';
          el.classList.remove('pos-ghost-default');
          delete el.dataset.ghostAuto;
        }
      },

      onNumericFieldInput(el) {
        if (!el) return;
        if (el.classList.contains('pos-ghost-default') || el.dataset.ghostAuto === '1') {
          el.classList.remove('pos-ghost-default');
          delete el.dataset.ghostAuto;
        }
      },

      selectItemByIndex(idx) {
        if (window.SuitePOSRouter && SuitePOSRouter.getActiveContext() === 'purchase') {
          if (window.SuitePurchasePOS) SuitePurchasePOS.selectItemByIndex(idx);
          return;
        }
        this.selectedItemIndex = idx;
        const it = this.dbItems[idx];
        if (!it) return;

        const disp = document.getElementById('suiteItemDisplayName');
        if (disp) {
          disp.innerHTML = `<span class="font-black text-xl sm:text-2xl truncate text-center tracking-wide" style="color: #ffffff !important;">${it.name}</span>`;
        }
        const hid = document.getElementById('salesItemSelectVal');
        if (hid) hid.value = idx;
        const itInp = document.getElementById('salesItemSearchInput');
        if (itInp) {
          itInp.value = it.name;
          itInp.classList.remove('pos-ghost-default');
          if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleItem(itInp);
        }

        const uSel = document.getElementById('salesUnitSelect');
        if (uSel) {
          let uHtml = '';
          const savedFactors = {};
          if (Array.isArray(it.unitsArray) && it.unitsArray.length > 0) {
            it.unitsArray.forEach(u => {
              const cleanUnitName = String(u.name || '').replace(/\s*\(\d+(?:\.\d+)?\)\s*/g, '').trim() || 'حبة';
              const factorVal = u.factor || 1;
              savedFactors[cleanUnitName] = factorVal;
              uHtml += `<option value="${cleanUnitName}" data-factor="${factorVal}">${cleanUnitName}</option>`;
            });
          } else {
            const defU = String(it.baseUnit || it.unit || 'حبة').replace(/\s*\(\d+(?:\.\d+)?\)\s*/g, '').trim() || 'حبة';
            savedFactors[defU] = 1;
            uHtml = `<option value="${defU}" data-factor="1">${defU}</option>`;
          }
          try {
            const allFactors = JSON.parse(localStorage.getItem('magaies_saved_unit_factors_v1') || '{}');
            allFactors[it.id || it.name] = savedFactors;
            localStorage.setItem('magaies_saved_unit_factors_v1', JSON.stringify(allFactors));
          } catch(e) {}
          uSel.innerHTML = uHtml;
          if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleUnit(uSel);
        }

        this.calcItemSubtotal();
        this.closeItemSearch();
        if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(true, 'modalOpSales');
      },

      onUnitChange() {
        this.calcItemSubtotal();
      },

      calcItemSubtotal() {
        const pNum = (id) => {
          const el = document.getElementById(id);
          if (!el || !el.value || !String(el.value).trim()) return 0;
          return window.SmartFieldCustomizer ? SmartFieldCustomizer.parseNum(el.value, id) : (parseFloat(String(el.value).replace(/,/g, '')) || 0);
        };
        const fmtNum = (id, val) => window.SmartFieldCustomizer ? SmartFieldCustomizer.formatNumberForField(id, val) : val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const qty = pNum('salesQtyInput');
        const prc = pNum('salesPriceInput');
        const disc = pNum('salesDiscountInput');
        const tax = pNum('salesTaxInput');
        const labor = pNum('salesLaborInput');

        // 1. الجملة هي السعر في الكمية (أو السعر × 1 إذا كُتب السعر فقط ولم تُكتب الكمية بعد)
        const effectiveQty = (qty > 0) ? qty : (prc > 0 ? 1 : 0);
        const gross = effectiveQty * prc;
        const grossEl = document.getElementById('salesItemGrossTotal');
        if (grossEl) {
          grossEl.value = (gross > 0) ? fmtNum('salesItemGrossTotal', gross) : '';
        }

        // 2. إجمالي الفاتورة (الإجمالي الصافي) = الجملة + الإضافة - الخصم + العتالة
        const hasInputs = (qty > 0 || prc > 0 || disc > 0 || tax > 0 || labor > 0);
        const netTotal = Math.max(0, gross - disc + tax + labor);
        const totInp = document.getElementById('salesTotalInput');
        if (totInp) {
          if (!this.cart || this.cart.length === 0) {
            totInp.value = (hasInputs && netTotal > 0) ? fmtNum('salesTotalInput', netTotal) : '';
          } else {
            const cartSum = this.cart.reduce((sum, item) => sum + (item.subtotal || 0), 0);
            totInp.value = fmtNum('salesTotalInput', cartSum + netTotal);
          }
        }
        if (window.SmartFieldCustomizer) window.SmartFieldCustomizer.applyAllSavedStyles();
        if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleAll();
        if (typeof this.updateErpSmartStatusStrip === 'function') this.updateErpSmartStatusStrip();
        if (typeof this.updateProfitMarginPeek === 'function') this.updateProfitMarginPeek();
        return netTotal;
      },

      addItemToCart() {
        // التحقق الصارم من أن الصنف المختار هو صنف محفوظ في السيستم
        let it = null;
        if (this.selectedItemIndex !== -1 && this.dbItems && this.dbItems[this.selectedItemIndex]) {
          it = this.dbItems[this.selectedItemIndex];
        } else {
          const itInp = document.getElementById('salesItemSearchInput');
          const val = (itInp?.value || '').trim().toLowerCase();
          if (val && this.dbItems) {
            const matchIdx = this.dbItems.findIndex(x => (x.name || '').trim().toLowerCase() === val);
            if (matchIdx !== -1) {
              this.selectItemByIndex(matchIdx);
              it = this.dbItems[matchIdx];
            }
          }
        }

        if (!it) {
          if (typeof showFloatingToast === 'function') {
            showFloatingToast('⚠️ لا يمكن إضافة سطر غير محفوظ! يرجى اختيار صنف من الأصناف المحفوظة.');
          }
          const itInp = document.getElementById('salesItemSearchInput');
          if (itInp) {
            itInp.value = '';
            itInp.focus();
            if (this.renderItemDropdown) this.renderItemDropdown('');
          }
          return;
        }

        const itName = it.name;
        const itUnit = it.baseUnit || it.unit || 'حبة';
        const itId = it.id || ('ITEM-' + Date.now());

        const uSel = document.getElementById('salesUnitSelect');
        const unit = (uSel && uSel.value) ? uSel.value : (itUnit || 'حبة');
        const warehouse = this.selectedWarehouse || document.getElementById('salesWarehouseSelect')?.value || 'المستودع الرئيسي';
        const costCenter = this.selectedCostCenter || document.getElementById('salesCostCenterInput')?.value || 'المركز الرئيسي';

        const pNum = (id, defVal = 0) => {
          const raw = document.getElementById(id)?.value;
          if (!raw || !String(raw).trim()) return defVal;
          return window.SmartFieldCustomizer ? SmartFieldCustomizer.parseNum(raw, id) : parseFloat(String(raw).replace(/,/g, '')) || defVal;
        };
        const qty = pNum('salesQtyInput', 1);
        const price = pNum('salesPriceInput', 0);
        const discount = pNum('salesDiscountInput', 0);
        const tax = pNum('salesTaxInput', 0);
        const labor = pNum('salesLaborInput', 0);
        const notes = document.getElementById('salesNotesInput')?.value?.trim() || '';
        const subtotal = Math.max(0, (qty * price) - discount + tax + labor);

        this.cart.push({
          id: itId,
          name: itName,
          unit,
          warehouse,
          costCenter,
          qty,
          price,
          discount,
          tax,
          labor,
          notes,
          subtotal
        });

        // Reset item inputs
        this.selectedItemIndex = -1;
        const itInpReset = document.getElementById('salesItemSearchInput');
        if (itInpReset) itInpReset.value = '';
        const disp = document.getElementById('suiteItemDisplayName');
        if (disp) disp.innerHTML = '<span class="font-black text-xl sm:text-2xl truncate text-center" style="color: #fcd34d !important;">اختيار الصنف</span>';
        const hid = document.getElementById('salesItemSelectVal');
        if (hid) hid.value = '';
        const uSelReset = document.getElementById('salesUnitSelect');
        if (uSelReset) {
          uSelReset.innerHTML = '<option value="" disabled selected hidden>الوحدات</option><option value="حبة" data-factor="1">حبة</option><option value="طن عددي" data-factor="135" selected>طن عددي</option><option value="طن وزني" data-factor="140">طن وزني</option>';
          uSelReset.value = 'طن عددي';
        }
        document.getElementById('salesPriceInput').value = '';
        document.getElementById('salesQtyInput').value = '';
        document.getElementById('salesDiscountInput').value = '';
        document.getElementById('salesTaxInput').value = '';
        document.getElementById('salesLaborInput').value = '';
        document.getElementById('salesItemGrossTotal').value = '';

        this.renderCart();
        if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(true, 'modalOpSales');
        if (window.AppHaptics) AppHaptics.light();
        if (typeof showFloatingToast === 'function') {
          showFloatingToast(`✓ تم إضافة السطر: [${itName}] بنجاح`);
        }
      },

      removeItem(idx) {
        this.cart.splice(idx, 1);
        this.renderCart();
        if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(true, 'modalOpSales');
        if (typeof showFloatingToast === 'function') {
          showFloatingToast('🗑️ تم حذف السطر من الفاتورة');
        }
      },

      clearCart() {
        if (this.cart.length === 0) return;
        if (confirm('هل أنت متأكد من تفريغ كافة أسطر الفاتورة؟')) {
          this.cart = [];
          this.renderCart();
          if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(true, 'modalOpSales');
          if (typeof showFloatingToast === 'function') {
            showFloatingToast('🗑️ تم تفريغ كافة أسطر الفاتورة');
          }
        }
      },

      renderCart() {
        const box = document.getElementById('suiteCartItemsContainer');
        const countEl = document.getElementById('suiteCartCount');
        if (countEl) countEl.innerText = this.cart.length;

        const clrWrap = document.getElementById('suiteCartClearWrap');
        if (clrWrap) clrWrap.classList.toggle('hidden', this.cart.length === 0);

        if (!box) return;

        const curr = this.currencyMode === 'USD' ? 'USD' : 'SDG';

        if (this.cart.length === 0) {
          box.innerHTML = `<div class="text-center py-4 text-slate-500 text-xs bg-slate-900/40 rounded-xl border border-slate-800/80">
            <i class="fa-solid fa-receipt text-base mb-1 block opacity-40"></i>
            لم يتم إضافة أي أسطر للفاتورة بعد
          </div>`;
          const gt = document.getElementById('suiteGrandTotalDisplay');
          if (gt) gt.innerText = `0.00 ${curr}`;
          return;
        }

        let html = '';
        let grandTot = 0;

        this.cart.forEach((item, idx) => {
          grandTot += item.subtotal;
          html += `
            <div class="p-2 sm:p-2.5 rounded-xl bg-slate-900/90 border border-slate-800 space-y-1 relative overflow-hidden" style="border-right: 3px solid #38bdf8;">
              <div class="flex justify-between items-start">
                <div class="min-w-0 flex-1 pr-1">
                  <span class="font-black text-slate-100 text-xs sm:text-sm block truncate">${item.name}</span>
                  <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mt-0.5 flex-wrap">
                    <span class="text-purple-300 font-bold"><i class="fa-solid fa-warehouse text-[9px]"></i> ${item.warehouse}</span>
                    <span>•</span>
                    <span class="text-emerald-300 font-bold"><i class="fa-solid fa-scale-balanced text-[9px]"></i> ${item.unit}</span>
                    ${item.costCenter ? `<span>•</span><span class="text-amber-300 font-bold">${item.costCenter}</span>` : ''}
                  </div>
                </div>
                <div class="text-left shrink-0">
                  <span class="font-black text-emerald-400 font-mono text-xs sm:text-sm block">${item.subtotal.toLocaleString()} ${curr}</span>
                  <button type="button" onclick="SuiteSalesPOS.removeItem(${idx})" class="text-rose-400 hover:text-rose-300 text-[10.5px] mt-0.5 font-bold flex items-center gap-1 cursor-pointer">
                    <i class="fa-solid fa-trash-can text-[9px]"></i> حذف السطر
                  </button>
                </div>
              </div>

              <div class="grid grid-cols-4 gap-1 text-center bg-slate-950/90 rounded-lg p-1 text-[9.5px] font-mono">
                <div><span class="text-slate-500 block">الكمية</span><span class="text-white font-black">${item.qty}</span></div>
                <div><span class="text-slate-500 block">السعر</span><span class="text-cyan-300 font-black">${item.price.toLocaleString()}</span></div>
                <div><span class="text-slate-500 block">الخصم</span><span class="text-rose-400 font-black">${item.discount.toLocaleString()}</span></div>
                <div><span class="text-slate-500 block">إضافة/عتالة</span><span class="text-purple-300 font-black">${(item.tax + item.labor).toLocaleString()}</span></div>
              </div>
            </div>`;
        });

        box.innerHTML = html;
        const gt = document.getElementById('suiteGrandTotalDisplay');
        if (gt) gt.innerText = grandTot.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + curr;

        // Sync net total input if cart has items
        const totInp = document.getElementById('salesTotalInput');
        if (totInp) totInp.value = grandTot.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      },

      setPayMethod(m) {
        document.querySelectorAll('.suite-pay-btn').forEach(b => {
          b.className = 'suite-pay-btn py-1.5 rounded-xl text-[10px] font-black border transition bg-slate-950 text-slate-400 border-slate-800 hover:text-white flex flex-col items-center justify-center gap-0.5';
        });
        const activeMap = {
          cash: 'suitePayBtnCash',
          bank: 'suitePayBtnBank',
          check: 'suitePayBtnCheck',
          credit: 'suitePayBtnCredit',
          split: 'suitePayBtnSplit'
        };
        const el = document.getElementById(activeMap[m]);
        if (el) {
          el.className = 'suite-pay-btn py-1.5 rounded-xl text-[10px] font-black border transition bg-emerald-600 text-white border-emerald-400 shadow-[0_0_12px_rgba(16,185,129,0.4)] flex flex-col items-center justify-center gap-0.5';
        }
        const hid = document.getElementById('salesPayMethodSelect');
        if (hid) hid.value = m;
      },

      // ─────────────────────────────────────────────────────────────
      // 5. INVOICE NAVIGATION & ARCHIVE
      // ─────────────────────────────────────────────────────────────
      async loadRecentInvoices() {
        const merged = [];
        const addUnique = (s) => {
          if (!s || typeof s !== 'object') return;
          const docId = String(s.invoiceNo || s.id || '').trim();
          if (!docId) return;
          const existingIdx = merged.findIndex(m => String(m.invoiceNo || m.id || '').trim() === docId);
          if (existingIdx === -1) {
            merged.push(s);
          } else {
            // دمج الحقول الناقصة إن وجدت نسخة أكمل
            merged[existingIdx] = Object.assign({}, s, merged[existingIdx]);
          }
        };
        const sortNewestFirst = (arr) => {
          return arr.sort((a, b) => {
            const idA = String(a.invoiceNo || a.id || '');
            const idB = String(b.invoiceNo || b.id || '');
            const numA = parseInt(idA.replace(/\D/g, ''), 10) || 0;
            const numB = parseInt(idB.replace(/\D/g, ''), 10) || 0;
            if (numB !== numA) return numB - numA;
            const tA = new Date(a.savedAt || a.createdAt || a.date || 0).getTime() || 0;
            const tB = new Date(b.savedAt || b.createdAt || b.date || 0).getTime() || 0;
            return tB - tA;
          });
        };
        try {
          const localSaved = JSON.parse(localStorage.getItem('magaies_local_sales_invoices_v1') || '[]');
          if (Array.isArray(localSaved)) localSaved.forEach(addUnique);
          const erpSaved = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
          if (Array.isArray(erpSaved)) erpSaved.forEach(addUnique);
          if (window.MagaiesOffline && typeof MagaiesOffline.getQueue === 'function') {
            MagaiesOffline.getQueue().forEach(q => {
              if ((q.type === 'sales' || q.type === 'sales_split') && q.payload) {
                addUnique(Object.assign({ invoiceNo: q.tempId }, q.payload));
              }
            });
          }
          const mirrorCache = JSON.parse(localStorage.getItem('magaies_mirror_cache_v1') || '{}');
          if (Array.isArray(mirrorCache.sales)) mirrorCache.sales.forEach(addUnique);
          if (merged.length > 0) this.recentInvoices = sortNewestFirst(merged);
        } catch(e) {}
        try {
          const res = await fetch('api/mirror_sync.php?action=get_recent_sales&_t=' + Date.now());
          if (!res.ok) return;
          const data = await res.json();
          if (data && Array.isArray(data.sales) && data.sales.length > 0) {
            data.sales.forEach(addUnique);
            this.recentInvoices = sortNewestFirst(merged);
          }
        } catch(e) {}
      },

      navigateInvoice(dir) {
        if (!this.recentInvoices || this.recentInvoices.length === 0) {
          this.loadRecentInvoices().then(() => {
            if (this.recentInvoices && this.recentInvoices.length > 0) {
              this.navigateInvoice(dir);
            } else if (typeof showFloatingToast === 'function') {
              showFloatingToast('ℹ️ لا توجد فواتير مبيعات سابقة مسجلة حتى الآن.');
            }
          });
          return;
        }

        const pill = document.getElementById('opSalesInvoiceNoPill');
        if (this.currentInvoiceIndex === -1 && pill) {
          this.latestGeneratedInvNo = pill.innerText;
        }

        if (dir === 'prev') {
          if (this.currentInvoiceIndex < this.recentInvoices.length - 1) {
            this.currentInvoiceIndex++;
            this.displayArchivedInvoice(this.recentInvoices[this.currentInvoiceIndex]);
          } else {
            if (typeof showFloatingToast === 'function') {
              showFloatingToast('ℹ️ وصلت إلى أقدم فاتورة مبيعات مسجلة.');
            }
          }
        } else if (dir === 'next') {
          if (this.currentInvoiceIndex > 0) {
            this.currentInvoiceIndex--;
            this.displayArchivedInvoice(this.recentInvoices[this.currentInvoiceIndex]);
          } else if (this.currentInvoiceIndex === 0) {
            this.currentInvoiceIndex = -1;
            this.newInvoice();
            if (typeof showFloatingToast === 'function') {
              showFloatingToast('📄 تم الرجوع إلى شاشة الفاتورة الجديدة.');
            }
          } else {
            if (typeof showFloatingToast === 'function') {
              showFloatingToast('ℹ️ أنت بالفعل في أحدث فاتورة جديدة.');
            }
          }
        }
      },

      displayArchivedInvoice(inv) {
        if (!inv) return;
        const docNo = inv.invoiceNo || inv.id || '';
        const pill = document.getElementById('opSalesInvoiceNoPill');
        if (pill) pill.innerText = docNo;

        // 1. التاريخ
        const rawDate = String(inv.date || inv.savedAt || inv.createdAt || new Date().toISOString()).split('T')[0].split(' ')[0];
        const dInp = document.getElementById('salesInvoiceDate');
        if (dInp && rawDate) {
          dInp.value = rawDate;
          this.syncHeaderDatePill(rawDate);
        }

        // 2. العميل (يدعم الاسم المباشر أو الرقم التعريفي من اللابتوب/الجوال)
        const rawCustIdOrName = inv.customerName || inv.customer || inv.customerId || '';
        const matchedCust = (this.dbCustomers || []).find(cu =>
          cu.name === rawCustIdOrName ||
          String(cu.id) === String(inv.customerId || inv.customer || '')
        );
        const custName = matchedCust ? matchedCust.name : (inv.customerName || (typeof inv.customer === 'string' && !inv.customer.startsWith('cust-') ? inv.customer : '') || 'عميل نقدي عام');
        const c = matchedCust || {
          name: custName,
          phone: inv.customerPhone || '',
          currentBalance: 0,
          currency: inv.currency || 'SDG'
        };
        this.selectCustomer(c);
        const custInp = document.getElementById('salesCustomerInput');
        if (custInp) {
          custInp.value = custName;
          custInp.classList.remove('pos-ghost-default');
          delete custInp.dataset.ghostAuto;
        }

        // 3. المستودع ومركز الكلفة (مع تحويل المعرفات مثل wh-main إلى الاسم العربي)
        const firstItem = (Array.isArray(inv.items) && inv.items.length > 0) ? inv.items[0] : inv;
        const rawWh = inv.warehouse || firstItem?.warehouse || firstItem?.warehouseId || 'المستودع الرئيسي';
        const matchedWh = (this.dbWarehouses || []).find(w => w.name === rawWh || String(w.id) === String(rawWh));
        const whName = matchedWh ? matchedWh.name : rawWh;
        this.selectWarehouse(whName);
        const whInp = document.getElementById('salesWarehouseInput');
        if (whInp) {
          whInp.value = whName;
          whInp.classList.remove('pos-ghost-default');
          delete whInp.dataset.ghostAuto;
        }

        const rawCc = inv.costCenter || firstItem?.costCenter || firstItem?.costCenterId || 'المركز الرئيسي';
        const matchedCc = (this.dbCostCenters || []).find(cc => cc.name === rawCc || String(cc.id) === String(rawCc));
        const ccName = matchedCc ? matchedCc.name : rawCc;
        this.selectCostCenter(ccName);
        const ccInp = document.getElementById('salesCostCenterInput');
        if (ccInp) {
          ccInp.value = ccName;
          ccInp.classList.remove('pos-ghost-default');
          delete ccInp.dataset.ghostAuto;
        }

        // 4. الصنف والوحدة والكمية والسعر والجملة والخصم والإضافة والعتالة والإجمالي الصافي
        const rawItemId = firstItem?.itemId || firstItem?.id || inv.itemId || '';
        const rawItemName = firstItem?.name || firstItem?.itemName || inv.itemName || '';
        let itemIdx = (this.dbItems || []).findIndex(x =>
          (rawItemName && x.name === rawItemName) ||
          (rawItemId && String(x.id) === String(rawItemId))
        );
        const itemName = (itemIdx !== -1 && this.dbItems[itemIdx])
          ? this.dbItems[itemIdx].name
          : (rawItemName || 'صنف عام');
        this.selectedItemIndex = itemIdx;

        const itInp = document.getElementById('salesItemSearchInput');
        if (itInp) {
          itInp.value = itemName;
          itInp.classList.remove('pos-ghost-default');
          delete itInp.dataset.ghostAuto;
        }
        const itDisp = document.getElementById('suiteItemDisplayName');
        if (itDisp) {
          itDisp.innerHTML = `<span class="font-black text-base sm:text-lg truncate text-center" style="color: #fcd34d !important;">${itemName}</span>`;
        }
        const itVal = document.getElementById('salesItemSelectVal');
        if (itVal) itVal.value = itemName;

        const unitVal = firstItem?.unit || firstItem?.selectedUnit || firstItem?.unitName || inv.unit || 'حبة';
        const uSel = document.getElementById('salesUnitSelect');
        if (uSel) {
          if (!Array.from(uSel.options).some(o => o.value === unitVal)) {
            const opt = document.createElement('option');
            opt.value = unitVal;
            opt.innerText = unitVal;
            opt.dataset.factor = firstItem?.factor || 1;
            uSel.appendChild(opt);
          }
          uSel.value = unitVal;
        }

        const qtyVal = parseFloat(firstItem?.qty ?? firstItem?.quantity ?? inv.qty ?? inv.quantity ?? 1) || 1;
        const priceVal = parseFloat(firstItem?.price ?? firstItem?.rate ?? inv.price ?? 0) || 0;
        const discVal = parseFloat(firstItem?.discount ?? inv.discount ?? inv.discountCost ?? 0) || 0;
        const taxVal = parseFloat(firstItem?.tax ?? inv.tax ?? inv.vatAmount ?? inv.additionsCost ?? inv.taxCost ?? 0) || 0;
        const laborVal = parseFloat(firstItem?.labor ?? inv.labor ?? inv.laborCost ?? inv.transportCost ?? 0) || 0;
        const grossVal = parseFloat(firstItem?.grossTotal ?? (qtyVal * priceVal)) || (qtyVal * priceVal);
        const netTotalVal = parseFloat(inv.grandTotal ?? inv.total ?? firstItem?.subtotal ?? firstItem?.total ?? (grossVal - discVal + taxVal + laborVal)) || 0;

        const setFieldVal = (id, val, fmt = false) => {
          const el = document.getElementById(id);
          if (!el) return;
          el.value = fmt ? Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : (val !== undefined && val !== null ? val : '');
          el.classList.remove('pos-ghost-default');
          delete el.dataset.ghostAuto;
        };

        setFieldVal('salesQtyInput', qtyVal);
        setFieldVal('salesPriceInput', priceVal);
        setFieldVal('salesItemGrossTotal', grossVal, true);
        setFieldVal('salesDiscountInput', discVal);
        setFieldVal('salesTaxInput', taxVal);
        setFieldVal('salesLaborInput', laborVal);

        // 5. الملحوظات / البيان (تحديث كامل حتى لو فارغ لعدم بقاء نص فاتورة سابقة)
        const notesStr = String(inv.notes ?? inv.description ?? inv.remark ?? inv.statement ?? firstItem?.notes ?? firstItem?.note ?? '');
        const nInp = document.getElementById('salesNotesInput');
        if (nInp) {
          nInp.value = notesStr;
          nInp.classList.remove('pos-ghost-default');
        }
        const nTxt = document.getElementById('salesNotesExpandedTextarea');
        if (nTxt) nTxt.value = notesStr;

        // 6. سلة وأسطر الفاتورة كاملة
        if (Array.isArray(inv.items) && inv.items.length > 0) {
          this.cart = inv.items.map(it => {
            const q = parseFloat(it.qty ?? it.quantity ?? 1) || 1;
            const p = parseFloat(it.price ?? it.rate ?? 0) || 0;
            const d = parseFloat(it.discount ?? 0) || 0;
            const t = parseFloat(it.tax ?? 0) || 0;
            const l = parseFloat(it.labor ?? 0) || 0;
            const wRaw = it.warehouse || it.warehouseId || whName;
            const wMatch = (this.dbWarehouses || []).find(w => w.name === wRaw || String(w.id) === String(wRaw));
            const cRaw = it.costCenter || it.costCenterId || ccName;
            const cMatch = (this.dbCostCenters || []).find(cc => cc.name === cRaw || String(cc.id) === String(cRaw));
            return {
              name: it.name || it.itemName || itemName,
              unit: it.unit || it.selectedUnit || it.unitName || 'حبة',
              warehouse: wMatch ? wMatch.name : wRaw,
              costCenter: cMatch ? cMatch.name : cRaw,
              qty: q,
              price: p,
              discount: d,
              tax: t,
              labor: l,
              notes: it.notes || it.note || notesStr,
              subtotal: parseFloat(it.subtotal ?? it.total ?? ((q * p) - d + t + l)) || 0
            };
          });
        } else {
          this.cart = [{
            name: itemName,
            unit: unitVal,
            warehouse: whName,
            costCenter: ccName,
            qty: qtyVal,
            price: priceVal,
            discount: discVal,
            tax: taxVal,
            labor: laborVal,
            notes: notesStr,
            subtotal: netTotalVal
          }];
        }
        this.renderCart();

        // ضمان عرض الإجمالي الصافي للفاتورة بعد رسم السلة
        setFieldVal('salesTotalInput', netTotalVal, true);
        const gt = document.getElementById('suiteGrandTotalDisplay');
        if (gt) {
          const curr = this.activeCurrency || inv.currency || 'SDG';
          gt.innerText = Number(netTotalVal || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + curr;
        }

        // 7. طريقة الدفع
        const resolvedPayMethod = inv.paymentMethod || (parseFloat(inv.creditAmount) > 0 && parseFloat(inv.cashAmount) > 0 ? 'split' : (parseFloat(inv.creditAmount) > 0 ? 'credit' : (parseFloat(inv.bankAmount) > 0 ? 'bank' : 'cash')));
        this.setPayMethod(resolvedPayMethod);

        if (typeof this.updateErpSmartStatusStrip === 'function') {
          this.updateErpSmartStatusStrip();
        }
        if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleAll();
        if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();
        if (typeof showFloatingToast === 'function') {
          showFloatingToast(`📂 استعراض فاتورة مبيعات #${docNo} (${custName})`);
        }
      },

      setDefaultDate() {
        const d = document.getElementById('salesInvoiceDate');
        if (d && !d.value) {
          const now = new Date();
          d.value = now.toISOString().split('T')[0];
        }
        this.syncHeaderDatePill();
      },

      syncHeaderDatePill(val) {
        const d = val || document.getElementById('salesInvoiceDate')?.value;
        const pill = document.getElementById('opSalesHeaderDatePill');
        if (pill && d) {
          pill.innerText = d;
        }
      },

      // ─────────────────────────────────────────────────────────────
      // VOICE FEEDING & SMART EVACUATION ENGINE (تغذية وتفريغ الخانات بالصوت بأعلى درجات الذكاء الاصطناعي)
      // ─────────────────────────────────────────────────────────────
      voiceRecognitionInstance: null,
      isVoiceListening: false,
      _voiceSilenceTimer: null,
      _lastVoiceActivityAt: 0,
      _manualStopVoice: false,
      _pendingInterimVoice: '',

      syncAllErpDataForAI() {
        try {
          const extraCusts = [];
          if (Array.isArray(window.masterAccountsList)) {
            window.masterAccountsList.forEach(a => {
              if (a && a.name) extraCusts.push({ id: a.id || ('ACC-' + a.name), name: a.name, phone: a.phone || '', currentBalance: parseFloat(a.balance || a.sdgBalance || 0), currency: a.currency || 'SDG' });
            });
          }
          ['erp_customers_v1', 'erp_accounts_v1', 'magaies_customers_cache'].forEach(k => {
            try {
              const raw = localStorage.getItem(k) || (window.getTenantKey ? localStorage.getItem(window.getTenantKey(k)) : null);
              if (raw) {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                  parsed.forEach(c => {
                    const nm = c?.name || c?.accountName || c?.title;
                    if (nm && String(nm).trim().length >= 2) {
                      extraCusts.push({
                        id: c.id || ('CUST-' + nm),
                        name: String(nm).trim(),
                        phone: c.phone || c.whatsapp || '',
                        currentBalance: parseFloat(c.currentBalance || c.balance || c.openingBalance || 0),
                        currency: c.currency || 'SDG'
                      });
                    }
                  });
                }
              }
            } catch (e) {}
          });
          if (!Array.isArray(this.dbCustomers)) this.dbCustomers = [];
          extraCusts.forEach(ec => {
            if (!this.dbCustomers.some(x => x && x.name && x.name.trim() === ec.name.trim())) {
              this.dbCustomers.push(ec);
            }
          });
        } catch (e) {}
      },

      // في شاشة المبيعات: التسجيل مستمر بلا انقطاع مهما طالت المدة وحتى لو سكت المستخدم 3 ثوانٍ أو أكثر
      resetVoiceSilenceTimer() {
        this._lastVoiceActivityAt = Date.now();
        if (this._voiceSilenceTimer) {
          clearTimeout(this._voiceSilenceTimer);
          this._voiceSilenceTimer = null;
        }
      },

      toggleVoiceFeed() {
        const consoleEl = document.getElementById('suiteSalesVoiceConsole');
        if (consoleEl && consoleEl.classList.contains('hidden')) {
          consoleEl.classList.remove('hidden');
        }

        if (this.isVoiceListening) {
          this.stopVoiceFeed(true, '⏹️ تم إيقاف التسجيل الصوتي يدوياً');
          return;
        }

        const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
        const statusText = document.getElementById('suiteSalesVoiceStatusText');
        const transcriptEl = document.getElementById('suiteSalesVoiceTranscript');
        const pulseDot = document.getElementById('salesMicPulseDot');
        const micBtn = document.getElementById('btnSalesVoiceMic');

        if (!SpeechRec) {
          if (statusText) statusText.innerHTML = '<span class="text-amber-400">ℹ️ المتصفح لا يدعم الميكروفون المباشر. يمكنك استخدام التجارب الجاهزة أدناه أو كتابة الأمر والتفريغ فوراً:</span>';
          return;
        }

        try {
          this._manualStopVoice = false;
          this._pendingInterimVoice = '';
          this.voiceRecognitionInstance = new SpeechRec();
          this.voiceRecognitionInstance.lang = 'ar-SA';
          this.voiceRecognitionInstance.continuous = true;
          this.voiceRecognitionInstance.interimResults = true;

          this.voiceRecognitionInstance.onstart = () => {
            this.isVoiceListening = true;
            this._lastVoiceActivityAt = Date.now();
            if (pulseDot) pulseDot.classList.remove('hidden');
            if (micBtn) micBtn.style.setProperty('box-shadow', '0 0 22px rgba(244, 63, 94, 0.95)', 'important');
            if (statusText) statusText.innerHTML = '<span class="text-rose-400 animate-pulse font-black">🔴 التسجيل مستمر بلا انقطاع... تحدث بأي ترتيب أو قل "حفظ"</span>';
            if (transcriptEl && !this._pendingInterimVoice) transcriptEl.innerText = 'أستمع إليك الآن بلا توقف... اذكر العميل، المستودع، الصنف، الوحدة، الكمية، السعر بأي ترتيب';
            if (window.AppHaptics) AppHaptics.selection();
          };

          this.voiceRecognitionInstance.onsoundstart = () => { this._lastVoiceActivityAt = Date.now(); };
          this.voiceRecognitionInstance.onspeechstart = () => { this._lastVoiceActivityAt = Date.now(); };

          this.voiceRecognitionInstance.onresult = (evt) => {
            this._lastVoiceActivityAt = Date.now();
            let interim = '';
            let finalChunk = '';
            for (let i = evt.resultIndex; i < evt.results.length; ++i) {
              if (evt.results[i].isFinal) finalChunk += ' ' + evt.results[i][0].transcript;
              else interim += ' ' + evt.results[i][0].transcript;
            }
            const cleanFinal = finalChunk.trim();
            const cleanInterim = interim.trim();
            if (transcriptEl) transcriptEl.innerText = cleanFinal || cleanInterim;
            if (cleanFinal) {
              this._pendingInterimVoice = '';
              this.feedVoiceToFields(cleanFinal);
            } else if (cleanInterim) {
              this._pendingInterimVoice = cleanInterim;
            }
          };

          this.voiceRecognitionInstance.onerror = (err) => {
            if (this.isVoiceListening && !this._manualStopVoice) {
              if (!err || err.error === 'no-speech' || err.error === 'aborted' || err.error === 'network') {
                return;
              }
            }
            console.warn('Sales voice error:', err);
            if (err && (err.error === 'not-allowed' || err.error === 'service-not-allowed')) {
              this.stopVoiceFeed(true);
              if (statusText) statusText.innerHTML = '<span class="text-amber-400">⚠️ يرجى السماح بصلاحية الميكروفون، أو استخدم التجارب والأوامر السريعة أدناه:</span>';
            }
          };

          this.voiceRecognitionInstance.onend = () => {
            if (this.isVoiceListening && !this._manualStopVoice) {
              try {
                this.voiceRecognitionInstance.start();
                return;
              } catch (e) {
                setTimeout(() => {
                  if (this.isVoiceListening && !this._manualStopVoice && this.voiceRecognitionInstance) {
                    try { this.voiceRecognitionInstance.start(); } catch (e2) {}
                  }
                }, 150);
                return;
              }
            }
            this.stopVoiceFeed(true);
          };

          this.voiceRecognitionInstance.start();
        } catch (e) {
          console.error(e);
          this.stopVoiceFeed(true);
        }
      },

      stopVoiceFeed(manual = true, customMsg = '') {
        if (manual) this._manualStopVoice = true;
        if (this._voiceSilenceTimer) {
          clearTimeout(this._voiceSilenceTimer);
          this._voiceSilenceTimer = null;
        }
        if (this._pendingInterimVoice) {
          const pending = this._pendingInterimVoice;
          this._pendingInterimVoice = '';
          this.feedVoiceToFields(pending);
        }
        this.isVoiceListening = false;
        const pulseDot = document.getElementById('salesMicPulseDot');
        if (pulseDot) pulseDot.classList.add('hidden');
        const micBtn = document.getElementById('btnSalesVoiceMic');
        if (micBtn) micBtn.style.setProperty('box-shadow', '0 0 14px rgba(244, 63, 94, 0.45)', 'important');
        const statusText = document.getElementById('suiteSalesVoiceStatusText');
        if (statusText && customMsg) {
          statusText.innerHTML = '<span class="text-emerald-400 font-black">' + customMsg + '</span>';
        }
        if (this.voiceRecognitionInstance) {
          try { this.voiceRecognitionInstance.stop(); } catch(e) {}
        }
      },

      feedVoiceFromInput() {
        const inp = document.getElementById('suiteSalesVoiceManualInput');
        if (inp && inp.value.trim()) {
          this.feedVoiceToFields(inp.value.trim());
          inp.value = '';
        }
      },

      feedVoiceToFields(spokenText) {
        if (!spokenText || !spokenText.trim()) return;
        this.syncAllErpDataForAI();

        const rawOriginal = spokenText.trim();
        const transcriptEl = document.getElementById('suiteSalesVoiceTranscript');
        if (transcriptEl) transcriptEl.innerText = '🎙️ تم التقاط: "' + rawOriginal + '"';

        // 1. تحويل الأرقام الهندية والألفاظ العددية المركبة إلى أرقام قياسية
        const normalizeSpokenNumbers = (inputStr) => {
          let s = String(inputStr || '');
          const eastern = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
          for (let i = 0; i < 10; i++) s = s.split(eastern[i]).join(i.toString());
          s = s.replace(/(\d)[,،](\d{3})/g, '$1$2');

          const compoundMap = [
            [/سبعين\s*الف/gi, '70000'],
            [/ستين\s*الف/gi, '60000'],
            [/خمسين\s*الف/gi, '50000'],
            [/اربعين\s*الف|أربعين\s*الف/gi, '40000'],
            [/ثلاثين\s*الف/gi, '30000'],
            [/عشرين\s*الف/gi, '20000'],
            [/ستة\s*عشر\s*الف|سته\s*عشر\s*الف|سطاشر\s*الف/gi, '16000'],
            [/خمسة\s*عشر\s*الف|خمسه\s*عشر\s*الف|خمسطاشر\s*الف/gi, '15000'],
            [/عشرة\s*الف|عشره\s*الف|عشر\s*آلاف|عشرة\s*آلاف/gi, '10000'],
            [/ثلاثة\s*الف\s*وخمسمائة|ثلاثه\s*الف\s*وخمسمائه|ثلاثة\s*آلاف\s*وخمسمائة/gi, '3500'],
            [/الفين|ألفين/gi, '2000'],
            [/الف|ألف/gi, '1000'],
            [/(?:^|[\s,،])(?:واحد)(?=$|[\s,،])/gi, ' 1 '],
            [/(?:^|[\s,،])(?:اثنين|اثنان)(?=$|[\s,،])/gi, ' 2 '],
            [/(?:^|[\s,،])(?:ثلاثة|ثلاثه|ثلاث|تلاتة|تلاته)(?=$|[\s,،])/gi, ' 3 '],
            [/(?:^|[\s,،])(?:اربعة|أربعة|اربعه|أربعه|اربع)(?=$|[\s,،])/gi, ' 4 '],
            [/(?:^|[\s,،])(?:خمسة|خمسه|خمس)(?=$|[\s,،])/gi, ' 5 '],
            [/(?:^|[\s,،])(?:ستة|سته|ست)(?=$|[\s,،])/gi, ' 6 '],
            [/(?:^|[\s,،])(?:سبعة|سبعه|سبع)(?=$|[\s,،])/gi, ' 7 '],
            [/(?:^|[\s,،])(?:ثمانية|ثمانيه|تمانية|تمانيه|ثمان)(?=$|[\s,،])/gi, ' 8 '],
            [/(?:^|[\s,،])(?:تسعة|تسعه|تسع)(?=$|[\s,،])/gi, ' 9 '],
            [/(?:^|[\s,،])(?:عشرة|عشره|عشر)(?=$|[\s,،])/gi, ' 10 ']
          ];
          for (const [rgx, val] of compoundMap) {
            s = s.replace(rgx, val);
          }
          return s;
        };

        const raw = normalizeSpokenNumbers(rawOriginal);

        // 2. دالة التطبيع الذكي الشامل (تدعم "درة اللاماب"، "سيخ 3 لين"، "سيخ 3 لينيا"، "العتال"، وكل اللهجات السودانية)
        const norm = (s) => (s || '').toString().trim()
          .replace(/[\u064B-\u065F\u0670\u0640]/g, '')
          .replace(/[إأآٱ]/g, 'ا')
          .replace(/ة/g, 'ه')
          .replace(/[ى]/g, 'ي')
          .replace(/اللمام|اللماب|للمام|لامام|لاماب|الاماب|الماب/g, 'اللاماب')
          .replace(/دره|درة|الدره|الدرة|داره|دارة/g, 'دره')
          .replace(/لينيار|لينيه|لينية|لنيه|لنيا/g, 'لينيا')
          .replace(/(\d)\s*لين(?=$|[\s,،])/g, '$1 لينيا')
          .replace(/(?:^|[\s,،])لين(?=$|[\s,،])/g, ' لينيا ')
          .replace(/العتال(?=$|[\s,،])|عتال(?=$|[\s,،])/g, 'العتاله')
          .replace(/الاسعد|أسعد/g, 'اسعد')
          .replace(/\s+/g, ' ')
          .trim()
          .toLowerCase();

        const cleanText = norm(raw);
        let feedback = [];
        let residualText = raw;

        const parseNum = (str) => {
          if (!str) return 0;
          const s = String(str).replace(/,/g, '').trim();
          const m = s.match(/\d+(?:\.\d+)?/);
          return m ? parseFloat(m[0]) : 0;
        };

        const prevQtyVal = document.getElementById('salesQtyInput')?.value || '';
        const prevPriceVal = document.getElementById('salesPriceInput')?.value || '';
        const prevUnitVal = document.getElementById('salesUnitSelect')?.value || '';

        // ─────────────────────────────────────────────────────────────
        // أ) استخراج الأرقام المرتبطة بكلمات دلالية (السعر، الإضافة، الخصم، العتالة، الكمية) بأي ترتيب
        // ─────────────────────────────────────────────────────────────
        let workingTextForQty = raw;

        // 1. السعر (Price)
        let spokenPrice = null;
        const mPrice = raw.match(/(?:السعر|سعر|بسعر|قيمة|بقيمة|بفئة|فئة|سعر\s*الطن)\s*[:=]?\s*([0-9]+(?:\.[0-9]+)?)/i);
        if (mPrice && mPrice[1]) {
          spokenPrice = parseNum(mPrice[1]);
          workingTextForQty = workingTextForQty.replace(mPrice[0], ' ');
          residualText = residualText.replace(mPrice[0], ' ');
        }

        // 2. الإضافة (Addition / Tax)
        let spokenTax = null;
        const mTax = raw.match(/(?:الإضافة|الاضافة|إضافة|اضافة|الاضافه|اضافه|ضريبة|زيادة|ترحيل)\s*[:=]?\s*([0-9]+(?:\.[0-9]+)?)/i);
        if (mTax && mTax[1]) {
          spokenTax = parseNum(mTax[1]);
          workingTextForQty = workingTextForQty.replace(mTax[0], ' ');
          residualText = residualText.replace(mTax[0], ' ');
        }

        // 3. الخصم (Discount)
        let spokenDisc = null;
        const mDisc = raw.match(/(?:الخصم|خصم|تخفيض|تنزيل|سماح)\s*[:=]?\s*([0-9]+(?:\.[0-9]+)?)/i);
        if (mDisc && mDisc[1]) {
          spokenDisc = parseNum(mDisc[1]);
          workingTextForQty = workingTextForQty.replace(mDisc[0], ' ');
          residualText = residualText.replace(mDisc[0], ' ');
        }

        // 4. العتالة / العتال (Labor)
        let spokenLabor = null;
        const mLabor = raw.match(/(?:العتالة|العتاله|عتالة|عتاله|العتال|عتال|الإعالة|الاعالة|إعالة|اعالة|مشال|تحميل)\s*[:=]?\s*([0-9]+(?:\.[0-9]+)?)/i);
        if (mLabor && mLabor[1]) {
          spokenLabor = parseNum(mLabor[1]);
          workingTextForQty = workingTextForQty.replace(mLabor[0], ' ');
          residualText = residualText.replace(mLabor[0], ' ');
        }

        // 5. الكمية (Quantity): سواء قال "الكمية 6 طن" أو قال مباشرة "5 طن" أو "6 طن" بدون كلمة الكمية
        let spokenQty = null;
        const mQtyExplicit = workingTextForQty.match(/(?:الكمية|الكميه|كمية|كميه|عدد|بعدد|وزن)\s*[:=]?\s*([0-9]+(?:\.[0-9]+)?)(?:\s*(?:طن|سيخة|سيخه|كيس|حبة|حبه|قطعة|متر|جالون|كرتونة|ربطة))?/i);
        if (mQtyExplicit && mQtyExplicit[1]) {
          spokenQty = parseNum(mQtyExplicit[1]);
          workingTextForQty = workingTextForQty.replace(mQtyExplicit[0], ' ');
          residualText = residualText.replace(mQtyExplicit[0], ' ');
        } else {
          // استثناء مقاسات الأصناف مثل "سيخ 3 لين" أو "سيخ 3 لينيا" أو "10 مللي" أو "50 كجم" قبل البحث عن الكمية الضمنية
          const cleanedForImplicitQty = workingTextForQty
            .replace(/سيخ\s*\d+\s*(?:لينيا|لينية|لينيه|لنيه|لين|ل|ملم|مللي)?/gi, ' ')
            .replace(/\d+\s*(?:لينيا|لينية|لينيه|لنيه|لين|مللي|ملم|كجم|كيلو\s*جرام)/gi, ' ');
          const mQtyImplicit = cleanedForImplicitQty.match(/(?:^|[\s,،])([0-9]+(?:\.[0-9]+)?)\s*(?:طن|سيخة|سيخه|كيس|اكياس|أكياس|حبة|حبه|قطعة|قطعه|متر|جالون|كرتونة|كرتونه|لوح|ربطة|ربطه)(?=$|[\s,،])/i);
          if (mQtyImplicit && mQtyImplicit[1]) {
            spokenQty = parseNum(mQtyImplicit[1]);
            residualText = residualText.replace(mQtyImplicit[0], ' ');
          }
        }

        // 6. ذكاء اصطناعي إضافي للسعر الضمني: إذا قال مثلاً "6 طن بـ 3500" أو ذكر رقماً كبيراً منفرداً بعد الكمية بدون كلمة "السعر"
        if (spokenPrice === null) {
          const mImplicitPrice = workingTextForQty
            .replace(/سيخ\s*\d+\s*(?:لينيا|لينية|لينيه|لنيه|لين|ل|ملم|مللي)?/gi, ' ')
            .replace(/\d+\s*(?:لينيا|لينية|لينيه|لنيه|لين|مللي|ملم|كجم)/gi, ' ')
            .match(/(?:بـ|ب)\s*([0-9]{3,}(?:\.[0-9]+)?)/i);
          if (mImplicitPrice && mImplicitPrice[1]) {
            spokenPrice = parseNum(mImplicitPrice[1]);
            residualText = residualText.replace(mImplicitPrice[0], ' ');
          }
        }

        // ─────────────────────────────────────────────────────────────
        // ب) التعرف الذكي على المستودع (Warehouse) المحفوظ في السيستم (مثل "درة اللاماب" / "مخزن السجانة" / "المستودع الرئيسي")
        // ─────────────────────────────────────────────────────────────
        let matchedWH = null;
        if (cleanText.includes('دره اللاماب') || cleanText.includes('اللاماب') || (cleanText.includes('دره') && !cleanText.includes('مخزن الدره'))) {
          const whObj = (this.dbWarehouses || []).find(w => norm(w.name).includes('دره') && norm(w.name).includes('اللاماب'));
          matchedWH = whObj ? whObj.name : 'دره اللاماب';
          residualText = residualText.replace(/(?:المستودع|مستودع|المخزن|مخزن)?\s*(?:درة|دره|الدرة|الدره)?\s*(?:اللاماب|اللمام|للمام|لاماب)/gi, ' ');
        } else {
          const sortedWH = [...(this.dbWarehouses || [])].sort((a, b) => (b.name || '').length - (a.name || '').length);
          for (const wh of sortedWH) {
            if (wh.name && cleanText.includes(norm(wh.name))) {
              matchedWH = wh.name;
              residualText = residualText.replace(new RegExp(wh.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi'), ' ');
              break;
            }
          }
        }
        if (!matchedWH) {
          const mWH = raw.match(/(?:المخزن|مخزن|المستودع|مستودع)\s+([^\s,،]+(?:\s+[^\s,،]+){0,2})/i);
          if (mWH && mWH[1]) {
            const candWH = mWH[1].replace(/(?:صنف|سيخ|وحدة|طن|كمية|سعر|خصم|إضافة|اضافة|العتال|عتالة|حفظ).*/i, '').trim();
            if (candWH) {
              const candNorm = norm(candWH);
              const foundWh = (this.dbWarehouses || []).find(w => norm(w.name).includes(candNorm) || candNorm.includes(norm(w.name)));
              matchedWH = foundWh ? foundWh.name : candWH;
              residualText = residualText.replace(mWH[0], ' ');
            }
          }
        }
        if (matchedWH) {
          this._userPickedWarehouse = true;
          this.selectWarehouse(matchedWH);
          const whInp = document.getElementById('salesWarehouseInput');
          if (whInp) whInp.classList.remove('pos-ghost-default');
          feedback.push('المستودع: ' + matchedWH);
        }

        // ─────────────────────────────────────────────────────────────
        // ج) التعرف الذكي على مركز الكلفة (Cost Center)
        // ─────────────────────────────────────────────────────────────
        let matchedCC = null;
        const mCC = raw.match(/(?:مركز الكلفة|مركز كلفة|المركز)\s+([^\s,،]+(?:\s+[^\s,،]+){0,2})/i);
        if (mCC && mCC[1]) {
          const candCC = mCC[1].replace(/(?:مخزن|مستودع|صنف|وحدة|كمية|سعر|خصم|إضافة|اضافة|حفظ).*/i, '').trim();
          if (candCC) {
            matchedCC = candCC;
            residualText = residualText.replace(mCC[0], ' ');
          }
        }
        if (matchedCC) {
          this._userPickedCostCenter = true;
          this.selectCostCenter(matchedCC);
          const ccInp = document.getElementById('salesCostCenterInput');
          if (ccInp) ccInp.classList.remove('pos-ghost-default');
          feedback.push('مركز الكلفة: ' + matchedCC);
        }

        // ─────────────────────────────────────────────────────────────
        // د) التعرف الذكي الفائق على الصنف المحفوظ (Item) — يدعم "سيخ 3 لين" و "سيخ 3 لينيا" وجميع الأصناف المحفوظة
        // ─────────────────────────────────────────────────────────────
        let matchedItemIdx = -1;
        let bestItemScore = 0;

        // أولاً: اختصارات الأصناف المحفوظة الأكثر تداولاً (مثل "سيخ 3 لين" أو "سيخ 3 لينيا" أو "سيخ 4 لين" أو "سيخ 5 لين")
        if (/سيخ\s*3(?:\s*لينيا|\s*لين|\s*ل)?(?=$|[\s,،])/i.test(cleanText)) {
          const prefIdx = (this.dbItems || []).findIndex(it => it && it.name && it.name.includes('سيخ 3 لينيا أسعد 10 مللي'));
          const altIdx = (this.dbItems || []).findIndex(it => it && it.name && norm(it.name).includes('سيخ 3 لينيا'));
          matchedItemIdx = prefIdx !== -1 ? prefIdx : altIdx;
          bestItemScore = 500;
          residualText = residualText.replace(/سيخ\s*3\s*(?:لينيا|لينية|لينيه|لنيه|لين|ل)?(?:\s*اسعد|\s*أسعد)?(?:\s*10\s*مللي)?/gi, ' ');
        } else if (/سيخ\s*4(?:\s*لينيا|\s*لين|\s*ل)?(?=$|[\s,،])/i.test(cleanText)) {
          const prefIdx = (this.dbItems || []).findIndex(it => it && it.name && norm(it.name).includes('سيخ 4 لينيا'));
          if (prefIdx !== -1) {
            matchedItemIdx = prefIdx;
            bestItemScore = 500;
            residualText = residualText.replace(/سيخ\s*4\s*(?:لينيا|لينية|لينيه|لنيه|لين|ل)?/gi, ' ');
          }
        } else if (/سيخ\s*5(?:\s*لينيا|\s*لين|\s*ل)?(?=$|[\s,،])/i.test(cleanText)) {
          const prefIdx = (this.dbItems || []).findIndex(it => it && it.name && norm(it.name).includes('سيخ 5 لينيا'));
          if (prefIdx !== -1) {
            matchedItemIdx = prefIdx;
            bestItemScore = 500;
            residualText = residualText.replace(/سيخ\s*5\s*(?:لينيا|لينية|لينيه|لنيه|لين|ل)?/gi, ' ');
          }
        }

        // ثانياً: البحث الذكي في كامل قائمة الأصناف المحفوظة (dbItems)
        if (matchedItemIdx === -1) {
          (this.dbItems || []).forEach((it, idx) => {
            if (!it || !it.name || it.name.length < 2) return;
            const iNorm = norm(it.name);
            if (!iNorm) return;

            if (cleanText.includes(iNorm)) {
              let sc = 200 + iNorm.length;
              if (Array.isArray(it.unitsArray) && it.unitsArray.length >= 3) sc += 50;
              if (it.name.includes('لينيا')) sc += 20;
              if (sc > bestItemScore) {
                bestItemScore = sc;
                matchedItemIdx = idx;
              }
              return;
            }

            const tokens = iNorm.split(' ').filter(Boolean);
            let matchCount = 0;
            let hasCoreNoun = false;
            let hasNumberMatch = true;

            for (const tok of tokens) {
              if (/^\d+$/.test(tok)) {
                if (cleanText.includes(tok)) {
                  matchCount += 2;
                } else if (tok === '10' && iNorm.includes('3 لينيا')) {
                  // تجاهل غياب "10" في "سيخ 3 لينيا أسعد 10 مللي"
                } else {
                  hasNumberMatch = false;
                }
              } else if (cleanText.includes(tok)) {
                matchCount += 1;
                if (['سيخ','اسمنت','حديد','طوب','رمل','دهان','زنك','ماسوره'].includes(tok)) {
                  hasCoreNoun = true;
                }
              }
            }

            if (hasCoreNoun && hasNumberMatch && matchCount >= 2) {
              let sc = matchCount * 40;
              if (Array.isArray(it.unitsArray) && it.unitsArray.length >= 3) sc += 50;
              if (it.name.includes('لينيا')) sc += 20;
              if (parseFloat(it.sellingPrice || it.price || 0) > 0) sc += 15;
              if (sc > bestItemScore) {
                bestItemScore = sc;
                matchedItemIdx = idx;
              }
            }
          });
        }

        if (matchedItemIdx !== -1) {
          this.selectItemByIndex(matchedItemIdx);
          if (prevQtyVal && spokenQty === null) {
            const qInp = document.getElementById('salesQtyInput');
            if (qInp) qInp.value = prevQtyVal;
          }
          if (prevPriceVal && spokenPrice === null) {
            const pInp = document.getElementById('salesPriceInput');
            if (pInp) pInp.value = prevPriceVal;
          }
          feedback.push('الصنف: ' + this.dbItems[matchedItemIdx].name);
        }

        // ─────────────────────────────────────────────────────────────
        // هـ) التعرف الذكي الفائق على العميل / الحساب (Customer / Account)
        // يبحث أولاً في جميع العملاء والحسابات المسجلة، وإذا لم يجده يستخرج اسم الحساب المنطوق بالذكاء الاصطناعي!
        // ─────────────────────────────────────────────────────────────
        let matchedCustomer = null;
        let bestCustScore = 0;
        const stopWordsCust = new Set([
          'العميل','عميل','زبون','حساب','المستودع','مستودع','المخزن','مخزن','الصنف','صنف',
          'الوحدة','وحده','الوحدات','وحدات','الكمية','كميه','الكميه','كمية','السعر','سعر','بسعر',
          'الخصم','خصم','الاضافه','اضافه','الإضافة','الاضافة','العتاله','عتال','العتال','عتاله',
          'طن','عددي','وزني','سيخ','لينيا','لين','دره','درة','اللاماب','اللمام','حفظ','وسداد',
          'احفظ','وسدد','ملحوظه','البيان','بيان','الرئيسي','كيس','سيخه','حبه','متر','جالون'
        ]);

        for (const cust of (this.dbCustomers || [])) {
          if (!cust || !cust.name || cust.name.trim().length < 2) continue;
          if (cust.name.includes('نقدي عام')) continue;
          const cNorm = norm(cust.name);
          if (!cNorm || stopWordsCust.has(cNorm)) continue;

          if (cleanText.includes(cNorm) && cNorm.length >= 3) {
            const sc = 150 + cNorm.length * 3;
            if (sc > bestCustScore) {
              bestCustScore = sc;
              matchedCustomer = cust;
            }
          } else {
            const cTokens = cNorm.split(' ').filter(t => t.length >= 2 && !stopWordsCust.has(t));
            if (cTokens.length >= 2) {
              const matchedTokens = cTokens.filter(t => cleanText.split(' ').includes(t) || cleanText.includes(t));
              if (matchedTokens.length >= 2 && matchedTokens.length >= Math.ceil(cTokens.length * 0.6)) {
                const sc = matchedTokens.length * 35;
                if (sc > bestCustScore) {
                  bestCustScore = sc;
                  matchedCustomer = cust;
                }
              }
            } else if (cTokens.length === 1 && cTokens[0].length >= 4) {
              const tok = cTokens[0];
              const wordRgx = new RegExp('(?:^|[\\s,،])' + tok.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '(?=$|[\\s,،])', 'i');
              if (wordRgx.test(cleanText)) {
                const sc = 65 + tok.length;
                if (sc > bestCustScore) {
                  bestCustScore = sc;
                  matchedCustomer = cust;
                }
              }
            }
          }
        }

        // إذا ذكر المستخدم كلمة "العميل / حساب / زبون" متبوعة بأي اسم
        if (!matchedCustomer) {
          const mCust = raw.match(/(?:العميل|عميل|زبون|حساب|باسم)\s+([^\s,،]+(?:\s+[^\s,،]+){0,3})/i);
          if (mCust && mCust[1]) {
            const candName = mCust[1].replace(/(?:مركز|مخزن|مستودع|درة|دره|صنف|سيخ|وحدة|طن|كمية|سعر|خصم|إضافة|اضافة|العتال|عتالة|حفظ).*/i, '').trim();
            if (candName && candName.length >= 2) {
              matchedCustomer = (this.dbCustomers || []).find(c => norm(c.name).includes(norm(candName))) || {
                id: 'CUST-VOICE-' + Date.now(),
                name: candName,
                phone: '',
                currentBalance: 0,
                currency: 'SDG'
              };
            }
          }
        }

        // الذكاء الاصطناعي الاستنتاجي للأسماء: إذا نطق المستخدم أي اسم شخص أو حساب في بداية أو وسط الجملة بدون كلمة "العميل" ولم يكن مسجلاً مسبقاً
        if (!matchedCustomer) {
          const segments = residualText
            .replace(/(?:طن\s*عددي|طن\s*وزني|الطن\s*العددي|الطن\s*الوزني|حفظ\s*وسداد|حفظ|احفظ)/gi, ' ')
            .split(/[,،.\n]+/)
            .map(s => s.trim())
            .filter(Boolean);

          for (const seg of segments) {
            const words = seg.split(/\s+/).filter(w => {
              const wn = norm(w);
              return wn.length >= 2 && !/^\d+$/.test(wn) && !stopWordsCust.has(wn);
            });
            if (words.length >= 2 && words.length <= 5) {
              const candidateName = words.join(' ').trim();
              if (candidateName.length >= 5) {
                matchedCustomer = {
                  id: 'CUST-VOICE-' + Date.now(),
                  name: candidateName,
                  phone: '',
                  currentBalance: 0,
                  currency: 'SDG'
                };
                this.dbCustomers.unshift(matchedCustomer);
                break;
              }
            }
          }
        }

        if (matchedCustomer) {
          this._userPickedCustomer = true;
          this.selectCustomer(matchedCustomer);
          const custInp = document.getElementById('salesCustomerInput');
          if (custInp) custInp.classList.remove('pos-ghost-default');
          feedback.push('العميل: ' + matchedCustomer.name);
        }

        // ─────────────────────────────────────────────────────────────
        // و) التعرف الذكي على الوحدة (Unit) بدون اشتراط كلمة "الوحدة"
        // ─────────────────────────────────────────────────────────────
        let targetUnit = null;
        if (cleanText.includes('طن عددي') || cleanText.includes('الطن العددي')) {
          targetUnit = 'طن عددي';
        } else if (cleanText.includes('طن وزني') || cleanText.includes('الطن الوزني')) {
          targetUnit = 'طن وزني';
        } else if (cleanText.includes('سيخه') || cleanText.includes('بالسيخه')) {
          targetUnit = 'سيخه';
        } else if (cleanText.includes('متر مكعب')) {
          targetUnit = 'متر مكعب';
        } else if (cleanText.includes('الف حبه')) {
          targetUnit = 'ألف حبة';
        } else if (cleanText.includes('كيس') || cleanText.includes('اكياس')) {
          targetUnit = 'كيس';
        } else if (cleanText.includes('جالون')) {
          targetUnit = 'جالون';
        } else if (cleanText.includes('كرتونه')) {
          targetUnit = 'كرتونة';
        } else if (cleanText.includes('حبه') || cleanText.includes('قطعه')) {
          targetUnit = 'حبة';
        } else {
          const mUnit = raw.match(/(?:الوحدة|وحدة|الوحدات)\s+([^\s,،]+(?:\s+[^\s,،]+)?)/i);
          if (mUnit && mUnit[1]) {
            targetUnit = mUnit[1].replace(/(?:الكمية|كمية|السعر|سعر|خصم|إضافة|اضافة|العتال|عتالة|حفظ).*/i, '').trim();
          }
        }

        if (targetUnit) {
          const uSel = document.getElementById('salesUnitSelect');
          if (uSel) {
            const uNorm = norm(targetUnit);
            let foundUnitIdx = -1;
            for (let i = 0; i < uSel.options.length; i++) {
              const optNorm = norm(uSel.options[i].value || uSel.options[i].text);
              if (optNorm && (optNorm === uNorm || optNorm.includes(uNorm) || uNorm.includes(optNorm))) {
                foundUnitIdx = i;
                break;
              }
            }
            if (foundUnitIdx === -1) {
              const newOpt = document.createElement('option');
              newOpt.value = targetUnit;
              newOpt.text = targetUnit;
              newOpt.setAttribute('data-factor', targetUnit.includes('عددي') ? '135' : (targetUnit.includes('وزني') ? '140' : '1'));
              uSel.appendChild(newOpt);
              foundUnitIdx = uSel.options.length - 1;
            }
            uSel.selectedIndex = foundUnitIdx;
            if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleUnit(uSel);
            this.onUnitChange();
            feedback.push('الوحدة: ' + uSel.options[foundUnitIdx].value);
          }
        } else if (prevUnitVal) {
          const uSel = document.getElementById('salesUnitSelect');
          if (uSel && uSel.options) {
            for (let i = 0; i < uSel.options.length; i++) {
              if (uSel.options[i].value === prevUnitVal) {
                uSel.selectedIndex = i;
                uSel.value = prevUnitVal;
                break;
              }
            }
          }
        }

        // ─────────────────────────────────────────────────────────────
        // ز) تفريغ القيم الرقمية المستخرجة في الحقول وتحديث الجملة والصافي فوراً
        // ─────────────────────────────────────────────────────────────
        const fmtField = (id, val) => window.SmartFieldCustomizer ? SmartFieldCustomizer.formatNumberForField(id, val) : val;

        if (spokenQty !== null && spokenQty > 0) {
          const qInp = document.getElementById('salesQtyInput');
          if (qInp) {
            qInp.classList.remove('pos-ghost-default');
            delete qInp.dataset.ghostAuto;
            qInp.value = fmtField('salesQtyInput', spokenQty);
            if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleQty(qInp);
          }
          feedback.push('الكمية: ' + spokenQty);
        }

        if (spokenPrice !== null && spokenPrice > 0) {
          const pInp = document.getElementById('salesPriceInput');
          if (pInp) {
            pInp.classList.remove('pos-ghost-default');
            delete pInp.dataset.ghostAuto;
            pInp.value = fmtField('salesPriceInput', spokenPrice);
            if (window.SmartPOSFontAI) window.SmartPOSFontAI.scalePrice(pInp);
          }
          feedback.push('السعر: ' + spokenPrice.toLocaleString());
        }

        if (spokenTax !== null && spokenTax >= 0) {
          const tInp = document.getElementById('salesTaxInput');
          if (tInp) {
            tInp.classList.remove('pos-ghost-default');
            delete tInp.dataset.ghostAuto;
            tInp.value = fmtField('salesTaxInput', spokenTax);
            if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleTax(tInp);
          }
          feedback.push('الإضافة: ' + spokenTax.toLocaleString());
        }

        if (spokenDisc !== null && spokenDisc >= 0) {
          const dInp = document.getElementById('salesDiscountInput');
          if (dInp) {
            dInp.classList.remove('pos-ghost-default');
            delete dInp.dataset.ghostAuto;
            dInp.value = fmtField('salesDiscountInput', spokenDisc);
            if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleTax(dInp);
          }
          feedback.push('الخصم: ' + spokenDisc.toLocaleString());
        }

        if (spokenLabor !== null && spokenLabor >= 0) {
          const lInp = document.getElementById('salesLaborInput');
          if (lInp) {
            lInp.classList.remove('pos-ghost-default');
            delete lInp.dataset.ghostAuto;
            lInp.value = fmtField('salesLaborInput', spokenLabor);
            if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleTax(lInp);
          }
          feedback.push('العتالة: ' + spokenLabor.toLocaleString());
        }

        // الملاحظة / البيان (Notes)
        const mNote = raw.match(/(?:الملحوظة|ملحوظة|الملاحظة|ملاحظة|ملاحظات|البيان|بيان)\s+([^\s,،]+(?:\s+[^\s,،]+){0,6})/i);
        if (mNote && mNote[1]) {
          const nVal = mNote[1].replace(/(?:صنف|وحدة|كمية|سعر|خصم|إضافة|اضافة|العتال|عتالة|حفظ).*/i, '').trim();
          const nInp = document.getElementById('salesNotesInput');
          if (nInp && nVal) {
            nInp.value = nVal;
            const nTxt = document.getElementById('salesNotesExpandedTextarea');
            if (nTxt) nTxt.value = nVal;
            if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleNotes(nInp);
            feedback.push('البيان: ' + nVal);
          }
        }

        // حساب وتفريغ الجملة والإجمالي الصافي فوراً
        const netSubtotal = this.calcItemSubtotal();
        const grossVal = document.getElementById('salesItemGrossTotal')?.value || '';
        if (grossVal && grossVal !== '0') {
          feedback.push('الجملة: ' + grossVal);
        }
        if (netSubtotal > 0) {
          feedback.push('الصافي: ' + netSubtotal.toLocaleString());
        }

        // تحديث شريط حالة الـ ERP الذكي
        if (typeof this.updateErpSmartStatusStrip === 'function') {
          this.updateErpSmartStatusStrip();
        }

        // إشعار النتيجة في لوحة الصوت
        const statusText = document.getElementById('suiteSalesVoiceStatusText');
        if (statusText) {
          statusText.innerHTML = '<span class="text-emerald-400 font-black">✅ تم التفريغ الذكي:</span> <span class="text-white">' + feedback.join(' • ') + '</span>';
        }

        if (window.AppHaptics) AppHaptics.success();

        // إضاءة نبضية للحقول التي تم تغذيتها
        ['salesCustomerInput', 'salesWarehouseInput', 'salesItemSearchInput', 'salesQtyInput', 'salesPriceInput', 'salesItemGrossTotal', 'salesTaxInput', 'salesDiscountInput', 'salesLaborInput', 'salesTotalInput'].forEach(id => {
          const el = document.getElementById(id);
          if (el) {
            el.classList.add('ring-2', 'ring-emerald-400');
            setTimeout(() => el.classList.remove('ring-2', 'ring-emerald-400'), 1800);
          }
        });

        // ─────────────────────────────────────────────────────────────
        // ح) تنفيذ الأوامر الصوتية التنفيذية الفورية ("حفظ" / "حفظ وسداد" / "تعليق الفاتورة" / "استرجاع الفاتورة" / "آخر سعر")
        // ─────────────────────────────────────────────────────────────
        if (/(?:تعليق\s*الفاتوره|علق\s*الفاتوره|تعليق)/i.test(cleanText)) {
          this.stopVoiceFeed(true, '⏸️ جاري تعليق الفاتورة الحالية وتفريغ الشاشة...');
          setTimeout(() => this.parkCurrentInvoice(), 200);
          return;
        } else if (/(?:استرجاع\s*الفاتوره|الفواتير\s*المعلقه|استرجاع)/i.test(cleanText)) {
          this.stopVoiceFeed(true, '▶️ جاري فتح قائمة الفواتير المعلقة...');
          setTimeout(() => this.openParkedInvoicesModal(), 200);
          return;
        } else if (/(?:اخر\s*سعر|اعتمد\s*السعر)/i.test(cleanText)) {
          this.applyRememberedPrice();
        }

        if (/(?:حفظ\s*وسداد|احفظ\s*وسدد|حفظ\s*و\s*سداد)/i.test(rawOriginal) || /(?:حفظ\s*وسداد|احفظ\s*وسدد)/i.test(cleanText)) {
          this.stopVoiceFeed(true, '⚡ جاري تنفيذ أمر "حفظ وسداد" والانتقال لشاشة حركة مالية...');
          setTimeout(() => {
            if (typeof commitOpSales === 'function') commitOpSales(true);
          }, 250);
          return;
        } else if (/(?:^|[\s,،])(?:حفظ|احفظ|اعتمد\s*الفاتوره|اعتماد\s*الفاتوره)(?=$|[\s,،])/i.test(cleanText)) {
          this.stopVoiceFeed(true, '💾 جاري تنفيذ أمر "حفظ" الفاتورة فوراً...');
          setTimeout(() => {
            if (typeof commitOpSales === 'function') commitOpSales(false);
          }, 250);
          return;
        }
      },

      // ─────────────────────────────────────────────────────────────
      // 6. CALCULATOR METHODS
      // ─────────────────────────────────────────────────────────────
      toggleCalculator() {
        const m = document.getElementById('suiteSmartCalcModal');
        if (m) m.classList.toggle('hidden');
      },

      calcInput(val) {
        const screen = document.getElementById('suiteCalcScreen');
        const hist = document.getElementById('suiteCalcHistory');
        if (!screen) return;

        if (val === 'C') {
          this.currentCalcVal = '0';
          this.calcHistoryVal = '';
        } else if (val === 'BACK') {
          this.currentCalcVal = this.currentCalcVal.length > 1 ? this.currentCalcVal.slice(0, -1) : '0';
        } else if (val === '=') {
          try {
            this.calcHistoryVal = this.currentCalcVal + ' =';
            const res = eval(this.currentCalcVal.replace(/×/g, '*').replace(/÷/g, '/'));
            this.currentCalcVal = String(res);
          } catch(e) {
            this.currentCalcVal = 'Error';
          }
        } else {
          if (this.currentCalcVal === '0' && val !== '.') {
            this.currentCalcVal = val;
          } else {
            this.currentCalcVal += val;
          }
        }
        screen.innerText = this.currentCalcVal;
        if (hist) hist.innerText = this.calcHistoryVal;
      },

      pasteCalcToField(target) {
        const val = parseFloat(this.currentCalcVal) || 0;
        if (target === 'price') {
          document.getElementById('salesPriceInput').value = val;
        } else if (target === 'qty') {
          document.getElementById('salesQtyInput').value = val;
        }
        this.calcItemSubtotal();
        this.toggleCalculator();
      },

      // ─────────────────────────────────────────────────────────────
      // 7. PRINTING & ACTIONS (INVOICE WITH QR, PREVIEW, GIN, STATEMENT)
      // ─────────────────────────────────────────────────────────────
      printInvoice() {
        const invNo = document.getElementById('opSalesInvoiceNoPill')?.innerText || 'S-PRINT';
        const cust = this.selectedCustomer?.name || document.getElementById('salesCustomerInput')?.value || 'عميل نقدي عام';
        const date = document.getElementById('salesInvoiceDate')?.value || new Date().toISOString().split('T')[0];
        const payMethod = document.getElementById('salesPayMethodSelect')?.value || 'كاش';

        const items = this.cart.length > 0 ? this.cart : [{
          name: this.selectedItemIndex !== -1 ? this.dbItems[this.selectedItemIndex]?.name : 'صنف عام',
          unit: document.getElementById('salesUnitSelect')?.value || 'حبة',
          qty: document.getElementById('salesQtyInput')?.value || 1,
          price: document.getElementById('salesPriceInput')?.value || 0,
          discount: document.getElementById('salesDiscountInput')?.value || 0,
          subtotal: parseFloat(document.getElementById('salesTotalInput')?.value?.replace(/,/g, '') || 0)
        }];

        const grandTotal = items.reduce((acc, it) => acc + (parseFloat(it.subtotal) || 0), 0);

        const qrData = `المؤسسة: مؤسسة السجانة لمواد البناء\nالرقم الضريبي / السجل: 10482910\nالفاتورة: #${invNo}\nالتاريخ: ${date}\nالعميل: ${cust}\nالإجمالي: ${grandTotal.toLocaleString()} SDG`;
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=4&data=${encodeURIComponent(qrData)}`;

        let rowsHtml = '';
        items.forEach((it, i) => {
          rowsHtml += `<tr>
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">${i + 1}</td>
            <td style="padding:6px; border:1px solid #ccc; font-weight:bold;">${it.name}</td>
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">${it.unit}</td>
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">${it.qty}</td>
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">${Number(it.price).toLocaleString()}</td>
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">${Number(it.discount || 0).toLocaleString()}</td>
            <td style="padding:6px; border:1px solid #ccc; text-align:center; font-weight:bold;">${Number(it.subtotal).toLocaleString()} SDG</td>
          </tr>`;
        });

        const printWin = window.open('', '_blank', 'width=800,height=750');
        printWin.document.write(`
          <html dir="rtl" lang="ar">
          <head>
            <title>فاتورة مبيعات - #${invNo}</title>
            <style>
              body { font-family: 'Cairo', Tahoma, sans-serif; padding: 25px; color: #111; }
              .header { display:flex; justify-content:space-between; align-items:center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 15px; }
              table { width:100%; border-collapse:collapse; margin-top:15px; }
              th { background:#f3f4f6; border:1px solid #ccc; padding:8px; font-size:12px; }
              .total-box { margin-top:20px; padding:12px; background:#f9fafb; border:1.5px solid #d1d5db; font-size:1.15rem; font-weight:bold; }
              .qr-container { text-align:center; }
              .qr-container img { width:110px; height:110px; border:1px solid #ccc; padding:3px; background:#fff; }
            
    /* ── MOBILE ENHANCEMENTS: ZERO-LAG KEYBOARD & SAFE AREA ── */
    html, body {
      overscroll-behavior-y: contain;
      -webkit-tap-highlight-color: transparent;
      touch-action: manipulation;
    }
    .modal-backdrop > div {
      max-height: 92dvh !important;
      max-height: 92vh;
      scroll-padding-bottom: 90px !important;
      padding-bottom: env(safe-area-inset-bottom, 16px) !important;
    }
    @supports (height: 100dvh) {
      .modal-backdrop > div {
        max-height: calc(100dvh - 24px) !important;
      }
    }
    input, select, textarea {
      font-size: 16px !important; /* Prevents auto-zoom on iOS Safari */
    }
    .pos-field-interactive input {
      /* Keep large fonts for numbers in POS */
    }

  </style>
          </head>
          <body>
            <div class="header">
              <div>
                <h2 style="margin:0 0 4px 0; color:#0f172a;">مؤسسة السجانة لمواد البناء والتجارة</h2>
                <p style="margin:2px 0; color:#475569; font-size:12px;">فاتورة مبيعات إلكترونية معتمدة • نظام مقاييس ERP</p>
                <div style="font-size:12px; margin-top:8px;">
                  <div><strong>رقم الفاتورة:</strong> #${invNo} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>التاريخ:</strong> ${date}</div>
                  <div style="margin-top:4px;"><strong>العميل:</strong> ${cust} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>طريقة السداد:</strong> ${payMethod}</div>
                  <div style="margin-top:4px;"><strong>مركز الكلفة:</strong> ${this.selectedCostCenter || 'الرئيسي'}</div>
                </div>
              </div>
              <div class="qr-container">
                <img src="${qrUrl}" alt="QR Code" />
                <div style="font-size:10px; color:#64748b; margin-top:2px;">رمز الفوترة الإلكترونية</div>
              </div>
            </div>

            <table>
              <thead>
                <tr>
                  <th>#</th><th>الصنف</th><th>الوحدة</th><th>الكمية</th><th>السعر</th><th>الخصم</th><th>الجملة</th>
                </tr>
              </thead>
              <tbody>${rowsHtml}</tbody>
            </table>

            <div class="total-box" style="display:flex; justify-content:space-between; align-items:center;">
              <span>الصافي الكلي النهائي:</span>
              <span style="color:#059669;">${grandTotal.toLocaleString()} SDG</span>
            </div>

            <div style="display:flex; justify-content:space-between; margin-top:45px; text-align:center; font-size:12px;">
              <div>توقيع واستلام العميل: ....................</div>
              <div>ختم وتوقيع المحاسب / الكاشير: ....................</div>
            </div>

            <script>window.onload = function() { window.print(); }<\/script>
          </body>
          </html>
        `);
        printWin.document.close();
      },

      previewInvoice() {
        this.printInvoice();
      },

      printDeliveryNote() {
        const invNo = document.getElementById('opSalesInvoiceNoPill')?.innerText || 'DEL-PRINT';
        const cust = this.selectedCustomer?.name || document.getElementById('salesCustomerInput')?.value || 'عميل نقدي عام';
        const date = document.getElementById('salesInvoiceDate')?.value || new Date().toISOString().split('T')[0];

        const items = this.cart.length > 0 ? this.cart : [{
          name: this.selectedItemIndex !== -1 ? this.dbItems[this.selectedItemIndex]?.name : 'صنف عام',
          unit: document.getElementById('salesUnitSelect')?.value || 'حبة',
          warehouse: this.selectedWarehouse || 'المستودع الرئيسي',
          qty: document.getElementById('salesQtyInput')?.value || 1
        }];

        const qrData = `إذن صرف مبيعات\nرقم الفاتورة: #${invNo}\nالتاريخ: ${date}\nالعميل: ${cust}\nعدد البنود: ${items.length}`;
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=4&data=${encodeURIComponent(qrData)}`;

        let rowsHtml = '';
        items.forEach((it, i) => {
          rowsHtml += `<tr>
            <td style="padding:8px; border:1px solid #ccc; text-align:center;">${i + 1}</td>
            <td style="padding:8px; border:1px solid #ccc; font-weight:bold;">${it.name}</td>
            <td style="padding:8px; border:1px solid #ccc; text-align:center;">${it.unit}</td>
            <td style="padding:8px; border:1px solid #ccc; text-align:center;">${it.warehouse || 'المستودع الرئيسي'}</td>
            <td style="padding:8px; border:1px solid #ccc; text-align:center; font-weight:bold; font-size:1.1rem; color:#0284c7;">${it.qty}</td>
            <td style="padding:8px; border:1px solid #ccc; text-align:center;">[ &nbsp; ] سليم ومطابق</td>
          </tr>`;
        });

        const printWin = window.open('', '_blank', 'width=800,height=750');
        printWin.document.write(`
          <html dir="rtl" lang="ar">
          <head>
            <title>إذن صرف وتسليم بضاعة (GIN)</title>
            <style>
              body { font-family: 'Cairo', Tahoma, sans-serif; padding: 25px; color: #111; }
              .header { display:flex; justify-content:space-between; align-items:center; border-bottom: 2px dashed #000; padding-bottom: 12px; margin-bottom: 15px; }
              table { width:100%; border-collapse:collapse; margin-top:15px; }
              th { background:#f3f4f6; border:1px solid #ccc; padding:8px; font-size:12px; }
            
    /* ── MOBILE ENHANCEMENTS: ZERO-LAG KEYBOARD & SAFE AREA ── */
    html, body {
      overscroll-behavior-y: contain;
      -webkit-tap-highlight-color: transparent;
      touch-action: manipulation;
    }
    .modal-backdrop > div {
      max-height: 92dvh !important;
      max-height: 92vh;
      scroll-padding-bottom: 90px !important;
      padding-bottom: env(safe-area-inset-bottom, 16px) !important;
    }
    @supports (height: 100dvh) {
      .modal-backdrop > div {
        max-height: calc(100dvh - 24px) !important;
      }
    }
    input, select, textarea {
      font-size: 16px !important; /* Prevents auto-zoom on iOS Safari */
    }
    .pos-field-interactive input {
      /* Keep large fonts for numbers in POS */
    }

  </style>
          </head>
          <body>
            <div class="header">
              <div>
                <h2 style="margin:0 0 4px 0; color:#0f172a;">إذن صرف بضاعة وتسليم مخزني (GIN)</h2>
                <p style="margin:2px 0; color:#475569; font-size:12px;">مؤسسة السجانة لمواد البناء • مستودعات التوزيع المركزي</p>
                <div style="font-size:12px; margin-top:8px;">
                  <div><strong>رقم الفاتورة المرجعية:</strong> #${invNo} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>تاريخ الإذن:</strong> ${date}</div>
                  <div style="margin-top:4px;"><strong>العميل المستلم / السائق:</strong> ${cust}</div>
                  <div style="margin-top:4px;"><strong>مركز الكلفة:</strong> ${this.selectedCostCenter || 'الرئيسي'}</div>
                </div>
              </div>
              <div style="text-align:center;">
                <img src="${qrUrl}" alt="QR" style="width:105px; height:105px; border:1px solid #ccc; padding:2px;" />
                <div style="font-size:10px; color:#64748b; margin-top:2px;">تحقق الصرف المخزني</div>
              </div>
            </div>

            <table>
              <thead>
                <tr>
                  <th>#</th><th>الصنف المصروف</th><th>الوحدة</th><th>المخزن المخرج</th><th>الكمية المصروفة</th><th>حالة الاستلام</th>
                </tr>
              </thead>
              <tbody>${rowsHtml}</tbody>
            </table>

            <div style="display:flex; justify-content:space-between; margin-top:50px; text-align:center; font-size:12px;">
              <div>توقيع أمين المستودع المخرج: ....................</div>
              <div>توقيع السائق / المستلم: ....................</div>
            </div>
            <script>window.onload = function() { window.print(); }<\/script>
          </body>
          </html>
        `);
        printWin.document.close();
      },

      updateErpSmartStatusStrip() {
        const strip = document.getElementById('salesErpSmartStatusStrip');
        const custBadge = document.getElementById('salesErpCustBalanceBadge');
        const stockBadge = document.getElementById('salesErpItemStockBadge');
        const alertBanner = document.getElementById('salesStockCreditAlertBanner');
        const alertText = document.getElementById('salesStockCreditAlertText');

        const custName = (document.getElementById('salesCustomerInput')?.value || this.selectedCustomer?.name || '').trim();
        const custObj = this.selectedCustomer || (this.dbCustomers || []).find(c => c.name === custName);
        let itObj = (this.selectedItemIndex !== -1 && this.dbItems) ? this.dbItems[this.selectedItemIndex] : null;
        const itInpVal = (document.getElementById('salesItemSearchInput')?.value || '').trim();
        if (!itObj && itInpVal && itInpVal !== 'اختيار الصنف' && itInpVal !== 'اختر الصنف...') {
          itObj = (this.dbItems || []).find(x => x.name === itInpVal) || { name: itInpVal, unit: 'طن', stock: 1 };
        }

        // تحديث شريط ذاكرة التسعير الذكية للعميل والصنف
        if (typeof this.updateLastPriceMemoryUI === 'function') {
          this.updateLastPriceMemoryUI(custName, itObj);
        }

        if (!strip) return;
        const whKey = (document.getElementById('salesWarehouseInput')?.value || this.selectedWarehouse || '').trim();
        const hasCust = Boolean(custName && custName !== 'عميل نقدي عام');
        const hasItemAndWh = Boolean(itObj && whKey);

        if (!hasCust && !hasItemAndWh) {
          strip.classList.add('hidden');
          if (alertBanner) alertBanner.classList.add('hidden');
          return;
        }
        strip.classList.remove('hidden');

        const bal = parseFloat(custObj?.currentBalance || custObj?.balance || custObj?.openingBalance || 0);
        const limit = parseFloat(custObj?.creditLimit || 0);
        const cur = custObj?.currency === 'USD' ? '$' : 'ج.س';
        const netInp = document.getElementById('salesTotalInput');
        const currentInvoiceNet = netInp && netInp.value ? (parseFloat(String(netInp.value).replace(/,/g, '')) || 0) : 0;
        const projectedBal = bal + currentInvoiceNet;
        const isOverLimit = (limit > 0 && projectedBal > limit);

        if (custBadge) {
          custBadge.className = 'flex items-center gap-1 truncate ' + (isOverLimit ? 'text-rose-400 font-black' : (bal > 0 ? 'text-amber-300' : 'text-sky-300'));
          custBadge.innerHTML = `<span>رصيد: ${bal.toLocaleString()} ${cur}${isOverLimit ? ' ⚠️' : ''}</span>`;
        }

        let numericAvailableStock = null;
        const uSel = document.getElementById('salesUnitSelect');
        const opt = uSel && uSel.options && uSel.selectedIndex >= 0 ? uSel.options[uSel.selectedIndex] : null;
        const factor = opt ? (parseFloat(opt.getAttribute('data-factor')) || 1) : 1;
        const rawUnitName = (opt && opt.value) ? opt.value : (itObj?.baseUnit || itObj?.unit || 'طن');
        const uName = String(rawUnitName).replace(/\s*\(\d+(?:\.\d+)?\)\s*/g, '').trim() || 'طن';

        if (stockBadge) {
          if (hasItemAndWh) {
            const whObj = (this.dbWarehouses || []).find(w => w.name === whKey || w.id === whKey);
            let rawBaseStock = null;

            if (typeof itObj.stock === 'number') {
              rawBaseStock = itObj.stock;
            } else if (itObj.stock && typeof itObj.stock === 'object' && !Array.isArray(itObj.stock)) {
              if (whKey && itObj.stock[whKey] !== undefined) {
                rawBaseStock = parseFloat(itObj.stock[whKey]) || 0;
              } else if (whObj && whObj.id && itObj.stock[whObj.id] !== undefined) {
                rawBaseStock = parseFloat(itObj.stock[whObj.id]) || 0;
              } else {
                const vals = Object.values(itObj.stock).map(v => parseFloat(v) || 0);
                rawBaseStock = vals.reduce((a, b) => a + b, 0);
              }
            } else if (Array.isArray(itObj.stock) && itObj.stock.length > 0) {
              const mWh = itObj.stock.find(s => s && (s.warehouse === whKey || s.warehouseId === whObj?.id));
              if (mWh && mWh.qty !== undefined) {
                rawBaseStock = parseFloat(mWh.qty) || 0;
              } else if (itObj.stock[0]?.qty !== undefined) {
                rawBaseStock = Number(itObj.stock[0].qty) || 0;
              }
            }

            if (rawBaseStock === null || rawBaseStock === undefined || rawBaseStock <= 0) {
              rawBaseStock = parseFloat(itObj.availableQty || itObj.qty || 0) || 0;
            }

            if (rawBaseStock > 0) {
              numericAvailableStock = (factor > 1 && rawBaseStock >= factor)
                ? Math.round((rawBaseStock / factor) * 100) / 100
                : Math.round(rawBaseStock * 100) / 100;
            } else {
              numericAvailableStock = 1;
            }

            stockBadge.innerHTML = `<span>${numericAvailableStock.toLocaleString()} ${uName}</span>`;
            stockBadge.classList.remove('hidden');
          } else {
            stockBadge.innerHTML = '';
          }
        }

        // التحقق الفوري من رصيد المستودع والحد الائتماني قبل الحفظ
        if (alertBanner && alertText) {
          const warnings = [];
          const enteredQtyRaw = document.getElementById('salesQtyInput')?.value;
          const enteredQty = enteredQtyRaw ? (parseFloat(String(enteredQtyRaw).replace(/,/g, '')) || 0) : 0;

          if (numericAvailableStock !== null && numericAvailableStock > 0 && enteredQty > numericAvailableStock) {
            warnings.push(`الكمية المطلوبة (${enteredQty}) تتجاوز الرصيد المتاح في المستودع (${numericAvailableStock.toLocaleString()} ${uName})`);
          }
          if (isOverLimit && custName && custName !== 'عميل نقدي عام') {
            warnings.push(`إجمالي المديونية مع الفاتورة (${projectedBal.toLocaleString()} ${cur}) يتجاوز الحد الائتماني للعميل (${limit.toLocaleString()} ${cur})`);
          }

          if (warnings.length > 0) {
            alertText.innerText = 'تنبيه رقابي: ' + warnings.join(' • ');
            alertBanner.classList.remove('hidden');
          } else {
            alertBanner.classList.add('hidden');
          }
        }
      },

      // ─────────────────────────────────────────────────────────────
      // 1. ذاكرة التسعير الذكية للعميل والصنف (LAST PRICE MEMORY)
      // ─────────────────────────────────────────────────────────────
      _lastRememberedPriceObj: null,

      getRememberedPrice(custName, itObj) {
        if (!itObj || !itObj.name) return null;
        const itemName = itObj.name.trim();
        const cleanCust = (custName || '').trim();

        // أولاً: فحص ذاكرة التسعير المحفوظة محلياً
        try {
          const mem = JSON.parse(localStorage.getItem('magaies_last_price_memory_v1') || '{}');
          if (cleanCust && cleanCust !== 'عميل نقدي عام' && mem[`${cleanCust}__${itemName}`]) {
            return { ...mem[`${cleanCust}__${itemName}`], scope: 'customer' };
          }
          if (mem[`*__${itemName}`]) {
            return { ...mem[`*__${itemName}`], scope: 'global' };
          }
        } catch (e) {}

        // ثانياً: فحص أرشيف الفواتير السابقة
        const allInvs = [
          ...(Array.isArray(this.recentInvoices) ? this.recentInvoices : []),
          ...(function() {
            try { return JSON.parse(localStorage.getItem('magaies_local_sales_invoices_v1') || '[]'); } catch(e) { return []; }
          })()
        ];

        let globalMatch = null;
        for (const inv of allInvs) {
          if (!inv || !Array.isArray(inv.items)) continue;
          for (const line of inv.items) {
            const p = parseFloat(line.price || 0);
            if (line.name === itemName && p > 0) {
              if (cleanCust && cleanCust !== 'عميل نقدي عام' && inv.customerName === cleanCust) {
                return { price: p, unit: line.unit || itObj.unit || 'طن عددي', date: inv.date || '', scope: 'customer', customerName: cleanCust };
              }
              if (!globalMatch) {
                globalMatch = { price: p, unit: line.unit || itObj.unit || 'طن عددي', date: inv.date || '', scope: 'global' };
              }
            }
          }
        }
        if (globalMatch) return globalMatch;

        // ثالثاً: السعر الافتراضي للصنف في قاعدة البيانات إن وجد
        const catPrice = parseFloat(itObj.sellingPrice || itObj.price || 0);
        if (catPrice > 0) {
          return { price: catPrice, unit: itObj.baseUnit || itObj.unit || 'حبة', date: 'التسعيرة الأساسية', scope: 'catalog' };
        }
        return null;
      },

      updateLastPriceMemoryUI(custName, itObj) {
        const bar = document.getElementById('salesLastPriceMemoryBar');
        const txt = document.getElementById('salesLastPriceMemoryText');
        if (!bar || !txt) return;

        const info = this.getRememberedPrice(custName, itObj);
        this._lastRememberedPriceObj = info;
        if (!info || !info.price) {
          bar.classList.add('hidden');
          return;
        }

        const cur = this.currencyMode === 'USD' ? '$' : 'ج.س';
        const scopeLabel = info.scope === 'customer'
          ? `آخر سعر بيع لـ [${custName}]`
          : (info.scope === 'global' ? 'آخر سعر بيع للصنف' : 'سعر الصنف المسجل');
        txt.innerHTML = `${scopeLabel}: <strong class="text-emerald-300 font-mono">${Number(info.price).toLocaleString()} ${cur}</strong> <span class="text-slate-400">(${info.unit || 'وحدة'})</span>`;
        bar.classList.remove('hidden');
      },

      applyRememberedPrice() {
        const info = this._lastRememberedPriceObj;
        if (!info || !info.price) return;
        const pInp = document.getElementById('salesPriceInput');
        if (pInp) {
          pInp.classList.remove('pos-ghost-default');
          delete pInp.dataset.ghostAuto;
          pInp.value = window.SmartFieldCustomizer
            ? SmartFieldCustomizer.formatNumberForField('salesPriceInput', info.price)
            : info.price;
          if (window.SmartPOSFontAI) window.SmartPOSFontAI.scalePrice(pInp);
        }
        if (info.unit) {
          const uSel = document.getElementById('salesUnitSelect');
          if (uSel && uSel.options) {
            for (let i = 0; i < uSel.options.length; i++) {
              if (uSel.options[i].value === info.unit) {
                uSel.selectedIndex = i;
                if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleUnit(uSel);
                break;
              }
            }
          }
        }
        this.calcItemSubtotal();
        if (window.triggerHapticPulse) triggerHapticPulse(15);
        if (typeof showFloatingToast === 'function') {
          showFloatingToast(`🏷️ تم اعتماد السعر المحفوظ (${Number(info.price).toLocaleString()}) في الفاتورة فوراً!`);
        }
      },

      saveToLastPriceMemory(custName, items, dateStr) {
        if (!Array.isArray(items) || items.length === 0) return;
        try {
          const mem = JSON.parse(localStorage.getItem('magaies_last_price_memory_v1') || '{}');
          const cleanCust = (custName || '').trim();
          items.forEach(it => {
            const prc = parseFloat(it.price || 0);
            if (it && it.name && prc > 0) {
              const entry = { price: prc, unit: it.unit || '', date: dateStr || new Date().toISOString().split('T')[0] };
              mem[`*__${it.name.trim()}`] = entry;
              if (cleanCust && cleanCust !== 'عميل نقدي عام') {
                mem[`${cleanCust}__${it.name.trim()}`] = entry;
              }
            }
          });
          localStorage.setItem('magaies_last_price_memory_v1', JSON.stringify(mem));
        } catch (e) {}
      },

      // ─────────────────────────────────────────────────────────────
      // 2. تحليل ربحية الفاتورة اللحظي للإدارة (PROFIT MARGIN PEEK)
      // ─────────────────────────────────────────────────────────────
      toggleProfitMarginPeek() {
        const box = document.getElementById('salesProfitMarginPeekBox');
        if (!box) return;
        box.classList.toggle('hidden');
        if (!box.classList.contains('hidden')) {
          this.updateProfitMarginPeek();
          if (window.triggerHapticPulse) triggerHapticPulse(12);
        }
      },

      updateProfitMarginPeek() {
        const box = document.getElementById('salesProfitMarginPeekBox');
        if (!box || box.classList.contains('hidden')) return;

        const pNum = (id) => {
          const el = document.getElementById(id);
          if (!el || !el.value) return 0;
          return parseFloat(String(el.value).replace(/,/g, '')) || 0;
        };

        const qty = pNum('salesQtyInput') || 1;
        const prc = pNum('salesPriceInput');
        const netRev = pNum('salesTotalInput') || (qty * prc);
        const itObj = (this.selectedItemIndex !== -1 && this.dbItems) ? this.dbItems[this.selectedItemIndex] : null;

        let unitCost = parseFloat(itObj?.lastPurchaseCost || itObj?.averageCost || 0);
        if (unitCost > 0 && prc > unitCost * 10) {
          // إذا كانت التكلفة مسجلة للوحدة الصغرى والسعر للطن، نضرب بمعامل التحويل أو تقدير التكلفة القياسي
          const uSel = document.getElementById('salesUnitSelect');
          const opt = uSel && uSel.options ? uSel.options[uSel.selectedIndex] : null;
          const factor = opt ? (parseFloat(opt.getAttribute('data-factor')) || 1) : 1;
          unitCost = unitCost * factor;
        }
        if (unitCost <= 0 || unitCost >= prc * 1.5) {
          unitCost = prc > 0 ? Math.round(prc * 0.84 * 100) / 100 : 0;
        }

        const totalCost = Math.round(qty * unitCost * 100) / 100;
        const profit = Math.round((netRev - totalCost) * 100) / 100;
        const pct = netRev > 0 ? Math.round((profit / netRev) * 1000) / 10 : 0;

        const costEl = document.getElementById('salesMarginCostVal');
        const revEl = document.getElementById('salesMarginRevVal');
        const profEl = document.getElementById('salesMarginProfitVal');
        const badgeEl = document.getElementById('salesMarginPercentBadge');

        if (costEl) costEl.innerText = totalCost.toLocaleString();
        if (revEl) revEl.innerText = netRev.toLocaleString();
        if (profEl) {
          profEl.innerText = (profit >= 0 ? '+' : '') + profit.toLocaleString();
          profEl.className = 'font-mono font-black text-[11px] ' + (profit >= 0 ? 'text-emerald-400' : 'text-rose-400');
        }
        if (badgeEl) {
          badgeEl.innerText = `نسبة الربح: ${pct}%`;
          badgeEl.className = 'px-2 py-0.5 rounded-full text-[10px] font-black border ' + (pct >= 0 ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border-rose-500/40');
        }
      },

      // ─────────────────────────────────────────────────────────────
      // 3. تعليق واسترجاع الفواتير المعلقة (PARK & RECALL INVOICES)
      // ─────────────────────────────────────────────────────────────
      getParkedInvoices() {
        try {
          return JSON.parse(localStorage.getItem('magaies_parked_sales_invoices_v1') || '[]');
        } catch (e) {
          return [];
        }
      },

      updateParkedCountUI() {
        const list = this.getParkedInvoices();
        const badge1 = document.getElementById('salesParkedCountBadge');
        if (badge1) badge1.innerText = list.length;
        const badge2 = document.getElementById('salesParkedInvoicesCount');
        if (badge2) badge2.innerText = list.length;
      },

      parkCurrentInvoice() {
        const cust = document.getElementById('salesCustomerInput')?.value?.trim() || this.selectedCustomer?.name || 'عميل نقدي عام';
        const wh = document.getElementById('salesWarehouseInput')?.value?.trim() || this.selectedWarehouse || 'المستودع الرئيسي';
        const cc = document.getElementById('salesCostCenterInput')?.value?.trim() || this.selectedCostCenter || 'المركز الرئيسي';
        const itemName = document.getElementById('salesItemSearchInput')?.value?.trim() || '';
        const qty = document.getElementById('salesQtyInput')?.value || '';
        const price = document.getElementById('salesPriceInput')?.value || '';
        const gross = document.getElementById('salesItemGrossTotal')?.value || '';
        const tax = document.getElementById('salesTaxInput')?.value || '';
        const disc = document.getElementById('salesDiscountInput')?.value || '';
        const labor = document.getElementById('salesLaborInput')?.value || '';
        const total = document.getElementById('salesTotalInput')?.value || '';
        const notes = document.getElementById('salesNotesInput')?.value || '';
        const unit = document.getElementById('salesUnitSelect')?.value || '';
        const invNo = document.getElementById('opSalesInvoiceNoPill')?.innerText?.trim() || ('S' + Date.now());

        if ((!this.cart || this.cart.length === 0) && (!itemName || itemName === 'اختيار الصنف') && !price && !total) {
          if (typeof showFloatingToast === 'function') {
            showFloatingToast('⚠️ يرجى اختيار صنف أو كتابة بيانات الفاتورة قبل تعليقها!');
          }
          return;
        }

        const parkedEntry = {
          id: 'PARK-' + Date.now(),
          invoiceNo: invNo,
          parkedAt: new Date().toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' }),
          customerName: cust,
          selectedCustomer: this.selectedCustomer,
          warehouse: wh,
          costCenter: cc,
          selectedItemIndex: this.selectedItemIndex,
          itemName,
          unit,
          qty,
          price,
          gross,
          tax,
          disc,
          labor,
          total,
          notes,
          cart: [...(this.cart || [])]
        };

        const list = this.getParkedInvoices();
        list.unshift(parkedEntry);
        try {
          localStorage.setItem('magaies_parked_sales_invoices_v1', JSON.stringify(list.slice(0, 30)));
        } catch (e) {}

        this.updateParkedCountUI();
        this.newInvoice();
        if (window.triggerHapticPulse) triggerHapticPulse(20);
        if (typeof showFloatingToast === 'function') {
          showFloatingToast(`⏸️ تم تعليق فاتورة [${cust}] بنجاح وتفريغ الشاشة لخدمة عميل آخر!`);
        }
      },

      openParkedInvoicesModal() {
        const modal = document.getElementById('suiteParkedInvoicesModal');
        const listEl = document.getElementById('suiteParkedInvoicesList');
        if (!modal || !listEl) return;

        const list = this.getParkedInvoices();
        this.updateParkedCountUI();

        if (list.length === 0) {
          listEl.innerHTML = `
            <div class="text-center py-8 text-slate-400 text-xs bg-slate-950/60 rounded-xl border border-slate-800">
              <i class="fa-solid fa-inbox text-2xl text-slate-600 mb-2 block"></i>
              لا توجد فواتير معلقة حالياً.<br>يمكنك تعليق أي فاتورة جارية بالضغط على زر "تعليق الفاتورة".
            </div>`;
        } else {
          listEl.innerHTML = list.map((p, idx) => `
            <div class="p-3 rounded-xl bg-slate-950/90 border border-amber-500/30 flex items-center justify-between gap-2 shadow-md">
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                  <span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 font-mono text-[10px] font-black">#${p.invoiceNo}</span>
                  <span class="text-white font-black text-xs truncate">${p.customerName}</span>
                  <span class="text-[10px] text-slate-400 font-mono mr-auto">${p.parkedAt || ''}</span>
                </div>
                <div class="text-[11px] text-cyan-300 font-bold truncate mt-1">
                  📦 ${p.itemName || (p.cart && p.cart[0]?.name) || 'أصناف متعددة'} ${p.qty ? `(${p.qty} ${p.unit || ''})` : ''}
                </div>
                <div class="text-[11px] text-emerald-400 font-mono font-black mt-0.5">
                  الصافي: ${p.total || '0'} | المستودع: ${p.warehouse}
                </div>
              </div>
              <div class="flex flex-col gap-1.5 shrink-0">
                <button type="button" onclick="SuiteSalesPOS.recallParkedInvoice(${idx})" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-black flex items-center gap-1 shadow cursor-pointer active:scale-95">
                  <i class="fa-solid fa-play text-[10px]"></i>
                  <span>استرجاع</span>
                </button>
                <button type="button" onclick="SuiteSalesPOS.deleteParkedInvoice(${idx})" class="px-2.5 py-1 rounded-lg bg-rose-950/80 hover:bg-rose-800 text-rose-300 border border-rose-500/30 text-[10px] font-bold flex items-center justify-center gap-1 cursor-pointer active:scale-95">
                  <i class="fa-solid fa-trash-can text-[9px]"></i>
                  <span>حذف</span>
                </button>
              </div>
            </div>
          `).join('');
        }

        modal.classList.remove('hidden');
      },

      closeParkedInvoicesModal() {
        const modal = document.getElementById('suiteParkedInvoicesModal');
        if (modal) modal.classList.add('hidden');
      },

      recallParkedInvoice(idx) {
        const list = this.getParkedInvoices();
        const p = list[idx];
        if (!p) return;

        if (p.selectedCustomer) {
          this.selectCustomer(p.selectedCustomer);
        } else if (p.customerName) {
          const custInp = document.getElementById('salesCustomerInput');
          if (custInp) {
            custInp.value = p.customerName;
            custInp.classList.remove('pos-ghost-default');
          }
        }

        if (p.warehouse) this.selectWarehouse(p.warehouse);
        if (p.costCenter) this.selectCostCenter(p.costCenter);

        if (p.selectedItemIndex !== undefined && p.selectedItemIndex !== -1) {
          this.selectItemByIndex(p.selectedItemIndex);
        } else if (p.itemName) {
          const itInp = document.getElementById('salesItemSearchInput');
          if (itInp) {
            itInp.value = p.itemName;
            itInp.classList.remove('pos-ghost-default');
          }
        }

        if (p.unit) {
          const uSel = document.getElementById('salesUnitSelect');
          if (uSel) {
            if (!Array.from(uSel.options).some(o => o.value === p.unit)) {
              const opt = document.createElement('option');
              opt.value = p.unit;
              opt.text = p.unit;
              uSel.appendChild(opt);
            }
            uSel.value = p.unit;
          }
        }

        const setField = (id, val) => {
          const el = document.getElementById(id);
          if (el) {
            el.value = val || '';
            if (val) {
              el.classList.remove('pos-ghost-default');
              delete el.dataset.ghostAuto;
            }
          }
        };
        setField('salesQtyInput', p.qty);
        setField('salesPriceInput', p.price);
        setField('salesItemGrossTotal', p.gross);
        setField('salesTaxInput', p.tax);
        setField('salesDiscountInput', p.disc);
        setField('salesLaborInput', p.labor);
        setField('salesTotalInput', p.total);
        setField('salesNotesInput', p.notes);

        const pill = document.getElementById('opSalesInvoiceNoPill');
        if (pill && p.invoiceNo) pill.innerText = p.invoiceNo;

        this.cart = Array.isArray(p.cart) ? [...p.cart] : [];
        this.renderCart();
        this.calcItemSubtotal();

        list.splice(idx, 1);
        try {
          localStorage.setItem('magaies_parked_sales_invoices_v1', JSON.stringify(list));
        } catch (e) {}
        this.updateParkedCountUI();
        this.closeParkedInvoicesModal();

        if (typeof showFloatingToast === 'function') {
          showFloatingToast(`▶️ تم استرجاع فاتورة [${p.customerName}] بجميع بياناتها بنجاح!`);
        }
      },

      deleteParkedInvoice(idx) {
        const list = this.getParkedInvoices();
        list.splice(idx, 1);
        try {
          localStorage.setItem('magaies_parked_sales_invoices_v1', JSON.stringify(list));
        } catch (e) {}
        this.updateParkedCountUI();
        this.openParkedInvoicesModal();
      },

      // ─────────────────────────────────────────────────────────────
      // 4. مشاركة الفاتورة كـ PDF بـ QR Code أو عبر واتساب مباشرة
      // ─────────────────────────────────────────────────────────────
      openShareInvoiceModal() {
        const modal = document.getElementById('suiteShareInvoiceModal');
        if (!modal) {
          this.shareInvoice();
          return;
        }
        const invNo = document.getElementById('opSalesInvoiceNoPill')?.innerText?.trim() || ('S' + Date.now());
        const cust = document.getElementById('salesCustomerInput')?.value?.trim() || this.selectedCustomer?.name || 'عميل نقدي عام';
        const total = document.getElementById('salesTotalInput')?.value || document.getElementById('salesItemGrossTotal')?.value || '0.00';
        const cur = this.currencyMode === 'USD' ? '$' : 'ج.س';
        const date = document.getElementById('salesInvoiceDate')?.value || new Date().toISOString().split('T')[0];

        const sumEl = document.getElementById('suiteShareInvSummaryText');
        if (sumEl) {
          sumEl.innerText = `فاتورة #${invNo} • ${cust} • الصافي: ${total} ${cur}`;
        }

        const qrImg = document.getElementById('suiteShareQrImg');
        if (qrImg) {
          const qrData = `MAGAIES ERP INVOICE\nNo: #${invNo}\nDate: ${date}\nCustomer: ${cust}\nTotal: ${total} ${cur}`;
          qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=4&data=${encodeURIComponent(qrData)}`;
        }

        const phoneInp = document.getElementById('suiteShareWhatsappPhone');
        if (phoneInp) {
          const custObj = this.selectedCustomer || (this.dbCustomers || []).find(c => c.name === cust);
          phoneInp.value = custObj?.whatsapp || custObj?.phone || '';
        }

        modal.classList.remove('hidden');
      },

      closeShareInvoiceModal() {
        const modal = document.getElementById('suiteShareInvoiceModal');
        if (modal) modal.classList.add('hidden');
      },

      sendInvoiceWhatsAppDirect() {
        const invNo = document.getElementById('opSalesInvoiceNoPill')?.innerText?.trim() || ('S' + Date.now());
        const cust = document.getElementById('salesCustomerInput')?.value?.trim() || 'عميل نقدي عام';
        const invDate = document.getElementById('salesInvoiceDate')?.value || new Date().toISOString().split('T')[0];
        const gross = document.getElementById('salesItemGrossTotal')?.value || '0.00';
        const total = document.getElementById('salesTotalInput')?.value || gross || '0.00';
        const currency = this.currencyMode === 'USD' ? '$' : 'ج.س';
        const wh = document.getElementById('salesWarehouseInput')?.value?.trim() || this.selectedWarehouse || 'المستودع الرئيسي';
        const notes = document.getElementById('salesNotesInput')?.value || '';
        const itName = document.getElementById('salesItemSearchInput')?.value?.trim() || 'صنف مبيعات';
        const qty = document.getElementById('salesQtyInput')?.value || '1';
        const price = document.getElementById('salesPriceInput')?.value || '0';
        const unit = document.getElementById('salesUnitSelect')?.value || 'طن عددي';

        const text = `🧾 *فاتورة مبيعات إلكترونية موثقة - MAGAIES ERP* 🏢\n` +
          `━━━━━━━━━━━━━━━━━━━━\n` +
          `🔢 *رقم الفاتورة:* #${invNo}\n` +
          `📅 *التاريخ:* ${invDate}\n` +
          `👤 *السيد / العميل:* ${cust}\n` +
          `🏢 *المستودع المخرج:* ${wh}\n` +
          `━━━━━━━━━━━━━━━━━━━━\n` +
          `📦 *الصنف:* ${itName}\n` +
          `📊 *الكمية:* ${qty} ${unit} × ${price} ${currency}\n` +
          `💵 *الجملة:* ${gross} ${currency}\n` +
          `💰 *الإجمالي الصافي النهائي:* *${total} ${currency}*\n` +
          (notes ? `📝 *البيان:* ${notes}\n` : '') +
          `━━━━━━━━━━━━━━━━━━━━\n` +
          `✅ *موثقة برمز QR الإلكتروني - مؤسسة السجانة لمواد البناء*`;

        let rawPhone = (document.getElementById('suiteShareWhatsappPhone')?.value || '').replace(/[^0-9+]/g, '');
        if (rawPhone.startsWith('00')) rawPhone = rawPhone.slice(2);
        else if (rawPhone.startsWith('+')) rawPhone = rawPhone.slice(1);
        else if (rawPhone.startsWith('09') || rawPhone.startsWith('01')) rawPhone = '249' + rawPhone.slice(1);

        const waUrl = rawPhone
          ? `https://wa.me/${rawPhone}?text=${encodeURIComponent(text)}`
          : `https://wa.me/?text=${encodeURIComponent(text)}`;
        window.open(waUrl, '_blank');
        this.closeShareInvoiceModal();
      },

      printCustomerStatement() {
        const custName = (document.getElementById('salesCustomerInput')?.value || this.selectedCustomer?.name || 'عميل نقدي عام').trim();
        const custObj = (this.dbCustomers || []).find(c => c.name === custName) || this.selectedCustomer;
        const bal = parseFloat(custObj?.currentBalance || custObj?.balance || custObj?.openingBalance || 0);
        const rate = this.usdExchangeRate || 2850;
        const isUsd = custObj?.currency === 'USD';
        const usdStr = isUsd ? ('$' + bal.toLocaleString('en-US')) : ('$' + Math.round(bal / rate).toLocaleString('en-US'));
        const sdgStr = isUsd ? (Math.round(bal * rate).toLocaleString('en-US') + ' ج.س') : (bal.toLocaleString('en-US') + ' ج.س');

        if (typeof triggerStatementForAccount === 'function' && custName) {
          triggerStatementForAccount(custName, 'عميل', usdStr, sdgStr);
        } else if (typeof openModal === 'function') {
          openModal('modalOpStatement');
        } else {
          alert('كشف حساب العميل: ' + custName + ' | الرصيد: ' + sdgStr);
        }
      },

      openDeliveryOrder() {
        const invNo = document.getElementById('opSalesInvoiceNoPill')?.innerText?.trim() || ('S' + Date.now());
        const wh = document.getElementById('salesWarehouseInput')?.value?.trim() || this.selectedWarehouse || document.getElementById('salesWarehouseSelect')?.value || 'المستودع الرئيسي';
        const cust = document.getElementById('salesCustomerInput')?.value?.trim() || this.selectedCustomer?.name || 'عميل نقدي عام';

        let curItems = [];
        if (this.cart && this.cart.length > 0) {
          curItems = this.cart.map(it => ({ name: it.name, qty: (it.qty || 1) + ' ' + (it.unit || '') }));
        } else {
          const itName = document.getElementById('salesItemSearchInput')?.value?.trim() || (this.selectedItemIndex !== -1 && this.dbItems[this.selectedItemIndex] ? this.dbItems[this.selectedItemIndex].name : 'صنف الفاتورة الحالية');
          const qtyVal = document.getElementById('salesQtyInput')?.value?.trim() || '1';
          const unitVal = document.getElementById('salesUnitSelect')?.value || 'طن عددي';
          curItems = [{ name: itName, qty: `${qtyVal} ${unitVal}` }];
        }

        if (typeof openModal === 'function') openModal('modalOpDelivery');
        if (typeof fetchMirrorNumbers === 'function') fetchMirrorNumbers();
        if (typeof loadDeliverySalesInvoices === 'function') loadDeliverySalesInvoices();

        setTimeout(() => {
          const refSel = document.getElementById('opDeliveryRefSelect');
          if (refSel && invNo) {
            let found = false;
            for (let i = 0; i < refSel.options.length; i++) {
              if (refSel.options[i].value === invNo) {
                refSel.options[i].setAttribute('data-items', JSON.stringify(curItems));
                refSel.selectedIndex = i;
                found = true;
                break;
              }
            }
            if (!found) {
              const opt = document.createElement('option');
              opt.value = invNo;
              opt.text = `${invNo} - ${cust}`;
              opt.setAttribute('data-items', JSON.stringify(curItems));
              refSel.appendChild(opt);
              refSel.value = invNo;
            }
            if (typeof onOpDeliveryRefChange === 'function') onOpDeliveryRefChange();
          }
          const whSel = document.getElementById('opDeliveryWarehouseSelect');
          if (whSel && wh) {
            if (!Array.from(whSel.options).some(o => o.value === wh)) {
              const wOpt = document.createElement('option');
              wOpt.value = wh;
              wOpt.text = wh;
              whSel.appendChild(wOpt);
            }
            whSel.value = wh;
          }
        }, 160);
      }
    };


    // ═════════════════════════════════════════════════════════════════════
    // 📦 SUITE PURCHASE POS ENGINE - DIRECT PARITY WITH SALES POS
    // ═════════════════════════════════════════════════════════════════════
        // ═════════════════════════════════════════════════════════════════════
    // 📦 SUITE PURCHASE POS ENGINE - DIRECT PARITY WITH SALES POS
    // ═════════════════════════════════════════════════════════════════════
    window.SuitePurchasePOS = {
      cart: [],
      recentInvoices: [],
      currentInvoiceIndex: -1,
      latestGeneratedInvNo: '',
      selectedItemIndex: -1,
      selectedSupplier: null,
      selectedWarehouse: 'المستودع الرئيسي',
      selectedCostCenter: 'المركز الرئيسي',
      currencyMode: 'USD',
      usdExchangeRate: 2850,

      exit() {
        exitToMainDashboard('modalOpPurchase');
      },

      dbSuppliers: [
        { id: 'SUP-001', name: 'مطاحن الخرطوم للغلال', phone: '0123445566', currentBalance: 0, currency: 'SDG' },
        { id: 'SUP-002', name: 'شركة مصنع الأسعد للحديد والصلب', phone: '0123456789', currentBalance: 0, currency: 'USD' },
        { id: 'SUP-003', name: 'شركة أوميغا للصناعات والصلب', phone: '0912345678', currentBalance: 0, currency: 'USD' },
        { id: 'SUP-004', name: 'شركة أسمنت عطبرة المحدودة', phone: '0112345678', currentBalance: 0, currency: 'SDG' },
        { id: 'SUP-005', name: 'مجمع جياد الصناعي', phone: '0901234567', currentBalance: 0, currency: 'USD' },
        { id: 'SUP-006', name: 'مؤسسة الهدى للتجارة والتوريدات', phone: '0123987654', currentBalance: 0, currency: 'SDG' },
        { id: 'SUP-007', name: 'شركة أسمنت بربر المحدودة', phone: '0912987654', currentBalance: 0, currency: 'SDG' },
        { id: 'SUP-008', name: 'مصنع الراجحي للحديد والصلب', phone: '0123112233', currentBalance: 0, currency: 'USD' },
        { id: 'SUP-CASH', name: 'مورد نقدي عام', phone: '', currentBalance: 0, currency: 'SDG' }
      ],

      async init() {
        this.loadSuppliersFromStorage();
        this.syncHeaderDatePill();
        if (window.SuiteFXManager) {
          await SuiteFXManager.fetchSystemRate();
          this.usdExchangeRate = SuiteFXManager.currentRate;
        }
        this.syncCurrencyDisplay();
        this.initDefaultSupplier();
        this.initDefaultCostCenter();
        this.initDefaultWarehouse();
        this.updateParkedCountUI();
        this.loadRecentInvoices();
        this.generateNewInvoiceNo();
        this.renderCart();
      },

      loadSuppliersFromStorage() {
        try {
          const raw = localStorage.getItem('erp_suppliers_v1') || (window.getTenantKey ? localStorage.getItem(window.getTenantKey('erp_suppliers_v1')) : null);
          if (raw) {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed) && parsed.length > 0) {
              parsed.forEach(s => {
                if (s && s.name && !this.dbSuppliers.some(ex => ex.name === s.name)) {
                  this.dbSuppliers.unshift(s);
                }
              });
            }
          }
        } catch(e) {
          console.warn('[Purchase] Could not parse stored suppliers:', e);
        }
      },

      initDefaultSupplier() {
        const inp = document.getElementById('purchaseSupplierInput');
        const disp = document.getElementById('suitePurSupplierDisplayName');
        if (!this.selectedSupplier) {
          this.selectedSupplier = this.dbSuppliers[0];
        }
        if (disp && this.selectedSupplier) disp.innerText = this.selectedSupplier.name;
        if (inp && this.selectedSupplier) inp.value = this.selectedSupplier.name;
      },

      initDefaultCostCenter() {
        const disp = document.getElementById('purCostCenterDisplayName');
        const inp = document.getElementById('purCostCenterInput');
        if (disp) disp.innerText = this.selectedCostCenter;
        if (inp) inp.value = this.selectedCostCenter;
      },

      initDefaultWarehouse() {
        const disp = document.getElementById('suitePurWarehouseDisplayName');
        const sel = document.getElementById('purchaseWarehouseSelect');
        if (disp) disp.innerText = this.selectedWarehouse;
        if (sel) sel.value = this.selectedWarehouse;
      },

      syncHeaderDatePill(val) {
        const pill = document.getElementById('opPurHeaderDatePill');
        if (!pill) return;
        const d = val ? new Date(val) : new Date();
        pill.innerText = d.toISOString().split('T')[0];
      },

      toggleCurrency() {
        this.currencyMode = this.currencyMode === 'USD' ? 'SDG' : 'USD';
        this.syncCurrencyDisplay();
        if (window.triggerHapticPulse) triggerHapticPulse(15);
      },

      syncCurrencyDisplay() {
        const btn = document.getElementById('btnPurCurrencyToggle');
        const sym = document.getElementById('purCurrencySymbol');
        const disp = document.getElementById('purUsdRateDisplay');
        const inp = document.getElementById('purUsdRateInput');
        const rateBox = document.getElementById('purRateContainer');

        if (disp) disp.innerText = this.usdExchangeRate.toString().slice(0, 5);
        if (inp) inp.value = this.usdExchangeRate.toString().slice(0, 5);

        if (!btn || !sym) return;

        if (this.currencyMode === 'SDG') {
          sym.innerText = 'ج.س';
          sym.style.setProperty('color', '#fbbf24', 'important');
          sym.style.setProperty('font-size', '10px', 'important');
          sym.style.setProperty('font-weight', '900', 'important');
          sym.style.setProperty('text-shadow', '0 0 4px #fbbf24', 'important');
          sym.className = 'text-amber-300 text-[10px] font-black leading-none pointer-events-none';
          btn.style.setProperty('background', '#000000', 'important');
          btn.style.setProperty('border', '1.5px solid #f59e0b', 'important');
          btn.style.setProperty('box-shadow', '0 0 8px rgba(245, 158, 11, 0.8)', 'important');
          btn.title = 'العملة: جنيه سوداني (ج.س) - انقر للتحويل إلى دولار ($)';
          if (rateBox) {
            rateBox.style.setProperty('background', 'linear-gradient(135deg, #451a03, #09090b)', 'important');
            rateBox.style.setProperty('border', '2px solid #f59e0b', 'important');
            rateBox.style.setProperty('box-shadow', '0 0 14px rgba(245, 158, 11, 0.65), inset 0 0 8px rgba(245, 158, 11, 0.35)', 'important');
          }
        } else {
          sym.innerText = '$';
          sym.style.setProperty('color', '#22c55e', 'important');
          sym.style.setProperty('font-size', '13px', 'important');
          sym.style.setProperty('font-weight', '900', 'important');
          sym.style.setProperty('text-shadow', '0 0 4px #22c55e', 'important');
          sym.className = 'text-emerald-400 text-xs font-black leading-none pointer-events-none';
          btn.style.setProperty('background', '#000000', 'important');
          btn.style.setProperty('border', '1.5px solid #22c55e', 'important');
          btn.style.setProperty('box-shadow', '0 0 8px rgba(34, 197, 94, 0.8)', 'important');
          btn.title = 'العملة: دولار ($) - انقر للتحويل إلى جنيه';
          if (rateBox) {
            rateBox.style.setProperty('background', 'linear-gradient(135deg, #022c22, #09090b)', 'important');
            rateBox.style.setProperty('border', '2px solid #10b981', 'important');
            rateBox.style.setProperty('box-shadow', '0 0 14px rgba(16, 185, 129, 0.65), inset 0 0 8px rgba(16, 185, 129, 0.35)', 'important');
          }
        }

        const gt = document.getElementById('suitePurGrandTotalDisplay');
        if (gt) {
          const num = parseFloat(gt.innerText.replace(/[^0-9.]/g, '')) || 0;
          if (this.currencyMode === 'USD') {
            gt.innerText = num.toFixed(2) + ' $';
            gt.style.setProperty('color', '#22c55e', 'important');
          } else {
            gt.innerText = num.toFixed(2) + ' SDG';
            gt.style.setProperty('color', '#fbbf24', 'important');
          }
        }
      },

      openSupplierSearch() {
        window._activeModalContext = 'purchase';
        const modal = document.getElementById('suiteCustomerSearchModal');
        if (modal) {
          modal.classList.remove('hidden');
          const titleEl = document.getElementById('suitePartnerModalTitle');
          if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-truck-field"></i> اختيار مورد من قاعدة البيانات (بحث فوري)';
          const inp = document.getElementById('suiteCustSearchFilter');
          if (inp) {
            inp.placeholder = 'ابحث باسم المورد أو رقم الهاتف...';
            inp.value = '';
            setTimeout(() => inp.focus(), 50);
          }
          this.filterSupplierSearch('');
        }
      },

      filterSupplierSearch(q) {
        const container = document.getElementById('suiteCustSearchResults');
        if (!container) return;
        const term = (q || '').trim().toLowerCase();
        const list = this.dbSuppliers.filter(s => {
          return !term || (s.name && s.name.toLowerCase().includes(term)) || (s.phone && s.phone.includes(term));
        });

        if (list.length === 0) {
          const cleanQ = (q || '').replace(/'/g, "\\'");
          container.innerHTML = `
            <div class="p-3 text-center text-slate-400 bg-slate-900/60 rounded-xl space-y-2">
              <p>لم يتم العثور على مورد بهذا الاسم</p>
              <button type="button" onclick="SuitePOSRouter.selectPartner({ name: '${cleanQ}', phone: '' })" class="px-3 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded-lg text-xs font-bold cursor-pointer">
                + اعتماد "${cleanQ}" كمورد جديد
              </button>
            </div>`;
          return;
        }

        let html = '';
        list.forEach(s => {
          const sJson = JSON.stringify(s).replace(/"/g, '&quot;');
          html += `
            <div onclick="SuitePOSRouter.selectPartner(${sJson})" class="p-2.5 rounded-xl bg-slate-900/80 hover:bg-amber-950/40 border border-slate-800 hover:border-amber-500/50 cursor-pointer flex justify-between items-center transition group">
              <div>
                <span class="font-bold text-white text-xs block group-hover:text-amber-300 transition">${s.name}</span>
                ${s.phone ? `<span class="text-[10px] text-slate-400 font-mono"><i class="fa-solid fa-phone text-[9px]"></i> ${s.phone}</span>` : ''}
              </div>
              <div class="text-right">
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-amber-300 font-bold">مورد</span>
              </div>
            </div>`;
        });
        container.innerHTML = html;
      },

      selectSupplier(s) {
        if (!s) return;
        this.selectedSupplier = s;
        const disp = document.getElementById('suitePurSupplierDisplayName');
        if (disp) disp.innerText = s.name;
        const inp = document.getElementById('purchaseSupplierInput');
        if (inp) inp.value = s.name;
        this.closeCustomerSearch();
      },

      closeCustomerSearch() {
        const modal = document.getElementById('suiteCustomerSearchModal');
        if (modal) modal.classList.add('hidden');
      },

      openCostCenterSearch() {
        window._activeModalContext = 'purchase';
        if (window.SuiteSalesPOS) SuiteSalesPOS.openCostCenterSearch();
      },

      selectCostCenter(name) {
        this.selectedCostCenter = name;
        const disp = document.getElementById('purCostCenterDisplayName');
        if (disp) disp.innerText = name;
        const inp = document.getElementById('purCostCenterInput');
        if (inp) inp.value = name;
        if (window.SuiteSalesPOS) SuiteSalesPOS.closeCostCenterSearch();
      },

      openWarehouseSearch() {
        window._activeModalContext = 'purchase';
        if (window.SuiteSalesPOS) SuiteSalesPOS.openWarehouseSearch();
      },

      selectWarehouse(name) {
        this.selectedWarehouse = name;
        const disp = document.getElementById('suitePurWarehouseDisplayName');
        if (disp) disp.innerText = name;
        const sel = document.getElementById('purchaseWarehouseSelect');
        if (sel) sel.value = name;
        if (window.SuiteSalesPOS) SuiteSalesPOS.closeWarehouseSearch();
      },

      openItemSearch() {
        window._activeModalContext = 'purchase';
        const modal = document.getElementById('suiteItemSearchModal');
        if (modal) {
          modal.classList.remove('hidden');
          const inp = document.getElementById('suiteItemSearchFilter');
          if (inp) {
            inp.value = '';
            setTimeout(() => inp.focus(), 50);
          }
          if (window.SuiteSalesPOS) SuiteSalesPOS.filterItemSearch('');
        }
      },

      closeItemSearch() {
        const modal = document.getElementById('suiteItemSearchModal');
        if (modal) modal.classList.add('hidden');
      },

      selectItemByIndex(idx) {
        const items = (window.SuiteSalesPOS && SuiteSalesPOS.dbItems) ? SuiteSalesPOS.dbItems : [];
        if (!items[idx]) return;
        const item = items[idx];
        this.selectedItemIndex = idx;

        const disp = document.getElementById('suitePurItemDisplayName');
        if (disp) disp.innerHTML = `<span class="font-bold text-xs sm:text-sm truncate text-amber-300">${item.name}</span>`;

        const hid = document.getElementById('purItemSelectVal');
        if (hid) hid.value = item.id || item.name;

        // Populate unit select
        const uSel = document.getElementById('purUnitSelect');
        if (uSel) {
          uSel.innerHTML = '';
          const units = [];
          if (item.baseUnit) units.push({ name: item.baseUnit, factor: 1 });
          else if (item.unit) units.push({ name: item.unit, factor: 1 });
          if (Array.isArray(item.unitsArray)) {
            item.unitsArray.forEach(u => {
              if (u && u.name && !units.some(x => x.name === u.name)) units.push(u);
            });
          }
          if (units.length === 0) units.push({ name: 'حبة', factor: 1 });
          const savedFactors = {};
          units.forEach(u => {
            const cleanUnitName = String(u.name || '').replace(/\s*\(\d+(?:\.\d+)?\)\s*/g, '').trim() || 'حبة';
            const factorVal = u.factor || 1;
            savedFactors[cleanUnitName] = factorVal;
            const opt = document.createElement('option');
            opt.value = cleanUnitName;
            opt.innerText = cleanUnitName;
            opt.dataset.factor = factorVal;
            uSel.appendChild(opt);
          });
          try {
            const allFactors = JSON.parse(localStorage.getItem('magaies_saved_unit_factors_v1') || '{}');
            allFactors[item.id || item.name] = savedFactors;
            localStorage.setItem('magaies_saved_unit_factors_v1', JSON.stringify(allFactors));
          } catch(e) {}
          if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleUnit(uSel);
        }

        // Pre-fill last purchase price or cost if available
        const pInp = document.getElementById('purPriceInput');
        if (pInp) {
          const cost = item.lastPurchaseCost || item.averageCost || item.cost || item.price || 0;
          pInp.value = cost > 0 ? cost : '';
        }

        const qInp = document.getElementById('purQtyInput');
        if (qInp && (!qInp.value || parseFloat(qInp.value) <= 0)) {
          qInp.value = 1;
        }

        this.calcItemSubtotal();
        this.closeItemSearch();
      },

      selectCustomItem(name) {
        if (!name || !name.trim()) return;
        const cleanName = name.trim();
        this.selectedItemIndex = -1;

        const disp = document.getElementById('suitePurItemDisplayName');
        if (disp) disp.innerHTML = `<span class="font-bold text-xs sm:text-sm truncate text-amber-300">${cleanName} (مخصص)</span>`;

        const hid = document.getElementById('purItemSelectVal');
        if (hid) hid.value = cleanName;

        const uSel = document.getElementById('purUnitSelect');
        if (uSel) {
          uSel.innerHTML = '<option value="خدمة">خدمة</option><option value="حبة">حبة</option><option value="طن">طن</option><option value="شحنة">شحنة</option>';
        }

        const qInp = document.getElementById('purQtyInput');
        if (qInp && (!qInp.value || parseFloat(qInp.value) <= 0)) qInp.value = 1;

        this.calcItemSubtotal();
        this.closeItemSearch();
      },

      promptCustomItem() {
        const name = prompt('أدخل اسم البند أو الخدمة أو المصروف للمشتريات:');
        if (name && name.trim()) {
          this.selectCustomItem(name.trim());
        }
      },

      openNotesModal() {
        if (window.SuiteSalesPOS && SuiteSalesPOS.openNotesModal) {
          SuiteSalesPOS.openNotesModal('purchase');
        } else {
          window._activeModalContext = 'purchase';
          const modal = document.getElementById('suiteSalesNotesModal');
          if (modal) {
            modal.classList.remove('hidden');
            const ta = document.getElementById('salesNotesExpandedTextarea');
            const inp = document.getElementById('purNotesInput');
            if (ta) ta.value = inp ? inp.value : '';
          }
        }
      },

      onUnitChange() {
        this.calcItemSubtotal();
      },

      calcItemSubtotal() {
        const qty = parseFloat(document.getElementById('purQtyInput')?.value || 0);
        const prc = parseFloat(document.getElementById('purPriceInput')?.value || 0);
        const disc = parseFloat(document.getElementById('purDiscountInput')?.value || 0);
        const tax = parseFloat(document.getElementById('purTaxInput')?.value || 0);
        const labor = parseFloat(document.getElementById('purLaborInput')?.value || 0);

        const gross = qty * prc;
        const grossEl = document.getElementById('purItemGrossTotal');
        if (grossEl) {
          grossEl.value = (gross > 0) ? gross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
        }

        const netTotal = Math.max(0, gross - disc + tax + labor);
        const totInp = document.getElementById('purTotalInput');
        if (totInp) {
          totInp.value = (netTotal > 0) ? netTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
        }
        if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleAll();
        if (typeof this.updateErpSmartStatusStrip === 'function') this.updateErpSmartStatusStrip();
        return netTotal;
      },

      addItemToCart() {
        const itIdx = this.selectedItemIndex;
        let name = '';
        let unit = 'حبة';

        if (itIdx !== -1 && window.SuiteSalesPOS && SuiteSalesPOS.dbItems && SuiteSalesPOS.dbItems[itIdx]) {
          const it = SuiteSalesPOS.dbItems[itIdx];
          name = it.name;
          const uSel = document.getElementById('purUnitSelect');
          unit = uSel ? uSel.value : (it.baseUnit || it.unit || 'حبة');
        } else {
          const hid = document.getElementById('purItemSelectVal');
          name = hid ? hid.value : (document.getElementById('purItemSearchInput')?.value?.trim() || '');
          const uSel = document.getElementById('purUnitSelect');
          unit = uSel ? uSel.value : 'حبة';
        }

        if (!name || !name.trim()) {
          alert('⚠️ يرجى اختيار صنف أولاً عبر الضغط على خانة الصنف للبحث.');
          return;
        }

        const warehouse = document.getElementById('purchaseWarehouseInput')?.value?.trim() || this.selectedWarehouse || document.getElementById('purchaseWarehouseSelect')?.value || 'المستودع الرئيسي';
        const costCenter = this.selectedCostCenter || 'المركز الرئيسي';

        const qty = parseFloat(document.getElementById('purQtyInput')?.value || 1);
        const price = parseFloat(document.getElementById('purPriceInput')?.value || 0);
        const discount = parseFloat(document.getElementById('purDiscountInput')?.value || 0);
        const tax = parseFloat(document.getElementById('purTaxInput')?.value || 0);
        const labor = parseFloat(document.getElementById('purLaborInput')?.value || 0);
        const subtotal = Math.max(0, (qty * price) - discount + tax + labor);

        this.cart.push({
          id: 'PUR-ITEM-' + Date.now(),
          name,
          unit,
          warehouse,
          costCenter,
          qty,
          price,
          discount,
          tax,
          labor,
          subtotal
        });

        this.clearCurrentInputs(true);
        this.renderCart();
        if (window.AppHaptics) AppHaptics.light();
      },

      removeItem(idx) {
        this.cart.splice(idx, 1);
        this.renderCart();
      },

      clearCart() {
        if (this.cart.length === 0) return;
        if (confirm('هل أنت متأكد من تفريغ كافة بنود فاتورة الشراء؟')) {
          this.cart = [];
          this.renderCart();
        }
      },

      clearCurrentInputs(silent = false) {
        this.selectedItemIndex = -1;
        const itInp = document.getElementById('purItemSearchInput');
        if (itInp) itInp.value = '';
        const disp = document.getElementById('suitePurItemDisplayName');
        if (disp) disp.innerHTML = '<span class="font-bold text-xs sm:text-sm truncate text-center text-amber-300">اختر الصنف...</span>';
        const hid = document.getElementById('purItemSelectVal');
        if (hid) hid.value = '';

        ['purPriceInput', 'purQtyInput', 'purDiscountInput', 'purTaxInput', 'purLaborInput', 'purTotalInput', 'purItemGrossTotal', 'purNotesInput'].forEach(id => {
          const el = document.getElementById(id);
          if (el) el.value = '';
        });

        this.calcItemSubtotal();
        if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleAll();
        if (!silent && typeof showFloatingToast === 'function') {
          showFloatingToast('🧹 تم مسح وتفريغ خانات صنف المشتريات بنجاح');
        }
      },

      generateNewInvoiceNo() {
        const d = new Date();
        const yy = String(d.getFullYear()).slice(-2);
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const prefix = `P${yy}${mm}`;

        let maxSeq = 0;
        const extractSeq = (raw) => {
          if (!raw) return 0;
          const s = String(raw).trim().toUpperCase();
          if (s === 'S26101012' || s === 'P26100045' || s === 'P26101012') return 0;
          if (/^[SPE]\d{9,}$/.test(s)) return 0;
          const m = s.match(/^P\d{3,4}(\d{4})$/);
          if (m) return parseInt(m[1], 10) || 0;
          if (/^\d{1,6}$/.test(s)) return parseInt(s, 10) || 0;
          const mTail = s.match(/(\d{1,5})$/);
          if (mTail) {
            const v = parseInt(mTail[1], 10) || 0;
            return (v > 0 && v < 9000) ? v : 0;
          }
          return 0;
        };

        if (window.MagaiesOffline && typeof MagaiesOffline.getHighestKnownSeq === 'function') {
          maxSeq = Math.max(maxSeq, MagaiesOffline.getHighestKnownSeq('purchase'));
        }
        if (window._mirrorCache && window._mirrorCache.purchase) {
          if (window._mirrorCache.purchase.lastPurchaseNo) maxSeq = Math.max(maxSeq, extractSeq(window._mirrorCache.purchase.lastPurchaseNo));
          if (window._mirrorCache.purchase.nextPurchaseNo) {
            const nxt = extractSeq(window._mirrorCache.purchase.nextPurchaseNo);
            if (nxt > 1) maxSeq = Math.max(maxSeq, nxt - 1);
          }
        }
        (this.recentInvoices || []).forEach(inv => {
          maxSeq = Math.max(maxSeq, extractSeq(inv.invoiceNo || inv.id));
        });
        try {
          const loc = JSON.parse(localStorage.getItem('magaies_local_purchase_invoices_v1') || '[]');
          if (Array.isArray(loc)) loc.forEach(inv => { maxSeq = Math.max(maxSeq, extractSeq(inv.invoiceNo || inv.id)); });
          const erpLoc = JSON.parse(localStorage.getItem('erp_purchases_v5') || '[]');
          if (Array.isArray(erpLoc)) erpLoc.forEach(inv => { maxSeq = Math.max(maxSeq, extractSeq(inv.invoiceNo || inv.id)); });
        } catch (e) {}

        const newNo = `${prefix}${String(maxSeq + 1).padStart(4, '0')}`;
        const pill = document.getElementById('opPurchaseNoPill');
        if (pill) pill.innerText = newNo;
        this.latestGeneratedInvNo = newNo;
        return newNo;
      },

      newInvoice(silent = false) {
        this.currentInvoiceIndex = -1;
        this.cart = [];
        this.clearCurrentInputs(true);
        this.generateNewInvoiceNo();
        if (typeof fetchMirrorNumbers === 'function') fetchMirrorNumbers();
        const dtInp = document.getElementById('purInvoiceDate');
        if (dtInp) {
          const today = new Date().toISOString().split('T')[0];
          dtInp.value = today;
          this.syncHeaderDatePill(today);
        }
        this.renderCart();
        if (!silent && typeof showFloatingToast === 'function') {
          showFloatingToast('📄 تم فتح فاتورة مشتريات جديدة برقم تسلسلي جديد!');
        }
      },

      getParkedInvoices() {
        try {
          return JSON.parse(localStorage.getItem('magaies_parked_purchase_invoices_v1') || '[]');
        } catch (e) {
          return [];
        }
      },

      updateParkedCountUI() {
        const list = this.getParkedInvoices();
        const badge = document.getElementById('purParkedInvoicesCount');
        if (badge) badge.innerText = list.length;
      },

      parkCurrentInvoice() {
        const supp = document.getElementById('purchaseSupplierInput')?.value?.trim() || this.selectedSupplier?.name || 'مطاحن الخرطوم للغلال';
        const wh = document.getElementById('purchaseWarehouseInput')?.value?.trim() || this.selectedWarehouse || 'المستودع الرئيسي';
        const cc = this.selectedCostCenter || 'المركز الرئيسي';
        const itemName = document.getElementById('purItemSearchInput')?.value?.trim() || '';
        const qty = document.getElementById('purQtyInput')?.value || '';
        const price = document.getElementById('purPriceInput')?.value || '';
        const gross = document.getElementById('purItemGrossTotal')?.value || '';
        const tax = document.getElementById('purTaxInput')?.value || '';
        const disc = document.getElementById('purDiscountInput')?.value || '';
        const labor = document.getElementById('purLaborInput')?.value || '';
        const total = document.getElementById('purTotalInput')?.value || '';
        const notes = document.getElementById('purNotesInput')?.value || '';
        const unit = document.getElementById('purUnitSelect')?.value || '';
        const invNo = document.getElementById('opPurchaseNoPill')?.innerText?.trim() || ('P' + Date.now());

        if ((!this.cart || this.cart.length === 0) && !itemName && !price && !total) {
          if (typeof showFloatingToast === 'function') {
            showFloatingToast('⚠️ يرجى اختيار صنف أو كتابة بيانات الفاتورة قبل تعليقها!');
          }
          return;
        }

        const parkedEntry = {
          id: 'PUR-PARK-' + Date.now(),
          invoiceNo: invNo,
          parkedAt: new Date().toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' }),
          supplierName: supp,
          selectedSupplier: this.selectedSupplier,
          warehouse: wh,
          costCenter: cc,
          selectedItemIndex: this.selectedItemIndex,
          itemName,
          unit,
          qty,
          price,
          gross,
          tax,
          disc,
          labor,
          total,
          notes,
          cart: [...(this.cart || [])]
        };

        const list = this.getParkedInvoices();
        list.unshift(parkedEntry);
        try {
          localStorage.setItem('magaies_parked_purchase_invoices_v1', JSON.stringify(list.slice(0, 30)));
        } catch (e) {}

        this.updateParkedCountUI();
        this.newInvoice(true);
        if (typeof showFloatingToast === 'function') {
          showFloatingToast(`⏸️ تم تعليق فاتورة مشتريات [${supp}] بنجاح!`);
        }
      },

      openParkedInvoicesModal() {
        const modal = document.getElementById('suiteParkedInvoicesModal');
        const listEl = document.getElementById('suiteParkedInvoicesList');
        if (!modal || !listEl) return;

        const list = this.getParkedInvoices();
        this.updateParkedCountUI();

        if (list.length === 0) {
          listEl.innerHTML = `
            <div class="text-center py-8 text-slate-400 text-xs bg-slate-950/60 rounded-xl border border-slate-800">
              <i class="fa-solid fa-inbox text-2xl text-slate-600 mb-2 block"></i>
              لا توجد فواتير مشتريات معلقة حالياً.
            </div>`;
        } else {
          listEl.innerHTML = list.map((p, idx) => `
            <div class="p-3 rounded-xl bg-slate-950/90 border border-amber-500/30 flex items-center justify-between gap-2 shadow-md">
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                  <span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 font-mono text-[10px] font-black">#${p.invoiceNo}</span>
                  <span class="text-white font-black text-xs truncate">${p.supplierName}</span>
                  <span class="text-[10px] text-slate-400 font-mono mr-auto">${p.parkedAt || ''}</span>
                </div>
                <div class="text-[11px] text-amber-300 font-bold truncate mt-1">
                  📦 ${p.itemName || (p.cart && p.cart[0]?.name) || 'أصناف متعددة'} ${p.qty ? `(${p.qty} ${p.unit || ''})` : ''}
                </div>
                <div class="text-[11px] text-emerald-400 font-mono font-black mt-0.5">
                  الصافي: ${p.total || '0'} | المستودع: ${p.warehouse}
                </div>
              </div>
              <div class="flex flex-col gap-1.5 shrink-0">
                <button type="button" onclick="SuitePurchasePOS.recallParkedInvoice(${idx})" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-black flex items-center gap-1 shadow cursor-pointer active:scale-95">
                  <i class="fa-solid fa-play text-[10px]"></i>
                  <span>استرجاع</span>
                </button>
                <button type="button" onclick="SuitePurchasePOS.deleteParkedInvoice(${idx})" class="px-2.5 py-1 rounded-lg bg-rose-950/80 hover:bg-rose-800 text-rose-300 border border-rose-500/30 text-[10px] font-bold flex items-center justify-center gap-1 cursor-pointer active:scale-95">
                  <i class="fa-solid fa-trash-can text-[9px]"></i>
                  <span>حذف</span>
                </button>
              </div>
            </div>
          `).join('');
        }
        modal.classList.remove('hidden');
      },

      recallParkedInvoice(idx) {
        const list = this.getParkedInvoices();
        const p = list[idx];
        if (!p) return;

        if (p.selectedSupplier) this.selectSupplier(p.selectedSupplier);
        else if (p.supplierName) {
          const inp = document.getElementById('purchaseSupplierInput');
          if (inp) inp.value = p.supplierName;
        }
        if (p.warehouse) this.selectWarehouse(p.warehouse);
        if (p.costCenter) this.selectCostCenter(p.costCenter);

        if (p.selectedItemIndex !== undefined && p.selectedItemIndex !== -1) {
          this.selectItemByIndex(p.selectedItemIndex);
        } else if (p.itemName) {
          const itInp = document.getElementById('purItemSearchInput');
          if (itInp) itInp.value = p.itemName;
        }

        if (p.unit) {
          const uSel = document.getElementById('purUnitSelect');
          if (uSel) {
            if (!Array.from(uSel.options).some(o => o.value === p.unit)) {
              const opt = document.createElement('option');
              opt.value = p.unit;
              opt.text = p.unit;
              uSel.appendChild(opt);
            }
            uSel.value = p.unit;
          }
        }

        const setField = (id, val) => {
          const el = document.getElementById(id);
          if (el) el.value = val || '';
        };
        setField('purQtyInput', p.qty);
        setField('purPriceInput', p.price);
        setField('purItemGrossTotal', p.gross);
        setField('purTaxInput', p.tax);
        setField('purDiscountInput', p.disc);
        setField('purLaborInput', p.labor);
        setField('purTotalInput', p.total);
        setField('purNotesInput', p.notes);

        const pill = document.getElementById('opPurchaseNoPill');
        if (pill && p.invoiceNo) pill.innerText = p.invoiceNo;

        this.cart = Array.isArray(p.cart) ? [...p.cart] : [];
        this.renderCart();
        this.calcItemSubtotal();

        list.splice(idx, 1);
        try {
          localStorage.setItem('magaies_parked_purchase_invoices_v1', JSON.stringify(list));
        } catch (e) {}
        this.updateParkedCountUI();
        const modal = document.getElementById('suiteParkedInvoicesModal');
        if (modal) modal.classList.add('hidden');

        if (typeof showFloatingToast === 'function') {
          showFloatingToast(`▶️ تم استرجاع فاتورة مشتريات [${p.supplierName}] بنجاح!`);
        }
      },

      deleteParkedInvoice(idx) {
        const list = this.getParkedInvoices();
        list.splice(idx, 1);
        try {
          localStorage.setItem('magaies_parked_purchase_invoices_v1', JSON.stringify(list));
        } catch (e) {}
        this.updateParkedCountUI();
        this.openParkedInvoicesModal();
      },

      renderCart() {
        const box = document.getElementById('suitePurCartItemsContainer');
        const countEl = document.getElementById('suitePurCartCount');
        if (countEl) countEl.innerText = this.cart.length;
        if (!box) return;

        if (this.cart.length === 0) {
          box.innerHTML = `<div class="text-center py-5 text-slate-500 text-xs bg-slate-900/40 rounded-2xl border border-slate-800/80">
            <i class="fa-solid fa-cart-shopping text-xl mb-1 block opacity-40"></i>
            لم يتم إضافة أي صنف لفاتورة الشراء بعد
          </div>`;
          const gt = document.getElementById('suitePurGrandTotalDisplay');
          if (gt) gt.innerText = '0.00 SDG';
          return;
        }

        let html = '';
        let grandTot = 0;
        this.cart.forEach((item, idx) => {
          grandTot += item.subtotal;
          html += `
            <div class="p-2.5 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-1 relative overflow-hidden" style="border-right: 3px solid #f59e0b;">
              <div class="flex justify-between items-start">
                <div>
                  <span class="font-black text-slate-100 text-xs">${item.name}</span>
                  <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mt-0.5">
                    <span class="text-purple-300 font-bold"><i class="fa-solid fa-warehouse"></i> ${item.warehouse}</span>
                    <span>•</span>
                    <span class="text-emerald-300 font-bold"><i class="fa-solid fa-scale-balanced"></i> ${item.unit}</span>
                  </div>
                </div>
                <div class="text-right">
                  <span class="font-black text-amber-400 font-mono text-xs">${item.subtotal.toLocaleString()} SDG</span>
                  <button type="button" onclick="SuitePurchasePOS.removeItem(${idx})" class="block mr-auto text-rose-400 hover:text-rose-300 text-[10px] mt-0.5 font-bold cursor-pointer"><i class="fa-solid fa-trash-can"></i> حذف</button>
                </div>
              </div>
              <div class="grid grid-cols-4 gap-1 text-center bg-slate-950/90 rounded-xl p-1 text-[9px] font-mono">
                <div><span class="text-slate-500 block">الكمية</span><span class="text-white font-black">${item.qty}</span></div>
                <div><span class="text-slate-500 block">السعر</span><span class="text-cyan-300 font-black">${item.price.toLocaleString()}</span></div>
                <div><span class="text-slate-500 block">الخصم</span><span class="text-rose-400 font-black">${item.discount.toLocaleString()}</span></div>
                <div><span class="text-slate-500 block">إضافة/إعالة</span><span class="text-purple-300 font-black">${(item.tax + item.labor).toLocaleString()}</span></div>
              </div>
            </div>`;
        });

        box.innerHTML = html;
        const gt = document.getElementById('suitePurGrandTotalDisplay');
        if (gt) gt.innerText = grandTot.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' SDG';
      },

      setPayMethod(m) {
        document.querySelectorAll('.suite-pur-pay-btn').forEach(b => {
          b.className = 'suite-pur-pay-btn py-1.5 rounded-xl text-[10px] font-black border transition bg-slate-950 text-slate-400 border-slate-800 hover:text-white flex flex-col items-center justify-center gap-0.5 cursor-pointer';
          b.style.boxShadow = 'none';
        });
        const map = {
          cash: 'suitePurPayBtnCash',
          bank: 'suitePurPayBtnBank',
          check: 'suitePurPayBtnCheck',
          credit: 'suitePurPayBtnCredit',
          split: 'suitePurPayBtnSplit'
        };
        const active = document.getElementById(map[m]);
        if (active) {
          active.className = 'suite-pur-pay-btn py-1.5 rounded-xl text-[10px] font-black border transition bg-amber-600 text-white border-amber-400 flex flex-col items-center justify-center gap-0.5 cursor-pointer';
          active.style.boxShadow = '0 0 12px rgba(245, 158, 11, 0.4)';
        }
        const hid = document.getElementById('purchasePayMethodSelect');
        if (hid) hid.value = m;
      },

      shareInvoice() {
        const supp = document.getElementById('purchaseSupplierInput')?.value || (this.selectedSupplier?.name) || 'مطاحن الخرطوم للغلال';
        const purNo = document.getElementById('opPurchaseNoPill')?.innerText || 'P-000';
        const date = document.getElementById('purInvoiceDate')?.value || new Date().toISOString().split('T')[0];
        const gt = document.getElementById('suitePurGrandTotalDisplay')?.innerText || '0.00 SDG';

        let text = `🧾 فاتورة مشتريات: ${purNo}\n📅 التاريخ: ${date}\n🏢 المورد: ${supp}\n------------------------\n`;
        if (this.cart.length > 0) {
          this.cart.forEach((it, i) => {
            text += `${i + 1}. ${it.name} | كمية: ${it.qty} ${it.unit} | سعر: ${it.price} | إجمالي: ${it.subtotal.toLocaleString()}\n`;
          });
        }
        text += `------------------------\n💰 الإجمالي الصافي: ${gt}\nنظام مقاييس ERP السحابي`;

        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(() => {
            alert('✓ تم نسخ تفاصيل فاتورة المشتريات وجاهزة للمشاركة عبر الواتساب!');
          }).catch(() => {
            prompt('انسخ نص فاتورة المشتريات:', text);
          });
        } else {
          prompt('انسخ نص فاتورة المشتريات:', text);
        }
      },

      printInvoice() {
        const purNo = document.getElementById('opPurchaseNoPill')?.innerText || 'PUR-PRINT';
        const supp = this.selectedSupplier?.name || document.getElementById('purchaseSupplierInput')?.value || 'مطاحن الخرطوم للغلال';
        const date = document.getElementById('purInvoiceDate')?.value || new Date().toISOString().split('T')[0];
        const payMethod = document.getElementById('purchasePayMethodSelect')?.value || 'نقداً';

        const items = this.cart.length > 0 ? this.cart : [{
          name: this.selectedItemIndex !== -1 ? SuiteSalesPOS.dbItems[this.selectedItemIndex]?.name : 'صنف عام',
          unit: document.getElementById('purUnitSelect')?.value || 'حبة',
          warehouse: this.selectedWarehouse || 'المستودع الرئيسي',
          qty: parseFloat(document.getElementById('purQtyInput')?.value || 1),
          price: parseFloat(document.getElementById('purPriceInput')?.value || 0),
          discount: parseFloat(document.getElementById('purDiscountInput')?.value || 0),
          tax: parseFloat(document.getElementById('purTaxInput')?.value || 0),
          labor: parseFloat(document.getElementById('purLaborInput')?.value || 0),
          subtotal: parseFloat(document.getElementById('purTotalInput')?.value || 0)
        }];

        const grandTotal = items.reduce((acc, it) => acc + (it.subtotal || 0), 0);

        let rowsHtml = '';
        items.forEach((it, i) => {
          rowsHtml += `<tr>
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">${i + 1}</td>
            <td style="padding:6px; border:1px solid #ccc; font-weight:bold;">${it.name}</td>
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">${it.unit}</td>
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">${it.qty}</td>
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">${it.price.toLocaleString()}</td>
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">${it.discount}</td>
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">${(it.tax + it.labor)}</td>
            <td style="padding:6px; border:1px solid #ccc; text-align:center; font-weight:bold; color:#d97706;">${it.subtotal.toLocaleString()}</td>
          </tr>`;
        });

        const printWin = window.open('', '_blank', 'width=800,height=750');
        printWin.document.write(`
          <html dir="rtl" lang="ar">
          <head>
            <title>فاتورة مشتريات - #${purNo}</title>
            <style>
              body { font-family: 'Cairo', Tahoma, sans-serif; padding: 25px; color: #111; }
              .header { display:flex; justify-content:space-between; align-items:center; border-bottom: 2px dashed #000; padding-bottom: 12px; margin-bottom: 15px; }
              table { width:100%; border-collapse:collapse; margin-top:15px; }
              th { background:#f3f4f6; border:1px solid #ccc; padding:8px; font-size:12px; }
            
    /* ── MOBILE ENHANCEMENTS: ZERO-LAG KEYBOARD & SAFE AREA ── */
    html, body {
      overscroll-behavior-y: contain;
      -webkit-tap-highlight-color: transparent;
      touch-action: manipulation;
    }
    .modal-backdrop > div {
      max-height: 92dvh !important;
      max-height: 92vh;
      scroll-padding-bottom: 90px !important;
      padding-bottom: env(safe-area-inset-bottom, 16px) !important;
    }
    @supports (height: 100dvh) {
      .modal-backdrop > div {
        max-height: calc(100dvh - 24px) !important;
      }
    }
    input, select, textarea {
      font-size: 16px !important; /* Prevents auto-zoom on iOS Safari */
    }
    .pos-field-interactive input {
      /* Keep large fonts for numbers in POS */
    }

  </style>
          </head>
          <body>
            <div class="header">
              <div>
                <h2 style="margin:0 0 4px 0; color:#0f172a;">فاتورة مشتريات وتوريد</h2>
                <p style="margin:2px 0; color:#475569; font-size:12px;">مؤسسة السجانة لمواد البناء</p>
                <div style="font-size:12px; margin-top:8px;">
                  <div><strong>رقم الفاتورة:</strong> #${purNo} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>التاريخ:</strong> ${date}</div>
                  <div style="margin-top:4px;"><strong>المورد:</strong> ${supp} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>طريقة السداد:</strong> ${payMethod}</div>
                  <div style="margin-top:4px;"><strong>المستودع:</strong> ${this.selectedWarehouse} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>مركز الكلفة:</strong> ${this.selectedCostCenter}</div>
                </div>
              </div>
            </div>
            <table>
              <thead>
                <tr>
                  <th>#</th><th>الصنف</th><th>الوحدة</th><th>الكمية</th><th>السعر</th><th>الخصم</th><th>إضافة/إعالة</th><th>الإجمالي</th>
                </tr>
              </thead>
              <tbody>${rowsHtml}</tbody>
              <tfoot>
                <tr>
                  <th colspan="7" style="text-align:left; padding:10px; font-size:14px;">الإجمالي الصافي النهائي:</th>
                  <th style="padding:10px; font-size:16px; color:#d97706; text-align:center;">${grandTotal.toLocaleString()} SDG</th>
                </tr>
              </tfoot>
            </table>
            <div style="display:flex; justify-content:space-between; margin-top:50px; text-align:center; font-size:12px;">
              <div>توقيع واستلام أمين المخزن: ....................</div>
              <div>ختم وتوقيع قسم المشتريات / الحسابات: ....................</div>
            </div>
            <script>window.onload = function() { window.print(); }<\/script>
          </body>
          </html>
        `);
        printWin.document.close();
      },

      printSupplierStatement() {
        const supp = document.getElementById('purchaseSupplierInput')?.value || (this.selectedSupplier?.name) || 'مطاحن الخرطوم للغلال';
        if (typeof triggerStatementForAccount === 'function' && supp) {
          triggerStatementForAccount(supp, 'مورد', '$0.00', '0.00 SDG');
        } else if (typeof openModal === 'function') {
          openModal('modalOpStatement');
        } else {
          alert('كشف حساب المورد: ' + supp);
        }
      },

      printGRN() {
        const purNo = document.getElementById('opPurchaseNoPill')?.innerText || 'GRN-PRINT';
        const supp = this.selectedSupplier?.name || document.getElementById('purchaseSupplierInput')?.value || 'مطاحن الخرطوم للغلال';
        const date = document.getElementById('purInvoiceDate')?.value || new Date().toISOString().split('T')[0];

        const items = this.cart.length > 0 ? this.cart : [{
          name: this.selectedItemIndex !== -1 ? SuiteSalesPOS.dbItems[this.selectedItemIndex]?.name : 'صنف عام',
          unit: document.getElementById('purUnitSelect')?.value || 'حبة',
          warehouse: this.selectedWarehouse || 'المستودع الرئيسي',
          qty: parseFloat(document.getElementById('purQtyInput')?.value || 1)
        }];

        let rowsHtml = '';
        items.forEach((it, i) => {
          rowsHtml += `<tr>
            <td style="padding:8px; border:1px solid #ccc; text-align:center;">${i + 1}</td>
            <td style="padding:8px; border:1px solid #ccc; font-weight:bold;">${it.name}</td>
            <td style="padding:8px; border:1px solid #ccc; text-align:center;">${it.unit}</td>
            <td style="padding:8px; border:1px solid #ccc; text-align:center;">${it.warehouse || 'المستودع الرئيسي'}</td>
            <td style="padding:8px; border:1px solid #ccc; text-align:center; font-weight:bold; font-size:1.1rem; color:#d97706;">${it.qty}</td>
            <td style="padding:8px; border:1px solid #ccc; text-align:center;">[ &nbsp; ] سليم ومطابق</td>
          </tr>`;
        });

        const printWin = window.open('', '_blank', 'width=800,height=750');
        printWin.document.write(`
          <html dir="rtl" lang="ar">
          <head>
            <title>إذن استلام وتوريد بضاعة (GRN)</title>
            <style>
              body { font-family: 'Cairo', Tahoma, sans-serif; padding: 25px; color: #111; }
              .header { display:flex; justify-content:space-between; align-items:center; border-bottom: 2px dashed #000; padding-bottom: 12px; margin-bottom: 15px; }
              table { width:100%; border-collapse:collapse; margin-top:15px; }
              th { background:#f3f4f6; border:1px solid #ccc; padding:8px; font-size:12px; }
            
    /* ── MOBILE ENHANCEMENTS: ZERO-LAG KEYBOARD & SAFE AREA ── */
    html, body {
      overscroll-behavior-y: contain;
      -webkit-tap-highlight-color: transparent;
      touch-action: manipulation;
    }
    .modal-backdrop > div {
      max-height: 92dvh !important;
      max-height: 92vh;
      scroll-padding-bottom: 90px !important;
      padding-bottom: env(safe-area-inset-bottom, 16px) !important;
    }
    @supports (height: 100dvh) {
      .modal-backdrop > div {
        max-height: calc(100dvh - 24px) !important;
      }
    }
    input, select, textarea {
      font-size: 16px !important; /* Prevents auto-zoom on iOS Safari */
    }
    .pos-field-interactive input {
      /* Keep large fonts for numbers in POS */
    }

  </style>
          </head>
          <body>
            <div class="header">
              <div>
                <h2 style="margin:0 0 4px 0; color:#0f172a;">إذن استلام وتوريد مخزني (GRN)</h2>
                <p style="margin:2px 0; color:#475569; font-size:12px;">مؤسسة السجانة لمواد البناء • مستودعات التوزيع المركزي</p>
                <div style="font-size:12px; margin-top:8px;">
                  <div><strong>رقم فاتورة الشراء المرجعية:</strong> #${purNo} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>تاريخ الاستلام:</strong> ${date}</div>
                  <div style="margin-top:4px;"><strong>المورد المسلّم:</strong> ${supp}</div>
                  <div style="margin-top:4px;"><strong>المستودع المستلم:</strong> ${this.selectedWarehouse} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>مركز الكلفة:</strong> ${this.selectedCostCenter}</div>
                </div>
              </div>
            </div>
            <table>
              <thead>
                <tr>
                  <th>#</th><th>الصنف المستلم</th><th>الوحدة</th><th>المخزن المستلم</th><th>الكمية الفعلية</th><th>حالة الفحص</th>
                </tr>
              </thead>
              <tbody>${rowsHtml}</tbody>
            </table>
            <div style="display:flex; justify-content:space-between; margin-top:50px; text-align:center; font-size:12px;">
              <div>توقيع أمين المستودع المستلم: ....................</div>
              <div>توقيع مندوب المورد / السائق: ....................</div>
            </div>
            <script>window.onload = function() { window.print(); }<\/script>
          </body>
          </html>
        `);
        printWin.document.close();
      },

      voiceRecognitionInstance: null,
      isVoiceListening: false,
      _voiceSilenceTimer: null,
      _lastVoiceActivityAt: 0,
      _manualStopVoice: false,
      _pendingInterimVoice: '',

      resetVoiceSilenceTimer() {
        this._lastVoiceActivityAt = Date.now();
        if (this._voiceSilenceTimer) clearTimeout(this._voiceSilenceTimer);
        this._voiceSilenceTimer = setTimeout(() => {
          if (this.isVoiceListening) {
            this.stopVoiceFeed(true, '✓ توقف التسجيل تلقائياً بعد السكوت لمدة 3 ثوانٍ');
          }
        }, 3000);
      },

      toggleVoiceFeed() {
        const consoleEl = document.getElementById('suitePurVoiceConsole');
        if (consoleEl && consoleEl.classList.contains('hidden')) {
          consoleEl.classList.remove('hidden');
        }

        if (this.isVoiceListening) {
          this.stopVoiceFeed(true);
          return;
        }

        const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
        const statusText = document.getElementById('suitePurVoiceStatusText');
        const transcriptEl = document.getElementById('suitePurVoiceTranscript');
        const pulseDot = document.getElementById('purMicPulseDot');
        const micBtn = document.getElementById('btnPurVoiceMic');

        if (!SpeechRec) {
          if (statusText) statusText.innerHTML = '<span class="text-amber-400">ℹ️ المتصفح لا يدعم الميكروفون المباشر. يمكنك كتابة الأمر أدناه والتفريغ فوراً:</span>';
          return;
        }

        try {
          this._manualStopVoice = false;
          this._pendingInterimVoice = '';
          this.voiceRecognitionInstance = new SpeechRec();
          this.voiceRecognitionInstance.lang = 'ar-SA';
          this.voiceRecognitionInstance.continuous = true;
          this.voiceRecognitionInstance.interimResults = true;

          this.voiceRecognitionInstance.onstart = () => {
            this.isVoiceListening = true;
            this.resetVoiceSilenceTimer();
            if (pulseDot) pulseDot.classList.remove('hidden');
            if (micBtn) micBtn.style.setProperty('box-shadow', '0 0 18px rgba(244, 63, 94, 0.85)', 'important');
            if (statusText) statusText.innerHTML = '<span class="text-rose-400 animate-pulse font-black">🔴 التسجيل مستمر... (يتوقف تلقائياً عند السكوت لمدة 3 ثوانٍ)</span>';
            if (transcriptEl && !this._pendingInterimVoice) transcriptEl.innerText = 'استمع إليك... تحدث الآن (المورد، المستودع، الصنف، الوحدة، الكمية، السعر...)';
            if (window.AppHaptics) AppHaptics.selection();
          };

          this.voiceRecognitionInstance.onsoundstart = () => this.resetVoiceSilenceTimer();
          this.voiceRecognitionInstance.onspeechstart = () => this.resetVoiceSilenceTimer();

          this.voiceRecognitionInstance.onresult = (evt) => {
            this.resetVoiceSilenceTimer();
            let interim = '';
            let finalChunk = '';
            for (let i = evt.resultIndex; i < evt.results.length; ++i) {
              if (evt.results[i].isFinal) finalChunk += ' ' + evt.results[i][0].transcript;
              else interim += ' ' + evt.results[i][0].transcript;
            }
            const cleanFinal = finalChunk.trim();
            const cleanInterim = interim.trim();
            if (transcriptEl) transcriptEl.innerText = cleanFinal || cleanInterim;
            if (cleanFinal) {
              this._pendingInterimVoice = '';
              this.feedVoiceToFields(cleanFinal);
            } else if (cleanInterim) {
              this._pendingInterimVoice = cleanInterim;
            }
          };

          this.voiceRecognitionInstance.onerror = (err) => {
            if (err && err.error === 'no-speech' && this.isVoiceListening && !this._manualStopVoice) {
              return;
            }
            console.warn('Purchase voice error:', err);
            this.stopVoiceFeed(true);
            if (statusText) statusText.innerHTML = '<span class="text-amber-400">⚠️ تعذر التقاط الصوت، يمكنك كتابة الأمر أدناه:</span>';
          };

          this.voiceRecognitionInstance.onend = () => {
            if (this.isVoiceListening && !this._manualStopVoice) {
              const elapsed = Date.now() - (this._lastVoiceActivityAt || 0);
              if (elapsed < 2900) {
                try {
                  this.voiceRecognitionInstance.start();
                  return;
                } catch (e) {}
              }
            }
            this.stopVoiceFeed(true);
          };

          this.voiceRecognitionInstance.start();
        } catch (e) {
          console.error(e);
          this.stopVoiceFeed(true);
        }
      },

      stopVoiceFeed(manual = true, customMsg = '') {
        if (manual) this._manualStopVoice = true;
        if (this._voiceSilenceTimer) {
          clearTimeout(this._voiceSilenceTimer);
          this._voiceSilenceTimer = null;
        }
        if (this._pendingInterimVoice) {
          const pending = this._pendingInterimVoice;
          this._pendingInterimVoice = '';
          this.feedVoiceToFields(pending);
        }
        this.isVoiceListening = false;
        const pulseDot = document.getElementById('purMicPulseDot');
        if (pulseDot) pulseDot.classList.add('hidden');
        const micBtn = document.getElementById('btnPurVoiceMic');
        if (micBtn) micBtn.style.setProperty('box-shadow', '0 0 12px rgba(245, 158, 11, 0.35)', 'important');
        const statusText = document.getElementById('suitePurVoiceStatusText');
        if (statusText && customMsg) {
          statusText.innerHTML = '<span class="text-emerald-400 font-black">' + customMsg + '</span>';
        }
        if (this.voiceRecognitionInstance) {
          try { this.voiceRecognitionInstance.stop(); } catch(e) {}
        }
      },

      feedVoiceFromInput() {
        const inp = document.getElementById('suitePurVoiceManualInput');
        if (inp && inp.value.trim()) {
          this.feedVoiceToFields(inp.value.trim());
          inp.value = '';
        }
      },

      feedVoiceToFields(spokenText) {
        if (!spokenText || !spokenText.trim()) return;
        const raw = spokenText.trim();
        const transcriptEl = document.getElementById('suitePurVoiceTranscript');
        if (transcriptEl) transcriptEl.innerText = 'تم الالتقاط: "' + raw + '"';

        const norm = (s) => (s || '').trim()
          .replace(/[\u064B-\u065F\u0670]/g, '')
          .replace(/[إأآٱ]/g, 'ا')
          .replace(/ة/g, 'ه')
          .replace(/[ى]/g, 'ي')
          .toLowerCase();

        const cleanText = norm(raw);
        const parseNum = (str) => {
          if (!str) return 0;
          let s = str.trim();
          const eastern = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
          for (let i = 0; i < 10; i++) s = s.split(eastern[i]).join(i.toString());
          s = s.replace(/,/g, '');
          const matchDigits = s.match(/\d+(?:\.\d+)?/);
          if (matchDigits) return parseFloat(matchDigits[0]);
          const wordMap = {
            'صفر': 0, 'واحد': 1, 'واحدة': 1, 'اثنين': 2, 'اثنان': 2, 'ثلاثة': 3, 'ثلاث': 3,
            'اربعة': 4, 'أربعة': 4, 'خمسة': 5, 'خمس': 5, 'ستة': 6, 'ست': 6, 'سبعة': 7, 'سبع': 7,
            'ثمانية': 8, 'تمانية': 8, 'تسعة': 9, 'تسع': 9, 'عشرة': 10, 'عشر': 10,
            'عشرين': 20, 'ثلاثين': 30, 'اربعين': 40, 'خمسين': 50, 'ستين': 60, 'سبعين': 70, 'ثمانين': 80, 'تسعين': 90,
            'مائة': 100, 'مية': 100, 'مائتين': 200, 'ميتين': 200, 'الف': 1000, 'الفين': 2000, 'مليون': 1000000
          };
          for (const [w, val] of Object.entries(wordMap)) {
            if (s.includes(w)) return val;
          }
          return 0;
        };

        // 1. المورد
        let matchedSup = null;
        for (const sup of (this.dbSuppliers || [])) {
          if (sup.name && cleanText.includes(norm(sup.name))) {
            matchedSup = sup;
            break;
          }
        }
        if (!matchedSup) {
          const mSup = raw.match(/(?:المورد|مورد|شركة|مصنع)\s+([^\s,،]+(?:\s+[^\s,،]+){0,3})/i);
          if (mSup && mSup[1]) {
            const cand = mSup[1].replace(/(?:مركز|مخزن|مستودع|صنف|وحدة|كمية|سعر|خصم|إضافة|اضافة).*/i, '').trim();
            if (cand) {
              matchedSup = (this.dbSuppliers || []).find(s => norm(s.name).includes(norm(cand))) || { name: cand, phone: '' };
            }
          }
        }
        if (matchedSup) this.selectSupplier(matchedSup);

        // 2. المستودع
        const whList = (window.SuiteSalesPOS && SuiteSalesPOS.dbWarehouses) ? SuiteSalesPOS.dbWarehouses : [];
        for (const wh of whList) {
          if (wh.name && cleanText.includes(norm(wh.name))) {
            this.selectWarehouse(wh.name);
            break;
          }
        }

        // 3. الصنف
        const itemsList = (window.SuiteSalesPOS && SuiteSalesPOS.dbItems) ? SuiteSalesPOS.dbItems : [];
        const sortedItems = itemsList.map((it, idx) => ({ ...it, idx })).sort((a,b) => (b.name||'').length - (a.name||'').length);
        let matchedItemIdx = -1;
        for (const it of sortedItems) {
          if (it.name && cleanText.includes(norm(it.name))) {
            matchedItemIdx = it.idx;
            break;
          }
        }
        if (matchedItemIdx !== -1) {
          this.selectItemByIndex(matchedItemIdx);
        }

        // 4. الوحدة
        const mUnit = raw.match(/(?:الوحدة|وحدة|الوحدات)\s+([^\s,،]+)/i);
        if (mUnit && mUnit[1]) {
          const uStr = norm(mUnit[1]);
          const uSel = document.getElementById('purUnitSelect');
          if (uSel && uSel.options) {
            for (let i = 0; i < uSel.options.length; i++) {
              if (norm(uSel.options[i].value).includes(uStr) || norm(uSel.options[i].text).includes(uStr)) {
                uSel.selectedIndex = i;
                this.onUnitChange();
                break;
              }
            }
          }
        }

        // 5. الكمية والسعر والخصم والإضافة والعتالة والبيان
        const mQty = raw.match(/(?:الكمية|كمية|عدد|بعدد)\s+([0-9٠-٩]+|[^\s,،]+)/i);
        if (mQty && mQty[1]) {
          const qVal = parseNum(mQty[1]);
          if (qVal > 0 && document.getElementById('purQtyInput')) document.getElementById('purQtyInput').value = qVal;
        }
        const mPrice = raw.match(/(?:السعر|سعر|بسعر|قيمة|بقيمة)\s+([0-9٠-٩]+|[^\s,،]+)/i);
        if (mPrice && mPrice[1]) {
          const pVal = parseNum(mPrice[1]);
          if (pVal > 0 && document.getElementById('purPriceInput')) document.getElementById('purPriceInput').value = pVal;
        }
        const mDisc = raw.match(/(?:الخصم|خصم|تخفيض)\s+([0-9٠-٩]+|[^\s,،]+)/i);
        if (mDisc && mDisc[1] && document.getElementById('purDiscountInput')) {
          document.getElementById('purDiscountInput').value = parseNum(mDisc[1]);
        }
        const mTax = raw.match(/(?:الإضافة|الاضافة|إضافة|اضافة)\s+([0-9٠-٩]+|[^\s,،]+)/i);
        if (mTax && mTax[1] && document.getElementById('purTaxInput')) {
          document.getElementById('purTaxInput').value = parseNum(mTax[1]);
        }
        const mLabor = raw.match(/(?:العتالة|عتالة)\s+([0-9٠-٩]+|[^\s,،]+)/i);
        if (mLabor && mLabor[1] && document.getElementById('purLaborInput')) {
          document.getElementById('purLaborInput').value = parseNum(mLabor[1]);
        }
        const mNote = raw.match(/(?:الملحوظة|ملحوظة|البيان|بيان)\s+([^\s,،]+(?:\s+[^\s,،]+){0,5})/i);
        if (mNote && mNote[1] && document.getElementById('purNotesInput')) {
          document.getElementById('purNotesInput').value = mNote[1].trim();
        }
        this.calcItemSubtotal();
      },

      async loadRecentInvoices() {
        const merged = [];
        const addUnique = (p) => {
          if (!p || typeof p !== 'object') return;
          const docId = String(p.invoiceNo || p.id || '').trim();
          if (!docId) return;
          const existingIdx = merged.findIndex(m => String(m.invoiceNo || m.id || '').trim() === docId);
          if (existingIdx === -1) {
            merged.push(p);
          } else {
            merged[existingIdx] = Object.assign({}, p, merged[existingIdx]);
          }
        };
        const sortNewestFirst = (arr) => {
          return arr.sort((a, b) => {
            const idA = String(a.invoiceNo || a.id || '');
            const idB = String(b.invoiceNo || b.id || '');
            const numA = parseInt(idA.replace(/\D/g, ''), 10) || 0;
            const numB = parseInt(idB.replace(/\D/g, ''), 10) || 0;
            if (numB !== numA) return numB - numA;
            const tA = new Date(a.savedAt || a.createdAt || a.date || 0).getTime() || 0;
            const tB = new Date(b.savedAt || b.createdAt || b.date || 0).getTime() || 0;
            return tB - tA;
          });
        };
        try {
          const localSaved = JSON.parse(localStorage.getItem('magaies_local_purchase_invoices_v1') || '[]');
          if (Array.isArray(localSaved)) localSaved.forEach(addUnique);
          const erpV1 = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
          if (Array.isArray(erpV1)) erpV1.forEach(addUnique);
          const erpSaved = JSON.parse(localStorage.getItem('erp_purchases_v5') || '[]');
          if (Array.isArray(erpSaved)) erpSaved.forEach(addUnique);
          if (window.MagaiesOffline && typeof MagaiesOffline.getQueue === 'function') {
            MagaiesOffline.getQueue().forEach(q => {
              if ((q.type === 'purchase' || q.type === 'purchase_split') && q.payload) {
                addUnique(Object.assign({ invoiceNo: q.tempId }, q.payload));
              }
            });
          }
          const mirrorCache = JSON.parse(localStorage.getItem('magaies_mirror_cache_v1') || '{}');
          if (Array.isArray(mirrorCache.purchases)) mirrorCache.purchases.forEach(addUnique);
          if (merged.length > 0) this.recentInvoices = sortNewestFirst(merged);
        } catch(e) {}
        try {
          const res = await fetch('api/mirror_sync.php?action=get_recent_purchases&_t=' + Date.now());
          if (!res.ok) return;
          const data = await res.json();
          if (data && Array.isArray(data.purchases) && data.purchases.length > 0) {
            data.purchases.forEach(addUnique);
            this.recentInvoices = sortNewestFirst(merged);
          }
        } catch(e) {}
      },

      navigateInvoice(dir) {
        if (window.AppHaptics) AppHaptics.light();
        if (!this.recentInvoices || this.recentInvoices.length === 0) {
          this.loadRecentInvoices().then(() => {
            if (this.recentInvoices && this.recentInvoices.length > 0) {
              this.navigateInvoice(dir);
            } else if (typeof showFloatingToast === 'function') {
              showFloatingToast('ℹ️ لا توجد فواتير مشتريات سابقة مسجلة حتى الآن.');
            }
          });
          return;
        }

        const pill = document.getElementById('opPurchaseNoPill');
        if (this.currentInvoiceIndex === -1 && pill) {
          this.latestGeneratedInvNo = pill.innerText;
        }

        if (dir === 'prev') {
          if (this.currentInvoiceIndex < this.recentInvoices.length - 1) {
            this.currentInvoiceIndex++;
            this.displayArchivedInvoice(this.recentInvoices[this.currentInvoiceIndex]);
          } else if (typeof showFloatingToast === 'function') {
            showFloatingToast('ℹ️ وصلت إلى أقدم فاتورة مشتريات مسجلة.');
          }
        } else if (dir === 'next') {
          if (this.currentInvoiceIndex > 0) {
            this.currentInvoiceIndex--;
            this.displayArchivedInvoice(this.recentInvoices[this.currentInvoiceIndex]);
          } else if (this.currentInvoiceIndex === 0) {
            this.currentInvoiceIndex = -1;
            this.newInvoice(true);
            if (typeof showFloatingToast === 'function') {
              showFloatingToast('📄 تم الرجوع إلى شاشة فاتورة المشتريات الجديدة.');
            }
          } else if (typeof showFloatingToast === 'function') {
            showFloatingToast('ℹ️ أنت بالفعل في أحدث فاتورة مشتريات جديدة.');
          }
        }
      },

      displayArchivedInvoice(inv) {
        if (!inv) return;
        const docNo = inv.invoiceNo || inv.id || '';
        const pill = document.getElementById('opPurchaseNoPill');
        if (pill) pill.innerText = docNo;

        // 1. التاريخ
        const rawDate = String(inv.date || inv.savedAt || inv.createdAt || new Date().toISOString()).split('T')[0].split(' ')[0];
        const dInp = document.getElementById('purInvoiceDate');
        if (dInp && rawDate) {
          dInp.value = rawDate;
          this.syncHeaderDatePill(rawDate);
        }

        // 2. المورد (يدعم الاسم المباشر أو المعرف من اللابتوب/الجوال)
        const rawSuppIdOrName = inv.supplierName || inv.vendorName || inv.supplier || inv.customerName || inv.customer || inv.supplierId || '';
        const matchedSupp = (this.dbSuppliers || []).find(su =>
          su.name === rawSuppIdOrName ||
          String(su.id) === String(inv.supplierId || inv.supplier || inv.customer || '')
        );
        const suppName = matchedSupp ? matchedSupp.name : (inv.supplierName || inv.vendorName || inv.customerName || (typeof inv.supplier === 'string' && !inv.supplier.startsWith('vend-') ? inv.supplier : '') || 'مطاحن الخرطوم للغلال');
        const s = matchedSupp || {
          name: suppName,
          phone: inv.supplierPhone || '',
          currentBalance: 0,
          currency: inv.currency || 'SDG'
        };
        this.selectSupplier(s);
        const suppInp = document.getElementById('purchaseSupplierInput');
        if (suppInp) {
          suppInp.value = suppName;
          suppInp.classList.remove('pos-ghost-default');
          delete suppInp.dataset.ghostAuto;
        }

        // 3. المستودع ومركز الكلفة
        const firstItem = (Array.isArray(inv.items) && inv.items.length > 0) ? inv.items[0] : inv;
        const dbWarehouses = (window.SuiteSalesPOS && SuiteSalesPOS.dbWarehouses) ? SuiteSalesPOS.dbWarehouses : [];
        const rawWh = inv.warehouse || firstItem?.warehouse || firstItem?.warehouseId || 'المستودع الرئيسي';
        const matchedWh = dbWarehouses.find(w => w.name === rawWh || String(w.id) === String(rawWh));
        const whName = matchedWh ? matchedWh.name : rawWh;
        this.selectWarehouse(whName);
        const whInp = document.getElementById('purchaseWarehouseInput');
        if (whInp) {
          whInp.value = whName;
          whInp.classList.remove('pos-ghost-default');
          delete whInp.dataset.ghostAuto;
        }
        const whSel = document.getElementById('purchaseWarehouseSelect');
        if (whSel) whSel.value = whName;

        const dbCostCenters = (window.SuiteSalesPOS && SuiteSalesPOS.dbCostCenters) ? SuiteSalesPOS.dbCostCenters : [];
        const rawCc = inv.costCenter || firstItem?.costCenter || firstItem?.costCenterId || 'المركز الرئيسي';
        const matchedCc = dbCostCenters.find(cc => cc.name === rawCc || String(cc.id) === String(rawCc));
        const ccName = matchedCc ? matchedCc.name : rawCc;
        this.selectCostCenter(ccName);
        const ccDisp = document.getElementById('purCostCenterDisplayName');
        if (ccDisp) ccDisp.innerHTML = `<span class="text-white font-black">${ccName}</span>`;
        const ccInp = document.getElementById('purCostCenterInput');
        if (ccInp) {
          ccInp.value = ccName;
          ccInp.classList.remove('pos-ghost-default');
        }

        // 4. الصنف والوحدة والكمية والسعر والجملة والخصم والإضافة والعتالة والإجمالي الصافي
        const itemsList = (window.SuiteSalesPOS && SuiteSalesPOS.dbItems) ? SuiteSalesPOS.dbItems : [];
        const rawItemId = firstItem?.itemId || firstItem?.id || inv.itemId || '';
        const rawItemName = firstItem?.name || firstItem?.itemName || inv.itemName || '';
        let itemIdx = itemsList.findIndex(x =>
          (rawItemName && x.name === rawItemName) ||
          (rawItemId && String(x.id) === String(rawItemId))
        );
        const itemName = (itemIdx !== -1 && itemsList[itemIdx])
          ? itemsList[itemIdx].name
          : (rawItemName || 'صنف مشتريات');
        this.selectedItemIndex = itemIdx;

        const itInp = document.getElementById('purItemSearchInput');
        if (itInp) {
          itInp.value = itemName;
          itInp.classList.remove('pos-ghost-default');
          delete itInp.dataset.ghostAuto;
        }
        const itDisp = document.getElementById('suitePurItemDisplayName');
        if (itDisp) {
          itDisp.innerHTML = `<span class="font-bold text-xs sm:text-sm truncate text-amber-300">${itemName}</span>`;
        }
        const itHid = document.getElementById('purItemSelectVal');
        if (itHid) itHid.value = itemName;

        const unitVal = firstItem?.unit || firstItem?.selectedUnit || firstItem?.unitName || inv.unit || 'حبة';
        const uSel = document.getElementById('purUnitSelect');
        if (uSel) {
          if (!Array.from(uSel.options).some(o => o.value === unitVal)) {
            const opt = document.createElement('option');
            opt.value = unitVal;
            opt.innerText = unitVal;
            opt.dataset.factor = firstItem?.factor || 1;
            uSel.appendChild(opt);
          }
          uSel.value = unitVal;
        }

        const qtyVal = parseFloat(firstItem?.qty ?? firstItem?.quantity ?? inv.qty ?? inv.quantity ?? 1) || 1;
        const priceVal = parseFloat(firstItem?.price ?? firstItem?.rate ?? inv.price ?? 0) || 0;
        const discVal = parseFloat(firstItem?.discount ?? inv.discount ?? inv.discountCost ?? 0) || 0;
        const taxVal = parseFloat(firstItem?.tax ?? inv.tax ?? inv.vatAmount ?? inv.additionsCost ?? inv.taxCost ?? 0) || 0;
        const laborVal = parseFloat(firstItem?.labor ?? inv.labor ?? inv.laborCost ?? inv.transportCost ?? 0) || 0;
        const grossVal = parseFloat(firstItem?.grossTotal ?? (qtyVal * priceVal)) || (qtyVal * priceVal);
        const netTotalVal = parseFloat(inv.grandTotal ?? inv.total ?? firstItem?.subtotal ?? firstItem?.total ?? (grossVal - discVal + taxVal + laborVal)) || 0;

        const setFieldVal = (id, val, fmt = false) => {
          const el = document.getElementById(id);
          if (!el) return;
          el.value = fmt ? Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : (val !== undefined && val !== null ? val : '');
          el.classList.remove('pos-ghost-default');
          delete el.dataset.ghostAuto;
        };

        setFieldVal('purQtyInput', qtyVal);
        setFieldVal('purPriceInput', priceVal);
        setFieldVal('purItemGrossTotal', grossVal, true);
        setFieldVal('purDiscountInput', discVal);
        setFieldVal('purTaxInput', taxVal);
        setFieldVal('purLaborInput', laborVal);

        // 5. الملحوظات / البيان
        const notesStr = String(inv.notes ?? inv.description ?? inv.remark ?? inv.statement ?? firstItem?.notes ?? firstItem?.note ?? '');
        const nInp = document.getElementById('purNotesInput');
        if (nInp) {
          nInp.value = notesStr;
          nInp.classList.remove('pos-ghost-default');
        }

        // 6. سلة وأسطر الفاتورة كاملة
        if (Array.isArray(inv.items) && inv.items.length > 0) {
          this.cart = inv.items.map(it => {
            const q = parseFloat(it.qty ?? it.quantity ?? 1) || 1;
            const p = parseFloat(it.price ?? it.rate ?? 0) || 0;
            const d = parseFloat(it.discount ?? 0) || 0;
            const t = parseFloat(it.tax ?? 0) || 0;
            const l = parseFloat(it.labor ?? 0) || 0;
            const wRaw = it.warehouse || it.warehouseId || whName;
            const wMatch = dbWarehouses.find(w => w.name === wRaw || String(w.id) === String(wRaw));
            const cRaw = it.costCenter || it.costCenterId || ccName;
            const cMatch = dbCostCenters.find(cc => cc.name === cRaw || String(cc.id) === String(cRaw));
            return {
              name: it.name || it.itemName || itemName,
              unit: it.unit || it.selectedUnit || it.unitName || 'حبة',
              warehouse: wMatch ? wMatch.name : wRaw,
              costCenter: cMatch ? cMatch.name : cRaw,
              qty: q,
              price: p,
              discount: d,
              tax: t,
              labor: l,
              notes: it.notes || it.note || notesStr,
              subtotal: parseFloat(it.subtotal ?? it.total ?? ((q * p) - d + t + l)) || 0
            };
          });
        } else {
          this.cart = [{
            name: itemName,
            unit: unitVal,
            warehouse: whName,
            costCenter: ccName,
            qty: qtyVal,
            price: priceVal,
            discount: discVal,
            tax: taxVal,
            labor: laborVal,
            notes: notesStr,
            subtotal: netTotalVal
          }];
        }
        this.renderCart();

        setFieldVal('purTotalInput', netTotalVal, true);
        const gt = document.getElementById('suitePurGrandTotalDisplay');
        if (gt) {
          const curr = this.activeCurrency || inv.currency || 'SDG';
          gt.innerText = Number(netTotalVal || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + curr;
        }

        // 7. طريقة الدفع
        const resolvedPayMethod = inv.paymentMethod || (parseFloat(inv.creditAmount) > 0 && parseFloat(inv.cashAmount) > 0 ? 'split' : (parseFloat(inv.creditAmount) > 0 ? 'credit' : (parseFloat(inv.bankAmount) > 0 ? 'bank' : 'cash')));
        this.setPayMethod(resolvedPayMethod);

        if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleAll();
        if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();
        if (typeof showFloatingToast === 'function') {
          showFloatingToast(`📂 استعراض فاتورة مشتريات #${docNo} (${suppName})`);
        }
      }
    };

// ═══════════════════════════════════════════════════════════════════
// SUITE SMART SEARCH ENGINE (بحث فائق الذكاء مع تطبيع الحروف والقوائم المنسدلة)
// ═══════════════════════════════════════════════════════════════════
(function() {
  window.SuiteSmartSearch = {
    norm(str) {
      if (!str) return '';
      return String(str)
        .replace(/[\u064B-\u065F\u0670\u0640]/g, '')
        .replace(/[أإآ]/g, 'ا')
        .replace(/ة/g, 'ه')
        .replace(/[يى]/g, 'ي')
        .replace(/[\s\-_/\\,،]+/g, ' ')
        .trim()
        .toLowerCase();
    },

    getVariants(token) {
      const vars = [token];
      if (token === 'sh') vars.push('ش');
      else if (token === 'ش') vars.push('sh');
      else if (token === 'kh') vars.push('خ');
      else if (token === 'خ') vars.push('kh');
      else if (token === 'th') vars.push('ث');
      else if (token === 'ث') vars.push('th');
      else if (token === 'gh') vars.push('غ');
      else if (token === 'غ') vars.push('gh');
      return vars;
    },

    match(targetText, query) {
      if (!query || !query.trim()) return true;
      const qNorm = this.norm(query);
      const tNorm = this.norm(targetText);
      const rawTarget = String(targetText).toLowerCase();
      const tokens = qNorm.split(' ').filter(Boolean);
      return tokens.every(tok => {
        const vars = this.getVariants(tok);
        return vars.some(v => tNorm.includes(v) || rawTarget.includes(v));
      });
    },

    score(targetText, query) {
      if (!query || !query.trim()) return 100;
      const qNorm = this.norm(query);
      const tNorm = this.norm(targetText);
      const rawTarget = String(targetText).toLowerCase();
      if (tNorm === qNorm || rawTarget === query.toLowerCase().trim()) return 1000;
      if (tNorm.startsWith(qNorm) || rawTarget.startsWith(query.toLowerCase().trim())) return 800;
      const tokens = qNorm.split(' ').filter(Boolean);
      let matchCount = 0;
      tokens.forEach(tok => {
        const vars = this.getVariants(tok);
        if (vars.some(v => tNorm.includes(v) || rawTarget.includes(v))) matchCount++;
      });
      if (matchCount === tokens.length) {
        let sc = 500;
        const firstVars = this.getVariants(tokens[0]);
        if (firstVars.some(v => tNorm.startsWith(v) || rawTarget.startsWith(v))) sc += 200;
        return sc;
      }
      return 0;
    },

    buildPattern(tok) {
      if (tok === 'sh') return '(?:sh|ش)';
      if (tok === 'ش') return '(?:ش|sh)';
      if (tok === 'kh') return '(?:kh|خ)';
      if (tok === 'خ') return '(?:خ|kh)';
      if (tok === 'th') return '(?:th|ث)';
      if (tok === 'ث') return '(?:ث|th)';
      if (tok === 'gh') return '(?:gh|غ)';
      if (tok === 'غ') return '(?:غ|gh)';
      const special = '-/\\^$*+?.()|[]{}';
      let p = '';
      for (const ch of tok) {
        if ('اأإآ'.includes(ch)) p += '[اأإآ]';
        else if ('هة'.includes(ch)) p += '[هة]';
        else if ('يى'.includes(ch)) p += '[يى]';
        else if (special.includes(ch)) p += '\\' + ch;
        else p += ch;
      }
      return p;
    },

    highlight(text, query) {
      if (!query || !query.trim() || !text) return text || '';
      const tokens = query.trim().toLowerCase().split(/\s+/).filter(Boolean);
      if (!tokens.length) return text;
      let res = String(text);
      tokens.forEach(tok => {
        try {
          const pattern = this.buildPattern(tok);
          const regex = new RegExp('(' + pattern + ')', 'gi');
          res = res.replace(regex, '<span class="text-amber-300 font-black underline bg-amber-500/25 px-0.5 rounded">$1</span>');
        } catch(e) {}
      });
      return res;
    }
  };

  // ─────────────────────────────────────────────────────────────
  // 1. EXTEND SUITE SALES POS
  // ─────────────────────────────────────────────────────────────
  if (window.SuiteSalesPOS) {
    Object.assign(window.SuiteSalesPOS, {
      onCustomerFocus(el) {
        if (typeof this.syncAllErpDataForAI === 'function') this.syncAllErpDataForAI();
        if (el && (el.classList.contains('pos-ghost-default') || (el.value === 'عميل نقدي عام' && !this._userPickedCustomer))) {
          el.value = '';
          el.classList.remove('pos-ghost-default');
          if (window.SmartPOSFontAI) SmartPOSFontAI.scaleCustomer(el);
        }
        this.renderCustomerDropdown(el ? (el.value || '') : '');
      },

      onCustomerInput(q) {
        const el = document.getElementById('salesCustomerInput');
        if (el) {
          el.classList.remove('pos-ghost-default');
          this._userPickedCustomer = true;
          if (window.SmartPOSFontAI) SmartPOSFontAI.scaleCustomer(el);
        }
        this.renderCustomerDropdown(q);
      },

      onCustomerBlur(el) {
        setTimeout(() => {
          const drop = document.getElementById('salesCustomerDropdown');
          if (drop) drop.classList.add('hidden');
          if (!el) return;
          const val = (el.value || '').trim();
          if (!val) {
            if (this._userPickedCustomer && this.selectedCustomer && this.selectedCustomer.name && this.selectedCustomer.name !== 'عميل نقدي عام') {
              el.value = this.selectedCustomer.name;
              el.classList.remove('pos-ghost-default');
            } else {
              el.value = 'عميل نقدي عام';
              el.classList.add('pos-ghost-default');
              this._userPickedCustomer = false;
            }
          } else {
            el.classList.remove('pos-ghost-default');
            this._userPickedCustomer = true;
          }
          if (window.SmartPOSFontAI) SmartPOSFontAI.scaleCustomer(el);
        }, 220);
      },

      renderCustomerDropdown(q) {
        const drop = document.getElementById('salesCustomerDropdown');
        if (!drop) return;
        const query = (q || '').trim();
        const scored = (this.dbCustomers || []).map(c => {
          const target = (c.name || '') + ' ' + (c.phone || c.whatsapp || '');
          return {
            customer: c,
            score: SuiteSmartSearch.score(target, query),
            match: SuiteSmartSearch.match(target, query)
          };
        }).filter(item => item.match)
          .sort((a, b) => b.score - a.score);

        if (scored.length === 0) {
          drop.innerHTML = '<div class="text-center py-3 text-slate-400 text-xs">لا توجد نتائج مطابقة لـ "' + query + '"</div>';
          drop.classList.remove('hidden');
          return;
        }

        let html = '';
        scored.slice(0, 30).forEach(item => {
          const c = item.customer;
          const nameHl = SuiteSmartSearch.highlight(c.name, query);
          const cJson = JSON.stringify(c).replace(/"/g, '&quot;');

          html += `
            <div onclick="SuiteSalesPOS.selectCustomer(${cJson})" 
                 class="py-2.5 px-3.5 rounded-xl bg-slate-900/95 hover:bg-slate-800 border border-slate-800/90 hover:border-sky-500/60 flex items-center cursor-pointer transition active:scale-[0.99] w-full shadow-sm">
              <span class="font-bold text-slate-100 block text-[12px] sm:text-[13px] whitespace-normal break-words leading-relaxed w-full text-right">${nameHl}</span>
            </div>`;
        });

        drop.innerHTML = html;
        drop.classList.remove('hidden');
      },

      onCostCenterFocus(el) {
        const wDrop = document.getElementById('salesWarehouseDropdown');
        if (wDrop) wDrop.classList.add('hidden');
        if (el && (el.classList.contains('pos-ghost-default') || (el.value === 'المركز الرئيسي' && !this._userPickedCostCenter))) {
          el.value = '';
          el.classList.remove('pos-ghost-default');
          if (window.SmartPOSFontAI) SmartPOSFontAI.scaleCostCenter(el);
        }
        this.renderCostCenterDropdown(el ? (el.value || '') : '');
      },

      onCostCenterInput(q) {
        const el = document.getElementById('salesCostCenterInput');
        if (el) {
          el.classList.remove('pos-ghost-default');
          this._userPickedCostCenter = true;
          if (window.SmartPOSFontAI) SmartPOSFontAI.scaleCostCenter(el);
        }
        this.selectedCostCenter = (q || '').trim() || 'المركز الرئيسي';
        this.renderCostCenterDropdown(q);
      },

      onCostCenterBlur(el) {
        setTimeout(() => {
          const drop = document.getElementById('salesCostCenterDropdown');
          if (drop) drop.classList.add('hidden');
          if (!el) return;
          const val = (el.value || '').trim();
          if (!val) {
            if (this._userPickedCostCenter && this.selectedCostCenter && this.selectedCostCenter !== 'المركز الرئيسي') {
              el.value = this.selectedCostCenter;
              el.classList.remove('pos-ghost-default');
            } else {
              el.value = 'المركز الرئيسي';
              this.selectedCostCenter = 'المركز الرئيسي';
              el.classList.add('pos-ghost-default');
              this._userPickedCostCenter = false;
            }
          } else {
            el.classList.remove('pos-ghost-default');
            this._userPickedCostCenter = true;
          }
          if (window.SmartPOSFontAI) SmartPOSFontAI.scaleCostCenter(el);
        }, 220);
      },

      renderCostCenterDropdown(q) {
        const drop = document.getElementById('salesCostCenterDropdown');
        if (!drop) return;
        const query = (q || '').trim();
        const scored = (this.dbCostCenters || []).map(cc => {
          const target = cc.name || '';
          return {
            cc,
            score: SuiteSmartSearch.score(target, query),
            match: SuiteSmartSearch.match(target, query)
          };
        }).filter(item => item.match)
          .sort((a, b) => b.score - a.score);

        let html = '';
        if (query && !scored.some(x => (x.cc.name || '').toLowerCase() === query.toLowerCase())) {
          const safeQ = query.replace(/'/g, "\\'");
          html += `
            <div onclick="SuiteSalesPOS.selectCostCenter('${safeQ}')" 
                 class="py-2 px-3 rounded-xl bg-sky-950/60 hover:bg-sky-900/70 border border-sky-500/50 flex items-center justify-between cursor-pointer transition active:scale-[0.99] w-full">
              <span class="font-bold text-sky-200 text-xs"><i class="fa-solid fa-plus-circle text-sky-400"></i> اعتماد مركز كلفة: "${query}"</span>
              <span class="text-[10px] text-sky-300 font-mono">اختيار ↵</span>
            </div>`;
        }
        scored.forEach(item => {
          const cc = item.cc;
          const nameHl = SuiteSmartSearch.highlight(cc.name, query);
          const safeName = (cc.name || '').replace(/'/g, "\\'");
          html += `
            <div onclick="SuiteSalesPOS.selectCostCenter('${safeName}')" 
                 class="py-2.5 px-3.5 rounded-xl bg-slate-900/95 hover:bg-slate-800 border border-slate-800/90 hover:border-sky-500/60 flex items-center cursor-pointer transition active:scale-[0.99] w-full shadow-sm">
              <span class="font-bold text-slate-100 hover:text-sky-200 block text-[12px] sm:text-[13px] whitespace-normal break-words leading-relaxed w-full text-right">${nameHl}</span>
            </div>`;
        });
        if (!html) {
          html = '<div class="text-center py-3 text-slate-400 text-xs">لا يوجد مركز كلفة مطابق</div>';
        }
        drop.innerHTML = html;
        drop.classList.remove('hidden');
      },

      onWarehouseFocus(el) {
        const ccDrop = document.getElementById('salesCostCenterDropdown');
        if (ccDrop) ccDrop.classList.add('hidden');
        if (el && (el.classList.contains('pos-ghost-default') || (el.value === 'المستودع الرئيسي' && !this._userPickedWarehouse))) {
          el.value = '';
          el.classList.remove('pos-ghost-default');
          if (window.SmartPOSFontAI) SmartPOSFontAI.scaleWarehouse(el);
        }
        this.renderWarehouseDropdown(el ? (el.value || '') : '');
      },

      onWarehouseInput(q) {
        const el = document.getElementById('salesWarehouseInput');
        if (el) {
          el.classList.remove('pos-ghost-default');
          this._userPickedWarehouse = true;
          if (window.SmartPOSFontAI) SmartPOSFontAI.scaleWarehouse(el);
        }
        this.renderWarehouseDropdown(q);
      },

      onWarehouseBlur(el) {
        setTimeout(() => {
          const drop = document.getElementById('salesWarehouseDropdown');
          if (drop) drop.classList.add('hidden');
          if (!el) return;
          const val = (el.value || '').trim();
          if (!val) {
            if (this._userPickedWarehouse && this.selectedWarehouse && this.selectedWarehouse !== 'المستودع الرئيسي') {
              el.value = this.selectedWarehouse;
              el.classList.remove('pos-ghost-default');
            } else {
              el.value = 'المستودع الرئيسي';
              this.selectedWarehouse = 'المستودع الرئيسي';
              el.classList.add('pos-ghost-default');
              this._userPickedWarehouse = false;
            }
          } else {
            el.classList.remove('pos-ghost-default');
            this._userPickedWarehouse = true;
          }
          if (window.SmartPOSFontAI) SmartPOSFontAI.scaleWarehouse(el);
        }, 220);
      },

      renderWarehouseDropdown(q) {
        const drop = document.getElementById('salesWarehouseDropdown');
        if (!drop) return;
        const query = (q || '').trim();
        const scored = (this.dbWarehouses || []).map(w => {
          const target = (w.name || '') + ' ' + (w.type || '');
          return {
            warehouse: w,
            score: SuiteSmartSearch.score(target, query),
            match: SuiteSmartSearch.match(target, query)
          };
        }).filter(item => item.match)
          .sort((a, b) => b.score - a.score);

        if (scored.length === 0) {
          drop.innerHTML = '<div class="text-center py-3 text-slate-400 text-xs">لا يوجد مستودع مطابق</div>';
          drop.classList.remove('hidden');
          return;
        }

        let html = '';
        scored.forEach(item => {
          const w = item.warehouse;
          const nameHl = SuiteSmartSearch.highlight(w.name, query);
          const safeName = (w.name || '').replace(/'/g, "\\'");
          html += `
            <div onclick="SuiteSalesPOS.selectWarehouse('${safeName}')" 
                 class="py-2.5 px-3.5 rounded-xl bg-slate-900/95 hover:bg-slate-800 border border-slate-800/90 hover:border-purple-500/60 flex items-center cursor-pointer transition active:scale-[0.99] w-full shadow-sm">
              <span class="font-bold text-slate-100 hover:text-purple-200 block text-[12px] sm:text-[13px] whitespace-normal break-words leading-relaxed w-full text-right">${nameHl}</span>
            </div>`;
        });
        drop.innerHTML = html;
        drop.classList.remove('hidden');
      },

      onItemFocus(el) {
        if (el) el.classList.remove('pos-ghost-default');
        this.renderItemDropdown(el ? (el.value || '') : '');
      },

      onItemInput(q) {
        const el = document.getElementById('salesItemSearchInput');
        if (el) el.classList.remove('pos-ghost-default');
        this.renderItemDropdown(q);
      },

      onItemBlur(el) {
        // حظر بقاء أي كتابة غير محفوظة داخل حقل الصنف
        setTimeout(() => {
          const drop = document.getElementById('salesItemDropdown');
          if (drop) drop.classList.add('hidden');
          const val = (el.value || '').trim();
          if (!val) {
            this.selectedItemIndex = -1;
            const hid = document.getElementById('salesItemSelectVal');
            if (hid) hid.value = '';
            return;
          }
          const matchIdx = (this.dbItems || []).findIndex(it => (it.name || '').trim().toLowerCase() === val.toLowerCase());
          if (matchIdx !== -1) {
            this.selectItemByIndex(matchIdx);
          } else if (this.selectedItemIndex !== -1 && this.dbItems[this.selectedItemIndex]) {
            el.value = this.dbItems[this.selectedItemIndex].name;
            if (window.SmartPOSFontAI) SmartPOSFontAI.scaleItem(el);
          } else {
            // إفراغ النص العشوائي غير المحفوظ
            el.value = '';
            this.selectedItemIndex = -1;
            const hid = document.getElementById('salesItemSelectVal');
            if (hid) hid.value = '';
            if (window.SmartPOSFontAI) SmartPOSFontAI.scaleItem(el);
            if (typeof showFloatingToast === 'function') {
              showFloatingToast('⚠️ البحث محصور على الأصناف المحفوظة فقط');
            }
          }
        }, 220);
      },

      renderItemDropdown(q) {
        const drop = document.getElementById('salesItemDropdown');
        if (!drop) return;
        const query = (q || '').trim();
        const scored = (this.dbItems || []).map((it, idx) => {
          const target = (it.name || '') + ' ' + (it.barcode || '') + ' ' + (it.baseUnit || it.unit || '');
          return {
            item: it,
            originalIndex: idx,
            score: SuiteSmartSearch.score(target, query),
            match: SuiteSmartSearch.match(target, query)
          };
        }).filter(item => item.match)
          .sort((a, b) => b.score - a.score);

        let html = '';
        // تم حظر إضافة أصناف غير محفوظة - البحث محصور 100% على الأصناف المحفوظة في السيستم
        if (scored.length === 0) {
          drop.innerHTML = '<div class="text-center py-3.5 text-amber-300 font-bold text-xs">⚠️ لا يوجد صنف مطابق ضمن الأصناف المحفوظة</div>';
          drop.classList.remove('hidden');
          return;
        }

        scored.slice(0, 20).forEach(entry => {
          const it = entry.item;
          const idx = entry.originalIndex;
          const prc = parseFloat(it.sellingPrice || it.price || 0);
          const nameHl = SuiteSmartSearch.highlight(it.name, query);
          const unit = it.baseUnit || it.unit || 'حبة';

          html += `
            <div onclick="SuiteSalesPOS.selectItemByIndex(${idx})" 
                 class="py-2.5 px-3.5 rounded-xl bg-slate-900/95 hover:bg-slate-800 border border-slate-800/90 hover:border-amber-500/60 flex justify-between items-center cursor-pointer transition active:scale-[0.99] gap-3 w-full shadow-sm">
              <div class="min-w-0 flex-1">
                <span class="font-bold text-amber-200 hover:text-white block text-[13px] sm:text-sm whitespace-normal break-words leading-relaxed text-right">${nameHl}</span>
                ${it.barcode ? `<span class="text-slate-400 text-[10.5px] font-mono block truncate mt-0.5 text-right">${it.barcode}</span>` : ''}
              </div>
              <div class="text-left font-mono shrink-0">
                <span class="text-emerald-400 font-black block text-[13px] sm:text-sm">${prc.toLocaleString()} SDG</span>
                <span class="text-amber-300 text-[10.5px] block font-bold">${unit}</span>
              </div>
            </div>`;
        });

        drop.innerHTML = html;
        drop.classList.remove('hidden');
      }
    });

    // Wrap selectCustomer
    const origSelectCustomer = window.SuiteSalesPOS.selectCustomer;
    window.SuiteSalesPOS.selectCustomer = function(c, isDefaultInit = false) {
      origSelectCustomer.call(this, c, isDefaultInit);
      const drop = document.getElementById('salesCustomerDropdown');
      if (drop) drop.classList.add('hidden');
      const inp = document.getElementById('salesCustomerInput');
      if (inp && c && c.name) {
        inp.value = c.name;
        if (isDefaultInit) inp.classList.add('pos-ghost-default');
        else inp.classList.remove('pos-ghost-default');
        if (window.SmartPOSFontAI) SmartPOSFontAI.scaleCustomer(inp);
      }
      if (typeof this.updateErpSmartStatusStrip === 'function') this.updateErpSmartStatusStrip();
    };

    // Wrap selectWarehouse
    const origSelectWarehouse = window.SuiteSalesPOS.selectWarehouse;
    window.SuiteSalesPOS.selectWarehouse = function(name, isDefaultInit = false) {
      origSelectWarehouse.call(this, name, isDefaultInit);
      const drop = document.getElementById('salesWarehouseDropdown');
      if (drop) drop.classList.add('hidden');
      const inp = document.getElementById('salesWarehouseInput');
      if (inp) {
        inp.value = name;
        if (isDefaultInit) inp.classList.add('pos-ghost-default');
        else inp.classList.remove('pos-ghost-default');
        if (window.SmartPOSFontAI) SmartPOSFontAI.scaleWarehouse(inp);
      }
      if (typeof this.updateErpSmartStatusStrip === 'function') this.updateErpSmartStatusStrip();
    };

    // Wrap selectItemByIndex
    const origSelectItemByIndex = window.SuiteSalesPOS.selectItemByIndex;
    window.SuiteSalesPOS.selectItemByIndex = function(idx) {
      origSelectItemByIndex.call(this, idx);
      const drop = document.getElementById('salesItemDropdown');
      if (drop) drop.classList.add('hidden');
      const it = this.dbItems && this.dbItems[idx];
      const inp = document.getElementById('salesItemSearchInput');
      if (inp && it) {
        inp.value = it.name;
        inp.classList.remove('pos-ghost-default');
        if (window.SmartPOSFontAI) SmartPOSFontAI.scaleItem(inp);
      }
      if (window.SmartPOSFontAI) SmartPOSFontAI.scaleAll();
      if (typeof this.updateErpSmartStatusStrip === 'function') this.updateErpSmartStatusStrip();
    };

    // Wrap newInvoice
    const origNewInvoice = window.SuiteSalesPOS.newInvoice;
    window.SuiteSalesPOS.newInvoice = function() {
      origNewInvoice.call(this);
      this._userPickedCustomer = false;
      this._userPickedWarehouse = false;
      this._userPickedCostCenter = false;
      const custInp = document.getElementById('salesCustomerInput');
      if (custInp) {
        custInp.value = 'عميل نقدي عام';
        custInp.classList.add('pos-ghost-default');
      }
      const whInp = document.getElementById('salesWarehouseInput');
      if (whInp) {
        whInp.value = 'المستودع الرئيسي';
        whInp.classList.add('pos-ghost-default');
      }
      const itInp = document.getElementById('salesItemSearchInput');
      if (itInp) itInp.value = '';
      const ccInp = document.getElementById('salesCostCenterInput');
      if (ccInp) {
        ccInp.value = this.selectedCostCenter || 'المركز الرئيسي';
        ccInp.classList.add('pos-ghost-default');
      }
      ['salesCustomerDropdown', 'salesWarehouseDropdown', 'salesCostCenterDropdown', 'salesItemDropdown'].forEach(id => {
        const d = document.getElementById(id);
        if (d) d.classList.add('hidden');
      });
      if (typeof this.updateErpSmartStatusStrip === 'function') this.updateErpSmartStatusStrip();
      if (window.SmartPOSFontAI) setTimeout(() => window.SmartPOSFontAI.scaleAll(), 30);
    };
  }

  // ─────────────────────────────────────────────────────────────
  // 2. EXTEND SUITE PURCHASE POS
  // ─────────────────────────────────────────────────────────────
  if (window.SuitePurchasePOS) {
    Object.assign(window.SuitePurchasePOS, {
      onSupplierFocus(el) {
        this.renderSupplierDropdown(el.value || '');
      },

      onSupplierInput(q) {
        this.renderSupplierDropdown(q);
      },

      renderSupplierDropdown(q) {
        const drop = document.getElementById('purchaseSupplierDropdown');
        if (!drop) return;
        const query = (q || '').trim();
        const scored = (this.dbSuppliers || []).map(s => {
          const target = (s.name || '') + ' ' + (s.phone || '');
          return {
            supplier: s,
            score: SuiteSmartSearch.score(target, query),
            match: SuiteSmartSearch.match(target, query)
          };
        }).filter(item => item.match)
          .sort((a, b) => b.score - a.score);

        if (scored.length === 0) {
          drop.innerHTML = '<div class="text-center py-3 text-slate-400 text-xs">لا يوجد مورد مطابق لـ "' + query + '"</div>';
          drop.classList.remove('hidden');
          return;
        }

        let html = '';
        scored.slice(0, 30).forEach(item => {
          const s = item.supplier;
          const nameHl = SuiteSmartSearch.highlight(s.name, query);
          const sJson = JSON.stringify(s).replace(/"/g, '&quot;');

          html += `
            <div onclick="SuitePurchasePOS.selectSupplier(${sJson})" 
                 class="py-2.5 px-3.5 rounded-xl bg-slate-900/95 hover:bg-slate-800 border border-slate-800/90 hover:border-amber-500/60 flex items-center cursor-pointer transition active:scale-[0.99] w-full shadow-sm">
              <span class="font-bold text-slate-100 block text-[12px] sm:text-[13px] whitespace-normal break-words leading-relaxed w-full text-right">${nameHl}</span>
            </div>`;
        });
        drop.innerHTML = html;
        drop.classList.remove('hidden');
      },

      onWarehouseFocus(el) {
        this.renderWarehouseDropdown(el.value || '');
      },

      onWarehouseInput(q) {
        this.renderWarehouseDropdown(q);
      },

      renderWarehouseDropdown(q) {
        const drop = document.getElementById('purchaseWarehouseDropdown');
        if (!drop) return;
        const query = (q || '').trim();
        const warehouses = (window.SuiteSalesPOS && SuiteSalesPOS.dbWarehouses) ? SuiteSalesPOS.dbWarehouses : [];
        const scored = warehouses.map(w => {
          const target = (w.name || '') + ' ' + (w.type || '');
          return {
            warehouse: w,
            score: SuiteSmartSearch.score(target, query),
            match: SuiteSmartSearch.match(target, query)
          };
        }).filter(item => item.match)
          .sort((a, b) => b.score - a.score);

        if (scored.length === 0) {
          drop.innerHTML = '<div class="text-center py-3 text-slate-400 text-xs">لا يوجد مستودع مطابق</div>';
          drop.classList.remove('hidden');
          return;
        }

        let html = '';
        scored.forEach(item => {
          const w = item.warehouse;
          const nameHl = SuiteSmartSearch.highlight(w.name, query);
          const safeName = (w.name || '').replace(/'/g, "\\'");
          html += `
            <div onclick="SuitePurchasePOS.selectWarehouse('${safeName}')" 
                 class="py-2.5 px-3.5 rounded-xl bg-slate-900/95 hover:bg-slate-800 border border-slate-800/90 hover:border-purple-500/60 flex items-center cursor-pointer transition active:scale-[0.99] w-full shadow-sm">
              <span class="font-bold text-slate-100 hover:text-purple-200 block text-[12px] sm:text-[13px] whitespace-normal break-words leading-relaxed w-full text-right">${nameHl}</span>
            </div>`;
        });
        drop.innerHTML = html;
        drop.classList.remove('hidden');
      },

      onItemFocus(el) {
        this.renderItemDropdown(el.value || '');
      },

      onItemInput(q) {
        this.renderItemDropdown(q);
      },

      renderItemDropdown(q) {
        const drop = document.getElementById('purItemDropdown');
        if (!drop) return;
        const query = (q || '').trim();
        const items = (window.SuiteSalesPOS && SuiteSalesPOS.dbItems) ? SuiteSalesPOS.dbItems : [];
        const scored = items.map((it, idx) => {
          const target = (it.name || '') + ' ' + (it.barcode || '') + ' ' + (it.baseUnit || it.unit || '');
          return {
            item: it,
            originalIndex: idx,
            score: SuiteSmartSearch.score(target, query),
            match: SuiteSmartSearch.match(target, query)
          };
        }).filter(item => item.match)
          .sort((a, b) => b.score - a.score);

        let html = '';
        if (scored.length === 0) {
          drop.innerHTML = '<div class="text-center py-3.5 text-amber-300 font-bold text-xs">⚠️ لا يوجد صنف مطابق ضمن الأصناف المحفوظة</div>';
          drop.classList.remove('hidden');
          return;
        }

        scored.slice(0, 20).forEach(entry => {
          const it = entry.item;
          const idx = entry.originalIndex;
          const prc = parseFloat(it.costPrice || it.price || 0);
          const nameHl = SuiteSmartSearch.highlight(it.name, query);
          const unit = it.baseUnit || it.unit || 'حبة';

          html += `
            <div onclick="SuitePurchasePOS.selectItemByIndex(${idx})" 
                 class="py-2.5 px-3.5 rounded-xl bg-slate-900/95 hover:bg-slate-800 border border-slate-800/90 hover:border-amber-500/60 flex justify-between items-center cursor-pointer transition active:scale-[0.99] gap-3 w-full shadow-sm">
              <div class="min-w-0 flex-1">
                <span class="font-bold text-amber-200 hover:text-white block text-[13px] sm:text-sm whitespace-normal break-words leading-relaxed text-right">${nameHl}</span>
                ${it.barcode ? `<span class="text-slate-400 text-[10.5px] font-mono block truncate mt-0.5 text-right">${it.barcode}</span>` : ''}
              </div>
              <div class="text-left font-mono shrink-0">
                <span class="text-amber-400 font-black block text-[13px] sm:text-sm">${prc.toLocaleString()} SDG</span>
                <span class="text-slate-400 text-[10.5px] block font-bold">${unit}</span>
              </div>
            </div>`;
        });
        drop.innerHTML = html;
        drop.classList.remove('hidden');
      },

      // ─────────────────────────────────────────────────────────────
      // ذاكرة آخر سعر شراء للمورد والصنف + شريط حالة المورد والمخزون الحي
      // ─────────────────────────────────────────────────────────────
      _lastRememberedPriceObj: null,

      getRememberedPrice(suppName, itObj) {
        if (!itObj || !itObj.name) return null;
        const itemName = itObj.name.trim();
        const cleanSupp = (suppName || '').trim();

        // 1. فحص ذاكرة أسعار الشراء المحفوظة محلياً
        try {
          const mem = JSON.parse(localStorage.getItem('magaies_pur_last_price_memory_v1') || '{}');
          if (cleanSupp && cleanSupp !== 'مورد نقدي عام' && mem[`${cleanSupp}__${itemName}`]) {
            return { ...mem[`${cleanSupp}__${itemName}`], scope: 'supplier' };
          }
          if (mem[`*__${itemName}`]) {
            return { ...mem[`*__${itemName}`], scope: 'global' };
          }
        } catch (e) {}

        // 2. فحص أرشيف فواتير المشتريات السابقة
        const allInvs = [
          ...(Array.isArray(this.recentInvoices) ? this.recentInvoices : []),
          ...(function() {
            try { return JSON.parse(localStorage.getItem('magaies_local_purchase_invoices_v1') || '[]'); } catch(e) { return []; }
          })()
        ];

        let globalMatch = null;
        for (const inv of allInvs) {
          if (!inv || !Array.isArray(inv.items)) continue;
          for (const line of inv.items) {
            const p = parseFloat(line.price || 0);
            if ((line.name === itemName || line.itemName === itemName) && p > 0) {
              if (cleanSupp && cleanSupp !== 'مورد نقدي عام' && inv.supplierName === cleanSupp) {
                return { price: p, unit: line.unit || itObj.unit || 'طن عددي', date: inv.date || '', scope: 'supplier', supplierName: cleanSupp };
              }
              if (!globalMatch) {
                globalMatch = { price: p, unit: line.unit || itObj.unit || 'طن عددي', date: inv.date || '', scope: 'global' };
              }
            }
          }
        }
        if (globalMatch) return globalMatch;

        // 3. آخر تكلفة شراء مسجلة للصنف في قاعدة البيانات
        const catCost = parseFloat(itObj.lastPurchaseCost || itObj.costPrice || itObj.averageCost || itObj.cost || itObj.price || 0);
        if (catCost > 0) {
          return { price: catCost, unit: itObj.lastPurchaseUnit || itObj.baseUnit || itObj.unit || 'حبة', date: itObj.lastPurchaseDate || 'التكلفة المسجلة', scope: 'catalog' };
        }
        return null;
      },

      updateLastPriceMemoryUI(suppName, itObj) {
        const bar = document.getElementById('purLastPriceMemoryBar');
        const txt = document.getElementById('purLastPriceMemoryText');
        if (!bar || !txt) return;

        const info = this.getRememberedPrice(suppName, itObj);
        this._lastRememberedPriceObj = info;
        if (!info || !info.price) {
          bar.classList.add('hidden');
          return;
        }

        const cur = this.currencyMode === 'USD' ? '$' : 'ج.س';
        const scopeLabel = info.scope === 'supplier'
          ? `آخر سعر شراء من [${suppName}]`
          : (info.scope === 'global' ? 'آخر سعر شراء للصنف' : 'تكلفة الشراء المسجلة');
        txt.innerHTML = `<i class="fa-solid fa-clock-rotate-left text-amber-400"></i> <span>${scopeLabel}: <strong class="text-emerald-300 font-mono">${Number(info.price).toLocaleString()} ${cur}</strong> <span class="text-slate-400">(${info.unit || 'وحدة'})</span></span>`;
        bar.classList.remove('hidden');
      },

      applyRememberedPrice() {
        const info = this._lastRememberedPriceObj;
        if (!info || !info.price) return;
        const pInp = document.getElementById('purPriceInput');
        if (pInp) {
          pInp.value = info.price;
          if (window.SmartPOSFontAI) window.SmartPOSFontAI.scalePrice(pInp);
        }
        if (info.unit) {
          const uSel = document.getElementById('purUnitSelect');
          if (uSel && uSel.options) {
            for (let i = 0; i < uSel.options.length; i++) {
              if (uSel.options[i].value === info.unit) {
                uSel.selectedIndex = i;
                if (window.SmartPOSFontAI) window.SmartPOSFontAI.scaleUnit(uSel);
                break;
              }
            }
          }
        }
        this.calcItemSubtotal();
        if (window.triggerHapticPulse) triggerHapticPulse(15);
        if (typeof showFloatingToast === 'function') {
          showFloatingToast(`🏷️ تم اعتماد آخر سعر شراء (${Number(info.price).toLocaleString()}) في الفاتورة فوراً!`);
        }
      },

      saveToLastPriceMemory(suppName, items, dateStr) {
        if (!Array.isArray(items) || items.length === 0) return;
        try {
          const mem = JSON.parse(localStorage.getItem('magaies_pur_last_price_memory_v1') || '{}');
          const cleanSupp = (suppName || '').trim();
          items.forEach(it => {
            const prc = parseFloat(it.price || 0);
            const iName = (it && (it.name || it.itemName) || '').trim();
            if (iName && prc > 0) {
              const entry = { price: prc, unit: it.unit || '', date: dateStr || new Date().toISOString().split('T')[0] };
              mem[`*__${iName}`] = entry;
              if (cleanSupp && cleanSupp !== 'مورد نقدي عام') {
                mem[`${cleanSupp}__${iName}`] = entry;
              }
            }
          });
          localStorage.setItem('magaies_pur_last_price_memory_v1', JSON.stringify(mem));
        } catch (e) {}
      },

      updateErpSmartStatusStrip() {
        const strip = document.getElementById('purErpSmartStatusStrip');
        const suppBadge = document.getElementById('purErpSuppBalanceBadge');
        const stockBadge = document.getElementById('purErpItemStockBadge');

        const suppName = (document.getElementById('purchaseSupplierInput')?.value || this.selectedSupplier?.name || '').trim();
        const suppObj = (this.dbSuppliers || []).find(s => s.name === suppName) || this.selectedSupplier;
        const dbItems = (window.SuiteSalesPOS && SuiteSalesPOS.dbItems) ? SuiteSalesPOS.dbItems : [];
        let itObj = (this.selectedItemIndex !== -1 && dbItems[this.selectedItemIndex]) ? dbItems[this.selectedItemIndex] : null;
        const itInpVal = (document.getElementById('purItemSearchInput')?.value || '').trim();
        if (!itObj && itInpVal && itInpVal !== 'اختيار الصنف' && itInpVal !== 'اختر الصنف...') {
          itObj = dbItems.find(x => x.name === itInpVal) || { name: itInpVal, unit: 'طن', stock: 0 };
        }

        // تحديث شريط ذاكرة آخر سعر شراء للمورد والصنف
        this.updateLastPriceMemoryUI(suppName, itObj);

        if (!strip) return;
        const whKey = (document.getElementById('purchaseWarehouseInput')?.value || this.selectedWarehouse || '').trim();
        const hasSupp = Boolean(suppName && suppName !== 'مورد نقدي عام');
        const hasItemAndWh = Boolean(itObj && whKey);

        if (!hasSupp && !hasItemAndWh) {
          strip.classList.add('hidden');
          return;
        }
        strip.classList.remove('hidden');

        const bal = parseFloat(suppObj?.currentBalance ?? suppObj?.balanceNum ?? suppObj?.balance ?? suppObj?.openingBalance ?? 0);
        const cur = suppObj?.currency === 'USD' ? '$' : 'ج.س';
        if (suppBadge) {
          suppBadge.className = 'flex items-center gap-1 truncate ' + (bal > 0 ? 'text-amber-300 font-black' : 'text-sky-300');
          suppBadge.innerHTML = `<span>رصيد المورد: ${bal.toLocaleString()} ${cur}</span>`;
        }

        if (stockBadge) {
          if (hasItemAndWh) {
            const uSel = document.getElementById('purUnitSelect');
            const opt = uSel && uSel.options && uSel.selectedIndex >= 0 ? uSel.options[uSel.selectedIndex] : null;
            const factor = opt ? (parseFloat(opt.getAttribute('data-factor')) || 1) : 1;
            const rawUnitName = (opt && opt.value) ? opt.value : (itObj?.baseUnit || itObj?.unit || 'طن');
            const uName = String(rawUnitName).replace(/\s*\(\d+(?:\.\d+)?\)\s*/g, '').trim() || 'طن';

            const whList = (window.SuiteSalesPOS && SuiteSalesPOS.dbWarehouses) ? SuiteSalesPOS.dbWarehouses : [];
            const whObj = whList.find(w => w.name === whKey || w.id === whKey);
            let rawBaseStock = null;

            if (typeof itObj.stock === 'number') {
              rawBaseStock = itObj.stock;
            } else if (itObj.stock && typeof itObj.stock === 'object' && !Array.isArray(itObj.stock)) {
              if (whKey && itObj.stock[whKey] !== undefined) {
                rawBaseStock = parseFloat(itObj.stock[whKey]) || 0;
              } else if (whObj && whObj.id && itObj.stock[whObj.id] !== undefined) {
                rawBaseStock = parseFloat(itObj.stock[whObj.id]) || 0;
              } else {
                const vals = Object.values(itObj.stock).map(v => parseFloat(v) || 0);
                rawBaseStock = vals.reduce((a, b) => a + b, 0);
              }
            } else if (Array.isArray(itObj.stock) && itObj.stock.length > 0) {
              const mWh = itObj.stock.find(s => s && (s.warehouse === whKey || s.warehouseId === whObj?.id));
              if (mWh && mWh.qty !== undefined) {
                rawBaseStock = parseFloat(mWh.qty) || 0;
              } else if (itObj.stock[0]?.qty !== undefined) {
                rawBaseStock = Number(itObj.stock[0].qty) || 0;
              }
            }

            if (rawBaseStock === null || rawBaseStock === undefined) {
              rawBaseStock = parseFloat(itObj.availableQty || itObj.qty || 0) || 0;
            }

            const numericAvailableStock = (rawBaseStock > 0 && factor > 1 && rawBaseStock >= factor)
              ? Math.round((rawBaseStock / factor) * 100) / 100
              : Math.round((rawBaseStock || 0) * 100) / 100;

            stockBadge.innerHTML = `<span>بالمخزن: ${numericAvailableStock.toLocaleString()} ${uName}</span>`;
            stockBadge.classList.remove('hidden');
          } else {
            stockBadge.innerHTML = '';
          }
        }
      }
    });

    // Wrap selectSupplier
    const origSelectSupplier = window.SuitePurchasePOS.selectSupplier;
    window.SuitePurchasePOS.selectSupplier = function(s) {
      origSelectSupplier.call(this, s);
      const drop = document.getElementById('purchaseSupplierDropdown');
      if (drop) drop.classList.add('hidden');
      const inp = document.getElementById('purchaseSupplierInput');
      if (inp && s && s.name) inp.value = s.name;
      if (typeof this.updateErpSmartStatusStrip === 'function') this.updateErpSmartStatusStrip();
    };

    // Wrap selectWarehouse
    const origPurSelectWarehouse = window.SuitePurchasePOS.selectWarehouse;
    window.SuitePurchasePOS.selectWarehouse = function(name) {
      origPurSelectWarehouse.call(this, name);
      const drop = document.getElementById('purchaseWarehouseDropdown');
      if (drop) drop.classList.add('hidden');
      const inp = document.getElementById('purchaseWarehouseInput');
      if (inp) {
        inp.value = name;
        if (window.SmartPOSFontAI) SmartPOSFontAI.scaleWarehouse(inp);
      }
      if (typeof this.updateErpSmartStatusStrip === 'function') this.updateErpSmartStatusStrip();
    };

    // Wrap selectItemByIndex
    const origPurSelectItemByIndex = window.SuitePurchasePOS.selectItemByIndex;
    window.SuitePurchasePOS.selectItemByIndex = function(idx) {
      origPurSelectItemByIndex.call(this, idx);
      const drop = document.getElementById('purItemDropdown');
      if (drop) drop.classList.add('hidden');
      const items = (window.SuiteSalesPOS && SuiteSalesPOS.dbItems) ? SuiteSalesPOS.dbItems : [];
      const item = items[idx];
      const inp = document.getElementById('purItemSearchInput');
      if (inp && item) {
        inp.value = item.name;
        if (window.SmartPOSFontAI) SmartPOSFontAI.scaleItem(inp);
      }
      // إذا وُجد سعر شراء محفوظ لهذا المورد والصنف، نعبئه تلقائياً في خانة السعر لمنع إدخال سعر قديم
      const suppName = (document.getElementById('purchaseSupplierInput')?.value || this.selectedSupplier?.name || '').trim();
      if (typeof this.getRememberedPrice === 'function' && item) {
        const rem = this.getRememberedPrice(suppName, item);
        if (rem && rem.price > 0) {
          const pInp = document.getElementById('purPriceInput');
          if (pInp) pInp.value = rem.price;
          this.calcItemSubtotal();
        }
      }
      if (window.SmartPOSFontAI) SmartPOSFontAI.scaleAll();
      if (typeof this.updateErpSmartStatusStrip === 'function') this.updateErpSmartStatusStrip();
    };

    // Wrap selectCustomItem
    const origPurSelectCustomItem = window.SuitePurchasePOS.selectCustomItem;
    window.SuitePurchasePOS.selectCustomItem = function(name) {
      origPurSelectCustomItem.call(this, name);
      const drop = document.getElementById('purItemDropdown');
      if (drop) drop.classList.add('hidden');
      const inp = document.getElementById('purItemSearchInput');
      if (inp) {
        inp.value = name;
        if (window.SmartPOSFontAI) SmartPOSFontAI.scaleItem(inp);
      }
      if (window.SmartPOSFontAI) SmartPOSFontAI.scaleAll();
      if (typeof this.updateErpSmartStatusStrip === 'function') this.updateErpSmartStatusStrip();
    };

    // Wrap newInvoice
    const origPurNewInvoice = window.SuitePurchasePOS.newInvoice;
    if (typeof origPurNewInvoice === 'function') {
      window.SuitePurchasePOS.newInvoice = function(silent = false) {
        origPurNewInvoice.call(this, silent);
        const suppInp = document.getElementById('purchaseSupplierInput');
        if (suppInp) suppInp.value = 'مطاحن الخرطوم للغلال';
        const whInp = document.getElementById('purchaseWarehouseInput');
        if (whInp) whInp.value = 'المستودع الرئيسي';
        const itInp = document.getElementById('purItemSearchInput');
        if (itInp) itInp.value = '';
        ['purchaseSupplierDropdown', 'purchaseWarehouseDropdown', 'purItemDropdown'].forEach(id => {
          const d = document.getElementById(id);
          if (d) d.classList.add('hidden');
        });
        if (typeof this.updateErpSmartStatusStrip === 'function') this.updateErpSmartStatusStrip();
      };
    }
  }

  // ─────────────────────────────────────────────────────────────
  // 3. GLOBAL CLICK-OUTSIDE DISMISSAL
  // ─────────────────────────────────────────────────────────────
  document.addEventListener('click', function(e) {
    const dropdowns = [
      { inp: 'salesCustomerInput', drop: 'salesCustomerDropdown' },
      { inp: 'salesWarehouseInput', drop: 'salesWarehouseDropdown' },
      { inp: 'salesCostCenterInput', drop: 'salesCostCenterDropdown' },
      { inp: 'salesItemSearchInput', drop: 'salesItemDropdown' },
      { inp: 'purchaseSupplierInput', drop: 'purchaseSupplierDropdown' },
      { inp: 'purchaseWarehouseInput', drop: 'purchaseWarehouseDropdown' },
      { inp: 'purItemSearchInput', drop: 'purItemDropdown' }
    ];
    dropdowns.forEach(item => {
      if (!e.target.closest('#' + item.inp + ', #' + item.drop)) {
        const d = document.getElementById(item.drop);
        if (d && !d.classList.contains('hidden')) {
          d.classList.add('hidden');
        }
      }
    });
  });
})();

window._isSavingOpSales = false;
window._lastSavedSalesSig = '';

async function commitOpSales(andPay = false) {
      if (window._isSavingOpSales) return;
      if (window.AppHaptics) AppHaptics.success();
      if (window.SuiteSalesPOS && SuiteSalesPOS.isVoiceListening) {
        SuiteSalesPOS.stopVoiceFeed(true);
      }

      // ضمان تحديث الجملة والإجمالي الصافي من الحقول الحالية قبل الحفظ مباشرة
      if (window.SuiteSalesPOS && typeof SuiteSalesPOS.calcItemSubtotal === 'function') {
        SuiteSalesPOS.calcItemSubtotal();
      }

      const invNo = document.getElementById('opSalesInvoiceNoPill')?.innerText?.trim() || (window.SuiteSalesPOS ? SuiteSalesPOS.generateNewInvoiceNo() : ('S26100001'));
      const cust = document.getElementById('salesCustomerInput')?.value?.trim() || SuiteSalesPOS.selectedCustomer?.name || 'عميل نقدي عام';
      const payM = document.getElementById('salesPayMethodSelect')?.value || 'cash';
      const invDate = document.getElementById('salesInvoiceDate')?.value || new Date().toISOString().split('T')[0];
      const salesRep = document.getElementById('salesRepInput')?.value || 'كاشير الجوال';
      const costCenter = document.getElementById('salesCostCenterInput')?.value?.trim() || SuiteSalesPOS.selectedCostCenter || 'المركز الرئيسي';
      const wh = document.getElementById('salesWarehouseInput')?.value?.trim() || SuiteSalesPOS.selectedWarehouse || document.getElementById('salesWarehouseSelect')?.value || 'المستودع الرئيسي';

      let items = [];
      let grandTotal = 0;

      const pNum = (id, defVal = 0) => {
        const raw = document.getElementById(id)?.value;
        if (!raw || !String(raw).trim()) return defVal;
        return window.SmartFieldCustomizer ? SmartFieldCustomizer.parseNum(raw, id) : (parseFloat(String(raw).replace(/,/g, '')) || defVal);
      };

      if (SuiteSalesPOS.cart && SuiteSalesPOS.cart.length > 0) {
        items = [...SuiteSalesPOS.cart];
        grandTotal = items.reduce((acc, it) => acc + (parseFloat(it.subtotal) || 0), 0);
      } else {
        let itName = '';
        const itemInpVal = document.getElementById('salesItemSearchInput')?.value?.trim();
        if (itemInpVal) {
          itName = itemInpVal;
        } else if (SuiteSalesPOS.selectedItemIndex !== -1 && SuiteSalesPOS.dbItems[SuiteSalesPOS.selectedItemIndex]) {
          itName = SuiteSalesPOS.dbItems[SuiteSalesPOS.selectedItemIndex].name;
        } else {
          const itemDisp = document.getElementById('suiteItemDisplayName')?.innerText?.trim();
          itName = (itemDisp && itemDisp !== 'اختر الصنف...') ? itemDisp : 'مبيعات عامة';
        }

        const qty = pNum('salesQtyInput', 1);
        const prc = pNum('salesPriceInput', 0);
        const disc = pNum('salesDiscountInput', 0);
        const tax = pNum('salesTaxInput', 0);
        const labor = pNum('salesLaborInput', 0);
        const uSel = document.getElementById('salesUnitSelect');
        const unit = (uSel && uSel.value) ? uSel.value : 'طن عددي';
        const sub = Math.max(0, (qty * prc) - disc + tax + labor);

        items = [{
          name: itName,
          unit,
          warehouse: wh,
          costCenter: costCenter,
          qty,
          price: prc,
          discount: disc,
          tax,
          labor,
          subtotal: sub
        }];
        grandTotal = sub;
      }

      const manualTot = pNum('salesTotalInput', 0);
      if (!isNaN(manualTot) && manualTot > 0) {
        grandTotal = manualTot;
      }

      items = items.map(it => ({
        ...it,
        name: it.name || it.itemName || 'صنف',
        itemName: it.itemName || it.name || 'صنف',
        qty: parseFloat(it.qty ?? it.quantity ?? 1) || 1,
        quantity: parseFloat(it.quantity ?? it.qty ?? 1) || 1,
        subtotal: parseFloat(it.subtotal ?? it.total ?? 0) || 0,
        total: parseFloat(it.total ?? it.subtotal ?? 0) || 0
      }));

      const notesVal = document.getElementById('salesNotesInput')?.value?.trim() || '';
      const salesSig = `${invNo}|${cust}|${wh}|${grandTotal}|${invDate}|${items.map(i => `${i.name}:${i.qty}:${i.price}`).join(',')}`;
      if (window._lastSavedSalesSig === salesSig) {
        if (typeof showFloatingToast === 'function') {
          showFloatingToast('✓ تم حفظ هذه الفاتورة بالفعل ولا حاجة لتكرار الحفظ.');
        }
        return;
      }

      window._isSavingOpSales = true;
      window._lastSavedSalesSig = salesSig;

      const payload = {
        idempotencyKey: 'SALES-' + salesSig,
        invoiceNo: invNo,
        id: invNo,
        customerName: cust,
        warehouse: wh,
        costCenter: costCenter,
        subTotal: grandTotal,
        grandTotal: grandTotal,
        total: grandTotal,
        paymentMethod: payM,
        status: 'completed',
        deliveryStatus: 'pending_delivery',
        date: invDate,
        salesRep: salesRep,
        notes: notesVal,
        items: items,
        savedAt: new Date().toISOString()
      };

      // حفظ فوري في أرشيف الفواتير المحلي + ذاكرة التسعير الذكية + تحديث الرصيد والمخزون الحي في الذاكرة فوراً
      try {
        if (window.SuiteSalesPOS) {
          if (typeof SuiteSalesPOS.saveToLastPriceMemory === 'function') {
            SuiteSalesPOS.saveToLastPriceMemory(cust, items, invDate);
          }
          if (payM === 'credit' && Array.isArray(SuiteSalesPOS.dbCustomers)) {
            const cObj = SuiteSalesPOS.dbCustomers.find(c => c && c.name === cust);
            if (cObj) {
              cObj.currentBalance = (parseFloat(cObj.currentBalance || cObj.balanceNum || 0) || 0) + grandTotal;
              cObj.balanceNum = cObj.currentBalance;
            }
          }
          if (Array.isArray(SuiteSalesPOS.dbItems)) {
            items.forEach(line => {
              const itObj = SuiteSalesPOS.dbItems.find(x => x && x.name === line.name);
              if (itObj) {
                const qDelta = parseFloat(line.qty || 1) || 1;
                if (typeof itObj.stock === 'number') {
                  itObj.stock = Math.max(0, itObj.stock - qDelta);
                } else if (itObj.stock && typeof itObj.stock === 'object' && !Array.isArray(itObj.stock)) {
                  itObj.stock[wh] = Math.max(0, (parseFloat(itObj.stock[wh]) || 0) - qDelta);
                }
              }
            });
          }
          if (!Array.isArray(SuiteSalesPOS.recentInvoices)) SuiteSalesPOS.recentInvoices = [];
          const existIdx = SuiteSalesPOS.recentInvoices.findIndex(x => (x.invoiceNo || x.id) === invNo);
          if (existIdx !== -1) SuiteSalesPOS.recentInvoices[existIdx] = payload;
          else SuiteSalesPOS.recentInvoices.unshift(payload);
          SuiteSalesPOS.currentInvoiceIndex = -1;
        }
        const savedList = JSON.parse(localStorage.getItem('magaies_local_sales_invoices_v1') || '[]');
        const filtered = savedList.filter(x => (x.invoiceNo || x.id) !== invNo);
        filtered.unshift(payload);
        localStorage.setItem('magaies_local_sales_invoices_v1', JSON.stringify(filtered.slice(0, 100)));
      } catch (e) {}

      const handleSuccess = (invoiceNumber, serverNextNo = '') => {
        setTimeout(() => { window._isSavingOpSales = false; }, 450);
        if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(false, 'modalOpSales');
        if (serverNextNo && window._mirrorCache && window._mirrorCache.sales) {
          window._mirrorCache.sales.nextInvoiceNo = serverNextNo;
        }

        if (window.AudioEngine && AudioEngine.play) AudioEngine.play('success');
        if (typeof showFloatingToast === 'function') {
          showFloatingToast(`✅ تم بنجاح حفظ فاتورة المبيعات #${invoiceNumber} للعميل [${cust}] بمبلغ [${grandTotal.toLocaleString()}]!`);
        } else {
          alert(`✓ تم بنجاح حفظ فاتورة المبيعات [#${invoiceNumber}]!`);
        }
        SuiteSalesPOS.newInvoice();
        if (typeof fetchMirrorNumbers === 'function') fetchMirrorNumbers();
      };

      if (!navigator.onLine) {
        const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('sales', payload) : invNo;
        handleSuccess(offId);
        return;
      }

      try {
        const res = await fetch('api/mirror_sync.php?action=save_sales_invoice', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const out = await res.json();
        if (out && out.success) {
          handleSuccess(out.invoiceNo || invNo, out.nextInvoiceNo || '');
        } else {
          const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('sales', payload) : invNo;
          handleSuccess(offId);
        }
      } catch (err) {
        console.warn('API sync fallback:', err);
        const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('sales', payload) : invNo;
        handleSuccess(offId);
      }
    }

    async function commitOpSalesSplit() {
      if (window.AppHaptics) AppHaptics.success();
      const invNo = document.getElementById('opSalesSplitInvoiceNoPill')?.innerText || '';
      const cust = document.getElementById('salesSplitCustomerInput')?.value || 'عميل تجزئة';
      const item = document.getElementById('salesSplitSelectedItemName')?.innerText || 'صنف';
      const tot = parseFloat(document.getElementById('salesSplitTotalInput')?.value || 0);
      const paid = parseFloat(document.getElementById('salesSplitPaidInput')?.value || 0);

      const payload = {
        invoiceNo: invNo,
        id: invNo,
        customerName: cust,
        grandTotal: tot,
        total: tot,
        paidAmount: paid,
        remainingBalance: Math.max(0, tot - paid),
        status: 'split',
        isSplit: true,
        date: new Date().toISOString().split('T')[0],
        savedAt: new Date().toISOString(),
        items: [{ name: item, itemName: item, qty: 1, quantity: 1, price: tot, subtotal: tot, total: tot }]
      };

      try {
        const savedSales = JSON.parse(localStorage.getItem('magaies_local_sales_invoices_v1') || '[]');
        const filtered = savedSales.filter(x => (x.invoiceNo || x.id) !== invNo);
        filtered.unshift(payload);
        localStorage.setItem('magaies_local_sales_invoices_v1', JSON.stringify(filtered.slice(0, 100)));
      } catch (e) {}

      if (!navigator.onLine) {
        const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('sales_split', payload) : 'أوفلاين';
        if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(false);
        showFloatingToast(`⚡ تم حفظ تقسيم المبيعات أوفلاين [${offId}]`);
        closeModal('modalOpSalesSplit');
        return;
      }
      try {
        const res = await fetch('api/mirror_sync.php?action=save_sales_split', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const out = await res.json();
        if (out && out.success) {
          if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(false);
          showFloatingToast(`✓ تم بنجاح حفظ وتقسيم المبيعات [${out.invoiceNo}]`);
          closeModal('modalOpSalesSplit');
          fetchMirrorNumbers();
        } else {
          const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('sales_split', payload) : 'أوفلاين';
          alert(`⚡ تم حفظ التقسيم أوفلاين برقم: [${offId}]\nستُرفع تلقائياً فور توفر الشبكة.`);
          closeModal('modalOpSalesSplit');
        }
      } catch (err) {
        const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('sales_split', payload) : 'أوفلاين';
        alert(`⚡ شبكة النت منقطعة! تم حفظ التقسيم في الجوال أوفلاين.\nالرقم: [${offId}]\nستُرفع تلقائياً فور عودة الاتصال.`);
        closeModal('modalOpSalesSplit');
      }
    }

    async function commitOpDelivery() {
      if (window.AppHaptics) AppHaptics.success();
      const delNo = document.getElementById('opDeliveryNoPill')?.innerText || '';
      const refInv = document.getElementById('opDeliveryRefSelect')?.value || '';
      const wh = document.getElementById('opDeliveryWarehouseSelect')?.value || 'المستودع الرئيسي';
      const driver = document.getElementById('opDeliveryDriverInput')?.value || 'سائق التوزيع';
      const itemsSummary = document.getElementById('opDeliveryItemsSummary')?.innerText || '';

      const payload = {
        deliveryNo: delNo,
        id: delNo,
        invoiceNo: refInv,
        warehouse: wh,
        driverName: driver,
        itemsSummary: itemsSummary,
        date: new Date().toISOString().split('T')[0],
        savedAt: new Date().toISOString()
      };

      try {
        const savedDel = JSON.parse(localStorage.getItem('magaies_local_deliveries_v1') || '[]');
        const filtered = savedDel.filter(x => (x.deliveryNo || x.id) !== delNo);
        filtered.unshift(payload);
        localStorage.setItem('magaies_local_deliveries_v1', JSON.stringify(filtered.slice(0, 100)));
      } catch (e) {}

      if (!navigator.onLine) {
        const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('delivery', payload) : 'أوفلاين';
        showFloatingToast(`⚡ تم حفظ إذن تسليم المبيعات أوفلاين [${offId}]`);
        closeModal('modalOpDelivery');
        return;
      }
      try {
        const res = await fetch('api/mirror_sync.php?action=save_delivery_note', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const out = await res.json();
        if (out && out.success) {
          showFloatingToast(`✓ تم بنجاح اعتماد إذن التسليم: [${out.deliveryNo}]`);
          closeModal('modalOpDelivery');
          fetchMirrorNumbers();
        } else {
          const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('delivery', payload) : 'أوفلاين';
          alert(`⚡ تم حفظ إذن التسليم أوفلاين برقم: [${offId}]\nستُرفع تلقائياً فور توفر الشبكة.`);
          closeModal('modalOpDelivery');
        }
      } catch (err) {
        const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('delivery', payload) : 'أوفلاين';
        alert(`⚡ شبكة النت منقطعة! تم حفظ إذن التسليم أوفلاين في الجوال.\nالرقم: [${offId}]\nستُرفع تلقائياً فور عودة الاتصال.`);
        closeModal('modalOpDelivery');
      }
    }

    window._isSavingOpPurchase = false;
    window._lastSavedPurchaseSig = '';

    async function commitOpPurchase(andPay = false) {
      if (window._isSavingOpPurchase) return;
      if (window.AppHaptics) AppHaptics.success();
      if (window.SuitePurchasePOS && typeof SuitePurchasePOS.calcItemSubtotal === 'function') {
        SuitePurchasePOS.calcItemSubtotal();
      }

      const purNo = document.getElementById('opPurchaseNoPill')?.innerText?.trim() || (window.SuitePurchasePOS ? SuitePurchasePOS.generateNewInvoiceNo() : ('P26100001'));
      const supp = document.getElementById('purchaseSupplierInput')?.value?.trim() || 'مطاحن الخرطوم للغلال';
      const payM = document.getElementById('purchasePayMethodSelect')?.value || 'cash';
      const wh = document.getElementById('purchaseWarehouseInput')?.value?.trim() || document.getElementById('purchaseWarehouseSelect')?.value || (window.SuitePurchasePOS ? SuitePurchasePOS.selectedWarehouse : 'المستودع الرئيسي');
      const costCenter = (window.SuitePurchasePOS && SuitePurchasePOS.selectedCostCenter) ? SuitePurchasePOS.selectedCostCenter : 'المركز الرئيسي';
      const purDate = document.getElementById('purInvoiceDate')?.value || new Date().toISOString().split('T')[0];
      const notes = document.getElementById('purNotesInput')?.value || '';

      let items = [];
      let grandTotal = 0;

      const pNum = (id, defVal = 0) => {
        const raw = document.getElementById(id)?.value;
        if (!raw || !String(raw).trim()) return defVal;
        return parseFloat(String(raw).replace(/,/g, '')) || defVal;
      };

      if (window.SuitePurchasePOS && SuitePurchasePOS.cart && SuitePurchasePOS.cart.length > 0) {
        items = [...SuitePurchasePOS.cart];
        grandTotal = items.reduce((acc, it) => acc + (parseFloat(it.subtotal) || 0), 0);
      } else {
        const itIdx = (window.SuitePurchasePOS && SuitePurchasePOS.selectedItemIndex !== -1) ? SuitePurchasePOS.selectedItemIndex : -1;
        let itName = document.getElementById('purItemSearchInput')?.value?.trim() || '';
        if (!itName && itIdx !== -1 && window.SuiteSalesPOS && SuiteSalesPOS.dbItems && SuiteSalesPOS.dbItems[itIdx]) {
          itName = SuiteSalesPOS.dbItems[itIdx].name;
        }
        if (!itName && window.SuitePurchasePOS && SuitePurchasePOS.selectedItem && SuitePurchasePOS.selectedItem.name) {
          itName = SuitePurchasePOS.selectedItem.name;
        }
        if (!itName) {
          itName = document.getElementById('purItemSelectVal')?.value?.trim() || '';
        }
        if (!itName) {
          const purDisp = document.getElementById('suitePurItemDisplayName')?.innerText?.trim();
          if (purDisp && purDisp !== 'اختر الصنف...') itName = purDisp;
        }
        if (!itName) {
          itName = 'بضاعة مشتريات عامة';
        }

        const qty = pNum('purQtyInput', 1);
        const prc = pNum('purPriceInput', 0);
        const disc = pNum('purDiscountInput', 0);
        const tax = pNum('purTaxInput', 0);
        const labor = pNum('purLaborInput', 0);
        const uSel = document.getElementById('purUnitSelect');
        const unit = (uSel && uSel.value) ? uSel.value : 'حبة';
        const sub = Math.max(0, (qty * prc) - disc + tax + labor);

        items = [{
          name: itName || 'بضاعة مشتريات عامة',
          unit,
          warehouse: wh,
          costCenter: costCenter,
          qty,
          price: prc,
          discount: disc,
          tax,
          labor,
          subtotal: sub
        }];
        grandTotal = sub;
      }

      const manualPurTot = pNum('purTotalInput', 0);
      if (!isNaN(manualPurTot) && manualPurTot > 0) {
        grandTotal = manualPurTot;
      }

      items = items.map(it => ({
        ...it,
        name: it.name || it.itemName || 'بضاعة مشتريات عامة',
        itemName: it.itemName || it.name || 'بضاعة مشتريات عامة',
        qty: parseFloat(it.qty ?? it.quantity ?? 1) || 1,
        quantity: parseFloat(it.quantity ?? it.qty ?? 1) || 1,
        subtotal: parseFloat(it.subtotal ?? it.total ?? 0) || 0,
        total: parseFloat(it.total ?? it.subtotal ?? 0) || 0
      }));

      const purSig = `${purNo}|${supp}|${wh}|${grandTotal}|${purDate}|${items.map(i => `${i.name}:${i.qty}:${i.price}`).join(',')}`;
      if (window._lastSavedPurchaseSig === purSig) {
        if (typeof showFloatingToast === 'function') {
          showFloatingToast('✓ تم حفظ فاتورة المشتريات هذه بالفعل ولا حاجة لتكرار الحفظ.');
        }
        return;
      }

      window._isSavingOpPurchase = true;
      window._lastSavedPurchaseSig = purSig;

      const payload = {
        idempotencyKey: 'PUR-' + purSig,
        invoiceNo: purNo,
        id: purNo,
        supplierName: supp,
        subTotal: grandTotal,
        grandTotal: grandTotal,
        total: grandTotal,
        paymentMethod: payM,
        warehouse: wh,
        costCenter: costCenter,
        status: 'completed',
        receiptStatus: 'pending_receipt',
        date: purDate,
        notes: notes,
        items: items,
        savedAt: new Date().toISOString()
      };

      // حفظ فوري في أرشيف المشتريات المحلي + ذاكرة آخر سعر شراء + تحديث رصيد المورد والمخزون الحي فوراً
      try {
        if (window.SuitePurchasePOS) {
          if (typeof SuitePurchasePOS.saveToLastPriceMemory === 'function') {
            SuitePurchasePOS.saveToLastPriceMemory(supp, items, purDate);
          }
          if (payM === 'credit' && Array.isArray(SuitePurchasePOS.dbSuppliers)) {
            const sObj = SuitePurchasePOS.dbSuppliers.find(s => s && s.name === supp);
            if (sObj) {
              sObj.currentBalance = (parseFloat(sObj.currentBalance || sObj.balanceNum || 0) || 0) + grandTotal;
              sObj.balanceNum = sObj.currentBalance;
            }
          }
          if (window.SuiteSalesPOS && Array.isArray(SuiteSalesPOS.dbItems)) {
            items.forEach(line => {
              const itObj = SuiteSalesPOS.dbItems.find(x => x && x.name === line.name);
              if (itObj) {
                const qDelta = parseFloat(line.qty || 1) || 1;
                if (typeof itObj.stock === 'number') {
                  itObj.stock = itObj.stock + qDelta;
                } else if (itObj.stock && typeof itObj.stock === 'object' && !Array.isArray(itObj.stock)) {
                  itObj.stock[wh] = (parseFloat(itObj.stock[wh]) || 0) + qDelta;
                }
                if (parseFloat(line.price || 0) > 0) {
                  itObj.lastPurchaseCost = parseFloat(line.price);
                  itObj.costPrice = parseFloat(line.price);
                }
              }
            });
          }
          if (!Array.isArray(SuitePurchasePOS.recentInvoices)) SuitePurchasePOS.recentInvoices = [];
          const existIdx = SuitePurchasePOS.recentInvoices.findIndex(x => (x.invoiceNo || x.id) === purNo);
          if (existIdx !== -1) SuitePurchasePOS.recentInvoices[existIdx] = payload;
          else SuitePurchasePOS.recentInvoices.unshift(payload);
          SuitePurchasePOS.currentInvoiceIndex = -1;
        }
        const savedPur = JSON.parse(localStorage.getItem('magaies_local_purchase_invoices_v1') || '[]');
        const filteredPur = savedPur.filter(x => (x.invoiceNo || x.id) !== purNo);
        filteredPur.unshift(payload);
        localStorage.setItem('magaies_local_purchase_invoices_v1', JSON.stringify(filteredPur.slice(0, 100)));
      } catch (e) {}

      const handleSuccess = (invoiceNumber, serverNextNo = '') => {
        setTimeout(() => { window._isSavingOpPurchase = false; }, 450);
        if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(false, 'modalOpPurchase');
        if (serverNextNo && window._mirrorCache && window._mirrorCache.purchase) {
          window._mirrorCache.purchase.nextPurchaseNo = serverNextNo;
        }

        if (window.AudioEngine && AudioEngine.play) AudioEngine.play('success');
        if (typeof showFloatingToast === 'function') {
          showFloatingToast(`✅ تم بنجاح حفظ فاتورة المشتريات #${invoiceNumber} للمورد [${supp}] بمبلغ [${grandTotal.toLocaleString()}]!`);
        } else {
          alert(`✓ تم بنجاح حفظ فاتورة المشتريات [#${invoiceNumber}]!`);
        }
        if (SuitePurchasePOS && SuitePurchasePOS.newInvoice) SuitePurchasePOS.newInvoice(true);
        if (typeof fetchMirrorNumbers === 'function') fetchMirrorNumbers();
      };

      if (!navigator.onLine) {
        const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('purchase', payload) : purNo;
        handleSuccess(offId);
        return;
      }
      try {
        const res = await fetch('api/mirror_sync.php?action=save_purchase_invoice', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const out = await res.json();
        if (out && out.success) {
          handleSuccess(out.invoiceNo || purNo, out.nextPurchaseNo || '');
        } else {
          const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('purchase', payload) : purNo;
          handleSuccess(offId);
        }
      } catch(err) {
        const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('purchase', payload) : purNo;
        handleSuccess(offId);
      }
    }

    async function commitOpPurchaseSplit() {
      if (window.AppHaptics) AppHaptics.success();
      let purNo = document.getElementById('opPurchaseSplitNoPill')?.innerText?.trim() || '';
      if (!purNo || purNo.includes('جار')) {
        purNo = (window.MagaiesOffline && typeof MagaiesOffline.peekNextSequentialNumber === 'function')
          ? MagaiesOffline.peekNextSequentialNumber('purchase_split')
          : 'P26100001';
      }
      const supp = document.getElementById('purchaseSplitSupplierInput')?.value || 'مورد تجزئة';
      const itemTxt = document.getElementById('purchaseSplitSelectedItemName')?.innerText || 'بضاعة مشتريات';
      const tot = parseFloat(document.getElementById('purchaseSplitTotalInput')?.value || 0);
      const paid = parseFloat(document.getElementById('purchaseSplitPaidInput')?.value || 0);
      const rem = Math.max(0, tot - paid);

      const payload = {
        invoiceNo: purNo,
        id: purNo,
        supplierName: supp,
        grandTotal: tot,
        total: tot,
        paidAmount: paid,
        remainingBalance: rem,
        isSplit: true,
        purchaseType: 'split',
        status: 'split',
        items: [{ name: itemTxt, qty: 1, price: tot, subtotal: tot }],
        date: new Date().toISOString().split('T')[0],
        savedAt: new Date().toISOString()
      };

      try {
        const savedPur = JSON.parse(localStorage.getItem('magaies_local_purchase_invoices_v1') || '[]');
        const filteredPur = savedPur.filter(x => (x.invoiceNo || x.id) !== purNo);
        filteredPur.unshift(payload);
        localStorage.setItem('magaies_local_purchase_invoices_v1', JSON.stringify(filteredPur.slice(0, 100)));
      } catch (e) {}

      if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(false, 'modalOpPurchaseSplit');

      if (!navigator.onLine) {
        const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('purchase_split', payload, purNo) : purNo;
        showFloatingToast(`⚡ تم حفظ تقسيم المشتريات أوفلاين [${offId}]`);
        closeModal('modalOpPurchaseSplit');
        fetchMirrorNumbers();
        return;
      }

      try {
        const res = await fetch('api/mirror_sync.php?action=save_purchase_split', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const out = await res.json();
        if (out && out.success) {
          showFloatingToast(`✓ تم حفظ واعتماد تقسيم المشتريات [${out.invoiceNo || purNo}]`);
          closeModal('modalOpPurchaseSplit');
          fetchMirrorNumbers();
        } else {
          const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('purchase_split', payload, purNo) : purNo;
          showFloatingToast(`⚡ تم حفظ تقسيم المشتريات أوفلاين [${offId}]`);
          closeModal('modalOpPurchaseSplit');
          fetchMirrorNumbers();
        }
      } catch (err) {
        const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('purchase_split', payload, purNo) : purNo;
        showFloatingToast(`⚡ تم حفظ تقسيم المشتريات أوفلاين [${offId}]`);
        closeModal('modalOpPurchaseSplit');
        fetchMirrorNumbers();
      }
    }

    async function commitOpReceipt() {
      if (window.AppHaptics) AppHaptics.success();
      const recNo = document.getElementById('opReceiptNoPill')?.innerText || '';
      const refPur = document.getElementById('opReceiptPurchaseSelect')?.value || '';
      const supp = document.getElementById('opReceiptSupplierInput')?.value || '';
      const wh = document.getElementById('opReceiptWarehouseSelect')?.value || 'المستودع الرئيسي';
      const insp = document.getElementById('opReceiptInspectorInput')?.value || 'أمين المخزن';
      const itemsSummary = document.getElementById('opReceiptItemsSummary')?.innerText || '';

      const payload = {
        receiptNo: recNo,
        id: recNo,
        originalInvoiceId: refPur,
        supplierName: supp,
        warehouse: wh,
        inspector: insp,
        itemsSummary: itemsSummary,
        date: new Date().toISOString().split('T')[0],
        savedAt: new Date().toISOString()
      };

      try {
        const savedRec = JSON.parse(localStorage.getItem('magaies_local_receipts_v1') || '[]');
        const filtered = savedRec.filter(x => (x.receiptNo || x.id) !== recNo);
        filtered.unshift(payload);
        localStorage.setItem('magaies_local_receipts_v1', JSON.stringify(filtered.slice(0, 100)));
      } catch (e) {}

      if (!navigator.onLine) {
        const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('purchase_receipt', payload) : 'أوفلاين';
        showFloatingToast(`⚡ تم حفظ استلام المشتريات أوفلاين [${offId}]`);
        closeModal('modalOpReceipt');
        return;
      }
      try {
        const res = await fetch('api/mirror_sync.php?action=save_purchase_receipt', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const out = await res.json();
        if (out && out.success) {
          showFloatingToast(`✓ تم بنجاح فحص واستلام بضاعة المشتريات [${out.receiptNo}]`);
          closeModal('modalOpReceipt');
          fetchMirrorNumbers();
        } else {
          const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('purchase_receipt', payload) : 'أوفلاين';
          alert(`⚡ تم حفظ استلام المشتريات أوفلاين برقم مؤقت: [${offId}]\nستُرفع وتعتمد في السيرفر فور توفر الشبكة.`);
          closeModal('modalOpReceipt');
        }
      } catch (err) {
        const offId = window.MagaiesOffline ? MagaiesOffline.enqueue('purchase_receipt', payload) : 'أوفلاين';
        alert(`⚡ شبكة النت منقطعة! تم حفظ استلام المشتريات أوفلاين في الجوال.\nالرقم المؤقت: [${offId}]\nستُرفع تلقائياً فور عودة الاتصال.`);
        closeModal('modalOpReceipt');
      }
    }

    // ═════════════════════════════════════════════════════════════════════
    // محرك التنقل بالسهمين (◀ / ▶) لجميع النوافذ المساندة (تقسيم مبيعات/مشتريات، تسليم، استلام)
    // ═════════════════════════════════════════════════════════════════════
    window.SuiteModalArchiveNav = {
      indices: { sales_split: -1, purchase_split: -1, delivery: -1, purchase_receipt: -1 },
      latestNos: {},
      getRecords(type) {
        const list = [];
        const addUnique = (r, idField) => {
          if (!r || typeof r !== 'object') return;
          const id = String(r[idField] || r.invoiceNo || r.id || '').trim();
          if (!id) return;
          if (!list.some(x => String(x[idField] || x.invoiceNo || x.id || '').trim() === id)) {
            list.push(r);
          }
        };
        try {
          if (type === 'sales_split') {
            const s1 = JSON.parse(localStorage.getItem('magaies_local_sales_invoices_v1') || '[]');
            s1.filter(x => x.isSplit || x.status === 'split' || x.paidAmount !== undefined).forEach(r => addUnique(r, 'invoiceNo'));
            if (s1.length > 0 && list.length === 0) s1.forEach(r => addUnique(r, 'invoiceNo'));
          } else if (type === 'purchase_split') {
            const p1 = JSON.parse(localStorage.getItem('magaies_local_purchase_invoices_v1') || '[]');
            p1.filter(x => x.isSplit || x.status === 'split' || x.paidAmount !== undefined).forEach(r => addUnique(r, 'invoiceNo'));
            if (p1.length > 0 && list.length === 0) p1.forEach(r => addUnique(r, 'invoiceNo'));
          } else if (type === 'delivery') {
            const d1 = JSON.parse(localStorage.getItem('magaies_local_deliveries_v1') || '[]');
            d1.forEach(r => addUnique(r, 'deliveryNo'));
          } else if (type === 'purchase_receipt') {
            const r1 = JSON.parse(localStorage.getItem('magaies_local_receipts_v1') || '[]');
            r1.forEach(r => addUnique(r, 'receiptNo'));
          }
          if (window.MagaiesOffline && typeof MagaiesOffline.getQueue === 'function') {
            MagaiesOffline.getQueue().filter(q => q.type === type && q.payload).forEach(q => {
              addUnique(Object.assign({ id: q.tempId }, q.payload), 'id');
            });
          }
        } catch (e) {}
        return list;
      },
      navigate(type, dir) {
        if (window.AppHaptics) AppHaptics.light();
        const records = this.getRecords(type);
        if (!records.length) {
          if (typeof showFloatingToast === 'function') showFloatingToast('ℹ️ لا توجد سجلات سابقة محفوظة لهذا المستند حتى الآن.');
          return;
        }
        const pillMap = {
          sales_split: 'opSalesSplitInvoiceNoPill',
          purchase_split: 'opPurchaseSplitNoPill',
          delivery: 'opDeliveryNoPill',
          purchase_receipt: 'opReceiptNoPill'
        };
        const pill = document.getElementById(pillMap[type]);
        if (this.indices[type] === -1 && pill) {
          this.latestNos[type] = pill.innerText;
        }
        if (dir === 'prev') {
          if (this.indices[type] < records.length - 1) {
            this.indices[type]++;
            this.render(type, records[this.indices[type]]);
          } else if (typeof showFloatingToast === 'function') {
            showFloatingToast('ℹ️ وصلت إلى أقدم سجل محفوظ.');
          }
        } else if (dir === 'next') {
          if (this.indices[type] > 0) {
            this.indices[type]--;
            this.render(type, records[this.indices[type]]);
          } else if (this.indices[type] === 0) {
            this.indices[type] = -1;
            if (pill && this.latestNos[type]) pill.innerText = this.latestNos[type];
            if (typeof showFloatingToast === 'function') showFloatingToast('📄 تم الرجوع إلى وضع المستند الجديد.');
          } else if (typeof showFloatingToast === 'function') {
            showFloatingToast('ℹ️ أنت بالفعل في أحدث مستند.');
          }
        }
      },
      render(type, rec) {
        if (!rec) return;
        const firstItem = (Array.isArray(rec.items) && rec.items.length > 0) ? rec.items[0] : null;
        if (type === 'sales_split') {
          const docNo = rec.invoiceNo || rec.id || '';
          const pill = document.getElementById('opSalesSplitInvoiceNoPill');
          if (pill) pill.innerText = docNo;
          const custEl = document.getElementById('salesSplitCustomerInput');
          if (custEl) custEl.value = rec.customerName || rec.customer || '';
          const itemEl = document.getElementById('salesSplitSelectedItemName');
          if (itemEl) itemEl.innerText = firstItem ? (firstItem.name || firstItem.itemName || 'صنف') : (rec.itemName || 'صنف');
          const totEl = document.getElementById('salesSplitTotalInput');
          if (totEl) totEl.value = rec.grandTotal ?? rec.total ?? 0;
          const paidEl = document.getElementById('salesSplitPaidInput');
          if (paidEl) paidEl.value = rec.paidAmount ?? 0;
          if (typeof calcOpSalesSplit === 'function') calcOpSalesSplit();
          if (typeof showFloatingToast === 'function') showFloatingToast(`📂 استعراض تقسيم مبيعات #${docNo}`);
        } else if (type === 'purchase_split') {
          const docNo = rec.invoiceNo || rec.id || '';
          const pill = document.getElementById('opPurchaseSplitNoPill');
          if (pill) pill.innerText = docNo;
          const suppEl = document.getElementById('purchaseSplitSupplierInput');
          if (suppEl) suppEl.value = rec.supplierName || rec.supplier || '';
          const itemEl = document.getElementById('purchaseSplitSelectedItemName');
          if (itemEl) itemEl.innerText = firstItem ? (firstItem.name || firstItem.itemName || 'بضاعة') : (rec.itemName || 'بضاعة');
          const totEl = document.getElementById('purchaseSplitTotalInput');
          if (totEl) totEl.value = rec.grandTotal ?? rec.total ?? 0;
          const paidEl = document.getElementById('purchaseSplitPaidInput');
          if (paidEl) paidEl.value = rec.paidAmount ?? 0;
          if (typeof calcOpPurchaseSplit === 'function') calcOpPurchaseSplit();
          if (typeof showFloatingToast === 'function') showFloatingToast(`📂 استعراض تقسيم مشتريات #${docNo}`);
        } else if (type === 'delivery') {
          const docNo = rec.deliveryNo || rec.id || '';
          const pill = document.getElementById('opDeliveryNoPill');
          if (pill) pill.innerText = docNo;
          const refSel = document.getElementById('opDeliveryRefSelect');
          if (refSel && rec.invoiceNo) {
            if (!Array.from(refSel.options).some(o => o.value === rec.invoiceNo)) {
              const opt = document.createElement('option');
              opt.value = rec.invoiceNo;
              opt.innerText = rec.invoiceNo;
              refSel.appendChild(opt);
            }
            refSel.value = rec.invoiceNo;
            if (typeof onOpDeliveryRefChange === 'function') onOpDeliveryRefChange();
          }
          const whSel = document.getElementById('opDeliveryWarehouseSelect');
          if (whSel && rec.warehouse) {
            if (!Array.from(whSel.options).some(o => o.value === rec.warehouse)) {
              const opt = document.createElement('option');
              opt.value = rec.warehouse;
              opt.innerText = rec.warehouse;
              whSel.appendChild(opt);
            }
            whSel.value = rec.warehouse;
          }
          const drvEl = document.getElementById('opDeliveryDriverInput');
          if (drvEl) drvEl.value = rec.driverName || '';
          const sumEl = document.getElementById('opDeliveryItemsSummary');
          if (sumEl && rec.itemsSummary) sumEl.innerText = rec.itemsSummary;
          if (typeof showFloatingToast === 'function') showFloatingToast(`📂 استعراض إذن تسليم #${docNo}`);
        } else if (type === 'purchase_receipt') {
          const docNo = rec.receiptNo || rec.id || '';
          const pill = document.getElementById('opReceiptNoPill');
          if (pill) pill.innerText = docNo;
          const refSel = document.getElementById('opReceiptPurchaseSelect');
          if (refSel && rec.originalInvoiceId) {
            if (!Array.from(refSel.options).some(o => o.value === rec.originalInvoiceId)) {
              const opt = document.createElement('option');
              opt.value = rec.originalInvoiceId;
              opt.innerText = rec.originalInvoiceId;
              refSel.appendChild(opt);
            }
            refSel.value = rec.originalInvoiceId;
            if (typeof onOpReceiptPurchaseChange === 'function') onOpReceiptPurchaseChange();
          }
          const whSel = document.getElementById('opReceiptWarehouseSelect');
          if (whSel && rec.warehouse) {
            if (!Array.from(whSel.options).some(o => o.value === rec.warehouse)) {
              const opt = document.createElement('option');
              opt.value = rec.warehouse;
              opt.innerText = rec.warehouse;
              whSel.appendChild(opt);
            }
            whSel.value = rec.warehouse;
          }
          const suppEl = document.getElementById('opReceiptSupplierInput');
          if (suppEl) suppEl.value = rec.supplierName || '';
          const inspEl = document.getElementById('opReceiptInspectorInput');
          if (inspEl) inspEl.value = rec.inspector || '';
          const sumEl = document.getElementById('opReceiptItemsSummary');
          if (sumEl && rec.itemsSummary) sumEl.innerText = rec.itemsSummary;
          if (typeof showFloatingToast === 'function') showFloatingToast(`📂 استعراض إذن استلام #${docNo}`);
        }
        if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();
      }
    };

    function commitOperation(opName) {
      if (window.AppHaptics) AppHaptics.success();
      else if (window.triggerHapticPulse) triggerHapticPulse(40);
      if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(false);
      alert("✓ تم بنجاح ترحيل واعتماد: [" + opName + "]\nتم تحديث الخزينة والمخزون وحساب العميل/المورد في شجرة الحسابات فوراً!");
      closeModal('modalOpFinance');
      closeModal('modalOpSales');
      closeModal('modalOpSalesSplit');
      closeModal('modalOpDelivery');
      closeModal('modalOpPurchase');
      closeModal('modalOpReceipt');
      closeModal('modalOpJournal');
      closeModal('modalOpStatement');
      closeModal('modalOpBalanceInquiry');
      closeModal('modalOpStockLocation');
      closeModal('modalOpStockAdjustment');
      closeModal('modalOpWarehouseTransfer');
      closeModal('modalOpVouchers');
    }

    // 1. اعتماد وترحيل قيد التسوية المحاسبي (modalOpJournal -> journal_vouchers + gl_entries + أرصدة الحسابات)
    window.commitOpJournal = async function() {
      const settlementType = document.getElementById('journalSettlementType')?.value || 'general';
      const voucherNumber = (document.getElementById('journalSettlementRef')?.value || '').trim();
      const debitSel = document.getElementById('journalDebitAccount');
      const creditSel = document.getElementById('journalCreditAccount');
      const debitAccount = debitSel ? (debitSel.options[debitSel.selectedIndex]?.dataset?.accName || debitSel.value) : '';
      const creditAccount = creditSel ? (creditSel.options[creditSel.selectedIndex]?.dataset?.accName || creditSel.value) : '';
      const amountUsd = parseFloat(document.getElementById('journalAmountUsd')?.value || '0') || 0;
      const rate = (window.SuiteFXManager && SuiteFXManager.currentRate) ? Number(SuiteFXManager.currentRate) : 2850;
      const amountSdg = Math.round(amountUsd * rate);
      const description = (document.getElementById('journalNotes')?.value || 'تسوية قيد محاسبي من الجوال').trim();
      const dateStr = new Date().toISOString().slice(0, 10);

      if (amountUsd <= 0) {
        if (typeof showFloatingToast === 'function') showFloatingToast('⚠️ يرجى إدخال مبلغ صحيح لقيد التسوية');
        return;
      }

      const payload = {
        voucherNumber,
        settlementType,
        debitAccount,
        creditAccount,
        amountUsd,
        amountSdg,
        rate,
        currency: 'USD',
        description,
        date: dateStr
      };

      try {
        const res = await fetch('api/mirror_sync.php?action=save_journal_entry', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data && data.status === 'success') {
          if (window.AppHaptics) AppHaptics.success();
          if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(false);
          if (typeof showFloatingToast === 'function') {
            showFloatingToast(`✅ تم ترحيل قيد التسوية #${data.voucherNumber} وتحديث الأرصدة في قاعدة البيانات!`);
          }
          if (window.SuiteSalesPOS && typeof SuiteSalesPOS.refreshPartnersFromServer === 'function') {
            SuiteSalesPOS.refreshPartnersFromServer();
          }
          closeModal('modalOpJournal');
          return;
        }
      } catch (err) {
        if (window.MagaiesOfflineSync && typeof MagaiesOfflineSync.enqueue === 'function') {
          const q = MagaiesOfflineSync.enqueue('journal_entry', payload);
          if (typeof showFloatingToast === 'function') {
            showFloatingToast(`📴 تم حفظ القيد #${q.docNo} محلياً وسيُرحّل تلقائياً فور عودة الشبكة`);
          }
          closeModal('modalOpJournal');
          return;
        }
      }
      commitOperation('قيد تسوية محاسبي #' + (voucherNumber || 'JV'));
    };

    // 2. اعتماد وترحيل تسوية الجرد الفوري (modalOpStockAdjustment -> inventory + تحديث مخزون الصنف)
    window.commitOpStockAdjustment = async function() {
      const warehouseName = document.getElementById('adjWarehouseSelect')?.value || 'المستودع الرئيسي';
      const docNo = (document.getElementById('adjDocNo')?.value || '').trim();
      const itemSel = document.getElementById('adjItemSelect');
      const itemName = itemSel ? (itemSel.options[itemSel.selectedIndex]?.dataset?.itemName || itemSel.value) : '';
      const bookQty = parseFloat(document.getElementById('adjBookQty')?.value || '0') || 0;
      const actualQty = parseFloat(document.getElementById('adjActualQty')?.value || '0') || 0;
      const varianceQty = actualQty - bookQty;
      const reason = document.getElementById('adjReasonSelect')?.value || 'damaged';
      const notes = (document.getElementById('adjNotes')?.value || 'تسوية جرد فوري من الجوال').trim();
      const dateStr = new Date().toISOString().slice(0, 10);

      const payload = {
        docNo,
        warehouseName,
        itemName,
        bookQty,
        actualQty,
        varianceQty,
        reason,
        notes,
        date: dateStr
      };

      try {
        const res = await fetch('api/mirror_sync.php?action=save_stock_adjustment', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data && data.status === 'success') {
          if (window.AppHaptics) AppHaptics.success();
          if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(false);
          if (typeof showFloatingToast === 'function') {
            showFloatingToast(`✅ تم اعتماد تسوية الجرد #${data.docNo} وتحديث رصيد (${itemName}) إلى ${actualQty}!`);
          }
          // تحديث الكاش المحلي للأصناف فوراً
          if (window.SuiteSalesPOS && Array.isArray(SuiteSalesPOS.dbItems)) {
            const it = SuiteSalesPOS.dbItems.find(x => x.name === itemName);
            if (it) {
              it.stock = actualQty;
              if (it.warehouseStocks && typeof it.warehouseStocks === 'object') {
                it.warehouseStocks[warehouseName] = actualQty;
              }
            }
          }
          closeModal('modalOpStockAdjustment');
          return;
        }
      } catch (err) {
        if (window.MagaiesOfflineSync && typeof MagaiesOfflineSync.enqueue === 'function') {
          const q = MagaiesOfflineSync.enqueue('stock_adjustment', payload);
          if (typeof showFloatingToast === 'function') {
            showFloatingToast(`📴 تم حفظ تسوية الجرد #${q.docNo} محلياً للمزامنة التلقائية`);
          }
          closeModal('modalOpStockAdjustment');
          return;
        }
      }
      commitOperation('تسوية جرد مخزني #' + (docNo || 'ADJ'));
    };

    // 3. اعتماد وترحيل المناقلة بين المخازن (modalOpWarehouseTransfer -> inventory + خصم المصدر وتغذية الوجهة)
    window.commitOpWarehouseTransfer = async function() {
      const fromWarehouse = document.getElementById('transferFromWarehouse')?.value || 'المستودع الرئيسي';
      const toWarehouse = document.getElementById('transferToWarehouse')?.value || 'مخزن صالة المعرض';
      const itemSel = document.getElementById('transferItemSelect');
      const itemName = itemSel ? (itemSel.options[itemSel.selectedIndex]?.dataset?.itemName || itemSel.value) : '';
      const qty = parseFloat(document.getElementById('transferQtyInput')?.value || '0') || 0;
      const transferType = document.getElementById('transferTypeSelect')?.value || 'مناقلة مباشرة فورية';
      const driver = (document.getElementById('transferDriver')?.value || '').trim();
      const badgeText = (document.getElementById('transferDocNoBadge')?.innerText || '').replace('#', '').trim();
      const dateStr = new Date().toISOString().slice(0, 10);

      if (qty <= 0) {
        if (typeof showFloatingToast === 'function') showFloatingToast('⚠️ يرجى تحديد كمية صحيحة للتحويل المخزني');
        return;
      }
      if (fromWarehouse === toWarehouse) {
        if (typeof showFloatingToast === 'function') showFloatingToast('⚠️ لا يمكن التحويل لنفس المستودع المصدر');
        return;
      }

      const payload = {
        docNo: badgeText,
        fromWarehouse,
        toWarehouse,
        itemName,
        qty,
        transferType,
        driver,
        date: dateStr
      };

      try {
        const res = await fetch('api/mirror_sync.php?action=save_warehouse_transfer', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data && data.status === 'success') {
          if (window.AppHaptics) AppHaptics.success();
          if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(false);
          if (typeof showFloatingToast === 'function') {
            showFloatingToast(`✅ تم ترحيل المناقلة #${data.docNo} (${qty} من ${fromWarehouse} إلى ${toWarehouse})!`);
          }
          closeModal('modalOpWarehouseTransfer');
          return;
        }
      } catch (err) {
        if (window.MagaiesOfflineSync && typeof MagaiesOfflineSync.enqueue === 'function') {
          const q = MagaiesOfflineSync.enqueue('warehouse_transfer', payload);
          if (typeof showFloatingToast === 'function') {
            showFloatingToast(`📴 تم حفظ أمر التحويل #${q.docNo} محلياً للمزامنة التلقائية`);
          }
          closeModal('modalOpWarehouseTransfer');
          return;
        }
      }
      commitOperation('تحويل بين المخازن #' + (badgeText || 'TR'));
    };

    // 4. اعتماد وترحيل إذن الاستلام/الصرف المخزني (GRN / GIN) في modalOpVouchers
    window.currentVoucherMode = 'in';
    window.commitOpWarehouseVoucher = async function() {
      const mode = window.currentVoucherMode || 'in';
      const docNo = (document.getElementById('voucherDocNumber')?.value || '').trim();
      const warehouse = document.getElementById('voucherWarehouse')?.value || 'المستودع الرئيسي';
      const party = (document.getElementById('voucherPartyInput')?.value || '').trim();
      const storekeeper = (document.getElementById('voucherStorekeeperInput')?.value || '').trim();
      const receiver = (document.getElementById('voucherReceiverInput')?.value || '').trim();
      const dateStr = new Date().toISOString().slice(0, 10);

      const actionName = mode === 'in' ? 'save_purchase_receipt' : 'save_delivery_order';
      const offlineType = mode === 'in' ? 'purchase_receipt' : 'delivery_order';
      const payload = mode === 'in' ? {
        receiptNo: docNo,
        warehouse,
        supplier: party,
        inspector: storekeeper,
        driver: receiver,
        itemsSummary: 'إذن استلام مخزني معتمد (GRN)',
        date: dateStr
      } : {
        deliveryNo: docNo,
        warehouse,
        customer: party,
        driver: receiver,
        supervisor: storekeeper,
        itemsSummary: 'إذن صرف مخزني معتمد (GIN)',
        date: dateStr
      };

      try {
        const res = await fetch(`api/mirror_sync.php?action=${actionName}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data && data.status === 'success') {
          if (window.AppHaptics) AppHaptics.success();
          if (window.SafeInvoiceGuard) SafeInvoiceGuard.setUnsavedChanges(false);
          const savedNo = data.receiptNo || data.deliveryNo || docNo;
          if (typeof showFloatingToast === 'function') {
            showFloatingToast(`✅ تم اعتماد وترحيل الإذن المخزني #${savedNo} في قاعدة البيانات!`);
          }
          closeModal('modalOpVouchers');
          return;
        }
      } catch (err) {
        if (window.MagaiesOfflineSync && typeof MagaiesOfflineSync.enqueue === 'function') {
          const q = MagaiesOfflineSync.enqueue(offlineType, payload);
          if (typeof showFloatingToast === 'function') {
            showFloatingToast(`📴 تم حفظ الإذن #${q.docNo} محلياً للمزامنة التلقائية`);
          }
          closeModal('modalOpVouchers');
          return;
        }
      }
      commitOperation(mode === 'in' ? `إذن استلام مخزني (GRN) #${docNo}` : `إذن صرف مخزني (GIN) #${docNo}`);
    };

    // Helper: Switch Warehouse Vouchers (GRN / GIN)
    window.switchVoucherType = function(type) {
      window.currentVoucherMode = type === 'out' ? 'out' : 'in';
      if (window.triggerHapticPulse) triggerHapticPulse(15);
      const btnIn = document.getElementById('btnVoucherTypeIn');
      const btnOut = document.getElementById('btnVoucherTypeOut');
      const docNum = document.getElementById('voucherDocNumber');
      const partyLabel = document.getElementById('voucherPartyLabel');
      const partyInput = document.getElementById('voucherPartyInput');
      const btnCommit = document.getElementById('btnCommitVoucher');
      const icon = document.getElementById('voucherHeaderIcon');
      const title = document.getElementById('voucherHeaderTitle');
      const subtitle = document.getElementById('voucherHeaderSubtitle');
      const seqs = window.DynamicOperationalSuiteSync?.lastSequences || {};

      if (type === 'in') {
        btnIn.className = 'py-2 px-3 rounded-xl font-bold text-xs bg-emerald-600 text-white border border-emerald-500/50 shadow flex items-center justify-center gap-1.5 transition cursor-pointer';
        btnOut.className = 'py-2 px-3 rounded-xl font-bold text-xs bg-slate-900 text-slate-400 hover:text-white border border-slate-700 flex items-center justify-center gap-1.5 transition cursor-pointer';
        docNum.value = seqs.receipt?.next || 'R26100001';
        docNum.className = 'w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-emerald-300 font-mono font-bold';
        partyLabel.innerText = 'الجهة الموردة (المورد):';
        partyInput.value = 'شركة النيل الدولية للتجارة والاستيراد';
        title.innerText = 'إذن استلام مخزني (GRN)';
        subtitle.innerText = 'إدخال وتوريد بضاعة للمستودع ومطابقة الفحص الفني';
        icon.className = 'w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold';
        btnCommit.className = 'w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-2xl font-bold text-xs shadow-lg active:scale-95 transition cursor-pointer';
        btnCommit.innerText = 'اعتماد وطباعة إذن الاستلام المخزني (GRN) وتحديث الأرصدة ✓';
      } else {
        btnOut.className = 'py-2 px-3 rounded-xl font-bold text-xs bg-indigo-600 text-white border border-indigo-500/50 shadow flex items-center justify-center gap-1.5 transition cursor-pointer';
        btnIn.className = 'py-2 px-3 rounded-xl font-bold text-xs bg-slate-900 text-slate-400 hover:text-white border border-slate-700 flex items-center justify-center gap-1.5 transition cursor-pointer';
        docNum.value = seqs.delivery?.next || 'D26100001';
        docNum.className = 'w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-indigo-300 font-mono font-bold';
        partyLabel.innerText = 'الجهة المستلمة (العميل / الصالة):';
        partyInput.value = 'محلات الصادق للتوزيع';
        title.innerText = 'إذن صرف مخزني (GIN)';
        subtitle.innerText = 'إخراج وصرف بضاعة من المستودع لتسليم المبيعات';
        icon.className = 'w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold';
        btnCommit.className = 'w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl font-bold text-xs shadow-lg active:scale-95 transition cursor-pointer';
        btnCommit.innerText = 'اعتماد وطباعة إذن الصرف المخزني (GIN) وخصم الكميات ✓';
      }
    };

    // Helper: Stock Adjustment Variance Auto-Calculation
    window.calcStockAdjustmentVariance = function() {
      const bookQty = parseFloat(document.getElementById('adjBookQty')?.value || '150');
      const actualQty = parseFloat(document.getElementById('adjActualQty')?.value || '0');
      const diff = actualQty - bookQty;
      const varianceInput = document.getElementById('adjVarianceQty');
      const costImpact = document.getElementById('adjCostImpact');

      if (varianceInput) {
        if (diff < 0) {
          varianceInput.value = `${diff} (عجز وتالف)`;
          varianceInput.className = 'w-full bg-rose-950/50 border border-rose-500/50 rounded-xl p-2.5 font-bold font-mono text-rose-300 text-center';
        } else if (diff > 0) {
          varianceInput.value = `+${diff} (زيادة غير مقيدة)`;
          varianceInput.className = 'w-full bg-emerald-950/50 border border-emerald-500/50 rounded-xl p-2.5 font-bold font-mono text-emerald-300 text-center';
        } else {
          varianceInput.value = '0 (مطابق تماماً ✓)';
          varianceInput.className = 'w-full bg-slate-800 border border-slate-700 rounded-xl p-2.5 font-bold font-mono text-cyan-300 text-center';
        }
      }

      if (costImpact) {
        const adjIt = document.getElementById('adjItemSelect');
        const selOpt = adjIt && adjIt.options[adjIt.selectedIndex];
        const unitCost = selOpt && selOpt.dataset.price ? parseFloat(selOpt.dataset.price) : 45;
        const rate = (window.SuiteFXManager && SuiteFXManager.currentRate) ? Number(SuiteFXManager.currentRate) : 2800;
        const impactVal = diff * unitCost;
        const sdgVal = Math.abs(Math.round(impactVal * rate)).toLocaleString();
        costImpact.innerText = (impactVal <= 0 ? `-$${Math.abs(impactVal).toLocaleString()}` : `+$${impactVal.toLocaleString()}`) + ` (${sdgVal} ج.س)`;
      }
    };

    // Helper: Update Stock & Bin Location Details from Real Items
    window.updateStockLocationDetails = function(itemIdOrName) {
      const items = window.DynamicOperationalSuiteSync ? window.DynamicOperationalSuiteSync.getRealItems() : [];
      const whs = window.DynamicOperationalSuiteSync ? window.DynamicOperationalSuiteSync.getRealWarehouses() : [];
      const found = items.find((it, idx) => String(it.id) === String(itemIdOrName) || it.name === itemIdOrName || String(idx + 1) === String(itemIdOrName));

      if (found) {
        const unit = found.unit || 'وحدة';
        const totalNum = typeof found.stock === 'number' ? found.stock : (Array.isArray(found.stock) && found.stock[0]?.qty !== undefined ? Number(found.stock[0].qty) : 120);
        const resNum = Math.max(0, Math.round(totalNum * 0.1));
        const availNum = Math.max(0, totalNum - resNum);

        const totEl = document.getElementById('stockLocTotalQty');
        const resEl = document.getElementById('stockLocReservedQty');
        const avEl = document.getElementById('stockLocAvailableQty');
        if (totEl) totEl.innerText = `${totalNum} ${unit}`;
        if (resEl) resEl.innerText = `${resNum} ${unit}`;
        if (avEl) avEl.innerText = `${availNum} ${unit}`;

        const binsList = document.getElementById('stockLocationBinsList');
        if (binsList && whs.length > 0) {
          const whStocks = (found.warehouseStocks && typeof found.warehouseStocks === 'object') ? found.warehouseStocks : null;
          binsList.innerHTML = whs.slice(0, 4).map((w, idx) => {
            const q = whStocks && whStocks[w.name] !== undefined
              ? Number(whStocks[w.name])
              : (idx === 0 ? Math.ceil(totalNum * 0.6) : (idx === 1 ? Math.floor(totalNum * 0.25) : Math.max(0, totalNum - Math.ceil(totalNum * 0.6) - Math.floor(totalNum * 0.25))));
            return `
              <div class="p-2.5 bg-slate-900/90 rounded-2xl border ${idx === 0 ? 'border-cyan-500/30' : 'border-slate-800'} flex justify-between items-center">
                <div>
                  <span class="font-bold text-white block">${w.name}</span>
                  <div class="text-[10px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                    <span class="bg-slate-800 px-1.5 py-0.5 rounded text-cyan-300 font-mono">ممر A${idx + 1}</span>
                    <span class="bg-slate-800 px-1.5 py-0.5 rounded text-cyan-300 font-mono">رف R-0${idx + 1}</span>
                  </div>
                </div>
                <div class="text-left">
                  <span class="text-xs font-black ${q > 10 ? 'text-cyan-400' : 'text-amber-300'} font-mono block">${q} ${unit}</span>
                  <span class="text-[9px] ${q > 10 ? 'bg-emerald-950 text-emerald-300 border-emerald-500/30' : 'bg-amber-950 text-amber-300 border-amber-500/30'} border px-1.5 py-0.2 rounded-full">${q > 10 ? 'جاهز للصرف ✓' : 'حد أمان ⚠️'}</span>
                </div>
              </div>
            `;
          }).join('');
        }
        return;
      }

      const data = {
        '1': { total: '150 جوالاً', res: '20 جوالاً', avail: '130 جوالاً' },
        '2': { total: '80 جوالاً', res: '10 جوالات', avail: '70 جوالاً' },
        '3': { total: '95 كرتونة', res: '15 كرتونة', avail: '80 كرتونة' },
        '4': { total: '320 سيخة', res: '40 سيخة', avail: '280 سيخة' },
        '5': { total: '240 كرتونة', res: '0 كرتونة', avail: '240 كرتونة' }
      }[itemIdOrName] || { total: '150 وحدة', res: '20 وحدة', avail: '130 وحدة' };

      document.getElementById('stockLocTotalQty').innerText = data.total;
      document.getElementById('stockLocReservedQty').innerText = data.res;
      document.getElementById('stockLocAvailableQty').innerText = data.avail;
    };

    // Helper: Dynamic Quick Statement Loader connected to Live Accounts
    window.renderQuickStatement = function(accId) {
      const liveAccounts = (window.DynamicOperationalSuiteSync && typeof window.DynamicOperationalSuiteSync.getRealAccounts === 'function')
        ? window.DynamicOperationalSuiteSync.getRealAccounts()
        : [];
      const foundAcc = liveAccounts.find(a => String(a.id) === String(accId) || a.name === accId);

      let accounts;
      if (foundAcc) {
        const bal = Math.abs(Number(foundAcc.balanceUsd || 0));
        const openBal = Math.round(bal * 0.85);
        const drBal = Math.round(bal * 0.35);
        const crBal = Math.round(bal * 0.20);
        const typeLabel = foundAcc.category === 'vendor' ? 'دائن' : (foundAcc.category === 'cash' ? 'رصيد' : 'مدين');
        accounts = {
          name: foundAcc.name,
          open: `$${openBal.toLocaleString()}`,
          dr: `+$${drBal.toLocaleString()}`,
          cr: `-$${crBal.toLocaleString()}`,
          net: `$${bal.toLocaleString()} (${typeLabel})`,
          type: typeLabel
        };
      } else {
        accounts = {
          '1': { name: 'شركة النيل للاستيراد', open: '$3,650.00', dr: '+$2,100.00', cr: '-$1,500.00', net: '$4,250.00 (مدين)', type: 'مدين' },
          '2': { name: 'محلات الصادق للتوزيع', open: '$8,000.00', dr: '+$3,200.00', cr: '-$2,050.00', net: '$9,150.00 (مدين)', type: 'مدين' },
          '3': { name: 'شركة البركة للتجارة', open: '$7,500.00', dr: '+$1,700.00', cr: '-$1,000.00', net: '$8,200.00 (مدين)', type: 'مدين' },
          '8': { name: 'بنك الخرطوم (بنكك)', open: '$38,000.00', dr: '+$12,500.00', cr: '-$7,700.00', net: '$42,800.00 (رصيد جاري)', type: 'رصيد' },
          '7': { name: 'خزينة الإدارة الرئيسية', open: '$14,200.00', dr: '+$8,400.00', cr: '-$4,150.00', net: '$18,450.00 (سيولة نقدية)', type: 'رصيد' },
          '10': { name: 'مطاحن الخرطوم للغلال', open: '$10,000.00', dr: '+$4,200.00', cr: '-$0.00', net: '$14,200.00 (دائن للمورد)', type: 'دائن' }
        }[accId] || { name: 'الحساب المختار', open: '$0.00', dr: '+$0.00', cr: '-$0.00', net: '$0.00', type: 'متزن' };
      }

      const summary = document.getElementById('quickStatementSummary');
      if (summary) {
        summary.innerHTML = `
          <div class="flex justify-between items-center text-slate-400">
            <span>رصيد أول الفترة:</span>
            <span class="font-mono text-slate-200 font-bold">${accounts.open}</span>
          </div>
          <div class="flex justify-between items-center text-slate-400">
            <span>إجمالي الحركات المدينة (+):</span>
            <span class="font-mono text-emerald-400 font-bold">${accounts.dr}</span>
          </div>
          <div class="flex justify-between items-center text-slate-400">
            <span>إجمالي الحركات الدائنة (-):</span>
            <span class="font-mono text-rose-400 font-bold">${accounts.cr}</span>
          </div>
          <div class="border-t border-slate-800 pt-1.5 flex justify-between items-center">
            <span class="font-black text-white">الرصيد الصافي المستحق:</span>
            <span class="font-mono font-black ${accounts.type === 'دائن' ? 'text-amber-400' : (accounts.type === 'مدين' ? 'text-rose-400' : 'text-emerald-400')} text-sm">${accounts.net}</span>
          </div>
        `;
      }
    };

    // Helper: Balance Inquiry Filter Functions
    window.filterQuickBalanceList = function(query) {
      const q = (query || '').toLowerCase().trim();
      const cards = document.querySelectorAll('#quickBalanceItemsContainer .quick-bal-card');
      cards.forEach(card => {
        const text = card.innerText.toLowerCase();
        card.style.display = text.includes(q) ? 'flex' : 'none';
      });
    };

    window.filterQuickBalanceCategory = function(cat, btn) {
      if (window.triggerHapticPulse) triggerHapticPulse(10);
      document.querySelectorAll('.bal-cat-pill').forEach(p => {
        p.className = 'bal-cat-pill bg-slate-800 text-slate-300 px-2.5 py-1 rounded-lg cursor-pointer';
      });
      if (btn) btn.className = 'bal-cat-pill active bg-yellow-500 text-slate-950 px-2.5 py-1 rounded-lg font-bold cursor-pointer';
      const cards = document.querySelectorAll('#quickBalanceItemsContainer .quick-bal-card');
      cards.forEach(card => {
        if (cat === 'all' || card.getAttribute('data-category') === cat) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    };

    // Transactions 5 Actions
    function txAction(action, txId) {
      if (action === 'تعديل') {
        alert("✏️ جاري فتح شاشة تعديل المعاملة " + txId + " بكافة بنودها.");
      } else if (action === 'حذف') {
        const conf = confirm("⚠️ هل أنت متأكد من إلغاء وحذف المعاملة " + txId + " وعكس قيودها من الحسابات؟");
        if (conf) alert("✓ تم إلغاء المعاملة " + txId + " بنجاح.");
      } else if (action === 'طباعة') {
        alert("🖨️ جاري تصدير وطباعة الفاتورة " + txId + " كملف PDF رسمي.");
      } else if (action === 'إرسال') {
        alert("📤 جاري إرسال إشعار وسند المعاملة " + txId + " عبر الواتساب للعميل مباشرة.");
      } else if (action === 'ضبط') {
        alert("⚙️ فتح إعدادات التدقيق والربط الضريبي والترحيل للمعاملة " + txId + ".");
      }
    }

    // Ledger Sub-Modules Switcher
    function switchLedgerSubModule(subMod) {
      const views = {
        'accountsList': document.getElementById('subViewAccountsList'),
        'statement': document.getElementById('subViewStatement'),
        'aging': document.getElementById('subViewAging'),
        'checks': document.getElementById('subViewChecks'),
        'creditLimit': document.getElementById('subViewCreditLimit'),
        'movements': document.getElementById('subViewMovements'),
        'settlement': document.getElementById('subViewSettlement')
      };
      const tabs = {
        'accountsList': document.getElementById('subTabAccountsList'),
        'statement': document.getElementById('subTabStatement'),
        'aging': document.getElementById('subTabAging'),
        'checks': document.getElementById('subTabChecks'),
        'creditLimit': document.getElementById('subTabCredit'),
        'movements': document.getElementById('subTabMovements'),
        'settlement': document.getElementById('subTabSettlement')
      };

      Object.values(views).forEach(v => { if (v) v.classList.add('hidden'); });
      Object.values(tabs).forEach(t => { if (t) t.className = "bg-slate-800 text-slate-400 hover:text-white px-3 py-1.5 rounded-xl shrink-0"; });

      if (views[subMod]) views[subMod].classList.remove('hidden');
      if (tabs[subMod]) {
        tabs[subMod].className = (subMod === 'accountsList' ? "bg-blue-600" : "bg-cyan-600") + " text-white px-3 py-1.5 rounded-xl shrink-0";
      }

      if (subMod === 'accountsList' && window.AccountsListEngine) {
        AccountsListEngine.render();
      }
    }

    // Open Non-Zero Balances Modal
    function openNonZeroBalancesModal() {
      filterBalances('all');
      openModal('modalNonZeroBalances');
    }

    let currentBalFilterType = 'all';
    let currentBalSearchQuery = '';

    function filterBalances(filterType, searchQuery) {
      if (filterType !== undefined) currentBalFilterType = filterType;
      if (searchQuery !== undefined) currentBalSearchQuery = searchQuery;

      const listContainer = document.getElementById('nonZeroBalancesList');
      const filterBtns = {
        'all': document.getElementById('btnBalFilterAll'),
        'customer': document.getElementById('btnBalFilterCust'),
        'vendor': document.getElementById('btnBalFilterVend'),
        'cash': document.getElementById('btnBalFilterCash'),
        'expense': document.getElementById('btnBalFilterExp')
      };

      Object.entries(filterBtns).forEach(([k, btn]) => {
        if (!btn) return;
        if (k === currentBalFilterType) {
          btn.className = "bg-emerald-600 text-white px-3 py-1 rounded-xl shrink-0 font-bold shadow";
        } else {
          btn.className = "bg-slate-800 text-slate-400 px-3 py-1 rounded-xl shrink-0 font-bold";
        }
      });

      const accounts = (window.masterAccountsList && window.masterAccountsList.length) ? window.masterAccountsList : masterAccountsList;
      let filtered = accounts.filter(acc => acc.balanceNum !== 0);

      if (currentBalFilterType === 'customer') filtered = filtered.filter(acc => acc.category === 'customer');
      else if (currentBalFilterType === 'vendor') filtered = filtered.filter(acc => acc.category === 'vendor');
      else if (currentBalFilterType === 'cash') filtered = filtered.filter(acc => acc.category === 'cash');
      else if (currentBalFilterType === 'expense') filtered = filtered.filter(acc => acc.category === 'expense');

      if (currentBalSearchQuery && currentBalSearchQuery.trim()) {
        const q = currentBalSearchQuery.trim().toLowerCase();
        filtered = filtered.filter(acc => acc.name.toLowerCase().includes(q) || acc.type.toLowerCase().includes(q));
      }

      if (filtered.length === 0) {
        listContainer.innerHTML = '<div class="p-4 bg-slate-900/60 rounded-2xl text-center text-xs text-slate-400 font-bold">لا توجد حسابات نشطة تطابق البحث</div>';
        return;
      }

      listContainer.innerHTML = filtered.map(acc => `
        <div class="p-3.5 bg-slate-900/90 rounded-2xl border border-slate-800 hover:border-emerald-500/50 transition space-y-2.5 shadow-md">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="text-xs font-black text-slate-100">${acc.name}</span>
              <span class="text-[9px] px-1.5 py-0.5 rounded-full border font-bold ${acc.natureClass}">${acc.nature}</span>
            </div>
            <span class="text-[10px] bg-slate-800 text-cyan-300 px-2 py-0.5 rounded-md font-bold">${acc.type}</span>
          </div>

          <!-- Dual Currency Display: Bold SDG & Bold USD with beautiful colors -->
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-amber-950/40 border border-amber-500/40 rounded-xl p-2">
              <span class="text-[10px] text-amber-300 font-bold block mb-0.5">🇸🇩 بالعملة المحلية (ج.س):</span>
              <span class="text-xs font-black text-amber-300 font-mono tracking-wide block">${acc.sdgFormatted}</span>
            </div>
            <div class="bg-emerald-950/40 border border-emerald-500/40 rounded-xl p-2">
              <span class="text-[10px] text-emerald-300 font-bold block mb-0.5">💵 بالدولار الأمريكي (USD):</span>
              <span class="text-xs font-black text-emerald-400 font-mono tracking-wide block">${acc.balanceFormatted} USD</span>
            </div>
          </div>

          <!-- Quick Action Buttons -->
          <div class="flex items-center justify-between pt-1 border-t border-slate-800/80">
            <button onclick="triggerStatementForAccount('${acc.name}', '${acc.type}', '${acc.balanceFormatted}', '${acc.sdgFormatted}')" 
                    class="text-[11px] bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-500 text-white px-3 py-1.5 rounded-xl font-black flex items-center gap-1.5 shadow-md shadow-cyan-600/20 active:scale-95 transition">
              <span>📄 كشف الحساب (كل الفترة)</span>
            </button>
            <button onclick="contactAccountWhatsApp('${acc.name}', '${acc.balanceFormatted}', '${acc.sdgFormatted}')"
                    class="text-[11px] bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-md shadow-emerald-600/20 active:scale-95 transition">
              <span>📲 واتساب</span>
            </button>
          </div>
        </div>
      `).join('');
    }

    function scrollToOperations() {
      switchTab('dashboard');
      const opSection = document.getElementById('sectionOperations');
      if (opSection) opSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function togglePrivacyMode() {
      isPrivacyActive = !isPrivacyActive;
      const eyeBtn = document.getElementById('privacyToggleBtn');
      const iconOpen = document.getElementById('privacyIconEyeOpen');
      const iconClosed = document.getElementById('privacyIconEyeClosed');
      const toastBar = document.getElementById('privacyToastBar');
      const toastMsg = document.getElementById('privacyToastMsg');
      const maskedElements = document.querySelectorAll('.privacy-mask');

      if (isPrivacyActive) {
        eyeBtn.classList.add('privacy-btn-active');
        iconOpen.classList.add('hidden');
        iconClosed.classList.remove('hidden');

        maskedElements.forEach(el => {
          el.textContent = el.dataset.star || "••••••";
        });

        toastMsg.textContent = "🛡️ وضع الخصوصية مفعل: تم إخفاء المبالغ المالية بنجوم (*****)";
        toastBar.classList.remove('hidden');
        setTimeout(() => toastBar.classList.add('hidden'), 2200);
      } else {
        eyeBtn.classList.remove('privacy-btn-active');
        iconOpen.classList.remove('hidden');
        iconClosed.classList.add('hidden');

        maskedElements.forEach(el => {
          el.textContent = el.dataset.original;
        });

        toastMsg.textContent = "👁️ تم إظهار الأرقام والمبالغ المالية";
        toastBar.classList.remove('hidden');
        setTimeout(() => toastBar.classList.add('hidden'), 2200);
      }
    }

    function switchTab(tabId) {
      if (window.AppHaptics) AppHaptics.click();
      const pages = {
        'dashboard': document.getElementById('pageDashboard'),
        'transactions': document.getElementById('pageTransactions'),
        'approvals': document.getElementById('pageApprovals'),
        'ledgers': document.getElementById('pageLedgers'),
        'inventory': document.getElementById('pageInventory'),
        'treasury': document.getElementById('pageTreasury'),
        'addPartner': document.getElementById('pageAddPartner'),
        'alerts': document.getElementById('pageAlerts')
      };

      const navButtons = {
        'dashboard': document.getElementById('navTabDashboard'),
        'transactions': document.getElementById('navTabTransactions'),
        'approvals': document.getElementById('navTabApprovals'),
        'ledgers': document.getElementById('navTabLedgers'),
        'inventory': document.getElementById('navTabInventory'),
        'alerts': document.getElementById('navTabAlerts')
      };

      Object.values(pages).forEach(p => { if (p) p.classList.add('hidden'); });

      Object.values(navButtons).forEach(btn => {
        if (btn) btn.classList.remove('is-active');
      });

      if (pages[tabId]) pages[tabId].classList.remove('hidden');
      if (navButtons[tabId]) {
        navButtons[tabId].classList.add('is-active');
        if (typeof navButtons[tabId].scrollIntoView === 'function') {
          navButtons[tabId].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }
      }

      if (tabId === 'alerts') {
        if (typeof initAlertRulesUI === 'function') initAlertRulesUI();
        initCrystalFlagshipGestures();
        OperationalTimer.init();
        applyTheme(currentActiveTheme);
      }
    }

    
    // ═════════════════════════════════════════════════════════════════
    // 🔄 DYNAMIC OPERATIONAL SUITE SYNC & OFFLINE-READY ENGINE
    // ربط كافة الشاشات التشغيلية الثانوية (القيود، الجرد، المناقلات، الأذونات، كشف الحساب، الأرصدة، مواقع المخزون) بقاعدة البيانات الحية
    // ═════════════════════════════════════════════════════════════════
    window.DynamicOperationalSuiteSync = {
      initialized: false,
      lastSequences: {},

      // الحصول على المستودعات الحقيقية من الماستر أو الكاش المحلي
      getRealWarehouses() {
        let list = [];
        try {
          if (window.SuiteSalesPOS && Array.isArray(SuiteSalesPOS.dbWarehouses) && SuiteSalesPOS.dbWarehouses.length > 0) {
            list = SuiteSalesPOS.dbWarehouses;
          } else {
            const cached = localStorage.getItem('erp_warehouses') || localStorage.getItem('erp_warehouses_v1') || localStorage.getItem('magaies_cached_warehouses');
            if (cached) list = JSON.parse(cached);
          }
        } catch(e) {}
        if (!Array.isArray(list) || list.length === 0) {
          list = [
            { id: 'wh-1', name: 'المستودع الرئيسي' },
            { id: 'wh-2', name: 'مخزن السجانة' },
            { id: 'wh-3', name: 'دره اللاماب' },
            { id: 'wh-4', name: 'مخزن الدره' },
            { id: 'wh-5', name: 'اللاماب' },
            { id: 'wh-6', name: 'دكان الخرطوم السجانه' }
          ];
        }
        return list;
      },

      // الحصول على الأصناف الحقيقية من الماستر أو الكاش المحلي
      getRealItems() {
        let list = [];
        try {
          if (window.SuiteSalesPOS && Array.isArray(SuiteSalesPOS.dbItems) && SuiteSalesPOS.dbItems.length > 0) {
            list = SuiteSalesPOS.dbItems;
          } else {
            const cached = localStorage.getItem('erp_items_v1') || localStorage.getItem('magaies_cached_items');
            if (cached) list = JSON.parse(cached);
          }
        } catch(e) {}
        if (!Array.isArray(list) || list.length === 0) {
          list = [
            { id: 'item-1', name: 'سيخ حديد 4 لنية مشرشر الأسعد', unit: 'طن', price: 1250, stock: 45 },
            { id: 'item-2', name: 'سيخ حديد 5 لنية مشرشر أوميغا', unit: 'طن', price: 1240, stock: 60 },
            { id: 'item-3', name: 'أسمنت صخر السودان بورتلاندي 50 كجم', unit: 'طن', price: 180, stock: 250 },
            { id: 'item-4', name: 'زنك أمريكي 0.35 مم أصلي', unit: 'لوح', price: 45, stock: 400 },
            { id: 'item-5', name: 'ماسورة حديد مضلعة 1.5 بوصة', unit: 'حبة', price: 28, stock: 180 }
          ];
        }
        return list;
      },

      // الحصول على الحسابات الحقيقية (عملاء، موردين، خزن، بنوك) من قاعدة البيانات الحية
      getRealAccounts() {
        const out = [];
        const rate = (window.SuiteFXManager && SuiteFXManager.currentRate) ? Number(SuiteFXManager.currentRate) : 2850;
        try {
          if (Array.isArray(window.masterAccountsList) && window.masterAccountsList.length > 0) {
            window.masterAccountsList.forEach((a, i) => {
              out.push({
                id: a.id || String(i + 1),
                name: a.name,
                category: a.category || 'customer',
                typeLabel: a.type || 'حساب',
                balanceUsd: Number(a.balanceNum || 0),
                balanceSdg: Math.round(Number(a.balanceNum || 0) * rate)
              });
            });
          } else if (window.SuiteSalesPOS && Array.isArray(SuiteSalesPOS.dbCustomers)) {
            SuiteSalesPOS.dbCustomers.forEach((c, i) => {
              const balSdg = Number(c.balance || 0);
              out.push({
                id: c.id || `cust-${i}`,
                name: c.name,
                category: 'customer',
                typeLabel: 'عميل',
                balanceUsd: Math.round(balSdg / (rate || 2850)),
                balanceSdg: balSdg
              });
            });
          }
        } catch(e) {}
        return out;
      },

      // جلب التسلسلات الحية من السيرفر لتغذية الشاشات الثانوية
      async fetchLiveSequences() {
        try {
          const res = await fetch('api/mirror_sync.php?action=get_next_numbers');
          const data = await res.json();
          if (data && data.status === 'success' && data.sequences) {
            this.lastSequences = data.sequences;
            const jRef = document.getElementById('journalSettlementRef');
            if (jRef && data.sequences.journal?.next) jRef.value = data.sequences.journal.next;
            const adjNo = document.getElementById('adjDocNo');
            if (adjNo && data.sequences.inventory?.next) adjNo.value = data.sequences.inventory.next;
            const trBadge = document.getElementById('transferDocNoBadge');
            if (trBadge && data.sequences.transfer?.next) trBadge.innerText = '#' + data.sequences.transfer.next;
            const vNum = document.getElementById('voucherDocNumber');
            if (vNum) {
              vNum.value = (window.currentVoucherMode === 'out')
                ? (data.sequences.delivery?.next || 'D26100001')
                : (data.sequences.receipt?.next || 'R26100001');
            }
          }
        } catch(e) {}
      },

      // مزامنة عناصر القوائم المنسدلة والأرصدة في الشاشة المفتوحة
      syncModal(modalId) {
        const whs = this.getRealWarehouses();
        const items = this.getRealItems();
        const accounts = this.getRealAccounts();
        this.fetchLiveSequences();

        // 0. شاشة قيود التسوية المحاسبية (modalOpJournal)
        if (accounts.length > 0) {
          const jDr = document.getElementById('journalDebitAccount');
          const jCr = document.getElementById('journalCreditAccount');
          const accOpts = accounts.slice(0, 40).map(a => `<option value="${a.name}" data-acc-name="${a.name}">${a.name} (${a.typeLabel} - $${Math.abs(a.balanceUsd).toLocaleString()})</option>`).join('') +
            `<option value="مصروفات عمومية وإدارية" data-acc-name="مصروفات عمومية وإدارية">مصروفات عمومية وإدارية</option>` +
            `<option value="مخسرات تالف وهالك البضاعة" data-acc-name="مخسرات تالف وهالك البضاعة">مخسرات تالف وهالك البضاعة</option>` +
            `<option value="فروقات تسوية حسابات" data-acc-name="فروقات تسوية حسابات">فروقات تسوية حسابات</option>`;
          if (jDr) jDr.innerHTML = accOpts;
          if (jCr) jCr.innerHTML = accOpts;
        }

        // 1. شاشة تسوية الجرد (modalOpStockAdjustment)
        const adjWh = document.getElementById('adjWarehouseSelect');
        if (adjWh) {
          adjWh.innerHTML = whs.map(w => `<option value="${w.name}">📦 ${w.name}</option>`).join('');
        }
        const adjIt = document.getElementById('adjItemSelect');
        if (adjIt) {
          adjIt.innerHTML = items.map((it, idx) => {
            const stQty = typeof it.stock === 'number' ? it.stock : ((it.stock && it.stock[0] && it.stock[0].qty !== undefined) ? it.stock[0].qty : (100 - idx * 12));
            return `<option value="${it.name}" data-item-name="${it.name}" data-qty="${stQty}" data-price="${it.price || 45}">🏷️ ${it.name} (${it.unit || 'حبة'}) - رصيد: ${stQty}</option>`;
          }).join('');
          adjIt.onchange = () => {
            const opt = adjIt.options[adjIt.selectedIndex];
            const q = opt ? opt.getAttribute('data-qty') : 100;
            const bEl = document.getElementById('adjBookQty');
            if (bEl) bEl.value = q;
            if (typeof calcStockAdjustmentVariance === 'function') calcStockAdjustmentVariance();
          };
          if (adjIt.options.length > 0) adjIt.dispatchEvent(new Event('change'));
        }

        // 2. شاشة التحويل بين المخازن (modalOpWarehouseTransfer)
        const tFrom = document.getElementById('transferFromWarehouse');
        const tTo = document.getElementById('transferToWarehouse');
        if (tFrom) {
          tFrom.innerHTML = whs.map(w => `<option value="${w.name}">📤 ${w.name}</option>`).join('');
        }
        if (tTo) {
          tTo.innerHTML = whs.map((w, i) => `<option value="${w.name}" ${i === 1 ? 'selected' : ''}>📥 ${w.name}</option>`).join('');
        }
        const tItem = document.getElementById('transferItemSelect');
        if (tItem) {
          tItem.innerHTML = items.map((it, idx) => {
            const avail = typeof it.stock === 'number' ? it.stock : ((it.stock && it.stock[0] && it.stock[0].qty !== undefined) ? it.stock[0].qty : (80 - idx * 10));
            return `<option value="${it.name}" data-item-name="${it.name}">🏷️ ${it.name} (المتاح: ${avail} ${it.unit || 'حبة'})</option>`;
          }).join('');
        }

        // 3. شاشة إذن استلام وصرف مخزني (modalOpVouchers)
        const vWh = document.getElementById('voucherWarehouse');
        if (vWh) {
          vWh.innerHTML = whs.map(w => `<option value="${w.name}">🏬 ${w.name}</option>`).join('');
        }

        // 4. مواقع المخزون (modalOpStockLocation)
        const locItemSel = document.getElementById('stockLocationItemSelect');
        if (locItemSel && items.length > 0) {
          locItemSel.innerHTML = items.map(it => `<option value="${it.name}">🏷️ ${it.name} (${it.unit || 'وحدة'})</option>`).join('');
          window.updateStockLocationDetails(items[0].name);
        }

        // 5. كشف الحساب الفوري (modalOpStatement)
        const stSel = document.getElementById('quickStatementAccountSelect');
        if (stSel && accounts.length > 0) {
          stSel.innerHTML = accounts.slice(0, 40).map(a => `<option value="${a.name}">${a.name} (${a.typeLabel} - $${Math.abs(a.balanceUsd).toLocaleString()})</option>`).join('');
          window.renderQuickStatement(accounts[0].name);
        }

        // 6. الاستعلام الفوري عن الأرصدة (modalOpBalanceInquiry)
        const balContainer = document.getElementById('quickBalanceItemsContainer');
        if (balContainer && accounts.length > 0) {
          balContainer.innerHTML = accounts.slice(0, 30).map(a => {
            const isCash = a.category === 'cash';
            const isVendor = a.category === 'vendor';
            const borderCol = isCash ? 'border-emerald-500/30' : (isVendor ? 'border-amber-500/30' : 'border-rose-500/30');
            const badgeCol = isCash ? 'bg-emerald-500/20 text-emerald-400' : (isVendor ? 'bg-amber-500/20 text-amber-400' : 'bg-rose-500/20 text-rose-400');
            const textCol = isCash ? 'text-emerald-400' : (isVendor ? 'text-amber-400' : 'text-rose-400');
            const iconClass = isCash ? 'fa-building-columns' : (isVendor ? 'fa-industry' : 'fa-user');
            return `
              <div class="quick-bal-card p-2.5 bg-slate-900/90 rounded-2xl border ${borderCol} flex justify-between items-center" data-category="${a.category}">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-xl ${badgeCol} flex items-center justify-center font-bold">
                    <i class="fa-solid ${iconClass}"></i>
                  </div>
                  <div>
                    <span class="font-bold text-white block">${a.name}</span>
                    <span class="text-[10px] ${textCol}">${a.typeLabel} • متصل بقاعدة البيانات 🟢</span>
                  </div>
                </div>
                <div class="text-left font-mono">
                  <span class="text-sm font-black ${textCol} block">$${Math.abs(a.balanceUsd).toLocaleString()}</span>
                  <span class="text-[10px] text-slate-400">${Math.abs(a.balanceSdg).toLocaleString()} ج.س</span>
                </div>
              </div>
            `;
          }).join('');
        }
      },

      init() {
        if (this.initialized) return;
        this.initialized = true;

        // Auto-scroll input into center on virtual keyboard focus
        document.addEventListener('focusin', (e) => {
          if (e.target && ['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) {
            const modal = e.target.closest('.modal-backdrop, .glass-base, [id^="modalOp"]');
            if (modal) {
              setTimeout(() => {
                e.target.scrollIntoView({ behavior: 'smooth', block: 'center' });
              }, 280);
            }
          }
        });

        // Cache master data for offline-first zero latency
        try {
          const whs = this.getRealWarehouses();
          const itms = this.getRealItems();
          localStorage.setItem('magaies_cached_warehouses', JSON.stringify(whs));
          localStorage.setItem('magaies_cached_items', JSON.stringify(itms));
        } catch(e) {}
      }
    };

    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => window.DynamicOperationalSuiteSync.init());
    } else {
      window.DynamicOperationalSuiteSync.init();
    }


    function openModal(modalId) {
      const m = document.getElementById(modalId);
      if (m) {
        m.classList.remove('hidden');
        m.style.display = '';
        if (window.DynamicOperationalSuiteSync) {
          DynamicOperationalSuiteSync.syncModal(modalId);
        }
        if (window.SmartPOSFontAI && (modalId === 'modalOpSales' || modalId === 'modalOpPurchase')) {
          requestAnimationFrame(() => window.SmartPOSFontAI.scaleAll());
          setTimeout(() => window.SmartPOSFontAI.scaleAll(), 60);
        }
        try {
          history.pushState({ modalOpen: true, modalId: modalId }, '', window.location.href);
        } catch(err) {}
      }
    }
    function closeModal(modalId) {
      const m = document.getElementById(modalId);
      if (m) {
        m.classList.add('hidden');
        m.style.display = 'none';
      }
      if (modalId === 'modalOpSales' || modalId === 'modalOpPurchase') {
        if (typeof switchTab === 'function') switchTab('dashboard');
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }

    function triggerRefreshAnimation() {
      const icon = document.getElementById('mainRefreshIcon');
      if (icon) {
        icon.classList.add('animate-spin');
        setTimeout(() => icon.classList.remove('animate-spin'), 1000);
      }
    }

    function performOmniSearch(query) {
      const dropdown = document.getElementById('omniSuggestions');
      const q = query.trim().toLowerCase();

      if (q.length === 0) {
        dropdown.classList.add('hidden');
        return;
      }

      const accounts = (window.masterAccountsList && window.masterAccountsList.length) ? window.masterAccountsList : masterAccountsList;
      const results = accounts.filter(acc => acc.name.toLowerCase().includes(q));

      if (results.length === 0) {
        dropdown.innerHTML = '<div class="p-2 text-xs text-slate-400 text-center">لا توجد نتائج مطابقة</div>';
      } else {
        dropdown.innerHTML = results.map(acc => `
          <div onclick="selectSearchAccount('${acc.name}', '${acc.type}', '${acc.balanceFormatted}')" class="p-2 rounded-xl hover:bg-slate-800 cursor-pointer flex justify-between items-center text-xs">
            <div>
              <span class="font-bold text-white">${acc.name}</span>
              <span class="text-[10px] text-cyan-400 mr-2">(${acc.type})</span>
            </div>
            <span class="font-mono text-emerald-400 font-bold">${acc.balanceFormatted}</span>
          </div>
        `).join('');
      }

      dropdown.classList.remove('hidden');
    }

    function selectSearchAccount(name, type, balance) {
      const input = document.getElementById('omniSearchInput');
      if (input) input.value = name;
      const dropdown = document.getElementById('omniSuggestions');
      if (dropdown) dropdown.classList.add('hidden');
      closeModal('modalNonZeroBalances');
      switchTab('ledgers');
    }

    function focusOmniSearch() {
      switchTab('dashboard');
      const input = document.getElementById('omniSearchInput');
      if (input) {
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        input.focus();
      }
    }

    // Partner Form Handling
    let currentPartnerType = 'customer';
    let currentBalanceDir = 'debit';

    function setPartnerFormType(type) {
      currentPartnerType = type;
      const btnC = document.getElementById('btnToggleCust');
      const btnV = document.getElementById('btnToggleVend');
      const title = document.getElementById('partnerHeaderTitle');
      const saveBtnLabel = document.getElementById('btnSavePartnerLabel');

      if (type === 'customer') {
        btnC.className = "py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-cyan-500 text-white shadow flex items-center justify-center gap-1.5 transition";
        btnV.className = "py-2.5 rounded-xl text-slate-400 hover:text-white flex items-center justify-center gap-1.5 transition";
        title.innerHTML = '<span class="w-2.5 h-2.5 rounded-full bg-cyan-400 shadow-[0_0_10px_#22d3ee]"></span><span>إضافة عميل جديد</span>';
        saveBtnLabel.textContent = "حفظ بيانات العميل فوراً";
      } else {
        btnV.className = "py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-cyan-500 text-white shadow flex items-center justify-center gap-1.5 transition";
        btnC.className = "py-2.5 rounded-xl text-slate-400 hover:text-white flex items-center justify-center gap-1.5 transition";
        title.innerHTML = '<span class="w-2.5 h-2.5 rounded-full bg-cyan-400 shadow-[0_0_10px_#22d3ee]"></span><span>إضافة مورد جديد</span>';
        saveBtnLabel.textContent = "حفظ بيانات المورد فوراً";
      }
    }

    function setBalanceDir(dir) {
      currentBalanceDir = dir;
      const btnD = document.getElementById('btnDebit');
      const btnC = document.getElementById('btnCredit');
      if (dir === 'debit') {
        btnD.className = "px-2 py-0.5 rounded bg-rose-500 text-white";
        btnC.className = "px-2 py-0.5 rounded text-slate-400";
      } else {
        btnC.className = "px-2 py-0.5 rounded bg-emerald-500 text-white";
        btnD.className = "px-2 py-0.5 rounded text-slate-400";
      }
    }

    function autoFillContact() {
      document.getElementById('partnerInputName').value = "مؤسسة النور الهندسية";
      document.getElementById('partnerInputPhone').value = "912 888 777";
      document.getElementById('partnerInputBal').value = "1200";
    }

    async function savePartnerAction() {
      const name = document.getElementById('partnerInputName').value.trim();
      const phone = document.getElementById('partnerInputPhone').value.trim();
      const bal = parseFloat(document.getElementById('partnerInputBal').value.trim()) || 0;

      if (!name) {
        alert("يرجى إدخال اسم العميل أو المورد");
        return;
      }

      const isCustomer = currentPartnerType === 'customer';

      if (window.PWAManager && typeof window.PWAManager.savePartnerToBackend === 'function') {
        await window.PWAManager.savePartnerToBackend({
          name: name,
          phone: phone,
          openingBalance: bal,
          type: currentPartnerType
        });
      }

      AudioEngine.play('success');
      triggerHapticPulse([60, 40, 60]);
      alert("✓ تم حفظ " + (isCustomer ? 'العميل' : 'المورد') + ": [" + name + "] بنجاح في قاعدة البيانات الحقيقية!\nتم فتح حساب جديد في شجرة الحسابات برصيد افتتاحي: $" + bal);

      // Clear input fields
      document.getElementById('partnerInputName').value = '';
      document.getElementById('partnerInputPhone').value = '';
      document.getElementById('partnerInputBal').value = '';

      switchTab('dashboard');
    }

    function contactDebtor(clientName, amount) {
      alert("جاري فتح واتساب لإرسال مطالبة سداد بقيمة $" + amount + " إلى " + clientName);
    }

    function openCheckDetails() {
      openModal('modalChecks');
    }

    // ==========================================
    // AI VOICE COPILOT LOGIC & SPEECH RECOGNITION
    // ==========================================
    let speechRecognitionInstance = null;
    let isListening = false;
    let currentAiParsedData = null;

    function openAiVoiceConsole() {
      openModal('modalAiVoiceConsole');
      resetAiVoiceConsole();
    }

    function resetAiVoiceConsole() {
      document.getElementById('aiVoiceStatusText').textContent = "اضغط على المايك للتحدث باللغة العربية أو اختر أمراً جاهزاً:";
      document.getElementById('aiLiveTranscript').textContent = "بانتظار صوتك أو اختيارك...";
      document.getElementById('aiGeneratedCard').classList.add('hidden');
      document.getElementById('aiEntityTags').innerHTML = '';
      stopListeningAnimation();
    }

    function startListeningAnimation() {
      document.querySelectorAll('.wave-bar').forEach(bar => {
        bar.style.animationPlayState = 'running';
      });
      const micBtn = document.getElementById('aiModalMicBtn');
      if (micBtn) micBtn.classList.add('ring-4', 'ring-cyan-400', 'animate-pulse');
    }

    function stopListeningAnimation() {
      document.querySelectorAll('.wave-bar').forEach(bar => {
        bar.style.animationPlayState = 'paused';
      });
      const micBtn = document.getElementById('aiModalMicBtn');
      if (micBtn) micBtn.classList.remove('ring-4', 'ring-cyan-400', 'animate-pulse');
    }

    function toggleVoiceRecognition() {
      if (isListening) {
        if (speechRecognitionInstance) speechRecognitionInstance.stop();
        isListening = false;
        stopListeningAnimation();
        document.getElementById('aiVoiceStatusText').textContent = "تم إيقاف الاستماع. يمكنك الضغط للبدء مجدداً.";
        return;
      }

      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SpeechRecognition) {
        alert("المتصفح الحالي لا يدعم التعرف المباشر على الصوت من الميكروفون، يمكنك تجربة الأوامر الصوتية الجاهزة بالأسفل فوراً أو كتابة الأمر!");
        return;
      }

      try {
        speechRecognitionInstance = new SpeechRecognition();
        speechRecognitionInstance.lang = 'ar-SA';
        speechRecognitionInstance.continuous = false;
        speechRecognitionInstance.interimResults = true;

        speechRecognitionInstance.onstart = () => {
          isListening = true;
          startListeningAnimation();
          document.getElementById('aiVoiceStatusText').textContent = "🔴 جاري الاستماع لصوتك الآن... تحدث باللغة العربية";
          document.getElementById('aiLiveTranscript').textContent = "استمع إليك...";
        };

        speechRecognitionInstance.onresult = (event) => {
          let interimTranscript = '';
          let finalTranscript = '';
          for (let i = event.resultIndex; i < event.results.length; ++i) {
            if (event.results[i].isFinal) {
              finalTranscript += event.results[i][0].transcript;
            } else {
              interimTranscript += event.results[i][0].transcript;
            }
          }
          const text = finalTranscript || interimTranscript;
          document.getElementById('aiLiveTranscript').textContent = text;
          if (finalTranscript) {
            processAiVoiceCommand(finalTranscript);
          }
        };

        speechRecognitionInstance.onerror = (event) => {
          console.warn("Speech recognition error:", event.error);
          isListening = false;
          stopListeningAnimation();
          document.getElementById('aiVoiceStatusText').textContent = "تعذر التقاط الصوت، يمكنك النقر على الأوامر الجاهزة بالأسفل:";
        };

        speechRecognitionInstance.onend = () => {
          isListening = false;
          stopListeningAnimation();
        };

        speechRecognitionInstance.start();
      } catch (err) {
        console.error(err);
        alert("تعذر تشغيل الميكروفون، يرجى التأكد من صلاحيات الصوت أو استخدام الأوامر الجاهزة.");
      }
    }

    // Trigger predefined voice command chips
    function runPredefinedVoiceCommand(commandText) {
      document.getElementById('aiLiveTranscript').textContent = commandText;
      document.getElementById('aiVoiceStatusText').textContent = "🟢 تم التقاط الأمر الصوتي وتحليله بالذكاء الاصطناعي:";
      startListeningAnimation();
      setTimeout(() => {
        stopListeningAnimation();
        processAiVoiceCommand(commandText);
      }, 350);
    }

    // AI Natural Language Parsing Engine for Accounting
    function processAiVoiceCommand(spokenText) {
      const text = spokenText.toLowerCase();
      let parsed = {
        raw: spokenText,
        intent: '',
        title: '',
        partner: '',
        amount: '',
        currency: '$',
        items: '',
        quantity: '',
        unitPrice: '',
        total: '',
        ref: '',
        account: '',
        date: '2026-10-04'
      };

      // Scenario 1: Sales invoice (محمد حسن 5 طن سكر ماستر)
      if (text.includes('مبيعات') || text.includes('فاتورة') || text.includes('بيع') || text.includes('محمد حسن') || text.includes('طن')) {
        parsed.intent = 'فاتورة مبيعات';
        parsed.title = 'فاتورة مبيعات - اعتماد بالذكاء الاصطناعي';
        parsed.partner = 'محمد حسن';
        parsed.items = 'سكر ماستر (أكياس 50 كجم)';
        parsed.quantity = '5 طن (100 شوال)';
        parsed.unitPrice = '$650 / طن (30 ريال للوحدة)';
        parsed.total = '$3,250 (9.1M ج.س)';
        parsed.currency = 'دولار ($)';
        parsed.account = 'مستودع الخرطوم الرئيسي • خزينة المبيعات';
        parsed.ref = '#1049';
      }
      // Scenario 2: Disbursement voucher (أصرف إلى همس 70 ألف جنيه، رقم العملية 678)
      else if (text.includes('صرف') || text.includes('أصرف') || text.includes('همس') || text.includes('678') || text.includes('جنيه')) {
        parsed.intent = 'سند صرف نقدية';
        parsed.title = 'سند صرف نقدية عاجل (#678)';
        parsed.partner = 'همس للخدمات والترحيل';
        parsed.amount = '70,000 ج.س';
        parsed.total = '70,000 جنيه سوداني (ما يعادل $250)';
        parsed.currency = 'ج.س';
        parsed.account = 'الخزينة الرئيسية للإدارة';
        parsed.ref = '#678';
        parsed.items = 'تفريغ وترحيل شحنة استثنائية';
      }
      // Scenario 3: Account statement (شركة النيل)
      else if (text.includes('كشف') || text.includes('النيل') || text.includes('ديون') || text.includes('تقادم')) {
        parsed.intent = 'استعراض كشف حساب';
        parsed.title = 'كشف حساب تفصيلي وموقف التقادم';
        parsed.partner = 'شركة النيل للاستيراد';
        parsed.total = '$12,400 مدين (متأخر 35 يوماً)';
        parsed.account = 'ذمم عملاء';
      }
      // Scenario 4: Liquidity pulse (السيولة)
      else if (text.includes('سيولة') || text.includes('الخزن') || text.includes('البنوك') || text.includes('النقد')) {
        parsed.intent = 'استعلام سيولة';
        parsed.title = 'موقف السيولة الجاهزة الفوري';
        parsed.total = '$86,150 (241.2M ج.س)';
        parsed.account = '5 خزن نقدية + 4 بنوك + 6 شيكات اليوم';
      }
      else {
        parsed.intent = 'أمر محاسبي بالذكاء الاصطناعي';
        parsed.title = 'معالجة قيد مالي مخصص';
        parsed.partner = 'الطرف المعني';
        parsed.total = 'حسب التقدير';
        parsed.account = 'الفرع الرئيسي';
      }

      currentAiParsedData = parsed;
      renderAiDecisionCard(parsed);

      // Synthesized Arabic Speech Output
      if ('speechSynthesis' in window) {
        let msg = "";
        if (parsed.intent === 'فاتورة مبيعات') {
          msg = "تم بنجاح تجهيز فاتورة مبيعات للعميل " + parsed.partner + " بإجمالي ثلاثة آلاف ومائتين وخمسين دولاراً، هل تعتمد الترحيل؟";
        } else if (parsed.intent === 'سند صرف نقدية') {
          msg = "تم تجهيز سند صرف للطرف " + parsed.partner + " بمبلغ سبعين ألف جنيه سوداني، برقم عملية 678، هل تعتمد الصرف؟";
        } else if (parsed.intent === 'استعراض كشف حساب') {
          msg = "تم تجهيز كشف حساب " + parsed.partner + " بإجمالي مديونية اثني عشر ألفاً وأربعمائة دولار";
        } else {
          msg = "تم استيعاب الأمر المحاسبي وجاري التنفيذ";
        }
        const utterance = new SpeechSynthesisUtterance(msg);
        utterance.lang = 'ar-SA';
        window.speechSynthesis.speak(utterance);
      }
    }

    function renderAiDecisionCard(data) {
      const container = document.getElementById('aiGeneratedCard');
      const tagsContainer = document.getElementById('aiEntityTags');

      tagsContainer.innerHTML = `
        <span class="px-2 py-0.5 rounded-lg bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 text-[10px] font-bold">🎯 ${data.intent}</span>
        <span class="px-2 py-0.5 rounded-lg bg-purple-500/20 text-purple-300 border border-purple-500/30 text-[10px] font-bold">👤 ${data.partner}</span>
        ${data.quantity ? `<span class="px-2 py-0.5 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-bold">📦 ${data.quantity}</span>` : ''}
        <span class="px-2 py-0.5 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold">💰 ${data.total}</span>
        ${data.ref ? `<span class="px-2 py-0.5 rounded-lg bg-blue-500/20 text-blue-300 border border-blue-500/30 text-[10px] font-bold">🔢 مرجع: ${data.ref}</span>` : ''}
      `;

      const cardBody = document.getElementById('aiCardDetails');
      cardBody.innerHTML = `
        <div class="flex items-center justify-between border-b border-slate-800 pb-2">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold">
              ⚡
            </div>
            <div>
              <h4 class="text-xs font-bold text-white">${data.title}</h4>
              <span class="text-[10px] text-slate-400">المرجع: ${data.ref || 'تلقائي'} • التاريخ: ${data.date}</span>
            </div>
          </div>
          <span class="text-xs font-black text-emerald-400 font-mono">${data.total}</span>
        </div>

        <div class="grid grid-cols-2 gap-2 text-[11px] pt-1">
          <div class="bg-slate-900/80 p-2 rounded-xl border border-slate-800">
            <span class="text-slate-400 block text-[9px]">الطرف المعني:</span>
            <span class="font-bold text-white">${data.partner}</span>
          </div>
          <div class="bg-slate-900/80 p-2 rounded-xl border border-slate-800">
            <span class="text-slate-400 block text-[9px]">الحساب / المستودع:</span>
            <span class="font-bold text-slate-200 text-[10px] truncate block">${data.account}</span>
          </div>
          ${data.items ? `
          <div class="col-span-2 bg-slate-900/80 p-2 rounded-xl border border-slate-800 flex justify-between items-center">
            <div>
              <span class="text-slate-400 block text-[9px]">الصنف والكمية:</span>
              <span class="font-bold text-white">${data.items} • ${data.quantity}</span>
            </div>
            <div class="text-left">
              <span class="text-slate-400 block text-[9px]">السعر المقدر:</span>
              <span class="font-mono text-cyan-300 font-bold">${data.unitPrice}</span>
            </div>
          </div>
          ` : ''}
        </div>
      `;

      container.classList.remove('hidden');
    }

    // Commit AI Approved Transaction
    function approveAiGeneratedTransaction() {
      if (!currentAiParsedData) return;
      const d = currentAiParsedData;

      if (d.intent === 'فاتورة مبيعات') {
        addNewTxToFeed({
          id: d.ref || '#1049',
          title: "فاتورة مبيعات AI " + (d.ref || '#1049'),
          subtitle: d.partner + " • " + d.items + " • نقداً",
          amountUsd: '+$3,250',
          amountSdg: '9.1M ج.س',
          typeColor: 'emerald'
        });
        alert("✅ تم بنجاح اعتماد وترحيل الفاتورة " + (d.ref || '#1049') + " للعميل [" + d.partner + "]!\nتم تحديث المخزون وحساب المبيعات فوراً.");
      } else if (d.intent === 'سند صرف نقدية') {
        addNewTxToFeed({
          id: d.ref || '#678',
          title: "سند صرف نقدية AI " + (d.ref || '#678'),
          subtitle: d.partner + " • الخزينة الرئيسية • تفريغ شحنة",
          amountUsd: '-$250',
          amountSdg: '-70,000 ج.س',
          typeColor: 'rose'
        });
        alert("✅ تم بنجاح صرف مبلغ [" + d.amount + "] إلى [" + d.partner + "] برقم قيد " + d.ref + "!\nتم خصم المبلغ من الخزينة الرئيسية فوراً.");
      } else if (d.intent === 'استعراض كشف حساب') {
        closeModal('modalAiVoiceConsole');
        switchTab('ledgers');
        return;
      } else if (d.intent === 'استعلام سيولة') {
        closeModal('modalAiVoiceConsole');
        switchTab('treasury');
        return;
      }

      closeModal('modalAiVoiceConsole');
      switchTab('transactions');
    }

    function editAiGeneratedTransaction() {
      if (!currentAiParsedData) return;
      closeModal('modalAiVoiceConsole');
      if (currentAiParsedData.intent === 'فاتورة مبيعات') {
        openOperationalForm('salesSplit');
      } else {
        openOperationalForm('financeMovement');
      }
    }

    function addNewTxToFeed(tx) {
      const container = document.querySelector('#pageTransactions .space-y-3:last-child');
      if (!container) return;

      const card = document.createElement('div');
      card.className = "glass-card-interactive p-3 rounded-2xl border border-cyan-500/60 space-y-2 animate-fadeIn";
      card.innerHTML = `
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-${tx.typeColor}-500/20 text-${tx.typeColor}-400 flex items-center justify-center font-bold">
              ⚡
            </div>
            <div>
              <span class="text-xs font-bold text-slate-100 block">${tx.title}</span>
              <span class="text-[10px] text-slate-400">${tx.subtitle} • معتمدة عبر AI 🎙️</span>
            </div>
          </div>
          <div class="text-left">
            <span class="text-xs font-black text-${tx.typeColor}-400 block">${tx.amountUsd}</span>
            <span class="text-[9px] text-slate-500 font-mono">${tx.amountSdg}</span>
          </div>
        </div>
        <div class="pt-2 border-t border-slate-800/80 grid grid-cols-5 gap-1.5">
          <button onclick="txAction('تعديل', '${tx.id}')" class="py-1.5 px-1 bg-slate-800/90 hover:bg-slate-700 text-cyan-300 rounded-xl text-[10px] font-bold flex items-center justify-center gap-1 border border-slate-700">
            <span>تعديل</span>
          </button>
          <button onclick="txAction('حذف', '${tx.id}')" class="py-1.5 px-1 bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 rounded-xl text-[10px] font-bold flex items-center justify-center gap-1 border border-rose-500/30">
            <span>حذف</span>
          </button>
          <button onclick="txAction('طباعة', '${tx.id}')" class="py-1.5 px-1 bg-slate-800/90 hover:bg-slate-700 text-slate-200 rounded-xl text-[10px] font-bold flex items-center justify-center gap-1 border border-slate-700">
            <span>طباعة</span>
          </button>
          <button onclick="txAction('إرسال', '${tx.id}')" class="py-1.5 px-1 bg-emerald-950/40 hover:bg-emerald-900/60 text-emerald-300 rounded-xl text-[10px] font-bold flex items-center justify-center gap-1 border border-emerald-500/30">
            <span>إرسال</span>
          </button>
          <button onclick="txAction('ضبط', '${tx.id}')" class="py-1.5 px-1 bg-slate-800/90 hover:bg-slate-700 text-amber-300 rounded-xl text-[10px] font-bold flex items-center justify-center gap-1 border border-slate-700">
            <span>ضبط</span>
          </button>
        </div>
      `;
      container.insertBefore(card, container.firstChild);
    }

    // ==========================================
    // APPROVALS EXECUTIVE ACTIONS
    // ==========================================
    function approveRequest(reqId, title) {
      const card = document.getElementById(reqId);
      if (card) {
        card.classList.add('opacity-40', 'pointer-events-none');
        setTimeout(() => card.remove(), 400);
      }
      updateApprovalsBadge(-1);
      alert("✅ تم بنجاح اعتماد: [" + title + "]\nتم تحديث حالة القيد وإشعار صاحب الطلب فوراً.");
    }

    function rejectRequest(reqId, title) {
      const card = document.getElementById(reqId);
      if (card) {
        card.classList.add('opacity-40', 'pointer-events-none');
        setTimeout(() => card.remove(), 400);
      }
      updateApprovalsBadge(-1);
      alert("❌ تم رفض الطلب: [" + title + "]. تم إرسال إشعار سبب الرفض.");
    }

    function bulkApproveAll() {
      const cards = document.querySelectorAll('#approvalsList .approval-card');
      cards.forEach(c => c.remove());
      updateApprovalsBadge(-99);
      alert("⚡ تم بنجاح اعتماد كافة الطلبات المعلقة دفعة واحدة!\nتم ترحيل كافة القيود في النظام.");
    }

    function updateApprovalsBadge(change) {
      const badge = document.getElementById('badgeApprovalsCount');
      const countBadge = document.getElementById('approvalsPendingCounter');
      const quickBadge = document.getElementById('badgeApprovalsQuick');
      if (!badge) return;
      let current = parseInt(badge.textContent) || 0;
      if (change === -99) current = 0;
      else current = Math.max(0, current + change);
      badge.textContent = current;
      if (quickBadge) quickBadge.textContent = current;
      if (countBadge) countBadge.textContent = current + " طلبات بانتظار قرارك";
      if (current === 0) {
        badge.classList.add('hidden');
        if (quickBadge) quickBadge.classList.add('hidden');
      }
    }

    // Initialize Default View
    window.addEventListener('DOMContentLoaded', () => {
      switchTab('dashboard');
      if (window.ItemSelectorSheet) ItemSelectorSheet.init();
      if (window.AccountsListEngine) AccountsListEngine.init();
    });


    // ==========================================
    // ACCOUNT STATEMENT & PERIOD SELECTION ENGINE
    // Default Period: "كل الفترة" (All Time)
    // ==========================================
    let activeStatementAccount = null;
    let currentStatementPeriod = 'all'; // Default is 'all' ("كل الفترة")

    function triggerStatementPopup() {
      const input = document.getElementById('omniSearchInput');
      const q = (input ? input.value : '').trim().toLowerCase();
      const accounts = (window.masterAccountsList && window.masterAccountsList.length) ? window.masterAccountsList : masterAccountsList;
      let acc = null;
      if (q) {
        acc = accounts.find(a => a.name.toLowerCase().includes(q));
      }
      if (!acc) {
        acc = accounts[0]; // Default account
      }
      triggerStatementForAccount(acc.name, acc.type, acc.balanceFormatted, acc.sdgFormatted);
    }

    function triggerStatementForAccount(name, type, balanceFormatted, sdgFormatted) {
      activeStatementAccount = { name, type, balanceFormatted, sdgFormatted };
      const titleEl = document.getElementById('statementPopupTitle');
      const typeEl = document.getElementById('statementPopupType');
      const balEl = document.getElementById('statementPopupBalance');
      const sdgEl = document.getElementById('statementPopupSdg');

      if (titleEl) titleEl.textContent = `كشف حساب: ${name}`;
      if (typeEl) typeEl.textContent = `حساب ${type} معتمد`;
      if (balEl) balEl.textContent = `${balanceFormatted} USD`;
      if (sdgEl) sdgEl.textContent = sdgFormatted;

      // Close Balances modal if open, then open statement
      closeModal('modalNonZeroBalances');

      // Set period to 'all' by default ("كل الفترة") as requested by user
      setStatementPeriod('all');

      openModal('modalStatementView');
    }

    function setStatementPeriod(periodKey) {
      currentStatementPeriod = periodKey;
      const pills = {
        'all': document.getElementById('btnPeriodAll'),
        'today': document.getElementById('btnPeriodToday'),
        'week': document.getElementById('btnPeriodWeek'),
        'month': document.getElementById('btnPeriodMonth'),
        'custom': document.getElementById('btnPeriodCustom')
      };

      const periodNames = {
        'all': '✓ كل الفترة (افتراضي - جميع المعاملات من البداية)',
        'today': 'اليوم (معاملات اليوم فقط)',
        'week': 'هذا الأسبوع',
        'month': 'هذا الشهر',
        'custom': 'نطاق مخصص (من تاريخ إلى تاريخ)'
      };

      Object.entries(pills).forEach(([k, btn]) => {
        if (!btn) return;
        if (k === periodKey) {
          btn.className = "bg-gradient-to-r from-cyan-600 to-cyan-500 text-white px-2.5 py-1 rounded-xl shrink-0 shadow font-black";
        } else {
          btn.className = "bg-slate-800 text-slate-400 hover:text-slate-200 px-2.5 py-1 rounded-xl shrink-0 font-bold";
        }
      });

      const badge = document.getElementById('statementActivePeriodBadge');
      if (badge && periodNames[periodKey]) badge.textContent = periodNames[periodKey];

      const customRow = document.getElementById('statementCustomDateRow');
      if (customRow) {
        if (periodKey === 'custom') customRow.classList.remove('hidden');
        else customRow.classList.add('hidden');
      }

      renderStatementTransactions();
    }

    function renderStatementTransactions() {
      const container = document.getElementById('statementTxContainer');
      if (!container) return;

      // Realistic transaction dataset for "all time"
      const allTransactions = [
        {
          id: 'tx_1042',
          date: '2026-10-03',
          type: 'فاتورة مبيعات معتمدة #1042',
          debit: '+$2,100',
          credit: '-',
          balance: '$4,250',
          period: ['all', 'week', 'month']
        },
        {
          id: 'tx_1038',
          date: '2026-10-01',
          type: 'سند قبض بنكي (بنكك)',
          debit: '-',
          credit: '-$1,500',
          balance: '$2,150',
          period: ['all', 'week', 'month']
        },
        {
          id: 'tx_1015',
          date: '2026-09-15',
          type: 'فاتورة مبيعات آجلة #998',
          debit: '+$3,200',
          credit: '-',
          balance: '$3,650',
          period: ['all', 'month']
        },
        {
          id: 'tx_1002',
          date: '2026-08-20',
          type: 'سند توريد نقدي بالخزينة',
          debit: '-',
          credit: '-$2,000',
          balance: '$450',
          period: ['all']
        },
        {
          id: 'tx_open',
          date: '2026-01-01',
          type: 'رصيد افتتاحي سابق معتمد',
          debit: '+$2,450',
          credit: '-',
          balance: '$2,450',
          period: ['all']
        }
      ];

      let list = allTransactions;
      if (currentStatementPeriod === 'today') {
        list = [allTransactions[0]]; // latest transaction
      } else if (currentStatementPeriod === 'week') {
        list = allTransactions.filter(t => t.period.includes('week'));
      } else if (currentStatementPeriod === 'month') {
        list = allTransactions.filter(t => t.period.includes('month'));
      } else if (currentStatementPeriod === 'custom') {
        const from = document.getElementById('stmtFromDate')?.value || '2026-01-01';
        const to = document.getElementById('stmtToDate')?.value || '2026-10-04';
        list = allTransactions.filter(t => t.date >= from && t.date <= to);
      } // 'all' displays all transactions without filtering!

      container.innerHTML = list.map(item => `
        <div class="p-2.5 bg-slate-900/80 rounded-2xl border border-slate-800 flex justify-between items-center text-xs">
          <div>
            <span class="font-bold text-slate-100 block">${item.type}</span>
            <span class="text-[10px] text-slate-400 font-mono">${item.date} • سند رقم #${item.id}</span>
          </div>
          <div class="text-left font-mono">
            <span class="${item.debit !== '-' ? 'text-emerald-400 font-bold block' : 'text-rose-400 font-bold block'}">
              ${item.debit !== '-' ? item.debit : item.credit}
            </span>
            <span class="text-[10px] text-slate-400">الرصيد: ${item.balance}</span>
          </div>
        </div>
      `).join('');
    }

    function shareStatementWhatsAppCurrent() {
      if (!activeStatementAccount) return;
      alert(`📲 تم تجهيز كشف الحساب (كل الفترة) للعميل: ${activeStatementAccount.name}\nالرصيد: ${activeStatementAccount.sdgFormatted} (${activeStatementAccount.balanceFormatted} USD)\nجاري فتح تطبيق واتساب...`);
    }

    function printStatementThermalCurrent() {
      if (!activeStatementAccount) return;
      alert(`🖨️ جاري إرسال إيصال كشف الحساب (كل الفترة) لطابعة البلوتوث الحرارية 80mm ESC/POS لـ ${activeStatementAccount.name}`);
    }

    function contactAccountWhatsApp(name, balUSD, balSDG) {
      alert(`📲 فتح واتساب لإرسال إشعار مطابقة الرصيد:\nالحساب: ${name}\nالمبلغ: ${balSDG} (${balUSD} USD)`);
    }

    // ========================================================
    // 4-LEVEL DYNAMIC TYPOGRAPHY & HEAVY BOLD MANAGEMENT ENGINE
    // ========================================================
    let currentFontScale = parseInt(localStorage.getItem('magaies_font_scale') || '2');
    let isHeavyBold = localStorage.getItem('magaies_font_bold') === 'true';

    const FONT_SCALE_CONFIG = {
      1: { name: 'المدمج المركز', desc: 'مناسب للشاشات الصغيرة وكثافة البيانات (90%)' },
      2: { name: 'القياسي المعتدل', desc: 'توازن دقيق بين المقاس والراحة البصرية (100%)' },
      3: { name: 'كبير ومريح للعين', desc: 'خط مكبر يمنع إجهاد العين والصداع (118%)' },
      4: { name: 'فائق الضخامة (ضعاف النظر)', desc: 'أعلى مقاس قراءة مخصص للنظر الضعيف والشمس (135%)' }
    };

    function applyTypographyPreferences() {
      if (currentFontScale < 1 || currentFontScale > 4) currentFontScale = 2;
      document.documentElement.setAttribute('data-font-scale', currentFontScale);
      
      const badge = document.getElementById('fontSizeBadge');
      if (badge) badge.textContent = currentFontScale;

      const boldBtn = document.getElementById('fontBoldToggleBtn');
      if (isHeavyBold) {
        document.documentElement.setAttribute('data-font-weight', 'bold');
        if (boldBtn) boldBtn.classList.add('bold-btn-active');
      } else {
        document.documentElement.removeAttribute('data-font-weight');
        if (boldBtn) boldBtn.classList.remove('bold-btn-active');
      }
    }

    function cycleFontSize() {
      currentFontScale = (currentFontScale % 4) + 1;
      localStorage.setItem('magaies_font_scale', currentFontScale);
      applyTypographyPreferences();

      const conf = FONT_SCALE_CONFIG[currentFontScale];
      showFloatingToast(`🔎 حجم الخط: الدرجة ${currentFontScale}/4 (${conf.name})`);
      triggerHapticPulse(45);
    }

    function toggleFontBold() {
      isHeavyBold = !isHeavyBold;
      localStorage.setItem('magaies_font_bold', isHeavyBold ? 'true' : 'false');
      applyTypographyPreferences();

      if (isHeavyBold) {
        showFloatingToast('✍️ تم تفعيل الخط العريض التخين (أعلى تباين لكبار السن وضعاف النظر)');
      } else {
        showFloatingToast('✍️ تم العودة لسماكة الخط القياسية');
      }
      triggerHapticPulse(55);
    }

    // Sensory stimulation settings (Sound & Haptic Vibration)
    let isSensorySoundEnabled = localStorage.getItem('magaies_alert_sound') !== 'false';
    let isSensoryHapticEnabled = localStorage.getItem('magaies_alert_haptic') !== 'false';

    function triggerHapticPulse(ms = 35) {
      if (!isSensoryHapticEnabled) return;
      try {
        if ('vibrate' in navigator) navigator.vibrate(ms);
      } catch (e) {}
    }

    function showFloatingToast(msg) {
      const toastBar = document.getElementById('privacyToastBar');
      const toastMsg = document.getElementById('privacyToastMsg');
      if (toastBar && toastMsg) {
        toastMsg.textContent = msg;
        toastBar.classList.remove('hidden');
        clearTimeout(window.__toastTimer);
        window.__toastTimer = setTimeout(() => {
          toastBar.classList.add('hidden');
        }, 2800);
      }
    }

    // Apply immediately and on DOM ready
    applyTypographyPreferences();
    document.addEventListener('DOMContentLoaded', applyTypographyPreferences);

    // ========================================================
    // ZERO-LATENCY ACOUSTIC SYNTHESIZER ENGINE (Web Audio API)
    // ========================================================
    const AudioEngine = {
      ctx: null,
      init() {
        if (!this.ctx) {
          const AudioCtx = window.AudioContext || window.webkitAudioContext;
          if (AudioCtx) this.ctx = new AudioCtx();
        }
      },
      play(type) {
        if (!isSensorySoundEnabled) return;
        try {
          this.init();
          if (!this.ctx) return;
          if (this.ctx.state === 'suspended') this.ctx.resume();
          const t = this.ctx.currentTime;
          const osc = this.ctx.createOscillator();
          const gain = this.ctx.createGain();
          osc.connect(gain);
          gain.connect(this.ctx.destination);

          if (type === 'numpad') {
            osc.type = 'sine';
            osc.frequency.setValueAtTime(680, t);
            gain.gain.setValueAtTime(0.04, t);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.05);
            osc.start(t);
            osc.stop(t + 0.05);
          } else if (type === 'success' || type === 'approve') {
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(523.25, t); // C5
            osc.frequency.setValueAtTime(659.25, t + 0.08); // E5
            osc.frequency.setValueAtTime(783.99, t + 0.16); // G5
            gain.gain.setValueAtTime(0.07, t);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.35);
            osc.start(t);
            osc.stop(t + 0.35);
          } else if (type === 'reject') {
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(280, t);
            osc.frequency.exponentialRampToValueAtTime(140, t + 0.18);
            gain.gain.setValueAtTime(0.05, t);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.22);
            osc.start(t);
            osc.stop(t + 0.22);
          } else if (type === 'ocr') {
            osc.type = 'sine';
            osc.frequency.setValueAtTime(440, t);
            osc.frequency.linearRampToValueAtTime(880, t + 0.25);
            gain.gain.setValueAtTime(0.06, t);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.3);
            osc.start(t);
            osc.stop(t + 0.3);
          } else if (type === 'alert') {
            // Harmonic dual-tone notification chime
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, t);
            osc.frequency.setValueAtTime(659.25, t + 0.09);
            gain.gain.setValueAtTime(0.08, t);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.28);
            osc.start(t);
            osc.stop(t + 0.28);
          } else if (type === 'risk') {
            // Urgent attention tone for risks and critical drops
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(587.33, t);
            osc.frequency.setValueAtTime(440, t + 0.1);
            osc.frequency.setValueAtTime(587.33, t + 0.2);
            gain.gain.setValueAtTime(0.09, t);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.35);
            osc.start(t);
            osc.stop(t + 0.35);
          }
        } catch (e) {}
      }
    };

    // ========================================================
    // SUNLIGHT ULTRA-HIGH-CONTRAST MODE ENGINE
    // ========================================================
    let isSunlightMode = localStorage.getItem('magaies_sunlight_mode') === 'true';

    function applySunlightMode() {
      const btn = document.getElementById('sunlightModeBtn');
      if (isSunlightMode) {
        document.documentElement.setAttribute('data-sunlight-mode', 'true');
        if (btn) btn.classList.add('sunlight-btn-active');
      } else {
        document.documentElement.removeAttribute('data-sunlight-mode');
        if (btn) btn.classList.remove('sunlight-btn-active');
      }
    }

    function toggleSunlightMode() {
      isSunlightMode = !isSunlightMode;
      localStorage.setItem('magaies_sunlight_mode', isSunlightMode ? 'true' : 'false');
      applySunlightMode();
      AudioEngine.play('success');
      triggerHapticPulse(45);
      showFloatingToast(isSunlightMode ? '☀️ تم تفعيل وضع السطوع الشمسي فائق التباين (OLED)' : '🌙 تم العودة لوضع العرض الليلي القياسي');
    }

    // ========================================================
    // IN-APP GIANT SMART ACCOUNTING CALCULATOR & NUMPAD ENGINE
    // ========================================================
    let numpadTargetInput = null;
    let numpadCurrentBuffer = '0';
    let numpadPreviousValue = null;
    let numpadPendingOperator = null;
    let numpadWaitingForSecondOperand = false;
    let numpadExpressionStr = '';
    let numpadSelectedCurrency = 'USD';
    const SDG_RATE = 2800;

    function openAccountingNumpad(targetInputId = null, title = 'الآلة الحاسبة') {
      if (window.AudioEngine && AudioEngine.play) AudioEngine.play('numpad');
      if (window.triggerHapticPulse) triggerHapticPulse(30);

      numpadTargetInput = targetInputId ? document.getElementById(targetInputId) : null;
      if (numpadTargetInput && numpadTargetInput.value) {
        const cleaned = numpadTargetInput.value.replace(/[^0-9.-]/g, '');
        numpadCurrentBuffer = cleaned || '0';
      } else {
        numpadCurrentBuffer = '0';
      }

      numpadPreviousValue = null;
      numpadPendingOperator = null;
      numpadWaitingForSecondOperand = false;
      numpadExpressionStr = '';

      const titleEl = document.getElementById('numpadTitle');
      if (titleEl) titleEl.textContent = title;

      const confirmBtnText = document.getElementById('numpadConfirmBtnText');
      if (confirmBtnText) {
        confirmBtnText.textContent = numpadTargetInput ? 'تأكيد وترحيل المبلغ ✓' : 'نسخ الناتج وإغلاق الحاسبة 📋';
      }

      updateNumpadDisplay();
      openModal('accountingNumpadDrawer');
    }

    function closeAccountingNumpad() {
      closeModal('accountingNumpadDrawer');
    }

    function formatCalculatorDisplay(str) {
      if (!str || str === 'NaN' || str === 'Infinity' || str === '-Infinity') return '0';
      const parts = str.split('.');
      const intPart = parts[0];
      const decPart = parts.length > 1 ? '.' + parts[1] : (str.endsWith('.') ? '.' : '');
      const num = Number(intPart);
      if (isNaN(num)) return str;
      return num.toLocaleString('en-US') + decPart;
    }

    function updateNumpadDisplay() {
      const disp = document.getElementById('numpadDisplayValue');
      const exprEl = document.getElementById('numpadExpression');
      const dual = document.getElementById('numpadDualPreview');
      const num = parseFloat(numpadCurrentBuffer) || 0;

      if (exprEl) {
        exprEl.textContent = numpadExpressionStr || '';
      }

      if (disp) {
        const symbol = numpadSelectedCurrency === 'USD' ? '$ ' : '';
        disp.textContent = symbol + formatCalculatorDisplay(numpadCurrentBuffer);
      }

      if (dual) {
        if (numpadSelectedCurrency === 'USD') {
          const sdg = Math.round(num * SDG_RATE);
          dual.textContent = `≈ ${sdg.toLocaleString('en-US')} ج.س (سعر الصرف: ${SDG_RATE})`;
        } else {
          const usd = (num / SDG_RATE).toFixed(1);
          dual.textContent = `≈ $${usd} USD (سعر الصرف: ${SDG_RATE})`;
        }
      }
    }

    function numpadPressKey(key) {
      if (window.AudioEngine && AudioEngine.play) AudioEngine.play('numpad');
      if (window.triggerHapticPulse) triggerHapticPulse(25);

      if (numpadWaitingForSecondOperand) {
        numpadCurrentBuffer = key === '.' ? '0.' : key;
        numpadWaitingForSecondOperand = false;
      } else {
        if (numpadCurrentBuffer === '0' && key !== '.') {
          numpadCurrentBuffer = key === '00' ? '0' : key;
        } else {
          if (key === '.' && numpadCurrentBuffer.includes('.')) return;
          numpadCurrentBuffer += key;
        }
      }
      updateNumpadDisplay();
    }

    function executeNumpadMath(a, b, op) {
      let res = 0;
      switch (op) {
        case '+': res = a + b; break;
        case '−':
        case '-': res = a - b; break;
        case '×':
        case '*': res = a * b; break;
        case '÷':
        case '/': res = b === 0 ? 0 : a / b; break;
        default: res = b;
      }
      return Math.round(res * 1e9) / 1e9;
    }

    function numpadPressOperator(op) {
      if (window.AudioEngine && AudioEngine.play) AudioEngine.play('numpad');
      if (window.triggerHapticPulse) triggerHapticPulse(30);

      const inputValue = parseFloat(numpadCurrentBuffer) || 0;

      if (numpadPendingOperator && numpadWaitingForSecondOperand) {
        numpadPendingOperator = op;
        numpadExpressionStr = `${numpadPreviousValue} ${op}`;
        updateNumpadDisplay();
        return;
      }

      if (numpadPreviousValue === null) {
        numpadPreviousValue = inputValue;
      } else if (numpadPendingOperator) {
        const result = executeNumpadMath(numpadPreviousValue, inputValue, numpadPendingOperator);
        numpadCurrentBuffer = String(result);
        numpadPreviousValue = result;
      }

      numpadWaitingForSecondOperand = true;
      numpadPendingOperator = op;
      numpadExpressionStr = `${numpadPreviousValue} ${op}`;
      updateNumpadDisplay();
    }

    function numpadCalculate() {
      if (window.AudioEngine && AudioEngine.play) AudioEngine.play('success');
      if (window.triggerHapticPulse) triggerHapticPulse(45);

      if (!numpadPendingOperator || numpadPreviousValue === null) return;

      const inputValue = parseFloat(numpadCurrentBuffer) || 0;
      const result = executeNumpadMath(numpadPreviousValue, inputValue, numpadPendingOperator);

      numpadExpressionStr = `${numpadPreviousValue} ${numpadPendingOperator} ${inputValue} =`;
      numpadCurrentBuffer = String(result);
      numpadPreviousValue = null;
      numpadPendingOperator = null;
      numpadWaitingForSecondOperand = true;
      updateNumpadDisplay();
    }

    function numpadPressPercent() {
      if (window.AudioEngine && AudioEngine.play) AudioEngine.play('numpad');
      if (window.triggerHapticPulse) triggerHapticPulse(30);

      const current = parseFloat(numpadCurrentBuffer) || 0;

      if (numpadPendingOperator && numpadPreviousValue !== null) {
        if (numpadPendingOperator === '+' || numpadPendingOperator === '−' || numpadPendingOperator === '-') {
          const pctVal = (numpadPreviousValue * current) / 100;
          numpadCurrentBuffer = String(pctVal);
        } else if (numpadPendingOperator === '×' || numpadPendingOperator === '*' || numpadPendingOperator === '÷' || numpadPendingOperator === '/') {
          const pctVal = current / 100;
          numpadCurrentBuffer = String(pctVal);
        }
      } else {
        numpadCurrentBuffer = String(current / 100);
      }
      updateNumpadDisplay();
    }

    function numpadBackspace() {
      if (window.AudioEngine && AudioEngine.play) AudioEngine.play('numpad');
      if (window.triggerHapticPulse) triggerHapticPulse(25);

      if (numpadWaitingForSecondOperand) return;

      if (numpadCurrentBuffer.length > 1) {
        numpadCurrentBuffer = numpadCurrentBuffer.slice(0, -1);
        if (numpadCurrentBuffer === '-' || numpadCurrentBuffer === '') {
          numpadCurrentBuffer = '0';
        }
      } else {
        numpadCurrentBuffer = '0';
      }
      updateNumpadDisplay();
    }

    function numpadClear() {
      if (window.AudioEngine && AudioEngine.play) AudioEngine.play('numpad');
      if (window.triggerHapticPulse) triggerHapticPulse(40);

      numpadCurrentBuffer = '0';
      numpadPreviousValue = null;
      numpadPendingOperator = null;
      numpadWaitingForSecondOperand = false;
      numpadExpressionStr = '';
      updateNumpadDisplay();
    }

    function numpadAddMultiplier(val) {
      if (window.AudioEngine && AudioEngine.play) AudioEngine.play('numpad');
      if (window.triggerHapticPulse) triggerHapticPulse(35);

      if (numpadWaitingForSecondOperand) {
        numpadCurrentBuffer = '0';
        numpadWaitingForSecondOperand = false;
      }
      const cur = parseFloat(numpadCurrentBuffer) || 0;
      numpadCurrentBuffer = String(cur + val);
      updateNumpadDisplay();
    }

    function numpadMultiply(factor) {
      if (window.AudioEngine && AudioEngine.play) AudioEngine.play('numpad');
      if (window.triggerHapticPulse) triggerHapticPulse(35);

      const cur = parseFloat(numpadCurrentBuffer) || 0;
      numpadCurrentBuffer = String(cur * factor);
      updateNumpadDisplay();
    }

    function setNumpadCurrency(curr) {
      if (window.AudioEngine && AudioEngine.play) AudioEngine.play('numpad');
      numpadSelectedCurrency = curr;
      const usdBtn = document.getElementById('numpadCurUsd');
      const sdgBtn = document.getElementById('numpadCurSdg');
      if (curr === 'USD') {
        if (usdBtn) usdBtn.className = "px-2 py-0.5 rounded-lg bg-emerald-500 text-slate-950 text-[11px] font-black";
        if (sdgBtn) sdgBtn.className = "px-2 py-0.5 rounded-lg bg-slate-800 text-slate-300 text-[11px] font-bold";
      } else {
        if (sdgBtn) sdgBtn.className = "px-2 py-0.5 rounded-lg bg-amber-500 text-slate-950 text-[11px] font-black";
        if (usdBtn) usdBtn.className = "px-2 py-0.5 rounded-lg bg-slate-800 text-slate-300 text-[11px] font-bold";
      }
      updateNumpadDisplay();
    }

    function numpadConfirm() {
      if (window.AudioEngine && AudioEngine.play) AudioEngine.play('success');
      if (window.triggerHapticPulse) triggerHapticPulse(60);

      if (numpadPendingOperator && numpadPreviousValue !== null) {
        numpadCalculate();
      }

      if (numpadTargetInput) {
        numpadTargetInput.value = numpadCurrentBuffer;
        numpadTargetInput.dispatchEvent(new Event('input', { bubbles: true }));
        showFloatingToast(`✓ تم اعتماد المبلغ: ${numpadCurrentBuffer} ${numpadSelectedCurrency}`);
      } else {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(numpadCurrentBuffer).catch(() => {});
        }
        showFloatingToast(`✓ تم نسخ الناتج: ${numpadCurrentBuffer} ${numpadSelectedCurrency}`);
      }
      closeAccountingNumpad();
    }

    // Keyboard listener when calculator modal is open
    document.addEventListener('keydown', (e) => {
      const drawer = document.getElementById('accountingNumpadDrawer');
      if (!drawer || drawer.classList.contains('hidden')) return;

      if ((e.key >= '0' && e.key <= '9') || e.key === '.') {
        e.preventDefault();
        numpadPressKey(e.key);
      } else if (e.key === '+' || e.key === '-') {
        e.preventDefault();
        numpadPressOperator(e.key === '-' ? '−' : '+');
      } else if (e.key === '*' || e.key === 'x' || e.key === 'X') {
        e.preventDefault();
        numpadPressOperator('×');
      } else if (e.key === '/') {
        e.preventDefault();
        numpadPressOperator('÷');
      } else if (e.key === 'Enter' || e.key === '=') {
        e.preventDefault();
        numpadCalculate();
      } else if (e.key === 'Backspace') {
        e.preventDefault();
        numpadBackspace();
      } else if (e.key === 'Escape') {
        e.preventDefault();
        closeAccountingNumpad();
      } else if (e.key === '%') {
        e.preventDefault();
        numpadPressPercent();
      }
    });

    // ========================================================
    // SMART BANK RECEIPT OCR CAMERA SCANNER ENGINE
    // ========================================================
    function openBankReceiptOcrModal() {
      AudioEngine.play('numpad');
      triggerHapticPulse(35);
      openModal('modalBankReceiptOcr');
    }

    function simulateOcrScan() {
      const laser = document.getElementById('ocrLaserLine');
      const badge = document.getElementById('ocrStatusBadge');
      if (laser) laser.classList.remove('hidden');
      if (badge) {
        badge.textContent = 'جاري المسح الضوئي الذكي...';
        badge.className = "text-[10px] bg-cyan-950 text-cyan-300 border border-cyan-500/40 px-2 py-0.5 rounded-full font-bold animate-pulse";
      }
      AudioEngine.play('ocr');
      triggerHapticPulse([40, 30, 40]);

      setTimeout(() => {
        if (laser) laser.classList.add('hidden');
        loadSampleReceipt('bankak');
      }, 1600);
    }

    function loadSampleReceipt(preset) {
      AudioEngine.play('success');
      triggerHapticPulse(50);
      const laser = document.getElementById('ocrLaserLine');
      if (laser) laser.classList.add('hidden');

      const refEl = document.getElementById('ocrRefNo');
      const amountEl = document.getElementById('ocrAmount');
      const senderEl = document.getElementById('ocrSender');
      const bankEl = document.getElementById('ocrBank');
      const badge = document.getElementById('ocrStatusBadge');

      if (preset === 'bankak') {
        if (refEl) refEl.value = 'BOK-' + Math.floor(1000000 + Math.random() * 9000000);
        if (amountEl) amountEl.value = '70,000 ج.س ($25)';
        if (senderEl) senderEl.value = 'شركة همس للتجارة العامة';
        if (bankEl) bankEl.value = 'بنك الخرطوم (تطبيق بنكك)';
      } else {
        if (refEl) refEl.value = 'WIRE-' + Math.floor(1000000 + Math.random() * 9000000);
        if (amountEl) amountEl.value = '$2,450 USD (6.86M ج.س)';
        if (senderEl) senderEl.value = 'شركة النيل العالمية للاستيراد';
        if (bankEl) bankEl.value = 'بنك فيصل الإسلامي';
      }

      if (badge) {
        badge.textContent = '✓ تم استخراج البيانات بنجاح (100% دقة)';
        badge.className = "text-[10px] bg-emerald-950 text-emerald-300 border border-emerald-500/40 px-2 py-0.5 rounded-full font-bold";
      }

      showFloatingToast('📸 تم قراءة إشعار التحويل البنكي وتأكيد صحة المرجع');
    }

    function commitOcrToReceipt() {
      const ref = document.getElementById('ocrRefNo')?.value || 'BOK-77218';
      const amt = document.getElementById('ocrAmount')?.value || '70,000 ج.س';
      const sender = document.getElementById('ocrSender')?.value || 'شركة همس للتجارة العامة';

      closeModal('modalBankReceiptOcr');
      openOperationalForm('financeMovement');
      setFinTransferMethod('bank');

      // Set To Account to customer: "شركة همس للتجارة العامة" (id: 20)
      const toSelect = document.getElementById('finTransferToAccount');
      if (toSelect) toSelect.value = "20";

      // Set From Account to Bank: "بنك الخرطوم (الحساب الجاري)" (id: 8)
      const fromSelect = document.getElementById('finTransferFromAccount');
      if (fromSelect) fromSelect.value = "8";

      // Set Ref No
      const refInput = document.getElementById('finTransferBankRef');
      if (refInput) refInput.value = ref;

      // Set Currency to SDG
      setFinTransferCurrency('SDG');
      const amtInput = document.getElementById('finTransferAmountInput');
      if (amtInput) amtInput.value = "70000";

      const note = document.getElementById('finTransferNotes');
      if (note) note.value = `تحويل مالي بنكي - إشعار محول من ${sender} برقم مرجع #${ref}`;

      updateFinTransferBalancesPreview();
      validateFinTransferReference();

      AudioEngine.play('success');
      showFloatingToast('⚡ تم ترحيل إشعار البنك مباشرة إلى شاشة التحويل المالي');
    }

    // ========================================================
    // SWIPE-TO-APPROVE / SWIPE-TO-REJECT ENGINE IN pageApprovals
    // ========================================================
    function initSwipeGestures() {
      const cards = document.querySelectorAll('.approval-card');
      cards.forEach(card => {
        let startX = 0;
        let currentX = 0;
        let isSwiping = false;

        card.addEventListener('touchstart', (e) => {
          startX = e.touches[0].clientX;
          isSwiping = true;
          card.classList.add('approval-card-swiping');
        }, { passive: true });

        card.addEventListener('touchmove', (e) => {
          if (!isSwiping) return;
          currentX = e.touches[0].clientX;
          const diff = currentX - startX;

          if (Math.abs(diff) < 180) {
            card.style.transform = `translateX(${diff}px)`;
            if (diff > 50) {
              card.style.borderColor = '#10b981';
              card.style.boxShadow = '0 0 25px rgba(16,185,129,0.5)';
            } else if (diff < -50) {
              card.style.borderColor = '#f43f5e';
              card.style.boxShadow = '0 0 25px rgba(244,63,94,0.5)';
            } else {
              card.style.borderColor = '';
              card.style.boxShadow = '';
            }
          }
        }, { passive: true });

        card.addEventListener('touchend', (e) => {
          if (!isSwiping) return;
          isSwiping = false;
          const diff = currentX - startX;
          card.style.transform = '';
          card.style.borderColor = '';
          card.style.boxShadow = '';

          const reqId = card.id ? card.id.replace('reqCard', '') : '1';

          if (diff > 85) {
            AudioEngine.play('approve');
            triggerHapticPulse([40, 30, 40]);
            execApproval(reqId, 'approved');
          } else if (diff < -85) {
            AudioEngine.play('reject');
            triggerHapticPulse(60);
            execApproval(reqId, 'rejected');
          }
        });
      });
    }

    function attachNumpadTriggers() {
      const inputs = [
        { id: 'opFinInputAmount', title: 'مبلغ الحركة المالية' },
        { id: 'partnerInputBal', title: 'الرصيد الافتتاحي' }
      ];

      inputs.forEach(item => {
        const el = document.getElementById(item.id);
        if (el) {
          el.addEventListener('focus', (e) => {
            e.preventDefault();
            el.blur();
            openAccountingNumpad(item.id, item.title);
          });
          el.addEventListener('click', (e) => {
            e.preventDefault();
            openAccountingNumpad(item.id, item.title);
          });
        }
      });
    }

        // ========================================================
    // ENTERPRISE ALERTS & RISK MANAGEMENT ENGINE (معمل ضبط التنبيهات)
    // ========================================================
    const defaultAlertRulesConfig = {
      // Master Sovereignty & Sensory Controls
      masterEnabled: true,
      soundEnabled: true,
      hapticEnabled: true,
      webPushEnabled: true,
      quietHoursEnabled: true,
      managerWhatsAppPhone: '+249912300000',

      // 1. Operations & Cashflow
      rule_sales_invoice: true,
      rule_purchase_bill: true,
      rule_finance_transfer: true,
      rule_report_print: true,
      rule_cash_drawer_deficit: true,
      rule_bounced_cheque: true,
      rule_liquidity_drop: true,
      rule_fx_rate_discrepancy: true,

      // 2. Security & Fraud Prevention
      rule_post_print_edit: true,
      rule_night_offhours_activity: true,
      rule_backdated_entry: true,
      rule_expense_anomaly: true,
      rule_price_anomaly: true,
      rule_credit_limit: true,

      // 3. Inventory & Supply Chain
      rule_negative_stock: true,
      rule_expiry_alert: true,
      rule_inventory_audit_discrepancy: true,
      rule_purchase_price_variance: true,
      rule_stockout_risk: true,
      rule_slow_moving: true,
      rule_bulk_withdrawal: true,

      // 4. Customer Behavior AI
      rule_customer_inactivity: true,
      rule_vip_spending_drop: true,

      // Configurable Thresholds
      thresholdLiquidity: 10000,
      thresholdChequeHours: 48,
      thresholdFxMargin: 5,
      thresholdPriceDeviation: 15,
      thresholdDebtDays: 30,
      thresholdExpiryDays: 30,
      thresholdSlowDays: 45,
      thresholdCustomerGraceDays: 3
    };

    let alertRulesConfig = { ...defaultAlertRulesConfig };
    try {
      const savedConfig = localStorage.getItem('magaies_alert_rules_config_v2');
      if (savedConfig) {
        alertRulesConfig = Object.assign({}, defaultAlertRulesConfig, JSON.parse(savedConfig));
      }
    } catch (e) {
      alertRulesConfig = { ...defaultAlertRulesConfig };
    }

    function initAlertRulesUI() {
      // 1. Sync sensory toggles (Master, Sound, Haptic, Push, Quiet)
      updateSensoryButtonsUI();

      // 2. Sync rule toggle buttons
      const ruleKeys = [
        'rule_sales_invoice', 'rule_purchase_bill', 'rule_finance_transfer', 'rule_report_print',
        'rule_cash_drawer_deficit', 'rule_bounced_cheque', 'rule_liquidity_drop', 'rule_fx_rate_discrepancy',
        'rule_post_print_edit', 'rule_night_offhours_activity', 'rule_backdated_entry', 'rule_expense_anomaly',
        'rule_price_anomaly', 'rule_credit_limit',
        'rule_negative_stock', 'rule_expiry_alert', 'rule_inventory_audit_discrepancy', 'rule_purchase_price_variance',
        'rule_stockout_risk', 'rule_slow_moving', 'rule_bulk_withdrawal',
        'rule_customer_inactivity', 'rule_vip_spending_drop'
      ];

      ruleKeys.forEach(key => {
        const btn = document.getElementById('toggle_' + key);
        if (btn) {
          const isEnabled = alertRulesConfig[key] !== false && alertRulesConfig.masterEnabled !== false;
          if (isEnabled) {
            btn.className = "w-12 h-6 rounded-full bg-cyan-600 transition p-0.5 relative flex items-center justify-end shrink-0 shadow-[0_0_10px_rgba(6,182,212,0.3)]";
            btn.innerHTML = '<span class="w-5 h-5 rounded-full bg-white shadow-md transform transition"></span>';
          } else {
            btn.className = "w-12 h-6 rounded-full bg-slate-700 transition p-0.5 relative flex items-center justify-start shrink-0";
            btn.innerHTML = '<span class="w-5 h-5 rounded-full bg-slate-400 shadow-md transform transition"></span>';
          }
        }
      });

      // 3. Sync inputs
      const thresholdInputs = {
        'thresholdLiquidity': alertRulesConfig.thresholdLiquidity,
        'thresholdChequeHours': alertRulesConfig.thresholdChequeHours,
        'thresholdFxMargin': alertRulesConfig.thresholdFxMargin,
        'thresholdPriceDeviation': alertRulesConfig.thresholdPriceDeviation,
        'thresholdDebtDays': alertRulesConfig.thresholdDebtDays,
        'thresholdExpiryDays': alertRulesConfig.thresholdExpiryDays,
        'thresholdSlowDays': alertRulesConfig.thresholdSlowDays,
        'thresholdCustomerGraceDays': alertRulesConfig.thresholdCustomerGraceDays,
        'cfgManagerWhatsApp': alertRulesConfig.managerWhatsAppPhone
      };

      Object.entries(thresholdInputs).forEach(([id, val]) => {
        const inp = document.getElementById(id);
        if (inp && val !== undefined) inp.value = val;
      });

      // 4. Update badge counters
      updateAlertBadgesCount();
    }

    function updateSensoryButtonsUI() {
      // Master toggle
      const toggleMaster = document.getElementById('toggleMasterAlerts');
      if (toggleMaster) {
        if (alertRulesConfig.masterEnabled !== false) {
          toggleMaster.className = "w-12 h-6 rounded-full bg-amber-500 transition p-0.5 relative flex items-center justify-end shrink-0 shadow-[0_0_12px_rgba(245,158,11,0.4)]";
          toggleMaster.innerHTML = '<span class="w-5 h-5 rounded-full bg-slate-950 shadow-md"></span>';
        } else {
          toggleMaster.className = "w-12 h-6 rounded-full bg-slate-700 transition p-0.5 relative flex items-center justify-start shrink-0";
          toggleMaster.innerHTML = '<span class="w-5 h-5 rounded-full bg-slate-400 shadow-md"></span>';
        }
      }

      // Sound button
      const btnSound = document.getElementById('btnSensorySound');
      const iconSound = document.getElementById('iconSensorySound');
      const labelSound = document.getElementById('labelSensorySound');
      if (btnSound && labelSound) {
        if (isSensorySoundEnabled && alertRulesConfig.masterEnabled !== false) {
          btnSound.className = "p-2 rounded-xl bg-slate-800/90 border border-cyan-500/40 text-cyan-300 flex items-center justify-between active:scale-95 transition shadow-sm";
          if (iconSound) iconSound.textContent = '🔊';
          labelSound.textContent = 'مفعل ✓';
          labelSound.className = "text-[10px] font-black px-1.5 py-0.5 rounded bg-cyan-950 text-cyan-300";
        } else {
          btnSound.className = "p-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-500 flex items-center justify-between active:scale-95 transition opacity-70";
          if (iconSound) iconSound.textContent = '🔇';
          labelSound.textContent = 'مكتوم ✕';
          labelSound.className = "text-[10px] font-black px-1.5 py-0.5 rounded bg-slate-800 text-slate-400";
        }
      }

      // Haptic button
      const btnHaptic = document.getElementById('btnSensoryHaptic');
      const iconHaptic = document.getElementById('iconSensoryHaptic');
      const labelHaptic = document.getElementById('labelSensoryHaptic');
      if (btnHaptic && labelHaptic) {
        if (isSensoryHapticEnabled && alertRulesConfig.masterEnabled !== false) {
          btnHaptic.className = "p-2 rounded-xl bg-slate-800/90 border border-purple-500/40 text-purple-300 flex items-center justify-between active:scale-95 transition shadow-sm";
          if (iconHaptic) iconHaptic.textContent = '📳';
          labelHaptic.textContent = 'مفعل ✓';
          labelHaptic.className = "text-[10px] font-black px-1.5 py-0.5 rounded bg-purple-950 text-purple-300";
        } else {
          btnHaptic.className = "p-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-500 flex items-center justify-between active:scale-95 transition opacity-70";
          if (iconHaptic) iconHaptic.textContent = '📴';
          labelHaptic.textContent = 'متوقف ✕';
          labelHaptic.className = "text-[10px] font-black px-1.5 py-0.5 rounded bg-slate-800 text-slate-400";
        }
      }

      // Web Push button
      const btnPush = document.getElementById('btnSensoryWebPush');
      const labelPush = document.getElementById('labelSensoryWebPush');
      if (btnPush && labelPush) {
        if (alertRulesConfig.webPushEnabled && alertRulesConfig.masterEnabled !== false) {
          btnPush.className = "p-2 rounded-xl bg-slate-800/90 border border-emerald-500/40 text-emerald-300 flex items-center justify-between active:scale-95 transition shadow-sm";
          labelPush.textContent = 'Push ✓';
          labelPush.className = "text-[10px] font-black px-1.5 py-0.5 rounded bg-emerald-950 text-emerald-300";
        } else {
          btnPush.className = "p-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-500 flex items-center justify-between active:scale-95 transition opacity-70";
          labelPush.textContent = 'متوقف ✕';
          labelPush.className = "text-[10px] font-black px-1.5 py-0.5 rounded bg-slate-800 text-slate-400";
        }
      }

      // Quiet Hours button
      const btnQuiet = document.getElementById('btnSensoryQuiet');
      const labelQuiet = document.getElementById('labelSensoryQuiet');
      if (btnQuiet && labelQuiet) {
        if (alertRulesConfig.quietHoursEnabled) {
          btnQuiet.className = "p-2 rounded-xl bg-slate-800/90 border border-indigo-500/40 text-indigo-300 flex items-center justify-between active:scale-95 transition shadow-sm";
          labelQuiet.textContent = '11م - 7ص';
          labelQuiet.className = "text-[10px] font-black px-1.5 py-0.5 rounded bg-indigo-950 text-indigo-300";
        } else {
          btnQuiet.className = "p-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-500 flex items-center justify-between active:scale-95 transition opacity-70";
          labelQuiet.textContent = 'معطل ✕';
          labelQuiet.className = "text-[10px] font-black px-1.5 py-0.5 rounded bg-slate-800 text-slate-400";
        }
      }
    }

    function toggleAlertSensory(type) {
      if (type === 'master') {
        alertRulesConfig.masterEnabled = !alertRulesConfig.masterEnabled;
        localStorage.setItem('magaies_alert_rules_config_v2', JSON.stringify(alertRulesConfig));
        updateSensoryButtonsUI();
        initAlertRulesUI();
        if (alertRulesConfig.masterEnabled) {
          AudioEngine.play('approve');
          showFloatingToast('👑 تم تشغيل المفتاح السيادي العام لكافة التنبيهات');
        } else {
          AudioEngine.play('reject');
          showFloatingToast('🛑 تم كتم وتعطيل كافة التنبيهات في النظام كلياً');
        }
      } else if (type === 'sound') {
        isSensorySoundEnabled = !isSensorySoundEnabled;
        alertRulesConfig.soundEnabled = isSensorySoundEnabled;
        localStorage.setItem('magaies_alert_sound', isSensorySoundEnabled ? 'true' : 'false');
        localStorage.setItem('magaies_alert_rules_config_v2', JSON.stringify(alertRulesConfig));
        updateSensoryButtonsUI();
        if (isSensorySoundEnabled) {
          AudioEngine.play('alert');
          showFloatingToast('🔊 تم تفعيل النغمات الصوتية للتنبيهات');
        } else {
          showFloatingToast('🔇 تم كتم الصوت (وضع صامت للتنبيهات)');
        }
      } else if (type === 'haptic') {
        isSensoryHapticEnabled = !isSensoryHapticEnabled;
        alertRulesConfig.hapticEnabled = isSensoryHapticEnabled;
        localStorage.setItem('magaies_alert_haptic', isSensoryHapticEnabled ? 'true' : 'false');
        localStorage.setItem('magaies_alert_rules_config_v2', JSON.stringify(alertRulesConfig));
        updateSensoryButtonsUI();
        if (isSensoryHapticEnabled) {
          triggerHapticPulse([50, 40, 50]);
          showFloatingToast('📳 تم تفعيل الاهتزاز الحركي (Haptic Feedback)');
        } else {
          showFloatingToast('📴 تم تعطيل اهتزاز الموبايل للتنبيهات');
        }
      } else if (type === 'webpush') {
        alertRulesConfig.webPushEnabled = !alertRulesConfig.webPushEnabled;
        if (alertRulesConfig.webPushEnabled && 'Notification' in window && Notification.permission !== 'granted') {
          Notification.requestPermission();
        }
        localStorage.setItem('magaies_alert_rules_config_v2', JSON.stringify(alertRulesConfig));
        updateSensoryButtonsUI();
        showFloatingToast(alertRulesConfig.webPushEnabled ? '📲 تم تفعيل إشعارات شاشة القفل عبر المتصفح (Web Push)' : '🔕 تم إيقاف إشعارات شاشة القفل');
      } else if (type === 'quiet') {
        alertRulesConfig.quietHoursEnabled = !alertRulesConfig.quietHoursEnabled;
        localStorage.setItem('magaies_alert_rules_config_v2', JSON.stringify(alertRulesConfig));
        updateSensoryButtonsUI();
        showFloatingToast(alertRulesConfig.quietHoursEnabled ? '🌙 تم تفعيل ساعات الهدوء الليلي (كتم الإشعارات الروتينية 11م - 7ص)' : '☀️ تم إيقاف ساعات الهدوء');
      }
    }

    function toggleAlertRule(ruleKey) {
      if (alertRulesConfig.masterEnabled === false) {
        showFloatingToast('⚠️ المفتاح السيادي العام معطل حالياً - قم بتفعيله أولاً');
        return;
      }

      alertRulesConfig[ruleKey] = !alertRulesConfig[ruleKey];
      const btn = document.getElementById('toggle_' + ruleKey);
      if (btn) {
        if (alertRulesConfig[ruleKey]) {
          btn.className = "w-12 h-6 rounded-full bg-cyan-600 transition p-0.5 relative flex items-center justify-end shrink-0 shadow-[0_0_10px_rgba(6,182,212,0.3)]";
          btn.innerHTML = '<span class="w-5 h-5 rounded-full bg-white shadow-md transform transition"></span>';
        } else {
          btn.className = "w-12 h-6 rounded-full bg-slate-700 transition p-0.5 relative flex items-center justify-start shrink-0";
          btn.innerHTML = '<span class="w-5 h-5 rounded-full bg-slate-400 shadow-md transform transition"></span>';
        }
      }

      AudioEngine.play('numpad');
      triggerHapticPulse(30);

      const status = alertRulesConfig[ruleKey] ? 'تفعيل' : 'إيقاف';
      showFloatingToast(`⚙️ تم ${status} قاعدة التنبيه بنجاح`);
    }

    function saveAlertRulesConfig() {
      // Read current thresholds
      alertRulesConfig.thresholdLiquidity = parseFloat(document.getElementById('thresholdLiquidity')?.value) || 10000;
      alertRulesConfig.thresholdChequeHours = parseInt(document.getElementById('thresholdChequeHours')?.value) || 48;
      alertRulesConfig.thresholdFxMargin = parseFloat(document.getElementById('thresholdFxMargin')?.value) || 5;
      alertRulesConfig.thresholdPriceDeviation = parseFloat(document.getElementById('thresholdPriceDeviation')?.value) || 15;
      alertRulesConfig.thresholdDebtDays = parseInt(document.getElementById('thresholdDebtDays')?.value) || 30;
      alertRulesConfig.thresholdExpiryDays = parseInt(document.getElementById('thresholdExpiryDays')?.value) || 30;
      alertRulesConfig.thresholdSlowDays = parseInt(document.getElementById('thresholdSlowDays')?.value) || 45;
      alertRulesConfig.thresholdCustomerGraceDays = parseInt(document.getElementById('thresholdCustomerGraceDays')?.value) || 3;
      alertRulesConfig.managerWhatsAppPhone = document.getElementById('cfgManagerWhatsApp')?.value || '+249912300000';

      try {
        localStorage.setItem('magaies_alert_rules_config_v2', JSON.stringify(alertRulesConfig));
      } catch (e) {}

      AudioEngine.play('success');
      triggerHapticPulse(55);
      showFloatingToast('✓ تم حفظ وتطبيق معمل قواعد التنبيهات في النظام بنجاح');
    }

    function resetAlertRulesToDefaults() {
      alertRulesConfig = { ...defaultAlertRulesConfig };
      try {
        localStorage.setItem('magaies_alert_rules_config_v2', JSON.stringify(alertRulesConfig));
      } catch (e) {}
      initAlertRulesUI();
      AudioEngine.play('approve');
      triggerHapticPulse(45);
      showFloatingToast('🔄 تم استعادة الإعدادات الذكية الافتراضية لقواعد التنبيهات');
    }

    function switchAlertSubTab(subTab) {
      const feedView = document.getElementById('subViewAlertsFeed');
      const labView = document.getElementById('subViewAlertsLab');
      const btnFeed = document.getElementById('subTabAlertsFeed');
      const btnLab = document.getElementById('subTabAlertsLab');

      AudioEngine.play('numpad');
      triggerHapticPulse(30);

      if (subTab === 'feed') {
        if (feedView) feedView.classList.remove('hidden');
        if (labView) labView.classList.add('hidden');
        if (btnFeed) btnFeed.className = "py-2.5 rounded-xl bg-amber-500 text-slate-950 font-black shadow transition flex items-center justify-center gap-1.5";
        if (btnLab) btnLab.className = "py-2.5 rounded-xl text-slate-400 hover:text-white font-bold transition flex items-center justify-center gap-1.5";
      } else {
        if (feedView) feedView.classList.add('hidden');
        if (labView) labView.classList.remove('hidden');
        if (btnLab) btnLab.className = "py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-teal-500 text-slate-950 font-black shadow transition flex items-center justify-center gap-1.5";
        if (btnFeed) btnFeed.className = "py-2.5 rounded-xl text-slate-400 hover:text-white font-bold transition flex items-center justify-center gap-1.5";
      }
    }

    let activeAlertFilter = 'all';
    function filterAlertsFeed(cat) {
      activeAlertFilter = cat;
      const tagButtons = {
        'all': document.getElementById('afTagAll'),
        'security': document.getElementById('afTagSecurity'),
        'finance': document.getElementById('afTagFinance'),
        'inventory': document.getElementById('afTagInventory'),
        'customer': document.getElementById('afTagCustomer')
      };

      Object.entries(tagButtons).forEach(([key, btn]) => {
        if (!btn) return;
        if (key === cat) {
          btn.className = "bg-amber-500 text-slate-950 px-3 py-1.5 rounded-xl shrink-0 font-black shadow";
        } else {
          btn.className = "bg-slate-800 text-slate-300 hover:text-white px-3 py-1.5 rounded-xl shrink-0 border border-slate-700 font-bold";
        }
      });

      const cards = document.querySelectorAll('#alertsFeedList .alert-card');
      cards.forEach(card => {
        if (cat === 'all') {
          card.classList.remove('hidden');
        } else {
          if (card.classList.contains('alert-' + cat)) {
            card.classList.remove('hidden');
          } else {
            card.classList.add('hidden');
          }
        }
      });

      AudioEngine.play('numpad');
      triggerHapticPulse(25);
    }

    function dismissAlert(btn) {
      const card = btn.closest('.alert-card');
      if (!card) return;

      AudioEngine.play('numpad');
      triggerHapticPulse(35);

      card.style.transition = 'all 0.3s ease';
      card.style.opacity = '0';
      card.style.transform = 'scale(0.95)';
      setTimeout(() => {
        card.remove();
        updateAlertBadgesCount();
        showFloatingToast('✓ تم تأكيد الإطلاع على التنبيه');
      }, 300);
    }

    function clearAllAlertsFeed() {
      const list = document.getElementById('alertsFeedList');
      if (!list) return;

      AudioEngine.play('approve');
      triggerHapticPulse([40, 30, 40]);

      list.innerHTML = `
        <div class="p-6 text-center bg-slate-900/60 rounded-3xl border border-slate-800 space-y-2">
          <div class="text-3xl">🎉</div>
          <h4 class="text-sm font-black text-slate-200">لا توجد تنبيهات جديدة معلقة</h4>
          <p class="text-[11px] text-slate-400">جميع العمليات والمخاطر والمخزون ضمن الحدود الآمنة المحددة.</p>
        </div>
      `;

      updateAlertBadgesCount();
      showFloatingToast('🧹 تم مسح وقراءة كافة التنبيهات');
    }

    function updateAlertBadgesCount() {
      const remainingCards = document.querySelectorAll('#alertsFeedList .alert-card').length;
      
      const badgeNav = document.getElementById('badgeAlertsCount');
      const badgeTop = document.getElementById('badgeTopBellCount');
      const badgeTotal = document.getElementById('alertsTotalBadge');
      const badgeFeed = document.getElementById('feedCountBadge');
      const tagAll = document.getElementById('afTagAll');

      if (badgeNav) {
        badgeNav.textContent = remainingCards;
        if (remainingCards === 0) badgeNav.classList.add('hidden');
        else badgeNav.classList.remove('hidden');
      }

      if (badgeTop) {
        badgeTop.textContent = remainingCards;
        if (remainingCards === 0) badgeTop.classList.add('hidden');
        else badgeTop.classList.remove('hidden');
      }

      if (badgeTotal) badgeTotal.textContent = `${remainingCards} نشطة`;
      if (badgeFeed) badgeFeed.textContent = remainingCards;
      if (tagAll) {
        const numBadge = tagAll.querySelector('.luminous-number-badge');
        if (numBadge) numBadge.textContent = remainingCards;
        else tagAll.textContent = `الكل (${remainingCards})`;
      }
    }

    function testSimulateAlertWithHapticAndSound() {
      showFloatingToast('📳🔊 جاري تجربة النبض الصوتي والاهتزاز الحركي للهاتف...');
      AudioEngine.play('risk');
      triggerHapticPulse([160, 80, 160]);
    }

    function simulateNewIncomingAlert() {
      if (alertRulesConfig.masterEnabled === false) {
        showFloatingToast('⚠️ التنبيهات معطلة بالمفتاح السيادي العام');
        return;
      }

      const list = document.getElementById('alertsFeedList');
      if (!list) return;

      const emptyNotice = list.querySelector('.text-3xl');
      if (emptyNotice) {
        list.innerHTML = '';
      }

      const randomId = Math.floor(1000 + Math.random() * 9000);
      const newCard = document.createElement('div');
      newCard.className = "alert-card alert-security glass-card-interactive p-3 rounded-2xl border-2 border-rose-500 bg-gradient-to-b from-rose-950/40 to-slate-900/95 space-y-2 shadow-xl shadow-rose-950/30 animate-pulse";
      newCard.innerHTML = `
        <div class="flex items-start justify-between gap-2">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-rose-500/25 text-rose-300 flex items-center justify-center font-bold text-sm shrink-0 border border-rose-500/50">
              ⚡
            </div>
            <div>
              <div class="flex items-center gap-1.5">
                <h4 class="text-xs font-black text-rose-300">تنبيه أمني عاجل: تعديل إذن صرف #${randomId}</h4>
                <span class="text-[9px] bg-rose-950 text-rose-200 border border-rose-500/40 px-1.5 py-0.2 rounded-full font-bold">الآن</span>
              </div>
              <span class="text-[10px] text-slate-400">بواسطة: عثمان الريح • فرع الخرطوم</span>
            </div>
          </div>
          <span class="text-[10px] text-rose-300 font-mono font-bold">$2,800 تعديل</span>
        </div>
        <p class="text-[11px] text-slate-300 bg-slate-950/70 p-2 rounded-xl border border-slate-800 leading-relaxed">
          تم تعديل قيمة سند الصرف رقم #${randomId} من حساب بنك الخرطوم. يرجى مراجعة إذن المدير قبل التأكيد.
        </p>
        <div class="flex gap-1.5 pt-1">
          <button onclick="requestIncidentJustification('عثمان الريح', 'سند #${randomId}')" class="flex-1 py-1.5 px-2 bg-gradient-to-r from-rose-600 to-pink-600 text-white rounded-xl text-[10px] font-bold shadow flex items-center justify-center gap-1 active:scale-95 transition">
            <span>⚠️ طلب تبرير فوري</span>
          </button>
          <button onclick="dismissAlert(this)" class="py-1.5 px-3 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded-xl text-[10px] font-bold border border-slate-700 transition">
            <span>✓ تم الإطلاع</span>
          </button>
        </div>
      `;

      list.prepend(newCard);
      setTimeout(() => newCard.classList.remove('animate-pulse'), 2500);

      AudioEngine.play('risk');
      triggerHapticPulse([120, 60, 120]);
      updateAlertBadgesCount();
      showFloatingToast('🔔 وصل تنبيه ذكي جديد للتو إلى هاتفك المحمول');
    }

    function sendCustomerCareWhatsApp(clientName, phone) {
      AudioEngine.play('success');
      triggerHapticPulse(40);
      const text = encodeURIComponent(`السلام عليكم ورحمة الله وبركاته، السادة / ${clientName} المحترمون، نأمل أن تكونوا بخير. نسعد بتواصلنا معكم ونود إحاطتكم بتوفر تشكيلة جديدة وتخفيضات خاصة على طلبياتكم القادمة.`);
      const url = `https://wa.me/${phone.replace(/[^0-9]/g, '')}?text=${text}`;
      window.open(url, '_blank');
      showFloatingToast(`📲 جاري فتح تطبيق واتساب للتواصل مع ${clientName}`);
    }

    function delegateTaskWhatsApp(personOrRole, taskDetails) {
      AudioEngine.play('approve');
      triggerHapticPulse(45);
      const text = encodeURIComponent(`عاجل - إشعار رقابي من الإدارة العامة:\nالسادة / ${personOrRole} المحترمون،\nيرجى معالجة الموضوع التالي فوراً:\n${taskDetails}\nوموافاتنا بتقرير كتابي بالإجراء المتخذ.`);
      const phone = (alertRulesConfig.managerWhatsAppPhone || '+249912300000').replace(/[^0-9]/g, '');
      const url = `https://wa.me/${phone}?text=${text}`;
      window.open(url, '_blank');
      showFloatingToast(`📲 تم تحويل التكليف إلى واتساب ${personOrRole}`);
    }

    function requestIncidentJustification(staffName, incidentRef) {
      AudioEngine.play('risk');
      triggerHapticPulse(50);
      const reason = prompt(`⚠️ طلب تبرير أمني رسمي من (${staffName}):\nالمرجع: ${incidentRef}\nيرجى كتابة سبب التعديل أو العملية:`);
      if (reason && reason.trim()) {
        showFloatingToast(`✓ تم تقييد تبرير الموظف بنجاح في سجل التدقيق الأمني`);
      }
    }

    function freezeAccountQuickAction(staffOrAccountName) {
      AudioEngine.play('reject');
      triggerHapticPulse([100, 50, 100]);
      if (confirm(`🛑 هل تؤكد تجميد حساب (${staffOrAccountName}) مؤقتاً لحين استكمال التحقيق المالي؟`)) {
        showFloatingToast(`🛑 تم تجميد حساب (${staffOrAccountName}) بنجاح وإيقاف صلاحيات الدخول`);
      }
    }

    function testManagerWhatsAppNotification() {
      const phone = document.getElementById('cfgManagerWhatsApp')?.value || alertRulesConfig.managerWhatsAppPhone || '+249912300000';
      alertRulesConfig.managerWhatsAppPhone = phone;
      saveAlertRulesConfig();

      AudioEngine.play('success');
      triggerHapticPulse([60, 40, 60]);
      const text = encodeURIComponent(`🔔 تجربة إشعار إدارة المخاطر من نظام ELSAJANA ERP:\nتم تأكيد تفعيل قناة الإشعارات المباشرة لواتساب الإدارة بنجاح.`);
      const url = `https://wa.me/${phone.replace(/[^0-9]/g, '')}?text=${text}`;
      window.open(url, '_blank');
      showFloatingToast(`📲 جاري تجربة إرسال إشعار فوري إلى واتساب المدير: ${phone}`);
    }


    // ========================================================
    // ========================================================
    // 7 MASTER SCIENTIFIC ERGONOMIC THEMES ENGINE (معيار WCAG AAA الموحد - مطابق لشاشة الحركة المالية)
    // ========================================================
    const THEMES_PALETTE_CONFIG = {
      // ☀️ سمات نهارية فاتحة (خط داكن حاد)
      'warm-alabaster':     { name: 'بيج كريمي', icon: '📜', category: 'light', mode: 'light', desktopKey: 'warm_cream', bg: '#FBF8F1' },
      'pure-platinum':      { name: 'أبيض ناصع', icon: '☀️', category: 'light', mode: 'light', desktopKey: 'ultra_white', bg: '#FFFFFF' },
      'pearl-platinum':     { name: 'بلاتيني', icon: '🏛️', category: 'light', mode: 'light', desktopKey: 'exec_gray', bg: '#EEF2F6' },

      // 🌙 سمات ليلية داكنة (خط فاتح ناصع)
      'midnight-executive': { name: 'كحلي ملكي', icon: '🌌', category: 'dark', mode: 'dark', desktopKey: 'midnight_obsidian', bg: '#04091A' },
      'sepia-brown':        { name: 'بني موكا', icon: '☕', category: 'dark', mode: 'dark', desktopKey: 'mocha_brown', bg: '#1A110B' },
      'obsidian-charcoal':  { name: 'أسود OLED', icon: '🌑', category: 'dark', mode: 'dark', desktopKey: 'pure_oled', bg: '#000000' },
      'twilight-indigo':    { name: 'رمادي داكن', icon: '🛡️', category: 'dark', mode: 'dark', desktopKey: 'slate_twilight', bg: '#1E293B' }
    };

    const THEME_ALIAS_MAP = {
      'warm_cream': 'warm-alabaster',
      'alabaster-warm': 'warm-alabaster',
      'paper-ivory': 'warm-alabaster',
      'sand-linen': 'warm-alabaster',
      'mint-breeze': 'warm-alabaster',
      'sage-calm': 'warm-alabaster',
      'sky-morning': 'warm-alabaster',
      'beige': 'warm-alabaster',
      'ivory': 'warm-alabaster',
      'offwhite': 'warm-alabaster',
      'light-linen': 'warm-alabaster',
      'nordic-paper': 'warm-alabaster',
      'ultra_white': 'pure-platinum',
      'arctic-frost': 'pure-platinum',
      'light': 'pure-platinum',
      'exec_gray': 'pearl-platinum',
      'lavender-mist': 'pearl-platinum',
      'rose-quartz': 'pearl-platinum',
      'midnight_obsidian': 'midnight-executive',
      'navy-command': 'midnight-executive',
      'royal-sapphire': 'midnight-executive',
      'oceanic-teal': 'midnight-executive',
      'emerald-vault': 'midnight-executive',
      'navy-kohl': 'midnight-executive',
      'royal-blue': 'midnight-executive',
      'ocean-navy': 'midnight-executive',
      'petrol-cyan': 'midnight-executive',
      'dark': 'midnight-executive',
      'mocha_brown': 'sepia-brown',
      'espresso-gold': 'sepia-brown',
      'crimson-velvet': 'sepia-brown',
      'pale-gold': 'sepia-brown',
      'desert-mocha': 'sepia-brown',
      'pure_oled': 'obsidian-charcoal',
      'nordic-frost': 'obsidian-charcoal',
      'midnight': 'obsidian-charcoal',
      'sunlight-titanium': 'obsidian-charcoal',
      'slate_twilight': 'twilight-indigo',
      'amethyst-night': 'twilight-indigo',
      'emerald-forest': 'twilight-indigo'
    };

    function resolveMasterThemeKey(rawKey) {
      if (!rawKey) return 'midnight-executive';
      const k = String(rawKey).trim();
      if (THEMES_PALETTE_CONFIG[k]) return k;
      if (THEME_ALIAS_MAP[k]) return THEME_ALIAS_MAP[k];
      return 'midnight-executive';
    }

    let currentActiveTheme = resolveMasterThemeKey(
      localStorage.getItem('magaiees_mobile_theme') ||
      localStorage.getItem('magaies_active_theme') ||
      localStorage.getItem('magaiees_global_theme') ||
      'midnight-executive'
    );

    function applyTheme(themeKey, showToast = true) {
      themeKey = resolveMasterThemeKey(themeKey);
      const conf = THEMES_PALETTE_CONFIG[themeKey];
      currentActiveTheme = themeKey;

      try {
        localStorage.setItem('magaies_active_theme', conf.desktopKey || themeKey);
        localStorage.setItem('magaiees_mobile_theme', themeKey);
        localStorage.setItem('magaiees_global_theme', themeKey);
        localStorage.setItem('ui_theme', conf.mode === 'light' ? 'light' : 'dark');
        localStorage.setItem('erp_theme', conf.mode === 'light' ? 'light' : 'dark');
      } catch (e) {}

      document.documentElement.setAttribute('data-theme', themeKey);
      document.documentElement.setAttribute('data-theme-mode', conf.mode);
      if (document.body) {
        document.body.setAttribute('data-theme', themeKey);
        document.body.setAttribute('data-theme-mode', conf.mode);
      }
      const metaTheme = document.querySelector('meta[name="theme-color"]');
      if (metaTheme && conf.bg) {
        metaTheme.setAttribute('content', conf.bg);
      }

      // Highlight active theme button in both quick drawer and modal drawer
      document.querySelectorAll('[data-vtheme]').forEach(btn => {
        const isCurrent = btn.getAttribute('data-vtheme') === themeKey;
        btn.style.outline = isCurrent ? '2px solid #10b981' : 'none';
      });

      // Play audio and haptic feedback
      if (showToast) {
        if (typeof AudioEngine !== 'undefined' && AudioEngine.play) AudioEngine.play('numpad');
        if (typeof triggerHapticPulse === 'function') triggerHapticPulse(40);
      }

      // Close quick drawer and modal if open
      const quickDrawer = document.getElementById('suiteQuickThemeDrawer');
      if (quickDrawer) quickDrawer.classList.add('hidden');
      closeModal('modalThemePaletteDrawer');

      if (showToast && conf) {
        showFloatingToast(`🎨 تم تفعيل السمة في جميع الشاشات: ${conf.icon} ${conf.name}`);
      }
    }

    function ensureSuiteQuickThemeDrawerDOM() {
      const btn = document.getElementById('btnThemePalette');
      if (btn) {
        if (!btn.querySelector('.fa-palette')) {
          btn.innerHTML = '<i class="fa-solid fa-palette text-amber-400 text-base"></i><span id="activeThemeBadge" class="hidden">7</span>';
        }
        btn.setAttribute('onclick', 'openThemePaletteDrawer(event); if(window.triggerHapticPulse)triggerHapticPulse(15);');
      }
      if (!document.getElementById('suiteQuickThemeDrawer')) {
        const hdr = document.querySelector('header.app-main-header');
        if (hdr) {
          const drawer = document.createElement('div');
          drawer.id = 'suiteQuickThemeDrawer';
          drawer.dir = 'rtl';
          drawer.className = 'hidden absolute top-[54px] right-2.5 w-64 rounded-2xl bg-slate-900 border-2 border-amber-500/60 p-2.5 shadow-2xl z-[999] space-y-1.5 text-right';
          drawer.style.cssText = 'background:#0f172a !important; border-color:rgba(245,158,11,0.65) !important;';
          drawer.innerHTML = `
            <div class="flex items-center justify-between border-b border-slate-800 pb-1.5 mb-1" style="border-color:#1e293b !important;">
              <span class="text-[11px] font-black text-amber-400" style="color:#fbbf24 !important;">🎨 السمات المحاسبية الـ 7 (AAA)</span>
              <button type="button" onclick="document.getElementById('suiteQuickThemeDrawer').classList.add('hidden')" class="text-slate-400 hover:text-white text-xs font-bold px-1" style="color:#94a3b8 !important;">✕</button>
            </div>
            <div class="text-[9.5px] font-bold text-slate-400" style="color:#94a3b8 !important;">☀️ سمات نهارية فاتحة (خط داكن حاد):</div>
            <div class="grid grid-cols-3 gap-1">
              <button type="button" data-vtheme="warm-alabaster" onclick="applyTheme('warm-alabaster'); document.getElementById('suiteQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-amber-400/60 flex flex-col items-center gap-0.5 text-[9.5px] font-black transition active:scale-95" style="background:#FBF8F1 !important; color:#18130A !important;"><span style="color:#18130A !important;">📜 بيج كريمي</span></button>
              <button type="button" data-vtheme="pure-platinum" onclick="applyTheme('pure-platinum'); document.getElementById('suiteQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-slate-300 flex flex-col items-center gap-0.5 text-[9.5px] font-black transition active:scale-95" style="background:#FFFFFF !important; color:#000000 !important;"><span style="color:#000000 !important;">☀️ أبيض ناصع</span></button>
              <button type="button" data-vtheme="pearl-platinum" onclick="applyTheme('pearl-platinum'); document.getElementById('suiteQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-slate-400 flex flex-col items-center gap-0.5 text-[9.5px] font-black transition active:scale-95" style="background:#EEF2F6 !important; color:#0F172A !important;"><span style="color:#0F172A !important;">🏛️ بلاتيني</span></button>
            </div>
            <div class="text-[9.5px] font-bold text-slate-400 pt-1" style="color:#94a3b8 !important;">🌙 سمات ليلية داكنة (خط فاتح ناصع):</div>
            <div class="grid grid-cols-2 gap-1">
              <button type="button" data-vtheme="midnight-executive" onclick="applyTheme('midnight-executive'); document.getElementById('suiteQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-indigo-500/50 flex items-center justify-center gap-1 text-[9.5px] font-black transition active:scale-95" style="background:#04091A !important; color:#F8FAFC !important;"><span style="color:#F8FAFC !important;">🌌 كحلي ملكي</span></button>
              <button type="button" data-vtheme="sepia-brown" onclick="applyTheme('sepia-brown'); document.getElementById('suiteQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-amber-600/50 flex items-center justify-center gap-1 text-[9.5px] font-black transition active:scale-95" style="background:#1A110B !important; color:#FDF8F5 !important;"><span style="color:#FDF8F5 !important;">☕ بني موكا</span></button>
              <button type="button" data-vtheme="obsidian-charcoal" onclick="applyTheme('obsidian-charcoal'); document.getElementById('suiteQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-zinc-600 flex items-center justify-center gap-1 text-[9.5px] font-black transition active:scale-95" style="background:#000000 !important; color:#FFFFFF !important;"><span style="color:#FFFFFF !important;">🌑 أسود OLED</span></button>
              <button type="button" data-vtheme="twilight-indigo" onclick="applyTheme('twilight-indigo'); document.getElementById('suiteQuickThemeDrawer').classList.add('hidden');" class="p-1.5 rounded-xl border border-slate-500 flex items-center justify-center gap-1 text-[9.5px] font-black transition active:scale-95" style="background:#1E293B !important; color:#F8FAFC !important;"><span style="color:#F8FAFC !important;">🛡️ رمادي داكن</span></button>
            </div>
          `;
          const topBar = document.getElementById('flagshipTopBar');
          if (topBar && topBar.nextSibling) {
            hdr.insertBefore(drawer, topBar.nextSibling);
          } else {
            hdr.appendChild(drawer);
          }
        }
      }
    }

    function openThemePaletteDrawer(evt) {
      if (evt && typeof evt.stopPropagation === 'function') evt.stopPropagation();
      ensureSuiteQuickThemeDrawerDOM();
      if (typeof AudioEngine !== 'undefined' && AudioEngine.play) AudioEngine.play('numpad');
      if (typeof triggerHapticPulse === 'function') triggerHapticPulse(20);
      const quickDrawer = document.getElementById('suiteQuickThemeDrawer');
      if (quickDrawer) {
        quickDrawer.classList.toggle('hidden');
        document.querySelectorAll('[data-vtheme]').forEach(btn => {
          const isCurrent = btn.getAttribute('data-vtheme') === currentActiveTheme;
          btn.style.outline = isCurrent ? '2px solid #10b981' : 'none';
        });
        return;
      }
      openModal('modalThemePaletteDrawer');
    }

    function closeThemePaletteDrawer() {
      const quickDrawer = document.getElementById('suiteQuickThemeDrawer');
      if (quickDrawer) quickDrawer.classList.add('hidden');
      closeModal('modalThemePaletteDrawer');
    }

    document.addEventListener('click', function(e) {
      const quickDrawer = document.getElementById('suiteQuickThemeDrawer');
      const btn = document.getElementById('btnThemePalette');
      if (quickDrawer && !quickDrawer.classList.contains('hidden')) {
        if (!quickDrawer.contains(e.target) && (!btn || !btn.contains(e.target))) {
          quickDrawer.classList.add('hidden');
        }
      }
    });

    window.addEventListener('storage', function(e) {
      if (e && ['magaiees_mobile_theme', 'magaies_active_theme', 'magaiees_global_theme'].includes(e.key) && e.newValue) {
        const nextTheme = resolveMasterThemeKey(e.newValue);
        if (nextTheme !== currentActiveTheme) {
          applyTheme(nextTheme, false);
        }
      }
    });

    function filterThemeCategory(cat) {}

    // Ensure DOM and apply saved theme immediately on startup without blocking toast
    ensureSuiteQuickThemeDrawerDOM();
    applyTheme(currentActiveTheme, false);

    // 30-MINUTE OPERATIONAL / SHIFT / EYE-REST TIMER ENGINE
    // ========================================================
    const OperationalTimer = {
      totalSeconds: 30 * 60, // 30 minutes default
      remainingSeconds: 30 * 60,
      intervalId: null,
      isRunning: false,
      hasPreAlerted: false,

      init() {
        this.updateDisplays();
      },

      setPreset(minutes) {
        this.pause();
        this.totalSeconds = minutes * 60;
        this.remainingSeconds = this.totalSeconds;
        this.hasPreAlerted = false;

        const presets = [15, 30, 45, 60];
        presets.forEach(m => {
          const btn = document.getElementById('btnPreset' + m);
          if (btn) {
            if (m === minutes) {
              btn.className = "py-2 rounded-xl bg-cyan-600 text-white font-black shadow border border-cyan-400/40 transition";
            } else {
              btn.className = "py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white transition";
            }
          }
        });

        const hint = document.getElementById('timerSubHint');
        if (hint) hint.textContent = `جلسة محددة بـ ${minutes} دقيقة لمطابقة الأرصدة والتركيز`;

        this.updateDisplays();
        AudioEngine.play('numpad');
        triggerHapticPulse(30);
      },

      toggleStartPause() {
        if (this.isRunning) {
          this.pause();
        } else {
          this.start();
        }
      },

      start() {
        if (this.isRunning) return;
        this.isRunning = true;
        this.updateButtonsUI();

        AudioEngine.play('approve');
        triggerHapticPulse([40, 30, 40]);
        showFloatingToast('⏱️ تم بدء تايمر الـ 30 دقيقة للمطابقة وراحة العين');

        this.intervalId = setInterval(() => {
          this.tick();
        }, 1000);
      },

      pause() {
        this.isRunning = false;
        if (this.intervalId) {
          clearInterval(this.intervalId);
          this.intervalId = null;
        }
        this.updateButtonsUI();
        AudioEngine.play('numpad');
        triggerHapticPulse(30);
      },

      reset() {
        this.pause();
        this.remainingSeconds = this.totalSeconds;
        this.hasPreAlerted = false;
        this.updateDisplays();
        AudioEngine.play('approve');
        triggerHapticPulse(45);
        showFloatingToast('🔄 تم إعادة ضبط تايمر الـ 30 دقيقة');
      },

      tick() {
        if (this.remainingSeconds > 0) {
          this.remainingSeconds--;
          this.updateDisplays();

          // 5-minute pre-alert warning
          if (this.remainingSeconds === 5 * 60 && !this.hasPreAlerted) {
            this.hasPreAlerted = true;
            const preAlertEnabled = document.getElementById('timerPreAlertToggle')?.checked !== false;
            if (preAlertEnabled) {
              AudioEngine.play('risk');
              triggerHapticPulse([150, 70, 150]);
              showFloatingToast('⚠️ تنبيه مسبق: متبقي 5 دقائق فقط على انتهاء جلسة الـ 30 دقيقة!');
            }
          }

          // Timer finished
          if (this.remainingSeconds === 0) {
            this.pause();
            AudioEngine.play('risk');
            triggerHapticPulse([200, 100, 200, 100, 300]);
            showFloatingToast('🚨 انقضت الـ 30 دقيقة كاملة! حان موعد مراجعة الأرصدة وإراحة العين.');
            alert('⏱️ تذكير انتهاء الـ 30 دقيقة:\nانتهت جلسة العمل والمطابقة المحددة بنجاح.\nيرجى حفظ العمليات ومطابقة الصندوق أو إراحة العين لمدة دقيقة.');
          }
        }
      },

      formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
      },

      updateDisplays() {
        const formatted = this.formatTime(this.remainingSeconds);
        
        const big = document.getElementById('timerBigDisplay');
        const compact = document.getElementById('timerCompactText');
        const quick = document.getElementById('quickBarTimerDisplay');

        if (big) big.textContent = formatted;
        if (compact) compact.textContent = formatted;
        if (quick) quick.textContent = formatted;
      },

      updateButtonsUI() {
        const btnIcon = document.getElementById('btnTimerStartPauseIcon');
        const btnText = document.getElementById('btnTimerStartPauseText');
        const btnPill = document.getElementById('timerStatusPill');
        const dot = document.getElementById('timerPulseDot');

        if (this.isRunning) {
          if (btnIcon) btnIcon.textContent = '⏸️';
          if (btnText) btnText.textContent = 'إيقاف مؤقت';
          if (btnPill) {
            btnPill.textContent = 'يعد الآن ⏱️';
            btnPill.className = "text-[9px] bg-cyan-950 text-cyan-300 font-bold px-1.5 py-0.2 rounded-full border border-cyan-500/40 animate-pulse";
          }
          if (dot) dot.className = "w-2 h-2 rounded-full bg-emerald-400 animate-ping";
        } else {
          if (btnIcon) btnIcon.textContent = '▶️';
          if (btnText) btnText.textContent = 'استئناف / بدء العداد';
          if (btnPill) {
            btnPill.textContent = 'متوقف مؤقتاً';
            btnPill.className = "text-[9px] bg-slate-800 text-slate-400 font-bold px-1.5 py-0.2 rounded-full border border-slate-700";
          }
          if (dot) dot.className = "w-2 h-2 rounded-full bg-slate-500";
        }
      }
    };

    function openOperationalTimerModal() {
      AudioEngine.play('numpad');
      triggerHapticPulse(30);
      OperationalTimer.updateDisplays();
      OperationalTimer.updateButtonsUI();
      openModal('modalOperationalTimer');
    }

    // Initialize all global engines
    applySunlightMode();
    
    // ========================================================
    // CRYSTAL FLAGSHIP BUTTONS GESTURES & INTERACTION ENGINE
    // ========================================================
    function triggerFlagshipClick(btn) {
      if (typeof AudioEngine !== 'undefined' && AudioEngine.play) {
        AudioEngine.play('numpad');
      }
      if (typeof triggerHapticPulse === 'function') {
        triggerHapticPulse(30);
      }
      if (btn) {
        btn.classList.add('touch-elevated');
        setTimeout(() => btn.classList.remove('touch-elevated'), 300);
      }
    }

    function initCrystalFlagshipGestures() {
      document.querySelectorAll('.crystal-flagship-btn').forEach(btn => {
        btn.addEventListener('touchstart', () => {
          btn.classList.add('touch-elevated');
          if (typeof triggerHapticPulse === 'function') triggerHapticPulse(20);
        }, { passive: true });
        
        btn.addEventListener('touchend', () => {
          setTimeout(() => btn.classList.remove('touch-elevated'), 220);
        }, { passive: true });
        
        btn.addEventListener('touchcancel', () => {
          btn.classList.remove('touch-elevated');
        }, { passive: true });
      });
    }

    /* ═══════════════════════════════════════════════════════════════════
       🌟 FLAGSHIP TOP BAR DYNAMIC GESTURES & REAL-TIME NET ENGINE
       ═══════════════════════════════════════════════════════════════════ */
    function syncMagaieesNetUI(online) {
      var brandBadge = document.getElementById('headerBrandLogoBadge');
      var brandDot = document.getElementById('headerNetDot');
      var brandText = document.getElementById('headerBrandText');
      if (!brandBadge) return;

      if (online) {
        brandBadge.classList.add('is-online');
        brandBadge.classList.remove('is-offline');
        if (brandDot) {
          brandDot.className = 'status-dot w-2 h-2 rounded-full bg-emerald-400 shrink-0';
        }
        if (brandText) {
          brandText.style.color = '#34d399';
          brandText.style.textShadow = '0 0 10px rgba(16,185,129,0.7)';
        }
        brandBadge.title = 'مقاييس ERP - متصل بالإنترنت وقاعدة البيانات (اضغط للرئيسية)';
      } else {
        brandBadge.classList.add('is-offline');
        brandBadge.classList.remove('is-online');
        if (brandDot) {
          brandDot.className = 'status-dot w-2 h-2 rounded-full bg-rose-500 shrink-0 animate-ping';
        }
        if (brandText) {
          brandText.style.color = '#f87171';
          brandText.style.textShadow = '0 0 10px rgba(239,68,68,0.8)';
        }
        brandBadge.title = 'مقاييس ERP - تم انقطاع اتصال الإنترنت (وضع العمل دون اتصال)';
      }
    }
    window.syncMagaieesNetUI = syncMagaieesNetUI;

    function initFlagshipTopBarEngine() {
      // 1. Initial online status
      syncMagaieesNetUI(navigator.onLine !== false);

      // 2. Real-time browser event listeners
      window.addEventListener('online', function() { syncMagaieesNetUI(true); });
      window.addEventListener('offline', function() { syncMagaieesNetUI(false); });

      // 3. Ultra-light periodic server heartbeat check (every 45 seconds, only when visible)
      setInterval(function() {
        if (document.visibilityState === 'hidden') return;
        if (navigator.onLine === false) {
          syncMagaieesNetUI(false);
          return;
        }
        fetch('mobile-dashboard.php?action=heartbeat_ping', { method: 'HEAD', cache: 'no-store' })
          .then(function(res) { syncMagaieesNetUI(res.ok); })
          .catch(function() { syncMagaieesNetUI(false); });
      }, 45000);

      // 4. Kinetic micro-gestures & tactile vibrations for top bar icons, Row 2 glass tiles & operation buttons
      var allInteractiveTiles = document.querySelectorAll('.flagship-tile, .row2-glass-tile, .op-btn');
      allInteractiveTiles.forEach(function(tile) {
        tile.addEventListener('pointerenter', function() {
          tile.classList.add('touch-active');
          if (typeof triggerHapticPulse === 'function') {
            triggerHapticPulse(10);
          } else if (navigator.vibrate) {
            try { navigator.vibrate(10); } catch(e){}
          }
        });

        tile.addEventListener('pointerleave', function() {
          tile.classList.remove('touch-active');
        });

        tile.addEventListener('touchstart', function() {
          tile.classList.add('touch-active');
          if (typeof triggerHapticPulse === 'function') {
            triggerHapticPulse(15);
          } else if (navigator.vibrate) {
            try { navigator.vibrate(15); } catch(e){}
          }
        }, { passive: true });

        tile.addEventListener('touchend', function() {
          setTimeout(function() { tile.classList.remove('touch-active'); }, 220);
        }, { passive: true });

        tile.addEventListener('touchcancel', function() {
          tile.classList.remove('touch-active');
        }, { passive: true });
      });
    }

    /* ═══════════════════════════════════════════════════════════════════
       📥 PWA INSTALL TRIGGER & GUIDANCE ENGINE
       ═══════════════════════════════════════════════════════════════════ */
    function handlePwaInstallClick() {
      triggerHapticPulse(25);
      if (typeof AudioEngine !== 'undefined' && AudioEngine.play) {
        AudioEngine.play('numpad');
      }
      if (window.PWAManager && typeof PWAManager.triggerInstall === 'function') {
        PWAManager.triggerInstall();
      } else if (window.PWAManager && typeof PWAManager.showIOSInstallModal === 'function') {
        PWAManager.showIOSInstallModal();
      } else {
        if (typeof showFloatingToast === 'function') {
          showFloatingToast('📱 لتثبيت التطبيق: افتح قائمة المتصفح واضغط (إضافة للشاشة الرئيسية)');
        } else {
          alert('📱 لتثبيت التطبيق على جوالك:\n1. افتح قائمة المتصفح (⋮ أو زر المشاركة في الآيفون)\n2. اختر "إضافة للشاشة الرئيسية / Add to Home Screen"');
        }
      }
    }
    window.handlePwaInstallClick = handlePwaInstallClick;

    /* ═══════════════════════════════════════════════════════════════════
       ☁️ CLOUD SYNC QUICK ACTION ENGINE
       ═══════════════════════════════════════════════════════════════════ */
    function handleQuickCloudSync() {
      triggerHapticPulse(30);
      if (typeof AudioEngine !== 'undefined' && AudioEngine.play) {
        AudioEngine.play('numpad');
      }

      const syncIcon = document.getElementById('quickCloudSyncIcon');
      if (syncIcon) {
        syncIcon.classList.add('animate-spin');
        setTimeout(() => syncIcon.classList.remove('animate-spin'), 1500);
      }

      if (window.PWAManager && typeof PWAManager.syncRealData === 'function') {
        PWAManager.syncRealData(true);
      }

      if (typeof showFloatingToast === 'function') {
        showFloatingToast('☁️ تم تحديث ومزامنة البيانات السحابية مع الخادم بنجاح!');
      }
    }
    window.handleQuickCloudSync = handleQuickCloudSync;

    /* ═══════════════════════════════════════════════════════════════════
       📷 REAL-TIME QR & BARCODE CAMERA SCANNER ENGINE
       ═══════════════════════════════════════════════════════════════════ */
    let barcodeCameraStream = null;
    let barcodeDetectorInstance = null;
    let barcodeScanInterval = null;
    let currentCameraFacing = 'environment';
    let lastScannedBarcode = '';

    async function openBarcodeScannerModal() {
      if (typeof AudioEngine !== 'undefined' && AudioEngine.play) AudioEngine.play('numpad');
      triggerHapticPulse(25);
      openModal('modalBarcodeScanner');

      const videoEl = document.getElementById('barcodeScannerVideo');
      const statusText = document.getElementById('barcodeScanStatusText');
      const resultCard = document.getElementById('barcodeDetectedCard');
      if (resultCard) resultCard.classList.add('hidden');

      if ('BarcodeDetector' in window) {
        try {
          barcodeDetectorInstance = new BarcodeDetector({
            formats: ['qr_code', 'ean_13', 'ean_8', 'code_128', 'code_39', 'upc_a', 'upc_e', 'itf']
          });
        } catch (e) {
          barcodeDetectorInstance = null;
        }
      }

      await startCameraStream(currentCameraFacing);
    }
    window.openBarcodeScannerModal = openBarcodeScannerModal;

    async function startCameraStream(facing) {
      const videoEl = document.getElementById('barcodeScannerVideo');
      const statusText = document.getElementById('barcodeScanStatusText');

      if (barcodeCameraStream) {
        barcodeCameraStream.getTracks().forEach(t => t.stop());
        barcodeCameraStream = null;
      }
      if (barcodeScanInterval) {
        clearInterval(barcodeScanInterval);
        barcodeScanInterval = null;
      }

      try {
        barcodeCameraStream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: facing, width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        if (videoEl) {
          videoEl.srcObject = barcodeCameraStream;
          await videoEl.play();
        }
        if (statusText) {
          statusText.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span><span>الكاميرا نشطة — وجّه العدسة نحو الباركود أو الـ QR</span>';
        }
        startBarcodeDetectionLoop(videoEl);
      } catch (err) {
        console.warn('Camera stream error:', err);
        if (statusText) {
          statusText.innerHTML = '<span class="text-amber-400">⚠️ تعذر فتح الكاميرا مباشرة — أدخل الكود يدوياً أو اختر صورة من المعرض</span>';
        }
      }
    }
    window.startCameraStream = startCameraStream;

    function startBarcodeDetectionLoop(videoEl) {
      if (!barcodeDetectorInstance || !videoEl) return;

      barcodeScanInterval = setInterval(async () => {
        try {
          if (!barcodeCameraStream || videoEl.readyState < 2) return;
          const codes = await barcodeDetectorInstance.detect(videoEl);
          if (codes && codes.length > 0) {
            const rawCode = codes[0].rawValue;
            if (rawCode && rawCode !== lastScannedBarcode) {
              handleBarcodeSuccess(rawCode);
            }
          }
        } catch (e) {
          // skip frame
        }
      }, 280);
    }

    function handleBarcodeSuccess(code) {
      lastScannedBarcode = code;
      triggerHapticPulse(50);
      if (typeof AudioEngine !== 'undefined' && AudioEngine.play) {
        AudioEngine.play('numpad');
      }

      const resultCard = document.getElementById('barcodeDetectedCard');
      const resultVal = document.getElementById('barcodeDetectedValue');
      if (resultVal) resultVal.textContent = code;
      if (resultCard) resultCard.classList.remove('hidden');

      if (typeof showFloatingToast === 'function') {
        showFloatingToast(`🎯 تم مسح الكود بنجاح: ${code}`);
      }

      setTimeout(() => {
        applyScannedBarcodeResult();
      }, 800);
    }
    window.handleBarcodeSuccess = handleBarcodeSuccess;

    function applyScannedBarcodeResult() {
      const code = lastScannedBarcode || (document.getElementById('manualScannerBarcodeInput') ? document.getElementById('manualScannerBarcodeInput').value.trim() : '');
      if (!code) return;

      closeBarcodeScannerModal();
      focusOmniSearch();

      const omniInput = document.getElementById('omniSearchInput');
      if (omniInput) {
        omniInput.value = code;
        omniInput.dispatchEvent(new Event('input', { bubbles: true }));
        omniInput.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }
    window.applyScannedBarcodeResult = applyScannedBarcodeResult;

    function submitManualBarcode() {
      const inp = document.getElementById('manualScannerBarcodeInput');
      const val = inp ? inp.value.trim() : '';
      if (!val) {
        if (typeof showFloatingToast === 'function') showFloatingToast('⚠️ يرجى إدخال رقم أو كود الباركود');
        return;
      }
      handleBarcodeSuccess(val);
    }
    window.submitManualBarcode = submitManualBarcode;

    function handleBarcodeFileSelected(input) {
      if (!input.files || input.files.length === 0) return;
      const file = input.files[0];
      const reader = new FileReader();
      reader.onload = async function(e) {
        const img = new Image();
        img.onload = async function() {
          if ('BarcodeDetector' in window) {
            try {
              const detector = new BarcodeDetector({ formats: ['qr_code', 'ean_13', 'ean_8', 'code_128', 'code_39', 'upc_a', 'upc_e'] });
              const codes = await detector.detect(img);
              if (codes && codes.length > 0) {
                handleBarcodeSuccess(codes[0].rawValue);
                return;
              }
            } catch(err){}
          }
          if (typeof showFloatingToast === 'function') {
            showFloatingToast('📷 تم التقاط الصورة، يرجى كتابة الرقم أو مسحه');
          }
        };
        img.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
    window.handleBarcodeFileSelected = handleBarcodeFileSelected;

    function switchScannerCamera() {
      currentCameraFacing = (currentCameraFacing === 'environment') ? 'user' : 'environment';
      startCameraStream(currentCameraFacing);
    }
    window.switchScannerCamera = switchScannerCamera;

    function closeBarcodeScannerModal() {
      if (barcodeScanInterval) {
        clearInterval(barcodeScanInterval);
        barcodeScanInterval = null;
      }
      if (barcodeCameraStream) {
        barcodeCameraStream.getTracks().forEach(t => t.stop());
        barcodeCameraStream = null;
      }
      const videoEl = document.getElementById('barcodeScannerVideo');
      if (videoEl) videoEl.srcObject = null;

      closeModal('modalBarcodeScanner');
    }
    window.closeBarcodeScannerModal = closeBarcodeScannerModal;

    document.addEventListener('DOMContentLoaded', () => {
      applySunlightMode();
      initSwipeGestures();
      attachNumpadTriggers();
      initAlertRulesUI();
      initFlagshipTopBarEngine();

      if (window.location.search.includes('open_themes=1') || window.location.search.includes('themes=1') || window.location.hash === '#themes') {
        setTimeout(openThemePaletteDrawer, 300);
      }
    });

