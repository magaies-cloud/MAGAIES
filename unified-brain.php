<?php 
$page_title = 'العقل الذكي الموحد'; 
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem('erp_lang') || 'ar';
    if(l==='en') {
        document.documentElement.setAttribute('dir','ltr');
        document.documentElement.setAttribute('lang','en');
    }
})();
</script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>العقل الذكي الموحد - MAGAIEES ERP</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/unified-brain.css?v=20260722">
    
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    
    <style>
        :root {
            --bg-color: #050308;
            --surface: rgba(18, 15, 25, 0.65);
            --surface-hover: rgba(28, 24, 38, 0.85);
            --border: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(255, 255, 255, 0.2);
            --primary-accent: #6366f1;
            --theme-pink: #ec4899;
            --theme-blue: #3b82f6;
            --theme-green: #10b981;
            --theme-orange: #f59e0b;
            --theme-red: #f87171;
            --theme-purple: #c084fc;
        }
        
        body {
            background-color: var(--bg-color, #050308);
            color: white;
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        
        /* Full Widescreen Layout Container */
        .ub-page-container {
            width: 100%;
            max-width: 1850px;
            margin: 0 auto;
            padding: 15px 30px 40px 30px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 20px;
            flex: 1;
        }
        
        .ub-header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            margin-top: 10px;
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.1), rgba(56, 189, 248, 0.05));
            border: 1px solid rgba(168, 85, 247, 0.2);
            border-radius: 20px;
            padding: 24px 30px;
            backdrop-filter: blur(20px);
        }
        
        .glass-panel {
            background: var(--surface, rgba(18, 15, 25, 0.65));
            border: 1px solid var(--border, rgba(255, 255, 255, 0.08));
            border-radius: 20px;
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 26px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4), inset 0 1px 1px rgba(255,255,255,0.05);
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
        }
        
        .glass-panel:hover {
            border-color: rgba(168, 85, 247, 0.3);
            box-shadow: 0 20px 40px rgba(0,0,0,0.5), 0 0 25px rgba(168, 85, 247, 0.15);
        }
        
        .ub-grid-top { 
            display: grid; 
            grid-template-columns: 1fr 2.8fr; 
            gap: 24px; 
            margin-bottom: 10px; 
        }
        
        .ub-grid-depts { 
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 24px; 
            margin-bottom: 10px; 
        }
        
        .ub-chat-box { 
            display: flex; 
            gap: 12px; 
            margin-top: 20px; 
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }
        
        .ub-chat-input { 
            flex: 1; 
            background: rgba(0,0,0,0.4); 
            border: 1px solid var(--border-highlight); 
            border-radius: 12px; 
            padding: 14px 18px; 
            color: white; 
            font-family: 'Tajawal'; 
            font-size: 1rem;
            outline: none;
            transition: all 0.3s;
        }
        
        .ub-chat-input:focus {
            border-color: #a855f7;
            box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.25);
        }
        
        .ub-chat-btn { 
            background: linear-gradient(135deg, #a855f7, #6366f1); 
            border: none; 
            border-radius: 12px; 
            padding: 0 32px; 
            color: white; 
            cursor: pointer; 
            font-weight: 800; 
            font-family: 'Tajawal';
            font-size: 1rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.3);
        }
        
        .ub-chat-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(168, 85, 247, 0.5);
        }
        
        .ub-big-circle { 
            width: 240px; 
            height: 240px; 
            margin: 15px auto; 
            position: relative; 
        }
        
        .ub-page-recs {
            flex: 1;
            background: linear-gradient(145deg, rgba(168, 85, 247, 0.04), transparent);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px;
            overflow-y: auto;
            max-height: 420px;
            line-height: 1.8;
            color: #e5e7eb;
            font-size: 1.05rem;
        }
        
        .ub-page-recs::-webkit-scrollbar {
            width: 8px;
        }
        
        .ub-page-recs::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.1);
        }
        
        .ub-page-recs::-webkit-scrollbar-thumb {
            background: var(--border-highlight);
            border-radius: 4px;
        }
        
        @media (max-width: 1400px) {
            .ub-grid-depts { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 1024px) {
            .ub-grid-top { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .ub-grid-depts { grid-template-columns: 1fr; }
            .ub-header-section { flex-direction: column; gap: 16px; align-items: flex-start; }
            .ub-chat-box { flex-direction: column; }
            .ub-chat-btn { padding: 14px; justify-content: center; }
        }
    </style>
</head>
<body>
    <!-- Top System Header Bar -->
    <div style="padding: 10px 30px 0 30px;">
        <?php $header_theme_class = 'header-theme-reports'; ?>
        <?php include 'includes/header.php'; ?>
    </div>

    <div class="ub-page-container">
        <!-- Main Section Header -->
        <div class="ub-header-section">
            <div>
                <h1 style="font-size: 2.2rem; font-weight: 900; margin-bottom: 6px; margin-top: 0; display: flex; align-items: center; gap: 12px; background: linear-gradient(135deg, #c084fc, #38bdf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fa-solid fa-brain" style="color: #c084fc; -webkit-text-fill-color: initial; filter: drop-shadow(0 0 10px rgba(192, 132, 252, 0.5));"></i> 
                    العقل الذكي الموحد
                </h1>
                <p style="color: #94a3b8; font-size: 1.05rem; margin: 0; font-weight: 500;">مركز الإدارة والرقابة الاستراتيجية الشاملة لجميع أقسام منظومة MAGAIEES ERP</p>
            </div>
            <button class="ub-action-btn ub-action-primary" style="width: auto; padding: 14px 32px; font-size: 1.1rem; border-radius: 14px; background: linear-gradient(135deg, #a855f7, #6366f1); font-weight: 800; cursor: pointer; border: none; color: white; box-shadow: 0 6px 20px rgba(168, 85, 247, 0.4);" id="page-analyze-btn" onclick="runPageAnalysis()">
                <i class="fa-solid fa-wand-magic-sparkles"></i> تشغيل التحليل الذكي الشامل
            </button>
        </div>

        <div class="ub-grid-top">
            <!-- Health Score Panel -->
            <div class="glass-panel" style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; text-align: center;">
                <h3 style="font-size: 1.25rem; margin: 0 0 10px 0; color: #f3f4f6; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-shield-heart" style="color: #38bdf8;"></i> مؤشر الصحة العام للمنظومة
                </h3>
                
                <div class="ub-big-circle">
                    <svg class="ub-health-svg" viewBox="0 0 120 120" style="transform: rotate(-90deg); width: 100%; height: 100%;">
                        <circle class="ub-health-bg" cx="60" cy="60" r="52" fill="none" stroke="var(--border)" stroke-width="7" />
                        <circle class="ub-health-progress" id="page-health-progress" cx="60" cy="60" r="52" fill="none" stroke="#10b981" stroke-width="7" stroke-linecap="round" style="transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1), stroke 0.5s;" />
                    </svg>
                    <div class="ub-health-value" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <span class="ub-health-number" id="page-health-number" style="display: block; font-size: 4.2rem; font-weight: 900; line-height: 1; color: #10b981;">--</span>
                        <span class="ub-health-label" id="page-health-label" style="font-size: 1.15rem; color: #9ca3af; font-weight: 700; margin-top: 8px; display: block;">في انتظار التحليل</span>
                    </div>
                </div>
                
                <div id="page-health-factors" style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                    <span style="background: rgba(255,255,255,0.05); padding: 8px 18px; border-radius: 20px; font-size: 0.9rem; color: #9ca3af; border: 1px solid var(--border);">جاري تحميل المؤشرات الحية...</span>
                </div>
            </div>

            <!-- AI Recommendations & Interactive Chat Panel -->
            <div class="glass-panel" style="display: flex; flex-direction: column;">
                <h3 style="font-size: 1.25rem; margin: 0 0 20px 0; color: #c084fc; display: flex; align-items: center; gap: 10px; font-weight: 800;">
                    <i class="fa-solid fa-robot"></i> التوصيات الاستراتيجية والمحادثة الذكية
                </h3>
                
                <div id="page-ai-recs" class="ub-page-recs">
                    <div style="text-align: center; color: #9ca3af; padding: 45px 0;">
                        <i class="fa-solid fa-wand-magic-sparkles" style="font-size: 3.2rem; opacity: 0.5; margin-bottom: 16px; color: #c084fc;"></i>
                        <p style="font-size: 1.1rem;">اضغط على زر "تشغيل التحليل الذكي الشامل" لجلب التوصيات العميقة لجميع الأقسام.</p>
                    </div>
                </div>
                
                <div class="ub-chat-box">
                    <input type="text" id="page-chat-input" class="ub-chat-input" placeholder="اسأل العقل الذكي عن أي قسم، أو اطلب تحليل بيانات محددة..." onkeypress="if(event.key === 'Enter') sendQuestion()">
                    <button class="ub-chat-btn" onclick="sendQuestion()" id="page-chat-btn">
                        <i class="fa-solid fa-paper-plane"></i> إرسال الطلب
                    </button>
                </div>
            </div>
        </div>

        <!-- Departments Details Grid Header -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin: 30px 0 15px 0;">
            <h3 style="font-size: 1.4rem; margin: 0; font-weight: 900; display: flex; align-items: center; gap: 12px; color: #f8fafc;">
                <i class="fa-solid fa-layer-group" style="color: #38bdf8; font-size: 1.5rem;"></i> حالة الأقسام التفصيلية والمؤشرات الحية
            </h3>
        </div>
        
        <div class="ub-grid-depts" id="page-depts-grid">
            <div class="glass-panel" style="text-align: center; padding: 45px 20px; color: #9ca3af; grid-column: span 4;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 2.2rem; margin-bottom: 16px; color: #c084fc;"></i><br>
                جاري قراءة وتحليل بيانات قاعدة البيانات الحية...
            </div>
        </div>
        
        <!-- Timeline Chart Panel -->
        <div class="glass-panel" style="margin-top: 10px;">
            <h3 style="font-size: 1.25rem; margin: 0 0 24px 0; display: flex; align-items: center; gap: 10px; font-weight: 800; color: #f8fafc;">
                <i class="fa-solid fa-chart-line" style="color: #38bdf8;"></i> المسار التاريخي لصحة المنظومة والاتجاه العام
            </h3>
            <div style="height: 280px; width: 100%;">
                <canvas id="healthTimelineChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Include the Unified Brain JS module -->
    <script src="js/unified-brain.js?v=20260722"></script>
    
    <script>
        async function runPageAnalysis() {
            const btn = document.getElementById('page-analyze-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري تحليل البيانات المعقدة...';
            btn.disabled = true;
            
            document.getElementById('page-ai-recs').innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #c084fc; padding: 40px 0;">
                    <i class="fa-solid fa-brain fa-beat-fade" style="font-size: 3.5rem; margin-bottom: 16px;"></i>
                    <p style="font-size: 1.1rem; font-weight: 700;">العقل الذكي يقوم بتحليل ملايين السجلات...</p>
                </div>
            `;
            
            try {
                const res = await fetch('api/unified-brain.php', { method: 'POST' });
                const data = await res.json();
                updatePageUI(data);
                
                if (window.UnifiedBrain && window.UnifiedBrain.isOpen) {
                    window.UnifiedBrain.updateHealthUI(data);
                    window.UnifiedBrain.updateDepartmentsUI(data);
                    window.UnifiedBrain.updateRecommendationsUI(data);
                }
            } catch(e) {
                console.error(e);
                document.getElementById('page-ai-recs').innerHTML = '<div style="color: #f87171; padding: 20px; text-align: center;"><i class="fa-solid fa-triangle-exclamation" style="font-size: 2rem; margin-bottom: 10px;"></i><br>فشل الاتصال بخادم الذكاء الاصطناعي. يرجى المحاولة لاحقاً.</div>';
            } finally {
                btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> تحديث التحليل الشامل';
                btn.disabled = false;
            }
        }
        
        async function sendQuestion() {
            const input = document.getElementById('page-chat-input');
            const btn = document.getElementById('page-chat-btn');
            const question = input.value.trim();
            if (!question) return;
            
            const recsBox = document.getElementById('page-ai-recs');
            
            if(recsBox.innerHTML.includes('fa-wand-magic-sparkles') && recsBox.innerHTML.includes('opacity: 0.5')) {
                recsBox.innerHTML = '';
            }
            
            recsBox.innerHTML += `
                <div style="margin-top: 16px; display: flex; justify-content: flex-start; margin-right: 20px;">
                    <div style="background: rgba(168, 85, 247, 0.18); border-radius: 14px 14px 0 14px; padding: 14px 18px; border-right: 3px solid #c084fc; max-width: 80%;">
                        <strong style="color: #c084fc; font-size: 0.9rem; display: block; margin-bottom: 4px;">أنت</strong>
                        ${question}
                    </div>
                </div>`;
                
            recsBox.innerHTML += `
                <div id="typing-indicator" style="margin-top: 16px; display: flex; justify-content: flex-end; margin-left: 20px;">
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: 14px 14px 14px 0; padding: 12px 18px; color: #c084fc;">
                        <i class="fa-solid fa-ellipsis fa-fade"></i> العقل الذكي يفكر ويستعلم من قاعدة البيانات...
                    </div>
                </div>`;
                
            recsBox.scrollTop = recsBox.scrollHeight;
            input.value = '';
            input.disabled = true;
            btn.disabled = true;
            
            try {
                const res = await fetch('api/unified-brain.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ question: question })
                });
                const data = await res.json();
                
                document.getElementById('typing-indicator')?.remove();
                
                let answerText = data.ai_recommendations || data.answer || data.response || 'لم أتمكن من استنتاج الإجابة في الوقت الحالي.';
                
                let answerHtml = answerText
                    .replace(/\*\*(.*?)\*\*/g, '<strong style="color:#38bdf8">$1</strong>')
                    .replace(/\n/g, '<br>');
                
                recsBox.innerHTML += `
                    <div style="margin-top: 16px; display: flex; justify-content: flex-end; margin-left: 20px;">
                        <div style="background: rgba(255, 255, 255, 0.07); border-radius: 14px 14px 14px 0; padding: 18px 22px; border-left: 4px solid #10b981; max-width: 85%; box-shadow: 0 6px 20px rgba(0,0,0,0.2);">
                            <strong style="color: #34d399; font-size: 0.95rem; display: block; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-brain"></i> العقل الذكي الموحد
                            </strong>
                            ${answerHtml}
                        </div>
                    </div>`;
                    
                recsBox.scrollTop = recsBox.scrollHeight;
            } catch(e) {
                document.getElementById('typing-indicator')?.remove();
                recsBox.innerHTML += `<div style="margin-top:12px; color: #f87171; text-align: left;">حدث خطأ في الاتصال أثناء جلب الإجابة.</div>`;
            } finally {
                input.disabled = false;
                btn.disabled = false;
                input.focus();
            }
        }
        
        function updatePageUI(data) {
            const score = data.health_score || 0;
            const numEl = document.getElementById('page-health-number');
            const startScore = parseInt(numEl.textContent) || 0;
            
            const duration = 1500;
            const startTime = performance.now();
            const range = score - startScore;
            
            function step(timestamp) {
                const elapsed = timestamp - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                numEl.textContent = Math.round(startScore + range * eased);
                if (progress < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
            
            document.getElementById('page-health-label').textContent = data.health_label || '';
            
            const circle = document.getElementById('page-health-progress');
            const circumference = 2 * Math.PI * 52;
            const offset = circumference - (score / 100) * circumference;
            circle.style.strokeDasharray = circumference;
            circle.style.strokeDashoffset = offset;
            
            let color = '#10b981';
            if (score < 30) color = '#f87171';
            else if (score < 50) color = '#f59e0b';
            else if (score < 70) color = '#fbbf24';
            else if (score < 85) color = '#34d399';
            circle.style.stroke = color;
            numEl.style.color = color;
            
            const factorsEl = document.getElementById('page-health-factors');
            if (data.health_factors && data.health_factors.length > 0) {
                factorsEl.innerHTML = data.health_factors.map(f => {
                    let icon = '⚡';
                    let bg = 'rgba(255,255,255,0.08)';
                    let borderColor = 'var(--border)';
                    let textColor = '#e5e7eb';
                    
                    if(f.includes('جيد') || f.includes('ممتاز') || f.includes('ارتفاع')) {
                        icon = '✅'; bg = 'rgba(16, 185, 129, 0.1)'; borderColor = 'rgba(16, 185, 129, 0.3)'; textColor = '#34d399';
                    } else if (f.includes('خطر') || f.includes('حرج') || f.includes('انخفاض شديد')) {
                        icon = '🔴'; bg = 'rgba(248, 113, 113, 0.1)'; borderColor = 'rgba(248, 113, 113, 0.3)'; textColor = '#f87171';
                    } else if (f.includes('تحت') || f.includes('متوسط') || f.includes('بعض')) {
                        icon = '⚠️'; bg = 'rgba(245, 158, 11, 0.1)'; borderColor = 'rgba(245, 158, 11, 0.3)'; textColor = '#fbbf24';
                    }
                    
                    return `<span style="background: ${bg}; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; color: ${textColor}; border: 1px solid ${borderColor}; display: flex; align-items: center; gap: 6px; font-weight: 700;">
                        <span>${icon}</span> ${f}
                    </span>`;
                }).join('');
            } else {
                factorsEl.innerHTML = '<span style="background: rgba(16, 185, 129, 0.1); padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); font-weight: 700;">✅ الوضع مستقر بشكل عام</span>';
            }
            
            const grid = document.getElementById('page-depts-grid');
            const depts = data.departments || {};
            const deptConfig = {
                inventory: { icon: 'fa-warehouse', label: 'إدارة المخازن والبضائع', color: '#fbbf24' },
                treasury: { icon: 'fa-vault', label: 'الخزينة والسيولة النقدية', color: '#10b981' },
                sales: { icon: 'fa-cart-shopping', label: 'حركة المبيعات والإيرادات', color: '#38bdf8' },
                customers: { icon: 'fa-users', label: 'محفظة العملاء والديون', color: '#c084fc' }
            };
            
            const statusColors = { good: '#10b981', warning: '#f59e0b', critical: '#f87171' };
            const statusLabels = { good: 'وضع صحي', warning: 'تنبيهات هامة', critical: 'وضع حرج' };
            
            grid.innerHTML = Object.entries(deptConfig).map(([key, cfg]) => {
                const dept = depts[key] || { status: 'good', score: 0, summary: 'لا توجد بيانات كافية للتحليل', raw_data: {} };
                const statusColor = statusColors[dept.status] || '#94a3b8';
                
                const barHtml = `
                    <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; margin-top: 16px; overflow: hidden;">
                        <div style="width: ${dept.score}%; height: 100%; background: ${statusColor}; border-radius: 3px;"></div>
                    </div>
                `;
                
                return `
                    <div class="glass-panel" style="padding: 26px; position: relative; overflow: hidden; transition: transform 0.3s; display: flex; flex-direction: column;">
                        <div style="position: absolute; top:0; right:0; width:5px; height:100%; background:${statusColor};"></div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <div style="width:50px; height:50px; border-radius:14px; background:${cfg.color}15; color:${cfg.color}; display:flex; align-items:center; justify-content:center; font-size: 1.45rem; border: 1px solid ${cfg.color}30; box-shadow: inset 0 1px 2px rgba(255,255,255,0.15);">
                                    <i class="fa-solid ${cfg.icon}"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 900; font-size: 1.15rem; color: #f8fafc;">${cfg.label}</div>
                                    <div style="font-size: 0.85rem; color:${statusColor}; font-weight: 800; margin-top: 3px; display: flex; align-items: center; gap: 6px;">
                                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: ${statusColor}; shadow: 0 0 8px ${statusColor};"></span>
                                        ${statusLabels[dept.status]} (${dept.score}%)
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div style="font-size: 0.98rem; color: #d1d5db; line-height: 1.6; margin-bottom: 20px; flex: 1;">
                            ${dept.summary}
                        </div>
                        
                        ${barHtml}
                        
                        <div style="margin-top: 20px;">
                            <button onclick="const el = this.nextElementSibling; if(el.style.display === 'none') { el.style.display = 'block'; this.innerHTML = 'إخفاء البيانات الخام <i class=\\'fa-solid fa-chevron-up\\'></i>'; } else { el.style.display = 'none'; this.innerHTML = 'عرض البيانات الخام <i class=\\'fa-solid fa-chevron-down\\'></i>'; }" style="background: rgba(255,255,255,0.04); border: 1px solid var(--border); color: #9ca3af; padding: 10px; border-radius: 10px; font-size: 0.9rem; cursor: pointer; width: 100%; font-family: 'Tajawal'; font-weight: 700; transition: all 0.2s;">
                                عرض البيانات الخام <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            <div style="display: none; margin-top: 12px; font-size: 0.85rem; background: rgba(0,0,0,0.4); padding: 16px; border-radius: 10px; font-family: monospace; white-space: pre-wrap; overflow-x: auto; color: #a7f3d0; border: 1px solid rgba(16, 185, 129, 0.2); max-height: 200px; overflow-y: auto;" dir="ltr">
${JSON.stringify(data.raw_data && data.raw_data[key] ? data.raw_data[key] : { message: "بيانات القسم محملة ومربوطة" }, null, 2)}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            const recsBox = document.getElementById('page-ai-recs');
            if (data.ai_recommendations) {
                let html = data.ai_recommendations
                    .replace(/\*\*(.*?)\*\*/g, '<strong style="color:#38bdf8; font-weight:800;">$1</strong>')
                    .replace(/🔴/g, '<span style="color:#f87171">🔴</span>')
                    .replace(/🟡/g, '<span style="color:#fbbf24">🟡</span>')
                    .replace(/🟢/g, '<span style="color:#34d399">🟢</span>')
                    .replace(/\n/g, '<br>');
                
                recsBox.innerHTML = `
                    <div style="padding: 10px;">
                        <h4 style="color: #f3f4f6; margin-bottom: 16px; font-size: 1.1rem; border-bottom: 1px solid var(--border); padding-bottom: 10px; font-weight: 800;">تحليل الذكاء الاصطناعي المفصل والربط بين الأقسام</h4>
                        ${html}
                    </div>
                `;
            }
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            fetch('api/unified-brain.php')
                .then(r => r.json())
                .then(data => {
                    if(data && data.health_score) {
                        updatePageUI(data);
                    }
                }).catch(e => console.error('Silent fail for quick load', e));
                
            const ctx = document.getElementById('healthTimelineChart').getContext('2d');
            
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(168, 85, 247, 0.4)');   
            gradient.addColorStop(1, 'rgba(168, 85, 247, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['الأسبوع 1', 'الأسبوع 2', 'الأسبوع 3', 'الأسبوع 4', 'الأسبوع 5', 'الأسبوع 6', 'الحالي'],
                    datasets: [{
                        label: 'مؤشر الصحة العام',
                        data: [65, 70, 68, 75, 80, 78, 85],
                        borderColor: '#a855f7',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#050308',
                        pointBorderColor: '#a855f7',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.85)',
                            titleFont: { family: 'Tajawal' },
                            bodyFont: { family: 'Tajawal' },
                            padding: 12,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: false,
                            min: 40, 
                            max: 100, 
                            grid: { 
                                color: 'rgba(255,255,255,0.05)',
                                drawBorder: false
                            },
                            ticks: { 
                                color: '#9ca3af',
                                font: { family: 'Tajawal' },
                                padding: 10
                            }
                        },
                        x: {
                            grid: { 
                                display: false,
                                drawBorder: false
                            },
                            ticks: { 
                                color: '#9ca3af',
                                font: { family: 'Tajawal' },
                                padding: 10
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                }
            });
        });
    </script>
</body>
</html>
