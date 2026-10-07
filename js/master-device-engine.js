/**
 * ═══════════════════════════════════════════════════════════════════════════
 * 🌐 MAGAIEES AI MASTER HARDWARE & SCREEN ADAPTIVE ENGINE 2026
 * ═══════════════════════════════════════════════════════════════════════════
 * محرك الكشف والتكيف الذكي الشامل للعتاد ومقاسات الشاشات لجميع الأجهزة:
 * 1. كمبيوتر مكتبي (Desktop): شاشات عادية، شاشات فائقة العرض Ultrawide (21:9 / 32:9)، شاشات 2K و 4K.
 * 2. كمبيوتر محمول (Laptop): لابتوبات 13" و 14" و 15.6" و 16" وماك بوك ريتينا بمختلف المقاسات.
 * 3. الأجهزة اللوحية (Tablets): تابلت هواوي MatePad، تابلت هونر Pad، تابلت سامسونج Galaxy Tab، آبل iPad Pro / Air / Mini.
 * 4. الهواتف الذكية (Smartphones):
 *    - آيفون: آيفون برو ماكس (Pro Max / Plus)، آيفون عادي / برو، آيفون X / ميني، آيفون SE / كلاسيك.
 *    - هواوي: سلسلة Mate المنحنية (Mate 20 / 30 / 40 / 50 / 60 / 70)، سلسلة P و Pura الفوتوغرافية (P20 / 30 / 40 / 50 / 60 / Pura 70).
 *    - هونر: سلسلة Magic الشلالية Quad-Curved 120Hz، سلسلة X.
 *    - سامسونج: سلسلة Galaxy Ultra العريضة، سلسلة Galaxy Fold القابلة للطي، سلسلة Galaxy Flip، وسلسلة S و A.
 *    - سوني: سلسلة Sony Xperia ذات الشاشات السينمائية الطولية الفائقة بنسبة (21:9 CinemaWide).
 *    - موتورولا: هواتف Moto Edge المنحنية، Moto Razr القابلة للطي، وسلسلة Moto G.
 *    - شاومي، بيكسل، ون بلس، وجميع الهواتف الأخرى.
 * 5. التكيف اللحظي: حقن متغيرات CSS، حساب Safe Areas للـ Dynamic Island والنوتش والحواف المنحنية،
 *    وتوفير المايك الصوتي الذكي (Voice Mic) ودعم تشغيل أي رابط ماستر.
 * ═══════════════════════════════════════════════════════════════════════════
 */

