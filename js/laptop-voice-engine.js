/**
 * 🎙️ نظام المايك الصوتي الذكي المتطور لشاشات اللابتوب والديسكتوب
 * Magaiees Smart Voice Recognition Engine for Laptop / Desktop Edition
 */
(function() {
  let recognition = null;
  let activeField = null;
  let isListening = false;

  // رنين مالي فخم عند التقاط الصوت
  function playAudioChime() {
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
      osc.frequency.exponentialRampToValueAtTime(880.00, ctx.currentTime + 0.12); // A5
      gain.gain.setValueAtTime(0.2, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + 0.35);
    } catch(e) {}
  }

  function showVoiceToast(msg, isSuccess = true) {
    if (typeof showToast === 'function') {
      showToast(msg, isSuccess ? 'success' : 'info');
      return;
    }
    const container = document.getElementById('toast-container') || document.body;
    const toast = document.createElement('div');
    toast.style.cssText = `position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: ${isSuccess ? 'rgba(16, 185, 129, 0.95)' : 'rgba(239, 68, 68, 0.95)'}; color: #ffffff; padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 1.1rem; z-index: 999999; box-shadow: 0 10px 25px rgba(0,0,0,0.5); display: flex; align-items: center; gap: 8px; font-family: 'Tajawal', sans-serif;`;
    toast.innerHTML = `<span>🎙️</span> <span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.4s';
      setTimeout(() => toast.remove(), 400);
    }, 3000);
  }

  function normalizeArabic(text) {
    if (!text) return '';
    return String(text).trim()
      .replace(/[\u064B-\u065F\u0670\u0640]/g, '')
      .replace(/[إأآٱ]/g, 'ا')
      .replace(/ة/g, 'ه')
      .replace(/ى/g, 'ي')
      .replace(/[\s\-_]+/g, ' ')
      .toLowerCase();
  }

  // تحويل الأرقام العربية المنطوقة إلى رقمية
  function parseSpokenNumber(text) {
    if (!text) return 0;
    const clean = text.replace(/[^0-9.]/g, '');
    if (clean && !isNaN(clean)) return parseFloat(clean);

    const norm = normalizeArabic(text);
    const nums = {
      'واحد': 1, 'اثنين': 2, 'اتنين': 2, 'ثلاثه': 3, 'تلاته': 3, 'اربعه': 4, 'خمسه': 5,
      'سته': 6, 'سبعه': 7, 'ثمانيه': 8, 'تمانيه': 8, 'تسعه': 9, 'عشره': 10,
      'عشرين': 20, 'ثلاثين': 30, 'تلاتين': 30, 'اربعين': 40, 'خمسين': 50,
      'ستين': 60, 'سبعين': 70, 'ثمانين': 80, 'تمانين': 80, 'تسعين': 90,
      'ميه': 100, 'مائه': 100, 'ميتين': 200, 'مائتين': 200, 'خمسميه': 500,
      'الف': 1000, 'الفين': 2000, 'مليون': 1000000
    };

    let total = 0;
    const words = norm.split(' ');
    for (let w of words) {
      if (nums[w]) total += nums[w];
    }
    return total > 0 ? total : 0;
  }

  function setButtonListening(btn, listening) {
    if (!btn) return;
    if (listening) {
      btn.classList.add('voice-listening-pulse');
      btn.style.boxShadow = '0 0 20px rgba(239, 68, 68, 0.9)';
      btn.style.borderColor = '#ef4444';
      const ping = btn.querySelector('.pulse-dot');
      if (ping) ping.style.display = 'inline-block';
    } else {
      btn.classList.remove('voice-listening-pulse');
      btn.style.boxShadow = '';
      btn.style.borderColor = '';
      const ping = btn.querySelector('.pulse-dot');
      if (ping) ping.style.display = 'none';
    }
  }

  window.toggleLaptopMasterVoice = function() {
    if (isListening) {
      stopVoice();
      return;
    }
    startLaptopVoice('master');
  };

  window.startLaptopVoiceField = function(fieldName) {
    if (isListening && activeField === fieldName) {
      stopVoice();
      return;
    }
    startLaptopVoice(fieldName);
  };

  function startLaptopVoice(field) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const masterBtn = document.getElementById('laptopMasterMicBtn');
    const fieldBtn = document.getElementById('mic-btn-' + field);

    if (!SpeechRecognition) {
      const val = prompt('🎙️ المايك الصوتي الذكي:\nتحدث أو اكتب القيمة المطلوبة:\n(يمكنك استخدام مايك لوحة المفاتيح بالنقر عليه)', '');
      if (val && val.trim()) {
        applyVoiceResult(field, val.trim());
      }
      return;
    }

    try {
      if (recognition) {
        recognition.abort();
      }
      recognition = new SpeechRecognition();
      recognition.lang = 'ar-SD'; // العربية - اللهجة السودانية والمصرفية
      recognition.continuous = false;
      recognition.interimResults = false;

      activeField = field;
      isListening = true;
      playAudioChime();

      if (field === 'master') {
        setButtonListening(masterBtn, true);
        showVoiceToast('🎙️ يستمع إليك... تحدث بالبيانات (مثال: من حساب ... إلى حساب ... مبلغ ... تحويل)');
      } else {
        setButtonListening(fieldBtn, true);
        showVoiceToast(`🎙️ تحدث الآن لإدخال: ${getFieldLabel(field)}`);
      }

      recognition.onresult = function(event) {
        const text = event.results[0][0].transcript;
        if (text) {
          applyVoiceResult(activeField, text.trim());
        }
      };

      recognition.onerror = function(event) {
        console.warn('Voice error:', event.error);
        if (event.error === 'not-allowed') {
          showVoiceToast('⚠️ يرجى السماح بصلاحية المايكروفون في المتصفح', false);
        } else if (event.error !== 'aborted') {
          showVoiceToast('لم يتم التقاط الصوت بوضوح، حاول مجدداً', false);
        }
      };

      recognition.onend = function() {
        isListening = false;
        setButtonListening(masterBtn, false);
        setButtonListening(fieldBtn, false);
      };

      recognition.start();
    } catch(e) {
      console.error('Recognition error:', e);
      isListening = false;
      setButtonListening(masterBtn, false);
      setButtonListening(fieldBtn, false);
    }
  }

  function stopVoice() {
    if (recognition) {
      try { recognition.abort(); } catch(e) {}
    }
    isListening = false;
    const masterBtn = document.getElementById('laptopMasterMicBtn');
    setButtonListening(masterBtn, false);
    document.querySelectorAll('.laptop-field-mic').forEach(b => setButtonListening(b, false));
  }

  function getFieldLabel(f) {
    const labels = {
      'from': 'من حساب',
      'to': 'إلى حساب',
      'amount': 'المبلغ',
      'transfer': 'رقم التحويلة',
      'statement': 'البيان'
    };
    return labels[f] || f;
  }

  function applyVoiceResult(field, spokenText) {
    playAudioChime();
    const norm = normalizeArabic(spokenText);

    if (field === 'amount') {
      const amt = parseSpokenNumber(spokenText);
      const inp = document.getElementById('amount-local');
      if (inp && amt > 0) {
        inp.value = amt.toLocaleString('en-US');
        if (typeof calcEquiv === 'function') calcEquiv('local');
        showVoiceToast(`✅ تم إدخال المبلغ: ${amt.toLocaleString()} ج.س`);
      } else {
        showVoiceToast(`⚠️ لم يتم التعرف على رقم دقيق من: "${spokenText}"`, false);
      }
      return;
    }

    if (field === 'from') {
      selectAccountByName('from', spokenText);
      return;
    }

    if (field === 'to') {
      selectAccountByName('to', spokenText);
      return;
    }

    if (field === 'transfer') {
      const cleanDigits = spokenText.replace(/\D/g, '');
      const inp = document.getElementById('transfer-no');
      if (inp) {
        inp.value = cleanDigits || spokenText;
        inp.focus();
        showVoiceToast(`✅ تم إدخال رقم التحويلة: ${inp.value}`);
      }
      return;
    }

    if (field === 'statement') {
      const inp = document.getElementById('to-statement');
      if (inp) {
        inp.value = spokenText;
        showVoiceToast(`✅ البيان: ${spokenText}`);
      }
      return;
    }

    // Comprehensive Master Voice
    if (field === 'master') {
      parseMasterSentence(spokenText);
    }
  }

  function selectAccountByName(type, query) {
    const searchInp = document.getElementById(type + '-account-search');
    const hiddenInp = document.getElementById(type + '-account');
    if (!searchInp) return;

    searchInp.value = query;
    if (typeof showAccountDropdown === 'function') showAccountDropdown(type);
    if (typeof filterAccountDropdown === 'function') filterAccountDropdown(type);

    const list = document.getElementById(type + '-account-dropdown-list');
    if (list) {
      const firstItem = list.querySelector('.custom-dropdown-item:not([style*="display: none"])');
      if (firstItem) {
        firstItem.click();
        showVoiceToast(`✅ تم اختيار ${type === 'from' ? 'حساب الصرف' : 'حساب الاستلام'}: ${firstItem.innerText.split('\n')[0]}`);
        return;
      }
    }
    showVoiceToast(`🔍 تم البحث عن: "${query}"`);
  }

  function parseMasterSentence(text) {
    const norm = normalizeArabic(text);

    // 1. نوع الحركة
    if (norm.includes('كاش') || norm.includes('نقدي')) {
      const r = document.getElementById('radio-cash');
      if (r) { r.checked = true; r.dispatchEvent(new Event('change')); }
    } else if (norm.includes('شيك')) {
      const r = document.getElementById('radio-check');
      if (r) { r.checked = true; r.dispatchEvent(new Event('change')); }
    } else if (norm.includes('تحويل') || norm.includes('تحويلة') || norm.includes('بنك')) {
      const r = document.getElementById('radio-mobile');
      if (r) { r.checked = true; r.dispatchEvent(new Event('change')); }
    }

    // 2. المبلغ
    const amt = parseSpokenNumber(text);
    if (amt > 0) {
      const amtInp = document.getElementById('amount-local');
      if (amtInp) {
        amtInp.value = amt.toLocaleString('en-US');
        if (typeof calcEquiv === 'function') calcEquiv('local');
      }
    }

    // 3. من حساب وإلى حساب
    const fromMatch = text.match(/(?:من حساب|من)\s+([^\s]+(?:\s+[^\s]+){0,3})(?=\s+(?:الى|إلى|مبلغ|رقم|تحويل|$))/i);
    if (fromMatch && fromMatch[1]) {
      selectAccountByName('from', fromMatch[1].trim());
    }

    const toMatch = text.match(/(?:الى حساب|إلى حساب|الى|إلى)\s+([^\s]+(?:\s+[^\s]+){0,3})(?=\s+(?:مبلغ|رقم|تحويل|$))/i);
    if (toMatch && toMatch[1]) {
      selectAccountByName('to', toMatch[1].trim());
    }

    // 4. رقم التحويلة
    const refMatch = text.match(/(?:رقم التحويله|رقم التحويلة|تحويلة رقم|تحويله رقم|رقم)\s+(\d+)/i);
    if (refMatch && refMatch[1]) {
      const ti = document.getElementById('transfer-no');
      if (ti) ti.value = refMatch[1];
    }

    // 5. حفظ
    if (norm.includes('حفظ') || norm.includes('احفظ')) {
      setTimeout(() => {
        const btnSave = document.getElementById('btn-save');
        if (btnSave) btnSave.click();
      }, 1000);
    }

    showVoiceToast('✅ تم معالجة وتطبيق الصوت بنجاح!');
  }

  // إضافة أنماط الوميض الصوتي
  const style = document.createElement('style');
  style.textContent = `
    @keyframes voicePulse {
      0%, 100% { transform: scale(1); filter: drop-shadow(0 0 4px #f59e0b); }
      50% { transform: scale(1.12); filter: drop-shadow(0 0 16px #ef4444); }
    }
    .voice-listening-pulse {
      animation: voicePulse 1s infinite !important;
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.4), rgba(220, 38, 38, 0.6)) !important;
    }
    .laptop-field-mic {
      transition: all 0.2s ease;
      cursor: pointer;
    }
    .laptop-field-mic:hover {
      transform: scale(1.1);
      filter: drop-shadow(0 0 8px #facc15);
    }
  `;
  document.head.appendChild(style);

  console.log('🎙️ Laptop Voice Engine initialized');
})();
