    let activeScholarFilter = 'all';
    let activeTopicFilter = 'all';

    // 🚀 AUTO-ACTIVATE TAB FROM URL HASH OR PARAMETER 🚀
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('suite') || urlParams.get('tab') || window.location.hash.replace('#', '');
        const subParam = urlParams.get('sub');
        const qParam = urlParams.get('q');
        
        if (tabParam) {
            const targetPill = document.querySelector('[onclick*="switchSuite(\'' + tabParam + '\'"]');
            switchSuite(tabParam, targetPill || null);
        }
        if (tabParam === 'fiqh' && subParam) {
            switchFiqhSubTab(subParam);
            if (subParam === 'fatwas' && qParam) {
                setTimeout(() => {
                    const input = document.getElementById('fiqh-search-input');
                    if (input) {
                        input.value = qParam;
                        if (typeof executeLiveFilter === 'function') executeLiveFilter();
                    }
                }, 300);
            }
        }
        setTimeout(() => {
            if (typeof initQuranStudio === 'function') {
                initQuranStudio();
            }
        }, 150);
    });


    // ════════════════════════════════════════════════════════════════════════════
    // 🏦 REAL-TIME AUTOMATED SUDANESE BBAN & IBAN GENERATOR 🏦
    // ════════════════════════════════════════════════════════════════════════════
    function generateUserBBAN() {
        const bankSelect = document.getElementById('bban_bank_selector');
        const accInput = document.getElementById('bban_raw_account');
        const displaySpan = document.getElementById('generated_bban_display');
        const lenBadge = document.getElementById('bban_len_counter');
        const stripFeedback = document.getElementById('bban_strip_feedback');

        if (!bankSelect || !accInput || !displaySpan) return;

        let bankPrefix = bankSelect.value.trim() || '170';
        if (bankPrefix.includes('|')) bankPrefix = bankPrefix.split('|')[0];
        
        let rawAcc = accInput.value.replace(/\D/g, '');
        let cleanAcc = rawAcc.replace(/^0+/, '');

        // Auto-clean leading zero from input box
        if (rawAcc !== cleanAcc && accInput.value.startsWith('0')) {
            accInput.value = cleanAcc;
            if (stripFeedback) {
                stripFeedback.textContent = '✓ تم حذف الصفر الأول تلقائياً بنجاح!';
                stripFeedback.className = 'text-emerald-400 font-bold';
            }
        }

        const branchCode = '00001';

        if (!cleanAcc) {
            displaySpan.innerHTML = `<span class="text-amber-400 font-mono">${bankPrefix}</span><span class="text-slate-600 font-mono">-------</span><span class="text-emerald-400 font-mono">${branchCode}</span>`;
            if (lenBadge) {
                lenBadge.innerHTML = `عدد الخانات: <span class="text-amber-300 font-black">اكتب رقم الحساب لتوليد الآيبان</span>`;
            }
            return;
        }

        // Format to at least 6 digits
        let formattedAcc = cleanAcc;
        while (formattedAcc.length < 6) {
            formattedAcc = '0' + formattedAcc;
        }

        const fullBBAN = bankPrefix + formattedAcc + branchCode;

        // Visual distinction: Bank Prefix (Gold) + Account (Cyan) + Branch (Emerald)
        displaySpan.innerHTML = `<span class="text-amber-400 font-black font-mono">${bankPrefix}</span><span class="text-cyan-300 font-black font-mono tracking-widest">${formattedAcc}</span><span class="text-emerald-400 font-black font-mono">${branchCode}</span>`;
        
        if (lenBadge) {
            lenBadge.innerHTML = `عدد الخانات: <strong class="text-emerald-400 font-mono font-black">${fullBBAN.length} رقماً (مكتمل وجاهز للتحويل ✓)</strong>`;
        }
    }

    function copyGeneratedBBAN() {
        const accInput = document.getElementById('bban_raw_account');
        if (!accInput || !accInput.value.trim()) {
            alert('الرجاء كتابة رقم الحساب أولاً ليتم توليد ونسخ الآيبان.');
            if (accInput) accInput.focus();
            return;
        }

        const displaySpan = document.getElementById('generated_bban_display');
        const fullBBAN = displaySpan ? displaySpan.textContent.replace(/\s+/g, '') : '';
        
        if (!fullBBAN || fullBBAN.includes('-')) {
            alert('الرجاء التأكد من كتابة رقم الحساب.');
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(fullBBAN).then(() => {
                alert('✓ تم نسخ رقم الـ BBAN بنجاح:\n' + fullBBAN + '\n(جاهز للصق في تطبيق البنك للتحويل الفوري).');
            }).catch(() => {
                fallbackCopyText(fullBBAN);
            });
        } else {
            fallbackCopyText(fullBBAN);
        }
    }

    function fallbackCopyText(text) {
        const temp = document.createElement('textarea');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        alert('✓ تم نسخ رقم الـ BBAN بنجاح:\n' + text);
    }

    function searchBanks(query) {
        const q = (query || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.bban-bank-card');
        cards.forEach(card => {
            const text = (card.textContent || '').toLowerCase();
            if (!q || text.includes(q)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function quickSelectBank(code) {
        const sel = document.getElementById('bban_bank_selector');
        if (sel) {
            sel.value = code;
            generateUserBBAN();
            sel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

        
    
    // ════════════════════════════════════════════════════════════════════════════
    // 📖 114 QURAN SURAHS & RECITERS INTERACTIVE STUDIO ENGINE 📖
    // ════════════════════════════════════════════════════════════════════════════
    const QURAN_SURAHS = [
        { num: 1, name: "الفاتحة", verses: 7, type: "مكية" },
        { num: 2, name: "البقرة", verses: 286, type: "مدنية" },
        { num: 3, name: "آل عمران", verses: 200, type: "مدنية" },
        { num: 4, name: "النساء", verses: 176, type: "مدنية" },
        { num: 5, name: "المائدة", verses: 120, type: "مدنية" },
        { num: 6, name: "الأنعام", verses: 165, type: "مكية" },
        { num: 7, name: "الأعراف", verses: 206, type: "مكية" },
        { num: 8, name: "الأنفال", verses: 75, type: "مدنية" },
        { num: 9, name: "التوبة", verses: 129, type: "مدنية" },
        { num: 10, name: "يونس", verses: 109, type: "مكية" },
        { num: 11, name: "هود", verses: 123, type: "مكية" },
        { num: 12, name: "يوسف", verses: 111, type: "مكية" },
        { num: 13, name: "الرعد", verses: 43, type: "مدنية" },
        { num: 14, name: "إبراهيم", verses: 52, type: "مكية" },
        { num: 15, name: "الحجر", verses: 99, type: "مكية" },
        { num: 16, name: "النحل", verses: 128, type: "مكية" },
        { num: 17, name: "الإسراء", verses: 111, type: "مكية" },
        { num: 18, name: "الكهف", verses: 110, type: "مكية" },
        { num: 19, name: "مريم", verses: 98, type: "مكية" },
        { num: 20, name: "طه", verses: 135, type: "مكية" },
        { num: 21, name: "الأنبياء", verses: 112, type: "مكية" },
        { num: 22, name: "الحج", verses: 78, type: "مدنية" },
        { num: 23, name: "المؤمنون", verses: 118, type: "مكية" },
        { num: 24, name: "النور", verses: 64, type: "مدنية" },
        { num: 25, name: "الفرقان", verses: 77, type: "مكية" },
        { num: 26, name: "الشعراء", verses: 227, type: "مكية" },
        { num: 27, name: "النمل", verses: 93, type: "مكية" },
        { num: 28, name: "القصص", verses: 88, type: "مكية" },
        { num: 29, name: "العنكبوت", verses: 69, type: "مكية" },
        { num: 30, name: "الروم", verses: 60, type: "مكية" },
        { num: 31, name: "لقمان", verses: 34, type: "مكية" },
        { num: 32, name: "السجدة", verses: 30, type: "مكية" },
        { num: 33, name: "الأحزاب", verses: 73, type: "مدنية" },
        { num: 34, name: "سبأ", verses: 54, type: "مكية" },
        { num: 35, name: "فاطر", verses: 45, type: "مكية" },
        { num: 36, name: "يس", verses: 83, type: "مكية" },
        { num: 37, name: "الصافات", verses: 182, type: "مكية" },
        { num: 38, name: "ص", verses: 88, type: "مكية" },
        { num: 39, name: "الزمر", verses: 75, type: "مكية" },
        { num: 40, name: "غافر", verses: 85, type: "مكية" },
        { num: 41, name: "فصلت", verses: 54, type: "مكية" },
        { num: 42, name: "الشورى", verses: 53, type: "مكية" },
        { num: 43, name: "الزخرف", verses: 89, type: "مكية" },
        { num: 44, name: "الدخان", verses: 59, type: "مكية" },
        { num: 45, name: "الجاثية", verses: 37, type: "مكية" },
        { num: 46, name: "الأحقاف", verses: 35, type: "مكية" },
        { num: 47, name: "محمد", verses: 38, type: "مدنية" },
        { num: 48, name: "الفتح", verses: 29, type: "مدنية" },
        { num: 49, name: "الحجرات", verses: 18, type: "مدنية" },
        { num: 50, name: "ق", verses: 45, type: "مكية" },
        { num: 51, name: "الذاريات", verses: 60, type: "مكية" },
        { num: 52, name: "الطور", verses: 49, type: "مكية" },
        { num: 53, name: "النجم", verses: 62, type: "مكية" },
        { num: 54, name: "القمر", verses: 55, type: "مكية" },
        { num: 55, name: "الرحمن", verses: 78, type: "مدنية" },
        { num: 56, name: "الواقعة", verses: 96, type: "مكية" },
        { num: 57, name: "الحديد", verses: 29, type: "مدنية" },
        { num: 58, name: "المجادلة", verses: 22, type: "مدنية" },
        { num: 59, name: "الحشر", verses: 24, type: "مدنية" },
        { num: 60, name: "الممتحنة", verses: 13, type: "مدنية" },
        { num: 61, name: "الصف", verses: 14, type: "مدنية" },
        { num: 62, name: "الجمعة", verses: 11, type: "مدنية" },
        { num: 63, name: "المنافقون", verses: 11, type: "مدنية" },
        { num: 64, name: "التغابن", verses: 18, type: "مدنية" },
        { num: 65, name: "الطلاق", verses: 12, type: "مدنية" },
        { num: 66, name: "التحريم", verses: 12, type: "مدنية" },
        { num: 67, name: "الملك", verses: 30, type: "مكية" },
        { num: 68, name: "القلم", verses: 52, type: "مكية" },
        { num: 69, name: "الحاقة", verses: 52, type: "مكية" },
        { num: 70, name: "المعارج", verses: 44, type: "مكية" },
        { num: 71, name: "نوح", verses: 28, type: "مكية" },
        { num: 72, name: "الجن", verses: 28, type: "مكية" },
        { num: 73, name: "المزمل", verses: 20, type: "مكية" },
        { num: 74, name: "المدثر", verses: 56, type: "مكية" },
        { num: 75, name: "القيامة", verses: 40, type: "مكية" },
        { num: 76, name: "الإنسان", verses: 31, type: "مدنية" },
        { num: 77, name: "المرسلات", verses: 50, type: "مكية" },
        { num: 78, name: "النبأ", verses: 40, type: "مكية" },
        { num: 79, name: "النازعات", verses: 46, type: "مكية" },
        { num: 80, name: "عبس", verses: 42, type: "مكية" },
        { num: 81, name: "التكوير", verses: 29, type: "مكية" },
        { num: 82, name: "الانفطار", verses: 19, type: "مكية" },
        { num: 83, name: "المطففين", verses: 36, type: "مكية" },
        { num: 84, name: "الانشقاق", verses: 25, type: "مكية" },
        { num: 85, name: "البروج", verses: 22, type: "مكية" },
        { num: 86, name: "الطارق", verses: 17, type: "مكية" },
        { num: 87, name: "الأعلى", verses: 19, type: "مكية" },
        { num: 88, name: "الغاشية", verses: 26, type: "مكية" },
        { num: 89, name: "الفجر", verses: 30, type: "مكية" },
        { num: 90, name: "البلد", verses: 20, type: "مكية" },
        { num: 91, name: "الشمس", verses: 15, type: "مكية" },
        { num: 92, name: "الليل", verses: 21, type: "مكية" },
        { num: 93, name: "الضحى", verses: 11, type: "مكية" },
        { num: 94, name: "الشرح", verses: 8, type: "مكية" },
        { num: 95, name: "التين", verses: 8, type: "مكية" },
        { num: 96, name: "العلق", verses: 19, type: "مكية" },
        { num: 97, name: "القدر", verses: 5, type: "مكية" },
        { num: 98, name: "البينة", verses: 8, type: "مدنية" },
        { num: 99, name: "الزلزلة", verses: 8, type: "مدنية" },
        { num: 100, name: "العاديات", verses: 11, type: "مكية" },
        { num: 101, name: "القارعة", verses: 11, type: "مكية" },
        { num: 102, name: "التكاثر", verses: 8, type: "مكية" },
        { num: 103, name: "العصر", verses: 3, type: "مكية" },
        { num: 104, name: "الهمزة", verses: 9, type: "مكية" },
        { num: 105, name: "الفيل", verses: 5, type: "مكية" },
        { num: 106, name: "قريش", verses: 4, type: "مكية" },
        { num: 107, name: "الماعون", verses: 7, type: "مكية" },
        { num: 108, name: "الكوثر", verses: 3, type: "مكية" },
        { num: 109, name: "الكافرون", verses: 6, type: "مكية" },
        { num: 110, name: "النصر", verses: 3, type: "مدنية" },
        { num: 111, name: "المسد", verses: 5, type: "مكية" },
        { num: 112, name: "الإخلاص", verses: 4, type: "مكية" },
        { num: 113, name: "الفلق", verses: 5, type: "مكية" },
        { num: 114, name: "الناس", verses: 6, type: "مكية" }
    ];

    // 📖 241 COMPLETE QURAN RECITERS CONTROLLER (MP3Quran Network)
    let selectedReciterId = "51_53"; // Default: الشيخ عبدالباسط عبدالصمد (المصحف المرتل) // Default: الشيخ عبدالباسط عبدالصمد
    let activeReciterCategory = 'famous';
    let isAllRecitersExpanded = false;
    let currentPlayingSurahNum = null;

    function getRecitersDataSource() {
        if (typeof window !== 'undefined' && Array.isArray(window.QURAN_ALL_RECITERS) && window.QURAN_ALL_RECITERS.length > 0) {
            return window.QURAN_ALL_RECITERS;
        }
        return [
            { id: "51", name: "الشيخ عبدالباسط عبدالصمد", rewaya: "حفص عن عاصم - مرتل", server: "https://server7.mp3quran.net/basit/", surahsCount: 114 },
            { id: "112", name: "الشيخ محمد صديق المنشاوي", rewaya: "المصحف المعلم", server: "https://server10.mp3quran.net/minsh/Almusshaf-Al-Mo-lim/", surahsCount: 114 },
            { id: "118", name: "الشيخ محمود خليل الحصري", rewaya: "قالون عن نافع - مرتل", server: "https://server13.mp3quran.net/husr/Rewayat-Qalon-A-n-Nafi/", surahsCount: 114 },
            { id: "123", name: "الشيخ مشاري العفاسي", rewaya: "حفص عن عاصم - مرتل", server: "https://server8.mp3quran.net/afs/", surahsCount: 114 },
            { id: "102", name: "الشيخ ماهر المعيقلي", rewaya: "حفص عن عاصم - مرتل", server: "https://server12.mp3quran.net/maher/", surahsCount: 114 }
        ];
    }

    function initQuranStudio() {
        renderRecitersGrid();
        renderSurahsGrid(QURAN_SURAHS);
    }

    function setReciterFilterCategory(cat, btn) {
        activeReciterCategory = cat;
        document.querySelectorAll('.reciter-cat-btn').forEach(b => {
            b.className = 'reciter-cat-btn px-4 py-2 rounded-xl bg-slate-900/90 hover:bg-amber-950 text-amber-200 font-bold text-xs border border-amber-500/30 transition cursor-pointer';
        });
        if (btn) {
            btn.className = 'reciter-cat-btn px-4 py-2 rounded-xl bg-amber-400 text-slate-950 font-black text-xs border-2 border-amber-300 shadow-md transition cursor-pointer';
        }
        const searchInput = document.getElementById('quran-reciter-search');
        if (searchInput) searchInput.value = '';
        renderRecitersGrid();
    }

    function toggleAllRecitersExpand() {
        if (activeReciterCategory === 'all') {
            activeReciterCategory = 'famous';
        } else {
            activeReciterCategory = 'all';
        }
        const btn = document.getElementById('btn-show-all-reciters');
        if (btn) {
            btn.innerText = (activeReciterCategory === 'all') ? 'عرض كبار القراء المشاهير فقط ⬆' : 'عرض كافة المصاحف والقراء (287 مصحف وقارئ) ⬇';
        }
        renderRecitersGrid();
    }

    function filterRecitersList() {
        const input = document.getElementById('quran-reciter-search');
        const q = (input ? input.value : '').trim().toLowerCase();
        renderRecitersGrid(q);
    }

    function renderRecitersGrid(searchQuery = '') {
        const container = document.getElementById('quran-reciters-container');
        if (!container) return;

        const allReciters = getRecitersDataSource();
        let list = allReciters;

        if (searchQuery) {
            list = allReciters.filter(r => {
                const name = (r.name || '').toLowerCase();
                const rewaya = (r.rewaya || '').toLowerCase();
                return name.includes(searchQuery) || rewaya.includes(searchQuery);
            });
        } else {
            if (activeReciterCategory === 'famous') {
                list = allReciters.slice(0, 68); // Top legendary editions (Abdul Basit all moshafs, Minshawi all moshafs, Husary all moshafs, Mustafa Ismail, etc.)
            } else if (activeReciterCategory === 'mujawwad') {
                list = allReciters.filter(r => (r.name || '').includes('المجود') || (r.rewaya || '').includes('مجود') || r.isMujawwad);
            } else if (activeReciterCategory === 'murattal') {
                list = allReciters.filter(r => (r.name || '').includes('مرتل') || (r.rewaya || '').includes('مرتل') || (!r.isMujawwad && !r.isMoalim));
            } else if (activeReciterCategory === 'haram') {
                list = allReciters.filter(r => {
                    const n = r.name || '';
                    return n.includes('المعيقلي') || n.includes('السديس') || n.includes('الشريم') || n.includes('الدوسري') || n.includes('الحذيفي') || n.includes('أيوب') || n.includes('الجهني') || n.includes('جابر') || n.includes('بليله') || n.includes('الغامدي');
                });
            } else if (activeReciterCategory === 'moalim') {
                list = allReciters.filter(r => (r.rewaya || '').includes('المعلم') || (r.name || '').includes('المعلم') || r.isMoalim);
            } else if (activeReciterCategory === 'riwayat') {
                list = allReciters.filter(r => (r.rewaya || '').includes('ورش') || (r.rewaya || '').includes('قالون') || (r.rewaya || '').includes('الدوري') || (r.rewaya || '').includes('شعبة') || (r.rewaya || '').includes('السوسي') || (r.rewaya || '').includes('خلف') || (r.name || '').includes('رواية'));
            } else {
                list = allReciters; // all 287 editions
            }
        }

        const countBadge = document.getElementById('reciter-showing-count');
        if (countBadge) {
            countBadge.innerText = 'يتم عرض ' + list.length + ' من إجمالي ' + allReciters.length + ' مصحف وقارئ كامل (4 في كل سطر)';
        }

        if (!list.length) {
            container.innerHTML = '<div class="col-span-full py-10 text-center text-base font-bold text-amber-300">لا يوجد مصحف أو قارئ مطابق للبحث... يمكنك كتابة اسم الشيخ أو الرواية</div>';
            return;
        }

        let html = '';
        list.forEach((r, idx) => {
            const isSelected = String(r.id) === String(selectedReciterId) || (selectedReciterId === "51" && String(r.id) === "51_53") || (!selectedReciterId && idx === 0);
            const selectClass = isSelected ? 'is-selected' : '';

            html += `
                <div onclick="selectQuranReciter('${r.id}')" class="quran-reciter-card ${selectClass} group">
                    <!-- Top Centered Icon Badge (Golden Luxury) -->
                    <div style="background: #f59e0b; color: #451a03;" class="w-10 h-10 rounded-xl flex items-center justify-center text-xl font-black shadow-md group-hover:scale-110 transition-transform">
                        <span>🎙️</span>
                    </div>

                    <!-- 🌟 EXACT CENTER: LARGE PROMINENT GOLDEN SHEIKH NAME 🌟 -->
                    <div class="my-auto py-2 text-center w-full">
                        <div class="quran-sheikh-title-center group-hover:text-amber-100 transition-colors line-clamp-1">
                            ${r.name}
                        </div>
                        <div class="text-xs font-black text-amber-200 mt-1 flex items-center justify-center gap-1.5 truncate drop-shadow">
                            <span>📜</span>
                            <span>${r.rewaya || 'حفص عن عاصم - مرتل'}</span>
                        </div>
                    </div>

                    <!-- Bottom Bar: Surahs Count & Selection Button -->
                    <div class="flex items-center justify-between w-full pt-2 border-t border-amber-400/40">
                        <span style="background: rgba(69, 26, 3, 0.9); color: #fde68a; border: 1px solid #d97706;" class="text-[11px] px-2.5 py-1 rounded-lg font-black shadow-sm">
                            ${r.surahsCount || 114} سورة
                        </span>
                        <span style="${isSelected ? 'background: #ffffff !important; color: #78350f !important;' : 'background: #fbbf24 !important; color: #1e1005 !important;'}" class="text-xs px-3.5 py-1.5 rounded-lg font-black transition shadow-md">
                            ${isSelected ? '✓ محدد الآن' : 'اختيار المصحف ◀'}
                        </span>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    function selectQuranReciter(reciterId) {
        selectedReciterId = String(reciterId);
        renderRecitersGrid();

        const allReciters = getRecitersDataSource();
        const reciter = allReciters.find(r => String(r.id) === String(reciterId));
        if (reciter) {
            const display = document.getElementById('active-reciter-name-display');
            const heading = document.getElementById('surahs-heading-title');
            if (display) display.innerText = 'القارئ المحدد: ' + reciter.name;
            if (heading) heading.innerText = 'الخطوة الثانية: اختر السورة للتشغيل بصوت (' + reciter.name + '):';
            if (typeof showFiqhToast === 'function') showFiqhToast('🎙️ تم اختيار ' + reciter.name);
        }

        const surahSec = document.getElementById('quran-surahs-section');
        if (surahSec) {
            surahSec.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // 🎨 6 ROTATING RADIANT JEWEL PALETTES FOR 114 SURAH BUTTONS
    const SURAH_PALETTES = [
        {
            // 0: Cyan Emerald Glow
            border: 'border-cyan-500/40 hover:border-cyan-300',
            bg: 'bg-gradient-to-b from-cyan-950/70 via-slate-950/90 to-cyan-950/80',
            numBg: 'bg-cyan-500/20 text-cyan-300 border-cyan-400/50',
            titleColor: 'text-cyan-100 group-hover:text-cyan-300',
            btnBg: 'bg-cyan-500/20 hover:bg-cyan-500 text-cyan-300 hover:text-slate-950',
            glowCss: '--sc-glow: rgba(6, 182, 212, 0.6); --sc-border: #22d3ee;'
        },
        {
            // 1: Royal Emerald Green
            border: 'border-emerald-500/40 hover:border-emerald-300',
            bg: 'bg-gradient-to-b from-emerald-950/70 via-slate-950/90 to-emerald-950/80',
            numBg: 'bg-emerald-500/20 text-emerald-300 border-emerald-400/50',
            titleColor: 'text-emerald-100 group-hover:text-emerald-300',
            btnBg: 'bg-emerald-500/20 hover:bg-emerald-500 text-emerald-300 hover:text-slate-950',
            glowCss: '--sc-glow: rgba(16, 185, 129, 0.6); --sc-border: #34d399;'
        },
        {
            // 2: Imperial Gold / Amber
            border: 'border-amber-500/40 hover:border-amber-300',
            bg: 'bg-gradient-to-b from-amber-950/70 via-slate-950/90 to-amber-950/80',
            numBg: 'bg-amber-500/20 text-amber-300 border-amber-400/50',
            titleColor: 'text-amber-100 group-hover:text-amber-300',
            btnBg: 'bg-amber-500/20 hover:bg-amber-500 text-amber-300 hover:text-slate-950',
            glowCss: '--sc-glow: rgba(245, 158, 11, 0.6); --sc-border: #fbbf24;'
        },
        {
            // 3: Royal Amethyst Purple
            border: 'border-purple-500/40 hover:border-purple-300',
            bg: 'bg-gradient-to-b from-purple-950/70 via-slate-950/90 to-purple-950/80',
            numBg: 'bg-purple-500/20 text-purple-300 border-purple-400/50',
            titleColor: 'text-purple-100 group-hover:text-purple-300',
            btnBg: 'bg-purple-500/20 hover:bg-purple-500 text-purple-300 hover:text-slate-950',
            glowCss: '--sc-glow: rgba(168, 85, 247, 0.6); --sc-border: #c084fc;'
        },
        {
            // 4: Radiant Ruby Rose
            border: 'border-rose-500/40 hover:border-rose-300',
            bg: 'bg-gradient-to-b from-rose-950/70 via-slate-950/90 to-rose-950/80',
            numBg: 'bg-rose-500/20 text-rose-300 border-rose-400/50',
            titleColor: 'text-rose-100 group-hover:text-rose-300',
            btnBg: 'bg-rose-500/20 hover:bg-rose-500 text-rose-300 hover:text-slate-950',
            glowCss: '--sc-glow: rgba(244, 63, 94, 0.6); --sc-border: #fb7185;'
        },
        {
            // 5: Sapphire Sky Blue
            border: 'border-sky-500/40 hover:border-sky-300',
            bg: 'bg-gradient-to-b from-sky-950/70 via-slate-950/90 to-sky-950/80',
            numBg: 'bg-sky-500/20 text-sky-300 border-sky-400/50',
            titleColor: 'text-sky-100 group-hover:text-sky-300',
            btnBg: 'bg-sky-500/20 hover:bg-sky-500 text-sky-300 hover:text-slate-950',
            glowCss: '--sc-glow: rgba(14, 165, 233, 0.6); --sc-border: #38bdf8;'
        }
    ];

    function renderSurahsGrid(surahsList) {
        const container = document.getElementById('quran-surahs-grid');
        if (!container) return;

        if (!surahsList || surahsList.length === 0) {
            container.innerHTML = '<div class="col-span-full py-12 text-center text-base font-bold text-emerald-300">لا توجد سور مطابقة لبحثك... جرب كتابة رقم السورة أو اسمها</div>';
            return;
        }

        let html = '';
        surahsList.forEach((s, idx) => {
            const padNum = String(s.num).padStart(3, '0');
            const isPlaying = (currentPlayingSurahNum === s.num);
            const activeClass = isPlaying ? 'is-playing-surah' : '';
            const typeIcon = (s.type === 'مكية') ? '🕋' : '🕌';

            html += `
                <div onclick="playSurahTrack(${s.num})" class="quran-surah-card ${activeClass} group shadow-lg">
                    <!-- Top Bar: Surah Number & Makki/Madani -->
                    <div class="flex items-center justify-between gap-1">
                        <span style="background: #34d399; color: #022c22;" class="w-8 h-8 rounded-lg font-mono font-black text-xs flex items-center justify-center shadow-md">
                            ${padNum}
                        </span>
                        <span style="background: rgba(6, 78, 59, 0.9); color: #a7f3d0; border: 1px solid #34d399;" class="text-[11px] font-black flex items-center gap-1 px-2.5 py-0.5 rounded-lg shadow-sm">
                            <span>${typeIcon}</span>
                            <span>${s.type}</span>
                        </span>
                    </div>

                    <!-- Center Area: LARGE PROMINENT CENTERED SURAH NAME (WHITE ON EMERALD) -->
                    <div class="my-auto py-2.5 text-center">
                        <div class="quran-surah-title-center">
                            سورة ${s.name}
                        </div>
                        ${isPlaying ? `
                            <div class="flex items-center justify-center gap-1.5 mt-1">
                                <span class="w-1.5 h-3 bg-white animate-bounce rounded-full"></span>
                                <span class="w-1.5 h-4 bg-emerald-200 animate-bounce rounded-full" style="animation-delay: 0.15s"></span>
                                <span class="w-1.5 h-2.5 bg-emerald-300 animate-bounce rounded-full" style="animation-delay: 0.3s"></span>
                                <span class="text-xs font-black text-white mr-1.5 drop-shadow">🟢 شغال الآن</span>
                            </div>
                        ` : ''}
                    </div>

                    <!-- Bottom Bar: Verses Count & Listen Button -->
                    <div class="flex items-center justify-between gap-2 pt-1.5 border-t border-emerald-400/40">
                        <span class="text-[11px] font-black text-emerald-100 drop-shadow">
                            ${s.verses} آية
                        </span>
                        ${isPlaying ? `
                            <span style="background: #ffffff; color: #064e3b;" class="px-3.5 py-1 rounded-lg font-black text-xs flex items-center gap-1.5 shadow-md">
                                <span>🟢</span>
                                <span>مشتغل</span>
                            </span>
                        ` : `
                            <button type="button" style="background: #34d399; color: #022c22;" class="px-3.5 py-1 rounded-lg hover:bg-emerald-300 font-black text-xs transition flex items-center gap-1.5 shadow-md">
                                <span>▶</span>
                                <span>استماع</span>
                            </button>
                        `}
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    function filterSurahsList() {
        const input = document.getElementById('quran-surah-search');
        const q = (input ? input.value : '').trim().toLowerCase();
        if (!q) {
            renderSurahsGrid(QURAN_SURAHS);
            return;
        }

        const filtered = QURAN_SURAHS.filter(s => {
            const numStr = String(s.num);
            const padStr = String(s.num).padStart(3, '0');
            const name = s.name.toLowerCase();
            return name.includes(q) || numStr.includes(q) || padStr.includes(q);
        });
        renderSurahsGrid(filtered);
    }

    function quickPlaySurah(surahNum) {
        playSurahTrack(surahNum);
    }

    function playSurahTrack(surahNum) {
        const allReciters = getRecitersDataSource();
        const reciter = allReciters.find(r => String(r.id) === String(selectedReciterId) || (selectedReciterId === "51" && String(r.id) === "51_53")) || allReciters[0];
        const surah = QURAN_SURAHS.find(s => s.num === surahNum);
        if (!surah) return;

        const padNum = String(surah.num).padStart(3, '0');
        const audioUrl = reciter.server + padNum + '.mp3';
        const title = 'سورة ' + surah.name + ' - ' + reciter.name;

        currentPlayingSurahNum = surahNum;
        activeQuickChannel = 'quran_mp3';
        renderQuickChannelVisuals('quran_mp3');

        // Update active title badge in studio
        const titleBadge = document.getElementById('active-quran-title');
        if (titleBadge) {
            titleBadge.innerHTML = '▶️ جاري الاستماع الآن: <strong class="text-cyan-300">سورة ' + surah.name + '</strong> بصوت <strong class="text-amber-300">' + reciter.name + '</strong> (' + (reciter.rewaya || 'مرتل') + ')';
        }

        // Re-render surahs to reflect active playing state
        const searchInput = document.getElementById('quran-surah-search');
        const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
        if (q) {
            filterSurahsList();
        } else {
            renderSurahsGrid(QURAN_SURAHS);
        }

        playPersistentMedia('audio', audioUrl, title, { channelKey: 'quran_mp3', surahNum: surahNum, reciterId: reciter.id });
        // Sync studio audio player UI bar without double audio output
        const studioPlayer = document.getElementById('main-quran-audio-player');
        if (studioPlayer) {
            studioPlayer.muted = true;
            studioPlayer.src = audioUrl;
            studioPlayer.play().catch(() => {});
        }
        if (typeof showFiqhToast === 'function') showFiqhToast('▶️ جاري تشغيل سورة ' + surah.name + ' بصوت ' + reciter.name + ' في الخلفية');
    }

    const TV_CHANNELS_DATA = {
        majd: {
            title: "تلفزيون المجد للقرآن الكريم (بث مباشر)",
            sub: "تلاوات عطرة خاشعة متواصلة 24/7",
            audioUrl: "https://backup.qurango.net/radio/tarateel",
            backupAudioUrl: "https://qurango.net/radio/tarateel",
            icon: "fa-solid fa-tv",
            color: "amber"
        },
        makkah: {
            title: "تلفزيون مكة المكرمة - الحرم المكي الشريف",
            sub: "بث حي ومباشر من بيت الله الحرام 24/7",
            audioUrl: "https://backup.qurango.net/radio/maher",
            backupAudioUrl: "https://qurango.net/radio/maher",
            icon: "fa-solid fa-kaaba",
            color: "yellow"
        },
        madinah: {
            title: "تلفزيون المدينة المنورة - المسجد النبوي",
            sub: "بث حي ومباشر من الروضة الشريفة 24/7",
            audioUrl: "https://backup.qurango.net/radio/ali_alhuthaifi",
            backupAudioUrl: "https://qurango.net/radio/ali_alhuthaifi",
            icon: "fa-solid fa-mosque",
            color: "emerald"
        },
        hadith: {
            title: "إذاعة صحيح البخاري والحديث الشريف (24/7)",
            sub: "أحاديث النبي ﷺ والسنة النبوية المطهرة",
            audioUrl: "https://backup.qurango.net/radio/saheh-bokharee",
            backupAudioUrl: "https://qurango.net/radio/saheh-bokharee",
            icon: "fa-solid fa-book-open",
            color: "purple"
        },
        hadith_bukhari: {
            title: "إذاعة صحيح البخاري (بث مباشر 24/7)",
            sub: "قراءة أحاديث الجامع الصحيح للإمام البخاري",
            audioUrl: "https://backup.qurango.net/radio/saheh-bokharee",
            backupAudioUrl: "https://qurango.net/radio/saheh-bokharee",
            icon: "fa-solid fa-book-open",
            color: "purple"
        },
        hadith_muslim: {
            title: "إذاعة صحيح مسلم (بث مباشر 24/7)",
            sub: "قراءة أحاديث صحيح مسلم رحمه الله",
            audioUrl: "https://backup.qurango.net/radio/saheh-muslim",
            backupAudioUrl: "https://qurango.net/radio/saheh-muslim",
            icon: "fa-solid fa-book-bookmark",
            color: "emerald"
        },
        hadith_riyad: {
            title: "إذاعة رياض الصالحين (بث مباشر 24/7)",
            sub: "كتاب رياض الصالحين للإمام النووي",
            audioUrl: "https://backup.qurango.net/radio/riyad",
            backupAudioUrl: "https://qurango.net/radio/riyad_assaliheen",
            icon: "fa-solid fa-leaf",
            color: "emerald"
        },
        hadith_shmaeel: {
            title: "إذاعة الشمائل المحمدية (بث مباشر 24/7)",
            sub: "أحاديث صفات وأخلاق النبي ﷺ للترمذي",
            audioUrl: "https://backup.qurango.net/radio/shmaeel",
            backupAudioUrl: "https://qurango.net/radio/shmaeel",
            icon: "fa-solid fa-star-and-crescent",
            color: "purple"
        },
        hadith_siyra: {
            title: "إذاعة في ظلال السيرة النبوية (بث مباشر 24/7)",
            sub: "400 حلقة متواصلة في سيرة الحبيب ﷺ",
            audioUrl: "https://backup.qurango.net/radio/fi_zilal_alsiyra",
            backupAudioUrl: "https://backup.qurango.net/radio/almukhtasar_fi_alsiyra",
            icon: "fa-solid fa-mosque",
            color: "amber"
        },
        hadith_sahabah: {
            title: "إذاعة صور من حياة الصحابة (24/7)",
            sub: "مواقف وأحاديث جيل الصحابة رضوان الله عليهم",
            audioUrl: "https://backup.qurango.net/radio/sahabah",
            backupAudioUrl: "https://qurango.net/radio/sahabah",
            icon: "fa-solid fa-users",
            color: "sky"
        },
        aljazeera: {
            title: "قناة الجزيرة الإخبارية (البث الصوتي المباشر)",
            sub: "تغطية إخبارية حية ومتواصلة على مدار الساعة 24/7",
            audioUrl: "https://live-hls-audio-web-aja.getaj.net/VOICE-AJA/index.m3u8",
            backupAudioUrl: "https://live-hls-web-aja.getaj.net/AJA/index.m3u8",
            icon: "fa-solid fa-tower-broadcast",
            color: "red"
        },
        bbc_arabic: {
            title: "قناة بي بي سي عربي الإخبارية - BBC News Arabic (بث حي مباشر باللغة العربية)",
            sub: "الأخبار والبرامج الإخبارية المباشرة 24/7 باللغة العربية",
            audioUrl: "https://vs-hls-pushb-ww-live.akamaized.net/x=4/i=urn:bbc:pips:service:bbc_arabic_tv/mobile_wifi_main_hd_abr_v2.m3u8",
            backupAudioUrl: "https://vs-hls-pushb-ww.live.cf.md.bbci.co.uk/x=4/i=urn:bbc:pips:service:bbc_arabic_tv/pc_hd_abr_v2.m3u8",
            icon: "fa-solid fa-radio",
            color: "rose"
        }
    };

    let persistentMediaType = null;
    let isPersistentVideoExpanded = true;

    
    // ════════════════════════════════════════════════════════════════════════════
    // ════════════════════════════════════════════════════════════════════════════
    // 💡 ONE-CLICK TOGGLE & GLOWING MEDIA ENGINE (مكة، المدينة، المجد، القرآن MP3، الحديث الشريف)
    // ════════════════════════════════════════════════════════════════════════════
    var activeQuickChannel = null;
    var currentHadithChannel = 'hadith_bukhari';

    function toggleQuickChannel(channelKey) {
        if (activeQuickChannel === channelKey) {
            // Currently playing this channel -> STOP IT!
            if (typeof window.stopPersistentMedia === 'function') {
                window.stopPersistentMedia();
            }
            activeQuickChannel = null;
            renderQuickChannelVisuals(null);
            if (typeof showFiqhToast === 'function') showFiqhToast('⏹️ تم إيقاف البث');
        } else {
            // Start playing requested channel immediately!
            if (channelKey === 'quran_mp3') {
                openQuranMp3Studio();
                return;
            }
            playTvChannel(channelKey);
            activeQuickChannel = channelKey;
            renderQuickChannelVisuals(channelKey);
            const chData = TV_CHANNELS_DATA[channelKey];
            const channelName = chData ? chData.title.split('(')[0].replace('إذاعة ', '').replace('تلفزيون ', '').trim() : channelKey;
            if (typeof showFiqhToast === 'function') {
                showFiqhToast('▶️ تم تشغيل ' + channelName + ' في الخلفية');
            }
        }
    }

    function handleQuranCardClick(event) {
        if (event && event.target && event.target.closest && event.target.closest('.stop-quran-pill-action')) {
            event.stopPropagation();
            if (typeof window.stopPersistentMedia === 'function') {
                window.stopPersistentMedia();
            }
            activeQuickChannel = null;
            renderQuickChannelVisuals(null);
            if (typeof showFiqhToast === 'function') showFiqhToast('⏹️ تم إيقاف تلاوة القرآن الكريم');
            return;
        }
        openQuranMp3Studio();
    }

    function openQuranMp3Studio() {
        switchSuite('quran_mp3', null);
        const sec = document.getElementById('suite-sec-quran_mp3');
        if (sec) {
            sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        if (typeof showFiqhToast === 'function') showFiqhToast('📖 اختر قارئك المفضل ثم اختر السورة لبدء الاستماع مباشرة في الخلفية');
    }

    function renderQuickChannelVisuals(activeKey) {
        const keys = [
            'makkah', 'madinah', 'majd', 'quran_mp3', 'aljazeera', 'bbc_arabic',
            'hadith_bukhari', 'hadith_muslim', 'hadith_riyad',
            'hadith_shmaeel', 'hadith_siyra', 'hadith_sahabah'
        ];

        const defaultLabels = {
            makkah: 'الحرم المكي',
            madinah: 'المسجد النبوي',
            majd: 'تلاوات خاشعة',
            quran_mp3: '287 قارئ ومصحف',
            aljazeera: 'بث صوتي مباشر',
            bbc_arabic: 'أخبار حية بالعربية',
            hadith_bukhari: 'إذاعة السنة 24/7',
            hadith_muslim: 'الجامع الصحيح',
            hadith_riyad: 'للإمام النووي',
            hadith_shmaeel: 'للإمام الترمذي',
            hadith_siyra: 'في ظلال السيرة',
            hadith_sahabah: 'سير الصحابة الأبرار'
        };

        keys.forEach(k => {
            const btn = document.getElementById('quick-media-btn-' + k);
            const sub = document.getElementById('sm-sub-' + k);

            if (btn) {
                btn.classList.remove('is-active', 'is-active-' + k);
            }

            const isMatch = (k === activeKey);

            if (isMatch) {
                if (btn) {
                    btn.classList.add('is-active');
                    btn.classList.add('is-active-' + k);
                }
                if (sub) {
                    if (k === 'quran_mp3') {
                        sub.innerHTML = '<span class="text-emerald-300 font-black animate-pulse">🟢 شغال</span> <span class="stop-quran-pill-action underline text-red-300 mr-0.5 hover:text-red-100 cursor-pointer" onclick="event.stopPropagation(); window.stopPersistentMedia(); renderQuickChannelVisuals(null);">(⏹️)</span>';
                    } else {
                        sub.innerHTML = '<span class="text-emerald-300 font-black animate-pulse">🟢 شغال</span>';
                    }
                }
            } else {
                if (sub) {
                    sub.innerText = defaultLabels[k] || 'تشغيل فوري';
                }
            }
        });
    }

    const HADITH_STATIONS = [
        { key: 'hadith_bukhari', title: 'صحيح البخاري', desc: 'أحاديث الجامع الصحيح للإمام البخاري كاملة', icon: '📜', badge: 'متفق عليه' },
        { key: 'hadith_muslim', title: 'صحيح مسلم', desc: 'أحاديث صحيح الإمام مسلم رحمه الله', icon: '📗', badge: 'أعلى الصحة' },
        { key: 'hadith_riyad', title: 'رياض الصالحين', desc: 'كتاب رياض الصالحين للإمام النووي', icon: '🌿', badge: 'أحاديث الأخلاق' },
        { key: 'hadith_shmaeel', title: 'الشمائل المحمدية', desc: 'صفات وأخلاق وشمائل المصطفى ﷺ للترمذي', icon: '✨', badge: 'الشمائل' },
        { key: 'hadith_siyra', title: 'في ظلال السيرة', desc: '400 حلقة متواصلة في سيرة الحبيب ﷺ', icon: '🕌', badge: 'السيرة النبوية' },
        { key: 'hadith_sahabah', title: 'حياة الصحابة', desc: 'مواقف وأحاديث جيل الصحابة والتابعين', icon: '👥', badge: 'الصحابة' }
    ];

    function openHadithChannelModal(e) {
        if (e) e.stopPropagation();
        renderHadithModalList();
        const modal = document.getElementById('hadith-channels-modal');
        if (modal) modal.style.display = 'flex';
    }

    function closeHadithChannelModal() {
        const modal = document.getElementById('hadith-channels-modal');
        if (modal) modal.style.display = 'none';
    }

    function selectHadithChannel(stationKey) {
        currentHadithChannel = stationKey;
        playTvChannel(stationKey);
        activeQuickChannel = stationKey;
        renderQuickChannelVisuals(stationKey);
        renderHadithModalList();
        closeHadithChannelModal();
        const curData = TV_CHANNELS_DATA[stationKey];
        if (typeof showFiqhToast === 'function') {
            showFiqhToast('▶️ تم تشغيل ' + (curData ? curData.title : 'الحديث الشريف') + ' في الخلفية');
        }
    }

    function renderHadithModalList() {
        const container = document.getElementById('hadith-modal-channels-list');
        if (!container) return;
        const activeKey = activeQuickChannel;

        container.innerHTML = HADITH_STATIONS.map(st => {
            const isPlaying = (activeKey === st.key);
            const isCurrent = (currentHadithChannel === st.key);
            return `
                <div onclick="selectHadithChannel('${st.key}')" class="p-3 rounded-2xl border transition flex items-center justify-between cursor-pointer ${isPlaying ? 'bg-purple-900/60 border-purple-400 shadow-lg shadow-purple-500/20' : 'bg-slate-800/80 hover:bg-slate-800 border-white/10 hover:border-purple-500/40'}">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">${st.icon}</span>
                        <div>
                            <div class="font-black text-sm ${isPlaying ? 'text-purple-200' : 'text-slate-100'} flex items-center gap-2">
                                <span>${st.title}</span>
                                ${isPlaying ? '<span class="text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 px-2 py-0.5 rounded-md animate-pulse">🟢 شغال الآن</span>' : ''}
                            </div>
                            <div class="text-xs text-slate-400 font-bold mt-0.5">${st.desc}</div>
                        </div>
                    </div>
                    <span class="text-xs font-black px-2.5 py-1 rounded-lg ${isPlaying ? 'bg-purple-500 text-white' : (isCurrent ? 'bg-white/10 text-purple-300' : 'bg-white/5 text-slate-300')}">
                        ${isPlaying ? 'مشغل ▶' : (isCurrent ? 'محدد ✓' : 'استماع')}
                    </span>
                </div>
            `;
        }).join('');
    }

    window.openHadithChannelModal = openHadithChannelModal;
    window.closeHadithChannelModal = closeHadithChannelModal;
    window.selectHadithChannel = selectHadithChannel;
    window.renderHadithModalList = renderHadithModalList;

    // Sync from global storage on page load
    try {
        const savedState = localStorage.getItem('elsajana_global_media_state');
        if (savedState) {
            const parsed = JSON.parse(savedState);
            if (parsed && parsed.active && parsed.extraMeta && parsed.extraMeta.channelKey) {
                activeQuickChannel = parsed.extraMeta.channelKey;
                setTimeout(() => renderQuickChannelVisuals(activeQuickChannel), 300);
            }
        }
        if (typeof BroadcastChannel !== 'undefined') {
            const bcSync = new BroadcastChannel('elsajana_media_broadcast');
            bcSync.onmessage = function(ev) {
                if (!ev.data) return;
                if (ev.data.type === 'STATE_CHANGED' && ev.data.state && ev.data.state.extraMeta && ev.data.state.extraMeta.channelKey) {
                    activeQuickChannel = ev.data.state.extraMeta.channelKey;
                    renderQuickChannelVisuals(activeQuickChannel);
                } else if (ev.data.type === 'STOP_ALL') {
                    activeQuickChannel = null;
                    renderQuickChannelVisuals(null);
                }
            };
        }
    } catch(e) {}

    window.toggleQuickChannel = toggleQuickChannel;
    window.handleQuranCardClick = handleQuranCardClick;
    window.openQuranMp3Studio = openQuranMp3Studio;
    window.renderQuickChannelVisuals = renderQuickChannelVisuals;

    function playTvChannel(channelKey) {
        if (window.top && window.top !== window.self && typeof window.top.playTvChannel === 'function') {
            window.top.playTvChannel(channelKey);
            return;
        }
        if (typeof window.quickHeaderPlay === 'function' && window.playTvChannel !== playTvChannel) {
            window.playTvChannel(channelKey);
            return;
        }
        const ch = TV_CHANNELS_DATA[channelKey];
        if (ch && typeof window.playPersistentMedia === 'function' && window.playPersistentMedia !== playPersistentMedia) {
            window.playPersistentMedia('audio', ch.audioUrl, ch.title, {
                channelKey: channelKey,
                backupAudioUrl: ch.backupAudioUrl,
                sub: ch.sub
            });
        }
    }

    function playPersistentMedia(type, sourceUrl, title, extraMeta) {
        if (window.top && window.top !== window.self && typeof window.top.playPersistentMedia === 'function') {
            window.top.playPersistentMedia(type, sourceUrl, title, extraMeta);
            return;
        }
        if (typeof window.playPersistentMedia === 'function' && window.playPersistentMedia !== playPersistentMedia) {
            window.playPersistentMedia(type, sourceUrl, title, extraMeta);
        }
    }

    function stopPersistentMedia() {
        if (typeof window.quickHeaderStop === 'function') {
            window.quickHeaderStop();
        } else if (typeof window.stopPersistentMedia === 'function' && window.stopPersistentMedia !== stopPersistentMedia) {
            window.stopPersistentMedia();
        }
        const studioPlayer = document.getElementById('main-quran-audio-player');
        if (studioPlayer) {
            try { studioPlayer.pause(); } catch (e) {}
        }
        activeQuickChannel = null;
        renderQuickChannelVisuals(null);
    }

    function switchSuite(suiteId, el) {
        if (suiteId === 'fatwa') suiteId = 'fiqh';
        document.querySelectorAll('.suite-section').forEach(sec => sec.classList.add('hidden'));
        document.querySelectorAll('.suite-pill-card').forEach(btn => btn.classList.remove('active'));

        const targetSec = document.getElementById('suite-sec-' + suiteId);
        if (targetSec) targetSec.classList.remove('hidden');
        if (!el) {
            el = document.getElementById('tab-pill-' + suiteId) || document.querySelector('[onclick*="switchSuite(\'' + suiteId + '\'"]');
        }
        if (el) el.classList.add('active');

        if (suiteId === 'quran_mp3') {
            if (typeof initQuranStudio === 'function') {
                initQuranStudio();
            }
        } else if (suiteId === 'fiqh') {
            if (typeof initFiqhEngine === 'function') {
                initFiqhEngine();
            }
        } else if (suiteId === 'inheritance') {
            if (typeof initInheritanceEngine === 'function') {
                initInheritanceEngine();
            }
        } else if (suiteId === 'reconciliation') {
            if (typeof initReconciliationSuite === 'function') {
                initReconciliationSuite();
            }
        } else if (suiteId === 'wellness') {
            if (typeof window.initWellnessSuite === 'function') {
                window.initWellnessSuite();
            }
        }
    }
    window.switchSuite = switchSuite;

    // ════════════════════════════════════════════════════════════════════════════
    // 🧠 EMPLOYEE PSYCHOLOGICAL, MENTAL WELLNESS & EDUTAINMENT CONTROLLERS
    // ════════════════════════════════════════════════════════════════════════════
    var _wellnessBreathingTimer = null;
    var _wellnessBreathingActive = false;
    var _wellnessBreathingCycles = 0;
    var _wellnessPomoTimer = null;
    var _wellnessPomoSeconds = 1500;
    var _wellnessMathAnswer = null;
    var _wellnessMathScore = 0;
    var _wellnessAuditMatch = true;
    var _wellnessAuditScore = 0;

    window.initWellnessSuite = function() {
        const best = localStorage.getItem('erp_wellness_math_best') || '0';
        const bestEl = document.getElementById('math-game-best');
        if (bestEl) bestEl.textContent = best;
        if (_wellnessMathAnswer === null) window.nextWellnessMathQuestion();
        window.nextAuditSpotterRound();
    };

    window.selectWellnessMood = function(moodKey) {
        const prescriptions = {
            burnout: '💆‍♂️ <strong>وصفة تفريغ ضغط العمل المحاسبي:</strong> خذ استراحة 3 دقائق بعيداً عن لوحة المفاتيح، اشرب كوب ماء، وابدأ دورة تنفس (4-7-8) على اليسار. قسّم المهام المتبقية إلى دفعات صغيرة (كل 5 فواتير على حدة) ولا تجمع المراجعة والإدخال في نفس اللحظة.',
            anxiety: '🛡️ <strong>وصفة طمأنينة المطابقة والجرد:</strong> القلق طبيعي لدى المحاسب الدقيق! استخدم أداة "فحص الكشوفات" في التبويب رقم 12 لمطابقة أي كشف آلياً في ثوانٍ بدون إجهاد، وتأكد أن كل حركة موثقة في سجل الرقابة.',
            fog: '⚡ <strong>وصفة استعادة التركيز الذهني:</strong> التشتت يحدث بسبب تعدد النوافذ. شغل مؤقت التركيز العميق (25 دقيقة) على مهمة واحدة فقط، وجرّب جولة سريعة في "لعبة البرق الحسابي" بالأسفل لتنشيط الفص الجبهي للمخ.',
            eyestrain: '👁️ <strong>وصفة حماية البصر الفورية (قاعدة 20-20-20):</strong> اضغط زر (20ث للعين 👁️) على اليسار الآن، وانظر إلى أبعد نقطة في الغرفة لمدة 20 ثانية مع الرمش ببطء 10 مرات لترطيب القرنية وإرخاء عضلة العدسة.',
            motivation: '🚀 <strong>شحنة تحفيز وطاقة إيجابية:</strong> تذكر أن عملك المالي والمحاسبي المتقن هو العمود الفقري لنجاح المؤسسة وحفظ الحقوق والأمانات. كل رقم تضبطه هو أمانة تؤجر عليها!',
            peace: '🌙 <strong>وصفة السكينة والانشراح:</strong> «أَلَا بِذِكْرِ اللَّهِ تَطْمَئِنُّ الْقُلُوبُ» — قمنا بتجهيز تلاوة خاشعة وإذاعة القرآن الكريم والسنة النبوية لتعمل في الخلفية بهدوء أثناء عملك.'
        };
        const box = document.getElementById('wellness-ai-prescription-text');
        if (box) box.innerHTML = prescriptions[moodKey] || prescriptions.peace;
    };

    window.triggerWellnessAIBoost = function() {
        const hour = new Date().getHours();
        const shiftAdvice = hour >= 15
            ? 'أنت الآن في الفترة المسائية؛ يوصي المستشار النفسي الذكي بتمرين تنفس لمدة دقيقة وتخفيف سطوع الشاشة وتفعيل وضع راحة العين.'
            : 'أنت في ذروة النشاط الذهني الصباحي؛ هذا أفضل وقت لإنجاز القيود والتسويات المعقدة قبل المهام الروتينية.';
        const box = document.getElementById('wellness-ai-prescription-text');
        if (box) {
            box.innerHTML = `🤖 <strong>تحليل الذكاء النفسي اللحظي:</strong> ${shiftAdvice}`;
        }
        if (typeof showFiqhToast === 'function') showFiqhToast('🧠 تم توليد الروشتة النفسية والذهنية الذكية');
    };

    window.toggleWellnessBreathing = function() {
        const orb = document.getElementById('wellness-breathing-orb');
        const lbl = document.getElementById('wellness-breathing-label');
        const inst = document.getElementById('wellness-breathing-instruction');
        const btn = document.getElementById('btn-toggle-breathing');
        if (_wellnessBreathingActive) {
            _wellnessBreathingActive = false;
            clearTimeout(_wellnessBreathingTimer);
            if (orb) orb.style.transform = 'scale(1)';
            if (lbl) lbl.textContent = 'اضغط لبدء التنفس 🧘‍♂️';
            if (inst) inst.textContent = 'تم إيقاف جلسة التنفس — يمكنك معاودة البدء في أي وقت.';
            if (btn) btn.textContent = '▶️ ابدأ جلسة التنفس';
            return;
        }
        _wellnessBreathingActive = true;
        if (btn) btn.textContent = '⏹️ إيقاف التنفس';

        const runCycle = () => {
            if (!_wellnessBreathingActive) return;
            // Phase 1: Inhale 4s
            if (orb) {
                orb.style.transition = 'all 4s ease-in-out';
                orb.style.transform = 'scale(1.22)';
                orb.style.borderColor = '#38bdf8';
            }
            if (lbl) lbl.textContent = 'شهيق عميق... (4ث)';
            if (inst) inst.textContent = '🌬️ خذ شهيقاً عميقاً وهادئاً من الأنف لمدة 4 ثوانٍ...';

            _wellnessBreathingTimer = setTimeout(() => {
                if (!_wellnessBreathingActive) return;
                // Phase 2: Hold 7s
                if (orb) {
                    orb.style.transition = 'all 0.5s ease';
                    orb.style.borderColor = '#fbbf24';
                }
                if (lbl) lbl.textContent = 'احبس النفس... (7ث)';
                if (inst) inst.textContent = '⏳ احبس النفس بهدوء لتوزيع الأكسجين وتهدئة النبض (7 ثوانٍ)...';

                _wellnessBreathingTimer = setTimeout(() => {
                    if (!_wellnessBreathingActive) return;
                    // Phase 3: Exhale 8s
                    if (orb) {
                        orb.style.transition = 'all 8s ease-in-out';
                        orb.style.transform = 'scale(0.92)';
                        orb.style.borderColor = '#10b981';
                    }
                    if (lbl) lbl.textContent = 'زفير بطيء... (8ث)';
                    if (inst) inst.textContent = '🍃 أخرج الهواء ببطء من الفم وفرّغ معه كل ضغط العمل (8 ثوانٍ)...';

                    _wellnessBreathingTimer = setTimeout(() => {
                        if (!_wellnessBreathingActive) return;
                        _wellnessBreathingCycles++;
                        const badge = document.getElementById('breathing-cycles-badge');
                        if (badge) badge.textContent = 'الدورات: ' + _wellnessBreathingCycles;
                        runCycle();
                    }, 8000);
                }, 7000);
            }, 4000);
        };
        runCycle();
    };

    window.startWellnessTimer = function(seconds, statusLabel) {
        clearInterval(_wellnessPomoTimer);
        _wellnessPomoSeconds = seconds;
        const st = document.getElementById('wellness-pomo-status');
        if (st && statusLabel) st.textContent = statusLabel;
        const disp = document.getElementById('wellness-pomo-display');
        const renderTime = () => {
            const m = Math.floor(_wellnessPomoSeconds / 60);
            const s = _wellnessPomoSeconds % 60;
            if (disp) disp.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        };
        renderTime();
        _wellnessPomoTimer = setInterval(() => {
            _wellnessPomoSeconds--;
            if (_wellnessPomoSeconds <= 0) {
                clearInterval(_wellnessPomoTimer);
                if (disp) disp.textContent = '00:00';
                if (typeof showFiqhToast === 'function') showFiqhToast('🔔 انتهى المؤقت بنجاح! أحسنت الالتزام.');
                return;
            }
            renderTime();
        }, 1000);
    };

    window.stopWellnessTimer = function() {
        clearInterval(_wellnessPomoTimer);
        const disp = document.getElementById('wellness-pomo-display');
        if (disp) disp.textContent = '25:00';
    };

    window.nextWellnessMathQuestion = function() {
        const types = ['add', 'sub', 'disc', 'vat'];
        const t = types[Math.floor(Math.random() * types.length)];
        let qText = '';
        let ans = 0;
        if (t === 'add') {
            const a = (Math.floor(Math.random() * 45) + 15) * 10;
            const b = (Math.floor(Math.random() * 35) + 12) * 10;
            ans = a + b;
            qText = `كم إجمالي سندين: ${a} + ${b} ؟`;
        } else if (t === 'sub') {
            const a = (Math.floor(Math.random() * 60) + 40) * 10;
            const b = (Math.floor(Math.random() * 30) + 10) * 10;
            ans = a - b;
            qText = `عميل عليه ${a} سدد ${b} — كم المتبقي؟`;
        } else if (t === 'disc') {
            const price = (Math.floor(Math.random() * 9) + 2) * 1000;
            const pct = [5, 10, 15, 20, 25][Math.floor(Math.random() * 5)];
            ans = price - (price * pct / 100);
            qText = `فاتورة بـ ${price} بخصم ${pct}% — كم الصافي؟`;
        } else {
            const cost = (Math.floor(Math.random() * 8) + 2) * 500;
            const margin = [10, 20, 30][Math.floor(Math.random() * 3)];
            ans = cost + (cost * margin / 100);
            qText = `صنف تكلفته ${cost} بربح ${margin}% — كم سعر البيع؟`;
        }
        _wellnessMathAnswer = ans;
        const qEl = document.getElementById('math-game-question');
        if (qEl) qEl.textContent = qText;

        const opts = new Set([ans]);
        while (opts.size < 4) {
            const delta = (Math.floor(Math.random() * 7) - 3) * 50 || 100;
            if (ans + delta > 0) opts.add(ans + delta);
        }
        const shuffled = Array.from(opts).sort(() => Math.random() - 0.5);
        const box = document.getElementById('math-game-options');
        if (box) {
            box.innerHTML = shuffled.map(v => `
                <button type="button" onclick="answerWellnessMath(${v})" class="py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-amber-200 font-black text-base border border-amber-500/30 transition cursor-pointer">${v}</button>
            `).join('');
        }
    };

    window.answerWellnessMath = function(val) {
        const fb = document.getElementById('math-game-feedback');
        if (Number(val) === Number(_wellnessMathAnswer)) {
            _wellnessMathScore += 10;
            const scEl = document.getElementById('math-game-score');
            if (scEl) scEl.textContent = _wellnessMathScore;
            const prevBest = parseInt(localStorage.getItem('erp_wellness_math_best') || '0', 10);
            if (_wellnessMathScore > prevBest) {
                localStorage.setItem('erp_wellness_math_best', String(_wellnessMathScore));
                const bestEl = document.getElementById('math-game-best');
                if (bestEl) bestEl.textContent = _wellnessMathScore;
            }
            if (fb) fb.innerHTML = '<span class="text-emerald-400">✅ إجابة صحيحة وسريعة! (+10 نقاط)</span>';
            setTimeout(() => window.nextWellnessMathQuestion(), 500);
        } else {
            if (fb) fb.innerHTML = `<span class="text-rose-400">❌ حاول مجدداً! الإجابة الصحيحة كانت (${_wellnessMathAnswer})</span>`;
        }
    };

    window.nextAuditSpotterRound = function() {
        const baseNum = Math.floor(100000 + Math.random() * 900000);
        const prefix = ['INV-2026-', 'RV-904-', 'ACC-110-', 'PO-772-'][Math.floor(Math.random() * 4)];
        const left = prefix + baseNum;
        let right = left;
        _wellnessAuditMatch = Math.random() > 0.5;
        if (!_wellnessAuditMatch) {
            const chars = String(baseNum).split('');
            const idx = Math.floor(Math.random() * chars.length);
            chars[idx] = String((parseInt(chars[idx], 10) + 1) % 10);
            right = prefix + chars.join('');
        }
        const pairBox = document.getElementById('audit-game-pair');
        if (pairBox) {
            pairBox.innerHTML = `<span class="p-2 bg-slate-900 rounded-lg">${left}</span><span class="p-2 bg-slate-900 rounded-lg">${right}</span>`;
        }
    };

    window.answerAuditSpotter = function(userSaysMatch) {
        const fb = document.getElementById('audit-game-feedback');
        if (userSaysMatch === _wellnessAuditMatch) {
            _wellnessAuditScore += 10;
            const sc = document.getElementById('audit-game-score');
            if (sc) sc.textContent = _wellnessAuditScore;
            if (fb) fb.innerHTML = '<span class="text-emerald-400">🎯 عين صقر رقابية! ملاحظة دقيقة (+10)</span>';
        } else {
            if (fb) fb.innerHTML = '<span class="text-rose-400">⚠️ دقق النظر! كان هناك فرق في الأرقام.</span>';
        }
        setTimeout(() => window.nextAuditSpotterRound(), 600);
    };

    window.nextWellnessQuote = function() {
        const quotes = [
            '«الإتقان في العمل عبادة، وابتسامتك في وجه المراجع والعميل تفتح أبواب الرزق والبركة.»',
            '«المحاسب الناجح يجمع بين دقة الرقم، وأمانة الضمير، وهدوء الأعصاب عند ضغط الإقفال.»',
            '«راحة ذهنك لـ 5 دقائق كل ساعة تضاعف سرعة إنجازك وتمنع 90% من الأخطاء الحسابية.»',
            '«تذكر: كل قيد متوازن تضبطه اليوم هو لبنة في صرح نجاح المؤسسة واستقرارها المالي.»',
            '«طرفة محاسبية: المحاسب الوحيد الذي ينام قرير العين هو من تطابق عنده ميزان المراجعة من أول مرة! 😊»'
        ];
        const el = document.getElementById('wellness-daily-quote');
        if (el) el.textContent = quotes[Math.floor(Math.random() * quotes.length)];
    };

    // ── Fiqh Sub-Tabs Controller ──
    
    let activeHadithCat = 'all';
    function setHadithCategory(cat, btn) {
        activeHadithCat = cat;
        document.querySelectorAll('.hadith-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterHadiths();
    }

    function filterHadiths() {
        const rawQuery = (document.getElementById('hadith-search-input') ? document.getElementById('hadith-search-input').value : '').trim();
        const normQuery = typeof normalizeArabicText === 'function' ? normalizeArabicText(rawQuery) : rawQuery.toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('#hadiths-grid-container .fiqh-hadith-card').forEach(card => {
            const cat = card.getAttribute('data-hadith-cat');
            const rawSearch = card.getAttribute('data-hadith-search') || '';
            const normSearch = typeof normalizeArabicText === 'function' ? normalizeArabicText(rawSearch) : rawSearch.toLowerCase();

            const matchCat = (activeHadithCat === 'all' || cat === activeHadithCat);
            const matchSearch = (normQuery === '' || normSearch.includes(normQuery));

            if (matchCat && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noAlert = document.getElementById('no-hadiths-alert');
        if (noAlert) noAlert.classList.toggle('hidden', visibleCount > 0);
    }

    
    let activeQuranTopic = 'all';
    function setQuranTopic(cat, btn) {
        activeQuranTopic = cat;
        document.querySelectorAll('.quran-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterQuranVerses();
    }

    function filterQuranVerses() {
        const rawQuery = (document.getElementById('quran-search-input') ? document.getElementById('quran-search-input').value : '').trim();
        const normQuery = typeof normalizeArabicText === 'function' ? normalizeArabicText(rawQuery) : rawQuery.toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.fiqh-ayah-card').forEach(card => {
            const cat = card.getAttribute('data-ayah-topic');
            const rawSearch = card.getAttribute('data-ayah-search') || '';
            const normSearch = typeof normalizeArabicText === 'function' ? normalizeArabicText(rawSearch) : rawSearch.toLowerCase();

            const matchCat = (activeQuranTopic === 'all' || cat === activeQuranTopic);
            const matchSearch = (normQuery === '' || normSearch.includes(normQuery) || rawSearch.toLowerCase().includes(rawQuery.toLowerCase()));

            if (matchCat && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noAlert = document.getElementById('no-quran-alert');
        if (noAlert) noAlert.classList.toggle('hidden', visibleCount > 0);
    }

    let activeBookScholar = 'all';
    function setBookScholar(scholar, btn) {
        activeBookScholar = scholar;
        document.querySelectorAll('.book-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterBooksLibrary();
    }

    function filterBooksLibrary() {
        const query = (document.getElementById('book-search-input') ? document.getElementById('book-search-input').value : '').trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.fiqh-book-card').forEach(card => {
            const scholar = card.getAttribute('data-book-scholar');
            const search = card.getAttribute('data-book-search') || '';

            const matchScholar = (activeBookScholar === 'all' || scholar === activeBookScholar);
            const matchSearch = (query === '' || search.includes(query));

            if (matchScholar && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noAlert = document.getElementById('no-books-alert');
        if (noAlert) noAlert.classList.toggle('hidden', visibleCount > 0);
    }

    
    // 🌐 LIVE GOOGLE SCHOLAR SEARCH ENGINE CONTROLLER 🌐
    const googleScholarNamesMap = {
        'all': '("ابن باز" OR "ابن عثيمين" OR "ابن تيمية" OR "ابن القيم" OR "أحمد بن حنبل" OR "النووي" OR "الذهبي" OR "محمد بن عبد الوهاب" OR "السعدي" OR "الشنقيطي" OR "الألباني" OR "صالح الفوزان")',
        'ibn_baz': '"ابن باز"',
        'ibn_uthaymeen': '"ابن عثيمين"',
        'al_fawzan': '"صالح الفوزان"',
        'ibn_taymiyyah': '"ابن تيمية"',
        'ibn_al_qayyim': '"ابن القيم"',
        'imam_ahmad': '"أحمد بن حنبل"',
        'al_nawawi': '"النووي"',
        'al_dhahabi': '"الذهبي"',
        'muhammad_bin_abdulwahhab': '"محمد بن عبد الوهاب"',
        'al_saadi': '"السعدي"',
        'al_shinqiti': '"الشنقيطي"',
        'al_albani': '"الألباني"'
    };

    const googleSitesMap = {
        'all_approved': '(site:binbaz.org.sa OR site:ibnothaimeen.net OR site:alfawzan.af.org.sa OR site:alalbany.org OR site:shamela.ws OR site:islamweb.net OR site:alukah.net OR site:dorar.net)',
        'official_only': '(site:binbaz.org.sa OR site:ibnothaimeen.net OR site:alfawzan.af.org.sa OR site:alalbany.org)',
        'shamela': 'site:shamela.ws',
        'binbaz': 'site:binbaz.org.sa',
        'uthaymeen': 'site:ibnothaimeen.net',
        'fawzan': 'site:alfawzan.af.org.sa',
        'albany': 'site:alalbany.org',
        'dorar': 'site:dorar.net',
        'islamweb': 'site:islamweb.net',
        'alukah': 'site:alukah.net'
    };

    
    // 🌟 FIQH CLEAN SEARCH CONTROLLERS 🌟
    function executeInternalFiqhSearch() {
        switchFiqhSubTab('fatwas');
        executeLiveFilter();
        const fatwasContainer = document.getElementById('fiqh-sub-fatwas');
        if (fatwasContainer) {
            fatwasContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function executeScopedGoogle12Search() {
        const input = document.getElementById('fiqh-search-input');
        const q = input ? input.value.trim() : '';
        const query = q !== '' ? q : 'فتاوى البيوع والمعاملات المالية';
        
        const scholarSelect = document.getElementById('fiqh-scholar-select');
        const selectedScholar = scholarSelect ? scholarSelect.value : 'all';
        
        const allScholarsDork = '("ابن باز" OR "ابن عثيمين" OR "صالح الفوزان" OR "ابن تيمية" OR "ابن القيم" OR "أحمد بن حنبل" OR "النووي" OR "الذهبي" OR "محمد بن عبد الوهاب" OR "السعدي" OR "الشنقيطي" OR "الألباني")';
        
        const scholarMap = {
            'all': allScholarsDork,
            'ibn_baz': '"ابن باز"',
            'ibn_uthaymeen': '"ابن عثيمين"',
            'al_fawzan': '"صالح الفوزان"',
            'ibn_taymiyyah': '"ابن تيمية"',
            'ibn_al_qayyim': '"ابن القيم"',
            'imam_ahmad': '"أحمد بن حنبل"',
            'al_nawawi': '"النووي"',
            'al_dhahabi': '"الذهبي"',
            'muhammad_bin_abdulwahhab': '"محمد بن عبد الوهاب"',
            'al_saadi': '"السعدي"',
            'al_shinqiti': '"الشنقيطي"',
            'al_albani': '"الألباني"'
        };
        
        const scholars = scholarMap[selectedScholar] || allScholarsDork;
        const sites = '(site:binbaz.org.sa OR site:ibnothaimeen.net OR site:alfawzan.af.org.sa OR site:alalbany.org OR site:shamela.ws OR site:islamweb.net OR site:alukah.net OR site:dorar.net)';
        const fullQuery = query + ' ' + scholars + ' ' + sites;
        const googleUrl = 'https://www.google.com/search?q=' + encodeURIComponent(fullQuery);
        window.open(googleUrl, '_blank');
    }

    function clearFiqhInput() {
        const input = document.getElementById('fiqh-search-input');
        if (input) {
            input.value = '';
            input.focus();
        }
        executeLiveFilter();
    }

    function executeHeaderGoogleSearch() {
        const q = (document.getElementById('google_quick_input') ? document.getElementById('google_quick_input').value : '').trim();
        if (!q) {
            alert('يرجى كتابة مسألتك أو سؤالك أولاً للبحث في قوقل.');
            if (document.getElementById('google_quick_input')) document.getElementById('google_quick_input').focus();
            return;
        }
        const scholars = '("ابن باز" OR "ابن عثيمين" OR "صالح الفوزان" OR "ابن تيمية" OR "ابن القيم" OR "أحمد بن حنبل" OR "النووي" OR "الذهبي" OR "محمد بن عبد الوهاب" OR "السعدي" OR "الشنقيطي" OR "الألباني")';
        const sites = '(site:binbaz.org.sa OR site:ibnothaimeen.net OR site:alfawzan.af.org.sa OR site:alalbany.org OR site:shamela.ws OR site:islamweb.net OR site:alukah.net OR site:dorar.net)';
        const fullQuery = q + ' ' + scholars + ' ' + sites;
        window.open('https://www.google.com/search?q=' + encodeURIComponent(fullQuery), '_blank');
    }

    function executeGoogleScholarSearch() {
        const queryInput = (document.getElementById('google-fiqh-query') ? document.getElementById('google-fiqh-query').value : '').trim();
        if (!queryInput) {
            alert('يرجى كتابة كلمة البحث أو السؤال الفقهي أولاً للبحث في قوقل.');
            if (document.getElementById('google-fiqh-query')) document.getElementById('google-fiqh-query').focus();
            return;
        }

        const scholarKey = document.getElementById('google-scholar-select') ? document.getElementById('google-scholar-select').value : 'all';
        const sitesKey = document.getElementById('google-sites-select') ? document.getElementById('google-sites-select').value : 'all_approved';

        const scholarDork = googleScholarNamesMap[scholarKey] || googleScholarNamesMap['all'];
        const sitesDork = googleSitesMap[sitesKey] || googleSitesMap['all_approved'];

        const fullSearchQuery = queryInput + ' ' + scholarDork + ' ' + sitesDork;
        const googleUrl = 'https://www.google.com/search?q=' + encodeURIComponent(fullSearchQuery);
        window.open(googleUrl, '_blank');
    }

    function quickGoogleSearch(phrase) {
        if (document.getElementById('google-fiqh-query')) {
            document.getElementById('google-fiqh-query').value = phrase;
        }
        executeGoogleScholarSearch();
    }

    function executeQuickGoogleSearch() {
        const queryInput = (document.getElementById('fiqh-search-input') ? document.getElementById('fiqh-search-input').value : '').trim();
        const baseQuery = queryInput !== '' ? queryInput : 'فتاوى البيوع والمعاملات المالية';
        const fullSearchQuery = baseQuery + ' ' + googleScholarNamesMap['all'] + ' ' + googleSitesMap['all_approved'];
        const googleUrl = 'https://www.google.com/search?q=' + encodeURIComponent(fullSearchQuery);
        window.open(googleUrl, '_blank');
    }

    let activeMaximCat = 'all';
    function setMaximCategory(cat, btn) {
        activeMaximCat = cat;
        document.querySelectorAll('.maxim-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterMaxims();
    }

    function filterMaxims() {
        const rawQuery = (document.getElementById('maxims-search-input') ? document.getElementById('maxims-search-input').value : '').trim();
        const normQuery = typeof normalizeArabicText === 'function' ? normalizeArabicText(rawQuery) : rawQuery.toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.fiqh-maxim-card').forEach(card => {
            const cat = card.getAttribute('data-maxim-cat');
            const rawSearch = card.getAttribute('data-maxim-search') || '';
            const normSearch = typeof normalizeArabicText === 'function' ? normalizeArabicText(rawSearch) : rawSearch.toLowerCase();

            const matchCat = (activeMaximCat === 'all' || cat === activeMaximCat);
            const matchSearch = (normQuery === '' || normSearch.includes(normQuery));

            if (matchCat && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noAlert = document.getElementById('no-maxims-alert');
        if (noAlert) noAlert.classList.toggle('hidden', visibleCount > 0);
    }

    function filterTerms() {
        const query = (document.getElementById('terms-search-input') ? document.getElementById('terms-search-input').value : '').trim().toLowerCase();
        document.querySelectorAll('.fiqh-term-card').forEach(card => {
            const text = (card.innerText || card.textContent || '').toLowerCase();
            if (query === '' || text.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function switchFiqhSubTab(subId, el) {
        const allSubIds = ['fatwas', 'quran', 'hadiths', 'books', 'maxims', 'terms'];
        allSubIds.forEach(id => {
            const sec = document.getElementById('fiqh-sub-' + id);
            if (sec) sec.classList.add('hidden');
        });

        document.querySelectorAll('.fiqh-nav-pill').forEach(btn => btn.classList.remove('active'));

        const target = document.getElementById('fiqh-sub-' + subId);
        if (target) {
            target.classList.remove('hidden');
        }

        if (el) {
            el.classList.add('active');
        } else {
            const matchingBtn = document.querySelector(`.fiqh-nav-pill[onclick*="'${subId}'"]`);
            if (matchingBtn) matchingBtn.classList.add('active');
        }

        if (subId === 'fatwas') {
            const grid = document.getElementById('fiqh_fatwas_grid');
            if (grid && (!grid.children || grid.children.length === 0) && typeof initFiqhEngine === 'function') {
                initFiqhEngine();
            }
        }

        setTimeout(() => {
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 50);
    }

    // ════════════════════════════════════════════════════════════════════════════

    // ════════════════════════════════════════════════════════════════════════════
    // 🌟 MASTER 4,500 FATWAS ENGINE (EXACT SCOPED FILTERING & ZERO LAG) 🌟
    // ════════════════════════════════════════════════════════════════════════════
    const PAGE_SIZE = 20;
    let currentRulingFilter = 'all';
    let fiqhCardFontScale = 1.0;
    let currentPage = 1;
    let currentScholarFilter = 'all';
    let currentTopicFilter = 'all';
    let currentFilteredList = [];
    let searchDebounceTimer = null;
    let fiqhLoadingPromise = null;
    let currentActiveButtonKey = null;
    let currentActiveButtonTitle = '';

    // 🌟 ARABIC TEXT NORMALIZER & PREFIX STRIPPER (FOR ACCURATE FREE SEARCH) 🌟
    function normalizeArabicText(text) {
        if (!text) return '';
        return text.toString()
            .replace(/[\u064B-\u065F\u0670]/g, '') // remove tashkeel
            .replace(/[إأآا]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي')
            .toLowerCase()
            .trim();
    }

    function cleanToken(t) {
        let s = t;
        if (s.startsWith('و') && s.length > 3) s = s.slice(1);
        if (s.startsWith('بال') && s.length > 4) s = s.slice(3);
        else if (s.startsWith('ال') && s.length > 3) s = s.slice(2);
        else if (s.startsWith('ب') && s.length > 3) s = s.slice(1);
        else if (s.startsWith('ل') && s.length > 3) s = s.slice(1);
        return s;
    }

    // 🌟 36 BUTTON SPECIFIC TOPIC DEFINITIONS (EXACT FILTERING FOR EACH BUTTON) 🌟
    const FIQH_BUTTON_FILTERS = {
        // 1. التمويل والتقسيط والديون
        'installment': { title: 'البيع بالتقسيط وزيادة الأجل', filter: (item) => item.topic_code === 'installments' },
        'penalties': { title: 'غرامات التأخير والشرط الجزائي', filter: (item) => item.topic_code === 'penalties' },
        'murabaha': { title: 'المرابحة للآمر بالشراء', filter: (item) => item.topic_code === 'installments' || (item._normSearchStr && item._normSearchStr.includes('مرابحه')) },
        'tawarruq': { title: 'التورق للحصول على سيولة', filter: (item) => item.topic_code === 'riba' || (item._normSearchStr && item._normSearchStr.includes('تورق')) },
        'leasing': { title: 'الإيجار المنتهي بالتمليك', filter: (item) => item.topic_code === 'leasing' },
        'insolvency': { title: 'إنظار المعسر والتيسير في السداد', filter: (item) => item.topic_code === 'bankruptcy' || (item._normSearchStr && item._normSearchStr.includes('معسر')) },

        // 2. مقاولات السلع والضمان
        'unowned_sale': { title: 'بيع ما لا يملك والوساطة التجارية', filter: (item) => item.topic_code === 'brokerage' },
        'istisna_supply': { title: 'عقد الاستصناع وتوريد البضائع', filter: (item) => item.topic_code === 'contracts' || (item._normSearchStr && (item._normSearchStr.includes('استصناع') || item._normSearchStr.includes('توريد'))) },
        'scrap': { title: 'بيع حديد السكراب والخردة', filter: (item) => item.topic_code === 'brokerage' || (item._normSearchStr && (item._normSearchStr.includes('سكراب') || item._normSearchStr.includes('خرده'))) },
        'defects': { title: 'خيار العيب والضمان المصنعي', filter: (item) => item.topic_code === 'defects' },
        'workers_wages': { title: 'أجور العمال وحقوق الأجراء', filter: (item) => item.topic_code === 'charity' },
        'pricing': { title: 'التسعير وتطفيف الكيل والميزان', filter: (item) => item.topic_code === 'pricing' },

        // 3. الذهب والعملات والتحويلات
        'gold_mada': { title: 'شراء الذهب ببطاقات الصراف ومدى', filter: (item) => item.topic_code === 'gold' },
        'bankak_fawry': { title: 'الصرف بتطبيقات بنكك وفوري', filter: (item) => (item._normSearchStr && (item._normSearchStr.includes('بنكك') || item._normSearchStr.includes('فوري'))) || (item.topic_code === 'exchange' && item._normSearchStr && (item._normSearchStr.includes('تطبيق') || item._normSearchStr.includes('محفظ') || item._normSearchStr.includes('تحويل'))) },
        'gold_exchange': { title: 'بيع الذهب القديم بالجديد والمصنعية', filter: (item) => item.topic_code === 'gold' },
        'immediate_possession': { title: 'التقابض الفوري في مجلس عقد الصرف', filter: (item) => item.topic_code === 'exchange' },
        'riba_inah': { title: 'حيل الربا وبيوع العِينة', filter: (item) => item.topic_code === 'riba' },
        'crypto_forex': { title: 'العملات الرقمية والفوركس', filter: (item) => (item._normSearchStr && (item._normSearchStr.includes('رقمي') || item._normSearchStr.includes('فوركس') || item._normSearchStr.includes('بيتكوين'))) || item.topic_code === 'digital_assets' },

        // 4. الأوراق التجارية والمدفوعات
        'checks': { title: 'خصم الشيكات والكمبيالات الآجلة', filter: (item) => item.topic_code === 'checks' },
        'earnest_money': { title: 'بيع العربون وحجز البضاعة', filter: (item) => item.topic_code === 'contracts' || (item._normSearchStr && item._normSearchStr.includes('عربون')) },
        'debt_sale': { title: 'بيع الدين والمقاصة المالية', filter: (item) => item.topic_code === 'checks' || item.topic_code === 'installments' },
        'iqala': { title: 'إقالة النادم والفسخ الرضائي', filter: (item) => item.topic_code === 'contracts' },
        'salam': { title: 'بيع السلم ودفع الثمن مقدماً', filter: (item) => item.topic_code === 'contracts' || item.topic_code === 'letters_of_credit' },
        'contracts_terms': { title: 'العقود والشروط وحرية الاتفاق', filter: (item) => item.topic_code === 'contracts' },

        // 5. جميع مسائل المعاملات المالية (الموسوعة الشاملة)
        'stocks': { title: 'الشركات والأسهم والمضاربة', filter: (item) => item.topic_code === 'companies' },
        'ecommerce': { title: 'التجارة الإلكترونية ونقاط البيع POS', filter: (item) => item.topic_code === 'ecommerce' },
        'insurance': { title: 'التأمين التعاوني والتكافلي', filter: (item) => item.topic_code === 'insurance' },
        'guarantee': { title: 'الكفالة والضمان الشخصي', filter: (item) => item.topic_code === 'guarantee' },
        'franchise': { title: 'الامتياز التجاري (الفرنشايز)', filter: (item) => item.topic_code === 'franchise' },
        'letters_of_credit': { title: 'الاعتمادات والشحن الدولي', filter: (item) => item.topic_code === 'letters_of_credit' },
        'zakat': { title: 'الزكاة وعروض التجارة والديون', filter: (item) => item.topic_code === 'zakat' },
        'pawn': { title: 'الرهن الحيازي والتجاري', filter: (item) => item.topic_code === 'pawn' },
        'emergency': { title: 'الجوائح والظروف الطارئة', filter: (item) => item.topic_code === 'emergency' },
        'bankruptcy': { title: 'التفليس وقسمة الغرماء', filter: (item) => item.topic_code === 'bankruptcy' },
        'loyalty_points': { title: 'نقاط المكافآت وبطاقات الولاء', filter: (item) => item.topic_code === 'timeshare_realestate' || (item._normSearchStr && (item._normSearchStr.includes('مكافا') || item._normSearchStr.includes('ولاء'))) },
        'crowdfunding': { title: 'التمويل الجماعي والاستثماري', filter: (item) => item.topic_code === 'crowdfunding' }
    };

    function getMasterFatwas() {
        return window.MASTER_20000_FATWAS || window.MASTER_5500_FATWAS || [];
    }

    function indexFatwasDataset() {
        const dataset = getMasterFatwas();
        if (!dataset || dataset.length === 0) return;
        for (let i = 0; i < dataset.length; i++) {
            const item = dataset[i];
            if (!item._normSearchStr) {
                item._normSearchStr = normalizeArabicText(
                    (item.question || '') + ' ' +
                    (item.summary || '') + ' ' +
                    (item.evidence || '') + ' ' +
                    (item.scholar_name || '') + ' ' +
                    (item.topic_name || '') + ' ' +
                    (item.tags || '') + ' ' +
                    (item.ruling || '') + ' ' +
                    (item.source || '')
                );
            }
        }
    }

    function ensureFiqhLoaded(callback) {
        if (window.MASTER_20000_FATWAS && Array.isArray(window.MASTER_20000_FATWAS) && window.MASTER_20000_FATWAS.length > 0) {
            indexFatwasDataset();
            if (callback) callback();
            return Promise.resolve();
        }

        if (fiqhLoadingPromise) {
            return fiqhLoadingPromise.then(() => {
                indexFatwasDataset();
                if (callback) callback();
            });
        }

        const grid = document.getElementById('fiqh_fatwas_grid');
        const badge = document.getElementById('fiqh_search_badge');
        if (badge) {
            badge.innerHTML = '<i class="fa-solid fa-cloud-arrow-down text-amber-400 animate-pulse"></i> جاري تجهيز الموسوعة (4,500 فتوى)...';
        }

        fiqhLoadingPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'js/fiqh_20000_data.js?v=4500_v2';
            script.async = true;
            script.onload = () => {
                indexFatwasDataset();
                resolve();
            };
            script.onerror = (err) => {
                console.error('Failed to load fiqh dataset', err);
                if (grid) {
                    grid.innerHTML = `
                        <div class="col-span-full py-10 px-4 text-center bg-rose-950/60 border border-rose-500/50 rounded-xl space-y-2">
                            <i class="fa-solid fa-triangle-exclamation text-3xl text-rose-400"></i>
                            <div class="text-white font-black">تعذر تحميل بيانات الفتاوى، يرجى التحقق من الاتصال بالإنترنت والمحاولة مجدداً.</div>
                            <button type="button" onclick="ensureFiqhLoaded(() => initFiqhEngine())" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-black">إعادة المحاولة</button>
                        </div>
                    `;
                }
                reject(err);
            };
            document.body.appendChild(script);
        }).then(() => {
            if (callback) callback();
        });

        return fiqhLoadingPromise;
    }

    function initFiqhEngine() {
        ensureFiqhLoaded(() => {
            const input = document.getElementById('fiqh-search-input');
            if (input && input.value.trim() !== '') {
                executeLiveFilter();
            } else {
                currentActiveButtonKey = null;
                currentActiveButtonTitle = '';
                currentFilteredList = getMasterFatwas();
                currentPage = 1;
                renderFatwaCards();
            }
        });
    }

    function renderFatwaCards() {
        const grid = document.getElementById('fiqh_fatwas_grid');
        const badge = document.getElementById('fiqh_search_badge');
        if (!grid) return;

        const totalAll = getMasterFatwas().length || 4500;
        const totalMatches = currentFilteredList.length;
        const totalPages = Math.max(1, Math.ceil(totalMatches / PAGE_SIZE));

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        if (badge) {
            let rulingLabel = '';
            if (currentRulingFilter === 'halal') rulingLabel = ' (المباح والجائز فقط)';
            else if (currentRulingFilter === 'haram') rulingLabel = ' (المحرم والباطل فقط)';
            else if (currentRulingFilter === 'conditional') rulingLabel = ' (المقيد بضوابط فقط)';
            else if (currentRulingFilter === 'wajib') rulingLabel = ' (الواجب والملزم فقط)';

            if (totalMatches === totalAll) {
                badge.innerHTML = `<i class="fa-solid fa-layer-group text-emerald-400"></i> إجمالي <strong>${totalAll.toLocaleString()}</strong> فتوى موثقة${rulingLabel} | صفحة <strong>${currentPage.toLocaleString()}</strong> من <strong>${totalPages.toLocaleString()}</strong> (20 فتوى في الصفحة)`;
            } else if (currentActiveButtonTitle) {
                badge.innerHTML = `<i class="fa-solid fa-filter text-amber-400"></i> عُثر على <strong>${totalMatches.toLocaleString()}</strong> فتوى تخص: <span class="text-amber-300 font-bold">${currentActiveButtonTitle}</span>${rulingLabel} | صفحة <strong>${currentPage.toLocaleString()}</strong> من <strong>${totalPages.toLocaleString()}</strong> (20 فتوى في الصفحة)`;
            } else {
                badge.innerHTML = `<i class="fa-solid fa-filter text-amber-400"></i> عُثر على <strong>${totalMatches.toLocaleString()}</strong> من أصل ${totalAll.toLocaleString()} فتوى${rulingLabel} | صفحة <strong>${currentPage.toLocaleString()}</strong> من <strong>${totalPages.toLocaleString()}</strong> (20 فتوى في الصفحة)`;
            }
        }

        if (totalMatches === 0) {
            grid.innerHTML = `
                <div class="col-span-full p-10 bg-slate-900/90 border-2 border-dashed border-amber-500/40 rounded-3xl text-center space-y-3">
                    <span class="text-4xl">🔍</span>
                    <h4 class="text-lg font-black text-amber-300">لم يتم العثور على نتائج مطابقة لبحثك في موسوعة الفتاوى</h4>
                    <p class="text-xs text-slate-300 font-bold">جرب كتابة كلمة أخرى مثل: تقسيط، شيك، ذهب، عربون، توريد، أو قم بتغيير تصفية الحكم الشرعي والشيوخ.</p>
                    <button type="button" onclick="clearFiqhSearch()" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs rounded-xl shadow-lg transition">
                        ✕ إلغاء الفلترة وتصفح كافة الفتاوى
                    </button>
                </div>
            `;
            updatePaginationBarHtml('fiqh_pagination_bar_top', totalPages, 0, 0, 0);
            updatePaginationBarHtml('fiqh_pagination_bar_bottom', totalPages, 0, 0, 0);
            return;
        }

        // Get current search query for text highlighting
        const searchInput = document.getElementById('fiqh-search-input');
        const activeSearchQuery = searchInput ? searchInput.value.trim() : '';

        // Slice exactly 20 items for current page
        const startIndex = (currentPage - 1) * PAGE_SIZE;
        const endIndex = Math.min(startIndex + PAGE_SIZE, totalMatches);
        const pageItems = currentFilteredList.slice(startIndex, endIndex);

        let html = '';
        pageItems.forEach((item) => {
            let rulingClass = 'bg-emerald-500/25 text-emerald-200 border-2 border-emerald-500/60 shadow-md';
            let borderAccentClass = 'border-r-[8px] border-r-emerald-500';
            let topGlowClass = 'via-emerald-400/50';

            if (item.ruling.includes('محرم') || item.ruling.includes('باطل') || item.ruling.includes('لا يجوز') || item.ruling.includes('كبيرة') || item.ruling.includes('سحت')) {
                rulingClass = 'bg-rose-500/25 text-rose-200 border-2 border-rose-500/60 shadow-md';
                borderAccentClass = 'border-r-[8px] border-r-rose-500';
                topGlowClass = 'via-rose-400/50';
            } else if (item.ruling.includes('ضوابط') || item.ruling.includes('مشروطة') || item.ruling.includes('مكروه') || item.ruling.includes('تحري')) {
                rulingClass = 'bg-amber-500/25 text-amber-200 border-2 border-amber-500/60 shadow-md';
                borderAccentClass = 'border-r-[8px] border-r-amber-500';
                topGlowClass = 'via-amber-400/50';
            } else if (item.ruling.includes('واجب') || item.ruling.includes('لازم') || item.ruling.includes('فرض')) {
                rulingClass = 'bg-cyan-500/25 text-cyan-200 border-2 border-cyan-500/60 shadow-md';
                borderAccentClass = 'border-r-[8px] border-r-cyan-500';
                topGlowClass = 'via-cyan-400/50';
            } else if (item.ruling.includes('مستحب') || item.ruling.includes('ندب') || item.ruling.includes('سنة')) {
                rulingClass = 'bg-teal-500/25 text-teal-200 border-2 border-teal-500/60 shadow-md';
                borderAccentClass = 'border-r-[8px] border-r-teal-500';
                topGlowClass = 'via-teal-400/50';
            }

            // Highlighting search terms
            const highlightedQuestion = highlightSearchKeywords(item.question, activeSearchQuery);
            const highlightedSummary = highlightSearchKeywords(item.summary, activeSearchQuery);
            const highlightedEvidence = item.evidence ? highlightSearchKeywords(item.evidence, activeSearchQuery) : '';

            // 🌟 مستطيل الحديث والأثر: خط كبير جداً وواضح وعنوان بارز وهوامش مريحة ويكبر ويتحرك لزيادة التركيز 🌟
            const hadithBox = item.evidence ? `
                <div class="fiqh-hadith-card bg-gradient-to-r from-amber-950/60 via-slate-900/95 to-amber-950/50 border-2 border-amber-500/45 border-r-[6px] border-r-amber-400 shadow-lg select-text" title="الأثر والدليل الشرعي من السنة الصحيحة والقرآن الكريم (يكبر ويتحرك بتمرير المؤشر)">
                    <div class="flex items-center justify-between gap-3 border-b border-amber-500/20 pb-3 mb-3.5 px-2">
                        <div class="flex items-center gap-3 text-sm sm:text-base md:text-lg font-black text-amber-300">
                            <span class="hadith-icon-bubble w-10 h-10 rounded-full bg-amber-500/20 border border-amber-400/50 flex items-center justify-center text-amber-300 shadow-inner flex-shrink-0">
                                <i class="fa-solid fa-book-quran text-base"></i>
                            </span>
                            <span>الأثر والدليل من السنة الصحيحة والقرآن الكريم:</span>
                        </div>
                        <span class="text-xs sm:text-sm font-black text-amber-300 bg-amber-500/20 px-4 py-1.5 rounded-full border border-amber-400/40 flex items-center gap-1.5 shadow-sm flex-shrink-0">
                            <i class="fa-solid fa-sparkles text-xs text-amber-400"></i> سند وحجة شرعية
                        </span>
                    </div>
                    <!-- نص الحديث والآية بخط ملكي عريض وضخم جداً وهوامش مريحة -->
                    <div class="px-4 sm:px-6 py-3 text-xl sm:text-2xl md:text-[27px] text-amber-100 font-black leading-loose font-serif select-text" style="font-family: 'Amiri', 'Cairo', serif; font-size: 1.6rem; line-height: 2.4; letter-spacing: 0.4px; color: #fef3c7; text-shadow: 0 1px 2px rgba(0,0,0,0.5); text-align: justify;">
                        ${highlightedEvidence}
                    </div>
                </div>
            ` : '';

            // 🌟 بطاقة الفتوى بحواف دائرية وشريط إدراك بصري جانبي وزري نسخ ومشاركة عبر واتساب 🌟
            html += `
                <div class="fiqh-fatwa-card border-2 border-slate-800/80 ${borderAccentClass} flex flex-col justify-between group min-h-[460px] relative space-y-6">
                    <!-- خط توهج علوي انسيابي ناعم بلون نوع الحكم -->
                    <div class="absolute top-0 right-0 left-0 h-1.5 bg-gradient-to-r from-transparent ${topGlowClass} to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-t-[36px]"></div>
                    
                    <div class="space-y-6">
                        <!-- Top Header: Ruling Badge, ID, and Action Buttons (Copy & WhatsApp) -->
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800/90 pb-4 px-2">
                            <div class="flex items-center gap-2.5">
                                <span class="px-5 sm:px-6 py-2 sm:py-2.5 text-sm sm:text-base md:text-lg font-black rounded-full ${rulingClass} backdrop-blur-sm">
                                    ${item.ruling}
                                </span>
                                <span class="text-sm sm:text-base font-mono text-amber-400 font-black px-4 py-2 bg-amber-500/15 rounded-full border border-amber-500/40 shadow-sm">
                                    #${item.id}
                                </span>
                            </div>

                            <!-- أزرار الإجراءات السريعة: نسخ الفتوى ومشاركتها عبر واتساب -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button type="button" onclick="copyFatwaCard(${item.id})" class="px-3.5 py-2 bg-slate-800 hover:bg-amber-500/25 text-slate-200 hover:text-amber-300 font-black text-xs sm:text-sm rounded-full border border-slate-700 hover:border-amber-400/60 shadow-sm transition-all duration-300 flex items-center gap-1.5" title="نسخ نص الفتوى والأثر الشرعي كاملاً للحافظة">
                                    <i class="fa-solid fa-copy text-amber-400"></i>
                                    <span>نسخ</span>
                                </button>
                                <button type="button" onclick="shareFatwaWhatsApp(${item.id})" class="px-3.5 py-2 bg-emerald-950/60 hover:bg-emerald-600/30 text-emerald-300 hover:text-emerald-200 font-black text-xs sm:text-sm rounded-full border border-emerald-500/40 hover:border-emerald-400 shadow-sm transition-all duration-300 flex items-center gap-1.5" title="مشاركة الفتوى مباشرة عبر واتساب">
                                    <i class="fa-brands fa-whatsapp text-emerald-400 text-sm"></i>
                                    <span>واتساب</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Question (عنوان ومسألة الفتوى بخط بارز وكبير جداً ومريح) -->
                        <h4 class="px-3 sm:px-4 py-1 text-lg sm:text-xl md:text-2xl lg:text-[24px] font-black text-white group-hover:text-amber-300 transition-colors leading-relaxed" style="font-family: 'Cairo', sans-serif; line-height: 1.9;">
                            ${highlightedQuestion}
                        </h4>
                        
                        ${hadithBox}

                        <!-- Summary (نص الفتوى والجواب بخط كبير وواضح وهوامش داخلية فسيحة) -->
                        <p class="fiqh-summary-box text-sm sm:text-base md:text-lg text-slate-100 font-bold bg-slate-900/85 hover:bg-slate-900 border border-slate-800/80 hover:border-slate-700/80 shadow-inner transition-colors duration-300" style="line-height: 2.15 !important;">
                            ${highlightedSummary}
                        </p>
                    </div>

                    <!-- Bottom Metadata: الشيخ والمصدر كاملين بأزرار كبسولية عريضة وخطوط بارزة وكبيرة -->
                    <div class="mt-6 pt-5 border-t border-slate-800/90 flex flex-wrap items-center justify-between gap-4 px-2 text-sm sm:text-base font-black">
                        <span class="text-amber-300 font-black flex items-center gap-3 px-6 py-3 bg-amber-500/15 hover:bg-amber-500/25 rounded-full border-2 border-amber-500/40 hover:border-amber-400 shadow-md transition-all duration-300">
                            <i class="fa-solid fa-user-tie text-amber-400 text-base sm:text-lg"></i>
                            <span>${item.scholar_name}</span>
                        </span>
                        <span class="text-sky-200 font-bold flex items-center gap-3 px-6 py-3 bg-sky-950/80 hover:bg-sky-950 rounded-full border-2 border-sky-500/40 hover:border-sky-400 shadow-md transition-all duration-300" title="${item.source}">
                            <i class="fa-solid fa-book-bookmark text-sky-400 text-base sm:text-lg"></i>
                            <span>${item.source}</span>
                        </span>
                    </div>
                </div>
            `;
        });

        grid.innerHTML = html;

        // Render Pagination Bars
        updatePaginationBarHtml('fiqh_pagination_bar_top', totalPages, totalMatches, startIndex + 1, endIndex);
        updatePaginationBarHtml('fiqh_pagination_bar_bottom', totalPages, totalMatches, startIndex + 1, endIndex);
    }

    function updatePaginationBarHtml(containerId, totalPages, totalMatches, fromItem, toItem) {
        const bar = document.getElementById(containerId);
        if (!bar) return;

        if (totalMatches === 0) {
            bar.innerHTML = '<div class="text-xs text-slate-400 font-bold w-full text-center">لا توجد صفحات للعرض</div>';
            return;
        }

        let pagesHtml = '';
        const maxButtons = 5;
        let startP = Math.max(1, currentPage - 2);
        let endP = Math.min(totalPages, startP + maxButtons - 1);
        if (endP - startP < maxButtons - 1) {
            startP = Math.max(1, endP - maxButtons + 1);
        }

        if (startP > 1) {
            pagesHtml += `<button type="button" onclick="goToFiqhPage(1)" class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 hover:border-amber-500 text-slate-300 text-xs font-black transition">1</button>`;
            if (startP > 2) {
                pagesHtml += `<span class="text-slate-500 text-xs px-1">...</span>`;
            }
        }

        for (let p = startP; p <= endP; p++) {
            const isActive = (p === currentPage);
            const activeClasses = isActive 
                ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 font-black shadow-md shadow-amber-500/20 border-amber-400' 
                : 'bg-slate-900 border border-slate-800 hover:border-amber-500 text-slate-300 font-bold';
            pagesHtml += `<button type="button" onclick="goToFiqhPage(${p})" class="w-8 h-8 rounded-lg text-xs transition ${activeClasses}">${p}</button>`;
        }

        if (endP < totalPages) {
            if (endP < totalPages - 1) {
                pagesHtml += `<span class="text-slate-500 text-xs px-1">...</span>`;
            }
            pagesHtml += `<button type="button" onclick="goToFiqhPage(${totalPages})" class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 hover:border-amber-500 text-slate-300 text-xs font-black transition">${totalPages}</button>`;
        }

        bar.innerHTML = `
            <div class="flex flex-wrap items-center justify-between gap-3 w-full sm:w-auto">
                <span class="text-xs sm:text-sm text-amber-300 font-black">
                    عرض المسائل (${fromItem.toLocaleString()} - ${toItem.toLocaleString()}) من أصل ${totalMatches.toLocaleString()} مسألة
                </span>
                <button type="button" onclick="executeScopedGoogle12Search()" class="px-3.5 py-1.5 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-black text-xs sm:text-sm rounded-xl shadow-md border border-sky-400/50 flex items-center gap-2 transition transform hover:-translate-y-0.5" title="البحث في فتاوى الشيوخ والعلماء الـ 12 المعتمدين فقط عبر قوقل">
                    <i class="fa-brands fa-google text-white text-sm"></i>
                    <span>بحث قوقل (الشيوخ الـ 12 فقط)</span>
                </button>
            </div>

            <div class="flex items-center gap-1.5 flex-wrap justify-center">
                <button type="button" onclick="goToFiqhPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled class="opacity-40 cursor-not-allowed px-3 py-1.5 bg-slate-900 text-slate-400 text-xs font-black rounded-lg border border-slate-800"' : 'class="px-3 py-1.5 bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-amber-300 text-xs font-black rounded-lg border border-slate-800 hover:border-amber-400 transition shadow-sm"'}>
                    <i class="fa-solid fa-chevron-right"></i> السابق
                </button>
                ${pagesHtml}
                <button type="button" onclick="goToFiqhPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled class="opacity-40 cursor-not-allowed px-3 py-1.5 bg-slate-900 text-slate-400 text-xs font-black rounded-lg border border-slate-800"' : 'class="px-3 py-1.5 bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-amber-300 text-xs font-black rounded-lg border border-slate-800 hover:border-amber-400 transition shadow-sm"'}>
                    التالي <i class="fa-solid fa-chevron-left"></i>
                </button>
            </div>
        `;
    }

    function goToFiqhPage(page) {
        const totalMatches = currentFilteredList.length;
        const totalPages = Math.max(1, Math.ceil(totalMatches / PAGE_SIZE));
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderFatwaCards();
        const el = document.getElementById('fiqh_pagination_bar_top');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function filterFatwas() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            executeLiveFilter();
        }, 50); // Instant 50ms response
    }

    function matchesRulingFilter(item) {
        if (currentRulingFilter === 'all') return true;
        const r = item.ruling || '';
        const isHaram = r.includes('محرم') || r.includes('باطل') || r.includes('لا يجوز') || r.includes('كبيرة') || r.includes('سحت');
        
        if (currentRulingFilter === 'halal') {
            return !isHaram && (r.includes('جائز') || r.includes('مباح') || r.includes('مشروع') || r.includes('صحيح') || r.includes('مستحب') || r.includes('ندب') || r.includes('سنة'));
        }
        if (currentRulingFilter === 'haram') {
            return isHaram;
        }
        if (currentRulingFilter === 'conditional') {
            return r.includes('ضوابط') || r.includes('مشروطة') || r.includes('مكروه') || r.includes('تحري');
        }
        if (currentRulingFilter === 'wajib') {
            return r.includes('واجب') || r.includes('لازم') || r.includes('فرض');
        }
        return true;
    }

    function executeLiveFilter() {
        ensureFiqhLoaded(() => {
            const input = document.getElementById('fiqh-search-input');
            const rawQ = input ? input.value.trim() : '';

            // If user cleared input, reset button filter
            if (!rawQ) {
                currentActiveButtonKey = null;
                currentActiveButtonTitle = '';
                const allBtn = document.getElementById('btn-show-all-fiqh');
                if (allBtn) {
                    document.querySelectorAll('.fiqh-pill-btn').forEach(b => b.classList.remove('active'));
                    allBtn.classList.add('active');
                }
            } else if (currentActiveButtonTitle && rawQ !== currentActiveButtonTitle) {
                // User is actively editing text away from clicked button title
                currentActiveButtonKey = null;
                currentActiveButtonTitle = '';
                document.querySelectorAll('.fiqh-pill-btn').forEach(b => b.classList.remove('active'));
            }

            const dataset = getMasterFatwas();
            currentPage = 1;

            if (currentActiveButtonKey && FIQH_BUTTON_FILTERS[currentActiveButtonKey]) {
                const btnDef = FIQH_BUTTON_FILTERS[currentActiveButtonKey];
                currentFilteredList = dataset.filter(item => {
                    if (currentScholarFilter !== 'all' && item.scholar_code !== currentScholarFilter) return false;
                    if (!matchesRulingFilter(item)) return false;
                    return btnDef.filter(item);
                });
            } else if (rawQ) {
                // Free text search with smart token matching and Arabic prefix stripping
                const normQ = normalizeArabicText(rawQ);
                const rawTokens = normQ.split(/\s+/).filter(t => t.length > 0);
                const cleanTokens = rawTokens.map(cleanToken).filter(t => t.length > 1);

                // 1. Strict match on raw tokens
                let matches = dataset.filter(item => {
                    if (currentScholarFilter !== 'all' && item.scholar_code !== currentScholarFilter) return false;
                    if (!matchesRulingFilter(item)) return false;
                    const s = item._normSearchStr || '';
                    return rawTokens.every(t => s.includes(t));
                });

                // 2. Strict match on cleaned tokens (without ال, و, بال prefixes)
                if (matches.length === 0 && cleanTokens.length > 0) {
                    matches = dataset.filter(item => {
                        if (currentScholarFilter !== 'all' && item.scholar_code !== currentScholarFilter) return false;
                        if (!matchesRulingFilter(item)) return false;
                        const s = item._normSearchStr || '';
                        return cleanTokens.every(t => s.includes(t));
                    });
                }

                // 3. Fallback: match any significant token
                if (matches.length === 0 && cleanTokens.length > 1) {
                    const stopWords = ['في', 'من', 'عن', 'على', 'الي', 'الى', 'مع', 'او', 'و', 'ما', 'هل', 'حكم'];
                    const sig = cleanTokens.filter(t => !stopWords.includes(t) && t.length > 2);
                    if (sig.length > 0) {
                        matches = dataset.filter(item => {
                            if (currentScholarFilter !== 'all' && item.scholar_code !== currentScholarFilter) return false;
                            if (!matchesRulingFilter(item)) return false;
                            const s = item._normSearchStr || '';
                            return sig.some(t => s.includes(t));
                        });
                    }
                }

                currentFilteredList = matches;
            } else {
                currentFilteredList = dataset.filter(item => {
                    if (currentScholarFilter !== 'all' && item.scholar_code !== currentScholarFilter) return false;
                    if (!matchesRulingFilter(item)) return false;
                    return true;
                });
            }

            renderFatwaCards();
        });
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 🌟 36 BUTTON CONTROLLER & SCHOLAR SELECT CONTROLLERS 🌟
    // ════════════════════════════════════════════════════════════════════════════
    function filterByFiqhButton(btnKey, el) {
        ensureFiqhLoaded(() => {
            currentActiveButtonKey = btnKey;
            const filterDef = FIQH_BUTTON_FILTERS[btnKey];
            if (!filterDef) return;

            currentActiveButtonTitle = filterDef.title;

            const input = document.getElementById('fiqh-search-input');
            if (input) {
                input.value = filterDef.title;
            }

            // Update active state on buttons
            document.querySelectorAll('.fiqh-pill-btn').forEach(btn => btn.classList.remove('active'));
            if (el) el.classList.add('active');

            const allBtn = document.getElementById('btn-show-all-fiqh');
            if (allBtn && el !== allBtn) allBtn.classList.remove('active');

            // Switch to fatwas subtab
            const fatwasPill = document.querySelector('.fiqh-nav-pill');
            if (typeof switchFiqhSubTab === 'function' && fatwasPill) {
                switchFiqhSubTab('fatwas', fatwasPill);
            }

            const dataset = getMasterFatwas();
            currentFilteredList = dataset.filter(item => {
                if (currentScholarFilter !== 'all' && item.scholar_code !== currentScholarFilter) {
                    return false;
                }
                return filterDef.filter(item);
            });

            currentPage = 1;
            renderFatwaCards();

            // 🚀 Smooth scroll directly to the fatwas cards so the user sees results immediately! 🚀
            setTimeout(() => {
                const fatwasSection = document.getElementById('fiqh-sub-fatwas');
                if (fatwasSection) {
                    fatwasSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 60);
        });
    }

    function onScholarSelectChange(val) {
        currentScholarFilter = val;
        
        const googleScholarSelect = document.getElementById('google-scholar-select');
        if (googleScholarSelect) {
            googleScholarSelect.value = val;
        }

        const fatwasPill = document.querySelector('.fiqh-nav-pill');
        if (typeof switchFiqhSubTab === 'function' && fatwasPill) {
            switchFiqhSubTab('fatwas', fatwasPill);
        }

        executeLiveFilter();

        setTimeout(() => {
            const fatwasSection = document.getElementById('fiqh-sub-fatwas');
            if (fatwasSection) {
                fatwasSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 60);
    }

    function showAllFiqhTransactions(btn) {
        ensureFiqhLoaded(() => {
            currentActiveButtonKey = null;
            currentActiveButtonTitle = '';

            const searchInput = document.getElementById('fiqh-search-input');
            if (searchInput) searchInput.value = '';
            
            currentScholarFilter = 'all';
            currentTopicFilter = 'all';
            
            const scholarSelect = document.getElementById('fiqh-scholar-select');
            if (scholarSelect) scholarSelect.value = 'all';

            const googleScholarSelect = document.getElementById('google-scholar-select');
            if (googleScholarSelect) googleScholarSelect.value = 'all';
            
            document.querySelectorAll('.fiqh-pill-btn').forEach(el => el.classList.remove('active'));
            if (btn) btn.classList.add('active');

            const fatwasPill = document.querySelector('.fiqh-nav-pill');
            if (typeof switchFiqhSubTab === 'function' && fatwasPill) {
                switchFiqhSubTab('fatwas', fatwasPill);
            }
            
            currentFilteredList = getMasterFatwas();
            currentPage = 1;
            renderFatwaCards();

            setTimeout(() => {
                const fatwasSection = document.getElementById('fiqh-sub-fatwas');
                if (fatwasSection) {
                    fatwasSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 60);
        });
    }

    function setQuickSearch(word, el) {
        // Find if word matches any of the button filter keys or titles
        for (const [key, def] of Object.entries(FIQH_BUTTON_FILTERS)) {
            if (def.title.includes(word) || word.includes(def.title)) {
                filterByFiqhButton(key, el);
                return;
            }
        }
        const input = document.getElementById('fiqh-search-input');
        if (input) input.value = word;
        currentActiveButtonKey = null;
        currentActiveButtonTitle = '';
        executeLiveFilter();
    }

    function clearFiqhSearch() {
        const input = document.getElementById('fiqh-search-input');
        if (input) input.value = '';
        
        currentActiveButtonKey = null;
        currentActiveButtonTitle = '';
        currentScholarFilter = 'all';
        currentTopicFilter = 'all';
        
        const scholarSelect = document.getElementById('fiqh-scholar-select');
        if (scholarSelect) scholarSelect.value = 'all';

        const googleScholarSelect = document.getElementById('google-scholar-select');
        if (googleScholarSelect) googleScholarSelect.value = 'all';
        
        const allBtn = document.getElementById('btn-show-all-fiqh');
        if (allBtn) allBtn.classList.add('active');

        document.querySelectorAll('.fiqh-pill-btn').forEach(btn => {
            if (btn !== allBtn) btn.classList.remove('active');
        });

        executeLiveFilter();
    }

    function setFiqhScholar(scholar, el) {
        currentScholarFilter = scholar;
        document.querySelectorAll('.fiqh-filter-btn[data-filter-type="scholar"]').forEach(btn => btn.classList.remove('active'));
        if (el) el.classList.add('active');
        executeLiveFilter();
    }

    function setFiqhTopic(topic, el) {
        currentTopicFilter = topic;
        document.querySelectorAll('.fiqh-filter-btn[data-filter-type="topic"]').forEach(btn => btn.classList.remove('active'));
        if (el) el.classList.add('active');
        executeLiveFilter();
    }

    document.addEventListener("DOMContentLoaded", function() {
        if (document.getElementById('fiqh_fatwas_grid')) {
            initFiqhEngine();
        }
    });
    

    // ════════════════════════════════════════════════════════════════════════════
    // 🌟 HELPER FUNCTIONS: SEARCH HIGHLIGHTING, COPY, WHATSAPP, FONT RESIZING 🌟
    // ════════════════════════════════════════════════════════════════════════════
    function highlightSearchKeywords(text, rawQuery) {
        if (!rawQuery || !text) return text;
        const tokens = rawQuery.trim().split(/\s+/).filter(t => t.length >= 2);
        if (tokens.length === 0) return text;

        let result = text;
        tokens.forEach(tok => {
            const escaped = tok.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
                               .replace(/[اأإآ]/g, '[اأإآ]');
            try {
                const re = new RegExp('(' + escaped + ')', 'gi');
                result = result.replace(re, '<mark class="fiqh-highlight">$1</mark>');
            } catch (e) {}
        });
        return result;
    }

    function setFiqhRulingFilter(rulingKey, btn) {
        currentRulingFilter = rulingKey;
        document.querySelectorAll('.fiqh-ruling-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        executeLiveFilter();
    }

    function copyFatwaCard(id) {
        const dataset = getMasterFatwas();
        const item = dataset.find(f => f.id === id);
        if (!item) return;

        let textToCopy = `المسألة الفقهية (#${item.id}):\n${item.question}\n\n`;
        textToCopy += `الحكم الشرعي: ${item.ruling}\n\n`;
        if (item.evidence) {
            textToCopy += `الأثر والدليل الشرعي من السنة الصحيحة والقرآن:\n${item.evidence}\n\n`;
        }
        textToCopy += `البيان والفتوى:\n${item.summary}\n\n`;
        textToCopy += `المرجع والتوثيق:\nالشيخ: ${item.scholar_name}\nالمصدر: ${item.source}\n\n`;
        textToCopy += `— منقول من: موسوعة فتاوى المعاملات والبيوع الإسلامية (نظام السجانة الذكي)`;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                showFiqhToast('✓ تم نسخ الفتوى والدليل الشرعي بنجاح إلى الحافظة');
            }).catch(() => {
                fallbackCopyText(textToCopy);
            });
        } else {
            fallbackCopyFiqhText(textToCopy);
        }
    }

    function fallbackCopyFiqhText(text) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            showFiqhToast('✓ تم نسخ الفتوى بنجاح');
        } catch (e) {
            showFiqhToast('يرجى تحديد النص ونسخه يدوياً');
        }
        document.body.removeChild(ta);
    }

    function shareFatwaWhatsApp(id) {
        const dataset = getMasterFatwas();
        const item = dataset.find(f => f.id === id);
        if (!item) return;

        let text = `*مسألة فقهية (#${item.id})*: ${item.question}\n\n`;
        text += `*الحكم الشرعي*: ${item.ruling}\n\n`;
        if (item.evidence) {
            text += `*الدليل الشرعي*: ${item.evidence}\n\n`;
        }
        text += `*البيان والفتوى*: ${item.summary}\n\n`;
        text += `*المرجع*: ${item.scholar_name} - ${item.source}\n`;
        text += `_من موسوعة فتاوى المعاملات والبيوع (نظام السجانة)_`;

        const url = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(text);
        window.open(url, '_blank');
    }

    function showFiqhToast(msg) {
        let toast = document.getElementById('fiqh-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'fiqh-toast';
            toast.className = 'px-6 py-3.5 bg-slate-900/95 border-2 border-amber-400 text-amber-200 font-black text-sm rounded-2xl shadow-2xl flex items-center gap-3 backdrop-blur-md';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="fa-solid fa-circle-check text-amber-400 text-lg"></i> <span>${msg}</span>`;
        toast.classList.add('show');
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.classList.remove('show');
        }, 2800);
    }

    function adjustFiqhCardFontSize(direction) {
        if (direction > 0) {
            fiqhCardFontScale = Math.min(1.35, fiqhCardFontScale + 0.1);
        } else if (direction < 0) {
            fiqhCardFontScale = Math.max(0.85, fiqhCardFontScale - 0.1);
        }
        applyFiqhCardFontScale();
    }

    function resetFiqhCardFontSize() {
        fiqhCardFontScale = 1.0;
        applyFiqhCardFontScale();
    }

    function applyFiqhCardFontScale() {
        const grid = document.getElementById('fiqh_fatwas_grid');
        if (grid) {
            grid.style.fontSize = `${fiqhCardFontScale}rem`;
        }
        const indicator = document.getElementById('fiqh-font-indicator');
        if (indicator) {
            indicator.textContent = `${Math.round(fiqhCardFontScale * 100)}%`;
        }
    }


    // ════════════════════════════════════════════════════════════════════════════
    // 🌟 QURAN & HADITH DATASETS & COPY / WHATSAPP HANDLERS 🌟
    // ════════════════════════════════════════════════════════════════════════════
    window.QURAN_VERSES_DATA = [{"num":1,"topic":"debts","search":"آية المداينة الكبرى (أطول آية في القرآن الكريم - دستور توثيق الديون والالتزامات المالية) يَا أَيُّهَا الَّذِينَ آمَنُوا إِذَا تَدَايَنتُم بِدَيْنٍ إِلَىٰ أَجَلٍ مُّسَمًّى فَاكْتُبُوهُ ۚ وَلْيَكْتُب بَّيْنَكُمْ كَاتِبٌ بِالْعَدْلِ ۚ وَلَا يَأْبَ كَاتِبٌ أَن يَكْتُبَ كَمَا عَلَّمَهُ اللَّهُ ۚ فَلْيَكْتُبْ وَلْيُمْلِلِ الَّذِي عَلَيْهِ الْحَقُّ وَلْيَتَّقِ اللَّهَ رَبَّهُ وَلَا يَبْخَسْ مِنْهُ شَيْئًا... وَاسْتَشْهِدُوا شَهِيدَيْنِ مِن رِّجَالِكُمْ ۖ فَإِن لَّمْ يَكُونَا رَجُلَيْنِ فَرَجُلٌ وَامْرَأَتَانِ مِمَّن تَرْضَوْنَ مِنَ الشُّهَدَاءِ أَن تَضِلَّ إِحْدَاهُمَا فَتُذَكِّرَ إِحْدَاهُمَا الْأُخْرَىٰ ۚ وَلَا يَأْبَ الشُّهَدَاءُ إِذَا مَا دُعُوا ۚ وَلَا تَسْأَمُوا أَن تَكْتُبُوهُ صَغِيرًا أَوْ كَبِيرًا إِلَىٰ أَجَلِهِ ۚ ذَٰلِكُمْ أَقْسَطُ عِندَ اللَّهِ وَأَقْوَمُ لِلشَّهَادَةِ وَأَدْنَىٰ أَلَّا تَرْتَابُوا ۖ إِلَّا أَن تَكُونَ تِجَارَةً حَاضِرَةً تُدِيرُونَهَا بَيْنَكُمْ فَلَيْسَ عَلَيْكُمْ جُنَاحٌ أَلَّا تَكْتُبُوهَا ۗ وَأَشْهِدُوا إِذَا تَبَايَعْتُمْ... سورة البقرة: الآية 282 الأحكام الاقتصادية: وجوب/ندب توثيق الديون المؤجلة بالكتابة الرسمية، وتحديد أجل السداد بدقة، وإملاء المدين بنفسه اعترافاً بالحق، والإشهاد العدلي، واستثناء التجارة الحاضرة النقدية لتيسير حركة السوق وسرعة تداول السلع. الديون والتوثيق","title":"آية المداينة الكبرى (أطول آية في القرآن الكريم - دستور توثيق الديون والالتزامات المالية)","surah":"سورة البقرة: الآية 282","verseText":"يَا أَيُّهَا الَّذِينَ آمَنُوا إِذَا تَدَايَنتُم بِدَيْنٍ إِلَىٰ أَجَلٍ مُّسَمًّى فَاكْتُبُوهُ ۚ وَلْيَكْتُب بَّيْنَكُمْ كَاتِبٌ بِالْعَدْلِ ۚ وَلَا يَأْبَ كَاتِبٌ أَن يَكْتُبَ كَمَا عَلَّمَهُ اللَّهُ ۚ فَلْيَكْتُبْ وَلْيُمْلِلِ الَّذِي عَلَيْهِ الْحَقُّ وَلْيَتَّقِ اللَّهَ رَبَّهُ وَلَا يَبْخَسْ مِنْهُ شَيْئًا... وَاسْتَشْهِدُوا شَهِيدَيْنِ مِن رِّجَالِكُمْ ۖ فَإِن لَّمْ يَكُونَا رَجُلَيْنِ فَرَجُلٌ وَامْرَأَتَانِ مِمَّن تَرْضَوْنَ مِنَ الشُّهَدَاءِ أَن تَضِلَّ إِحْدَاهُمَا فَتُذَكِّرَ إِحْدَاهُمَا الْأُخْرَىٰ ۚ وَلَا يَأْبَ الشُّهَدَاءُ إِذَا مَا دُعُوا ۚ وَلَا تَسْأَمُوا أَن تَكْتُبُوهُ صَغِيرًا أَوْ كَبِيرًا إِلَىٰ أَجَلِهِ ۚ ذَٰلِكُمْ أَقْسَطُ عِندَ اللَّهِ وَأَقْوَمُ لِلشَّهَادَةِ وَأَدْنَىٰ أَلَّا تَرْتَابُوا ۖ إِلَّا أَن تَكُونَ تِجَارَةً حَاضِرَةً تُدِيرُونَهَا بَيْنَكُمْ فَلَيْسَ عَلَيْكُمْ جُنَاحٌ أَلَّا تَكْتُبُوهَا ۗ وَأَشْهِدُوا إِذَا تَبَايَعْتُمْ...","guidance":"الأحكام الاقتصادية: وجوب/ندب توثيق الديون المؤجلة بالكتابة الرسمية، وتحديد أجل السداد بدقة، وإملاء المدين بنفسه اعترافاً بالحق، والإشهاد العدلي، واستثناء التجارة الحاضرة النقدية لتيسير حركة السوق وسرعة تداول السلع.","catBadge":"الديون والتوثيق"},{"num":2,"topic":"debts","search":"مشروعية الرهن الحيازي في السفر والديون وأداء الأمانات وتحريم كتمان الشهادة وَإِن كُنتُمْ عَلَىٰ سَفَرٍ وَلَمْ تَجِدُوا كَاتِبًا فَرِهَانٌ مَّقْبُوضَةٌ ۖ فَإِنْ أَمِنَ بَعْضُكُم بَعْضًا فَلْيُؤَدِّ الَّذِي اؤْتُمِنَ أَمَانَتَهُ وَلْيَتَّقِ اللَّهَ رَبَّهُ ۗ وَلَا تَكْتُمُوا الشَّهَادَةَ ۚ وَمَن يَكْتُمْهَا فَإِنَّهُ آثِمٌ قَلْبُهُ ۗ وَاللَّهُ بِمَا تَعْمَلُونَ عَلِيمٌ سورة البقرة: الآية 283 الأحكام الاقتصادية: مشروعية أخذ الرهن لضمان استرداد الدين المالي عند تعذر التوثيق الكتابي، وكون الرهن مقبوضاً بيد الدائن، وتأكيد أداء الأمانة المالية للطرفين، وتحريم كتمان الشهادات المالية في المنازعات القضائية. الرهن والأمانات","title":"مشروعية الرهن الحيازي في السفر والديون وأداء الأمانات وتحريم كتمان الشهادة","surah":"سورة البقرة: الآية 283","verseText":"وَإِن كُنتُمْ عَلَىٰ سَفَرٍ وَلَمْ تَجِدُوا كَاتِبًا فَرِهَانٌ مَّقْبُوضَةٌ ۖ فَإِنْ أَمِنَ بَعْضُكُم بَعْضًا فَلْيُؤَدِّ الَّذِي اؤْتُمِنَ أَمَانَتَهُ وَلْيَتَّقِ اللَّهَ رَبَّهُ ۗ وَلَا تَكْتُمُوا الشَّهَادَةَ ۚ وَمَن يَكْتُمْهَا فَإِنَّهُ آثِمٌ قَلْبُهُ ۗ وَاللَّهُ بِمَا تَعْمَلُونَ عَلِيمٌ","guidance":"الأحكام الاقتصادية: مشروعية أخذ الرهن لضمان استرداد الدين المالي عند تعذر التوثيق الكتابي، وكون الرهن مقبوضاً بيد الدائن، وتأكيد أداء الأمانة المالية للطرفين، وتحريم كتمان الشهادات المالية في المنازعات القضائية.","catBadge":"الرهن والأمانات"},{"num":3,"topic":"debts","search":"وجوب إنظار المدين المعسر شرعاً وفضل التجاوز عنه بالإسقاط والصدقة وَإِن كَانَ ذُو عُسْرَةٍ فَنَظِرَةٌ إِلَىٰ مَيْسَرَةٍ ۚ وَأَن تَصَدَّقُوا خَيْرٌ لَّكُمْ ۖ إِن كُنتُمْ تَعْلَمُونَ سورة البقرة: الآية 280 الأحكام الاقتصادية: وجوب تأجيل سداد الدين على المدين العاجز دون فرض أي غرامات تأخير أو فوائد إضافية، واستحباب التصدق بإبراء ذمة المدين كلياً أو جزئياً من باب الإحسان والمعونة الاجتماعية. إنظار المعسر","title":"وجوب إنظار المدين المعسر شرعاً وفضل التجاوز عنه بالإسقاط والصدقة","surah":"سورة البقرة: الآية 280","verseText":"وَإِن كَانَ ذُو عُسْرَةٍ فَنَظِرَةٌ إِلَىٰ مَيْسَرَةٍ ۚ وَأَن تَصَدَّقُوا خَيْرٌ لَّكُمْ ۖ إِن كُنتُمْ تَعْلَمُونَ","guidance":"الأحكام الاقتصادية: وجوب تأجيل سداد الدين على المدين العاجز دون فرض أي غرامات تأخير أو فوائد إضافية، واستحباب التصدق بإبراء ذمة المدين كلياً أو جزئياً من باب الإحسان والمعونة الاجتماعية.","catBadge":"إنظار المعسر"},{"num":4,"topic":"trade","search":"القاعدة الكلية الكبرى: إحلال البيع والتجارة وتحريم الربا القاطع الَّذِينَ يَأْكُلُونَ الرِّبَا لَا يَقُومُونَ إِلَّا كَمَا يَقُومُ الَّذِي يَتَخَبَّطُهُ الشَّيْطَانُ مِنَ الْمَسِّ ۚ ذَٰلِكَ بِأَنَّهُمْ قَالُوا إِنَّمَا الْبَيْعُ مِثْلُ الرِّبَا ۗ وَأَحَلَّ اللَّهُ الْبَيْعَ وَحَرَّمَ الرِّبَا ۚ فَمَن جَاءَهُ مَوْعِظَةٌ مِّن رَّبِّهِ فَانتَهَىٰ فَلَهُ مَا سَلَفَ وَأَمْرُهُ إِلَى اللَّهِ ۖ وَمَنْ عَادَ فَأُولَٰئِكَ أَصْحَابُ النَّارِ ۖ هُمْ فِيهَا خَالِدُونَ سورة البقرة: الآية 275 الأحكام الاقتصادية: التفريق الجذري بين الربح الناتج عن بيع السلع والخدمات وتحمل المخاطر وتداول الأموال الحقيقية (المباح)، وبين الزيادة المشروطة على القرض والنقد بلا عوض ولا مخاطرة (المحرم قطيعاً). إحلال البيع","title":"القاعدة الكلية الكبرى: إحلال البيع والتجارة وتحريم الربا القاطع","surah":"سورة البقرة: الآية 275","verseText":"الَّذِينَ يَأْكُلُونَ الرِّبَا لَا يَقُومُونَ إِلَّا كَمَا يَقُومُ الَّذِي يَتَخَبَّطُهُ الشَّيْطَانُ مِنَ الْمَسِّ ۚ ذَٰلِكَ بِأَنَّهُمْ قَالُوا إِنَّمَا الْبَيْعُ مِثْلُ الرِّبَا ۗ وَأَحَلَّ اللَّهُ الْبَيْعَ وَحَرَّمَ الرِّبَا ۚ فَمَن جَاءَهُ مَوْعِظَةٌ مِّن رَّبِّهِ فَانتَهَىٰ فَلَهُ مَا سَلَفَ وَأَمْرُهُ إِلَى اللَّهِ ۖ وَمَنْ عَادَ فَأُولَٰئِكَ أَصْحَابُ النَّارِ ۖ هُمْ فِيهَا خَالِدُونَ","guidance":"الأحكام الاقتصادية: التفريق الجذري بين الربح الناتج عن بيع السلع والخدمات وتحمل المخاطر وتداول الأموال الحقيقية (المباح)، وبين الزيادة المشروطة على القرض والنقد بلا عوض ولا مخاطرة (المحرم قطيعاً).","catBadge":"إحلال البيع"},{"num":5,"topic":"trade","search":"ركن التراضي التام في العقود وتحريم أكل أموال الناس بالباطل يَا أَيُّهَا الَّذِينَ آمَنُوا لَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ إِلَّا أَن تَكُونَ تِجَارَةً عَن تَرَاضٍ مِّنكُمْ ۚ وَلَا تَقْتُلُوا أَنفُسَكُمْ ۚ إِنَّ اللَّهَ كَانَ بِكُمْ رَحِيمًا سورة النساء: الآية 29 الأحكام الاقتصادية: بطلان أي معاملة مالية قائمة على الغصب أو التدليس أو الغرر أو الإكراه، واشتراط التراضي الحقيقي والإيجاب والقبول الصحيح بين المتبايعين لصحة انتقال الملكية. التراضي في التجارة","title":"ركن التراضي التام في العقود وتحريم أكل أموال الناس بالباطل","surah":"سورة النساء: الآية 29","verseText":"يَا أَيُّهَا الَّذِينَ آمَنُوا لَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ إِلَّا أَن تَكُونَ تِجَارَةً عَن تَرَاضٍ مِّنكُمْ ۚ وَلَا تَقْتُلُوا أَنفُسَكُمْ ۚ إِنَّ اللَّهَ كَانَ بِكُمْ رَحِيمًا","guidance":"الأحكام الاقتصادية: بطلان أي معاملة مالية قائمة على الغصب أو التدليس أو الغرر أو الإكراه، واشتراط التراضي الحقيقي والإيجاب والقبول الصحيح بين المتبايعين لصحة انتقال الملكية.","catBadge":"التراضي في التجارة"},{"num":6,"topic":"trade","search":"الأمر الإلهي القطعي بالوفاء بكافة العقود والعهود والالتزامات التجارية يَا أَيُّهَا الَّذِينَ آمَنُوا أَوْفُوا بِالْعُقُودِ ۚ أُحِلَّتْ لَكُم بَهِيمَةُ الْأَنْعَامِ إِلَّا مَا يُتْلَىٰ عَلَيْكُمْ... سورة المائدة: الآية 1 الأحكام الاقتصادية: قاعدة 'العقد شريعة المتعاقدين' في الفقه الإسلامي؛ وجوب الالتزام التام بكافة بنود وشروط عقود التوريد والمقاولات والوكالة والشراكة والبيع ما لم تحرم حلالاً أو تحل حراماً. الوفاء بالعقود","title":"الأمر الإلهي القطعي بالوفاء بكافة العقود والعهود والالتزامات التجارية","surah":"سورة المائدة: الآية 1","verseText":"يَا أَيُّهَا الَّذِينَ آمَنُوا أَوْفُوا بِالْعُقُودِ ۚ أُحِلَّتْ لَكُم بَهِيمَةُ الْأَنْعَامِ إِلَّا مَا يُتْلَىٰ عَلَيْكُمْ...","guidance":"الأحكام الاقتصادية: قاعدة 'العقد شريعة المتعاقدين' في الفقه الإسلامي؛ وجوب الالتزام التام بكافة بنود وشروط عقود التوريد والمقاولات والوكالة والشراكة والبيع ما لم تحرم حلالاً أو تحل حراماً.","catBadge":"الوفاء بالعقود"},{"num":7,"topic":"trade","search":"إيفاء الكيل والميزان بالقسطاس المستقيم وأثره في بركة الاقتصاد وَأَوْفُوا الْكَيْلَ إِذَا كِلْتُمْ وَزِنُوا بِالْقِسْطَاسِ الْمُسْتَقِيمِ ۚ ذَٰلِكَ خَيْرٌ وَأَحْسَنُ تَأْوِيلًا سورة الإسراء: الآية 35 الأحكام الاقتصادية: وجوب دقة المعايير والمكاييل والأوزان والمواصفات القياسية الهندسية والتجارية في تسليم البضائع والسلع، واعتبار العدل أساس استقرار الأسواق. الموازين والمكاييل","title":"إيفاء الكيل والميزان بالقسطاس المستقيم وأثره في بركة الاقتصاد","surah":"سورة الإسراء: الآية 35","verseText":"وَأَوْفُوا الْكَيْلَ إِذَا كِلْتُمْ وَزِنُوا بِالْقِسْطَاسِ الْمُسْتَقِيمِ ۚ ذَٰلِكَ خَيْرٌ وَأَحْسَنُ تَأْوِيلًا","guidance":"الأحكام الاقتصادية: وجوب دقة المعايير والمكاييل والأوزان والمواصفات القياسية الهندسية والتجارية في تسليم البضائع والسلع، واعتبار العدل أساس استقرار الأسواق.","catBadge":"الموازين والمكاييل"},{"num":8,"topic":"trade","search":"الوعيد الشديد والهلاك للمطففين الذين ينقصون في الكيل والوزن والمواصفات وَيْلٌ لِّلْمُطَفِّفِينَ * الَّذِينَ إِذَا اكْتَالُوا عَلَى النَّاسِ يَسْتَوْفُونَ * وَإِذَا كَالُوهُمْ أَو وَّزَنُوهُمْ يُخْسِرُونَ سورة المطففين: الآيات 1-3 الأحكام الاقتصادية: تجريم الازدواجية والمخادعة في البيع والشراء؛ أن يطلب حقه كاملاً في الشراء وينقص حقوق الناس عند البيع والتسليم في الجودة أو الكمية أو الوزن. تحريم التطفيف","title":"الوعيد الشديد والهلاك للمطففين الذين ينقصون في الكيل والوزن والمواصفات","surah":"سورة المطففين: الآيات 1-3","verseText":"وَيْلٌ لِّلْمُطَفِّفِينَ * الَّذِينَ إِذَا اكْتَالُوا عَلَى النَّاسِ يَسْتَوْفُونَ * وَإِذَا كَالُوهُمْ أَو وَّزَنُوهُمْ يُخْسِرُونَ","guidance":"الأحكام الاقتصادية: تجريم الازدواجية والمخادعة في البيع والشراء؛ أن يطلب حقه كاملاً في الشراء وينقص حقوق الناس عند البيع والتسليم في الجودة أو الكمية أو الوزن.","catBadge":"تحريم التطفيف"},{"num":9,"topic":"trade","search":"النهي عن بخس الناس أشياءهم وبخس مجهوداتهم والفساد في الأسواق فَأَوْفُوا الْكَيْلَ وَالْمِيزَانَ وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تُفْسِدُوا فِي الْأَرْضِ بَعْدَ إِصْلَاحِهَا ۚ ذَٰلِكُمْ خَيْرٌ لَّكُمْ إِن كُنتُم مُّؤْمِنِينَ سورة الأعراف: الآية 85 الأحكام الاقتصادية: تحريم التقليل المصطنع من قيمة سلع الناس أو خدماتهم أو كفاءاتهم لشرائها بأقل من قيمتها العادلة، والنهي عن الاحتكار والممارسات الاحتكارية الضارة بالسوق. عدم بخس السلع","title":"النهي عن بخس الناس أشياءهم وبخس مجهوداتهم والفساد في الأسواق","surah":"سورة الأعراف: الآية 85","verseText":"فَأَوْفُوا الْكَيْلَ وَالْمِيزَانَ وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تُفْسِدُوا فِي الْأَرْضِ بَعْدَ إِصْلَاحِهَا ۚ ذَٰلِكُمْ خَيْرٌ لَّكُمْ إِن كُنتُم مُّؤْمِنِينَ","guidance":"الأحكام الاقتصادية: تحريم التقليل المصطنع من قيمة سلع الناس أو خدماتهم أو كفاءاتهم لشرائها بأقل من قيمتها العادلة، والنهي عن الاحتكار والممارسات الاحتكارية الضارة بالسوق.","catBadge":"عدم بخس السلع"},{"num":10,"topic":"trade","search":"وجوب أداء الأمانات المالية إلى أصحابها والحكم بالعدل في المعاملات إِنَّ اللَّهَ يَأْمُرُكُمْ أَن تُؤَدُّوا الْأَمَانَاتِ إِلَىٰ أَهْلِهَا وَإِذَا حَكَمْتُم بَيْنَ النَّاسِ أَن تَحْكُمُوا بِالْعَدْلِ ۚ إِنَّ اللَّهَ نِعِمَّا يَعِظُكُم بِهِ ۗ إِنَّ اللَّهَ كَانَ سَمِيعًا بَصِيرًا سورة النساء: الآية 58 الأحكام الاقتصادية: إلزامية رد الودائع، وحفظ أموال الشركاء والمساهمين، وسداد مستحقات الموردين والعمال دون مماطلة، والعدل في الفصل في النزاعات التجارية والتحكيم المالي. أداء الأمانات","title":"وجوب أداء الأمانات المالية إلى أصحابها والحكم بالعدل في المعاملات","surah":"سورة النساء: الآية 58","verseText":"إِنَّ اللَّهَ يَأْمُرُكُمْ أَن تُؤَدُّوا الْأَمَانَاتِ إِلَىٰ أَهْلِهَا وَإِذَا حَكَمْتُم بَيْنَ النَّاسِ أَن تَحْكُمُوا بِالْعَدْلِ ۚ إِنَّ اللَّهَ نِعِمَّا يَعِظُكُم بِهِ ۗ إِنَّ اللَّهَ كَانَ سَمِيعًا بَصِيرًا","guidance":"الأحكام الاقتصادية: إلزامية رد الودائع، وحفظ أموال الشركاء والمساهمين، وسداد مستحقات الموردين والعمال دون مماطلة، والعدل في الفصل في النزاعات التجارية والتحكيم المالي.","catBadge":"أداء الأمانات"},{"num":11,"topic":"trade","search":"التعاون على البر والتقوى: الأصل القرآني للشركات التعاونية والتأمين التكافلي وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَىٰ ۖ وَلَا تَعَاوَنُوا عَلَى الْإِثْمِ وَالْعُدْوَانِ ۚ وَاتَّقُوا اللَّهَ ۖ إِنَّ اللَّهَ شَدِيدُ الْعِقَابِ سورة المائدة: الآية 2 الأحكام الاقتصادية: تأصيل العمل التكافلي التعاوني، وصناديق الضمان الاجتماعي، والتأمين الإسلامي القائم على التعاون والتبرع المتبادل دون ربا ولا قمار. التأمين التكافلي والتعاون","title":"التعاون على البر والتقوى: الأصل القرآني للشركات التعاونية والتأمين التكافلي","surah":"سورة المائدة: الآية 2","verseText":"وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَىٰ ۖ وَلَا تَعَاوَنُوا عَلَى الْإِثْمِ وَالْعُدْوَانِ ۚ وَاتَّقُوا اللَّهَ ۖ إِنَّ اللَّهَ شَدِيدُ الْعِقَابِ","guidance":"الأحكام الاقتصادية: تأصيل العمل التكافلي التعاوني، وصناديق الضمان الاجتماعي، والتأمين الإسلامي القائم على التعاون والتبرع المتبادل دون ربا ولا قمار.","catBadge":"التأمين التكافلي والتعاون"},{"num":12,"topic":"trade","search":"الأمر بإيفاء الكيل والميزان الصحيح وعدم الإفساد الاقتصادي في الأرض أَوْفُوا الْكَيْلَ وَلَا تَكُونُوا مِنَ الْمُخْسِرِينَ * وَزِنُوا بِالْقِسْطَاسِ الْمُسْتَقِيمِ * وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تَعْثَوْا فِي الْأَرْضِ مُفْسِدِينَ سورة الشعراء: الآيات 181-183 الأحكام الاقتصادية: ربط سلامة الممارسات التجارية والمعايير الفنية بالإيمان وتقوى الله، والتحذير من أن الفساد التجاري والتلاعب بالموازين يؤدي إلى هلاك الأمم وزوال النعم. الكيل الصادق","title":"الأمر بإيفاء الكيل والميزان الصحيح وعدم الإفساد الاقتصادي في الأرض","surah":"سورة الشعراء: الآيات 181-183","verseText":"أَوْفُوا الْكَيْلَ وَلَا تَكُونُوا مِنَ الْمُخْسِرِينَ * وَزِنُوا بِالْقِسْطَاسِ الْمُسْتَقِيمِ * وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تَعْثَوْا فِي الْأَرْضِ مُفْسِدِينَ","guidance":"الأحكام الاقتصادية: ربط سلامة الممارسات التجارية والمعايير الفنية بالإيمان وتقوى الله، والتحذير من أن الفساد التجاري والتلاعب بالموازين يؤدي إلى هلاك الأمم وزوال النعم.","catBadge":"الكيل الصادق"},{"num":13,"topic":"riba","search":"إعلان الحرب الإلهية على أكلة الربا وحق استرداد رأس المال الأصلي فقط يَا أَيُّهَا الَّذِينَ آمَنُوا اتَّقُوا اللَّهَ وَذَرُوا مَا بَقِيَ مِنَ الرِّبَا إِن كُنتُم مُّؤْمِنِينَ * فَإِن لَّمْ تَفْعَلُوا فَأْذَنُوا بِحَرْبٍ مِّنَ اللَّهِ وَرَسُولِهِ ۖ وَإِن تُبْتُمْ فَلَكُمْ رُءُوسُ أَمْوَالِكُمْ لَا تَظْلِمُونَ وَلَا تُظْلَمُونَ سورة البقرة: الآيات 278-279 الأحكام الاقتصادية: أعظم تحذير مالي في الشريعة، وبطلان الزيادة الربوية في القروض، وحق الدائن التائب في استرجاع رأس ماله الأساسي فقط دون زيادة (ظلم للمدين) ودون نقصان (ظلم للدائن). تحريم الربا القاطع","title":"إعلان الحرب الإلهية على أكلة الربا وحق استرداد رأس المال الأصلي فقط","surah":"سورة البقرة: الآيات 278-279","verseText":"يَا أَيُّهَا الَّذِينَ آمَنُوا اتَّقُوا اللَّهَ وَذَرُوا مَا بَقِيَ مِنَ الرِّبَا إِن كُنتُم مُّؤْمِنِينَ * فَإِن لَّمْ تَفْعَلُوا فَأْذَنُوا بِحَرْبٍ مِّنَ اللَّهِ وَرَسُولِهِ ۖ وَإِن تُبْتُمْ فَلَكُمْ رُءُوسُ أَمْوَالِكُمْ لَا تَظْلِمُونَ وَلَا تُظْلَمُونَ","guidance":"الأحكام الاقتصادية: أعظم تحذير مالي في الشريعة، وبطلان الزيادة الربوية في القروض، وحق الدائن التائب في استرجاع رأس ماله الأساسي فقط دون زيادة (ظلم للمدين) ودون نقصان (ظلم للدائن).","catBadge":"تحريم الربا القاطع"},{"num":14,"topic":"riba","search":"سنة الله في محق الربا وتدمير أرباحه ونماء أموال الصدقات والبركات يَمْحَقُ اللَّهُ الرِّبَا وَيُرْبِي الصَّدَقَاتِ ۗ وَاللَّهُ لَا يُحِبُّ كُلَّ كَفَّارٍ أَثِيمٍ سورة البقرة: الآية 276 الأحكام الاقتصادية: الحقيقة الاقتصادية الإلهية بزوال بركة المال الربوي وانهياره في النهاية عبر الأزمات والتضخم، ونماء الأموال الحلال المزكاة وحمايتها من التلف والجوائح. محق الربا وبركة الصدقات","title":"سنة الله في محق الربا وتدمير أرباحه ونماء أموال الصدقات والبركات","surah":"سورة البقرة: الآية 276","verseText":"يَمْحَقُ اللَّهُ الرِّبَا وَيُرْبِي الصَّدَقَاتِ ۗ وَاللَّهُ لَا يُحِبُّ كُلَّ كَفَّارٍ أَثِيمٍ","guidance":"الأحكام الاقتصادية: الحقيقة الاقتصادية الإلهية بزوال بركة المال الربوي وانهياره في النهاية عبر الأزمات والتضخم، ونماء الأموال الحلال المزكاة وحمايتها من التلف والجوائح.","catBadge":"محق الربا وبركة الصدقات"},{"num":15,"topic":"riba","search":"النهي الصريح عن ربا الديون وتراكم الفوائد أضعافاً مضاعفة يَا أَيُّهَا الَّذِينَ آمَنُوا لَا تَأْكُلُوا الرِّبَا أَضْعَافًا مُّضَاعَفَةً ۖ وَاتَّقُوا اللَّهَ لَعَلَّكُمْ تُفْلِحُونَ سورة آل عمران: الآية 130 الأحكام الاقتصادية: تحريم ربا الجاهلية المتمثل في مضاعفة الديون عند عجز المدين عن السداد في الأجل، وهو ما تطبقه البنوك الربوية المعاصرة في الفوائد التراكمية المركبة. الربا المضاعف","title":"النهي الصريح عن ربا الديون وتراكم الفوائد أضعافاً مضاعفة","surah":"سورة آل عمران: الآية 130","verseText":"يَا أَيُّهَا الَّذِينَ آمَنُوا لَا تَأْكُلُوا الرِّبَا أَضْعَافًا مُّضَاعَفَةً ۖ وَاتَّقُوا اللَّهَ لَعَلَّكُمْ تُفْلِحُونَ","guidance":"الأحكام الاقتصادية: تحريم ربا الجاهلية المتمثل في مضاعفة الديون عند عجز المدين عن السداد في الأجل، وهو ما تطبقه البنوك الربوية المعاصرة في الفوائد التراكمية المركبة.","catBadge":"الربا المضاعف"},{"num":16,"topic":"riba","search":"تحريم الرشوة والوساطة الباطلة لأكل أموال الناس واستباحة الحقوق وَلَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ وَتُدْلُوا بِهَا إِلَى الْحُكَّامِ لِتَأْكُلُوا فَرِيقًا مِّنْ أَمْوَالِ النَّاسِ بِالْإِثْمِ وَأَنتُمْ تَعْلَمُونَ سورة البقرة: الآية 188 الأحكام الاقتصادية: تجريم دفع الرشاوى والعمولات الخفية للمسؤولين أو القضاة للاستيلاء على مناقصات أو أراضٍ أو عقارات بغير وجه حق، وبطلان كل عقد استند إلى رشوة. تحريم الرشوة","title":"تحريم الرشوة والوساطة الباطلة لأكل أموال الناس واستباحة الحقوق","surah":"سورة البقرة: الآية 188","verseText":"وَلَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ وَتُدْلُوا بِهَا إِلَى الْحُكَّامِ لِتَأْكُلُوا فَرِيقًا مِّنْ أَمْوَالِ النَّاسِ بِالْإِثْمِ وَأَنتُمْ تَعْلَمُونَ","guidance":"الأحكام الاقتصادية: تجريم دفع الرشاوى والعمولات الخفية للمسؤولين أو القضاة للاستيلاء على مناقصات أو أراضٍ أو عقارات بغير وجه حق، وبطلان كل عقد استند إلى رشوة.","catBadge":"تحريم الرشوة"},{"num":17,"topic":"riba","search":"حقيقة المال الربوي في ميزان الله ومضاعفة أجر الصدقة الخالصة وَمَا آتَيْتُم مِّن رِّبًا لِّيَرْبُوَ فِي أَمْوَالِ النَّاسِ فَلَا يَرْبُو عِندَ اللَّهِ ۖ وَمَا آتَيْتُم مِّن زَكَاةٍ تُرِيدُونَ وَجْهَ اللَّهِ فَأُولَٰئِكَ هُمُ الْمُضْعِفُونَ سورة الروم: الآية 39 الأحكام الاقتصادية: بيان أن الفوائد المالية الربوية لا تحقق نمواً حقيقياً مستداماً في الثروة ولا بركة فيها، بينما الزكاة والصدقات هي التي تضاعف الثروات وتنشط الطلب الحقيقي. الربا المالي المحرم","title":"حقيقة المال الربوي في ميزان الله ومضاعفة أجر الصدقة الخالصة","surah":"سورة الروم: الآية 39","verseText":"وَمَا آتَيْتُم مِّن رِّبًا لِّيَرْبُوَ فِي أَمْوَالِ النَّاسِ فَلَا يَرْبُو عِندَ اللَّهِ ۖ وَمَا آتَيْتُم مِّن زَكَاةٍ تُرِيدُونَ وَجْهَ اللَّهِ فَأُولَٰئِكَ هُمُ الْمُضْعِفُونَ","guidance":"الأحكام الاقتصادية: بيان أن الفوائد المالية الربوية لا تحقق نمواً حقيقياً مستداماً في الثروة ولا بركة فيها، بينما الزكاة والصدقات هي التي تضاعف الثروات وتنشط الطلب الحقيقي.","catBadge":"الربا المالي المحرم"},{"num":18,"topic":"zakat","search":"دستور مصارف الزكاة الثمانية الحصرية في الشريعة الإسلامية إِنَّمَا الصَّدَقَاتُ لِلْفُقَرَاءِ وَالْمَسَاكِينِ وَالْعَامِلِينَ عَلَيْهَا وَالْمُؤَلَّفَةِ قُلُوبُهُمْ وَفِي الرِّقَابِ وَالْغَارِمِينَ وَفِي سَبِيلِ اللَّهِ وَابْنِ السَّبِيلِ ۖ فَرِيضَةً مِّنَ اللَّهِ ۗ وَاللَّهُ عَلِيمٌ حَكِيمٌ سورة التوبة: الآية 60 الأحكام الاقتصادية: حصر مصارف الزكاة المفروضة في الأصناف الثمانية بدقة تشريعية، وأبرزهم الغارمون (المدينون العاجزون عن سداد ديونهم المشروعة)، وإعادة تدوير الثروة لتأمين الحماية الاجتماعية. مصارف الزكاة الثمانية","title":"دستور مصارف الزكاة الثمانية الحصرية في الشريعة الإسلامية","surah":"سورة التوبة: الآية 60","verseText":"إِنَّمَا الصَّدَقَاتُ لِلْفُقَرَاءِ وَالْمَسَاكِينِ وَالْعَامِلِينَ عَلَيْهَا وَالْمُؤَلَّفَةِ قُلُوبُهُمْ وَفِي الرِّقَابِ وَالْغَارِمِينَ وَفِي سَبِيلِ اللَّهِ وَابْنِ السَّبِيلِ ۖ فَرِيضَةً مِّنَ اللَّهِ ۗ وَاللَّهُ عَلِيمٌ حَكِيمٌ","guidance":"الأحكام الاقتصادية: حصر مصارف الزكاة المفروضة في الأصناف الثمانية بدقة تشريعية، وأبرزهم الغارمون (المدينون العاجزون عن سداد ديونهم المشروعة)، وإعادة تدوير الثروة لتأمين الحماية الاجتماعية.","catBadge":"مصارف الزكاة الثمانية"},{"num":19,"topic":"zakat","search":"وجوب جباية الزكاة وأثرها في تطهير النفوس ونماء الأموال خُذْ مِنْ أَمْوَالِهِمْ صَدَقَةً تُطَهِّرُهُمْ وَتُزَكِّيهِم بِهَا وَصَلِّ عَلَيْهِمْ ۖ إِنَّ صَلَاتَكَ سَكَنٌ لَّهُمْ ۗ وَاللَّهُ سَمِيعٌ عَلِيمٌ سورة التوبة: الآية 103 الأحكام الاقتصادية: الزكاة ليست منحة اختيارية بل حق مالي واجب تؤخذ من الأغنياء لدعم استقرار المجتمع وتطهير المال من الشبهات، ونماء المال بالبركة والتوسعة على المحتاجين. وجوب أخذ الزكاة","title":"وجوب جباية الزكاة وأثرها في تطهير النفوس ونماء الأموال","surah":"سورة التوبة: الآية 103","verseText":"خُذْ مِنْ أَمْوَالِهِمْ صَدَقَةً تُطَهِّرُهُمْ وَتُزَكِّيهِم بِهَا وَصَلِّ عَلَيْهِمْ ۖ إِنَّ صَلَاتَكَ سَكَنٌ لَّهُمْ ۗ وَاللَّهُ سَمِيعٌ عَلِيمٌ","guidance":"الأحكام الاقتصادية: الزكاة ليست منحة اختيارية بل حق مالي واجب تؤخذ من الأغنياء لدعم استقرار المجتمع وتطهير المال من الشبهات، ونماء المال بالبركة والتوسعة على المحتاجين.","catBadge":"وجوب أخذ الزكاة"},{"num":20,"topic":"zakat","search":"تقرير الحق المالي المعلوم في أموال الأغنياء للسائل والمحروم وَالَّذِينَ فِي أَمْوَالِهِمْ حَقٌّ مَّعْلُومٌ * لِّلسَّائِلِ وَالْمَحْرُومِ سورة المعارج: الآيات 24-25 الأحكام الاقتصادية: ترسيخ مبدأ 'الحق المعلوم' وهو نسبة الزكاة المحددة شرعاً (ربع العشر 2.5% في النقدين وعروض التجارة)، وحق الفقير المتعفف الذي لا يسأل الناس إلحافاً. حق معلوم في الأموال","title":"تقرير الحق المالي المعلوم في أموال الأغنياء للسائل والمحروم","surah":"سورة المعارج: الآيات 24-25","verseText":"وَالَّذِينَ فِي أَمْوَالِهِمْ حَقٌّ مَّعْلُومٌ * لِّلسَّائِلِ وَالْمَحْرُومِ","guidance":"الأحكام الاقتصادية: ترسيخ مبدأ 'الحق المعلوم' وهو نسبة الزكاة المحددة شرعاً (ربع العشر 2.5% في النقدين وعروض التجارة)، وحق الفقير المتعفف الذي لا يسأل الناس إلحافاً.","catBadge":"حق معلوم في الأموال"},{"num":21,"topic":"zakat","search":"إيتاء زكاة الإنتاج الزراعي والثمار يوم الحصاد وجوباً وَهُوَ الَّذِي أَنشَأَ جَنَّاتٍ مَّعْرُوشَاتٍ وَغَيْرَ مَعْرُوشَاتٍ وَالنَّخْلَ وَالزَّرْعَ مُخْتَلِفًا أُكُلُهُ وَالزَّيْتُونَ وَالرُّمَّانَ مُتَشَابِهًا وَغَيْرَ مُتَشَابِهٍ ۚ كُلُوا مِن ثَمَرِهِ إِذَا أَثْمَرَ وَآتُوا حَقَّهُ يَوْمَ حَصَادِهِ ۖ وَلَا تُسْرِفُوا ۚ إِنَّهُ لَا يُحِبُّ الْمُسْرِفِينَ سورة الأنعام: الآية 141 الأحكام الاقتصادية: وجوب إخراج زكاة المحاصيل الزراعية فور الحصاد دون انتظار حولان الحول، بمقدار 10% فيما سقي بلا كلفة (ماء المطر) و5% فيما سقي بكلفة وآلات. زكاة الزروع والثمار","title":"إيتاء زكاة الإنتاج الزراعي والثمار يوم الحصاد وجوباً","surah":"سورة الأنعام: الآية 141","verseText":"وَهُوَ الَّذِي أَنشَأَ جَنَّاتٍ مَّعْرُوشَاتٍ وَغَيْرَ مَعْرُوشَاتٍ وَالنَّخْلَ وَالزَّرْعَ مُخْتَلِفًا أُكُلُهُ وَالزَّيْتُونَ وَالرُّمَّانَ مُتَشَابِهًا وَغَيْرَ مُتَشَابِهٍ ۚ كُلُوا مِن ثَمَرِهِ إِذَا أَثْمَرَ وَآتُوا حَقَّهُ يَوْمَ حَصَادِهِ ۖ وَلَا تُسْرِفُوا ۚ إِنَّهُ لَا يُحِبُّ الْمُسْرِفِينَ","guidance":"الأحكام الاقتصادية: وجوب إخراج زكاة المحاصيل الزراعية فور الحصاد دون انتظار حولان الحول، بمقدار 10% فيما سقي بلا كلفة (ماء المطر) و5% فيما سقي بكلفة وآلات.","catBadge":"زكاة الزروع والثمار"},{"num":22,"topic":"inheritance","search":"دستور قسمة المواريث الأول: أنصبة الأولاد والوالدين وتقديم الدين والوصية يُوصِيكُمُ اللَّهُ فِي أَوْلَادِكُمْ ۖ لِلذَّكَرِ مِثْلُ حَظِّ الْأُنثَيَيْنِ ۚ فَإِن كُنَّ نِسَاءً فَوْقَ اثْنَتَيْنِ فَلَهُنَّ ثُلُثَا مَا تَرَكَ ۖ وَإِن كَانَتْ وَاحِدَةً فَلَهَا النِّصْفُ ۚ وَلِأَبَوَيْهِ لِكُلِّ وَاحِدٍ مِّنْهُمَا السُّدُسُ مِمَّا تَرَكَ إِن كَانَ لَهُ وَلَدٌ... مِن بَعْدِ وَصِيَّةٍ يُوصِي بِهَا أَوْ دَيْنٍ ۗ آبَاؤُكُمْ وَأَبْنَاؤُكُمْ لَا تَدْرُونَ أَيُّهُمْ أَقْرَبُ لَكُمْ نَفْعًا ۚ فَرِيضَةً مِّنَ اللَّهِ ۗ إِنَّ اللَّهَ كَانَ عَلِيمًا حَكِيمًا سورة النساء: الآية 11 الأحكام الاقتصادية: توزيع التركات بأنصبة محكمة قطعية الدلالة تمنع النزاع العائلي، وقاعدة تقديم سداد ديون المتوفى أولاً ثم تنفيذ الوصية المشروعة (في حدود ثلث التركة لغير وارث) قبل قسمة التركة. مواريث الأولاد والوالدين","title":"دستور قسمة المواريث الأول: أنصبة الأولاد والوالدين وتقديم الدين والوصية","surah":"سورة النساء: الآية 11","verseText":"يُوصِيكُمُ اللَّهُ فِي أَوْلَادِكُمْ ۖ لِلذَّكَرِ مِثْلُ حَظِّ الْأُنثَيَيْنِ ۚ فَإِن كُنَّ نِسَاءً فَوْقَ اثْنَتَيْنِ فَلَهُنَّ ثُلُثَا مَا تَرَكَ ۖ وَإِن كَانَتْ وَاحِدَةً فَلَهَا النِّصْفُ ۚ وَلِأَبَوَيْهِ لِكُلِّ وَاحِدٍ مِّنْهُمَا السُّدُسُ مِمَّا تَرَكَ إِن كَانَ لَهُ وَلَدٌ... مِن بَعْدِ وَصِيَّةٍ يُوصِي بِهَا أَوْ دَيْنٍ ۗ آبَاؤُكُمْ وَأَبْنَاؤُكُمْ لَا تَدْرُونَ أَيُّهُمْ أَقْرَبُ لَكُمْ نَفْعًا ۚ فَرِيضَةً مِّنَ اللَّهِ ۗ إِنَّ اللَّهَ كَانَ عَلِيمًا حَكِيمًا","guidance":"الأحكام الاقتصادية: توزيع التركات بأنصبة محكمة قطعية الدلالة تمنع النزاع العائلي، وقاعدة تقديم سداد ديون المتوفى أولاً ثم تنفيذ الوصية المشروعة (في حدود ثلث التركة لغير وارث) قبل قسمة التركة.","catBadge":"مواريث الأولاد والوالدين"},{"num":23,"topic":"inheritance","search":"دستور قسمة المواريث الثاني: أنصبة الأزواج والزوجات والإخوة لأم والنهي عن الإضرار وَلَكُمْ نِصْفُ مَا تَرَكَ أَزْوَاجُكُمْ إِن لَّمْ يَكُن لَّهُنَّ وَلَدٌ ۚ فَإِن كَانَ لَهُنَّ وَلَدٌ فَلَكُمُ الرُّبُعُ مِمَّا تَرَكْنَ ۚ مِن بَعْدِ وَصِيَّةٍ يُوصِينَ بِهَا أَوْ دَيْنٍ ۚ وَلَهُنَّ الرُّبُعُ مِمَّا تَرَكْتُمْ إِن لَّمْ يَكُن لَّكُمْ وَلَدٌ ۚ فَإِن كَانَ لَكُمْ وَلَدٌ فَلَهُنَّ الثُّمُنُ مِمَّا تَرَكْتُم ۚ مِن بَعْدِ وَصِيَّةٍ تُوصُونَ بِهَا أَوْ دَيْنٍ... غَيْرَ مُضَارٍّ ۚ وَصِيَّةً مِّنَ اللَّهِ ۗ وَاللَّهُ عَلِيمٌ حَلِيمٌ سورة النساء: الآية 12 الأحكام الاقتصادية: تفصيل أنصبة الزوج والزوجة في التركة حال وجود الولد وحال عدمه، وتحريم المضارة في الوصية كالإقرار بديون مصطنعة أو حرمان الورثة الشرعيين. مواريث الأزواج والإخوة","title":"دستور قسمة المواريث الثاني: أنصبة الأزواج والزوجات والإخوة لأم والنهي عن الإضرار","surah":"سورة النساء: الآية 12","verseText":"وَلَكُمْ نِصْفُ مَا تَرَكَ أَزْوَاجُكُمْ إِن لَّمْ يَكُن لَّهُنَّ وَلَدٌ ۚ فَإِن كَانَ لَهُنَّ وَلَدٌ فَلَكُمُ الرُّبُعُ مِمَّا تَرَكْنَ ۚ مِن بَعْدِ وَصِيَّةٍ يُوصِينَ بِهَا أَوْ دَيْنٍ ۚ وَلَهُنَّ الرُّبُعُ مِمَّا تَرَكْتُمْ إِن لَّمْ يَكُن لَّكُمْ وَلَدٌ ۚ فَإِن كَانَ لَكُمْ وَلَدٌ فَلَهُنَّ الثُّمُنُ مِمَّا تَرَكْتُم ۚ مِن بَعْدِ وَصِيَّةٍ تُوصُونَ بِهَا أَوْ دَيْنٍ... غَيْرَ مُضَارٍّ ۚ وَصِيَّةً مِّنَ اللَّهِ ۗ وَاللَّهُ عَلِيمٌ حَلِيمٌ","guidance":"الأحكام الاقتصادية: تفصيل أنصبة الزوج والزوجة في التركة حال وجود الولد وحال عدمه، وتحريم المضارة في الوصية كالإقرار بديون مصطنعة أو حرمان الورثة الشرعيين.","catBadge":"مواريث الأزواج والإخوة"},{"num":24,"topic":"inheritance","search":"فرائض المواريث حدود إلهية ملزمة والوعيد الشديد لمن يعتدي عليها ويحرم الورثة تِلْكَ حُدُودُ اللَّهِ ۚ وَمَن يُطِعِ اللَّهَ وَرَسُولَهُ يُدْخِلْهُ جَنَّاتٍ تَجْرِي مِن تَحْتِهَا الْأَنْهَارُ خَالِدِينَ فِيهَا ۚ وَذَٰلِكَ الْفَوْزُ الْعَظِيمُ * وَمَن يَعْصِ اللَّهَ وَيَتَعَدَّ حُدُودَهُ يُدْخِلْهُ نَارًا خَالِدًا فِيهَا وَلَهُ عَذَابٌ مُّهِينٌ سورة النساء: الآيات 13-14 الأحكام الاقتصادية: تأكيد أن قسمة المواريث ليست خاضعة للأهواء ولا التقاليد القبلية؛ فتحريم حرمان البنات أو الزوجات أو الإخوة من حقهم المالي المفروض، والوعيد بالنار لمن يتعدى حدود القسمة. حدود الله في الميراث","title":"فرائض المواريث حدود إلهية ملزمة والوعيد الشديد لمن يعتدي عليها ويحرم الورثة","surah":"سورة النساء: الآيات 13-14","verseText":"تِلْكَ حُدُودُ اللَّهِ ۚ وَمَن يُطِعِ اللَّهَ وَرَسُولَهُ يُدْخِلْهُ جَنَّاتٍ تَجْرِي مِن تَحْتِهَا الْأَنْهَارُ خَالِدِينَ فِيهَا ۚ وَذَٰلِكَ الْفَوْزُ الْعَظِيمُ * وَمَن يَعْصِ اللَّهَ وَيَتَعَدَّ حُدُودَهُ يُدْخِلْهُ نَارًا خَالِدًا فِيهَا وَلَهُ عَذَابٌ مُّهِينٌ","guidance":"الأحكام الاقتصادية: تأكيد أن قسمة المواريث ليست خاضعة للأهواء ولا التقاليد القبلية؛ فتحريم حرمان البنات أو الزوجات أو الإخوة من حقهم المالي المفروض، والوعيد بالنار لمن يتعدى حدود القسمة.","catBadge":"حدود الله في الميراث"},{"num":25,"topic":"inheritance","search":"ميراث الكلالة: أحكام الإخوة والأخوات الأشقاء ولأب عند عدم الأصول والفروع يَسْتَفْتُونَكَ قُلِ اللَّهُ يُفْتِيكُمْ فِي الْكَلَالَةِ ۚ إِنِ امْرُؤٌ هَلَكَ لَيْسَ لَهُ وَلَدٌ وَلَهُ أُخْتٌ فَلَهَا نِصْفُ مَا تَرَكَ ۚ وَهُوَ يَرِثُهَا إِن لَّمْ يَكُن لَّهَا وَلَدٌ ۚ فَإِن كَانَتَا اثْنَتَيْنِ فَلَهُمَا الثُّلُثَانِ مِمَّا تَرَكَ ۚ وَإِن كَانُوا إِخْوَةً رِّجَالًا وَنِسَاءً فَلِلذَّكَرِ مِثْلُ حَظِّ الْأُنثَيَيْنِ ۗ يُبَيِّنُ اللَّهُ لَكُمْ أَن تَضِلُّوا ۗ وَاللَّهُ بِكُلِّ شَيْءٍ عَلِيمٌ سورة النساء: الآية 176 الأحكام الاقتصادية: أحكام توريث الإخوة والأخوات للمتوفى الذي ليس له ولد يرثه ولا والد (الكلالة)، واكتمال منظومة الفرائض المالية التي لا تترك مالاً ضائعاً أو مشاعاً. ميراث الكلالة","title":"ميراث الكلالة: أحكام الإخوة والأخوات الأشقاء ولأب عند عدم الأصول والفروع","surah":"سورة النساء: الآية 176","verseText":"يَسْتَفْتُونَكَ قُلِ اللَّهُ يُفْتِيكُمْ فِي الْكَلَالَةِ ۚ إِنِ امْرُؤٌ هَلَكَ لَيْسَ لَهُ وَلَدٌ وَلَهُ أُخْتٌ فَلَهَا نِصْفُ مَا تَرَكَ ۚ وَهُوَ يَرِثُهَا إِن لَّمْ يَكُن لَّهَا وَلَدٌ ۚ فَإِن كَانَتَا اثْنَتَيْنِ فَلَهُمَا الثُّلُثَانِ مِمَّا تَرَكَ ۚ وَإِن كَانُوا إِخْوَةً رِّجَالًا وَنِسَاءً فَلِلذَّكَرِ مِثْلُ حَظِّ الْأُنثَيَيْنِ ۗ يُبَيِّنُ اللَّهُ لَكُمْ أَن تَضِلُّوا ۗ وَاللَّهُ بِكُلِّ شَيْءٍ عَلِيمٌ","guidance":"الأحكام الاقتصادية: أحكام توريث الإخوة والأخوات للمتوفى الذي ليس له ولد يرثه ولا والد (الكلالة)، واكتمال منظومة الفرائض المالية التي لا تترك مالاً ضائعاً أو مشاعاً.","catBadge":"ميراث الكلالة"},{"num":26,"topic":"inheritance","search":"إثبات استقلال الذمة المالية للرجال والنساء وحقهم القطعي في التركات لِّلرِّجَالِ نَصِيبٌ مِّمَّا تَرَكَ الْوَالِدَانِ وَالْأَقْرَبُونَ وَلِلنِّسَاءِ نَصِيبٌ مِّمَّا تَرَكَ الْوَالِدَانِ وَالْأَقْرَبُونَ مِمَّا قَلَّ مِنْهُ أَوْ كَثُرَ ۚ نَصِيبًا مَّفْرُوضًا سورة النساء: الآية 7 الأحكام الاقتصادية: تقرير قاعدة العدالة المالية التامة؛ أن المال الموروث حق مقدر للرجل والمرأة مهما صغر حجم المال أو كبر، وإبطال حرمان الصغار والنساء من الثروات. حق النساء والرجال في التركة","title":"إثبات استقلال الذمة المالية للرجال والنساء وحقهم القطعي في التركات","surah":"سورة النساء: الآية 7","verseText":"لِّلرِّجَالِ نَصِيبٌ مِّمَّا تَرَكَ الْوَالِدَانِ وَالْأَقْرَبُونَ وَلِلنِّسَاءِ نَصِيبٌ مِّمَّا تَرَكَ الْوَالِدَانِ وَالْأَقْرَبُونَ مِمَّا قَلَّ مِنْهُ أَوْ كَثُرَ ۚ نَصِيبًا مَّفْرُوضًا","guidance":"الأحكام الاقتصادية: تقرير قاعدة العدالة المالية التامة؛ أن المال الموروث حق مقدر للرجل والمرأة مهما صغر حجم المال أو كبر، وإبطال حرمان الصغار والنساء من الثروات.","catBadge":"حق النساء والرجال في التركة"},{"num":27,"topic":"contracts","search":"معيارا التوظيف والعمل في عقود الإجارة: الكفاءة الفنية والنزاهة والأمانة قَالَتْ إِحْدَاهُمَا يَا أَبَتِ اسْتَأْجِرْهُ ۖ إِنَّ خَيْرَ مَنِ اسْتَأْجَرْتَ الْقَوِيُّ الْأَمِينُ سورة القصص: الآية 26 الأحكام الاقتصادية: مشروعية عقد الإجارة واستئجار الأشخاص للأعمال الهندسية والتجارية، والمعياران الحاكمان للنجاح: القوة (المهارة والكفاءة في العمل) والأمانة (الصدق وحفظ أموال المنشأة). عقد الإجارة والكفاءة","title":"معيارا التوظيف والعمل في عقود الإجارة: الكفاءة الفنية والنزاهة والأمانة","surah":"سورة القصص: الآية 26","verseText":"قَالَتْ إِحْدَاهُمَا يَا أَبَتِ اسْتَأْجِرْهُ ۖ إِنَّ خَيْرَ مَنِ اسْتَأْجَرْتَ الْقَوِيُّ الْأَمِينُ","guidance":"الأحكام الاقتصادية: مشروعية عقد الإجارة واستئجار الأشخاص للأعمال الهندسية والتجارية، والمعياران الحاكمان للنجاح: القوة (المهارة والكفاءة في العمل) والأمانة (الصدق وحفظ أموال المنشأة).","catBadge":"عقد الإجارة والكفاءة"},{"num":28,"topic":"contracts","search":"عقد العمل محدد المدة ومبادلة العمل بالأجر والمهر قَالَ إِنِّي أُرِيدُ أَنْ أُنكِحَكَ إِحْدَى ابْنَتَيَّ هَاتَيْنِ عَلَىٰ أَن تَأْجُرَنِي ثَمَانِيَ حِجَجٍ ۖ فَإِنْ أَتْمَمْتَ عَشْرًا فَمِنْ عِندِكَ ۖ وَمَا أُرِيدُ أَنْ أَشُقَّ عَلَيْكَ ۚ سَتَجِدُنِي إِن شَاءَ اللَّهُ مِنَ الصَّالِحِينَ سورة القصص: الآية 27 الأحكام الاقتصادية: صحة عقد العمل المشروط بمدة محددة (8 أو 10 سنوات)، وجواز جعل العمل والمنفعة عوضاً مالياً، وحسن معاملة الأجراء وعدم تكليفهم بما يشق عليهم. تحديد مدة عقد العمل","title":"عقد العمل محدد المدة ومبادلة العمل بالأجر والمهر","surah":"سورة القصص: الآية 27","verseText":"قَالَ إِنِّي أُرِيدُ أَنْ أُنكِحَكَ إِحْدَى ابْنَتَيَّ هَاتَيْنِ عَلَىٰ أَن تَأْجُرَنِي ثَمَانِيَ حِجَجٍ ۖ فَإِنْ أَتْمَمْتَ عَشْرًا فَمِنْ عِندِكَ ۖ وَمَا أُرِيدُ أَنْ أَشُقَّ عَلَيْكَ ۚ سَتَجِدُنِي إِن شَاءَ اللَّهُ مِنَ الصَّالِحِينَ","guidance":"الأحكام الاقتصادية: صحة عقد العمل المشروط بمدة محددة (8 أو 10 سنوات)، وجواز جعل العمل والمنفعة عوضاً مالياً، وحسن معاملة الأجراء وعدم تكليفهم بما يشق عليهم.","catBadge":"تحديد مدة عقد العمل"},{"num":29,"topic":"contracts","search":"وجوب دفع أجور العاملين فور أداء العمل والتعامل المالي بالمعروف ... فَإِنْ أَرْضَعْنَ لَكُمْ فَآتُوهُنَّ أُجُورَهُنَّ ۖ وَأْتَمِرُوا بَيْنَكُم بِمَعْرُوفٍ ۖ وَإِن تَعَاسَرْتُمْ فَسَتُرْضِعُ لَهُ أُخْرَىٰ سورة الطلاق: الآية 6 الأحكام الاقتصادية: وجوب الوفاء التام بأجور العمال والموظفين فور إنجاز العمل دون تأخير أو خصم تعسفي، وتراضي الطرفين على الأجر العادل المناسب لظروف السوق. وفاء أجور العاملين","title":"وجوب دفع أجور العاملين فور أداء العمل والتعامل المالي بالمعروف","surah":"سورة الطلاق: الآية 6","verseText":"... فَإِنْ أَرْضَعْنَ لَكُمْ فَآتُوهُنَّ أُجُورَهُنَّ ۖ وَأْتَمِرُوا بَيْنَكُم بِمَعْرُوفٍ ۖ وَإِن تَعَاسَرْتُمْ فَسَتُرْضِعُ لَهُ أُخْرَىٰ","guidance":"الأحكام الاقتصادية: وجوب الوفاء التام بأجور العمال والموظفين فور إنجاز العمل دون تأخير أو خصم تعسفي، وتراضي الطرفين على الأجر العادل المناسب لظروف السوق.","catBadge":"وفاء أجور العاملين"},{"num":30,"topic":"contracts","search":"أصل عقدي الجعالة (المكافأة المشروطة) والكفالة الشخصية والضمان المالي قَالُوا نَفْقِدُ صُوَاعَ الْمَلِكِ وَلِمَن جَاءَ بِهِ حِمْلُ بَعِيرٍ وَأَنَا بِهِ زَعِيمٌ سورة يوسف: الآية 72 الأحكام الاقتصادية: الأصل القرآني لعقد الجعالة (جائزة أو عمولة لمن ينجز مهمة محددة: 'حمل بعير')، وأصل عقد الكفالة والضمان الشخصي والمالي ('وأنا به زعيم' أي ضامن متعهد بالسداد). عقدا الجعالة والكفالة","title":"أصل عقدي الجعالة (المكافأة المشروطة) والكفالة الشخصية والضمان المالي","surah":"سورة يوسف: الآية 72","verseText":"قَالُوا نَفْقِدُ صُوَاعَ الْمَلِكِ وَلِمَن جَاءَ بِهِ حِمْلُ بَعِيرٍ وَأَنَا بِهِ زَعِيمٌ","guidance":"الأحكام الاقتصادية: الأصل القرآني لعقد الجعالة (جائزة أو عمولة لمن ينجز مهمة محددة: 'حمل بعير')، وأصل عقد الكفالة والضمان الشخصي والمالي ('وأنا به زعيم' أي ضامن متعهد بالسداد).","catBadge":"عقدا الجعالة والكفالة"},{"num":31,"topic":"contracts","search":"مشروعية الشركات والمخالطة في الأموال والتحذير من بغي وخيانة الشركاء ... وَإِنَّ كَثِيرًا مِّنَ الْخُلَطَاءِ لَيَبْغِي بَعْضُهُمْ عَلَىٰ بَعْضٍ إِلَّا الَّذِينَ آمَنُوا وَعَمِلُوا الصَّالِحَاتِ وَقَلِيلٌ مَّا هُمْ ۗ وَظَنَّ دَاوُودُ أَنَّمَا فَتَنَّاهُ فَاسْتَغْفَرَ رَبَّهُ وَخَرَّ رَاكِعًا وَأَنَابَ سورة ص: الآية 24 الأحكام الاقتصادية: مشروعية الشراكة التجارية وخلط الأموال لتنمية الاستثمار، والتحذير الشديد من خيانة الشريك لأخيه باختلاس الأرباح أو استغلال الصلاحيات بغير عدل. عقد الشركة وحقوق الشركاء","title":"مشروعية الشركات والمخالطة في الأموال والتحذير من بغي وخيانة الشركاء","surah":"سورة ص: الآية 24","verseText":"... وَإِنَّ كَثِيرًا مِّنَ الْخُلَطَاءِ لَيَبْغِي بَعْضُهُمْ عَلَىٰ بَعْضٍ إِلَّا الَّذِينَ آمَنُوا وَعَمِلُوا الصَّالِحَاتِ وَقَلِيلٌ مَّا هُمْ ۗ وَظَنَّ دَاوُودُ أَنَّمَا فَتَنَّاهُ فَاسْتَغْفَرَ رَبَّهُ وَخَرَّ رَاكِعًا وَأَنَابَ","guidance":"الأحكام الاقتصادية: مشروعية الشراكة التجارية وخلط الأموال لتنمية الاستثمار، والتحذير الشديد من خيانة الشريك لأخيه باختلاس الأرباح أو استغلال الصلاحيات بغير عدل.","catBadge":"عقد الشركة وحقوق الشركاء"},{"num":32,"topic":"orphans","search":"اختبار الرشد المالي لليتيم وتأهيله تجارياً قبل تسليمه الثروة والأموال وَابْتَلُوا الْيَتَامَىٰ حَتَّىٰ إِذَا بَلَغُوا النِّكَاحَ فَإِنْ آنَسْتُم مِّنْهُمْ رُشْدًا فَادْفَعُوا إِلَيْهِمْ أَمْوَالَهُمْ ۖ وَلَا تَأْكُلُوهَا إِسْرَافًا وَبِدَارًا أَن يَكْبَرُوا ۚ وَمَن كَانَ غَنِيًّا فَلْيَسْتَعْفِفْ ۖ وَمَن كَانَ فَقِيرًا فَلْيَأْكُلْ بِالْمَعْرُوفِ ۚ فَإِذَا دَفَعْتُمْ إِلَيْهِمْ أَمْوَالَهُمْ فَأَشْهِدُوا عَلَيْهِمْ ۚ وَكَفَىٰ بِاللَّهِ حَسِيبًا سورة النساء: الآية 6 الأحكام الاقتصادية: قاعدة 'التأهيل المالي'؛ لا يُسلَّم المال لمن لا يحسن إدارته حتى يثبت رشده وقدرته على التجارة وحفظ الأصول، وتنظيم أتعاب الوصي أو مدير الصناديق بالمعروف. اختبار الرشد المالي","title":"اختبار الرشد المالي لليتيم وتأهيله تجارياً قبل تسليمه الثروة والأموال","surah":"سورة النساء: الآية 6","verseText":"وَابْتَلُوا الْيَتَامَىٰ حَتَّىٰ إِذَا بَلَغُوا النِّكَاحَ فَإِنْ آنَسْتُم مِّنْهُمْ رُشْدًا فَادْفَعُوا إِلَيْهِمْ أَمْوَالَهُمْ ۖ وَلَا تَأْكُلُوهَا إِسْرَافًا وَبِدَارًا أَن يَكْبَرُوا ۚ وَمَن كَانَ غَنِيًّا فَلْيَسْتَعْفِفْ ۖ وَمَن كَانَ فَقِيرًا فَلْيَأْكُلْ بِالْمَعْرُوفِ ۚ فَإِذَا دَفَعْتُمْ إِلَيْهِمْ أَمْوَالَهُمْ فَأَشْهِدُوا عَلَيْهِمْ ۚ وَكَفَىٰ بِاللَّهِ حَسِيبًا","guidance":"الأحكام الاقتصادية: قاعدة 'التأهيل المالي'؛ لا يُسلَّم المال لمن لا يحسن إدارته حتى يثبت رشده وقدرته على التجارة وحفظ الأصول، وتنظيم أتعاب الوصي أو مدير الصناديق بالمعروف.","catBadge":"اختبار الرشد المالي"},{"num":33,"topic":"orphans","search":"مشروعية الحجر المالي على غير الرشيد لحفظ الثروة وتنميتها بالاستثمار وَلَا تُؤْتُوا السُّفَهَاءَ أَمْوَالَكُمُ الَّتِي جَعَلَ اللَّهُ لَكُمْ قِيَامًا وَارْزُقُوهُمْ فِيهَا وَاكْسُوهُمْ وَقُولُوا لَهُمْ قَوْلًا مَّعْرُوفًا سورة النساء: الآية 5 الأحكام الاقتصادية: الحجر المالي على المبذرين والسفهاء لحماية الثروات من التبدد، واستثمار أموال القاصرين في التجارة والإنفاق عليهم من الأرباح ('وارزقوهم فيها') لحفظ رأس المال الأصلي. الحجر المالي على السفيه","title":"مشروعية الحجر المالي على غير الرشيد لحفظ الثروة وتنميتها بالاستثمار","surah":"سورة النساء: الآية 5","verseText":"وَلَا تُؤْتُوا السُّفَهَاءَ أَمْوَالَكُمُ الَّتِي جَعَلَ اللَّهُ لَكُمْ قِيَامًا وَارْزُقُوهُمْ فِيهَا وَاكْسُوهُمْ وَقُولُوا لَهُمْ قَوْلًا مَّعْرُوفًا","guidance":"الأحكام الاقتصادية: الحجر المالي على المبذرين والسفهاء لحماية الثروات من التبدد، واستثمار أموال القاصرين في التجارة والإنفاق عليهم من الأرباح ('وارزقوهم فيها') لحفظ رأس المال الأصلي.","catBadge":"الحجر المالي على السفيه"},{"num":34,"topic":"orphans","search":"الوعيد الشديد على أكل أموال اليتامى ظلماً والاعتداء على حقوق الضعفاء إِنَّ الَّذِينَ يَأْكُلُونَ أَمْوَالَ الْيَتَامَىٰ ظُلْمًا إِنَّمَا يَأْكُلُونَ فِي بُطُونِهِمْ نَارًا ۖ وَسَيَصْلَوْنَ سَعِيرًا سورة النساء: الآية 10 الأحكام الاقتصادية: أشد تحذير في الشريعة على خيانة الأمانة المالية للوصي والوكيل، وتجريم التربح غير المشروع من أموال القاصرين ومن لا حيلة لهم. حرمة أكل أموال اليتامى","title":"الوعيد الشديد على أكل أموال اليتامى ظلماً والاعتداء على حقوق الضعفاء","surah":"سورة النساء: الآية 10","verseText":"إِنَّ الَّذِينَ يَأْكُلُونَ أَمْوَالَ الْيَتَامَىٰ ظُلْمًا إِنَّمَا يَأْكُلُونَ فِي بُطُونِهِمْ نَارًا ۖ وَسَيَصْلَوْنَ سَعِيرًا","guidance":"الأحكام الاقتصادية: أشد تحذير في الشريعة على خيانة الأمانة المالية للوصي والوكيل، وتجريم التربح غير المشروع من أموال القاصرين ومن لا حيلة لهم.","catBadge":"حرمة أكل أموال اليتامى"},{"num":35,"topic":"macro","search":"الوعيد الشديد على كنز الذهب والفضة والسيولة ومنعها من الدورة الاقتصادية ... وَالَّذِينَ يَكْنِزُونَ الذَّهَبَ وَالْفِضَّةَ وَلَا يُنفِقُونَهَا فِي سَبِيلِ اللَّهِ فَبَشِّرْهُم بِعَذَابٍ أَلِيمٍ * يَوْمَ يُحْمَىٰ عَلَيْهَا فِي نَارِ جَهَنَّمَ فَتُكْوَىٰ بِهَا جِبَاهُهُمْ وَجُنُوبُهُمْ وَظُهُورُهُمْ ۖ هَٰذَا مَا كَنَزْتُمْ لِأَنفُسِكُمْ فَذُوقُوا مَا كُنتُمْ تَكْنِزُونَ سورة التوبة: الآيات 34-35 الأحكام الاقتصادية: تحريم تعطيل السيولة وحبس النقود عن الاستثمار وعمارة الأسواق، وإيجاب الزكاة والإنفاق عليها لتبقى الأموال متدفقة في شرايين الاقتصاد لخدمة الإنتاج. تحريم كنز الأموال","title":"الوعيد الشديد على كنز الذهب والفضة والسيولة ومنعها من الدورة الاقتصادية","surah":"سورة التوبة: الآيات 34-35","verseText":"... وَالَّذِينَ يَكْنِزُونَ الذَّهَبَ وَالْفِضَّةَ وَلَا يُنفِقُونَهَا فِي سَبِيلِ اللَّهِ فَبَشِّرْهُم بِعَذَابٍ أَلِيمٍ * يَوْمَ يُحْمَىٰ عَلَيْهَا فِي نَارِ جَهَنَّمَ فَتُكْوَىٰ بِهَا جِبَاهُهُمْ وَجُنُوبُهُمْ وَظُهُورُهُمْ ۖ هَٰذَا مَا كَنَزْتُمْ لِأَنفُسِكُمْ فَذُوقُوا مَا كُنتُمْ تَكْنِزُونَ","guidance":"الأحكام الاقتصادية: تحريم تعطيل السيولة وحبس النقود عن الاستثمار وعمارة الأسواق، وإيجاب الزكاة والإنفاق عليها لتبقى الأموال متدفقة في شرايين الاقتصاد لخدمة الإنتاج.","catBadge":"تحريم كنز الأموال"},{"num":36,"topic":"macro","search":"قانون تداول الأموال العظيم: منع تركز الثروة في يد فئة قليلة واحتكار الأسواق ... كَيْ لَا يَكُونَ دُولَةً بَيْنَ الْأَغْنِيَاءِ مِنكُمْ ۚ وَمَا آتَاكُمُ الرَّسُولُ فَخُذُوهُ وَمَا نَهَاكُمْ عَنْهُ فَانتَهُوا ۚ وَاتَّقُوا اللَّهَ ۖ إِنَّ اللَّهَ شَدِيدُ الْعِقَابِ سورة الحشر: الآية 7 الأحكام الاقتصادية: أهم قاعدة في الاقتصاد الكلي والعدالة الاجتماعية؛ تحريم تركيز الثروة واحتكار الأسواق لدى القلة من أصحاب رؤوس الأموال، ووجوب توزيع الفرص الاقتصادية على المجتمع ككل. قانون دوران الثروة","title":"قانون تداول الأموال العظيم: منع تركز الثروة في يد فئة قليلة واحتكار الأسواق","surah":"سورة الحشر: الآية 7","verseText":"... كَيْ لَا يَكُونَ دُولَةً بَيْنَ الْأَغْنِيَاءِ مِنكُمْ ۚ وَمَا آتَاكُمُ الرَّسُولُ فَخُذُوهُ وَمَا نَهَاكُمْ عَنْهُ فَانتَهُوا ۚ وَاتَّقُوا اللَّهَ ۖ إِنَّ اللَّهَ شَدِيدُ الْعِقَابِ","guidance":"الأحكام الاقتصادية: أهم قاعدة في الاقتصاد الكلي والعدالة الاجتماعية؛ تحريم تركيز الثروة واحتكار الأسواق لدى القلة من أصحاب رؤوس الأموال، ووجوب توزيع الفرص الاقتصادية على المجتمع ككل.","catBadge":"قانون دوران الثروة"},{"num":37,"topic":"macro","search":"قاعدة إدارة الميزانية والإنفاق الرشيد: التوازن بين الإسراف والتقتير وَالَّذِينَ إِذَا أَنفَقُوا لَمْ يُسْرِفُوا وَلَمْ يَقْتُرُوا وَكَانَ بَيْنَ ذَٰلِكَ قَوَامًا سورة الفرقان: الآية 67 الأحكام الاقتصادية: التوازن المالي في الإنفاق الاستهلاكي والاستثماري؛ تحريم التبذير المهدور وتحريم البخل والشح الذي يضر بالنشاط، وتحقيق الوسطية في الإنفاق. الاعتدال المالي","title":"قاعدة إدارة الميزانية والإنفاق الرشيد: التوازن بين الإسراف والتقتير","surah":"سورة الفرقان: الآية 67","verseText":"وَالَّذِينَ إِذَا أَنفَقُوا لَمْ يُسْرِفُوا وَلَمْ يَقْتُرُوا وَكَانَ بَيْنَ ذَٰلِكَ قَوَامًا","guidance":"الأحكام الاقتصادية: التوازن المالي في الإنفاق الاستهلاكي والاستثماري؛ تحريم التبذير المهدور وتحريم البخل والشح الذي يضر بالنشاط، وتحقيق الوسطية في الإنفاق.","catBadge":"الاعتدال المالي"},{"num":38,"topic":"macro","search":"فضل الضرب في الأرض وابتغاء فضل الله بالتجارة والكسب الشريف ... وَآخَرُونَ يَضْرِبُونَ فِي الْأَرْضِ يَبْتَغُونَ مِن فَضْلِ اللَّهِ ۙ وَآخَرُونَ يُقَاتِلُونَ فِي سَبِيلِ اللَّهِ ۖ فَاقْرَءُوا مَا تَيَسَّرَ مِنْهُ... سورة المزمل: الآية 20 الأحكام الاقتصادية: مدح السعي التجاري والضرب في الأرض لطلب الرزق الحلال وقرنه بالجهاد في سبيل الله، وتأصيل السفر والاستيراد والتصدير وتنمية الاستثمارات المشروعة. السعي والتجارة الحلال","title":"فضل الضرب في الأرض وابتغاء فضل الله بالتجارة والكسب الشريف","surah":"سورة المزمل: الآية 20","verseText":"... وَآخَرُونَ يَضْرِبُونَ فِي الْأَرْضِ يَبْتَغُونَ مِن فَضْلِ اللَّهِ ۙ وَآخَرُونَ يُقَاتِلُونَ فِي سَبِيلِ اللَّهِ ۖ فَاقْرَءُوا مَا تَيَسَّرَ مِنْهُ...","guidance":"الأحكام الاقتصادية: مدح السعي التجاري والضرب في الأرض لطلب الرزق الحلال وقرنه بالجهاد في سبيل الله، وتأصيل السفر والاستيراد والتصدير وتنمية الاستثمارات المشروعة.","catBadge":"السعي والتجارة الحلال"}];
    window.HADITHS_DATA = [{"num":1,"cat":"sales","search":"1 الأصل في البيوع والتجارة هو التراضي الخالص بين الطرفين عن أبي سعيد الخدري رضي الله عنه قال: قال رسول الله ﷺ: «إِنَّمَا الْبَيْعُ عَنْ تَرَاضٍ» رواه ابن ماجه (2185) وابن حبان (4967) وصححه الألباني في الإرواء (1283) القاعدة العظمى في كافة العقود والمعاملات والمرابحات: بطلان أي عقد يشوبه إكراه أو غش أو تدليس أو غبن فاحش، واشتراط الرضا التام المتبادل لصحّة انتقال الملكية. البيع عن تراض الرضا الإكراه صحة البيع العقود التجارة","title":"الأصل في البيوع والتجارة هو التراضي الخالص بين الطرفين","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ صحيح","sanad":"عن أبي سعيد الخدري رضي الله عنه قال: قال رسول الله ﷺ:","matn":"«إِنَّمَا الْبَيْعُ عَنْ تَرَاضٍ»","source":"رواه ابن ماجه (2185) وابن حبان (4967) وصححه الألباني في الإرواء (1283)","benefit":"القاعدة العظمى في كافة العقود والمعاملات والمرابحات: بطلان أي عقد يشوبه إكراه أو غش أو تدليس أو غبن فاحش، واشتراط الرضا التام المتبادل لصحّة انتقال الملكية."},{"num":2,"cat":"sales","search":"2 خيار المجلس والبركة الكبرى في الصدق والبيان وشؤم الكتمان عن حكيم بن حزام رضي الله عنه قال: قال رسول الله ﷺ: «الْبَيِّعَانِ بِالْخِيَارِ مَا لَمْ يَتَفَرَّقَا، فَإِنْ صَدَقَا وَبَيَّنَا بُورِكَ لَهُمَا فِي بَيْعِهِمَا، وَإِنْ كَذَبَا وَكَتَمَا مُحِقَتْ بَرَكَةُ بَيْعِهِمَا» صحيح البخاري (2079) وصحيح مسلم (1532) إثبات خيار المجلس لكلا العاقدين، وبيان أن أرباح التجارة لا بركة فيها إن بنيت على إخفاء عيوب السلع أو المبالغة الكاذبة، وأن الشفافية التامة هي جالبة النماء. البيعان بالخيار ما لم يتفرقا صدقا وبينا محقت بركة بيعهما خيار المجلس الصدق","title":"خيار المجلس والبركة الكبرى في الصدق والبيان وشؤم الكتمان","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ متفق عليه","sanad":"عن حكيم بن حزام رضي الله عنه قال: قال رسول الله ﷺ:","matn":"«الْبَيِّعَانِ بِالْخِيَارِ مَا لَمْ يَتَفَرَّقَا، فَإِنْ صَدَقَا وَبَيَّنَا بُورِكَ لَهُمَا فِي بَيْعِهِمَا، وَإِنْ كَذَبَا وَكَتَمَا مُحِقَتْ بَرَكَةُ بَيْعِهِمَا»","source":"صحيح البخاري (2079) وصحيح مسلم (1532)","benefit":"إثبات خيار المجلس لكلا العاقدين، وبيان أن أرباح التجارة لا بركة فيها إن بنيت على إخفاء عيوب السلع أو المبالغة الكاذبة، وأن الشفافية التامة هي جالبة النماء."},{"num":3,"cat":"sales","search":"3 النهي الصارم عن بيع السلع قبل تملكها وقبضها (لا تبع ما ليس عندك) عن حكيم بن حزام رضي الله عنه قال: سألت النبي ﷺ فقلت: يأتيني الرجل فيريد مني البيع ليس عندي أفأبتاعه له من السوق؟ فقال: «لَا تَبِعْ مَا لَيْسَ عِنْدَكَ» رواه أبو داود (3503) والترمذي (1232) والنسائي (4613) وصححه الترمذي والألباني تحريم بيع الأعيان غير المملوكة للبائع درءاً للعجز عن التسليم والغرر والمخاطرة، وهو أصل تشريعي يضبط تجارة الدروب شيبينغ المعاصرة والبيع على المكشوف (Short Selling). لا تبع ما ليس عندك حكيم بن حزام بيع المعدوم الدروب شيبينغ البيع على المكشوف","title":"النهي الصارم عن بيع السلع قبل تملكها وقبضها (لا تبع ما ليس عندك)","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ صحيح","sanad":"عن حكيم بن حزام رضي الله عنه قال: سألت النبي ﷺ فقلت: يأتيني الرجل فيريد مني البيع ليس عندي أفأبتاعه له من السوق؟ فقال:","matn":"«لَا تَبِعْ مَا لَيْسَ عِنْدَكَ»","source":"رواه أبو داود (3503) والترمذي (1232) والنسائي (4613) وصححه الترمذي والألباني","benefit":"تحريم بيع الأعيان غير المملوكة للبائع درءاً للعجز عن التسليم والغرر والمخاطرة، وهو أصل تشريعي يضبط تجارة الدروب شيبينغ المعاصرة والبيع على المكشوف (Short Selling)."},{"num":4,"cat":"sales","search":"4 النهي عن بيوع الغرر والمخاطرة والمجهولات في الأسواق عن أبي هريرة رضي الله عنه قال: «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعِ الْحَصَاةِ، وَعَنْ بَيْعِ الْغَرَرِ» صحيح مسلم (1513) الأصل الجوهري في تحريم المعاملات المالية المشتملة على مجهول العاقبة، كعقود المشتقات المالية الاحتيالية والمقامرات الرقمية وبيع الصناديق العشوائية مجهولة المحتوى. نهى عن بيع الغرر بيع الحصاة الجهالة المخاطرة المشتقات المالية القمار","title":"النهي عن بيوع الغرر والمخاطرة والمجهولات في الأسواق","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ صحيح مسلم","sanad":"عن أبي هريرة رضي الله عنه قال:","matn":"«نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعِ الْحَصَاةِ، وَعَنْ بَيْعِ الْغَرَرِ»","source":"صحيح مسلم (1513)","benefit":"الأصل الجوهري في تحريم المعاملات المالية المشتملة على مجهول العاقبة، كعقود المشتقات المالية الاحتيالية والمقامرات الرقمية وبيع الصناديق العشوائية مجهولة المحتوى."},{"num":5,"cat":"sales","search":"5 اشتراط حيازة المبيع واستيفاء القبض قبل إعادة تصريفه وبيعه عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ: «مَنِ اشْتَرَى طَعَامًا فَلَا يَبِعْهُ حَتَّى يَسْتَوْفِيَهُ» وفي رواية: «حَتَّى يَقْبِضَهُ» صحيح البخاري (2136) وصحيح مسلم (1525) عدم جواز المتاجرة بالسلع وبيعها قبل دخولها في ضمان المشتري وحيازتها الفعلية أو الحكمية (كاستلام أوراق الشحن وبوالص التخزين الرسمية المعاصرة). من اشترى طعاما فلا يبعه حتى يستوفيه حتى يقبضه قبض المبيع الحيازة","title":"اشتراط حيازة المبيع واستيفاء القبض قبل إعادة تصريفه وبيعه","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ متفق عليه","sanad":"عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ:","matn":"«مَنِ اشْتَرَى طَعَامًا فَلَا يَبِعْهُ حَتَّى يَسْتَوْفِيَهُ» وفي رواية: «حَتَّى يَقْبِضَهُ»","source":"صحيح البخاري (2136) وصحيح مسلم (1525)","benefit":"عدم جواز المتاجرة بالسلع وبيعها قبل دخولها في ضمان المشتري وحيازتها الفعلية أو الحكمية (كاستلام أوراق الشحن وبوالص التخزين الرسمية المعاصرة)."},{"num":6,"cat":"sales","search":"6 النهي عن البيع على بيع المسلم والنجش واحتكار السلع بالخداع عن عبد الله بن عمر رضي الله عنهما أن رسول الله ﷺ قال: «لَا يَبِعْ بَعْضُكُمْ عَلَى بَيْعِ بَعْضٍ، وَلَا تَنَاجَشُوا، وَلَا يَبِعْ حَاضِرٌ لِبَادٍ» صحيح البخاري (2139) وصحيح مسلم (1412) حماية المنافسة الشريفة ومنع المزايدات الوهمية (النجش) في المزادات العلنية والرقمية، ومنع استغلال جهل القادمين من خارج السوق بالأسعار الحقيقية. لا يبع بعضكم على بيع بعض ولا تناجشوا ولا يبع حاضر لباد المزادات النجش","title":"النهي عن البيع على بيع المسلم والنجش واحتكار السلع بالخداع","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ متفق عليه","sanad":"عن عبد الله بن عمر رضي الله عنهما أن رسول الله ﷺ قال:","matn":"«لَا يَبِعْ بَعْضُكُمْ عَلَى بَيْعِ بَعْضٍ، وَلَا تَنَاجَشُوا، وَلَا يَبِعْ حَاضِرٌ لِبَادٍ»","source":"صحيح البخاري (2139) وصحيح مسلم (1412)","benefit":"حماية المنافسة الشريفة ومنع المزايدات الوهمية (النجش) في المزادات العلنية والرقمية، ومنع استغلال جهل القادمين من خارج السوق بالأسعار الحقيقية."},{"num":7,"cat":"sales","search":"7 النهي عن بيعتين في بيعة والصفقات المشروطة الملتوية عن أبي هريرة رضي الله عنه قال: «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعَتَيْنِ فِي بَيْعَةٍ» رواه الترمذي (1231) والنسائي (4632) وأحمد (9582) وصححه الترمذي والألباني تحريم العقود المتضمنة حيل الربا (كالعينة: أبيعك نقداً بـ 100 على أن تبيعني مؤجلاً بـ 120)، أو العقود التي تترك الثمن مجهولاً بين النقد والتقسيط دون حسم عند التعاقد. نهى عن بيعتين في بيعة العينة بيع وسلف الصفقات المشروطة","title":"النهي عن بيعتين في بيعة والصفقات المشروطة الملتوية","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ صحيح","sanad":"عن أبي هريرة رضي الله عنه قال:","matn":"«نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعَتَيْنِ فِي بَيْعَةٍ»","source":"رواه الترمذي (1231) والنسائي (4632) وأحمد (9582) وصححه الترمذي والألباني","benefit":"تحريم العقود المتضمنة حيل الربا (كالعينة: أبيعك نقداً بـ 100 على أن تبيعني مؤجلاً بـ 120)، أو العقود التي تترك الثمن مجهولاً بين النقد والتقسيط دون حسم عند التعاقد."},{"num":8,"cat":"sales","search":"8 النهي عن بيع الثمار والمحاصيل قبل بدو صلاحها وأمن العاهة عن أنس بن مالك رضي الله عنه أن رسول الله ﷺ: «نَهَى عَنْ بَيْعِ الثِّمَارِ حَتَّى تُزْهِيَ، قِيلَ: وَمَا تُزْهِي؟ قَالَ: حَتَّى تَحْمَرَّ، فَقَالَ: أَرَأَيْتَ إِذَا مَنَعَ اللَّهُ الثَّمَرَةَ بِمَ يَأْخُذُ أَحَدُكُمْ مَالَ أَخِيهِ؟» صحيح البخاري (2198) وصحيح مسلم (1555) قاعدة منع بيع الإنتاج الزراعي قبل وصوله للطور الصالح للاستعمال وأمنه من الآفات، منعاً للخصومة وأكل أموال المشترين بغير مقابل مشروع. نهى عن بيع الثمار حتى تزهي تحمر بم يأخذ أحدكم مال أخيه ضمان المحاصيل","title":"النهي عن بيع الثمار والمحاصيل قبل بدو صلاحها وأمن العاهة","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ متفق عليه","sanad":"عن أنس بن مالك رضي الله عنه أن رسول الله ﷺ:","matn":"«نَهَى عَنْ بَيْعِ الثِّمَارِ حَتَّى تُزْهِيَ، قِيلَ: وَمَا تُزْهِي؟ قَالَ: حَتَّى تَحْمَرَّ، فَقَالَ: أَرَأَيْتَ إِذَا مَنَعَ اللَّهُ الثَّمَرَةَ بِمَ يَأْخُذُ أَحَدُكُمْ مَالَ أَخِيهِ؟»","source":"صحيح البخاري (2198) وصحيح مسلم (1555)","benefit":"قاعدة منع بيع الإنتاج الزراعي قبل وصوله للطور الصالح للاستعمال وأمنه من الآفات، منعاً للخصومة وأكل أموال المشترين بغير مقابل مشروع."},{"num":9,"cat":"sales","search":"9 تشريع وضع الجوائح والكوارث الطارئة عن المشترين والمزارعين عن جابر بن عبد الله رضي الله عنهما أن النبي ﷺ قال: «لَوْ بِعْتَ مِنْ أَخِيكَ ثَمَرًا فَأَصَابَتْهُ جَائِحَةٌ، فَلَا يَحِلُّ لَكَ أَنْ تَأْخُذَ مِنْهُ شَيْئًا، بِمَ تَأْخُذُ مَالَ أَخِيكَ بِغَيْرِ حَقٍّ؟» صحيح مسلم (1554) أصل فقهي عظيم في نظرية الظروف الطارئة والقوة القاهرة المعاصرة: إسقاط التكاليف عن المشتري إذا تلفت البضاعة بآفة سماوية لا يد له فيها قبل التمكن من حيازتها. وضع الجوائح جائحة بم تأخذ مال أخيك بغير حق القوة القاهرة الكوارث","title":"تشريع وضع الجوائح والكوارث الطارئة عن المشترين والمزارعين","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ صحيح مسلم","sanad":"عن جابر بن عبد الله رضي الله عنهما أن النبي ﷺ قال:","matn":"«لَوْ بِعْتَ مِنْ أَخِيكَ ثَمَرًا فَأَصَابَتْهُ جَائِحَةٌ، فَلَا يَحِلُّ لَكَ أَنْ تَأْخُذَ مِنْهُ شَيْئًا، بِمَ تَأْخُذُ مَالَ أَخِيكَ بِغَيْرِ حَقٍّ؟»","source":"صحيح مسلم (1554)","benefit":"أصل فقهي عظيم في نظرية الظروف الطارئة والقوة القاهرة المعاصرة: إسقاط التكاليف عن المشتري إذا تلفت البضاعة بآفة سماوية لا يد له فيها قبل التمكن من حيازتها."},{"num":10,"cat":"sales","search":"10 وجوب الوفاء بالشروط التجارية والعقود الرضائية المبرمة عن عمرو بن عوف المزني رضي الله عنه أن رسول الله ﷺ قال: «الْمُسْلِمُونَ عَلَى شُرُوطِهِمْ، إِلَّا شَرْطًا حَرَّمَ حَلَالًا، أَوْ أَحَلَّ حَرَامًا» رواه الترمذي (1352) وصححه، وأبو داود (3594)، وصححه الألباني في الإرواء (1303) حرية الاشتراط والتنظيم التعاقدي في المعاملات المالية، وإلزامية مواعيد التسليم والدفعات وجداول السداد وغرامات التأخير غير الربوية ما دامت متفقة مع أحكام الشريعة. المسلمون على شروطهم الشروط العقدية الالتزامات الوفاء بالعقود","title":"وجوب الوفاء بالشروط التجارية والعقود الرضائية المبرمة","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ صحيح","sanad":"عن عمرو بن عوف المزني رضي الله عنه أن رسول الله ﷺ قال:","matn":"«الْمُسْلِمُونَ عَلَى شُرُوطِهِمْ، إِلَّا شَرْطًا حَرَّمَ حَلَالًا، أَوْ أَحَلَّ حَرَامًا»","source":"رواه الترمذي (1352) وصححه، وأبو داود (3594)، وصححه الألباني في الإرواء (1303)","benefit":"حرية الاشتراط والتنظيم التعاقدي في المعاملات المالية، وإلزامية مواعيد التسليم والدفعات وجداول السداد وغرامات التأخير غير الربوية ما دامت متفقة مع أحكام الشريعة."},{"num":11,"cat":"sales","search":"11 تحريم الغش والتطفيف في البضائع والمواصفات والبراءة النبوية من الغاش عن أبي هريرة رضي الله عنه أن رسول الله ﷺ مر على صُبرة طعام فأدخل يده فيها فنالت أصابعه بللاً، فقال: ما هذا يا صاحب الطعام؟ قال: أصابته السماء يا رسول الله، قال: «أَفَلَا جَعَلْتَهُ فَوْقَ الطَّعَامِ كَيْ يَرَاهُ النَّاسُ، مَنْ غَشَّ فَلَيْسَ مِنِّي» صحيح مسلم (102) وجوب الإفصاح والشفافية التامة عن كل عيب خفي في السلع أو الخدمات، وتحريم التلاعب بتاريخ الصلاحية أو بلد المنشأ أو إخفاء المعيب تحت السليم. من غش فليس مني صبرة طعام أصابته السماء عيوب السلع الغش التجاري","title":"تحريم الغش والتطفيف في البضائع والمواصفات والبراءة النبوية من الغاش","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ صحيح مسلم","sanad":"عن أبي هريرة رضي الله عنه أن رسول الله ﷺ مر على صُبرة طعام فأدخل يده فيها فنالت أصابعه بللاً، فقال: ما هذا يا صاحب الطعام؟ قال: أصابته السماء يا رسول الله، قال:","matn":"«أَفَلَا جَعَلْتَهُ فَوْقَ الطَّعَامِ كَيْ يَرَاهُ النَّاسُ، مَنْ غَشَّ فَلَيْسَ مِنِّي»","source":"صحيح مسلم (102)","benefit":"وجوب الإفصاح والشفافية التامة عن كل عيب خفي في السلع أو الخدمات، وتحريم التلاعب بتاريخ الصلاحية أو بلد المنشأ أو إخفاء المعيب تحت السليم."},{"num":12,"cat":"sales","search":"12 فضل التاجر الصدوق الأمين ومنزلته العالية في الجنة مع النبيين والشهداء عن أبي سعيد الخدري رضي الله عنه عن النبي ﷺ قال: «التَّاجِرُ الصَّدُوقُ الْأَمِينُ مَعَ النَّبِيِّينَ، وَالصِّدِّيقِينَ، وَالشُّهَدَاءِ» رواه الترمذي (1209) وصححه، والدارمي (2539)، وصححه الألباني في صحيح الترغيب (1782) بيان شرف مهنة التجارة والمحاسبة والمال إذا التزم صاحبها بصدق الوصف والأمانة في الحسابات والفواتير والوفاء بالعهود دون جشع أو احتيال. التاجر الصدوق الأمين مع النبيين والصديقين والشهداء بركة التجارة الأمانة","title":"فضل التاجر الصدوق الأمين ومنزلته العالية في الجنة مع النبيين والشهداء","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ صحيح","sanad":"عن أبي سعيد الخدري رضي الله عنه عن النبي ﷺ قال:","matn":"«التَّاجِرُ الصَّدُوقُ الْأَمِينُ مَعَ النَّبِيِّينَ، وَالصِّدِّيقِينَ، وَالشُّهَدَاءِ»","source":"رواه الترمذي (1209) وصححه، والدارمي (2539)، وصححه الألباني في صحيح الترغيب (1782)","benefit":"بيان شرف مهنة التجارة والمحاسبة والمال إذا التزم صاحبها بصدق الوصف والأمانة في الحسابات والفواتير والوفاء بالعهود دون جشع أو احتيال."},{"num":13,"cat":"sales","search":"13 السماحة واليسر في البيع والشراء والمطالبة بالحقوق واستجلاب رحمة الله عن جابر بن عبد الله رضي الله عنهما أن رسول الله ﷺ قال: «رَحِمَ اللَّهُ رَجُلًا سَمْحًا إِذَا بَاعَ، وَإِذَا اشْتَرَى، وَإِذَا اقْتَضَى» صحيح البخاري (2076) الحث على المرونة والتيسير وعدم المشاحة والتعنت في الأسعار والتفاوض وسداد المطالبات المالية، وهي روح الأخلاق الاقتصادية الإسلامية. رحم الله رجلا سمحا إذا باع وإذا اشترى وإذا اقتضى السماحة اليسر المعاملة الحسنة","title":"السماحة واليسر في البيع والشراء والمطالبة بالحقوق واستجلاب رحمة الله","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ صحيح البخاري","sanad":"عن جابر بن عبد الله رضي الله عنهما أن رسول الله ﷺ قال:","matn":"«رَحِمَ اللَّهُ رَجُلًا سَمْحًا إِذَا بَاعَ، وَإِذَا اشْتَرَى، وَإِذَا اقْتَضَى»","source":"صحيح البخاري (2076)","benefit":"الحث على المرونة والتيسير وعدم المشاحة والتعنت في الأسعار والتفاوض وسداد المطالبات المالية، وهي روح الأخلاق الاقتصادية الإسلامية."},{"num":14,"cat":"sales","search":"14 فضل إقالة النادم وقبول المرتجع وفسخ الصفقة لراحة الزبون عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ: «مَنْ أَقَالَ مُسْلِمًا، أَقَالَهُ اللَّهُ عَثْرَتَهُ يَوْمَ الْقِيَامَةِ» رواه أبو داود (3460) وابن ماجه (2199) وصححه ابن حبان (4931) والألباني استحباب قبول سياسة إرجاع البضائع واسترداد المشتري لماله إن ندم أو تبين عدم حاجته للسلعة، وأن جزاء ذلك تكفير الذنوب وتفريج الكربات يوم القيامة. من أقال مسلما أقاله الله عثرته إقالة النادم سياسة الاسترجاع المرتجعات","title":"فضل إقالة النادم وقبول المرتجع وفسخ الصفقة لراحة الزبون","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ صحيح","sanad":"عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ:","matn":"«مَنْ أَقَالَ مُسْلِمًا، أَقَالَهُ اللَّهُ عَثْرَتَهُ يَوْمَ الْقِيَامَةِ»","source":"رواه أبو داود (3460) وابن ماجه (2199) وصححه ابن حبان (4931) والألباني","benefit":"استحباب قبول سياسة إرجاع البضائع واسترداد المشتري لماله إن ندم أو تبين عدم حاجته للسلعة، وأن جزاء ذلك تكفير الذنوب وتفريج الكربات يوم القيامة."},{"num":15,"cat":"sales","search":"15 تحريم الحلف الكاذب أو الصادق لتنفيق البضائع وترويج المبيعات عن أبي هريرة رضي الله عنه قال: سمعت رسول الله ﷺ يقول: «الْحَلِفُ مُنَفِّقَةٌ لِلسِّلْعَةِ، مُمْحِقَةٌ لِلْبَرَكَةِ» صحيح البخاري (2087) وصحيح مسلم (1606) تحريم وتجريم الترويج الإعلاني المضلل باستخدام الأيمان والأقسام لاستدراج المشترين، والتحذير من أن المبيعات الناتجة عن ذلك تنزع البركة من رأس المال كلياً. الحلف منفقة للسلعة ممحقة للبركة الأيمان الكاذبة التسويق المضلل الإعلانات","title":"تحريم الحلف الكاذب أو الصادق لتنفيق البضائع وترويج المبيعات","catBadge":"🛒 بيوع ومرابحة وتجارة","gradeBadge":"✓ متفق عليه","sanad":"عن أبي هريرة رضي الله عنه قال: سمعت رسول الله ﷺ يقول:","matn":"«الْحَلِفُ مُنَفِّقَةٌ لِلسِّلْعَةِ، مُمْحِقَةٌ لِلْبَرَكَةِ»","source":"صحيح البخاري (2087) وصحيح مسلم (1606)","benefit":"تحريم وتجريم الترويج الإعلاني المضلل باستخدام الأيمان والأقسام لاستدراج المشترين، والتحذير من أن المبيعات الناتجة عن ذلك تنزع البركة من رأس المال كلياً."},{"num":16,"cat":"riba","search":"16 دستور الأصناف الربوية الستة وضوابط صرف العملات والسلع الأساسية عن عبادة بن الصامت رضي الله عنه قال: قال رسول الله ﷺ: «الذَّهَبُ بِالذَّهَبِ، وَالْفِضَّةُ بِالْفِضَّةِ، وَالْبُرُّ بِالْبُرِّ، وَالشَّعِيرُ بِالشَّعِيرِ، وَالتَّمْرُ بِالتَّمْرِ، وَالْمِلْحُ بِالْمِلْحِ، مِثْلًا بِمِثْلٍ، سَوَاءً بِسَوَاءٍ، يَدًا بِيَدٍ، فَإِذَا اخْتَلَفَتْ هَذِهِ الْأَصْنَافُ، فَبِيعُوا كَيْفَ شِئْتُمْ إِذَا كَانَ يَدًا بِيَدٍ» صحيح مسلم (1587) القانون الأساسي في تحريم ربا الفضل وربا النسيئة، واشتراط التقابض الفوري يداً بيد (Spot Exchange) عند تحويل العملات المختلفة كالدولار والريال واليورو لمنع الوقوع في الصرف المؤجل المحرم. الذهب بالذهب والفضة بالفضة مثلا بمثل يدا بيد الأصناف الستة الصرف العملات ربا الفضل ربا النسيئة","title":"دستور الأصناف الربوية الستة وضوابط صرف العملات والسلع الأساسية","catBadge":"🚫 ربا وصرف وعملات","gradeBadge":"✓ صحيح مسلم","sanad":"عن عبادة بن الصامت رضي الله عنه قال: قال رسول الله ﷺ:","matn":"«الذَّهَبُ بِالذَّهَبِ، وَالْفِضَّةُ بِالْفِضَّةِ، وَالْبُرُّ بِالْبُرِّ، وَالشَّعِيرُ بِالشَّعِيرِ، وَالتَّمْرُ بِالتَّمْرِ، وَالْمِلْحُ بِالْمِلْحِ، مِثْلًا بِمِثْلٍ، سَوَاءً بِسَوَاءٍ، يَدًا بِيَدٍ، فَإِذَا اخْتَلَفَتْ هَذِهِ الْأَصْنَافُ، فَبِيعُوا كَيْفَ شِئْتُمْ إِذَا كَانَ يَدًا بِيَدٍ»","source":"صحيح مسلم (1587)","benefit":"القانون الأساسي في تحريم ربا الفضل وربا النسيئة، واشتراط التقابض الفوري يداً بيد (Spot Exchange) عند تحويل العملات المختلفة كالدولار والريال واليورو لمنع الوقوع في الصرف المؤجل المحرم."},{"num":17,"cat":"riba","search":"17 تحريم التفاضل في مبادلة الجنس الواحد من الذهب والفضة وصرفهما عن أبي سعيد الخدري رضي الله عنه أن رسول الله ﷺ قال: «لَا تَبِيعُوا الذَّهَبَ بِالذَّهَبِ إِلَّا مِثْلًا بِمِثْلٍ، وَلَا تُشِفُّوا بَعْضَهَا عَلَى بَعْضٍ، وَلَا تَبِيعُوا الْوَرِقَ بِالْوَرِقِ إِلَّا مِثْلًا بِمِثْلٍ، وَلَا تُشِفُّوا بَعْضَهَا عَلَى بَعْضٍ، وَلَا تَبِيعُوا مِنْهَا غَائِبًا بِنَاجِزٍ» صحيح البخاري (2177) وصحيح مسلم (1584) منع دفع أي زيادة (تشفوا) عند استبدال ذهب قديم بذهب جديد أو مكسور بمصاغ، ووجوب التماثل التام في الوزن وزناً بوزن، أو البيع نقداً ثم الشراء نقداً. لا تبيعوا الذهب بالذهب إلا مثلا بمثل ولا تشفوا الورق غائبا بناجز مصاغ الذهب ربا الفضل","title":"تحريم التفاضل في مبادلة الجنس الواحد من الذهب والفضة وصرفهما","catBadge":"🚫 ربا وصرف وعملات","gradeBadge":"✓ متفق عليه","sanad":"عن أبي سعيد الخدري رضي الله عنه أن رسول الله ﷺ قال:","matn":"«لَا تَبِيعُوا الذَّهَبَ بِالذَّهَبِ إِلَّا مِثْلًا بِمِثْلٍ، وَلَا تُشِفُّوا بَعْضَهَا عَلَى بَعْضٍ، وَلَا تَبِيعُوا الْوَرِقَ بِالْوَرِقِ إِلَّا مِثْلًا بِمِثْلٍ، وَلَا تُشِفُّوا بَعْضَهَا عَلَى بَعْضٍ، وَلَا تَبِيعُوا مِنْهَا غَائِبًا بِنَاجِزٍ»","source":"صحيح البخاري (2177) وصحيح مسلم (1584)","benefit":"منع دفع أي زيادة (تشفوا) عند استبدال ذهب قديم بذهب جديد أو مكسور بمصاغ، ووجوب التماثل التام في الوزن وزناً بوزن، أو البيع نقداً ثم الشراء نقداً."},{"num":18,"cat":"riba","search":"18 حديث تمر خيبر والقاعدة الذهبية في تجنب ربا الفضل عبر النقد عن أبي سعيد الخدري وأبي هريرة رضي الله عنهما أن رسول الله ﷺ استعمل رجلاً على خيبر فجاءه بتمر جَنِيب، فقال: أكلُّ تمر خيبر هكذا؟ قال: لا والله يا رسول الله! إنا لنأخذ الصاع من هذا بالصاعين، والصاعين بالثلاثة، فقال ﷺ: «أَوَّهْ! عَيْنُ الرِّبَا! لَا تَفْعَلْ، وَلَكِنْ بِعِ الْجَمْعَ بِالدَّرَاهِمِ، ثُمَّ اشْتَرِ بِالدَّرَاهِمِ جَنِيبًا» صحيح البخاري (2201) وصحيح مسلم (1593) وضع الآلية المصرفية والتجارية الشرعية للخروج من مبادلة الأعيان الربوية المتفاوتة الجودة: بيع الرديء بالنقود أولاً، ثم شراء الجيد بالنقود في صفقة مستقلة منفصلة. تمر خيبر عين الربا بع الجمع بالدراهم ثم اشتر بالدراهم جنيبا ربا الفضل المبادلة العينية","title":"حديث تمر خيبر والقاعدة الذهبية في تجنب ربا الفضل عبر النقد","catBadge":"🚫 ربا وصرف وعملات","gradeBadge":"✓ متفق عليه","sanad":"عن أبي سعيد الخدري وأبي هريرة رضي الله عنهما أن رسول الله ﷺ استعمل رجلاً على خيبر فجاءه بتمر جَنِيب، فقال: أكلُّ تمر خيبر هكذا؟ قال: لا والله يا رسول الله! إنا لنأخذ الصاع من هذا بالصاعين، والصاعين بالثلاثة، فقال ﷺ:","matn":"«أَوَّهْ! عَيْنُ الرِّبَا! لَا تَفْعَلْ، وَلَكِنْ بِعِ الْجَمْعَ بِالدَّرَاهِمِ، ثُمَّ اشْتَرِ بِالدَّرَاهِمِ جَنِيبًا»","source":"صحيح البخاري (2201) وصحيح مسلم (1593)","benefit":"وضع الآلية المصرفية والتجارية الشرعية للخروج من مبادلة الأعيان الربوية المتفاوتة الجودة: بيع الرديء بالنقود أولاً، ثم شراء الجيد بالنقود في صفقة مستقلة منفصلة."},{"num":19,"cat":"riba","search":"19 لعن آكل الربا وموكله وموثقه وشاهديه واستواء الجميع في الوزر عن جابر بن عبد الله رضي الله عنهما قال: «لَعَنَ رَسُولُ اللَّهِ ﷺ آكِلَ الرِّبَا، وَمُؤْكِلَهُ، وَكَاتِبَهُ، وَشَاهِدَيْهِ، وَقَالَ: هُمْ سَوَاءٌ» صحيح مسلم (1598) تجريم كل وسيط أو محاسب أو مدقق أو موثق يتعاقد على فوائد ربوية أو يصيغ بنود الربا أو يوقع عليها، وإثبات أن الإعانة على المعاملة الربوية كأكلها تماماً. لعن آكل الربا وموكله وكاتبه وشاهديه هم سواء الإعانة على الحرام العقود الربوية","title":"لعن آكل الربا وموكله وموثقه وشاهديه واستواء الجميع في الوزر","catBadge":"🚫 ربا وصرف وعملات","gradeBadge":"✓ صحيح مسلم","sanad":"عن جابر بن عبد الله رضي الله عنهما قال:","matn":"«لَعَنَ رَسُولُ اللَّهِ ﷺ آكِلَ الرِّبَا، وَمُؤْكِلَهُ، وَكَاتِبَهُ، وَشَاهِدَيْهِ، وَقَالَ: هُمْ سَوَاءٌ»","source":"صحيح مسلم (1598)","benefit":"تجريم كل وسيط أو محاسب أو مدقق أو موثق يتعاقد على فوائد ربوية أو يصيغ بنود الربا أو يوقع عليها، وإثبات أن الإعانة على المعاملة الربوية كأكلها تماماً."},{"num":20,"cat":"riba","search":"20 التغليظ الشديد في جرم الربا وبيان شناعته وخطورته على المجتمع عن عبد الله بن مسعود رضي الله عنه عن النبي ﷺ قال: «الرِّبَا ثَلَاثَةٌ وَسَبْعُونَ بَابًا، أَيْسَرُهَا مِثْلُ أَنْ يَنْكِحَ الرَّجُلُ أُمَّهُ» رواه الحاكم في المستدرك (2259) وصححه، ورواه ابن ماجه (2275) وصححه الألباني في صحيح الجامع (3533) تحذير المسلمين والمستثمرين من الاستهانة بفوائد القروض والربا المقنع بألفاظ حديثة، وبيان أنه من أشد المحرمات الماحقة للبركة والمفسدة للأخلاق والاقتصاد. الربا ثلاثة وسبعون بابا أيسرها خطورة الربا الفوائد البنكية القروض الربوية","title":"التغليظ الشديد في جرم الربا وبيان شناعته وخطورته على المجتمع","catBadge":"🚫 ربا وصرف وعملات","gradeBadge":"✓ صحيح","sanad":"عن عبد الله بن مسعود رضي الله عنه عن النبي ﷺ قال:","matn":"«الرِّبَا ثَلَاثَةٌ وَسَبْعُونَ بَابًا، أَيْسَرُهَا مِثْلُ أَنْ يَنْكِحَ الرَّجُلُ أُمَّهُ»","source":"رواه الحاكم في المستدرك (2259) وصححه، ورواه ابن ماجه (2275) وصححه الألباني في صحيح الجامع (3533)","benefit":"تحذير المسلمين والمستثمرين من الاستهانة بفوائد القروض والربا المقنع بألفاظ حديثة، وبيان أنه من أشد المحرمات الماحقة للبركة والمفسدة للأخلاق والاقتصاد."},{"num":21,"cat":"riba","search":"21 ربا النسيئة وخطورة تأجيل تسليم البدلين في عقود الصرف عن أسامة بن زيد رضي الله عنهما أن رسول الله ﷺ قال: «إِنَّمَا الرِّبَا فِي النَّسِيئَةِ» صحيح البخاري (2178) وصحيح مسلم (1596) تأكيد أن الربا الأعظم والأخطر هو ربا النسيئة (تأجيل القبض أو زيادة المال مقابل مد أجل السداد)، وهو المعمول به في القروض الربوية والفوائد المركبة. إنما الربا في النسيئة تأخير القبض الصرف المؤجل الفوائد المركبة ربا الجاهلية","title":"ربا النسيئة وخطورة تأجيل تسليم البدلين في عقود الصرف","catBadge":"🚫 ربا وصرف وعملات","gradeBadge":"✓ متفق عليه","sanad":"عن أسامة بن زيد رضي الله عنهما أن رسول الله ﷺ قال:","matn":"«إِنَّمَا الرِّبَا فِي النَّسِيئَةِ»","source":"صحيح البخاري (2178) وصحيح مسلم (1596)","benefit":"تأكيد أن الربا الأعظم والأخطر هو ربا النسيئة (تأجيل القبض أو زيادة المال مقابل مد أجل السداد)، وهو المعمول به في القروض الربوية والفوائد المركبة."},{"num":22,"cat":"riba","search":"22 النهي عن بيع الكالئ بالكالئ (بيع الدين بالدين وتوريق الديون) عن عبد الله بن عمر رضي الله عنهما قال: «نَهَى النَّبِيُّ ﷺ عَنْ بَيْعِ الْكَالِئِ بِالْكَالِئِ» [أي: الدَّيْنِ بِالدَّيْنِ] رواه الحاكم (2341) والدارقطني (3079) والنووي في المجموع ونقل الإجماع على تحريمه شيخ الإسلام ابن تيمية تحريم بيع الديون المؤجلة بديون مؤجلة أخرى، وهو أصل قطعي يبطل تداول سندات الديون وخصم الكمبيالات المؤجلة بآجال أخرى والمشتقات المالية الائتمانية المعاصرة. نهى عن بيع الكالئ بالكالئ بيع الدين بالدين توريق الديون السندات خصم الأوراق التجارية","title":"النهي عن بيع الكالئ بالكالئ (بيع الدين بالدين وتوريق الديون)","catBadge":"🚫 ربا وصرف وعملات","gradeBadge":"✓ صحيح بمعناه إجماعاً","sanad":"عن عبد الله بن عمر رضي الله عنهما قال:","matn":"«نَهَى النَّبِيُّ ﷺ عَنْ بَيْعِ الْكَالِئِ بِالْكَالِئِ» [أي: الدَّيْنِ بِالدَّيْنِ]","source":"رواه الحاكم (2341) والدارقطني (3079) والنووي في المجموع ونقل الإجماع على تحريمه شيخ الإسلام ابن تيمية","benefit":"تحريم بيع الديون المؤجلة بديون مؤجلة أخرى، وهو أصل قطعي يبطل تداول سندات الديون وخصم الكمبيالات المؤجلة بآجال أخرى والمشتقات المالية الائتمانية المعاصرة."},{"num":23,"cat":"riba","search":"23 تحريم بيع العِينة والتحايل الصوري على القروض الربوية بالسلع عن عبد الله بن عمر رضي الله عنهما قال: سمعت رسول الله ﷺ يقول: «إِذَا تَبَايَعْتُمْ بِالْعِينَةِ، وَأَخَذْتُمْ أَذْنَابَ الْبَقَرِ، وَرَضِيتُمْ بِالزَّرْعِ، وَتَرَكْتُمُ الْجِهَادَ، سَلَّطَ اللَّهُ عَلَيْكُمْ ذُلًّا لَا يَنْزِعُهُ حَتَّى تَرْجِعُوا إِلَى دِينِكُمْ» رواه أبو داود (3462) وصححه الألباني في السلسلة الصحيحة (11) تحريم صور البيع الشكلي الذي يتخذ السلعة حيلة لأخذ نقد عاجل مقابل نقد آجل أكبر (كأن يبيع سلعة بالآجل بـ 120 ثم يشتريها نقداً بـ 100 في مجلس واحد). إذا تبايعتم بالعينة الحيل الربوية التورق المنظم القروض الصوريه أخذتم أذناب البقر","title":"تحريم بيع العِينة والتحايل الصوري على القروض الربوية بالسلع","catBadge":"🚫 ربا وصرف وعملات","gradeBadge":"✓ صحيح","sanad":"عن عبد الله بن عمر رضي الله عنهما قال: سمعت رسول الله ﷺ يقول:","matn":"«إِذَا تَبَايَعْتُمْ بِالْعِينَةِ، وَأَخَذْتُمْ أَذْنَابَ الْبَقَرِ، وَرَضِيتُمْ بِالزَّرْعِ، وَتَرَكْتُمُ الْجِهَادَ، سَلَّطَ اللَّهُ عَلَيْكُمْ ذُلًّا لَا يَنْزِعُهُ حَتَّى تَرْجِعُوا إِلَى دِينِكُمْ»","source":"رواه أبو داود (3462) وصححه الألباني في السلسلة الصحيحة (11)","benefit":"تحريم صور البيع الشكلي الذي يتخذ السلعة حيلة لأخذ نقد عاجل مقابل نقد آجل أكبر (كأن يبيع سلعة بالآجل بـ 120 ثم يشتريها نقداً بـ 100 في مجلس واحد)."},{"num":24,"cat":"riba","search":"24 تحريم الجمع بين سلف وبيع واشتراط نفع تجاري في القروض عن عبد الله بن عمرو رضي الله عنهما أن رسول الله ﷺ قال: «لَا يَحِلُّ سَلَفٌ وَبَيْعٌ، وَلَا شَرْطَانِ فِي بَيْعٍ، وَلَا رِبْحُ مَا لَمْ يُضْمَنْ، وَلَا بَيْعُ مَا لَيْسَ عِنْدَكَ» رواه الترمذي (1234) وأبو داود (3504) والنسائي (4611) وصححه الترمذي والألباني قاعدة مانعة لتحويل عقود التبرع (القرض) إلى عقود معاوضة وربح (كل قرض جر نفعاً مشروطاً فهو ربا)، ومنع اشتراط الشراء أو البيع لمنحه قرضاً حسناً. لا يحل سلف وبيع شرطان في بيع ربح ما لم يضمن قرض جر نفعا ربا القروض","title":"تحريم الجمع بين سلف وبيع واشتراط نفع تجاري في القروض","catBadge":"🚫 ربا وصرف وعملات","gradeBadge":"✓ صحيح","sanad":"عن عبد الله بن عمرو رضي الله عنهما أن رسول الله ﷺ قال:","matn":"«لَا يَحِلُّ سَلَفٌ وَبَيْعٌ، وَلَا شَرْطَانِ فِي بَيْعٍ، وَلَا رِبْحُ مَا لَمْ يُضْمَنْ، وَلَا بَيْعُ مَا لَيْسَ عِنْدَكَ»","source":"رواه الترمذي (1234) وأبو داود (3504) والنسائي (4611) وصححه الترمذي والألباني","benefit":"قاعدة مانعة لتحويل عقود التبرع (القرض) إلى عقود معاوضة وربح (كل قرض جر نفعاً مشروطاً فهو ربا)، ومنع اشتراط الشراء أو البيع لمنحه قرضاً حسناً."},{"num":25,"cat":"riba","search":"25 عقوبة المرابين في البرزخ والسباحة في نهر الدم مع الحجارة عن سمرة بن جندب رضي الله عنه في حديث الرؤيا الطويل عن النبي ﷺ قال: «فَانْطَلَقْنَا حَتَّى أَتَيْنَا عَلَى نَهَرٍ مِنْ دَمٍ فِيهِ رَجُلٌ قَائِمٌ، وَعَلَى شَطِّ النَّهَرِ رَجُلٌ بَيْنَ يَدَيْهِ حِجَارَةٌ، فَأَقْبَلَ الرَّجُلُ الَّذِي فِي النَّهَرِ، فَإِذَا أَرَادَ أَنْ يَخْرُجَ رَمَى فِي فِيهِ بِحَجَرٍ، فَرَجَعَ حَيْثُ كَانَ... فَقُلْتُ: مَنْ هَذَا؟ قَالَا: الَّذِي يَأْكُلُ الرِّبَا» صحيح البخاري (2085) تحذير إيماني قاطع من كبائر المال الحرام، وبيان الجزاء البرزخي الصارم لمن يستحل امتصاص جهود ودماء المعوزين والمقترضين بفوائد الربا. حديث الرؤيا نهر الدم آكل الربا عقوبة الربا كبائر الأموال","title":"عقوبة المرابين في البرزخ والسباحة في نهر الدم مع الحجارة","catBadge":"🚫 ربا وصرف وعملات","gradeBadge":"✓ صحيح البخاري","sanad":"عن سمرة بن جندب رضي الله عنه في حديث الرؤيا الطويل عن النبي ﷺ قال:","matn":"«فَانْطَلَقْنَا حَتَّى أَتَيْنَا عَلَى نَهَرٍ مِنْ دَمٍ فِيهِ رَجُلٌ قَائِمٌ، وَعَلَى شَطِّ النَّهَرِ رَجُلٌ بَيْنَ يَدَيْهِ حِجَارَةٌ، فَأَقْبَلَ الرَّجُلُ الَّذِي فِي النَّهَرِ، فَإِذَا أَرَادَ أَنْ يَخْرُجَ رَمَى فِي فِيهِ بِحَجَرٍ، فَرَجَعَ حَيْثُ كَانَ... فَقُلْتُ: مَنْ هَذَا؟ قَالَا: الَّذِي يَأْكُلُ الرِّبَا»","source":"صحيح البخاري (2085)","benefit":"تحذير إيماني قاطع من كبائر المال الحرام، وبيان الجزاء البرزخي الصارم لمن يستحل امتصاص جهود ودماء المعوزين والمقترضين بفوائد الربا."},{"num":26,"cat":"debts","search":"26 نية سداد الدين واستجلاب عون الله وتيسير الرزق وعقوبة الإتلاف عن أبي هريرة رضي الله عنه عن النبي ﷺ قال: «مَنْ أَخَذَ أَمْوَالَ النَّاسِ يُرِيدُ أَدَاءَهَا أَدَّى اللَّهُ عَنْهُ، وَمَنْ أَخَذَ يُرِيدُ إِتْلَافَهَا أَتْلَفَهُ اللَّهُ» صحيح البخاري (2387) النية الصادقة في سداد الالتزامات المالية والديون تستجلب معونة الله وتيسير أبواب الكسب، ونية المماطلة وأكل أموال الدائنين سبب مباشر لمحق رأس المال والهلاك. من أخذ أموال الناس يريد أداءها أدى الله عنه إتلافها أتلفه الله نية السداد القروض","title":"نية سداد الدين واستجلاب عون الله وتيسير الرزق وعقوبة الإتلاف","catBadge":"💳 ديون وقروض ورهن","gradeBadge":"✓ صحيح البخاري","sanad":"عن أبي هريرة رضي الله عنه عن النبي ﷺ قال:","matn":"«مَنْ أَخَذَ أَمْوَالَ النَّاسِ يُرِيدُ أَدَاءَهَا أَدَّى اللَّهُ عَنْهُ، وَمَنْ أَخَذَ يُرِيدُ إِتْلَافَهَا أَتْلَفَهُ اللَّهُ»","source":"صحيح البخاري (2387)","benefit":"النية الصادقة في سداد الالتزامات المالية والديون تستجلب معونة الله وتيسير أبواب الكسب، ونية المماطلة وأكل أموال الدائنين سبب مباشر لمحق رأس المال والهلاك."},{"num":27,"cat":"debts","search":"27 تحريم مَطْل الغني والمماطلة في الديون الحالة ومشروعية الحوالة المالية عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال: «مَطْلُ الْغَنِيِّ ظُلْمٌ، فَإِذَا أُتْبِعَ أَحَدُكُمْ عَلَى مَلِيءٍ فَلْيَتْبَعْ» صحيح البخاري (2287) وصحيح مسلم (1564) تحريم تأخير سداد المستحقات والفواتير من المقتدر، ومشروعية عقد الحوالة المالية المصرفية والتجارية إذا حُوِّل الدائن إلى طرف موسر قادر على الوفاء. مطل الغني ظلم إذا أتبع أحدكم على مليء فليتبع الحوالة المالية تسديد الديون المماطلة","title":"تحريم مَطْل الغني والمماطلة في الديون الحالة ومشروعية الحوالة المالية","catBadge":"💳 ديون وقروض ورهن","gradeBadge":"✓ متفق عليه","sanad":"عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال:","matn":"«مَطْلُ الْغَنِيِّ ظُلْمٌ، فَإِذَا أُتْبِعَ أَحَدُكُمْ عَلَى مَلِيءٍ فَلْيَتْبَعْ»","source":"صحيح البخاري (2287) وصحيح مسلم (1564)","benefit":"تحريم تأخير سداد المستحقات والفواتير من المقتدر، ومشروعية عقد الحوالة المالية المصرفية والتجارية إذا حُوِّل الدائن إلى طرف موسر قادر على الوفاء."},{"num":28,"cat":"debts","search":"28 مشروعية ملاحقة المماطل الموسر قضائياً وجواز عقوبته وحبسه عن عمرو بن الشريد عن أبيه رضي الله عنه عن رسول الله ﷺ قال: «لَيُّ الْوَاجِدِ يُحِلُّ عِرْضَهُ وَعُقُوبَتَهُ» رواه أبو داود (3628) والنسائي (4689) وابن ماجه (2427) وصححه الألباني في الإرواء (1434) المماطل المقتدر (الواجد) يجوز شرعاً وقانوناً رفع الشكوى ضده، والتشهير به في السجلات الائتمانية الرسمية، وفرض العقوبات المالية والإدارية وحبسه لإلزامه بالوفاء. لي الواجد يحل عرضه وعقوبته المماطل حبس المدين القضاء التنفيذي السجلات الائتمانية","title":"مشروعية ملاحقة المماطل الموسر قضائياً وجواز عقوبته وحبسه","catBadge":"💳 ديون وقروض ورهن","gradeBadge":"✓ صحيح","sanad":"عن عمرو بن الشريد عن أبيه رضي الله عنه عن رسول الله ﷺ قال:","matn":"«لَيُّ الْوَاجِدِ يُحِلُّ عِرْضَهُ وَعُقُوبَتَهُ»","source":"رواه أبو داود (3628) والنسائي (4689) وابن ماجه (2427) وصححه الألباني في الإرواء (1434)","benefit":"المماطل المقتدر (الواجد) يجوز شرعاً وقانوناً رفع الشكوى ضده، والتشهير به في السجلات الائتمانية الرسمية، وفرض العقوبات المالية والإدارية وحبسه لإلزامه بالوفاء."},{"num":29,"cat":"debts","search":"29 خطورة الدين وتعلق نفس المؤمن وذمته به بعد الوفاة حتى يُقضى عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ: «نَفْسُ الْمُؤْمِنِ مُعَلَّقَةٌ بِدَيْنِهِ حَتَّى يُقْضَى عَنْهُ» رواه الترمذي (1078) وصححه، وابن ماجه (2413)، وصححه الألباني في صحيح الجامع (6779) وجوب المبادرة فور الوفاة بتصفية تركة الميت وسداد ديونه قبل توزيع أي ميراث أو تنفيذ أي وصية تبرعية، لأن براءة الذمة مقدمة على حقوق الورثة. نفس المؤمن معلقة بدينه حتى يقضى عنه قضاء الديون تصفية التركات سداد الديون","title":"خطورة الدين وتعلق نفس المؤمن وذمته به بعد الوفاة حتى يُقضى","catBadge":"💳 ديون وقروض ورهن","gradeBadge":"✓ صحيح","sanad":"عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ:","matn":"«نَفْسُ الْمُؤْمِنِ مُعَلَّقَةٌ بِدَيْنِهِ حَتَّى يُقْضَى عَنْهُ»","source":"رواه الترمذي (1078) وصححه، وابن ماجه (2413)، وصححه الألباني في صحيح الجامع (6779)","benefit":"وجوب المبادرة فور الوفاة بتصفية تركة الميت وسداد ديونه قبل توزيع أي ميراث أو تنفيذ أي وصية تبرعية، لأن براءة الذمة مقدمة على حقوق الورثة."},{"num":30,"cat":"debts","search":"30 قدسية حقوق العباد: الدين لا يسقط حتى بالشهادة في سبيل الله عن عبد الله بن عمرو بن العاص رضي الله عنهما أن رسول الله ﷺ قال: «يُغْفَرُ لِلشَّهِيدِ كُلُّ ذَنْبٍ إِلَّا الدَّيْنَ» صحيح مسلم (1886) التأكيد القاطع على أن حقوق العباد والديون والمعاملات المالية مبنية على المشاحة ولا تسقطها العبادات مهما عظمت، ووجوب حفظ سجلات الديون بدقة متناهية. يغفر للشهيد كل ذنب إلا الدين حقوق العباد التوثيق المحاسبي الديون والالتزامات","title":"قدسية حقوق العباد: الدين لا يسقط حتى بالشهادة في سبيل الله","catBadge":"💳 ديون وقروض ورهن","gradeBadge":"✓ صحيح مسلم","sanad":"عن عبد الله بن عمرو بن العاص رضي الله عنهما أن رسول الله ﷺ قال:","matn":"«يُغْفَرُ لِلشَّهِيدِ كُلُّ ذَنْبٍ إِلَّا الدَّيْنَ»","source":"صحيح مسلم (1886)","benefit":"التأكيد القاطع على أن حقوق العباد والديون والمعاملات المالية مبنية على المشاحة ولا تسقطها العبادات مهما عظمت، ووجوب حفظ سجلات الديون بدقة متناهية."},{"num":31,"cat":"debts","search":"31 فضل حسن القضاء وإعطاء الأفضل والزيادة غير المشروطة عند السداد عن أبي هريرة رضي الله عنه في قصة اقتضاء الأعرابي لبعيره من النبي ﷺ فقال ﷺ لأصحابه: اشتروا له، فطلبوا فلم يجدوا إلا سناً أفضل من سنه، فقال: «أَعْطُوهُ، فَإِنَّ مِنْ خَيْرِكُمْ أَحْسَنَكُمْ قَضَاءً» صحيح البخاري (2306) وصحيح مسلم (1601) استحباب مكافأة الدائن ورد الدين بأفضل منه صفةً أو زيادة في القدر بطيب نفس ورضا من المدين، ما لم تكن تلك الزيادة مشروطة مسبقاً أو متفقاً عليها فتصير ربا. خيركم أحسنكم قضاء حسن الوفاء رد القرض الزيادة غير المشروطة البركة في السداد","title":"فضل حسن القضاء وإعطاء الأفضل والزيادة غير المشروطة عند السداد","catBadge":"💳 ديون وقروض ورهن","gradeBadge":"✓ متفق عليه","sanad":"عن أبي هريرة رضي الله عنه في قصة اقتضاء الأعرابي لبعيره من النبي ﷺ فقال ﷺ لأصحابه: اشتروا له، فطلبوا فلم يجدوا إلا سناً أفضل من سنه، فقال:","matn":"«أَعْطُوهُ، فَإِنَّ مِنْ خَيْرِكُمْ أَحْسَنَكُمْ قَضَاءً»","source":"صحيح البخاري (2306) وصحيح مسلم (1601)","benefit":"استحباب مكافأة الدائن ورد الدين بأفضل منه صفةً أو زيادة في القدر بطيب نفس ورضا من المدين، ما لم تكن تلك الزيادة مشروطة مسبقاً أو متفقاً عليها فتصير ربا."},{"num":32,"cat":"debts","search":"32 مشروعية الرهن التجاري في الديون لتوثيق وضمان الحقوق المالية عن عائشة رضي الله عنها قالت: «تُوُفِّيَ رَسُولُ اللَّهِ ﷺ وَدِرْعُهُ مَرْهُونَةٌ عِنْدَ يَهُودِيٍّ بِثَلَاثِينَ صَاعًا مِنْ شَعِيرٍ» صحيح البخاري (2916) وصحيح مسلم (1603) مشروعية توثيق الديون بالضمانات العينية والرهون العقارية والتجارية لضمان استرداد الحقوق، وجواز معاملة غير المسلمين في المعاملات المالية المباحة. درعه مرهونة عند يهودي الرهن التجاري الضمانات العينية توثيق الديون","title":"مشروعية الرهن التجاري في الديون لتوثيق وضمان الحقوق المالية","catBadge":"💳 ديون وقروض ورهن","gradeBadge":"✓ متفق عليه","sanad":"عن عائشة رضي الله عنها قالت:","matn":"«تُوُفِّيَ رَسُولُ اللَّهِ ﷺ وَدِرْعُهُ مَرْهُونَةٌ عِنْدَ يَهُودِيٍّ بِثَلَاثِينَ صَاعًا مِنْ شَعِيرٍ»","source":"صحيح البخاري (2916) وصحيح مسلم (1603)","benefit":"مشروعية توثيق الديون بالضمانات العينية والرهون العقارية والتجارية لضمان استرداد الحقوق، وجواز معاملة غير المسلمين في المعاملات المالية المباحة."},{"num":33,"cat":"debts","search":"33 بقاء ملكية المرهون للراهن: له نماء العين وغلّتها وعليه ضمانها عن سعيد بن المسيب عن رسول الله ﷺ أنه قال: «لَا يَغْلَقُ الرَّهْنُ مِنْ صَاحِبِهِ الَّذِي رَهَنَهُ، لَهُ غُنْمُهُ، وَعَلَيْهِ غُرْمُهُ» رواه الشافعي والدارقطني (3031) والحاكم (2371) وصححه، وصححه الألباني في الإرواء (1401) الرهن لا يسلب المالك ملكيته، وأرباح وعوائد العين المرهونة (كإيجار العقار المرهون) ملك للراهن، ولا يحق للمرتهن (الدائن) الاستيلاء على الرهن إلا عبر بيعه لسداد دينه فقط ورد الباقي. لا يغلق الرهن له غنمه وعليه غرمه ملكية المرهون عوائد الرهن حماية حقوق المدين","title":"بقاء ملكية المرهون للراهن: له نماء العين وغلّتها وعليه ضمانها","catBadge":"💳 ديون وقروض ورهن","gradeBadge":"✓ صحيح","sanad":"عن سعيد بن المسيب عن رسول الله ﷺ أنه قال:","matn":"«لَا يَغْلَقُ الرَّهْنُ مِنْ صَاحِبِهِ الَّذِي رَهَنَهُ، لَهُ غُنْمُهُ، وَعَلَيْهِ غُرْمُهُ»","source":"رواه الشافعي والدارقطني (3031) والحاكم (2371) وصححه، وصححه الألباني في الإرواء (1401)","benefit":"الرهن لا يسلب المالك ملكيته، وأرباح وعوائد العين المرهونة (كإيجار العقار المرهون) ملك للراهن، ولا يحق للمرتهن (الدائن) الاستيلاء على الرهن إلا عبر بيعه لسداد دينه فقط ورد الباقي."},{"num":34,"cat":"debts","search":"34 فضل إنظار المعسر والتجاوز عنه والاستظلال في ظل عرش الرحمن عن أبي قتادة رضي الله عنه قال: سمعت رسول الله ﷺ يقول: «مَنْ سَرَّهُ أَنْ يُنْجِيَهُ اللَّهُ مِنْ كُرَبِ يَوْمِ الْقِيَامَةِ، فَلْيُنَفِّسْ عَنْ مُعْسِرٍ، أَوْ يَضَعْ عَنْهُ» صحيح مسلم (1563) الأجر الإيماني العظيم في تأجيل مواعيد سداد الديون للمتعثرين مالياً أو إسقاط جزء منها (التنازل والخصم)، وتفريج كربهم في مواسم الأزمات المالية. من سره أن ينجيه الله من كرب يوم القيامة فلينفس عن معسر أو يضع عنه إنظار المعسر","title":"فضل إنظار المعسر والتجاوز عنه والاستظلال في ظل عرش الرحمن","catBadge":"💳 ديون وقروض ورهن","gradeBadge":"✓ صحيح مسلم","sanad":"عن أبي قتادة رضي الله عنه قال: سمعت رسول الله ﷺ يقول:","matn":"«مَنْ سَرَّهُ أَنْ يُنْجِيَهُ اللَّهُ مِنْ كُرَبِ يَوْمِ الْقِيَامَةِ، فَلْيُنَفِّسْ عَنْ مُعْسِرٍ، أَوْ يَضَعْ عَنْهُ»","source":"صحيح مسلم (1563)","benefit":"الأجر الإيماني العظيم في تأجيل مواعيد سداد الديون للمتعثرين مالياً أو إسقاط جزء منها (التنازل والخصم)، وتفريج كربهم في مواسم الأزمات المالية."},{"num":35,"cat":"debts","search":"35 مضاعفة أجر الصدقة عن كل يوم تأخير يُنظَر فيه المدين المعسر عن بريدة الأسلمي رضي الله عنه قال: سمعت رسول الله ﷺ يقول: «مَنْ أَنْظَرَ مُعْسِرًا فَلَهُ بِكُلِّ يَوْمٍ مِثْلِهِ صَدَقَةٌ، ثُمَّ سَمِعْتُهُ يَقُولُ: مَنْ أَنْظَرَ مُعْسِرًا فَلَهُ بِكُلِّ يَوْمٍ مِثْلَيْهِ صَدَقَةٌ، فَقُلْتُ: سَمِعْتُكَ يَا رَسُولَ اللَّهِ تَقُولُ... فَقَالَ: لَهُ بِكُلِّ يَوْمٍ مِثْلُهُ صَدَقَةٌ قَبْلَ أَنْ يَحِلَّ الدَّيْنُ، فَإِذَا حَلَّ الدَّيْنُ فَأَنْظَرَهُ فَلَهُ بِكُلِّ يَوْمٍ مِثْلَاهُ صَدَقَةٌ» رواه أحمد (22998) وابن ماجه (2418) والحاكم وصححه، وصححه الألباني في السلسلة الصحيحة (86) أعظم استثمار أخروي لرأس المال: اعتبار مهلة السداد الممنوحة للمدين المعسر صدقة يومية مضاعفة تعادل أصل الدين ومثليه في الميزان. من أنظر معسرا فله بكل يوم مثله صدقة مثلاه صدقة ثواب إنظار المعسر التمويل التنموي","title":"مضاعفة أجر الصدقة عن كل يوم تأخير يُنظَر فيه المدين المعسر","catBadge":"💳 ديون وقروض ورهن","gradeBadge":"✓ صحيح","sanad":"عن بريدة الأسلمي رضي الله عنه قال: سمعت رسول الله ﷺ يقول:","matn":"«مَنْ أَنْظَرَ مُعْسِرًا فَلَهُ بِكُلِّ يَوْمٍ مِثْلِهِ صَدَقَةٌ، ثُمَّ سَمِعْتُهُ يَقُولُ: مَنْ أَنْظَرَ مُعْسِرًا فَلَهُ بِكُلِّ يَوْمٍ مِثْلَيْهِ صَدَقَةٌ، فَقُلْتُ: سَمِعْتُكَ يَا رَسُولَ اللَّهِ تَقُولُ... فَقَالَ: لَهُ بِكُلِّ يَوْمٍ مِثْلُهُ صَدَقَةٌ قَبْلَ أَنْ يَحِلَّ الدَّيْنُ، فَإِذَا حَلَّ الدَّيْنُ فَأَنْظَرَهُ فَلَهُ بِكُلِّ يَوْمٍ مِثْلَاهُ صَدَقَةٌ»","source":"رواه أحمد (22998) وابن ماجه (2418) والحاكم وصححه، وصححه الألباني في السلسلة الصحيحة (86)","benefit":"أعظم استثمار أخروي لرأس المال: اعتبار مهلة السداد الممنوحة للمدين المعسر صدقة يومية مضاعفة تعادل أصل الدين ومثليه في الميزان."},{"num":36,"cat":"debts","search":"36 مشروعية الكفالة والضمان المالي (الزعيم غارم وضامن للدين) عن أبي أمامة الباهلي رضي الله عنه قال: سمعت رسول الله ﷺ يقول: «الْعَارِيَّةُ مُؤَدَّاةٌ، وَالْمِنْحَةُ مَرْدُودَةٌ، وَالدَّيْنُ مَقْضِيٌّ، وَالزَّعِيمُ غَارِمٌ» رواه أبو داود (3565) والترمذي (2120) وصححه، وصححه الألباني في صحيح الجامع (4068) تأصيل مشروعية عقود الكفالة والضمان المصرفي وخطابات الضمان البنكية، والتزام الكفيل قانوناً وشرعاً بسداد الدين كاملاً إذا تعذر السداد من المدين الأصلي. الزعيم غارم الكفالة الضمان خطابات الضمان العارية مؤداة الدين مقضي","title":"مشروعية الكفالة والضمان المالي (الزعيم غارم وضامن للدين)","catBadge":"💳 ديون وقروض ورهن","gradeBadge":"✓ صحيح","sanad":"عن أبي أمامة الباهلي رضي الله عنه قال: سمعت رسول الله ﷺ يقول:","matn":"«الْعَارِيَّةُ مُؤَدَّاةٌ، وَالْمِنْحَةُ مَرْدُودَةٌ، وَالدَّيْنُ مَقْضِيٌّ، وَالزَّعِيمُ غَارِمٌ»","source":"رواه أبو داود (3565) والترمذي (2120) وصححه، وصححه الألباني في صحيح الجامع (4068)","benefit":"تأصيل مشروعية عقود الكفالة والضمان المصرفي وخطابات الضمان البنكية، والتزام الكفيل قانوناً وشرعاً بسداد الدين كاملاً إذا تعذر السداد من المدين الأصلي."},{"num":37,"cat":"debts","search":"37 أحكام إفلاس المدين وحق البائع في استرداد عين سلعته قبل الغرماء عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال: «مَنْ أَدْرَكَ مَالَهُ بِعَيْنِهِ عِنْدَ رَجُلٍ قَدْ أَفْلَسَ، فَهُوَ أَحَقُّ بِهِ مِنْ غَيْرِهِ» صحيح البخاري (2402) وصحيح مسلم (1559) قاعدة حقوق الأولوية والامتياز عند إفلاس الشركات أو الأفراد: إذا وجد المورد أو التاجر بضاعته لم تتغير ولم تُبع، فله الأولوية في استردادها بعينها دون الدخول في قسمة الغرماء. من أدرك ماله بعينه عند رجل قد أفلس الإفلاس قسمة الغرماء حقوق الأولوية استرداد البضاعة","title":"أحكام إفلاس المدين وحق البائع في استرداد عين سلعته قبل الغرماء","catBadge":"💳 ديون وقروض ورهن","gradeBadge":"✓ متفق عليه","sanad":"عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال:","matn":"«مَنْ أَدْرَكَ مَالَهُ بِعَيْنِهِ عِنْدَ رَجُلٍ قَدْ أَفْلَسَ، فَهُوَ أَحَقُّ بِهِ مِنْ غَيْرِهِ»","source":"صحيح البخاري (2402) وصحيح مسلم (1559)","benefit":"قاعدة حقوق الأولوية والامتياز عند إفلاس الشركات أو الأفراد: إذا وجد المورد أو التاجر بضاعته لم تتغير ولم تُبع، فله الأولوية في استردادها بعينها دون الدخول في قسمة الغرماء."},{"num":38,"cat":"zakat","search":"38 فريضة الزكاة كركن مالي تعبدي ثالث بني عليه صرح الإسلام عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ: «بُنِيَ الْإِسْلَامُ عَلَى خَمْسٍ: شَهَادَةِ أَنْ لَا إِلَهَ إِلَّا اللَّهُ وَأَنَّ مُحَمَّدًا رَسُولُ اللَّهِ، وَإِقَامِ الصَّلَاةِ، وَإِيتَاءِ الزَّكَاةِ، وَالْحَجِّ، وَصَوْمِ رَمَضَانَ» صحيح البخاري (8) وصحيح مسلم (16) الزكاة ركن مالي واجتماعي واقتصادي قطعي لا يقوم إسلام العبد إلا بأدائه، وهي ليست ضريبة بشرية بل فريضة إلهية تطهر النفوس وتنمي الثروة. بني الإسلام على خمس وإيتاء الزكاة ركن الإسلام فريضة الزكاة","title":"فريضة الزكاة كركن مالي تعبدي ثالث بني عليه صرح الإسلام","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ متفق عليه","sanad":"عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ:","matn":"«بُنِيَ الْإِسْلَامُ عَلَى خَمْسٍ: شَهَادَةِ أَنْ لَا إِلَهَ إِلَّا اللَّهُ وَأَنَّ مُحَمَّدًا رَسُولُ اللَّهِ، وَإِقَامِ الصَّلَاةِ، وَإِيتَاءِ الزَّكَاةِ، وَالْحَجِّ، وَصَوْمِ رَمَضَانَ»","source":"صحيح البخاري (8) وصحيح مسلم (16)","benefit":"الزكاة ركن مالي واجتماعي واقتصادي قطعي لا يقوم إسلام العبد إلا بأدائه، وهي ليست ضريبة بشرية بل فريضة إلهية تطهر النفوس وتنمي الثروة."},{"num":39,"cat":"zakat","search":"39 دستور الزكاة النبوي: تؤخذ من أغنياء المجتمع وترد إلى فقرائه عن عبد الله بن عباس رضي الله عنهما أن رسول الله ﷺ لما بعث معاذاً رضي الله عنه إلى اليمن قال: «فَأَعْلِمْهُمْ أَنَّ اللَّهَ افْتَرَضَ عَلَيْهِمْ صَدَقَةً فِي أَمْوَالِهِمْ، تُؤْخَذُ مِنْ أَغْنِيَائِهِمْ، وَتُرَدُّ عَلَى فُقَرَائِهِمْ» صحيح البخاري (1395) وصحيح مسلم (19) أصل التكافل المالي المحلي: أولوية توزيع حصيلة الزكاة على فقراء ومحتاجي البلد أو المنطقة التي جُمعت منها الأموال لتحقيق الاستقرار الاجتماعي ومحاربة الفقر. تؤخذ من أغنيائهم وترد على فقرائهم معاذ بن جبل فريضة الزكاة التكافل الاجتماعي","title":"دستور الزكاة النبوي: تؤخذ من أغنياء المجتمع وترد إلى فقرائه","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ متفق عليه","sanad":"عن عبد الله بن عباس رضي الله عنهما أن رسول الله ﷺ لما بعث معاذاً رضي الله عنه إلى اليمن قال:","matn":"«فَأَعْلِمْهُمْ أَنَّ اللَّهَ افْتَرَضَ عَلَيْهِمْ صَدَقَةً فِي أَمْوَالِهِمْ، تُؤْخَذُ مِنْ أَغْنِيَائِهِمْ، وَتُرَدُّ عَلَى فُقَرَائِهِمْ»","source":"صحيح البخاري (1395) وصحيح مسلم (19)","benefit":"أصل التكافل المالي المحلي: أولوية توزيع حصيلة الزكاة على فقراء ومحتاجي البلد أو المنطقة التي جُمعت منها الأموال لتحقيق الاستقرار الاجتماعي ومحاربة الفقر."},{"num":40,"cat":"zakat","search":"40 تحديد أنصبة الزكاة الأساسية في النقد والمواشي والإنتاج الزراعي عن أبي سعيد الخدري رضي الله عنه عن النبي ﷺ قال: «لَيْسَ فِيمَا دُونَ خَمْسِ أَوَاقٍ صَدَقَةٌ، وَلَيْسَ فِيمَا دُونَ خَمْسِ ذَوْدٍ صَدَقَةٌ، وَلَيْسَ فِيمَا دُونَ خَمْسَةِ أَوْسُقٍ صَدَقَةٌ» صحيح البخاري (1405) وصحيح مسلم (979) حماية صغار الكسبة والملاك: لا تجب الزكاة إلا بعد بلوغ النصاب المحكم (5 أواق فضة = 595 غراماً، 5 جمال، 5 أوسق حبوب وثمار = حوالي 653 كغ). ليس فيما دون خمس أواق صدقة خمس ذود خمسة أوسق نصاب الزكاة الفضة الحبوب","title":"تحديد أنصبة الزكاة الأساسية في النقد والمواشي والإنتاج الزراعي","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ متفق عليه","sanad":"عن أبي سعيد الخدري رضي الله عنه عن النبي ﷺ قال:","matn":"«لَيْسَ فِيمَا دُونَ خَمْسِ أَوَاقٍ صَدَقَةٌ، وَلَيْسَ فِيمَا دُونَ خَمْسِ ذَوْدٍ صَدَقَةٌ، وَلَيْسَ فِيمَا دُونَ خَمْسَةِ أَوْسُقٍ صَدَقَةٌ»","source":"صحيح البخاري (1405) وصحيح مسلم (979)","benefit":"حماية صغار الكسبة والملاك: لا تجب الزكاة إلا بعد بلوغ النصاب المحكم (5 أواق فضة = 595 غراماً، 5 جمال، 5 أوسق حبوب وثمار = حوالي 653 كغ)."},{"num":41,"cat":"zakat","search":"41 تحديد النسبة المحاسبية الشرعية لزكاة الأموال والنقود (ربع العُشر 2.5%) عن أنس بن مالك رضي الله عنه أن أبا بكر الصديق رضي الله عنه كتب له كتاب فريضة الصدقة التي فرض رسول الله ﷺ وفيه: «وَفِي الرِّقَّةِ: رُبْعُ الْعُشْرِ، فَإِنْ لَمْ تَكُنْ إِلَّا تِسْعِينَ وَمِائَةً فَلَيْسَ فِيهَا شَيْءٌ إِلَّا أَنْ يَشَاءَ رَبُّهَا» صحيح البخاري (1454) المعيار المحاسبي والمالي الثابت في كافة البنوك والمؤسسات والشركات: نسبة زكاة النقد والأرصدة البنكية وعروض التجارة والأسهم الاستثمارية هي 2.5% سنوياً. وفي الرقة ربع العشر تسعين ومائة نسبة الزكاة 2.5% عروض التجارة الأسهم السيولة النقدية","title":"تحديد النسبة المحاسبية الشرعية لزكاة الأموال والنقود (ربع العُشر 2.5%)","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ صحيح البخاري","sanad":"عن أنس بن مالك رضي الله عنه أن أبا بكر الصديق رضي الله عنه كتب له كتاب فريضة الصدقة التي فرض رسول الله ﷺ وفيه:","matn":"«وَفِي الرِّقَّةِ: رُبْعُ الْعُشْرِ، فَإِنْ لَمْ تَكُنْ إِلَّا تِسْعِينَ وَمِائَةً فَلَيْسَ فِيهَا شَيْءٌ إِلَّا أَنْ يَشَاءَ رَبُّهَا»","source":"صحيح البخاري (1454)","benefit":"المعيار المحاسبي والمالي الثابت في كافة البنوك والمؤسسات والشركات: نسبة زكاة النقد والأرصدة البنكية وعروض التجارة والأسهم الاستثمارية هي 2.5% سنوياً."},{"num":42,"cat":"zakat","search":"42 تحديد نصاب الذهب بـ عشرين ديناراً (85 غراماً عيار 24) وشرط الحول عن علي بن أبي طالب رضي الله عنه عن النبي ﷺ قال: «إِذَا كَانَتْ لَكَ مِائَتَا دِرْهَمٍ وَحَالَ عَلَيْهَا الْحَوْلُ فَفِيهَا خَمْسَةُ دَرَاهِمَ، وَلَيْسَ عَلَيْكَ شَيْءٌ - يَعْنِي فِي الذَّهَبِ - حَتَّى يَكُونَ لَكَ عِشْرُونَ دِينَارًا، فَإِذَا كَانَتْ لَكَ عِشْرُونَ دِينَارًا وَحَالَ عَلَيْهَا الْحَوْلُ فَفِيهَا نِصْفُ دِينَارٍ» رواه أبو داود (1573) وصححه الألباني في صحيح أبي داود نصاب الذهب المعتمد عالمياً في الفقه الإسلامي هو 20 ديناراً ذهبياً يعادل 85 غراماً من الذهب الخالص، فإذا ملك المسلم قيمتها وحال عليها الحول وجب إخراج 2.5%. عشرون دينارا نصف دينار مائتا درهم نصاب الذهب 85 غرام عيار 24 حولان الحول","title":"تحديد نصاب الذهب بـ عشرين ديناراً (85 غراماً عيار 24) وشرط الحول","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ صحيح","sanad":"عن علي بن أبي طالب رضي الله عنه عن النبي ﷺ قال:","matn":"«إِذَا كَانَتْ لَكَ مِائَتَا دِرْهَمٍ وَحَالَ عَلَيْهَا الْحَوْلُ فَفِيهَا خَمْسَةُ دَرَاهِمَ، وَلَيْسَ عَلَيْكَ شَيْءٌ - يَعْنِي فِي الذَّهَبِ - حَتَّى يَكُونَ لَكَ عِشْرُونَ دِينَارًا، فَإِذَا كَانَتْ لَكَ عِشْرُونَ دِينَارًا وَحَالَ عَلَيْهَا الْحَوْلُ فَفِيهَا نِصْفُ دِينَارٍ»","source":"رواه أبو داود (1573) وصححه الألباني في صحيح أبي داود","benefit":"نصاب الذهب المعتمد عالمياً في الفقه الإسلامي هو 20 ديناراً ذهبياً يعادل 85 غراماً من الذهب الخالص، فإذا ملك المسلم قيمتها وحال عليها الحول وجب إخراج 2.5%."},{"num":43,"cat":"zakat","search":"43 تفاوت زكاة الزروع والمحاصيل بحسب كلفة وتعب الري والتشغيل عن عبد الله بن عمر رضي الله عنهما عن النبي ﷺ قال: «فِيمَا سَقَتِ السَّمَاءُ وَالْعُيُونُ أَوْ كَانَ عَثَرِيًّا: الْعُشْرُ، وَمَا سُقِيَ بِالنَّضْحِ: نِصْفُ الْعُشْرِ» صحيح البخاري (1483) العدالة التشريعية في التكاليف الاقتصادية: المحاصيل المروية بماء المطر دون كلفة تخرج 10%، والمروية بالآبار والماكينات والمضخات المكلفة تخرج 5% تقديراً لنفقات الإنتاج. فيما سقت السماء العشر وما سقي بالنضح نصف العشر زكاة الزروع الثمار كلفة الري","title":"تفاوت زكاة الزروع والمحاصيل بحسب كلفة وتعب الري والتشغيل","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ صحيح البخاري","sanad":"عن عبد الله بن عمر رضي الله عنهما عن النبي ﷺ قال:","matn":"«فِيمَا سَقَتِ السَّمَاءُ وَالْعُيُونُ أَوْ كَانَ عَثَرِيًّا: الْعُشْرُ، وَمَا سُقِيَ بِالنَّضْحِ: نِصْفُ الْعُشْرِ»","source":"صحيح البخاري (1483)","benefit":"العدالة التشريعية في التكاليف الاقتصادية: المحاصيل المروية بماء المطر دون كلفة تخرج 10%، والمروية بالآبار والماكينات والمضخات المكلفة تخرج 5% تقديراً لنفقات الإنتاج."},{"num":44,"cat":"zakat","search":"44 فرض زكاة الفطر صاعاً من طعام وتعميمها على جميع فئات الأسرة عن عبد الله بن عمر رضي الله عنهما قال: «فَرَضَ رَسُولُ اللَّهِ ﷺ زَكَاةَ الْفِطْرِ صَاعًا مِنْ تَمْرٍ، أَوْ صَاعًا مِنْ شَعِيرٍ، عَلَى الْعَبْدِ وَالْحُرِّ، وَالذَّكَرِ وَالْأُنْثَى، وَالصَّغِيرِ وَالْكَبِيرِ مِنَ الْمُسْلِمِينَ، وَأَمَرَ بِهَا أَنْ تُؤَدَّى قَبْلَ خُرُوجِ النَّاسِ إِلَى الصَّلَاةِ» صحيح البخاري (1503) وصحيح مسلم (984) وجوب إخراج زكاة الفطر عن كل فرد يعوله المسلم قبل صلاة عيد الفطر، ومقدارها صاع (نحو 2.5 - 3 كغ) من غالب قوت البلد، لإغناء المحتاجين عن السؤال في يوم العيد. فرض رسول الله زكاة الفطر صاعا من تمر صاعا من شعير العبد والحر صلاة العيد","title":"فرض زكاة الفطر صاعاً من طعام وتعميمها على جميع فئات الأسرة","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ متفق عليه","sanad":"عن عبد الله بن عمر رضي الله عنهما قال:","matn":"«فَرَضَ رَسُولُ اللَّهِ ﷺ زَكَاةَ الْفِطْرِ صَاعًا مِنْ تَمْرٍ، أَوْ صَاعًا مِنْ شَعِيرٍ، عَلَى الْعَبْدِ وَالْحُرِّ، وَالذَّكَرِ وَالْأُنْثَى، وَالصَّغِيرِ وَالْكَبِيرِ مِنَ الْمُسْلِمِينَ، وَأَمَرَ بِهَا أَنْ تُؤَدَّى قَبْلَ خُرُوجِ النَّاسِ إِلَى الصَّلَاةِ»","source":"صحيح البخاري (1503) وصحيح مسلم (984)","benefit":"وجوب إخراج زكاة الفطر عن كل فرد يعوله المسلم قبل صلاة عيد الفطر، ومقدارها صاع (نحو 2.5 - 3 كغ) من غالب قوت البلد، لإغناء المحتاجين عن السؤال في يوم العيد."},{"num":45,"cat":"zakat","search":"45 حكمة زكاة الفطر: طهرة للصائم من اللغو والرفث وإطعام للمساكين عن عبد الله بن عباس رضي الله عنهما قال: «فَرَضَ رَسُولُ اللَّهِ ﷺ زَكَاةَ الْفِطْرِ طُهْرَةً لِلصَّائِمِ مِنَ اللَّغْوِ وَالرَّفَثِ، وَطُعْمَةً لِلْمَسَاكِينِ، مَنْ أَدَّاهَا قَبْلَ الصَّلَاةِ فَهِيَ زَكَاةٌ مَقْبُولَةٌ، وَمَنْ أَدَّاهَا بَعْدَ الصَّلَاةِ فَهِيَ صَدَقَةٌ مِنَ الصَّدَقَاتِ» رواه أبو داود (1609) وابن ماجه (1827) والحاكم وصححه، وصححه الألباني في صحيح أبي داود بيان الأثر الروحي والاجتماعي المزدوج لزكاة الفطر: تطهير عمل الصائم وسد حاجة الجائع، وتأكيد وقتها الشرعي المستحب قبل صلاة العيد. زكاة الفطر طهرة للصائم طعمة للمساكين قبل الصلاة صدقة من الصدقات","title":"حكمة زكاة الفطر: طهرة للصائم من اللغو والرفث وإطعام للمساكين","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ صحيح","sanad":"عن عبد الله بن عباس رضي الله عنهما قال:","matn":"«فَرَضَ رَسُولُ اللَّهِ ﷺ زَكَاةَ الْفِطْرِ طُهْرَةً لِلصَّائِمِ مِنَ اللَّغْوِ وَالرَّفَثِ، وَطُعْمَةً لِلْمَسَاكِينِ، مَنْ أَدَّاهَا قَبْلَ الصَّلَاةِ فَهِيَ زَكَاةٌ مَقْبُولَةٌ، وَمَنْ أَدَّاهَا بَعْدَ الصَّلَاةِ فَهِيَ صَدَقَةٌ مِنَ الصَّدَقَاتِ»","source":"رواه أبو داود (1609) وابن ماجه (1827) والحاكم وصححه، وصححه الألباني في صحيح أبي داود","benefit":"بيان الأثر الروحي والاجتماعي المزدوج لزكاة الفطر: تطهير عمل الصائم وسد حاجة الجائع، وتأكيد وقتها الشرعي المستحب قبل صلاة العيد."},{"num":46,"cat":"zakat","search":"46 وعيد الله الشديد لمن كنز المال ومنع زكاته وتحوله لشجاع أقرع يعذبه عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ: «مَنْ آتَاهُ اللَّهُ مَالًا فَلَمْ يُؤَدِّ زَكَاتَهُ، مُثِّلَ لَهُ مَالُهُ يَوْمَ الْقِيَامَةِ شُجَاعًا أَقْرَعَ لَهُ زَبِيبَتَانِ يُطَوَّقُهُ يَوْمَ الْقِيَامَةِ، ثُمَّ يَأْخُذُ بِلِهْزِمَتَيْهِ - يَعْنِي بِشِدْقَيْهِ - ثُمَّ يَقُولُ: أَنَا مَالُكَ، أَنَا كَنْزُكَ» صحيح البخاري (1403) تحذير أصحاب الثروات والمستثمرين من التقاعس عن حساب وإخراج زكاة أموالهم، وأن المال غير المزكى ينقلب عذاباً وحسرة على صاحبه يوم الحساب. شجاعا أقرع له زبيبتان أنا مالك أنا كنزك عقوبة مانع الزكاة كنز الأموال","title":"وعيد الله الشديد لمن كنز المال ومنع زكاته وتحوله لشجاع أقرع يعذبه","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ صحيح البخاري","sanad":"عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ:","matn":"«مَنْ آتَاهُ اللَّهُ مَالًا فَلَمْ يُؤَدِّ زَكَاتَهُ، مُثِّلَ لَهُ مَالُهُ يَوْمَ الْقِيَامَةِ شُجَاعًا أَقْرَعَ لَهُ زَبِيبَتَانِ يُطَوَّقُهُ يَوْمَ الْقِيَامَةِ، ثُمَّ يَأْخُذُ بِلِهْزِمَتَيْهِ - يَعْنِي بِشِدْقَيْهِ - ثُمَّ يَقُولُ: أَنَا مَالُكَ، أَنَا كَنْزُكَ»","source":"صحيح البخاري (1403)","benefit":"تحذير أصحاب الثروات والمستثمرين من التقاعس عن حساب وإخراج زكاة أموالهم، وأن المال غير المزكى ينقلب عذاباً وحسرة على صاحبه يوم الحساب."},{"num":47,"cat":"zakat","search":"47 تحريم أخذ أموال الزكاة للأغنياء والمكتفين والأقوياء القادرين على الكسب عن عبد الله بن عمرو رضي الله عنهما عن النبي ﷺ قال: «لَا تَحِلُّ الصَّدَقَةُ لِغَنِيٍّ، وَلَا لِذِي مِرَّةٍ سَوِيٍّ» رواه أبو داود (1634) والترمذي (652) والنسائي (2597) وصححه الألباني حصر استحقاق الزكاة في الضعفاء والعاجزين والغارمين الحقيقيين، وتحريم تحايل المقتدرين أو الأصحاء القادرين على العمل والإنتاج في طلب أموال الزكاة. لا تحل الصدقة لغني ولا لذي مرة سوي استحقاق الزكاة القادر على الكسب الغنى","title":"تحريم أخذ أموال الزكاة للأغنياء والمكتفين والأقوياء القادرين على الكسب","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ صحيح","sanad":"عن عبد الله بن عمرو رضي الله عنهما عن النبي ﷺ قال:","matn":"«لَا تَحِلُّ الصَّدَقَةُ لِغَنِيٍّ، وَلَا لِذِي مِرَّةٍ سَوِيٍّ»","source":"رواه أبو داود (1634) والترمذي (652) والنسائي (2597) وصححه الألباني","benefit":"حصر استحقاق الزكاة في الضعفاء والعاجزين والغارمين الحقيقيين، وتحريم تحايل المقتدرين أو الأصحاء القادرين على العمل والإنتاج في طلب أموال الزكاة."},{"num":48,"cat":"zakat","search":"48 اشتراط تمام الحول القمري (عام كامل) لوجوب زكاة الأموال والتجارة عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ: «لَا زَكَاةَ فِي مَالٍ حَتَّى يَحُولَ عَلَيْهِ الْحَوْلُ» رواه الترمذي (631) وأبو داود (1573) وابن ماجه (1792) وصححه الألباني بشواهده في الإرواء (787) الشرط المحاسبي الحاسم لزكاة الثروة النقدية والتجارية: مرور 354 يوماً (سنة هجرية قمرية) على بقاء المال بالغاً للنصاب، تيسيراً على الملاك والمستثمرين. لا زكاة في مال حتى يحول عليه الحول تمام الحول السنة القمرية الميزانية الزكوية","title":"اشتراط تمام الحول القمري (عام كامل) لوجوب زكاة الأموال والتجارة","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ صحيح","sanad":"عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ:","matn":"«لَا زَكَاةَ فِي مَالٍ حَتَّى يَحُولَ عَلَيْهِ الْحَوْلُ»","source":"رواه الترمذي (631) وأبو داود (1573) وابن ماجه (1792) وصححه الألباني بشواهده في الإرواء (787)","benefit":"الشرط المحاسبي الحاسم لزكاة الثروة النقدية والتجارية: مرور 354 يوماً (سنة هجرية قمرية) على بقاء المال بالغاً للنصاب، تيسيراً على الملاك والمستثمرين."},{"num":49,"cat":"zakat","search":"49 مشروعية تعجيل إخراج الزكاة مقدماً قبل حلول الحول للمصلحة والأزمات عن علي بن أبي طالب رضي الله عنه: «أَنَّ الْعَبَّاسَ رَضِيَ اللَّهُ عَنْهُ سَأَلَ رَسُولَ اللَّهِ ﷺ فِي تَعْجِيلِ صَدَقَتِهِ قَبْلَ أَنْ تَحِلَّ، فَرَخَّصَ لَهُ فِي ذَلِكَ» رواه أبو داود (1624) والترمذي (678) وحسنه، وابن ماجه (1795) وصححه الحاكم والنووي والألباني جواز تقديم دفع الزكاة لعام أو عامين مقدماً في حالات الكوارث أو الحروب أو إغاثة المنكوبين ومشاريع الإغاثة العاجلة، كمرونة تمويلية إسلامية رائدة. تعجيل الصدقة قبل أن تحل العباس رضي الله عنه إخراج الزكاة مقدما الأزمات والكوارث","title":"مشروعية تعجيل إخراج الزكاة مقدماً قبل حلول الحول للمصلحة والأزمات","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ صحيح","sanad":"عن علي بن أبي طالب رضي الله عنه:","matn":"«أَنَّ الْعَبَّاسَ رَضِيَ اللَّهُ عَنْهُ سَأَلَ رَسُولَ اللَّهِ ﷺ فِي تَعْجِيلِ صَدَقَتِهِ قَبْلَ أَنْ تَحِلَّ، فَرَخَّصَ لَهُ فِي ذَلِكَ»","source":"رواه أبو داود (1624) والترمذي (678) وحسنه، وابن ماجه (1795) وصححه الحاكم والنووي والألباني","benefit":"جواز تقديم دفع الزكاة لعام أو عامين مقدماً في حالات الكوارث أو الحروب أو إغاثة المنكوبين ومشاريع الإغاثة العاجلة، كمرونة تمويلية إسلامية رائدة."},{"num":50,"cat":"zakat","search":"50 النهي عن أخذ نفائس وكرائم أموال الناس في جباية الزكاة والعدل مع المالك عن عبد الله بن عباس رضي الله عنهما في وصية النبي ﷺ لمعاذ لما بعثه لليمن: «فَإِيَّاكَ وَكَرَائِمَ أَمْوَالِهِمْ، وَاتَّقِ دَعْوَةَ الْمَظْلُومِ؛ فَإِنَّهُ لَيْسَ بَيْنَهَا وَبَيْنَ اللَّهِ حِجَابٌ» صحيح البخاري (1496) وصحيح مسلم (19) حظر التعسف في التقدير الزكوي أو الضريبي وأخذ خيار مال المكلف (كرائم أمواله)، بل يؤخذ الوسط العدل حماية لرأس مال التاجر والمستثمر من الجور. إياك وكرائم أموالهم دعوة المظلوم ليس بينها وبين الله حجاب جباية الزكاة العدالة المالية","title":"النهي عن أخذ نفائس وكرائم أموال الناس في جباية الزكاة والعدل مع المالك","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ متفق عليه","sanad":"عن عبد الله بن عباس رضي الله عنهما في وصية النبي ﷺ لمعاذ لما بعثه لليمن:","matn":"«فَإِيَّاكَ وَكَرَائِمَ أَمْوَالِهِمْ، وَاتَّقِ دَعْوَةَ الْمَظْلُومِ؛ فَإِنَّهُ لَيْسَ بَيْنَهَا وَبَيْنَ اللَّهِ حِجَابٌ»","source":"صحيح البخاري (1496) وصحيح مسلم (19)","benefit":"حظر التعسف في التقدير الزكوي أو الضريبي وأخذ خيار مال المكلف (كرائم أمواله)، بل يؤخذ الوسط العدل حماية لرأس مال التاجر والمستثمر من الجور."},{"num":51,"cat":"zakat","search":"51 تحريم الحيل المحاسبية والتهرب الزكوي بتفريق المجتمع أو جمع المفترق عن أنس بن مالك رضي الله عنه في كتاب الصدقة الذي كتبه أبو بكر عن رسول الله ﷺ: «وَلَا يُجْمَعُ بَيْنَ مُتَفَرِّقٍ، وَلَا يُفَرَّقُ بَيْنَ مُجْتَمِعٍ، خَشْيَةَ الصَّدَقَةِ» صحيح البخاري (1450) التأصيل النبوي الصارم لمكافحة التهرب من الزكاة: تحريم تفريق الحسابات البنكية أو تشتيت رؤوس الأموال بين شركات وهمية صورية بقصد خفض الوعاء الزكوي. لا يجمع بين متفرق ولا يفرق بين مجتمع خشية الصدقة التهرب الزكوي الحيل المحاسبية","title":"تحريم الحيل المحاسبية والتهرب الزكوي بتفريق المجتمع أو جمع المفترق","catBadge":"🌾 فريضة الزكاة وأنصبتها","gradeBadge":"✓ صحيح البخاري","sanad":"عن أنس بن مالك رضي الله عنه في كتاب الصدقة الذي كتبه أبو بكر عن رسول الله ﷺ:","matn":"«وَلَا يُجْمَعُ بَيْنَ مُتَفَرِّقٍ، وَلَا يُفَرَّقُ بَيْنَ مُجْتَمِعٍ، خَشْيَةَ الصَّدَقَةِ»","source":"صحيح البخاري (1450)","benefit":"التأصيل النبوي الصارم لمكافحة التهرب من الزكاة: تحريم تفريق الحسابات البنكية أو تشتيت رؤوس الأموال بين شركات وهمية صورية بقصد خفض الوعاء الزكوي."},{"num":52,"cat":"charity","search":"52 قانون البركة الرباني: نفي النقصان عن المال المتصدق به وزيادته حقيقة عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال: «مَا نَقَصَتْ صَدَقَةٌ مِنْ مَالٍ، وَمَا زَادَ اللَّهُ عَبْدًا بِعَفْوٍ إِلَّا عِزًّا، وَمَا تَوَاضَعَ أَحَدٌ لِلَّهِ إِلَّا رَفَعَهُ اللَّهُ» صحيح مسلم (2588) اليقين الاقتصادي الإسلامي بأن البذل والإنفاق لا ينقص رأس المال في المآل بل ينميه بالبركة ويدفع عنه الآفات والمصائب المهلكة. ما نقصت صدقة من مال بركة الصدقة نماء المال الإنفاق التواضع العفو","title":"قانون البركة الرباني: نفي النقصان عن المال المتصدق به وزيادته حقيقة","catBadge":"💎 صدقات وإنفاق ونماء","gradeBadge":"✓ صحيح مسلم","sanad":"عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال:","matn":"«مَا نَقَصَتْ صَدَقَةٌ مِنْ مَالٍ، وَمَا زَادَ اللَّهُ عَبْدًا بِعَفْوٍ إِلَّا عِزًّا، وَمَا تَوَاضَعَ أَحَدٌ لِلَّهِ إِلَّا رَفَعَهُ اللَّهُ»","source":"صحيح مسلم (2588)","benefit":"اليقين الاقتصادي الإسلامي بأن البذل والإنفاق لا ينقص رأس المال في المآل بل ينميه بالبركة ويدفع عنه الآفات والمصائب المهلكة."},{"num":53,"cat":"charity","search":"53 دعاء الملكين اليومي: الاستخلاف بالخلف للمنفق والهلاك والتلف للبخيل الممسك عن أبي هريرة رضي الله عنه أن النبي ﷺ قال: «مَا مِنْ يَوْمٍ يُصْبِحُ الْعِبَادُ فِيهِ، إِلَّا مَلَكَانِ يَنْزِلَانِ، فَيَقُولُ أَحَدُهُمَا: اللَّهُمَّ أَعْطِ مُنْفِقًا خَلَفًا، وَيَقُولُ الْآخَرُ: اللَّهُمَّ أَعْطِ مُمْسِكًا تَلَفًا» صحيح البخاري (1442) وصحيح مسلم (1010) قانون التدفق المالي الكوني: كثرة الإنفاق في وجوه الخير والمسؤولية المجتمعية تستجلب الخلف العاجل وتجدد رأس المال، والبخل والشح سبب لتلف الثروات. اللهم أعط منفقا خلفا أعط ممسكا تلفا دعاء الملكين البخل والشح المسؤولية الاجتماعية","title":"دعاء الملكين اليومي: الاستخلاف بالخلف للمنفق والهلاك والتلف للبخيل الممسك","catBadge":"💎 صدقات وإنفاق ونماء","gradeBadge":"✓ متفق عليه","sanad":"عن أبي هريرة رضي الله عنه أن النبي ﷺ قال:","matn":"«مَا مِنْ يَوْمٍ يُصْبِحُ الْعِبَادُ فِيهِ، إِلَّا مَلَكَانِ يَنْزِلَانِ، فَيَقُولُ أَحَدُهُمَا: اللَّهُمَّ أَعْطِ مُنْفِقًا خَلَفًا، وَيَقُولُ الْآخَرُ: اللَّهُمَّ أَعْطِ مُمْسِكًا تَلَفًا»","source":"صحيح البخاري (1442) وصحيح مسلم (1010)","benefit":"قانون التدفق المالي الكوني: كثرة الإنفاق في وجوه الخير والمسؤولية المجتمعية تستجلب الخلف العاجل وتجدد رأس المال، والبخل والشح سبب لتلف الثروات."},{"num":54,"cat":"charity","search":"54 فضل صدقة السر وإخفاء البذل المالي كأحد أسباب الظل في عرصات القيامة عن أبي هريرة رضي الله عنه في حديث السبعة الذين يظلهم الله في ظله قال ﷺ: «وَرَجُلٌ تَصَدَّقَ بِصَدَقَةٍ فَأَخْفَاهَا حَتَّى لَا تَعْلَمَ شِمَالُهُ مَا تُنْفِقُ يَمِينُهُ» صحيح البخاري (1423) وصحيح مسلم (1031) عظمة الإخلاص وتجريد النية في التبرعات المالية والأوقاف، والبعد عن الرياء والسمعة أو المن والأذى الذي يحبط أجر الصدقات ويثلم مروءة الفقراء. تصدق بصدقة فأخفاها لا تعلم شماله ما تنفق يمينه صدقة السر السبعة الذين يظلهم الله الإخلاص","title":"فضل صدقة السر وإخفاء البذل المالي كأحد أسباب الظل في عرصات القيامة","catBadge":"💎 صدقات وإنفاق ونماء","gradeBadge":"✓ متفق عليه","sanad":"عن أبي هريرة رضي الله عنه في حديث السبعة الذين يظلهم الله في ظله قال ﷺ:","matn":"«وَرَجُلٌ تَصَدَّقَ بِصَدَقَةٍ فَأَخْفَاهَا حَتَّى لَا تَعْلَمَ شِمَالُهُ مَا تُنْفِقُ يَمِينُهُ»","source":"صحيح البخاري (1423) وصحيح مسلم (1031)","benefit":"عظمة الإخلاص وتجريد النية في التبرعات المالية والأوقاف، والبعد عن الرياء والسمعة أو المن والأذى الذي يحبط أجر الصدقات ويثلم مروءة الفقراء."},{"num":55,"cat":"charity","search":"55 تزكية الثروة والغنى الحلال: نعم المال الصالح للرجل الصالح عن عمرو بن العاص رضي الله عنه قال: قال لي رسول الله ﷺ: «يَا عَمْرُو، نِعْمَ الْمَالُ الصَّالِحُ لِلْمَرْءِ الصَّالِحِ» رواه أحمد (17763) والبخاري في الأدب المفرد (299) وصححه ابن حبان (3210) والألباني تقرير شرعي حاسم بأن الإسلام لا يذم امتلاك الثروات ورؤوس الأموال الكبرى إذا اكتسبت من حلال وأُنفقت في حلال، بل هي قوة للمسلم وللأمة. نعم المال الصالح للمرء الصالح عمرو بن العاص الغنى الحلال كسب المال الثروة","title":"تزكية الثروة والغنى الحلال: نعم المال الصالح للرجل الصالح","catBadge":"💎 صدقات وإنفاق ونماء","gradeBadge":"✓ صحيح","sanad":"عن عمرو بن العاص رضي الله عنه قال: قال لي رسول الله ﷺ:","matn":"«يَا عَمْرُو، نِعْمَ الْمَالُ الصَّالِحُ لِلْمَرْءِ الصَّالِحِ»","source":"رواه أحمد (17763) والبخاري في الأدب المفرد (299) وصححه ابن حبان (3210) والألباني","benefit":"تقرير شرعي حاسم بأن الإسلام لا يذم امتلاك الثروات ورؤوس الأموال الكبرى إذا اكتسبت من حلال وأُنفقت في حلال، بل هي قوة للمسلم وللأمة."},{"num":56,"cat":"charity","search":"56 مشروعية الوقف الإسلامي والصدقة الجارية التي لا ينقطع أثرها بعد الموت عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال: «إِذَا مَاتَ الْإِنْسَانُ انْقَطَعَ عَنْهُ عَمَلُهُ إِلَّا مِنْ ثَلَاثَةٍ: إِلَّا مِنْ صَدَقَةٍ جَارِيَةٍ، أَوْ عِلْمٍ يُنْتَفَعُ بِهِ، أَوْ وَلَدٍ صَالِحٍ يَدْعُو لَهُ» صحيح مسلم (1631) التأسيس النبوي الأعظم لمنظومة الأوقاف الخيرية والاستثمارية المستدامة (المستشفيات، المدارس، آبار المياه، العقارات الوقفية) كأعظم ادخار مالي للمؤمن بعد وفاته. إذا مات الإنسان انقطع عمله إلا من ثلاثة صدقة جارية علم ينتفع به الوقف الخيري الاستدامة","title":"مشروعية الوقف الإسلامي والصدقة الجارية التي لا ينقطع أثرها بعد الموت","catBadge":"💎 صدقات وإنفاق ونماء","gradeBadge":"✓ صحيح مسلم","sanad":"عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال:","matn":"«إِذَا مَاتَ الْإِنْسَانُ انْقَطَعَ عَنْهُ عَمَلُهُ إِلَّا مِنْ ثَلَاثَةٍ: إِلَّا مِنْ صَدَقَةٍ جَارِيَةٍ، أَوْ عِلْمٍ يُنْتَفَعُ بِهِ، أَوْ وَلَدٍ صَالِحٍ يَدْعُو لَهُ»","source":"صحيح مسلم (1631)","benefit":"التأسيس النبوي الأعظم لمنظومة الأوقاف الخيرية والاستثمارية المستدامة (المستشفيات، المدارس، آبار المياه، العقارات الوقفية) كأعظم ادخار مالي للمؤمن بعد وفاته."},{"num":57,"cat":"charity","search":"57 فقه أولويات الموازنة المالية: أعظم الصدقات أجراً النفقة على الأهل والعيال عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ: «دِينَارٌ أَنْفَقْتَهُ فِي سَبِيلِ اللَّهِ، وَدِينَارٌ أَنْفَقْتَهُ فِي رَقَبَةٍ، وَدِينَارٌ تَصَدَّقْتَ بِهِ عَلَى مِسْكِينٍ، وَدِينَارٌ أَنْفَقْتَهُ عَلَى أَهْلِكَ، أَعْظَمُهَا أَجْرًا الَّذِي أَنْفَقْتَهُ عَلَى أَهْلِكَ» صحيح مسلم (995) ترتيب الأولويات في التخطيط المالي للأسرة: كفاية الأهل والأبناء والوفاء بحاجاتهم المعيشية الكريمة فريضة مقدمة في الأجر على صدقات التطوع الخارجية. دينار أنفقته في سبيل الله دينار أنفقته على أهلك أعظمها أجرا نفقة الأهل التخطيط المالي للأسرة","title":"فقه أولويات الموازنة المالية: أعظم الصدقات أجراً النفقة على الأهل والعيال","catBadge":"💎 صدقات وإنفاق ونماء","gradeBadge":"✓ صحيح مسلم","sanad":"عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ:","matn":"«دِينَارٌ أَنْفَقْتَهُ فِي سَبِيلِ اللَّهِ، وَدِينَارٌ أَنْفَقْتَهُ فِي رَقَبَةٍ، وَدِينَارٌ تَصَدَّقْتَ بِهِ عَلَى مِسْكِينٍ، وَدِينَارٌ أَنْفَقْتَهُ عَلَى أَهْلِكَ، أَعْظَمُهَا أَجْرًا الَّذِي أَنْفَقْتَهُ عَلَى أَهْلِكَ»","source":"صحيح مسلم (995)","benefit":"ترتيب الأولويات في التخطيط المالي للأسرة: كفاية الأهل والأبناء والوفاء بحاجاتهم المعيشية الكريمة فريضة مقدمة في الأجر على صدقات التطوع الخارجية."},{"num":58,"cat":"charity","search":"58 عزة اليد العليا المنفقة وفضل التعفف والكسب على التكفف والمسألة عن حكيم بن حزام رضي الله عنه عن النبي ﷺ قال: «الْيَدُ الْعُلْيَا خَيْرٌ مِنَ الْيَدِ السُّفْلَى، وَابْدَأْ بِمَنْ تَعُولُ، وَخَيْرُ الصَّدَقَةِ عَنْ ظَهْرِ غِنًى، وَمَنْ يَسْتَعْفِفْ يُعِفَّهُ اللَّهُ، وَمَنْ يَسْتَغْنِ يُغْنِهِ اللَّهُ» صحيح البخاري (1427) وصحيح مسلم (1053) الحث على الإنتاجية والعمل والاستقلال المالي لتكون يد المسلم معطية مانحة لا آخذة سائلة، واشتراط أن تكون الصدقة بعد استيفاء الكفاية الذاتية (عن ظهر غنى). اليد العليا خير من اليد السفلى ابدأ بمن تعول خير الصدقة عن ظهر غنى يستعفف يعفه الله","title":"عزة اليد العليا المنفقة وفضل التعفف والكسب على التكفف والمسألة","catBadge":"💎 صدقات وإنفاق ونماء","gradeBadge":"✓ متفق عليه","sanad":"عن حكيم بن حزام رضي الله عنه عن النبي ﷺ قال:","matn":"«الْيَدُ الْعُلْيَا خَيْرٌ مِنَ الْيَدِ السُّفْلَى، وَابْدَأْ بِمَنْ تَعُولُ، وَخَيْرُ الصَّدَقَةِ عَنْ ظَهْرِ غِنًى، وَمَنْ يَسْتَعْفِفْ يُعِفَّهُ اللَّهُ، وَمَنْ يَسْتَغْنِ يُغْنِهِ اللَّهُ»","source":"صحيح البخاري (1427) وصحيح مسلم (1053)","benefit":"الحث على الإنتاجية والعمل والاستقلال المالي لتكون يد المسلم معطية مانحة لا آخذة سائلة، واشتراط أن تكون الصدقة بعد استيفاء الكفاية الذاتية (عن ظهر غنى)."},{"num":59,"cat":"charity","search":"59 عدم احتقار أي قدر من البذل المالي: الوقاية من النار ولو بنصف تمرة عن عدي بن حاتم رضي الله عنه قال: سمعت النبي ﷺ يقول: «اتَّقُوا النَّارَ وَلَوْ بِشِقِّ تَمْرَةٍ، فَمَنْ لَمْ يَجِدْ فَبِكَلِمَةٍ طَيِّبَةٍ» صحيح البخاري (1417) وصحيح مسلم (1016) تشجيع ثقافة العطاء المالي الميسور للجميع دون استصغار، فكل مساهمة مالية مهما قلت تدفع الشر وتنجي صاحبها من المهالك يوم القيامة. اتقوا النار ولو بشق تمرة كلمة طيبة الصدقة القليلة ثقافة العطاء","title":"عدم احتقار أي قدر من البذل المالي: الوقاية من النار ولو بنصف تمرة","catBadge":"💎 صدقات وإنفاق ونماء","gradeBadge":"✓ متفق عليه","sanad":"عن عدي بن حاتم رضي الله عنه قال: سمعت النبي ﷺ يقول:","matn":"«اتَّقُوا النَّارَ وَلَوْ بِشِقِّ تَمْرَةٍ، فَمَنْ لَمْ يَجِدْ فَبِكَلِمَةٍ طَيِّبَةٍ»","source":"صحيح البخاري (1417) وصحيح مسلم (1016)","benefit":"تشجيع ثقافة العطاء المالي الميسور للجميع دون استصغار، فكل مساهمة مالية مهما قلت تدفع الشر وتنجي صاحبها من المهالك يوم القيامة."},{"num":60,"cat":"charity","search":"60 الصدقة تطفئ غضب الرب وتدفع ميتة السوء ومصائب المال عن أنس بن مالك رضي الله عنه قال: قال رسول الله ﷺ: «إِنَّ الصَّدَقَةَ لَتُطْفِئُ غَضَبَ الرَّبِّ، وَتَدْفَعُ مِيتَةَ السُّوءِ» رواه الترمذي (664) وحسنه، وصححه الألباني في صحيح الترغيب (864) الصدقة حصن حصين للأموال والأنفس، وهي صمام أمان لدفع تقلبات الزمان والمصائب الفجائية، وبوابة للمغفرة ورضا الله تعالى. إن الصدقة لتطفئ غضب الرب تدفع ميتة السوء حفظ الأموال دفع البلاء","title":"الصدقة تطفئ غضب الرب وتدفع ميتة السوء ومصائب المال","catBadge":"💎 صدقات وإنفاق ونماء","gradeBadge":"✓ صحيح","sanad":"عن أنس بن مالك رضي الله عنه قال: قال رسول الله ﷺ:","matn":"«إِنَّ الصَّدَقَةَ لَتُطْفِئُ غَضَبَ الرَّبِّ، وَتَدْفَعُ مِيتَةَ السُّوءِ»","source":"رواه الترمذي (664) وحسنه، وصححه الألباني في صحيح الترغيب (864)","benefit":"الصدقة حصن حصين للأموال والأنفس، وهي صمام أمان لدفع تقلبات الزمان والمصائب الفجائية، وبوابة للمغفرة ورضا الله تعالى."},{"num":61,"cat":"charity","search":"61 الأثر النفسي للبذل والبخل: انشراح صدر المنفق وضيق صدر البخيل عن أبي هريرة رضي الله عنه أنه سمع رسول الله ﷺ يقول: «مَثَلُ الْبَخِيلِ وَالْمُنْفِقِ، كَمَثَلِ رَجُلَيْنِ عَلَيْهِمَا جُبَّتَانِ مِنْ حَدِيدٍ مِنْ ثُدِيِّهِمَا إِلَى تَرَاقِيهِمَا، فَأَمَّا الْمُنْفِقُ فَلَا يُنْفِقُ إِلَّا سَبَغَتْ أَوْ وَفَرَتْ عَلَى جِلْدِهِ... وَأَمَّا الْبَخِيلُ فَلَا يُرِيدُ أَنْ يُنْفِقَ شَيْئًا إِلَّا لَزِقَتْ كُلُّ حَلْقَةٍ مَكَانَهَا» صحيح البخاري (1443) وصحيح مسلم (1021) تصوير نبوي عبقري لراحة النفس وسعة الصدر والبركة التي يجدها منفق المال، بينما يعيش الشحيح البخيل في ضيق وهم وخوف دائم من الفقر. مثل البخيل والمنفق جبتان من حديد انشراح الصدر الشح البخل علم النفس المالي","title":"الأثر النفسي للبذل والبخل: انشراح صدر المنفق وضيق صدر البخيل","catBadge":"💎 صدقات وإنفاق ونماء","gradeBadge":"✓ متفق عليه","sanad":"عن أبي هريرة رضي الله عنه أنه سمع رسول الله ﷺ يقول:","matn":"«مَثَلُ الْبَخِيلِ وَالْمُنْفِقِ، كَمَثَلِ رَجُلَيْنِ عَلَيْهِمَا جُبَّتَانِ مِنْ حَدِيدٍ مِنْ ثُدِيِّهِمَا إِلَى تَرَاقِيهِمَا، فَأَمَّا الْمُنْفِقُ فَلَا يُنْفِقُ إِلَّا سَبَغَتْ أَوْ وَفَرَتْ عَلَى جِلْدِهِ... وَأَمَّا الْبَخِيلُ فَلَا يُرِيدُ أَنْ يُنْفِقَ شَيْئًا إِلَّا لَزِقَتْ كُلُّ حَلْقَةٍ مَكَانَهَا»","source":"صحيح البخاري (1443) وصحيح مسلم (1021)","benefit":"تصوير نبوي عبقري لراحة النفس وسعة الصدر والبركة التي يجدها منفق المال، بينما يعيش الشحيح البخيل في ضيق وهم وخوف دائم من الفقر."},{"num":62,"cat":"inheritance","search":"62 القانون النبوي الأساسي لقسمة التركات: ألحقوا الفرائض بأهلها عن عبد الله بن عباس رضي الله عنهما عن النبي ﷺ قال: «أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لِأَوْلَى رَجُلٍ ذَكَرٍ» صحيح البخاري (6732) وصحيح مسلم (1615) القاعدة الأم التي يبنى عليها علم المواريث والتركات بأكمله: البدء بأصحاب الفروض المقدرة في كتاب الله أولاً، ثم دفع الفائض من التركة إلى أقرب عصبة من الرجال الذكور. ألحقوا الفرائض بأهلها فما بقي فلأولى رجل ذكر قسمة التركات المواريث أصحاب الفروض العصبات","title":"القانون النبوي الأساسي لقسمة التركات: ألحقوا الفرائض بأهلها","catBadge":"📜 مواريث وفرائض ووصايا","gradeBadge":"✓ متفق عليه","sanad":"عن عبد الله بن عباس رضي الله عنهما عن النبي ﷺ قال:","matn":"«أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لِأَوْلَى رَجُلٍ ذَكَرٍ»","source":"صحيح البخاري (6732) وصحيح مسلم (1615)","benefit":"القاعدة الأم التي يبنى عليها علم المواريث والتركات بأكمله: البدء بأصحاب الفروض المقدرة في كتاب الله أولاً، ثم دفع الفائض من التركة إلى أقرب عصبة من الرجال الذكور."},{"num":63,"cat":"inheritance","search":"63 تحديد سقف الوصية بالثلث كحد أقصى وأولوية إغناء الورثة وكفايتهم عن سعد بن أبي وقاص رضي الله عنه قال: قلت: يا رسول الله، أوصي بمالي كله؟ قال: لا، قلت: فالشطر؟ قال: لا، قلت: فالثلث؟ قال: «فَالثُّلُثُ، وَالثُّلُثُ كَثِيرٌ، إِنَّكَ أَنْ تَذَرَ وَرَثَتَكَ أَغْنِيَاءَ خَيْرٌ مِنْ أَنْ تَذَرَهُمْ عَالَةً يَتَكَفَّفُونَ النَّاسَ» صحيح البخاري (2742) وصحيح مسلم (1628) ضابط جوهري في إدارة الثروات العائلية: منع التبرع بالوصايا لما زاد عن ثلث التركة بعد سداد الديون، وتفضيل توريث المال للأبناء والأهل على التبرع الخارجي لحمايتهم من الفقر. الثلث والثلث كثير أن تذر ورثتك أغنياء خير من أن تذرهم عالة سعد بن أبي وقاص سقف الوصية","title":"تحديد سقف الوصية بالثلث كحد أقصى وأولوية إغناء الورثة وكفايتهم","catBadge":"📜 مواريث وفرائض ووصايا","gradeBadge":"✓ متفق عليه","sanad":"عن سعد بن أبي وقاص رضي الله عنه قال: قلت: يا رسول الله، أوصي بمالي كله؟ قال: لا، قلت: فالشطر؟ قال: لا، قلت: فالثلث؟ قال:","matn":"«فَالثُّلُثُ، وَالثُّلُثُ كَثِيرٌ، إِنَّكَ أَنْ تَذَرَ وَرَثَتَكَ أَغْنِيَاءَ خَيْرٌ مِنْ أَنْ تَذَرَهُمْ عَالَةً يَتَكَفَّفُونَ النَّاسَ»","source":"صحيح البخاري (2742) وصحيح مسلم (1628)","benefit":"ضابط جوهري في إدارة الثروات العائلية: منع التبرع بالوصايا لما زاد عن ثلث التركة بعد سداد الديون، وتفضيل توريث المال للأبناء والأهل على التبرع الخارجي لحمايتهم من الفقر."},{"num":64,"cat":"inheritance","search":"64 تحريم الوصية لأي وارث منعاً للظلم وتغيير أنصبة الميراث الإلهية عن أبي أمامة الباهلي رضي الله عنه قال: سمعت رسول الله ﷺ يقول في خطبته عام حجة الوداع: «إِنَّ اللَّهَ قَدْ أَعْطَى كُلَّ ذِي حَقٍّ حَقَّهُ، فَلَا وَصِيَّةَ لِوَارِثٍ» رواه أبو داود (2870) والترمذي (2120) وابن ماجه (2713) وصححه الألباني في الإرواء (1655) إبطال أي وصية مخصصة لأحد الورثة الشرعيين تميزه عن بقية إخوته أو شركائه في الإرث، حماية للروابط الأسرية من النزاع والحسد والجور. إن الله قد أعطى كل ذي حق حقه فلا وصية لوارث حجة الوداع بطلان الوصية للوارث","title":"تحريم الوصية لأي وارث منعاً للظلم وتغيير أنصبة الميراث الإلهية","catBadge":"📜 مواريث وفرائض ووصايا","gradeBadge":"✓ صحيح","sanad":"عن أبي أمامة الباهلي رضي الله عنه قال: سمعت رسول الله ﷺ يقول في خطبته عام حجة الوداع:","matn":"«إِنَّ اللَّهَ قَدْ أَعْطَى كُلَّ ذِي حَقٍّ حَقَّهُ، فَلَا وَصِيَّةَ لِوَارِثٍ»","source":"رواه أبو داود (2870) والترمذي (2120) وابن ماجه (2713) وصححه الألباني في الإرواء (1655)","benefit":"إبطال أي وصية مخصصة لأحد الورثة الشرعيين تميزه عن بقية إخوته أو شركائه في الإرث، حماية للروابط الأسرية من النزاع والحسد والجور."},{"num":65,"cat":"inheritance","search":"65 وجوب توثيق الوصايا والحقوق والديون كتابةً دون تأخير عن عبد الله بن عمر رضي الله عنهما أن رسول الله ﷺ قال: «مَا حَقُّ امْرِئٍ مُسْلِمٍ لَهُ شَيْءٌ يُوصِي فِيهِ، يَبِيتُ لَيْلَتَيْنِ إِلَّا وَوَصِيَّتُهُ مَكْتُوبَةٌ عِنْدَهُ» صحيح البخاري (2738) وصحيح مسلم (1627) أمر نبوي حاسم بتدوين المستندات المالية والوصايا والديون والأمانات والحسابات المعلقة خطياً ومحاسبياً، تحسباً للموت المفاجئ وضماناً لحقوق الدائنين والورثة. ما حق امرئ مسلم له شيء يوصي فيه يبيت ليلتين إلا ووصيته مكتوبة عنده توثيق الوصية الحقوق","title":"وجوب توثيق الوصايا والحقوق والديون كتابةً دون تأخير","catBadge":"📜 مواريث وفرائض ووصايا","gradeBadge":"✓ متفق عليه","sanad":"عن عبد الله بن عمر رضي الله عنهما أن رسول الله ﷺ قال:","matn":"«مَا حَقُّ امْرِئٍ مُسْلِمٍ لَهُ شَيْءٌ يُوصِي فِيهِ، يَبِيتُ لَيْلَتَيْنِ إِلَّا وَوَصِيَّتُهُ مَكْتُوبَةٌ عِنْدَهُ»","source":"صحيح البخاري (2738) وصحيح مسلم (1627)","benefit":"أمر نبوي حاسم بتدوين المستندات المالية والوصايا والديون والأمانات والحسابات المعلقة خطياً ومحاسبياً، تحسباً للموت المفاجئ وضماناً لحقوق الدائنين والورثة."},{"num":66,"cat":"inheritance","search":"66 موانع الميراث الشرعية: اختلاف الدين يمنع التوارث التلقائي في التركات عن أسامة بن زيد رضي الله عنهما أن النبي ﷺ قال: «لَا يَرِثُ الْمُسْلِمُ الْكَافِرَ، وَلَا الْكَافِرُ الْمُسْلِمَ» صحيح البخاري (6764) وصحيح مسلم (1614) المانع الأول من موانع الإرث في الفقه الإسلامي: اختلاف الملة يمنع استحقاق الفريضة المقدرة في التركة، ويبقى التبرع عبر الوصية في حدود الثلث جائزاً لغير الوارث. لا يرث المسلم الكافر ولا الكافر المسلم موانع الميراث اختلاف الدين التركات","title":"موانع الميراث الشرعية: اختلاف الدين يمنع التوارث التلقائي في التركات","catBadge":"📜 مواريث وفرائض ووصايا","gradeBadge":"✓ متفق عليه","sanad":"عن أسامة بن زيد رضي الله عنهما أن النبي ﷺ قال:","matn":"«لَا يَرِثُ الْمُسْلِمُ الْكَافِرَ، وَلَا الْكَافِرُ الْمُسْلِمَ»","source":"صحيح البخاري (6764) وصحيح مسلم (1614)","benefit":"المانع الأول من موانع الإرث في الفقه الإسلامي: اختلاف الملة يمنع استحقاق الفريضة المقدرة في التركة، ويبقى التبرع عبر الوصية في حدود الثلث جائزاً لغير الوارث."},{"num":67,"cat":"inheritance","search":"67 موانع الميراث: حرمان القاتل من تركة مورّثه منعاً للاستعجال غير المشروع عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ: «الْقَاتِلُ لَا يَرِثُ» وفي رواية: «لَيْسَ لِلْقَاتِلِ مِنَ الْمِيرَاثِ شَيْءٌ» رواه الترمذي (2109) وابن ماجه (2645) وصححه الألباني في الإرواء (1672) القاعدة الفقهية الجنائية والمالية الكبرى: (من استعجل الشيء قبل أوانه عوقب بحرمانه)، فإذا قتل الوارث مورثه سقط حقه في الميراث والدية كلياً. القاتل لا يرث ليس للقاتل من الميراث شيء موانع الإرث من استعجل الشيء قبل أوانه","title":"موانع الميراث: حرمان القاتل من تركة مورّثه منعاً للاستعجال غير المشروع","catBadge":"📜 مواريث وفرائض ووصايا","gradeBadge":"✓ صحيح","sanad":"عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ:","matn":"«الْقَاتِلُ لَا يَرِثُ» وفي رواية: «لَيْسَ لِلْقَاتِلِ مِنَ الْمِيرَاثِ شَيْءٌ»","source":"رواه الترمذي (2109) وابن ماجه (2645) وصححه الألباني في الإرواء (1672)","benefit":"القاعدة الفقهية الجنائية والمالية الكبرى: (من استعجل الشيء قبل أوانه عوقب بحرمانه)، فإذا قتل الوارث مورثه سقط حقه في الميراث والدية كلياً."},{"num":68,"cat":"inheritance","search":"68 قضاء النبي ﷺ بفرض السُّدس للجدة عند عدم وجود الأم عن قبيصة بن ذؤيب رضي الله عنه قال: «جَاءَتِ الْجَدَّةُ إِلَى أَبِي بَكْرٍ الصِّدِّيقِ تَسْأَلُهُ مِيرَاثَهَا، فَقَالَ: مَا لَكِ فِي كِتَابِ اللَّهِ شَيْءٌ... حَتَّى شَهِدَ الْمُغِيرَةُ بْنُ شُعْبَةَ وَمُحَمَّدُ بْنُ مَسْلَمَةَ أَنَّ رَسُولَ اللَّهِ ﷺ أَعْطَاهَا السُّدُسَ، فَأَنْفَذَهُ لَهَا أَبُو بَكْرٍ» رواه أبو داود (2894) والترمذي (2101) وصححه، وابن ماجه (2724) وصححه الألباني بيان فرض الجدة الشرعي الثابت بالسنة النبوية وإجماع الصحابة (السدس)، تقديراً للأمومة الكبرى عند فقد الأم المباشرة. ميراث الجدة السدس قبيصة بن ذؤيب المغيرة بن شعبة أصحاب الفروض","title":"قضاء النبي ﷺ بفرض السُّدس للجدة عند عدم وجود الأم","catBadge":"📜 مواريث وفرائض ووصايا","gradeBadge":"✓ صحيح","sanad":"عن قبيصة بن ذؤيب رضي الله عنه قال:","matn":"«جَاءَتِ الْجَدَّةُ إِلَى أَبِي بَكْرٍ الصِّدِّيقِ تَسْأَلُهُ مِيرَاثَهَا، فَقَالَ: مَا لَكِ فِي كِتَابِ اللَّهِ شَيْءٌ... حَتَّى شَهِدَ الْمُغِيرَةُ بْنُ شُعْبَةَ وَمُحَمَّدُ بْنُ مَسْلَمَةَ أَنَّ رَسُولَ اللَّهِ ﷺ أَعْطَاهَا السُّدُسَ، فَأَنْفَذَهُ لَهَا أَبُو بَكْرٍ»","source":"رواه أبو داود (2894) والترمذي (2101) وصححه، وابن ماجه (2724) وصححه الألباني","benefit":"بيان فرض الجدة الشرعي الثابت بالسنة النبوية وإجماع الصحابة (السدس)، تقديراً للأمومة الكبرى عند فقد الأم المباشرة."},{"num":69,"cat":"inheritance","search":"69 قضاء النبوة في ميراث الابنة وبنت الابن والأخت (تكملة الثلثين) عن عبد الله بن مسعود رضي الله عنه في قضائه المشهور الذي وافق قضاء رسول الله ﷺ قال: «لِلِابْنَةِ النِّصْفُ، وَلِابْنَةِ الِابْنِ السُّدُسُ تَكْمِلَةَ الثُّلُثَيْنِ، وَمَا بَقِيَ فَلِلْأُخْتِ» صحيح البخاري (6736) تطبيق حسابي فرائضي بالغ الدقة: إذا اجتمعت ابنة واحدة صلبة مع بنت ابن، فللصلبية النصف، ولبنت الابن السدس استيفاءً لحد الثلثين المخصص لإناث البنات، والباقي تعصيباً للأخت. للابنة النصف ولبنت الابن السدس تكملة الثلثين وما بقي فللأخت ابن مسعود الفرائض","title":"قضاء النبوة في ميراث الابنة وبنت الابن والأخت (تكملة الثلثين)","catBadge":"📜 مواريث وفرائض ووصايا","gradeBadge":"✓ صحيح البخاري","sanad":"عن عبد الله بن مسعود رضي الله عنه في قضائه المشهور الذي وافق قضاء رسول الله ﷺ قال:","matn":"«لِلِابْنَةِ النِّصْفُ، وَلِابْنَةِ الِابْنِ السُّدُسُ تَكْمِلَةَ الثُّلُثَيْنِ، وَمَا بَقِيَ فَلِلْأُخْتِ»","source":"صحيح البخاري (6736)","benefit":"تطبيق حسابي فرائضي بالغ الدقة: إذا اجتمعت ابنة واحدة صلبة مع بنت ابن، فللصلبية النصف، ولبنت الابن السدس استيفاءً لحد الثلثين المخصص لإناث البنات، والباقي تعصيباً للأخت."},{"num":70,"cat":"inheritance","search":"70 حرمة بيع أسباب الميراث: الولاء لحمة كلحمة النسب لا يباع ولا يوهب عن عبد الله بن عمر رضي الله عنهما قال: «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعِ الْوَلَاءِ، وَعَنْ هِبَتِهِ» صحيح البخاري (2535) وصحيح مسلم (1506) عدم جواز المتاجرة أو التنازل بمقابل مالي عن حقوق وأسباب التوارث والنسب، لأن أسباب الميراث في الشريعة صلات قرابة ثابتة بحكم الشرع لا تقبل المعاوضة التجارية. نهى عن بيع الولاء وعن هبته لحمة كلحمة النسب تجارة المواريث المتاجرة بالحقوق","title":"حرمة بيع أسباب الميراث: الولاء لحمة كلحمة النسب لا يباع ولا يوهب","catBadge":"📜 مواريث وفرائض ووصايا","gradeBadge":"✓ متفق عليه","sanad":"عن عبد الله بن عمر رضي الله عنهما قال:","matn":"«نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعِ الْوَلَاءِ، وَعَنْ هِبَتِهِ»","source":"صحيح البخاري (2535) وصحيح مسلم (1506)","benefit":"عدم جواز المتاجرة أو التنازل بمقابل مالي عن حقوق وأسباب التوارث والنسب، لأن أسباب الميراث في الشريعة صلات قرابة ثابتة بحكم الشرع لا تقبل المعاوضة التجارية."},{"num":71,"cat":"inheritance","search":"71 كفالة الدولة النبوية لديون المتوفى المعسر وتسليم صافي التركة للورثة عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال: «مَنْ تَرَكَ مَالًا فَلِوَرَثَتِهِ، وَمَنْ تَرَكَ كَلًّا أَوْ ضَيَاعًا فَإِلَيَّ، وَعَلَيَّ» صحيح البخاري (2298) وصحيح مسلم (1619) أصل الضمان الاجتماعي الحكومي الإسلامي: سلامة تركة المتوفى وخلوصها لورثته، وتكفل بيت مال الدولة بسداد ديون المعسرين الذين ماتوا وعجزت تركاتهم عن الوفاء. من ترك مالا فلورثته ومن ترك كلا أو ضياعا فإلي وعلي ديون الميت الضمان الاجتماعي","title":"كفالة الدولة النبوية لديون المتوفى المعسر وتسليم صافي التركة للورثة","catBadge":"📜 مواريث وفرائض ووصايا","gradeBadge":"✓ متفق عليه","sanad":"عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال:","matn":"«مَنْ تَرَكَ مَالًا فَلِوَرَثَتِهِ، وَمَنْ تَرَكَ كَلًّا أَوْ ضَيَاعًا فَإِلَيَّ، وَعَلَيَّ»","source":"صحيح البخاري (2298) وصحيح مسلم (1619)","benefit":"أصل الضمان الاجتماعي الحكومي الإسلامي: سلامة تركة المتوفى وخلوصها لورثته، وتكفل بيت مال الدولة بسداد ديون المعسرين الذين ماتوا وعجزت تركاتهم عن الوفاء."},{"num":72,"cat":"labor","search":"72 وجوب المبادرة الفورية بدفع أجور العمال والموظفين دون مماطلة عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ: «أَعْطُوا الْأَجِيرَ أَجْرَهُ قَبْلَ أَنْ يَجِفَّ عَرَقُهُ» رواه ابن ماجه (2443) وصححه الألباني في الإرواء (1498) إلزام أصحاب العمل والمنشآت بتحويل رواتب ومستحقات الموظفين والعمال في مواعيدها المحددة فور الانتهاء من أداء العمل، وتجريم تأخير الرواتب بحجج واهية. أعطوا الأجير أجره قبل أن يجف عرقه أجور العمال الرواتب نظام حماية الأجور حقوق العمال","title":"وجوب المبادرة الفورية بدفع أجور العمال والموظفين دون مماطلة","catBadge":"👥 أجور وعمال وشركات","gradeBadge":"✓ صحيح","sanad":"عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ:","matn":"«أَعْطُوا الْأَجِيرَ أَجْرَهُ قَبْلَ أَنْ يَجِفَّ عَرَقُهُ»","source":"رواه ابن ماجه (2443) وصححه الألباني في الإرواء (1498)","benefit":"إلزام أصحاب العمل والمنشآت بتحويل رواتب ومستحقات الموظفين والعمال في مواعيدها المحددة فور الانتهاء من أداء العمل، وتجريم تأخير الرواتب بحجج واهية."},{"num":73,"cat":"labor","search":"73 خصومة الله العظمى يوم القيامة لمن استوفى جهد العامل وبخسه حقه عن أبي هريرة رضي الله عنه عن النبي ﷺ قال: قال الله تعالى: «ثَلَاثَةٌ أَنَا خَصْمُهُمْ يَوْمَ الْقِيَامَةِ: رَجُلٌ أَعْطَى بِي ثُمَّ غَدَرَ، وَرَجُلٌ بَاعَ حُرًّا فَأَكَلَ ثَمَنَهُ، وَرَجُلٌ اسْتَأْجَرَ أَجِيرًا فَاسْتَوْفَى مِنْهُ وَلَمْ يُعْطِهِ أَجْرَهُ» صحيح البخاري (2227) وعيد إلهي مرعب لأي مدير أو صاحب شركة يستغل حاجة العمال فيكلفهم بأعمال شاقة ثم ينكر مستحقاتهم أو يقتطع منها ظلماً وعدواناً. ثلاثة أنا خصمهم يوم القيامة استأجر أجيرا فاستوفى منه ولم يعطه أجره بخس الأجور","title":"خصومة الله العظمى يوم القيامة لمن استوفى جهد العامل وبخسه حقه","catBadge":"👥 أجور وعمال وشركات","gradeBadge":"✓ صحيح البخاري","sanad":"عن أبي هريرة رضي الله عنه عن النبي ﷺ قال: قال الله تعالى:","matn":"«ثَلَاثَةٌ أَنَا خَصْمُهُمْ يَوْمَ الْقِيَامَةِ: رَجُلٌ أَعْطَى بِي ثُمَّ غَدَرَ، وَرَجُلٌ بَاعَ حُرًّا فَأَكَلَ ثَمَنَهُ، وَرَجُلٌ اسْتَأْجَرَ أَجِيرًا فَاسْتَوْفَى مِنْهُ وَلَمْ يُعْطِهِ أَجْرَهُ»","source":"صحيح البخاري (2227)","benefit":"وعيد إلهي مرعب لأي مدير أو صاحب شركة يستغل حاجة العمال فيكلفهم بأعمال شاقة ثم ينكر مستحقاتهم أو يقتطع منها ظلماً وعدواناً."},{"num":74,"cat":"labor","search":"74 بركة الأمانة والشفافية بين الشركاء وخروج البركة وحلول الخسارة بالخيانة عن أبي هريرة رضي الله عنه رفعه قال: إن الله يقول: «أَنَا ثَالِثُ الشَّرِيكَيْنِ مَا لَمْ يَخُنْ أَحَدُهُمَا صَاحِبَهُ، فَإِذَا خَانَهُ خَرَجْتُ مِنْ بَيْنِهِمَا» رواه أبو داود (3383) والحاكم (2322) وصححه، وصححه الألباني في غاية المرام (329) ميثاق الشراكة والمضاربة والمساهمة الاستثمارية: التوفيق الإلهي والربح يرافق الشركاء ما دامت الحسابات مكشوفة وصادقة، وتنزع البركة فور إخفاء الأرباح أو التلاعب بالدفاتر. أنا ثالث الشريكين ما لم يخن أحدهما صاحبه عقد الشراكة المضاربة الاستثمار الأمانة","title":"بركة الأمانة والشفافية بين الشركاء وخروج البركة وحلول الخسارة بالخيانة","catBadge":"👥 أجور وعمال وشركات","gradeBadge":"✓ صحيح","sanad":"عن أبي هريرة رضي الله عنه رفعه قال: إن الله يقول:","matn":"«أَنَا ثَالِثُ الشَّرِيكَيْنِ مَا لَمْ يَخُنْ أَحَدُهُمَا صَاحِبَهُ، فَإِذَا خَانَهُ خَرَجْتُ مِنْ بَيْنِهِمَا»","source":"رواه أبو داود (3383) والحاكم (2322) وصححه، وصححه الألباني في غاية المرام (329)","benefit":"ميثاق الشراكة والمضاربة والمساهمة الاستثمارية: التوفيق الإلهي والربح يرافق الشركاء ما دامت الحسابات مكشوفة وصادقة، وتنزع البركة فور إخفاء الأرباح أو التلاعب بالدفاتر."},{"num":75,"cat":"labor","search":"75 إلزامية معايير الجودة والإتقان المهني والمالي في الأعمال والخدمات عن عائشة رضي الله عنها أن رسول الله ﷺ قال: «إِنَّ اللَّهَ يُحِبُّ إِذَا عَمِلَ أَحَدُكُمْ عَمَلًا أَنْ يُتْقِنَهُ» رواه البيهقي في شعب الإيمان (4929) وأبو يعلى (4386) وصححه الألباني في السلسلة الصحيحة (1113) أصل الجودة وإدارة التميز التشغيلي في الفكر الإسلامي: وجوب مطابقة المواصفات الهندسية والتجارية، وتحريم الغش في المواد الخام أو إنهاء الأعمال بنواقص معيبة. إن الله يحب إذا عمل أحدكم عملا أن يتقنه معايير الجودة الإتقان المهني مواصفات العمل","title":"إلزامية معايير الجودة والإتقان المهني والمالي في الأعمال والخدمات","catBadge":"👥 أجور وعمال وشركات","gradeBadge":"✓ صحيح","sanad":"عن عائشة رضي الله عنها أن رسول الله ﷺ قال:","matn":"«إِنَّ اللَّهَ يُحِبُّ إِذَا عَمِلَ أَحَدُكُمْ عَمَلًا أَنْ يُتْقِنَهُ»","source":"رواه البيهقي في شعب الإيمان (4929) وأبو يعلى (4386) وصححه الألباني في السلسلة الصحيحة (1113)","benefit":"أصل الجودة وإدارة التميز التشغيلي في الفكر الإسلامي: وجوب مطابقة المواصفات الهندسية والتجارية، وتحريم الغش في المواد الخام أو إنهاء الأعمال بنواقص معيبة."},{"num":76,"cat":"labor","search":"76 وجوب تحديد الراتب والمهام الوظيفية بوضوح تام قبل مباشرة العمل عن أبي سعيد الخدري رضي الله عنه أن النبي ﷺ قال: «مَنِ اسْتَأْجَرَ أَجِيرًا، فَلْيُعْلِمْهُ أَجْرَهُ» رواه أحمد (11525) والبيهقي في السنن الكبرى (12071) وصححه الألباني في الإرواء (1497) اشتراط وضوح بنود عقد العمل (توصيف الوظيفة، ساعات الدوام، الراتب الإجمالي والبدلات) لقطع النزاع والجهالة والغرر في سوق العمل والتوظيف. من استأجر أجيرا فليعلمه أجره تحديد الراتب عقد العمل التوصيف الوظيفي وضوح الأجر","title":"وجوب تحديد الراتب والمهام الوظيفية بوضوح تام قبل مباشرة العمل","catBadge":"👥 أجور وعمال وشركات","gradeBadge":"✓ صحيح","sanad":"عن أبي سعيد الخدري رضي الله عنه أن النبي ﷺ قال:","matn":"«مَنِ اسْتَأْجَرَ أَجِيرًا، فَلْيُعْلِمْهُ أَجْرَهُ»","source":"رواه أحمد (11525) والبيهقي في السنن الكبرى (12071) وصححه الألباني في الإرواء (1497)","benefit":"اشتراط وضوح بنود عقد العمل (توصيف الوظيفة، ساعات الدوام، الراتب الإجمالي والبدلات) لقطع النزاع والجهالة والغرر في سوق العمل والتوظيف."},{"num":77,"cat":"labor","search":"77 تحريم الاستئجار والتكسب من المهن المحرمة والأنشطة غير الأخلاقية عن أبي مسعود الأنصاري رضي الله عنه: «أَنَّ رَسُولَ اللَّهِ ﷺ نَهَى عَنْ ثَمَنِ الْكَلْبِ، وَمَهْرِ الْبَغِيِّ، وَحُلْوَانِ الْكَاهِنِ» صحيح البخاري (2237) وصحيح مسلم (1567) بطلان كافة عقود العمل والتوظيف في الأنشطة المحرمة كالرهانات وتجارة المخدرات أو المنصات الإباحية والربوية، واعتبار الدخل الناتج عنها سحتاً باطلاً. نهى عن ثمن الكلب ومهر البغي وحلوان الكاهن المهن المحرمة الكسب الحرام بطلان العقد","title":"تحريم الاستئجار والتكسب من المهن المحرمة والأنشطة غير الأخلاقية","catBadge":"👥 أجور وعمال وشركات","gradeBadge":"✓ متفق عليه","sanad":"عن أبي مسعود الأنصاري رضي الله عنه:","matn":"«أَنَّ رَسُولَ اللَّهِ ﷺ نَهَى عَنْ ثَمَنِ الْكَلْبِ، وَمَهْرِ الْبَغِيِّ، وَحُلْوَانِ الْكَاهِنِ»","source":"صحيح البخاري (2237) وصحيح مسلم (1567)","benefit":"بطلان كافة عقود العمل والتوظيف في الأنشطة المحرمة كالرهانات وتجارة المخدرات أو المنصات الإباحية والربوية، واعتبار الدخل الناتج عنها سحتاً باطلاً."},{"num":78,"cat":"labor","search":"78 النهي عن إجارة قفيز الطحان (أخذ الأجر من عين العمل المعرض للتلف) عن أبي سعيد الخدري رضي الله عنه: «أَنَّ النَّبِيَّ ﷺ نَهَى عَنْ عَسِيبِ الْفَحْلِ، وَعَنْ قَفِيزِ الطَّحَّانِ» رواه الدارقطني (2998) والبيهقي في السنن (11984) وصححه الألباني في الإرواء (1492) قاعدة فقهية في تحديد الأجرة: اشتراط أن تكون الأجرة معلومة ومضمونة ومستقلة، وألا تكون جزءاً مجهولاً من عين ما ينتجه العمل إن كان عرضة للتلف دون ضمان. قفيز الطحان عسيب الفحل تحديد الأجرة ضمان الأجر عقود التشغيل","title":"النهي عن إجارة قفيز الطحان (أخذ الأجر من عين العمل المعرض للتلف)","catBadge":"👥 أجور وعمال وشركات","gradeBadge":"✓ صحيح بمجموع طرقه","sanad":"عن أبي سعيد الخدري رضي الله عنه:","matn":"«أَنَّ النَّبِيَّ ﷺ نَهَى عَنْ عَسِيبِ الْفَحْلِ، وَعَنْ قَفِيزِ الطَّحَّانِ»","source":"رواه الدارقطني (2998) والبيهقي في السنن (11984) وصححه الألباني في الإرواء (1492)","benefit":"قاعدة فقهية في تحديد الأجرة: اشتراط أن تكون الأجرة معلومة ومضمونة ومستقلة، وألا تكون جزءاً مجهولاً من عين ما ينتجه العمل إن كان عرضة للتلف دون ضمان."},{"num":79,"cat":"labor","search":"79 أعظم الكسب والرزق شرفاً وبركة: ما كسبه العبد من كدّ يده وعمله عن المقدام بن معديكرب رضي الله عنه عن النبي ﷺ قال: «مَا أَكَلَ أَحَدٌ طَعَامًا قَطُّ، خَيْرًا مِنْ أَنْ يَأْكُلَ مِنْ عَمَلِ يَدِهِ، وَإِنَّ نَبِيَّ اللَّهِ دَاوُدَ عَلَيْهِ السَّلَامُ كَانَ يَأْكُلُ مِنْ عَمَلِ يَدِهِ» صحيح البخاري (2072) التعظيم النبوي للعمل اليدوي والمهني والصناعي والحرفي، وبيان أن الاعتماد على الكسب الذاتي والشرف المهني هو منهاج الأنبياء والمرسلين. ما أكل أحد طعاما قط خيرا من أن يأكل من عمل يده نبي الله داود كسب اليد العمل الحرفي","title":"أعظم الكسب والرزق شرفاً وبركة: ما كسبه العبد من كدّ يده وعمله","catBadge":"👥 أجور وعمال وشركات","gradeBadge":"✓ صحيح البخاري","sanad":"عن المقدام بن معديكرب رضي الله عنه عن النبي ﷺ قال:","matn":"«مَا أَكَلَ أَحَدٌ طَعَامًا قَطُّ، خَيْرًا مِنْ أَنْ يَأْكُلَ مِنْ عَمَلِ يَدِهِ، وَإِنَّ نَبِيَّ اللَّهِ دَاوُدَ عَلَيْهِ السَّلَامُ كَانَ يَأْكُلُ مِنْ عَمَلِ يَدِهِ»","source":"صحيح البخاري (2072)","benefit":"التعظيم النبوي للعمل اليدوي والمهني والصناعي والحرفي، وبيان أن الاعتماد على الكسب الذاتي والشرف المهني هو منهاج الأنبياء والمرسلين."},{"num":80,"cat":"labor","search":"80 شرف المهن والإنتاج: ما بعث الله نبياً إلا رعى الغنم وتكسب بجهده عن أبي هريرة رضي الله عنه عن النبي ﷺ قال: «مَا بَعَثَ اللَّهُ نَبِيًّا إِلَّا رَعَى الْغَنَمَ، فَقَالَ أَصْحَابُهُ: وَأَنْتَ؟ فَقَالَ: نَعَمْ، كُنْتُ أَرْعَاهَا عَلَى قَرَارِيطَ لِأَهْلِ مَكَّةَ» صحيح البخاري (2262) إرساء التواضع المهني ومحاربة ثقافة العيب في العمل الميداني والإنتاجي، وإثبات أن أعظم الخلق ﷺ عمل أجيراً بأجر محدد (قراريط) قبل النبوة. ما بعث الله نبيا إلا رعى الغنم قراريط لأهل مكة ثقافة العمل الميداني التواضع المهني","title":"شرف المهن والإنتاج: ما بعث الله نبياً إلا رعى الغنم وتكسب بجهده","catBadge":"👥 أجور وعمال وشركات","gradeBadge":"✓ صحيح البخاري","sanad":"عن أبي هريرة رضي الله عنه عن النبي ﷺ قال:","matn":"«مَا بَعَثَ اللَّهُ نَبِيًّا إِلَّا رَعَى الْغَنَمَ، فَقَالَ أَصْحَابُهُ: وَأَنْتَ؟ فَقَالَ: نَعَمْ، كُنْتُ أَرْعَاهَا عَلَى قَرَارِيطَ لِأَهْلِ مَكَّةَ»","source":"صحيح البخاري (2262)","benefit":"إرساء التواضع المهني ومحاربة ثقافة العيب في العمل الميداني والإنتاجي، وإثبات أن أعظم الخلق ﷺ عمل أجيراً بأجر محدد (قراريط) قبل النبوة."},{"num":81,"cat":"contracts","search":"81 دستور عقد السَّلَم والتمويل المسبق للإنتاج وضوابطه الصارمة عن عبد الله بن عباس رضي الله عنهما قال: قَدِمَ النبيُّ ﷺ المدينةَ وهُمْ يُسْلِفُونَ بالتَّمْرِ السَّنَتَيْنِ والثَّلَاثَ، فقال: «مَنْ أَسْلَفَ فِي شَيْءٍ، فَفِي كَيْلٍ مَعْلُومٍ، وَوَزْنٍ مَعْلُومٍ، إِلَى أَجَلٍ مَعْلُومٍ» صحيح البخاري (2240) وصحيح مسلم (1604) أركان عقد السلم الاستثماري المعتمد في البنوك الإسلامية: دفع الثمن نقداً بالكامل مقدماً لتوفير السيولة للجهة الصانعة أو المزارعة، مع التحديد القاطع للمواصفات والكمية وتاريخ التسليم لمنع النزاع. من أسلف في شيء ففي كيل معلوم ووزن معلوم إلى أجل معلوم عقد السلم التمويل الإسلامي","title":"دستور عقد السَّلَم والتمويل المسبق للإنتاج وضوابطه الصارمة","catBadge":"📑 سلم وشفعة ومساقاة","gradeBadge":"✓ متفق عليه","sanad":"عن عبد الله بن عباس رضي الله عنهما قال: قَدِمَ النبيُّ ﷺ المدينةَ وهُمْ يُسْلِفُونَ بالتَّمْرِ السَّنَتَيْنِ والثَّلَاثَ، فقال:","matn":"«مَنْ أَسْلَفَ فِي شَيْءٍ، فَفِي كَيْلٍ مَعْلُومٍ، وَوَزْنٍ مَعْلُومٍ، إِلَى أَجَلٍ مَعْلُومٍ»","source":"صحيح البخاري (2240) وصحيح مسلم (1604)","benefit":"أركان عقد السلم الاستثماري المعتمد في البنوك الإسلامية: دفع الثمن نقداً بالكامل مقدماً لتوفير السيولة للجهة الصانعة أو المزارعة، مع التحديد القاطع للمواصفات والكمية وتاريخ التسليم لمنع النزاع."},{"num":82,"cat":"contracts","search":"82 قضاء النبوة بالشفعة في العقارات والأراضي المشتركة قبل القسمة عن جابر بن عبد الله رضي الله عنهما قال: «قَضَى رَسُولُ اللَّهِ ﷺ بِالشُّفْعَةِ فِي كُلِّ مَا لَمْ يُقْسَمْ، فَإِذَا وَقَعَتِ الْحُدُودُ، وَصُرِّفَتِ الطُّرُقُ، فَلَا شُفْعَةَ» صحيح البخاري (2257) وصحيح مسلم (1608) حق الشفعة للشريك في العقار أو الأرض غير المفرزة لدرء الضرر عنه بشراء حصة شريكه بنفس الثمن الذي اتفق عليه مع المشتري الأجنبي، وسقوط الشفعة بعد الفرز وتحديد الحدود. قضى بالشفعة في كل ما لم يقسم وقعت الحدود صرفت الطرق حق الشفعة العقارات الأراضي","title":"قضاء النبوة بالشفعة في العقارات والأراضي المشتركة قبل القسمة","catBadge":"📑 سلم وشفعة ومساقاة","gradeBadge":"✓ متفق عليه","sanad":"عن جابر بن عبد الله رضي الله عنهما قال:","matn":"«قَضَى رَسُولُ اللَّهِ ﷺ بِالشُّفْعَةِ فِي كُلِّ مَا لَمْ يُقْسَمْ، فَإِذَا وَقَعَتِ الْحُدُودُ، وَصُرِّفَتِ الطُّرُقُ، فَلَا شُفْعَةَ»","source":"صحيح البخاري (2257) وصحيح مسلم (1608)","benefit":"حق الشفعة للشريك في العقار أو الأرض غير المفرزة لدرء الضرر عنه بشراء حصة شريكه بنفس الثمن الذي اتفق عليه مع المشتري الأجنبي، وسقوط الشفعة بعد الفرز وتحديد الحدود."},{"num":83,"cat":"contracts","search":"83 حق الجار الملاصق في أولوية الشراء والعرض عليه قبل الغريب عن الشريد بن سويد رضي الله عنه قال: سمعت رسول الله ﷺ يقول: «الْجَارُ أَحَقُّ بِصَقَبِهِ مَا كَانَ» [أي: بِقُرْبِهِ وَشُفْعَتِهِ] صحيح البخاري (2258) استحباب -وفي مذهب الحنفية وجوب- عرض العقار أو الدار على الجار الملاصق قبل بيعه للأجنبي، حفاظاً على حسن الجوار ومنعاً لدخول من قد يضر بالحي أو البناء. الجار أحق بصقبه حق الجوار أولوية الشراء شفعة الجوار بيع العقارات","title":"حق الجار الملاصق في أولوية الشراء والعرض عليه قبل الغريب","catBadge":"📑 سلم وشفعة ومساقاة","gradeBadge":"✓ صحيح البخاري","sanad":"عن الشريد بن سويد رضي الله عنه قال: سمعت رسول الله ﷺ يقول:","matn":"«الْجَارُ أَحَقُّ بِصَقَبِهِ مَا كَانَ» [أي: بِقُرْبِهِ وَشُفْعَتِهِ]","source":"صحيح البخاري (2258)","benefit":"استحباب -وفي مذهب الحنفية وجوب- عرض العقار أو الدار على الجار الملاصق قبل بيعه للأجنبي، حفاظاً على حسن الجوار ومنعاً لدخول من قد يضر بالحي أو البناء."},{"num":84,"cat":"contracts","search":"84 مشروعية عقود المساقاة والمزارعة والمشاركة في المحصول بنسبة شائعة عن عبد الله بن عمر رضي الله عنهما: «أَنَّ رَسُولَ اللَّهِ ﷺ أَعْطَى خَيْبَرَ الْيَهُودَ، عَلَى أَنْ يَعْمَلُوهَا وَيَزْرَعُوهَا، وَلَهُمْ شَطْرُ مَا يَخْرُجُ مِنْهَا مِنْ ثَمَرٍ أَوْ زَرْعٍ» صحيح البخاري (2328) وصحيح مسلم (1551) تأصيل عقود الاستثمار الزراعي (المساقاة والمزارعة): تقديم الأرض والمزارع من طرف والعمل من طرف آخر، وتقاسم صافي الإنتاج بنسبة مئوية شائعة (كـ 50% أو 30%) دون تحديد أجر نقدي ثابت مشروط. أعطى خيبر على أن يعملوها ويزرعوها ولهم شطر ما يخرج منها المساقاة المزارعة الاستثمار الزراعي","title":"مشروعية عقود المساقاة والمزارعة والمشاركة في المحصول بنسبة شائعة","catBadge":"📑 سلم وشفعة ومساقاة","gradeBadge":"✓ متفق عليه","sanad":"عن عبد الله بن عمر رضي الله عنهما:","matn":"«أَنَّ رَسُولَ اللَّهِ ﷺ أَعْطَى خَيْبَرَ الْيَهُودَ، عَلَى أَنْ يَعْمَلُوهَا وَيَزْرَعُوهَا، وَلَهُمْ شَطْرُ مَا يَخْرُجُ مِنْهَا مِنْ ثَمَرٍ أَوْ زَرْعٍ»","source":"صحيح البخاري (2328) وصحيح مسلم (1551)","benefit":"تأصيل عقود الاستثمار الزراعي (المساقاة والمزارعة): تقديم الأرض والمزارع من طرف والعمل من طرف آخر، وتقاسم صافي الإنتاج بنسبة مئوية شائعة (كـ 50% أو 30%) دون تحديد أجر نقدي ثابت مشروط."},{"num":85,"cat":"contracts","search":"85 تشجيع الاستثمار واستصلاح الأراضي المهملة: من أحيا أرضاً ميتة فهي له عن سعيد بن زيد رضي الله عنه عن النبي ﷺ قال: «مَنْ أَحْيَا أَرْضًا مَيْتَةً فَهِيَ لَهُ، وَلَيْسَ لِعِرْقٍ ظَالِمٍ حَقٌّ» رواه أبو داود (3073) والترمذي (1378) وصححه، وصححه الألباني في الإرواء (1550) تحفيز التنمية العمرانية والزراعية: من استصلح أرضاً بواراً لا مالك لها بإذن الدولة وحفر فيها بئراً أو زرعها تملكها شرعاً، وبطلان وضع اليد الجائر الغاصب (العرق الظالم). من أحيا أرضا ميتة فهي له ليس لعرق ظالم حق إحياء الموات استصلاح الأراضي وضع اليد","title":"تشجيع الاستثمار واستصلاح الأراضي المهملة: من أحيا أرضاً ميتة فهي له","catBadge":"📑 سلم وشفعة ومساقاة","gradeBadge":"✓ صحيح","sanad":"عن سعيد بن زيد رضي الله عنه عن النبي ﷺ قال:","matn":"«مَنْ أَحْيَا أَرْضًا مَيْتَةً فَهِيَ لَهُ، وَلَيْسَ لِعِرْقٍ ظَالِمٍ حَقٌّ»","source":"رواه أبو داود (3073) والترمذي (1378) وصححه، وصححه الألباني في الإرواء (1550)","benefit":"تحفيز التنمية العمرانية والزراعية: من استصلح أرضاً بواراً لا مالك لها بإذن الدولة وحفر فيها بئراً أو زرعها تملكها شرعاً، وبطلان وضع اليد الجائر الغاصب (العرق الظالم)."},{"num":86,"cat":"contracts","search":"86 دستور المرافق العامة والملكية المشتركة: الناس شركاء في ثلاث عن رجل من المهاجرين من أصحاب النبي ﷺ قال: قال رسول الله ﷺ: «الْمُسْلِمُونَ شُرَكَاءُ فِي ثَلَاثٍ: فِي الْمَاءِ، وَالْكَلَإِ، وَالنَّارِ» رواه أبو داود (3477) وابن ماجه (2472) وصححه الألباني في صحيح الجامع (6701) حظر احتكار الموارد الطبيعية الحيوية ومصادر الطاقة والمياه العامة والمراعي الطبيعية، ووجوب إتاحتها لعموم المواطنين كمرافق عامة دون احتكار تجاري جائر. المسلمون شركاء في ثلاث في الماء والكلإ والنار المرافق العامة مصادر الطاقة حظر الاحتكار","title":"دستور المرافق العامة والملكية المشتركة: الناس شركاء في ثلاث","catBadge":"📑 سلم وشفعة ومساقاة","gradeBadge":"✓ صحيح","sanad":"عن رجل من المهاجرين من أصحاب النبي ﷺ قال: قال رسول الله ﷺ:","matn":"«الْمُسْلِمُونَ شُرَكَاءُ فِي ثَلَاثٍ: فِي الْمَاءِ، وَالْكَلَإِ، وَالنَّارِ»","source":"رواه أبو داود (3477) وابن ماجه (2472) وصححه الألباني في صحيح الجامع (6701)","benefit":"حظر احتكار الموارد الطبيعية الحيوية ومصادر الطاقة والمياه العامة والمراعي الطبيعية، ووجوب إتاحتها لعموم المواطنين كمرافق عامة دون احتكار تجاري جائر."},{"num":87,"cat":"contracts","search":"87 تحريم بيع فضل الماء في الآبار والأنهار لمنع رعي الماشية والأراضي عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال: «لَا يُمْنَعُ فَضْلُ الْمَاءِ؛ لِيُمْنَعَ بِهِ الْكَلَأُ» صحيح البخاري (2353) وصحيح مسلم (1566) حماية الموارد المائية من التعسف في استعمال الحق، وإلزام الملاك بالسماح للآخرين بالاستفادة من الفائض من المياه للشرب والري دون أخذ مقابل تعسفي. لا يمنع فضل الماء ليمنع به الكلأ الموارد المائية التعسف في استعمال الحق","title":"تحريم بيع فضل الماء في الآبار والأنهار لمنع رعي الماشية والأراضي","catBadge":"📑 سلم وشفعة ومساقاة","gradeBadge":"✓ متفق عليه","sanad":"عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال:","matn":"«لَا يُمْنَعُ فَضْلُ الْمَاءِ؛ لِيُمْنَعَ بِهِ الْكَلَأُ»","source":"صحيح البخاري (2353) وصحيح مسلم (1566)","benefit":"حماية الموارد المائية من التعسف في استعمال الحق، وإلزام الملاك بالسماح للآخرين بالاستفادة من الفائض من المياه للشرب والري دون أخذ مقابل تعسفي."},{"num":88,"cat":"contracts","search":"88 مشروعية عقد الجِعالة واستحقاق المكافأة المالية المشروطة عند إتمام الإنجاز عن أبي سعيد الخدري رضي الله عنه في قصة رقية سيد الحي بديغ بفاتحة الكتاب واشتراط قطيع من الغنم كجُعْل ومكافأة، فقال رسول الله ﷺ مقراً لهم: «اقْسِمُوا وَاضْرِبُوا لِي مَعَكُمْ بِسَهْمٍ» صحيح البخاري (2276) وصحيح مسلم (2201) تأصيل عقود الجعالة والمكافآت المرتبطة بالنتائج ومكافآت النجاح (Success Fees) وعمولات السمسرة المشروطة بإنجاز العمل كاملاً وقبض الجعل المستحق. عقد الجعالة اقسموا واضربوا لي معكم بسهم رقية اللديغ مكافآت الإنجاز عمولات السمسرة","title":"مشروعية عقد الجِعالة واستحقاق المكافأة المالية المشروطة عند إتمام الإنجاز","catBadge":"📑 سلم وشفعة ومساقاة","gradeBadge":"✓ متفق عليه","sanad":"عن أبي سعيد الخدري رضي الله عنه في قصة رقية سيد الحي بديغ بفاتحة الكتاب واشتراط قطيع من الغنم كجُعْل ومكافأة، فقال رسول الله ﷺ مقراً لهم:","matn":"«اقْسِمُوا وَاضْرِبُوا لِي مَعَكُمْ بِسَهْمٍ»","source":"صحيح البخاري (2276) وصحيح مسلم (2201)","benefit":"تأصيل عقود الجعالة والمكافآت المرتبطة بالنتائج ومكافآت النجاح (Success Fees) وعمولات السمسرة المشروطة بإنجاز العمل كاملاً وقبض الجعل المستحق."},{"num":89,"cat":"property","search":"89 دستور اللقطة والمال الضائع: حفظ الأوصاف والإعلان والتعريف لمدة عام عن زيد بن خالد الجهني رضي الله عنه أن رجلاً سأل رسول الله ﷺ عن اللُّقَطَةِ، فقال: «اعْرِفْ عِفَاصَهَا وَوِكَاءَهَا، ثُمَّ عَرِّفْهَا سَنَةً، فَإِنْ جَاءَ صَاحِبُهَا، وَإِلَّا فَشَأْنُكَ بِهَا» صحيح البخاري (2426) وصحيح مسلم (1722) التوثيق القانوني والشرعي للأموال والمفقودات الثمينة المعثور عليها: فحص وحفظ مواصفاتها وأغلفتها بدقة، والإعلان عنها في الوسائل المعاصرة لمدة سنة كاملة قبل التصرف فيها كوديعة مضمونة. اعرف عفاصها ووكاءها ثم عرفها سنة اللقطة الأموال الضائعة المفقودات التوثيق","title":"دستور اللقطة والمال الضائع: حفظ الأوصاف والإعلان والتعريف لمدة عام","catBadge":"🛡️ لقطة وأمانات وحفظ مال","gradeBadge":"✓ متفق عليه","sanad":"عن زيد بن خالد الجهني رضي الله عنه أن رجلاً سأل رسول الله ﷺ عن اللُّقَطَةِ، فقال:","matn":"«اعْرِفْ عِفَاصَهَا وَوِكَاءَهَا، ثُمَّ عَرِّفْهَا سَنَةً، فَإِنْ جَاءَ صَاحِبُهَا، وَإِلَّا فَشَأْنُكَ بِهَا»","source":"صحيح البخاري (2426) وصحيح مسلم (1722)","benefit":"التوثيق القانوني والشرعي للأموال والمفقودات الثمينة المعثور عليها: فحص وحفظ مواصفاتها وأغلفتها بدقة، والإعلان عنها في الوسائل المعاصرة لمدة سنة كاملة قبل التصرف فيها كوديعة مضمونة."},{"num":90,"cat":"property","search":"90 التفريق بين اللقطة المعرضة للضياع والحيوانات والممتلكات المصونة عن زيد بن خالد الجهني رضي الله عنه قال: سئل النبي ﷺ عن ضالة الغنم؟ قال: «هِيَ لَكَ أَوْ لِأَخِيكَ أَوْ لِلذِّئْبِ»، وسئل عن ضالة الإبل؟ فغضب وقال: «مَا لَكَ وَلَهَا؟ مَعَهَا حِذَاؤُهَا وَسِقَاؤُهَا، تَرِدُ الْمَاءَ، وَتَأْكُلُ الشَّجَرَ، حَتَّى يَلْقَاهَا رَبُّهَا» صحيح البخاري (2427) وصحيح مسلم (1722) المنع من حيازة والتقاط الممتلكات القادرة على حماية نفسها أو المحفوظة في أماكنها الآمنة خشية التعدي، ووجوب إنقاذ وحفظ الأموال الهشة المعرضة للتلف والضياع الفوري. ضالة الغنم لك أو لأخيك أو للذئب ضالة الإبل معها حذاؤها وسقاؤها حفظ الممتلكات","title":"التفريق بين اللقطة المعرضة للضياع والحيوانات والممتلكات المصونة","catBadge":"🛡️ لقطة وأمانات وحفظ مال","gradeBadge":"✓ متفق عليه","sanad":"عن زيد بن خالد الجهني رضي الله عنه قال: سئل النبي ﷺ عن ضالة الغنم؟ قال: «هِيَ لَكَ أَوْ لِأَخِيكَ أَوْ لِلذِّئْبِ»، وسئل عن ضالة الإبل؟ فغضب وقال:","matn":"«مَا لَكَ وَلَهَا؟ مَعَهَا حِذَاؤُهَا وَسِقَاؤُهَا، تَرِدُ الْمَاءَ، وَتَأْكُلُ الشَّجَرَ، حَتَّى يَلْقَاهَا رَبُّهَا»","source":"صحيح البخاري (2427) وصحيح مسلم (1722)","benefit":"المنع من حيازة والتقاط الممتلكات القادرة على حماية نفسها أو المحفوظة في أماكنها الآمنة خشية التعدي، ووجوب إنقاذ وحفظ الأموال الهشة المعرضة للتلف والضياع الفوري."},{"num":91,"cat":"property","search":"91 وجوب رد الأمانات والودائع المصرفية والتجارية وتحريم خيانة الخائن عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ: «أَدِّ الْأَمَانَةَ إِلَى مَنِ ائْتَمَنَكَ، وَلَا تَخُنْ مَنْ خَانَكَ» رواه أبو داود (3535) والترمذي (1264) وصححه، وصححه الألباني في السلسلة الصحيحة (423) قدسية حسابات الأمانات والودائع المصرفية والودائع العينية؛ ووجوب ردها كاملة لمالكها عند الطلب، والنهي الصارم عن مجاراة الخيانة بخيانة مقابلة أو اختلاس أموال من غدر بك. أد الأمانة إلى من ائتمنك ولا تخن من خانك الودائع المصرفية حسابات الأمانة رد الحقوق","title":"وجوب رد الأمانات والودائع المصرفية والتجارية وتحريم خيانة الخائن","catBadge":"🛡️ لقطة وأمانات وحفظ مال","gradeBadge":"✓ صحيح","sanad":"عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ:","matn":"«أَدِّ الْأَمَانَةَ إِلَى مَنِ ائْتَمَنَكَ، وَلَا تَخُنْ مَنْ خَانَكَ»","source":"رواه أبو داود (3535) والترمذي (1264) وصححه، وصححه الألباني في السلسلة الصحيحة (423)","benefit":"قدسية حسابات الأمانات والودائع المصرفية والودائع العينية؛ ووجوب ردها كاملة لمالكها عند الطلب، والنهي الصارم عن مجاراة الخيانة بخيانة مقابلة أو اختلاس أموال من غدر بك."},{"num":92,"cat":"property","search":"92 المسؤولية القانونية والضمان الشامل للأموال المقبوضة تحت يد الشخص عن سمرة بن جندب رضي الله عنه عن النبي ﷺ قال: «عَلَى الْيَدِ مَا أَخَذَتْ حَتَّى تُؤَدِّيَهُ» رواه أبو داود (3561) والترمذي (1266) وصححه، والنسائي وابن ماجه وصححه الحاكم والألباني القاعدة الأساسية في الضمان والتعويض والمسؤولية المدنية: كل من وضع يده على مال غيره بطريق عارية أو غصب أو استلام تجاري فهو ضامن لسلامته حتى يرجعه سالماً لمالكه الشرعي. على اليد ما أخذت حتى تؤديه قاعدة الضمان المسؤولية المدنية التعويض حيازة الأموال","title":"المسؤولية القانونية والضمان الشامل للأموال المقبوضة تحت يد الشخص","catBadge":"🛡️ لقطة وأمانات وحفظ مال","gradeBadge":"✓ صحيح","sanad":"عن سمرة بن جندب رضي الله عنه عن النبي ﷺ قال:","matn":"«عَلَى الْيَدِ مَا أَخَذَتْ حَتَّى تُؤَدِّيَهُ»","source":"رواه أبو داود (3561) والترمذي (1266) وصححه، والنسائي وابن ماجه وصححه الحاكم والألباني","benefit":"القاعدة الأساسية في الضمان والتعويض والمسؤولية المدنية: كل من وضع يده على مال غيره بطريق عارية أو غصب أو استلام تجاري فهو ضامن لسلامته حتى يرجعه سالماً لمالكه الشرعي."},{"num":93,"cat":"property","search":"93 حرمة الملكية الفردية الخاصة ومشروعية الدفاع عن المال حتى الشهادة عن عبد الله بن عمرو بن العاص رضي الله عنهما قال: سمعت النبي ﷺ يقول: «مَنْ قُتِلَ دُونَ مَالِهِ فَهُوَ شَهِيدٌ» صحيح البخاري (2480) وصحيح مسلم (141) تقرير قدسية الملكية الفردية والأموال الخاصة في الإسلام، ومنح صاحب المال الحق الكامل في حماية تجارته ومستودعاته وممتلكاته من اللصوص والغاصبين. من قتل دون ماله فهو شهيد حماية الملكية الخاصة الدفاع عن المال مكافحة السطو والسرقة","title":"حرمة الملكية الفردية الخاصة ومشروعية الدفاع عن المال حتى الشهادة","catBadge":"🛡️ لقطة وأمانات وحفظ مال","gradeBadge":"✓ متفق عليه","sanad":"عن عبد الله بن عمرو بن العاص رضي الله عنهما قال: سمعت النبي ﷺ يقول:","matn":"«مَنْ قُتِلَ دُونَ مَالِهِ فَهُوَ شَهِيدٌ»","source":"صحيح البخاري (2480) وصحيح مسلم (141)","benefit":"تقرير قدسية الملكية الفردية والأموال الخاصة في الإسلام، ومنح صاحب المال الحق الكامل في حماية تجارته ومستودعاته وممتلكاته من اللصوص والغاصبين."},{"num":94,"cat":"property","search":"94 عقوبة غصب العقارات وتزوير حجج الأراضي والتعدي على المخططات عن سعيد بن زيد رضي الله عنه قال: سمعت رسول الله ﷺ يقول: «مَنْ ظَلَمَ قِيدَ شِبْرٍ مِنَ الْأَرْضِ، طُوِّقَهُ مِنْ سَبْعِ أَرَضِينَ» صحيح البخاري (2452) وصحيح مسلم (1610) التغليظ الشديد في تحريم التعدي على الأراضي والعقارات وتغيير حدود المخططات أو الاستيلاء على أملاك الجيران والورثة، وبيان شناعة عقوبتها الأخروية. من ظلم قيد شبر من الأرض طوقه من سبع أرضين غصب العقارات الاستيلاء على الأراضي تزوير الحجج","title":"عقوبة غصب العقارات وتزوير حجج الأراضي والتعدي على المخططات","catBadge":"🛡️ لقطة وأمانات وحفظ مال","gradeBadge":"✓ متفق عليه","sanad":"عن سعيد بن زيد رضي الله عنه قال: سمعت رسول الله ﷺ يقول:","matn":"«مَنْ ظَلَمَ قِيدَ شِبْرٍ مِنَ الْأَرْضِ، طُوِّقَهُ مِنْ سَبْعِ أَرَضِينَ»","source":"صحيح البخاري (2452) وصحيح مسلم (1610)","benefit":"التغليظ الشديد في تحريم التعدي على الأراضي والعقارات وتغيير حدود المخططات أو الاستيلاء على أملاك الجيران والورثة، وبيان شناعة عقوبتها الأخروية."},{"num":95,"cat":"integrity","search":"95 لعن الراشي والمرتشي في المعاملات والمناقصات والقضاء والوظائف عن عبد الله بن عمرو رضي الله عنهما قال: «لَعَنَ رَسُولُ اللَّهِ ﷺ الرَّاشِي وَالْمُرْتَشِي» رواه أبو داود (3580) والترمذي (1337) وصححه، وابن ماجه (2313) وصححه الألباني التحريم القطعي لكافة أشكال الرشاوى والإكراميات غير المشروعة لتمرير الصفقات أو الحصول على عقود أو تزييف التدقيق المالي، واستحقاق الطرد من رحمة الله للطرفين. لعن رسول الله الراشي والمرتشي الرشوة الفساد المالي المناقصات النزاهة الشفافية","title":"لعن الراشي والمرتشي في المعاملات والمناقصات والقضاء والوظائف","catBadge":"⚖️ نزاهة ومحاربة الرشوة","gradeBadge":"✓ صحيح","sanad":"عن عبد الله بن عمرو رضي الله عنهما قال:","matn":"«لَعَنَ رَسُولُ اللَّهِ ﷺ الرَّاشِي وَالْمُرْتَشِي»","source":"رواه أبو داود (3580) والترمذي (1337) وصححه، وابن ماجه (2313) وصححه الألباني","benefit":"التحريم القطعي لكافة أشكال الرشاوى والإكراميات غير المشروعة لتمرير الصفقات أو الحصول على عقود أو تزييف التدقيق المالي، واستحقاق الطرد من رحمة الله للطرفين."},{"num":96,"cat":"integrity","search":"96 تجريم عمولات الموظفين وهدايا المشتريات: هدايا العمال غلول وخيانة عن أبي حميد الساعدي رضي الله عنه أن رسول الله ﷺ استعمل ابن اللُّتْبِيَّة على الصدقة فجاء فقال: هذا لكم وهذا أهدي لي، فقام ﷺ خطيباً وقال: «مَا بَالُ الْعَامِلِ نَبْعَثُهُ فَيَقُولُ: هَذَا لَكُمْ وَهَذَا أُهْدِيَ لِي! أَفَلَا جَلَسَ فِي بَيْتِ أَبِيهِ أَوْ بَيْتِ أُمِّهِ فَيَنْظُرَ أَيُهْدَى إِلَيْهِ أَمْ لَا؟ وَالَّذِي نَفْسِي بِيَدِهِ لَا يَأْخُذُ أَحَدٌ مِنْهُ شَيْئًا إِلَّا جَاءَ بِهِ يَوْمَ الْقِيَامَةِ يَحْمِلُهُ عَلَى رَقَبَتِهِ» صحيح البخاري (2597) وصحيح مسلم (1832) المعيار النبوي الدقيق في مكافحة تضارب المصالح والحوكمة المؤسسية: تحريم قبول موظفي المشتريات أو العقود أو الجباية لأي هدايا أو عمولات خاصة من الموردين والعملاء. هدايا العمال غلول ابن اللتبية أفلا جلس في بيت أبيه تضارب المصالح عمولات المشتريات الحوكمة","title":"تجريم عمولات الموظفين وهدايا المشتريات: هدايا العمال غلول وخيانة","catBadge":"⚖️ نزاهة ومحاربة الرشوة","gradeBadge":"✓ متفق عليه","sanad":"عن أبي حميد الساعدي رضي الله عنه أن رسول الله ﷺ استعمل ابن اللُّتْبِيَّة على الصدقة فجاء فقال: هذا لكم وهذا أهدي لي، فقام ﷺ خطيباً وقال:","matn":"«مَا بَالُ الْعَامِلِ نَبْعَثُهُ فَيَقُولُ: هَذَا لَكُمْ وَهَذَا أُهْدِيَ لِي! أَفَلَا جَلَسَ فِي بَيْتِ أَبِيهِ أَوْ بَيْتِ أُمِّهِ فَيَنْظُرَ أَيُهْدَى إِلَيْهِ أَمْ لَا؟ وَالَّذِي نَفْسِي بِيَدِهِ لَا يَأْخُذُ أَحَدٌ مِنْهُ شَيْئًا إِلَّا جَاءَ بِهِ يَوْمَ الْقِيَامَةِ يَحْمِلُهُ عَلَى رَقَبَتِهِ»","source":"صحيح البخاري (2597) وصحيح مسلم (1832)","benefit":"المعيار النبوي الدقيق في مكافحة تضارب المصالح والحوكمة المؤسسية: تحريم قبول موظفي المشتريات أو العقود أو الجباية لأي هدايا أو عمولات خاصة من الموردين والعملاء."},{"num":97,"cat":"integrity","search":"97 شؤم التكسب من السحت والحرام واستحقاق النار للجسد النابت منه عن كعب بن عجرة رضي الله عنه قال: قال لي رسول الله ﷺ: «يَا كَعْبُ بْنَ عُجْرَةَ، إِنَّهُ لَا يَرْبُو لَحْمٌ نَبَتَ مِنْ سُحْتٍ إِلَّا كَانَتِ النَّارُ أَوْلَى بِهِ» رواه الترمذي (614) وصححه، وأحمد (14441) وصححه الألباني في صحيح الترغيب (1729) التحذير الرهيب من إدخال المال الحرام أو العمولات المشبوهة في طعام الأهل والذرية، وبيان أن أكل السحت يحرم العبد من استجابة الدعاء ويجلب الأمراض ومحق البركة. لا يربو لحم نبت من سحت إلا كانت النار أولى به أكل الحرام كسب السحت إطعام الأهل","title":"شؤم التكسب من السحت والحرام واستحقاق النار للجسد النابت منه","catBadge":"⚖️ نزاهة ومحاربة الرشوة","gradeBadge":"✓ صحيح","sanad":"عن كعب بن عجرة رضي الله عنه قال: قال لي رسول الله ﷺ:","matn":"«يَا كَعْبُ بْنَ عُجْرَةَ، إِنَّهُ لَا يَرْبُو لَحْمٌ نَبَتَ مِنْ سُحْتٍ إِلَّا كَانَتِ النَّارُ أَوْلَى بِهِ»","source":"رواه الترمذي (614) وصححه، وأحمد (14441) وصححه الألباني في صحيح الترغيب (1729)","benefit":"التحذير الرهيب من إدخال المال الحرام أو العمولات المشبوهة في طعام الأهل والذرية، وبيان أن أكل السحت يحرم العبد من استجابة الدعاء ويجلب الأمراض ومحق البركة."},{"num":98,"cat":"integrity","search":"98 عقوبة اقتطاع أموال الناس وابتلاع حقوقهم بالأيمان الكاذبة المغلظة عن عبد الله بن مسعود رضي الله عنه قال: قال رسول الله ﷺ: «مَنْ حَلَفَ عَلَى يَمِينِ صَبْرٍ يَقْتَطِعُ بِهَا مَالَ امْرِئٍ مُسْلِمٍ، هُوَ فِيهَا فَاجِرٌ؛ لَقِيَ اللَّهَ وَهُوَ عَلَيْهِ غَضْبَانُ» صحيح البخاري (2666) وصحيح مسلم (138) تجريم شهادة الزور والأيمان الكاذبة في المحاكم والمرافعات لنهب الشركات أو حقوق الشركاء والورثة، وبيان أنها اليمين الغموس التي تغمس صاحبها في نار جهنم. من حلف على يمين صبر يقتطع بها مال امرئ مسلم اليمين الغموس شهادة الزور نهب الأموال","title":"عقوبة اقتطاع أموال الناس وابتلاع حقوقهم بالأيمان الكاذبة المغلظة","catBadge":"⚖️ نزاهة ومحاربة الرشوة","gradeBadge":"✓ متفق عليه","sanad":"عن عبد الله بن مسعود رضي الله عنه قال: قال رسول الله ﷺ:","matn":"«مَنْ حَلَفَ عَلَى يَمِينِ صَبْرٍ يَقْتَطِعُ بِهَا مَالَ امْرِئٍ مُسْلِمٍ، هُوَ فِيهَا فَاجِرٌ؛ لَقِيَ اللَّهَ وَهُوَ عَلَيْهِ غَضْبَانُ»","source":"صحيح البخاري (2666) وصحيح مسلم (138)","benefit":"تجريم شهادة الزور والأيمان الكاذبة في المحاكم والمرافعات لنهب الشركات أو حقوق الشركاء والورثة، وبيان أنها اليمين الغموس التي تغمس صاحبها في نار جهنم."},{"num":99,"cat":"integrity","search":"99 حرمة مال المسلم المطلقة: لا يحل أخذ أي جزء منه إلا بطيب نفس ورضا عن أبي حرة الرقاشي عن عمه رضي الله عنه أن رسول الله ﷺ قال في خطبة أيام التشريق: «أَلَا لَا تَظْلِمُوا، أَلَا لَا تَظْلِمُوا، أَلَا لَا تَظْلِمُوا، إِنَّهُ لَا يَحِلُّ مَالُ امْرِئٍ إِلَّا بِطِيبِ نَفْسٍ مِنْهُ» رواه أحمد (20695) والبيهقي في السنن (11325) والدارقطني وصححه الألباني في الإرواء (1459) أصل أصيل في حرمة الملكيات وحسابات الأفراد: بطلان أي استقطاع مالي أو فرض رسوم أو أخذ ممتلكات بالحياء أو الإحراج أو التحايل دون رضا قلبي تام واختيار حر. لا يحل مال امرئ إلا بطيب نفس منه ألا لا تظلموا طيب النفس الرضا التام استقطاع الأموال","title":"حرمة مال المسلم المطلقة: لا يحل أخذ أي جزء منه إلا بطيب نفس ورضا","catBadge":"⚖️ نزاهة ومحاربة الرشوة","gradeBadge":"✓ صحيح","sanad":"عن أبي حرة الرقاشي عن عمه رضي الله عنه أن رسول الله ﷺ قال في خطبة أيام التشريق:","matn":"«أَلَا لَا تَظْلِمُوا، أَلَا لَا تَظْلِمُوا، أَلَا لَا تَظْلِمُوا، إِنَّهُ لَا يَحِلُّ مَالُ امْرِئٍ إِلَّا بِطِيبِ نَفْسٍ مِنْهُ»","source":"رواه أحمد (20695) والبيهقي في السنن (11325) والدارقطني وصححه الألباني في الإرواء (1459)","benefit":"أصل أصيل في حرمة الملكيات وحسابات الأفراد: بطلان أي استقطاع مالي أو فرض رسوم أو أخذ ممتلكات بالحياء أو الإحراج أو التحايل دون رضا قلبي تام واختيار حر."},{"num":100,"cat":"integrity","search":"100 دستور الحوكمة وإدارة الثروات: النهي القاطع عن إضاعة المال والتبذير عن المغيرة بن شعبة رضي الله عنه قال: سمعت النبي ﷺ يقول: «إِنَّ اللَّهَ كَرِهَ لَكُمْ ثَلَاثًا: قِيلَ وَقَالَ، وَكَثْرَةَ السُّؤَالِ، وَإِضَاعَةَ الْمَالِ» صحيح البخاري (1477) وصحيح مسلم (593) التوجيه النبوي الأعلى في ترشيد الإنفاق وحسن التدبير المحاسبي والاستثماري: تحريم هدر الموارد المالية، ومحاربة المشاريع الخاسرة الناتجة عن الإهمال والتبذير وسوء التخطيط. إن الله كره لكم ثلاثا قيل وقال وكثرة السؤال وإضاعة المال ترشيد الإنفاق التبذير الحوكمة المالية","title":"دستور الحوكمة وإدارة الثروات: النهي القاطع عن إضاعة المال والتبذير","catBadge":"⚖️ نزاهة ومحاربة الرشوة","gradeBadge":"✓ متفق عليه","sanad":"عن المغيرة بن شعبة رضي الله عنه قال: سمعت النبي ﷺ يقول:","matn":"«إِنَّ اللَّهَ كَرِهَ لَكُمْ ثَلَاثًا: قِيلَ وَقَالَ، وَكَثْرَةَ السُّؤَالِ، وَإِضَاعَةَ الْمَالِ»","source":"صحيح البخاري (1477) وصحيح مسلم (593)","benefit":"التوجيه النبوي الأعلى في ترشيد الإنفاق وحسن التدبير المحاسبي والاستثماري: تحريم هدر الموارد المالية، ومحاربة المشاريع الخاسرة الناتجة عن الإهمال والتبذير وسوء التخطيط."}];

    function copyAyahCard(num) {
        const dataset = window.QURAN_VERSES_DATA || [];
        const item = dataset.find(v => v.num === num);
        if (!item) return;

        let textToCopy = `الدستور القرآني للمعاملات المالية (الآية #${item.num}):\n`;
        textToCopy += `المسألة: ${item.title}\n`;
        textToCopy += `الموضع: ${item.surah}\n\n`;
        textToCopy += `النص القرآني المحكم:\n${item.verseText}\n\n`;
        textToCopy += `الأحكام والفوائد الفقهية والاقتصادية المستنبطة:\n${item.guidance}\n\n`;
        textToCopy += `التصنيف: ${item.catBadge} | نص قطعي الثبوت والدلالة\n`;
        textToCopy += `— منقول من: الدستور القرآني للمعاملات المالية (نظام السجانة الذكي)`;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                showFiqhToast('✓ تم نسخ نص الآية الكريمة والأحكام بنجاح إلى الحافظة');
            }).catch(() => {
                fallbackCopyText(textToCopy);
            });
        } else {
            fallbackCopyText(textToCopy);
        }
    }

    function shareAyahWhatsApp(num) {
        const dataset = window.QURAN_VERSES_DATA || [];
        const item = dataset.find(v => v.num === num);
        if (!item) return;

        let text = `*الدستور القرآني للمعاملات المالية (#${item.num})*:\n`;
        text += `*الموضع*: ${item.surah}\n\n`;
        text += `${item.verseText}\n\n`;
        text += `*الأحكام والفوائد*: ${item.guidance}\n\n`;
        text += `_منظومة السجانة الذكية_`;

        const url = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(text);
        window.open(url, '_blank');
    }

    function copyHadithCard(num) {
        const dataset = window.HADITHS_DATA || [];
        const item = dataset.find(h => h.num === num);
        if (!item) return;

        let textToCopy = `الموسوعة النبوية الصحيحة للمعاملات المالية (الحديث #${item.num}):\n`;
        textToCopy += `الموضوع: ${item.title}\n`;
        textToCopy += `الباب: ${item.catBadge} | درجة الحديث: ${item.gradeBadge}\n\n`;
        if (item.sanad) {
            textToCopy += `السند:\n${item.sanad}\n`;
        }
        textToCopy += `الحديث النبوي الشريف:\n${item.matn}\n\n`;
        textToCopy += `التخريج والمصدر المعتمد:\n${item.source}\n\n`;
        textToCopy += `الضابط والفائدة للتاجر والمحاسب:\n${item.benefit}\n\n`;
        textToCopy += `— منقول من: الموسوعة النبوية الصحيحة للمعاملات المالية (نظام السجانة الذكي)`;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                showFiqhToast('✓ تم نسخ نص الحديث الشريف والتخريج بنجاح إلى الحافظة');
            }).catch(() => {
                fallbackCopyText(textToCopy);
            });
        } else {
            fallbackCopyText(textToCopy);
        }
    }

    function shareHadithWhatsApp(num) {
        const dataset = window.HADITHS_DATA || [];
        const item = dataset.find(h => h.num === num);
        if (!item) return;

        let text = `*الحديث النبوي الشريف (#${item.num})*:\n`;
        text += `*${item.title}*\n`;
        text += `الباب: ${item.catBadge} | درجة الصحة: ${item.gradeBadge}\n\n`;
        if (item.sanad) {
            text += `${item.sanad}\n`;
        }
        text += `${item.matn}\n\n`;
        text += `*التخريج*: ${item.source}\n\n`;
        text += `*الضابط والفائدة*: ${item.benefit}\n\n`;
        text += `_منظومة السجانة الذكية_`;

        const url = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(text);
        window.open(url, '_blank');
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 🤖 SMART AI ASSISTANT BRIDGE (openToolSuite & ensureFiqhLoaded) 🤖
    // ════════════════════════════════════════════════════════════════════════════
    window.ensureFiqhLoaded = ensureFiqhLoaded;

    window.openToolSuite = function(suiteId, subTab, searchKeyword, itemNum) {
        if (!window.location.pathname.includes('engineering_tools.php')) {
            let url = 'engineering_tools.php?tab=' + encodeURIComponent(suiteId);
            if (subTab) url += '&sub=' + encodeURIComponent(subTab);
            if (searchKeyword) url += '&q=' + encodeURIComponent(searchKeyword);
            if (itemNum) url += '&id=' + encodeURIComponent(itemNum);
            window.location.href = url;
            return;
        }

        const targetPill = document.querySelector('[onclick*="switchSuite(\'' + suiteId + '\'"]');
        switchSuite(suiteId, targetPill);

        if (suiteId === 'fiqh' && subTab) {
            switchFiqhSubTab(subTab);
            if (subTab === 'fatwas') {
                ensureFiqhLoaded(() => {
                    const input = document.getElementById('fiqh-search-input');
                    if (input && searchKeyword) {
                        input.value = searchKeyword;
                        if (typeof executeLiveFilter === 'function') executeLiveFilter();
                    }
                    setTimeout(() => {
                        const sec = document.getElementById('fiqh-sub-fatwas');
                        if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 150);
                });
            } else if (subTab === 'quran') {
                setTimeout(() => {
                    const input = document.getElementById('quran-search-input');
                    if (input && searchKeyword) {
                        input.value = searchKeyword;
                        if (typeof filterQuranVerses === 'function') filterQuranVerses();
                    }
                    const sec = document.getElementById('fiqh-sub-quran');
                    if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 150);
            } else if (subTab === 'hadiths') {
                setTimeout(() => {
                    const input = document.getElementById('hadith-search-input');
                    if (input && searchKeyword) {
                        input.value = searchKeyword;
                        if (typeof filterHadiths === 'function') filterHadiths();
                    }
                    const sec = document.getElementById('fiqh-sub-hadiths');
                    if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 150);
            } else if (subTab === 'maxims') {
                setTimeout(() => {
                    const input = document.getElementById('maxims-search-input');
                    if (input && searchKeyword) {
                        input.value = searchKeyword;
                        if (typeof filterMaxims === 'function') filterMaxims();
                    }
                    const sec = document.getElementById('fiqh-sub-maxims');
                    if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 150);
            }
        } else {
            setTimeout(() => {
                const targetSec = document.getElementById('suite-sec-' + suiteId);
                if (targetSec) targetSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        }
    };

    // ── Sacred Media Bar Toggle Controller ──
    window.toggleSacredMediaBar = function() {
        const wrap = document.getElementById('sacred-media-wrapper');
        const btn = document.getElementById('btn-toggle-media');
        const chevron = document.getElementById('media-chevron');
        if (!wrap) return;
        const isHidden = (wrap.style.display === 'none' || getComputedStyle(wrap).display === 'none');
        if (isHidden) {
            wrap.style.display = 'block';
            if (chevron) chevron.textContent = '▾';
            if (btn) btn.classList.add('active');
            try { localStorage.setItem('magaiees_tools_media_bar', 'open'); } catch(e) {}
        } else {
            wrap.style.display = 'none';
            if (chevron) chevron.textContent = '▴';
            if (btn) btn.classList.remove('active');
            try { localStorage.setItem('magaiees_tools_media_bar', 'closed'); } catch(e) {}
        }
    };

    // ════════════════════════════════════════════════════════════════
    // UNIVERSAL CALCULATOR ENGINES (STEEL, CONCRETE, SOLAR, FREIGHT,
    // UNITS, ZAKAT, FEASIBILITY, GRATUITY) + ERP LIVE SYNC + AI ADVISOR
    // ════════════════════════════════════════════════════════════════

    window._lastAppliedSteelPreset = '';

    window.calculateSteel = function() {
        const typeEl = document.getElementById('steel_product_type');
        const presetEl = document.getElementById('steel_preset_size');
        const wEl = document.getElementById('steel_width_mm');
        const hEl = document.getElementById('steel_height_mm');
        const tEl = document.getElementById('steel_thickness_mm');
        const lEl = document.getElementById('steel_length_m');
        const qtyEl = document.getElementById('steel_target_qty');
        const unitEl = document.getElementById('steel_target_unit');
        if (!wEl || !lEl) return;

        const preset = presetEl ? presetEl.value : 'custom';
        if (preset !== 'custom' && preset !== window._lastAppliedSteelPreset) {
            window._lastAppliedSteelPreset = preset;
            const presets = {
                'rebar_8mm':  { type: 'rebar', w: 8,  h: 8,  t: 8,   l: 12 },
                'rebar_10mm': { type: 'rebar', w: 10, h: 10, t: 10,  l: 12 },
                'rebar_12mm': { type: 'rebar', w: 12, h: 12, t: 12,  l: 12 },
                'rebar_16mm': { type: 'rebar', w: 16, h: 16, t: 16,  l: 12 },
                'rebar_20mm': { type: 'rebar', w: 20, h: 20, t: 20,  l: 12 },
                'rebar_25mm': { type: 'rebar', w: 25, h: 25, t: 25,  l: 12 },
                'rebar_32mm': { type: 'rebar', w: 32, h: 32, t: 32,  l: 12 },
                '4x8_1.5':    { type: 'pipe_rect', w: 80, h: 40, t: 1.5, l: 6 },
                '4x8_2.0':    { type: 'pipe_rect', w: 80, h: 40, t: 2.0, l: 6 },
                '2x4_1.5':    { type: 'pipe_rect', w: 40, h: 20, t: 1.5, l: 6 },
                '2x4_2.0':    { type: 'pipe_rect', w: 40, h: 20, t: 2.0, l: 6 },
                'zinc_0.3':   { type: 'sheet_zinc', w: 900, h: 1, t: 0.30, l: 3.0 },
                'zinc_0.4':   { type: 'sheet_zinc', w: 900, h: 1, t: 0.40, l: 3.0 }
            };
            if (presets[preset]) {
                if (typeEl) typeEl.value = presets[preset].type;
                wEl.value = presets[preset].w;
                if (hEl) hEl.value = presets[preset].h;
                if (tEl) tEl.value = presets[preset].t;
                lEl.value = presets[preset].l;
            }
        }

        const pType = typeEl ? typeEl.value : 'rebar';
        const w = Math.max(0.1, parseFloat(wEl.value) || 12);
        const h = Math.max(0.1, parseFloat(hEl ? hEl.value : w) || 12);
        const t = Math.max(0.05, parseFloat(tEl ? tEl.value : w) || 1.5);
        const len = Math.max(0.1, parseFloat(lEl.value) || 12);
        const targetQty = Math.max(0.01, parseFloat(qtyEl ? qtyEl.value : 1) || 1);
        const targetUnit = unitEl ? unitEl.value : 'ton';

        let weightPerPieceKg = 0;
        if (pType === 'rebar') {
            // ISO formula: D^2 / 162.2 * L
            weightPerPieceKg = ((w * w) / 162.2) * len;
        } else if (pType === 'pipe_rect') {
            // Rectangular hollow section: 2*(W+H - 2*t) * t * 0.00785 * L
            weightPerPieceKg = 2 * ((w + h) - 2 * t) * t * 0.00785 * len;
        } else if (pType === 'pipe_round') {
            // Round pipe: (D - t) * t * 0.02466 * L
            weightPerPieceKg = Math.max(0.1, (w - t)) * t * 0.02466 * len;
        } else if (pType === 'sheet_zinc') {
            // Sheet/Zinc: Width(m) * Length(m) * Thickness(mm) * 7.85 * 1.12 corrugation factor
            weightPerPieceKg = (w / 1000) * len * t * 7.85 * 1.12;
        } else {
            // Beam / Channel approximation
            weightPerPieceKg = ((w * t + 2 * h * t) * 0.00785) * len;
        }

        weightPerPieceKg = Math.max(0.05, weightPerPieceKg);
        const piecesPerTon = 1000 / weightPerPieceKg;

        let totalPcs = 0;
        let totalTons = 0;
        if (targetUnit === 'ton') {
            totalTons = targetQty;
            totalPcs = targetQty * piecesPerTon;
        } else {
            totalPcs = targetQty;
            totalTons = (targetQty * weightPerPieceKg) / 1000;
        }

        const elPcsTon = document.getElementById('res_pieces_per_ton');
        const elWPiece = document.getElementById('res_weight_per_piece');
        const elSummary = document.getElementById('res_total_summary');
        if (elPcsTon) elPcsTon.textContent = Math.round(piecesPerTon).toLocaleString('en-US');
        if (elWPiece) elWPiece.textContent = weightPerPieceKg.toFixed(2) + ' kg';
        if (elSummary) elSummary.textContent = Math.ceil(totalPcs).toLocaleString('en-US') + ' قطعة = ' + totalTons.toFixed(3) + ' طن';
    };

    window.calculateConcrete = function() {
        const type = (document.getElementById('concrete_type')?.value) || 'beam_slab';
        const area = Math.max(0, parseFloat(document.getElementById('concrete_area')?.value) || 0);
        const thickCm = Math.max(5, parseFloat(document.getElementById('concrete_thickness')?.value) || 15);
        const floors = Math.max(1, parseFloat(document.getElementById('concrete_floors')?.value) || 1);
        const rebarRatio = Math.max(40, parseFloat(document.getElementById('concrete_rebar_ratio')?.value) || 110);
        const wallArea = Math.max(0, parseFloat(document.getElementById('wall_area_m2')?.value) || 0);
        const tilesArea = Math.max(0, parseFloat(document.getElementById('tiles_area_m2')?.value) || 0);

        let mult = 1.25; // beam factor
        if (type === 'hordy') mult = 0.85;
        else if (type === 'flat_slab') mult = 1.15;
        else if (type === 'building_skeleton') mult = 2.15;

        const concreteM3 = area * (thickCm / 100) * floors * mult;
        const rebarTons = (concreteM3 * rebarRatio) / 1000;
        const cementBags = Math.ceil(concreteM3 * 7); // 7 bags (350kg) per m3
        const wallBlocks = Math.ceil(wallArea * 12.5 * floors); // 12.5 blocks per m2
        const tilesBoxes = Math.ceil((tilesArea * floors * 1.05) / 1.44);
        const tilesGlue = Math.ceil((tilesArea * floors) / 4.5);

        const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        setTxt('res_concrete_m3', concreteM3.toFixed(2) + ' m³');
        setTxt('res_rebar_tons', rebarTons.toFixed(2) + ' طن');
        setTxt('res_cement_bags', cementBags.toLocaleString('en-US') + ' كيس');
        setTxt('res_wall_blocks', wallBlocks.toLocaleString('en-US') + ' حبة');
        setTxt('res_tiles_boxes', tilesBoxes.toLocaleString('en-US') + ' كرتونة');
        setTxt('res_tiles_glue', tilesGlue.toLocaleString('en-US') + ' كيس');
    };

    window.toggleSolarMode = function() {
        const mode = document.getElementById('solar_sys_mode')?.value || 'home';
        const homeDiv = document.getElementById('solar_home_inputs');
        const pumpDiv = document.getElementById('solar_pump_inputs');
        if (mode === 'pump') {
            if (homeDiv) homeDiv.classList.add('hidden');
            if (pumpDiv) pumpDiv.classList.remove('hidden');
        } else {
            if (homeDiv) homeDiv.classList.remove('hidden');
            if (pumpDiv) pumpDiv.classList.add('hidden');
        }
        window.calculateSolar();
    };

    window.calculateSolar = function() {
        const mode = document.getElementById('solar_sys_mode')?.value || 'home';
        const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        if (mode === 'pump') {
            const hp = Math.max(1, parseFloat(document.getElementById('solar_pump_hp_val')?.value) || 10);
            const depth = Math.max(10, parseFloat(document.getElementById('solar_well_depth_m')?.value) || 70);
            const panelW = Math.max(100, parseFloat(document.getElementById('solar_pump_panel_w')?.value) || 550);
            const pumpKw = hp * 0.746;
            const vfdKw = Math.ceil(pumpKw * 1.35 * 10) / 10;
            const requiredPvW = pumpKw * (1.45 + (depth > 80 ? 0.15 : 0)) * 1000;
            const panels = Math.ceil(requiredPvW / panelW);
            setTxt('res_inverter_kw', vfdKw.toFixed(1) + ' kW VFD');
            setTxt('res_panel_count', panels.toLocaleString('en-US') + ' لوح (' + panelW + 'W)');
            setTxt('res_battery_ah', 'تشغيل نهاري مباشر (بدون بطاريات)');
        } else {
            const ac = Math.max(0, parseFloat(document.getElementById('solar_ac_count')?.value) || 0);
            const fridge = Math.max(0, parseFloat(document.getElementById('solar_fridge_count')?.value) || 0);
            const lightsW = Math.max(0, parseFloat(document.getElementById('solar_lights_watt')?.value) || 0);
            const nightH = Math.max(1, parseFloat(document.getElementById('solar_night_hours')?.value) || 8);
            const panelW = Math.max(100, parseFloat(document.getElementById('solar_panel_watt')?.value) || 550);
            const batType = document.getElementById('solar_bat_type')?.value || 'lithium';

            const peakLoadW = (ac * 1450) + (fridge * 250) + lightsW;
            const invKw = Math.max(3, Math.ceil((peakLoadW * 1.3) / 1000));
            const nightEnergyWh = ((ac * 1100 * 0.65) + (fridge * 160 * 0.7) + lightsW) * nightH;
            const dod = batType === 'lithium' ? 0.85 : 0.55;
            const batAh48V = Math.ceil(nightEnergyWh / (48 * dod));
            const totalDailyWh = nightEnergyWh * 1.45;
            const panels = Math.max(2, Math.ceil(totalDailyWh / (panelW * 5.2)));

            setTxt('res_inverter_kw', invKw + ' kW (48V)');
            setTxt('res_panel_count', panels + ' لوح (' + panelW + 'W)');
            setTxt('res_battery_ah', batAh48V + ' Ah / 48V (' + ((batAh48V * 48) / 1000).toFixed(1) + ' kWh)');
        }
    };

    window.calculateFreight = function() {
        const fob = Math.max(0, parseFloat(document.getElementById('freight_fob_usd')?.value) || 0);
        const sea = Math.max(0, parseFloat(document.getElementById('freight_sea_usd')?.value) || 0);
        const custPct = Math.max(0, parseFloat(document.getElementById('freight_customs_pct')?.value) || 0);
        const clearUsd = Math.max(0, parseFloat(document.getElementById('freight_clearance_usd')?.value) || 0);
        const inlandUsd = Math.max(0, parseFloat(document.getElementById('freight_inland_usd')?.value) || 0);
        const fx = Math.max(1, parseFloat(document.getElementById('freight_fx_rate')?.value) || 600);

        const cifUsd = fob + sea;
        const customsUsd = cifUsd * (custPct / 100);
        const totalLandedUsd = cifUsd + customsUsd + clearUsd + inlandUsd;
        const totalLandedSdg = totalLandedUsd * fx;
        const overheadPct = fob > 0 ? (((totalLandedUsd - fob) / fob) * 100) : 0;

        const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        setTxt('res_landed_usd', '$' + Math.round(totalLandedUsd).toLocaleString('en-US'));
        setTxt('res_landed_sdg', Math.round(totalLandedSdg).toLocaleString('en-US') + ' SDG');
        setTxt('res_overhead_pct', '%' + overheadPct.toFixed(1));
    };

    window.convertUnits = function(source) {
        const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = Number(val.toFixed(3)); };
        if (source === 'sqm' || source === 'feddan' || source === 'qirat') {
            let sqm = 0;
            if (source === 'sqm') sqm = parseFloat(document.getElementById('unit_sqm')?.value) || 0;
            else if (source === 'feddan') sqm = (parseFloat(document.getElementById('unit_feddan')?.value) || 0) * 4200;
            else if (source === 'qirat') sqm = (parseFloat(document.getElementById('unit_qirat')?.value) || 0) * 175;
            if (source !== 'sqm') setVal('unit_sqm', sqm);
            if (source !== 'feddan') setVal('unit_feddan', sqm / 4200);
            if (source !== 'qirat') setVal('unit_qirat', sqm / 175);
        } else if (source === 'inch' || source === 'mm' || source === 'foot') {
            let mm = 0;
            if (source === 'inch') mm = (parseFloat(document.getElementById('unit_inch')?.value) || 0) * 25.4;
            else if (source === 'mm') mm = parseFloat(document.getElementById('unit_mm')?.value) || 0;
            else if (source === 'foot') mm = (parseFloat(document.getElementById('unit_foot')?.value) || 0) * 304.8;
            if (source !== 'inch') setVal('unit_inch', mm / 25.4);
            if (source !== 'mm') setVal('unit_mm', mm);
            if (source !== 'foot') setVal('unit_foot', mm / 304.8);
        } else if (source === 'kg' || source === 'ton' || source === 'qantar') {
            let kg = 0;
            if (source === 'kg') kg = parseFloat(document.getElementById('unit_kg')?.value) || 0;
            else if (source === 'ton') kg = (parseFloat(document.getElementById('unit_ton')?.value) || 0) * 1000;
            else if (source === 'qantar') kg = (parseFloat(document.getElementById('unit_qantar')?.value) || 0) * 45.3592;
            if (source !== 'kg') setVal('unit_kg', kg);
            if (source !== 'ton') setVal('unit_ton', kg / 1000);
            if (source !== 'qantar') setVal('unit_qantar', kg / 45.3592);
        }
    };

    window.calculateZakat = function() {
        const inv = Math.max(0, parseFloat(document.getElementById('zakat_inventory_val')?.value) || 0);
        const cash = Math.max(0, parseFloat(document.getElementById('zakat_cash_val')?.value) || 0);
        const debtsIn = Math.max(0, parseFloat(document.getElementById('zakat_debts_in')?.value) || 0);
        const debtsOut = Math.max(0, parseFloat(document.getElementById('zakat_debts_out')?.value) || 0);
        const goldGram = Math.max(1, parseFloat(document.getElementById('gold_gram_price')?.value) || 80000);

        const nisabVal = 85 * goldGram;
        const pool = Math.max(0, (inv + cash + debtsIn) - debtsOut);
        const reachedNisab = pool >= nisabVal;
        const zakatDue = reachedNisab ? (pool * 0.025) : 0;

        const elPool = document.getElementById('res_zakat_pool');
        const elStatus = document.getElementById('res_nisab_status');
        const elDue = document.getElementById('res_zakat_due');

        if (elPool) elPool.textContent = Math.round(pool).toLocaleString('en-US') + ' SDG';
        if (elStatus) {
            elStatus.textContent = reachedNisab
                ? ('بلغ النصاب الشرعي (' + Math.round(nisabVal).toLocaleString('en-US') + ' SDG) ✓')
                : ('لم يبلغ النصاب الشرعي (' + Math.round(nisabVal).toLocaleString('en-US') + ' SDG) — لا تجب الزكاة');
            elStatus.className = reachedNisab ? 'text-xs text-emerald-300 font-bold mt-1 block' : 'text-xs text-amber-300 font-bold mt-1 block';
        }
        if (elDue) elDue.textContent = Math.round(zakatDue).toLocaleString('en-US') + ' SDG';
    };

    window.calculateFeasibility = function() {
        const fixed = Math.max(0, parseFloat(document.getElementById('fe_fixed_costs')?.value) || 0);
        const price = Math.max(0, parseFloat(document.getElementById('fe_unit_price')?.value) || 0);
        const cost = Math.max(0, parseFloat(document.getElementById('fe_unit_cost')?.value) || 0);
        const qty = Math.max(0, parseFloat(document.getElementById('fe_expected_qty')?.value) || 0);

        const contrib = price - cost;
        const beUnits = contrib > 0 ? Math.ceil(fixed / contrib) : 0;
        const beRev = beUnits * price;
        const netProfit = (contrib * qty) - fixed;
        const marginPct = price > 0 ? ((contrib / price) * 100) : 0;
        const markupPct = cost > 0 ? ((contrib / cost) * 100) : 0;

        const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        setTxt('res_breakeven_units', contrib > 0 ? (beUnits.toLocaleString('en-US') + ' قطعة') : 'سعر البيع أقل من التكلفة!');
        setTxt('res_breakeven_revenue', Math.round(beRev).toLocaleString('en-US') + ' SDG مبيعات للتعادل');
        setTxt('res_net_profit_month', (netProfit >= 0 ? '+' : '') + Math.round(netProfit).toLocaleString('en-US') + ' SDG');
        setTxt('res_margin_markup_info', 'هامش الربح: %' + marginPct.toFixed(1) + ' | الإضافة: %' + markupPct.toFixed(1));
    };

    window.calculateGratuity = function() {
        const salary = Math.max(0, parseFloat(document.getElementById('hr_basic_salary')?.value) || 0);
        const years = Math.max(0, parseFloat(document.getElementById('hr_service_years')?.value) || 0);
        const reason = document.getElementById('hr_end_reason')?.value || 'contract_end';
        const leaveDays = Math.max(0, parseFloat(document.getElementById('hr_leave_days')?.value) || 0);

        // First 5 years: half month per year; beyond 5 years: full month per year
        const first5 = Math.min(5, years) * (salary * 0.5);
        const after5 = Math.max(0, years - 5) * salary;
        let gratuity = first5 + after5;

        if (reason === 'resignation') {
            if (years < 2) gratuity = 0;
            else if (years < 5) gratuity *= (1 / 3);
            else if (years < 10) gratuity *= (2 / 3);
        }

        const leaveAmount = (salary / 30) * leaveDays;
        const totalDue = gratuity + leaveAmount;

        const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        setTxt('res_gratuity_amount', Math.round(gratuity).toLocaleString('en-US') + ' SDG');
        setTxt('res_leave_amount', Math.round(leaveAmount).toLocaleString('en-US') + ' SDG');
        setTxt('res_total_hr_due', Math.round(totalDue).toLocaleString('en-US') + ' SDG');
    };

    // ── Live ERP Sync into Engineering & Financial Calculators ──
    window.syncAllCalculatorsWithLiveERP = function(showToastFlag) {
        try {
            const items = JSON.parse(localStorage.getItem('erp_master_items_v1') || '[]');
            const customers = JSON.parse(localStorage.getItem('erp_master_customers_v1') || '[]');
            const suppliers = JSON.parse(localStorage.getItem('erp_master_suppliers_v1') || '[]');
            const safes = JSON.parse(localStorage.getItem('erp_master_safes_v1') || '[]');
            const banks = JSON.parse(localStorage.getItem('erp_master_banks_v1') || '[]');

            let invVal = 0;
            if (Array.isArray(items)) {
                items.forEach(it => {
                    let st = 0;
                    if (typeof it.stock === 'object' && it.stock !== null) {
                        st = Object.values(it.stock).reduce((a, b) => a + (parseFloat(b) || 0), 0);
                    } else {
                        st = parseFloat(it.stock ?? it.qty ?? 0) || 0;
                    }
                    const price = parseFloat(it.sell_price ?? it.price ?? it.cost ?? 0) || 0;
                    if (st > 0 && price > 0) invVal += (st * price);
                });
            }

            let cashVal = 0;
            [...(Array.isArray(safes) ? safes : []), ...(Array.isArray(banks) ? banks : [])].forEach(s => {
                cashVal += Math.max(0, parseFloat(s.balance ?? s.opening_balance ?? 0) || 0);
            });

            let recVal = 0;
            if (Array.isArray(customers)) {
                customers.forEach(c => { recVal += Math.max(0, parseFloat(c.balance ?? c.debit ?? 0) || 0); });
            }

            let payVal = 0;
            if (Array.isArray(suppliers)) {
                suppliers.forEach(s => { payVal += Math.max(0, parseFloat(s.balance ?? s.credit ?? 0) || 0); });
            }

            if (invVal > 0 && document.getElementById('zakat_inventory_val')) document.getElementById('zakat_inventory_val').value = Math.round(invVal);
            if (cashVal > 0 && document.getElementById('zakat_cash_val')) document.getElementById('zakat_cash_val').value = Math.round(cashVal);
            if (recVal > 0 && document.getElementById('zakat_debts_in')) document.getElementById('zakat_debts_in').value = Math.round(recVal);
            if (payVal > 0 && document.getElementById('zakat_debts_out')) document.getElementById('zakat_debts_out').value = Math.round(payVal);

            const fx = parseFloat(localStorage.getItem('magaiees_usd_rate') || localStorage.getItem('erp_usd_rate') || '0');
            if (fx > 100 && document.getElementById('freight_fx_rate')) {
                document.getElementById('freight_fx_rate').value = fx;
            }

            window.calculateSteel();
            window.calculateConcrete();
            window.calculateSolar();
            window.calculateFreight();
            window.calculateZakat();
            window.calculateFeasibility();
            window.calculateGratuity();

            if (showToastFlag && typeof window.showToast === 'function') {
                window.showToast('✅ تم سحب أرصدة المخازن والسيولة والذمم وسعر الصرف من النظام الحي بنجاح!');
            }
        } catch(e) {
            console.warn('ERP Sync warning:', e);
        }
    };

    // ── Post Zakat Accrual directly to Journal Vouchers ──
    window.postZakatToJournalEntry = function() {
        window.calculateZakat();
        const dueTxt = document.getElementById('res_zakat_due')?.textContent || '0';
        const dueVal = parseFloat(dueTxt.replace(/[^\d.]/g, '')) || 0;
        if (dueVal <= 0) {
            alert('⚠️ مقدار الزكاة الحالي (0 SDG). يرجى التحقق من بلوغ النصاب الشرعي أولاً.');
            return;
        }
        try {
            const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1') || '[]');
            const newJv = {
                id: 'JV-ZAKAT-' + Date.now().toString().slice(-6),
                voucher_no: 'JV-ZKT-' + new Date().toISOString().slice(0,10).replace(/-/g,''),
                date: new Date().toISOString().slice(0,10),
                description: 'إثبات استحقاق الزكاة الشرعية لعروض التجارة والسيولة (2.5% ربع العشر)',
                total_debit: Math.round(dueVal),
                total_credit: Math.round(dueVal),
                status: 'posted',
                source: 'engineering_tools_zakat',
                lines: [
                    { account_code: '5401', account_name: 'مصروف الزكاة الشرعية', debit: Math.round(dueVal), credit: 0, note: 'استحقاق زكاة الحول' },
                    { account_code: '2205', account_name: 'مخصص الزكاة الشرعية المستحقة', debit: 0, credit: Math.round(dueVal), note: 'مخصص زكاة واجب الصرف' }
                ]
            };
            jvs.unshift(newJv);
            localStorage.setItem('erp_journal_vouchers_v1', JSON.stringify(jvs));
            if (confirm('✅ تم إنشاء وترحيل قيد استحقاق الزكاة الشرعية بمبلغ (' + Math.round(dueVal).toLocaleString('en-US') + ' SDG) بنجاح!\n\nهل تود فتح شاشة الدفتر اليومي العام الآن لمراجعة القيد؟')) {
                window.location.href = 'journal.php';
            }
        } catch(e) {
            alert('حدث خطأ أثناء ترحيل القيد: ' + e.message);
        }
    };

    // ── Send Engineering Calculation directly to Sales Quotation / Invoice ──
    window.sendCalcToSalesOrPurchase = function(calcType, targetScreen) {
        let summary = '';
        if (calcType === 'steel') {
            summary = 'مقايسة حديد: ' + (document.getElementById('res_total_summary')?.textContent || '');
        } else if (calcType === 'concrete') {
            summary = 'مقايسة خرسانة وتشطيبات: خرسانة ' + (document.getElementById('res_concrete_m3')?.textContent || '') + ' + تسليح ' + (document.getElementById('res_rebar_tons')?.textContent || '');
        } else if (calcType === 'solar') {
            summary = 'منظومة طاقة شمسية: إنفرتر ' + (document.getElementById('res_inverter_kw')?.textContent || '') + ' + ألواح ' + (document.getElementById('res_panel_count')?.textContent || '');
        }
        try {
            localStorage.setItem('erp_pending_engineering_quote', JSON.stringify({
                type: calcType,
                summary: summary,
                timestamp: new Date().toISOString()
            }));
        } catch(e) {}
        window.location.href = (targetScreen === 'purchase' ? 'purchase.php' : 'sales-invoice.php') + '?from_tools=' + encodeURIComponent(calcType);
    };

    // ── Share Active Calculator Summary via WhatsApp ──
    window.shareActiveCalculatorWhatsApp = function() {
        const msg = [
            '📐 *تقرير مركز الأدوات الهندسية والمالية والشرعية — السجانة ERP*',
            '• حديد التسليح: ' + (document.getElementById('res_total_summary')?.textContent || '—'),
            '• الخرسانة والتشطيبات: ' + (document.getElementById('res_concrete_m3')?.textContent || '—') + ' | حديد: ' + (document.getElementById('res_rebar_tons')?.textContent || '—'),
            '• الطاقة الشمسية: ' + (document.getElementById('res_inverter_kw')?.textContent || '—') + ' | ' + (document.getElementById('res_panel_count')?.textContent || '—'),
            '• تكلفة الاستيراد الواصلة: ' + (document.getElementById('res_landed_usd')?.textContent || '—') + ' (' + (document.getElementById('res_landed_sdg')?.textContent || '—') + ')',
            '• الوعاء الزكوي والزكاة: ' + (document.getElementById('res_zakat_pool')?.textContent || '—') + ' ⬅️ الزكاة: ' + (document.getElementById('res_zakat_due')?.textContent || '—'),
            '• نقطة التعادل وصافي الربح: ' + (document.getElementById('res_breakeven_units')?.textContent || '—') + ' | ربح متوقع: ' + (document.getElementById('res_net_profit_month')?.textContent || '—')
        ].join('\n');
        window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
    };

    // ── AI Predictive Advisor Modal for Engineering, Financial & Sharia Suites ──
    window.openToolsAIPredictiveModal = function() {
        window.syncAllCalculatorsWithLiveERP(false);
        const existing = document.getElementById('tools-ai-predictive-modal');
        if (existing) existing.remove();

        const zakatPool = document.getElementById('res_zakat_pool')?.textContent || '0 SDG';
        const zakatDue = document.getElementById('res_zakat_due')?.textContent || '0 SDG';
        const breakEven = document.getElementById('res_breakeven_units')?.textContent || '0 قطعة';
        const netProfit = document.getElementById('res_net_profit_month')?.textContent || '0 SDG';
        const landedPct = document.getElementById('res_overhead_pct')?.textContent || '%0';

        const modal = document.createElement('div');
        modal.id = 'tools-ai-predictive-modal';
        modal.style.cssText = 'position:fixed;inset:0;z-index:99999999;background:rgba(2,6,23,0.88);backdrop-filter:blur(14px);display:flex;align-items:center;justify-content:center;padding:16px;';
        modal.innerHTML = `
            <div dir="rtl" style="width:100%;max-width:780px;background:linear-gradient(145deg,#0f172a,#020617);border:1.5px solid rgba(168,85,247,0.5);border-radius:24px;box-shadow:0 25px 60px rgba(0,0,0,0.9);overflow:hidden;color:#f8fafc;font-family:'Cairo',sans-serif;">
                <div style="padding:16px 22px;background:linear-gradient(90deg,rgba(88,28,135,0.45),rgba(15,23,42,0.95));border-bottom:1px solid rgba(168,85,247,0.3);display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:24px;">🤖</span>
                        <div>
                            <h3 style="margin:0;font-size:17px;font-weight:900;color:#e9d5ff;">المستشار الهندسي والمالي والشرعي التنبؤي (AI Advisor 360°)</h3>
                            <p style="margin:0;font-size:11px;color:#cbd5e1;">تحليل تنبؤي متكامل يربط الحاسبات الهندسية والزكاة والجدوى والمطابقة ببيانات السجانة الحية</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('tools-ai-predictive-modal').remove()" style="background:rgba(244,63,94,0.2);color:#fda4af;border:1px solid rgba(244,63,94,0.4);border-radius:10px;padding:4px 12px;font-weight:900;cursor:pointer;">✕ إغلاق</button>
                </div>
                <div style="padding:20px;max-height:78vh;overflow-y:auto;display:flex;flex-direction:column;gap:14px;">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:10px;">
                        <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);border-radius:14px;padding:12px;">
                            <div style="font-size:11px;color:#6ee7b7;font-weight:700;">⚖️ الوعاء الزكوي الحي</div>
                            <div style="font-size:16px;font-weight:900;color:#fff;margin-top:4px;">${zakatPool}</div>
                            <div style="font-size:11px;color:#34d399;margin-top:2px;">الزكاة: ${zakatDue}</div>
                        </div>
                        <div style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);border-radius:14px;padding:12px;">
                            <div style="font-size:11px;color:#fcd34d;font-weight:700;">🎯 نقطة التعادل الشهرية</div>
                            <div style="font-size:16px;font-weight:900;color:#fff;margin-top:4px;">${breakEven}</div>
                            <div style="font-size:11px;color:#fbbf24;margin-top:2px;">صافي الربح: ${netProfit}</div>
                        </div>
                        <div style="background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.3);border-radius:14px;padding:12px;">
                            <div style="font-size:11px;color:#7dd3fc;font-weight:700;">🚢 معامل تكلفة الاستيراد</div>
                            <div style="font-size:16px;font-weight:900;color:#fff;margin-top:4px;">${landedPct} فوق FOB</div>
                            <div style="font-size:11px;color:#38bdf8;margin-top:2px;">مربوط بسعر الصرف الحي</div>
                        </div>
                    </div>
                    <div style="background:rgba(15,23,42,0.85);border:1px solid rgba(148,163,184,0.25);border-radius:16px;padding:14px;font-size:12.5px;line-height:1.8;">
                        <div style="font-weight:900;color:#c084fc;margin-bottom:6px;">💡 توصيات الذكاء الاصطناعي التنبؤية (AI Actionable Insights):</div>
                        <ul style="margin:0;padding-right:18px;color:#e2e8f0;">
                            <li><strong>تسعير الحديد ومواد البناء:</strong> مع نسبة مصاريف الاستيراد الحالية (${landedPct})، يُوصى بتحديث هامش الربح في <a href="master-data.php" style="color:#38bdf8;text-decoration:underline;">البيانات الأساسية</a> بحيث لا يقل عن 18% لتغطية تذبذب العملة.</li>
                            <li><strong>الحوكمة الشرعية والزكوية:</strong> تم ربط حاسبة الزكاة آلياً بمخزون البضاعة والسيولة والذمم؛ يمكنك ترحيل قيد استحقاق الزكاة (${zakatDue}) مباشرة إلى <a href="journal.php" style="color:#34d399;text-decoration:underline;">الدفتر اليومي العام</a> بضغطة زر.</li>
                            <li><strong>المطابقة الذكية والرقابة:</strong> استخدم قسم <strong>المطابقة الذكية للإكسل</strong> مع <a href="alarm.php" style="color:#f43f5e;text-decoration:underline;">الحارس الأمين للتنبيهات</a> لكشف أي فروقات بين كشوفات البنوك والدفاتر.</li>
                        </ul>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;">
                        <button onclick="window.syncAllCalculatorsWithLiveERP(true);document.getElementById('tools-ai-predictive-modal').remove();" style="padding:8px 14px;border-radius:12px;background:rgba(16,185,129,0.25);color:#a7f3d0;border:1px solid rgba(16,185,129,0.5);font-size:12px;font-weight:900;cursor:pointer;">⚡ تحديث الحسابات من النظام الحي</button>
                        <button onclick="window.postZakatToJournalEntry()" style="padding:8px 14px;border-radius:12px;background:rgba(56,189,248,0.25);color:#bae6fd;border:1px solid rgba(56,189,248,0.5);font-size:12px;font-weight:900;cursor:pointer;">📒 ترحيل قيد الزكاة لليومية</button>
                        <button onclick="document.getElementById('tools-ai-predictive-modal').remove();if(typeof toggleAIChat==='function')toggleAIChat();" style="padding:8px 14px;border-radius:12px;background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;border:none;font-size:12px;font-weight:900;cursor:pointer;">💬 فتح المساعد التفاعلي الشامل</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    };

    document.addEventListener('DOMContentLoaded', function() {
        try {
            if (localStorage.getItem('magaiees_tools_media_bar') === 'closed') {
                const wrap = document.getElementById('sacred-media-wrapper');
                const chevron = document.getElementById('media-chevron');
                const btn = document.getElementById('btn-toggle-media');
                if (wrap) wrap.style.display = 'none';
                if (chevron) chevron.textContent = '▴';
                if (btn) btn.classList.remove('active');
            }
        } catch(e) {}

        // Initialize all 8 engineering & financial calculators immediately on page load
        setTimeout(function() {
            window.syncAllCalculatorsWithLiveERP(false);
            const params = new URLSearchParams(window.location.search);
            if (params.get('action') === 'chatbot') {
                setTimeout(function() {
                    if (typeof window.toggleAIChat === 'function') window.toggleAIChat();
                }, 400);
            }
        }, 120);
    });