(function (window, document) {
    'use strict';

    // ─────────────────────────────────────────────────────────────────────────────
    // 1. الدالة الأساسية لتشخيص العتاد والشاشة بالكامل (Master Hardware Profiler)
    // ─────────────────────────────────────────────────────────────────────────────
    function detectMasterHardware() {
        const ua = navigator.userAgent || '';
        const platform = navigator.platform || '';
        const vendor = navigator.vendor || '';
        const width = window.innerWidth || document.documentElement.clientWidth || screen.width;
        const height = window.innerHeight || document.documentElement.clientHeight || screen.height;
        const screenW = screen.width || width;
        const screenH = screen.height || height;
        const dpr = window.devicePixelRatio || 1;
        const minDim = Math.min(width, height);
        const maxDim = Math.max(width, height);
        const aspectRatio = (maxDim / (minDim || 1));
        const isLandscape = width > height;

        // التحقق من شاشات اللمس والمؤشر
        const isTouch = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0);
        const hasCoarsePointer = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
        const hasHover = window.matchMedia && window.matchMedia('(hover: hover)').matches;

        // التحقق من دعم هوامش الحماية (Safe Areas) للنوتش والجزيرة الديناميكية
        const hasSafeAreas = (typeof window.CSS !== 'undefined' && typeof window.CSS.supports === 'function' && window.CSS.supports('padding-top: env(safe-area-inset-top)'));

        // فحص كرت الشاشة ومعالج الرسوميات (WebGL Unmasked Renderer)
        let gpuRenderer = '';
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (gl) {
                const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                if (debugInfo) {
                    gpuRenderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) || '';
                }
            }
        } catch (e) {
            gpuRenderer = '';
        }

        // الكشف عن تفضيل العرض المحفوظ يدوياً
        const userViewPref = (typeof window.localStorage !== 'undefined' && window.localStorage) ? (window.localStorage.getItem('magaiees_view_preference') || '') : '';

        // الخصائص الافتراضية
        let category = 'mobile';      // 'desktop' | 'laptop' | 'tablet' | 'mobile' | 'foldable'
        let brand = 'android';         // 'apple' | 'samsung' | 'huawei' | 'honor' | 'sony' | 'motorola' | 'xiaomi' | 'google' | 'desktop'
        let brandName = 'هاتف ذكي';
        let modelName = 'جهاز ذكي متكيف';
        let screenType = 'شاشة لمس ذكية';
        let aspectDesc = 'قياسي';
        let icon = '📱';
        let color = '#38bdf8';
        let safeTop = '0px';
        let safeBottom = '16px';
        let safeLeft = '0px';
        let safeRight = '0px';
        let layoutMode = 'mobile';    // 'desktop' | 'tablet' | 'mobile'
        let isUltrawide = false;
        let isDynamicIsland = false;
        let isCurvedScreen = false;
        let isFoldable = false;
        let refreshRateEstimate = '60Hz - 120Hz';

        // ═════════════════════════════════════════════════════════════════════
        // الفئة الأولى: أجهزة التابلت والأجهزة اللوحية (Tablets & iPads)
        // ═════════════════════════════════════════════════════════════════════
        const isAppleTablet = /iPad/i.test(ua) || (isTouch && /Macintosh/i.test(ua) && navigator.maxTouchPoints > 1);
        const isAndroidTablet = (/Android/i.test(ua) && !/Mobile/i.test(ua)) || /Tablet/i.test(ua);
        const isTabletSize = isTouch && (minDim >= 600 && maxDim <= 1366 && !/iPhone/i.test(ua) && !/SM-(F9|F7)/i.test(ua));

        // الكشف الخاص بتابلت هواوي MatePad
        const isHuaweiTablet = isAndroidTablet && (/HUAWEI|HarmonyOS|HuaweiBrowser/i.test(ua) || /BAH|AGS|MRX|WGR|GOT|DBY/i.test(ua));
        // الكشف الخاص بتابلت هونر Pad
        const isHonorTablet = isAndroidTablet && (/HONOR|MagicOS/i.test(ua) || /ROD|HEY/i.test(ua));
        // الكشف الخاص بتابلت سامسونج Galaxy Tab
        const isSamsungTablet = isAndroidTablet && (/SamsungBrowser|SAMSUNG/i.test(ua) || /SM-T|SM-X/i.test(ua));

        if (isAppleTablet || isAndroidTablet || isTabletSize) {
            category = 'tablet';
            layoutMode = 'tablet';
            safeTop = '20px';
            safeBottom = '20px';

            if (isAppleTablet) {
                brand = 'apple';
                brandName = 'آبل (Apple)';
                if (maxDim >= 1300) {
                    modelName = 'آيباد برو 12.9 / 13 بوصة (iPad Pro 12.9")';
                    screenType = 'شاشة Liquid Retina XDR (120Hz ProMotion)';
                } else if (maxDim >= 1150) {
                    modelName = 'آيباد برو 11 / آير (iPad Pro 11" / Air)';
                    screenType = 'شاشة Liquid Retina فائقة الوضوح';
                } else if (minDim <= 750) {
                    modelName = 'آيباد ميني (Apple iPad Mini)';
                    screenType = 'شاشة Liquid Retina مدمجة وخفيفة';
                } else {
                    modelName = 'آبل آيباد لوحي (Apple iPad)';
                    screenType = 'شاشة Retina لوحية متكيفة';
                }
                icon = '📟';
                color = '#c084fc';
                refreshRateEstimate = 'ProMotion 120Hz';
            } else if (isHuaweiTablet) {
                brand = 'huawei';
                brandName = 'هواوي (Huawei)';
                modelName = 'تابلت هواوي ميت باد (Huawei MatePad Pro)';
                screenType = 'شاشة OLED لوحية بنسبة 16:10 / 3:2';
                icon = '📟';
                color = '#f43f5e';
                refreshRateEstimate = '120Hz OLED';
            } else if (isHonorTablet) {
                brand = 'honor';
                brandName = 'هونر (Honor)';
                modelName = 'تابلت هونر باد (Honor Pad)';
                screenType = 'شاشة لوحية عريضة بدقة 2K';
                icon = '📟';
                color = '#818cf8';
                refreshRateEstimate = '120Hz Eye Comfort';
            } else if (isSamsungTablet) {
                brand = 'samsung';
                brandName = 'سامسونج (Samsung)';
                if (maxDim >= 1400 || /SM-X9/i.test(ua)) {
                    modelName = 'سامسونج جالكسي تاب ألترا (Galaxy Tab S Ultra 14.6")';
                    screenType = 'شاشة عملاقة Dynamic AMOLED 2X (14.6 بوصة)';
                } else {
                    modelName = 'سامسونج جالكسي تاب (Galaxy Tab S / A)';
                    screenType = 'شاشة لوحية متقدمة Super AMOLED';
                }
                icon = '📟';
                color = '#38bdf8';
                refreshRateEstimate = 'Super AMOLED 120Hz';
            } else {
                brand = 'android';
                brandName = 'أندرويد لوحي';
                modelName = 'جهاز لوحي ذكي (Android Tablet)';
                screenType = 'شاشة لوحية أندرويد متكيفة';
                icon = '📟';
                color = '#10b981';
            }
            aspectDesc = 'نسبة لوحية متوازنة (4:3 / 16:10)';
        }

        // ═════════════════════════════════════════════════════════════════════
        // الفئة الثانية: الكمبيوتر المكتبي واللابتوب (Desktop & Laptop)
        // ═════════════════════════════════════════════════════════════════════
        else if (!isTouch && (width >= 1024 || /Windows NT|Macintosh|X11; Linux x86_64|CrOS/i.test(ua))) {
            layoutMode = 'desktop';
            safeTop = '0px';
            safeBottom = '0px';

            // التمييز بين اللابتوب المحمول والكمبيوتر المكتبي الشامل
            // اللابتوبات عادة تكون دقتها 1366x768 أو 1440x900 أو 1920x1080 بشاشات 13 إلى 16 بوصة
            // الكمبيوتر المكتبي يتميز بشاشات أكبر (2K، 4K، أو شاشات فائقة العرض Ultrawide)
            const isUltrawideRatio = aspectRatio >= 2.05;

            if (isUltrawideRatio) {
                category = 'desktop';
                isUltrawide = true;
                brand = 'desktop';
                brandName = 'كمبيوتر مكتبي';
                modelName = 'كمبيوتر بشاشة فائقة العرض (Ultrawide 21:9 / 32:9)';
                screenType = 'شاشة بانورامية عريضة للمحترفين';
                aspectDesc = 'فائقة العرض (21:9 / 32:9)';
                icon = '🖥️';
                color = '#a855f7';
            } else if (screenW >= 2560 || screenH >= 1440 || dpr >= 2.5) {
                // شاشة ريتينا ماك بوك أو شاشة ديسكتوب 2K / 4K
                if (/Macintosh/i.test(ua)) {
                    category = 'laptop';
                    brand = 'apple';
                    brandName = 'آبل ماك بوك';
                    modelName = 'ماك بوك برو / آير (Apple MacBook Retina)';
                    screenType = 'شاشة Liquid Retina متقدمة الألوان (P3 TrueTone)';
                    icon = '💻';
                    color = '#f8fafc';
                } else if (screenW >= 3400) {
                    category = 'desktop';
                    brand = 'desktop';
                    brandName = 'كمبيوتر مكتبي 4K';
                    modelName = 'كمبيوتر عالي الدقة (4K UHD Desktop)';
                    screenType = 'شاشة فائقة الدقة 4K (3840×2160)';
                    icon = '🖥️';
                    color = '#60a5fa';
                } else {
                    category = 'desktop';
                    brand = 'desktop';
                    brandName = 'كمبيوتر مكتبي 2K';
                    modelName = 'كمبيوتر مكتبي 2K QHD (2560×1440)';
                    screenType = 'شاشة QHD احترافية واسعة النطاق';
                    icon = '🖥️';
                    color = '#38bdf8';
                }
                aspectDesc = '16:9 / 16:10 عالي الدقة';
            } else if (width <= 1440 || (screenW <= 1600 && screenH <= 900)) {
                // لابتوب مدمج 13 إلى 14 بوصة (1366x768 أو 1440x900)
                category = 'laptop';
                brand = 'desktop';
                brandName = 'لابتوب محمول';
                modelName = 'لابتوب مدمج 13 - 14 بوصة (Compact Laptop)';
                screenType = 'شاشة كمبيوتر محمول مدمجة (HD / WXGA)';
                aspectDesc = '16:9 قياسي مدمج';
                icon = '💻';
                color = '#38bdf8';
            } else {
                // لابتوب أو كمبيوتر Full HD قياسي (1920x1080 15.6 بوصة أو شاشة مكتبية)
                category = 'laptop';
                brand = 'desktop';
                brandName = 'لابتوب / كمبيوتر';
                modelName = 'لابتوب 15.6 بوصة / كمبيوتر Full HD';
                screenType = 'شاشة كاملة الوضوح 1080p (Full HD 1920×1080)';
                aspectDesc = '16:9 عريض متزن';
                icon = '💻';
                color = '#0ea5e9';
            }
        }

        // ═════════════════════════════════════════════════════════════════════
        // الفئة الثالثة: الهواتف الذكية (Smartphones) - تفصيل دقيق وشامل
        // ═════════════════════════════════════════════════════════════════════
        else {
            category = 'mobile';
            layoutMode = 'mobile';

            // 1. آبل آيفون (Apple iPhone) - تفريق الموديلات
            if (/iPhone/i.test(ua)) {
                brand = 'apple';
                brandName = 'آبل (Apple)';
                color = '#f8fafc';
                icon = '🍎';

                // تمييز آيفون برو ماكس / بلس (الشاشات الكبيرة 6.7 إلى 6.9 بوصة)
                // عرض الشاشة المنطقي: 428pt أو 430pt أو 440pt
                if (width >= 428 || height >= 926 || (screenW >= 428 && screenH >= 926)) {
                    modelName = 'آيفون برو ماكس / بلس (iPhone Pro Max / Plus)';
                    screenType = 'شاشة Super Retina XDR مع Dynamic Island (6.7" - 6.9")';
                    isDynamicIsland = true;
                    safeTop = '54px';
                    safeBottom = '34px';
                    refreshRateEstimate = 'ProMotion 120Hz';
                }
                // تمييز آيفون العادي والبرو (6.1 إلى 6.3 بوصة: عرض 390pt إلى 393pt)
                else if (width >= 390 || height >= 844 || (screenW >= 390 && screenH >= 844)) {
                    modelName = 'آيفون برو / القياسي (iPhone Standard / Pro)';
                    screenType = 'شاشة Super Retina XDR دقيقة ومريحة (6.1" - 6.3")';
                    isDynamicIsland = (height >= 850);
                    safeTop = isDynamicIsland ? '50px' : '44px';
                    safeBottom = '34px';
                    refreshRateEstimate = 'Retina 60Hz - 120Hz';
                }
                // تمييز آيفون X / XS / 11 Pro / ميني (عرض 375pt بارتفاع 812pt مع نوتش)
                else if (height >= 780 || screenH >= 780) {
                    modelName = 'آيفون X / ميني (iPhone X / 11 Pro / Mini)';
                    screenType = 'شاشة OLED Retina مع نوتش كلاسيكي (5.4" - 5.8")';
                    safeTop = '44px';
                    safeBottom = '34px';
                    refreshRateEstimate = 'Retina 60Hz';
                }
                // آيفون SE والشاشات الكلاسيكية 16:9 بزر الهوم
                else {
                    modelName = 'آيفون كلاسيكي / SE (iPhone SE)';
                    screenType = 'شاشة Retina HD كلاسيكية بزر الرئيسية (4.7")';
                    safeTop = '20px';
                    safeBottom = '10px';
                    refreshRateEstimate = '60Hz';
                }
                aspectDesc = '19.5:9 آيفون فائق النقاء';
            }

            // 2. سوني إكسبيريا (Sony Xperia) - الشاشات السينمائية الطولية 21:9
            else if (/Xperia|Sony|\b(SO-|SOG|XQ-)/i.test(ua)) {
                brand = 'sony';
                brandName = 'سوني (Sony)';
                modelName = 'سوني إكسبيريا (Sony Xperia 1 / 5 / 10)';
                screenType = 'شاشة سينمائية فائقة الطول (21:9 CinemaWide 4K/FHD HDR OLED)';
                aspectDesc = 'نسبة سينمائية حصرية (21:9 CinemaWide)';
                icon = '🎬';
                color = '#38bdf8';
                safeTop = '24px';
                safeBottom = '18px';
                refreshRateEstimate = '120Hz OLED Cinema';
            }

            // 3. هواوي (Huawei) - تمييز فئات Mate المنحنية و P الفوتوغرافية
            else if (/HUAWEI|HarmonyOS|HuaweiBrowser|HMSCore|\b(AL00|TL00|HMA|VOG|ELE|NOH|NOP|BLA|LYA|POT|TAS|ANA|ELS|JAD|ALN|BRA)-/i.test(ua)) {
                brand = 'huawei';
                brandName = 'هواوي (Huawei)';
                color = '#f43f5e';
                icon = '🌺';
                isCurvedScreen = true;

                // فحص سلسلة ميت (Mate 20 / 30 / 40 / 50 / 60 / 70)
                // أكواد مثل: HMA (Mate 20), LYA (Mate 20 Pro), TAS (Mate 30), NOH (Mate 40), ALN (Mate 60)
                if (/\b(HMA|LYA|TAS|ANA|NOH|NOP|ALN|BRA)\b/i.test(ua) || /Mate/i.test(ua)) {
                    modelName = 'هواوي ميت فلاجشيب (Huawei Mate Series)';
                    screenType = 'شاشة شلالية فائقة الانحناء (Curved Waterfall OLED 120Hz)';
                    safeTop = '32px';
                    safeBottom = '20px';
                    refreshRateEstimate = '120Hz LTPO OLED';
                }
                // فحص سلسلة بي وبورا (P20 / 30 / 40 / 50 / 60 / Pura 70)
                // أكواد مثل: EML (P20), CLT (P20 Pro), VOG (P30 Pro), ELE (P30), ELS (P40 Pro), JAD (P50 Pro)
                else if (/\b(EML|CLT|VOG|ELE|ELS|JAD)\b/i.test(ua) || /Pura|\bP[0-9]{2}\b/i.test(ua)) {
                    modelName = 'هواوي بي / بورا (Huawei P / Pura Series)';
                    screenType = 'شاشة تصوير احترافية منحنية (Quad-Curve X-True OLED)';
                    safeTop = '28px';
                    safeBottom = '18px';
                    refreshRateEstimate = '120Hz OLED';
                } else {
                    modelName = 'هواوي سمارت (Huawei Smartphone)';
                    screenType = 'شاشة OLED مريحة للعين مع HarmonyOS';
                    safeTop = '24px';
                    safeBottom = '16px';
                    refreshRateEstimate = '90Hz - 120Hz';
                }
                aspectDesc = '19.8:9 منحنية الأطراف';
            }

            // 4. هونر (Honor) - شاشات ماجيك الرباعية الانحناء
            else if (/HONOR|MagicOS|MagicUI|\b(HNY|ANY|NTH|CRT|RMO|PGT|FRI|ALI|BND|ELZ|BVX)-/i.test(ua)) {
                brand = 'honor';
                brandName = 'هونر (Honor)';
                modelName = 'هونر ماجيك الذكي (Honor Magic / X Series)';
                screenType = 'شاشة شلالية عائمة منحنية الأركان (Quad-Curved Floating 120Hz)';
                aspectDesc = '19.5:9 مع تعتيم عالي التردد 4320Hz PWM';
                icon = '⚡';
                color = '#818cf8';
                isCurvedScreen = true;
                safeTop = '28px';
                safeBottom = '18px';
                refreshRateEstimate = 'Quad-Curved 120Hz';
            }

            // 5. سامسونج جالكسي (Samsung Galaxy) - تمييز ألترا وفولد وفليب
            else if (/SamsungBrowser|SAMSUNG|\b(SM|GT|SCH|SPH|SGH)-[A-Z0-9]+/i.test(ua)) {
                brand = 'samsung';
                brandName = 'سامسونج (Samsung)';
                color = '#38bdf8';
                icon = '🌌';

                // فحص سامسونج فولد (Galaxy Z Fold) - شاشة طي
                if (/SM-(F9[0-9]{2})/i.test(ua) || (isTouch && minDim >= 580 && minDim <= 720 && aspectRatio <= 1.4)) {
                    category = 'foldable';
                    isFoldable = true;
                    modelName = 'سامسونج جالكسي فولد (Galaxy Z Foldable)';
                    screenType = 'شاشة قابلة للطي مرنة (Dynamic AMOLED 2X Infinity Flex)';
                    aspectDesc = 'شاشة طي ديناميكية (مرنة قابلة للفتح)';
                    safeTop = '26px';
                    safeBottom = '18px';
                    refreshRateEstimate = '120Hz Adaptive';
                }
                // فحص سامسونج ألترا (Galaxy S21/S22/S23/S24/S25 Ultra)
                else if (/SM-(S9[0-9]{2})/i.test(ua) || (width >= 410 && dpr >= 3)) {
                    modelName = 'سامسونج جالكسي ألترا (Galaxy S-Series Ultra)';
                    screenType = 'شاشة Dynamic AMOLED 2X عريضة شديدة السطوع مع دعم القلم S-Pen';
                    aspectDesc = '19.5:9 مع حواف حادة وألوان سينمائية';
                    safeTop = '30px';
                    safeBottom = '18px';
                    refreshRateEstimate = 'Dynamic AMOLED 120Hz';
                }
                // فحص سامسونج فليب (Galaxy Z Flip)
                else if (/SM-(F7[0-9]{2})/i.test(ua)) {
                    category = 'foldable';
                    isFoldable = true;
                    modelName = 'سامسونج جالكسي فليب (Galaxy Z Flip)';
                    screenType = 'شاشة طويلة قابلة للطي عمودياً (22:9 Foldable)';
                    aspectDesc = 'شاشة صدفية مرنة';
                    safeTop = '26px';
                    safeBottom = '18px';
                    refreshRateEstimate = '120Hz Flex';
                } else {
                    modelName = 'سامسونج جالكسي (Samsung Galaxy S / A Series)';
                    screenType = 'شاشة Super AMOLED فائقة الوضوح مع One UI';
                    aspectDesc = '19.5:9 ناعمة الإيماءات';
                    safeTop = '24px';
                    safeBottom = '16px';
                    refreshRateEstimate = 'Super AMOLED 120Hz';
                }
            }

            // 6. موتورولا (Motorola) - شاشات إيدج الشديدة الانحناء وريزر
            else if (/moto|motorola|XT[0-9]{4}|edge|razr/i.test(ua)) {
                brand = 'motorola';
                brandName = 'موتورولا (Motorola)';
                if (/razr/i.test(ua)) {
                    category = 'foldable';
                    isFoldable = true;
                    modelName = 'موتورولا ريزر (Motorola Razr Foldable)';
                    screenType = 'شاشة قابلة للطي pOLED (165Hz Foldable)';
                } else if (/edge/i.test(ua)) {
                    modelName = 'موتورولا إيدج (Motorola Edge Curved)';
                    screenType = 'شاشة منحنية الأطراف بدون حواف (Endless Edge 144Hz-165Hz)';
                    isCurvedScreen = true;
                } else {
                    modelName = 'موتورولا موتو (Motorola Moto Series)';
                    screenType = 'شاشة أندرويد نقية سريعة الاستجابة';
                }
                aspectDesc = '19.5:9 / 20:9 فائقة السرعة';
                icon = 'Ⓜ️';
                color = '#06b6d4';
                safeTop = '26px';
                safeBottom = '18px';
                refreshRateEstimate = '144Hz - 165Hz pOLED';
            }

            // 7. شاومي وريدمي وبوكو (Xiaomi / Redmi / POCO)
            else if (/Xiaomi|Redmi|POCO|MiuiBrowser|HyperOS/i.test(ua)) {
                brand = 'xiaomi';
                brandName = 'شاومي (Xiaomi)';
                modelName = 'شاومي / ريدمي الذكي (Xiaomi HyperOS)';
                screenType = 'شاشة AMOLED بدقة 1.5K / 2K مع Dolby Vision';
                aspectDesc = '20:9 مع استجابة لمس 480Hz';
                icon = '⚡';
                color = '#f97316';
                safeTop = '24px';
                safeBottom = '16px';
                refreshRateEstimate = 'AdaptiveSync 120Hz';
            }

            // 8. جوجل بيكسل (Google Pixel)
            else if (/Pixel/i.test(ua)) {
                brand = 'google';
                brandName = 'جوجل (Google)';
                modelName = 'جوجل بيكسل (Google Pixel Pro)';
                screenType = 'شاشة Actua / Super Actua OLED مع تصميم Material You';
                aspectDesc = '20:9 أندرويد خام فائق السلاسة';
                icon = '🔍';
                color = '#4ade80';
                safeTop = '26px';
                safeBottom = '16px';
                refreshRateEstimate = 'Smooth Display 120Hz';
            }

            // 9. أي هاتف أندرويد آخر قياسي
            else {
                brand = 'android';
                brandName = 'أندرويد الذكي';
                modelName = 'هاتف ذكي متكيف (Android Smartphone)';
                screenType = 'شاشة لمس ذكية فائقة التجاوب';
                aspectDesc = 'شاشة جوال مريحة';
                icon = '📱';
                color = '#10b981';
                safeTop = '20px';
                safeBottom = '16px';
            }
        }

        // تطبيق التفضيل اليدوي إن وُجد
        if (userViewPref === 'desktop') {
            layoutMode = 'desktop';
        } else if (userViewPref === 'tablet') {
            layoutMode = 'tablet';
        } else if (userViewPref === 'mobile') {
            layoutMode = 'mobile';
        }
        // إنشاء معرف UUID فريد ومستقر للجهاز
        let uuid = (typeof window.localStorage !== 'undefined' && window.localStorage) ? window.localStorage.getItem('deviceUuid') : null;
        if (!uuid) {
            uuid = 'DEV-' + Math.random().toString(36).substr(2, 7).toUpperCase() + '-' + Date.now().toString(36).substr(-4).toUpperCase();
            if (typeof window.localStorage !== 'undefined' && window.localStorage) {
                window.localStorage.setItem('deviceUuid', uuid);
            }
        }

        return {
            uuid: uuid,
            category: category,              // 'desktop' | 'laptop' | 'tablet' | 'mobile' | 'foldable'
            brand: brand,                    // 'apple' | 'samsung' | 'huawei' | 'honor' | 'sony' | 'motorola' ...
            brandName: brandName,
            modelName: modelName,
            screenType: screenType,
            aspectDesc: aspectDesc,
            icon: icon,
            color: color,
            layoutMode: layoutMode,          // 'desktop' | 'tablet' | 'mobile'
            isLaptop: (category === 'laptop'),
            isDesktop: (category === 'desktop'),
            isTablet: (category === 'tablet'),
            isMobile: (category === 'mobile' || category === 'foldable'),
            isFoldable: isFoldable,
            isUltrawide: isUltrawide,
            isDynamicIsland: isDynamicIsland,
            isCurvedScreen: isCurvedScreen,
            isTouch: isTouch,
            isLandscape: isLandscape,
            refreshRate: refreshRateEstimate,
            viewportWidth: width,
            viewportHeight: height,
            screenWidth: screenW,
            screenHeight: screenH,
            dpr: dpr,
            aspectRatio: aspectRatio.toFixed(2),
            safeTop: safeTop,
            safeBottom: safeBottom,
            safeLeft: safeLeft,
            safeRight: safeRight,
            gpuRenderer: gpuRenderer
        };
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 2. تطبيق التكيف الفوري على مستند الـ HTML وشجرة DOM (Master Layout Injection)
    // ─────────────────────────────────────────────────────────────────────────────
    function applyMasterHardwareAdaptation(device) {
        if (!device) device = detectMasterHardware();

        const root = document.documentElement;
        const body = document.body;

        // 1. حساب الارتفاع الحقيقي لتفادي قفزة شريط المتصفح على الجوال
        const vh = window.innerHeight * 0.01;
        root.style.setProperty('--vh', `${vh}px`);
        root.style.setProperty('--app-height', `${window.innerHeight}px`);
        root.style.setProperty('--safe-top', device.safeTop);
        root.style.setProperty('--safe-bottom', device.safeBottom);
        root.style.setProperty('--device-accent', device.color);

        // 2. حساب مقياس الواجهة الذكي (UI Scale)
        let uiScale = 1.0;
        let cardWidth = '420px';

        if (device.isMobile) {
            cardWidth = device.viewportWidth <= 380 ? '94%' : (device.viewportWidth >= 430 ? '430px' : '92%');
            uiScale = device.viewportWidth <= 360 ? 0.92 : (device.viewportWidth >= 430 ? 1.04 : 1.0);
        } else if (device.isTablet) {
            cardWidth = '540px';
            uiScale = 1.05;
        } else if (device.isLaptop) {
            cardWidth = device.viewportWidth <= 1366 ? '420px' : '440px';
            uiScale = device.viewportWidth <= 1366 ? 0.95 : 1.0;
        } else {
            // Desktop / Ultrawide
            cardWidth = '450px';
            uiScale = 1.0;
        }

        root.style.setProperty('--ui-scale', String(uiScale));
        root.style.setProperty('--card-max-width', cardWidth);

        // 3. حقن السمات والبيانات في وسوم HTML
        root.setAttribute('data-device-category', device.category);
        root.setAttribute('data-device-brand', device.brand);
        root.setAttribute('data-layout-mode', device.layoutMode);
        root.setAttribute('data-screen-ratio', device.aspectRatio);
        root.setAttribute('data-is-touch', device.isTouch ? '1' : '0');

        if (body) {
            // إزالة الأصناف القديمة
            const classesToRemove = Array.from(body.classList).filter(c => c.startsWith('device-') || c.startsWith('layout-'));
            classesToRemove.forEach(c => body.classList.remove(c));

            body.classList.add(`device-${device.category}`);
            body.classList.add(`device-brand-${device.brand}`);
            body.classList.add(`layout-${device.layoutMode}`);
            if (device.isDynamicIsland) body.classList.add('has-dynamic-island');
            if (device.isCurvedScreen) body.classList.add('has-curved-edges');
            if (device.isUltrawide) body.classList.add('is-ultrawide');
        }

        // 4. تحديث الكبسولة والشارات الذكية في واجهة الدخول إذا كانت موجودة
        updateDiagnosticCapsule(device);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 3. تحديث واجهة كبسولة التشخيص التفاعلية في شاشة الدخول (UI Capsule Updater)
    // ─────────────────────────────────────────────────────────────────────────────
    function updateDiagnosticCapsule(device) {
        if (!document || typeof document.getElementById !== 'function') return;
        // تحديث البادج الصغير
        const badgeEl = document.getElementById('screen-adaptive-badge');
        if (badgeEl) {
            badgeEl.innerHTML = `<span style="color: ${device.color}; display: inline-flex; align-items: center; gap: 4px;">
                <i class="fa-solid fa-microchip" style="font-size: 0.7rem;"></i>
                <span>${device.icon} ${device.brandName} • ${device.aspectRatio}:1</span>
            </span>`;
        }

        // تحديث البطاقة الكبرى للتشخيص
        const descEl = document.getElementById('device-adaptive-desc');
        const iconEl = document.getElementById('device-icon');
        if (descEl) {
            descEl.innerHTML = `<div style="text-align: right; line-height: 1.4;">
                <div style="font-weight: 850; color: ${device.color}; font-size: 0.82rem; display: flex; align-items: center; gap: 5px;">
                    <span>${device.icon}</span>
                    <span>${device.modelName}</span>
                </div>
                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 1px;">
                    ${device.screenType} • <span style="color:#38bdf8;">${device.viewportWidth}×${device.viewportHeight}pt</span> (@${device.dpr}x)
                </div>
            </div>`;
        }
        if (iconEl) {
            iconEl.textContent = device.icon;
        }

        // تحديث معرف الجهاز
        const devIdEl = document.getElementById('device-id-display');
        if (devIdEl) {
            devIdEl.innerText = `معرف الجهاز: ${device.uuid} • متكيف 100% (${device.category})`;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 4. محرك المايك الصوتي الذكي (AI Smart Voice Microphone for Username / Search)
    // ─────────────────────────────────────────────────────────────────────────────
    let speechRecognitionInstance = null;
    let isVoiceListening = false;

    function initVoiceMic(targetInputId, micButtonId) {
        const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
        const micBtn = document.getElementById(micButtonId);
        const targetInput = document.getElementById(targetInputId);

        if (!micBtn || !targetInput) return;

        if (!SpeechRec) {
            micBtn.title = 'المايك الصوتي غير مدعوم في هذا المتصفح';
            micBtn.style.opacity = '0.4';
            return;
        }

        micBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (isVoiceListening && speechRecognitionInstance) {
                speechRecognitionInstance.stop();
                return;
            }

            try {
                speechRecognitionInstance = new SpeechRec();
                speechRecognitionInstance.lang = 'ar-SA';
                speechRecognitionInstance.continuous = false;
                speechRecognitionInstance.interimResults = false;

                speechRecognitionInstance.onstart = function () {
                    isVoiceListening = true;
                    micBtn.classList.add('mic-active-pulse');
                    micBtn.style.color = '#ef4444';
                    micBtn.style.transform = 'scale(1.25)';
                    micBtn.innerHTML = '<i class="fa-solid fa-microphone-lines fa-fade"></i>';
                    targetInput.placeholder = 'تحدث الآن... المايك يستمع لك';
                };

                speechRecognitionInstance.onresult = function (event) {
                    if (event.results && event.results.length > 0) {
                        const transcript = event.results[0][0].transcript.trim();
                        // تنظيف النص وإدخاله
                        targetInput.value = transcript.toLowerCase();
                        targetInput.focus();
                        targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                        targetInput.dispatchEvent(new Event('change', { bubbles: true }));

                        // تأثير صوتي خفيف إن أمكن
                        try {
                            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                            const osc = audioCtx.createOscillator();
                            const gain = audioCtx.createGain();
                            osc.connect(gain);
                            gain.connect(audioCtx.destination);
                            osc.frequency.value = 587.33;
                            gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
                            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.2);
                            osc.start();
                            osc.stop(audioCtx.currentTime + 0.2);
                        } catch (err) {}
                    }
                };

                speechRecognitionInstance.onerror = function (event) {
                    console.warn('Voice mic error:', event.error);
                    stopListeningUI();
                };

                speechRecognitionInstance.onend = function () {
                    stopListeningUI();
                };

                speechRecognitionInstance.start();
            } catch (err) {
                console.warn('Speech recognition start failed:', err);
                stopListeningUI();
            }
        });

        function stopListeningUI() {
            isVoiceListening = false;
            micBtn.classList.remove('mic-active-pulse');
            micBtn.style.color = '#38bdf8';
            micBtn.style.transform = 'scale(1)';
            micBtn.innerHTML = '<i class="fa-solid fa-microphone"></i>';
            targetInput.placeholder = 'أدخل اسم المستخدم أو تحدث';
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 5. موجه الرابط الماستر الذكي لأي شاشة أو رابط خارجي (Master Router Helper)
    // ─────────────────────────────────────────────────────────────────────────────
    function getMasterTargetUrl(defaultFallback) {
        const urlParams = new URLSearchParams(window.location.search);
        
        // فحص هل يوجد رابط مخصص ممرر بالرابط (مثل ?url=... أو ?target=... أو ?redirect=...)
        const customUrl = urlParams.get('url') || urlParams.get('target') || urlParams.get('target_url') || urlParams.get('redirect');
        if (customUrl) {
            return decodeURIComponent(customUrl);
        }

        const explicitForceDesktop = urlParams.get('force_desktop') === '1';
        const explicitForceMobile = urlParams.get('force_mobile') === '1';
        const explicitDevice = urlParams.get('device');

        const dev = detectMasterHardware();

        if (explicitForceMobile) {
            return 'mobile-dashboard.php?force_mobile=1';
        }
        if (explicitForceDesktop) {
            return 'magaiees_unified_dashboard.php?force_desktop=1';
        }
        if (explicitDevice === 'tablet' || dev.isTablet) {
            return 'mobile-dashboard.php?device=tablet';
        }
        if (dev.isLaptop || dev.isDesktop) {
            return 'magaiees_unified_dashboard.php?force_desktop=1';
        }

        return defaultFallback || 'mobile-dashboard.php';
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 6. تهيئة المراقبة الحية عند تغيير الحجم والتدوير
    // ─────────────────────────────────────────────────────────────────────────────
    let resizeTimer = null;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            applyMasterHardwareAdaptation();
        }, 60);
    });

    window.addEventListener('orientationchange', function () {
        setTimeout(function () {
            applyMasterHardwareAdaptation();
        }, 150);
    });

    // التنفيذ الفوري عند تحميل السكريبت
    const currentMasterDevice = detectMasterHardware();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            applyMasterHardwareAdaptation(currentMasterDevice);
        });
    } else {
        applyMasterHardwareAdaptation(currentMasterDevice);
    }

    // تصدير الكائن العام للاستخدام في جميع الشاشات
    window.MasterDeviceEngine = {
        detect: detectMasterHardware,
        apply: applyMasterHardwareAdaptation,
        updateUI: updateDiagnosticCapsule,
        initVoiceMic: initVoiceMic,
        getTargetUrl: getMasterTargetUrl,
        currentDevice: currentMasterDevice
    };

})(window, document);
