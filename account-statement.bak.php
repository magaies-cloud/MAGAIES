<?php $page_title = 'كشف الحساب - 25 نموذج عالمي'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف الحساب - 25 نموذج عالمي | Al-Sajana ERP</title>
    
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css">
    
    <!-- مكتبة PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <!-- مكتبة السحب والإفلات -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <!-- مكتبة إكسل -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- مكتبة ApexCharts للرسوم التفاعلية -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        /* ==============================================================
           Global Variables & Base
           ============================================================== */
        :root {
            --primary: #38bdf8; --primary-hover: #0ea5e9;
            --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
            --bg-dark: #0f172a; --card-bg: rgba(30, 41, 59, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc; --text-muted: #94a3b8;
        }

        body { font-family: 'Tajawal', 'Inter', sans-serif; background-color: var(--bg-dark); color: var(--text-main); margin: 0; padding: 0; transition: background-color 0.5s ease; overflow-x: hidden; }
        .num { font-family: 'JetBrains Mono', 'Inter', monospace; letter-spacing: 0.5px; }
        .container { max-width: 1700px; margin: 0 auto; padding: 30px; }
        .val-dr { color: var(--success); } .val-cr { color: var(--danger); } .val-bal { color: var(--primary); }
        
        /* ==============================================================
           The Visual Switcher - LUXURY EDITION
           ============================================================== */
        .page-header { margin-bottom: 40px; text-align: center; }
        .header-title h1 { font-size: 2.8rem; font-weight: 900; margin: 0 0 10px 0; background: linear-gradient(135deg, #38bdf8, #c084fc, #38bdf8); background-size: 200% auto; animation: shine 5s linear infinite; -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        @keyframes shine { to { background-position: 200% center; } }
        .header-title p { color: var(--text-muted); font-size: 1.1rem; }
        
        .visual-switcher { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .switcher-group { background: rgba(15,23,42,0.4); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 25px; backdrop-filter: blur(20px); box-shadow: 0 15px 40px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.1); transition: all 0.3s ease; }
        .switcher-group:hover { border-color: rgba(255,255,255,0.15); box-shadow: 0 20px 50px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.2); }
        .switcher-group h4 { margin: 0 0 20px 0; color: #f8fafc; font-size: 1.1rem; font-weight: 800; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px; display: flex; align-items: center; gap: 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
        
        .g-classic .switcher-buttons { grid-template-columns: repeat(4, 1fr); }
        .g-modern .switcher-buttons { grid-template-columns: repeat(4, 1fr); }
        .g-analytic .switcher-buttons { grid-template-columns: repeat(3, 1fr); }
        .switcher-buttons { display: grid; gap: 15px; }
        
        .v-btn { background: linear-gradient(180deg, rgba(255,255,255,0.05), rgba(255,255,255,0.01)); border: 1px solid rgba(255,255,255,0.05); border-radius: 18px; padding: 20px 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: var(--text-muted); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden; width: 100%; }
        .v-btn::before { content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); transform: skewX(-25deg); transition: 0.5s; z-index: 0; }
        .v-btn:hover { transform: translateY(-8px) scale(1.05); background: linear-gradient(180deg, rgba(255,255,255,0.1), rgba(255,255,255,0.03)); color: #fff; border-color: rgba(56,189,248,0.4); box-shadow: 0 15px 30px rgba(56,189,248,0.2), inset 0 1px 0 rgba(255,255,255,0.3); }
        .v-btn:hover::before { left: 200%; }
        .v-btn.active { background: linear-gradient(135deg, rgba(56,189,248,0.25), rgba(14,165,233,0.05)); border-color: var(--primary); color: #fff; transform: scale(1.1) translateY(-5px); box-shadow: 0 20px 40px rgba(14,165,233,0.3), inset 0 1px 0 rgba(255,255,255,0.3), inset 0 0 20px rgba(56,189,248,0.2); z-index: 2; }
        
        .v-icon { font-size: 2.2rem; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4)); z-index: 1; }
        .v-btn:hover .v-icon { transform: scale(1.2) translateY(-3px); filter: drop-shadow(0 0 15px rgba(255,255,255,0.6)); }
        .v-btn.active .v-icon { filter: drop-shadow(0 0 20px var(--primary)); transform: scale(1.2); }
        .v-name { font-size: 0.85rem; font-family: 'Tajawal', sans-serif; font-weight: 800; text-align: center; line-height: 1.4; z-index: 1; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }

        .actions-panel { display: flex; gap: 15px; justify-content: center; margin-bottom: 40px; }
        .btn-action { padding: 12px 30px; border-radius: 12px; font-weight: 800; font-size: 1rem; cursor: pointer; border: none; display: flex; align-items: center; gap: 10px; font-family: inherit; transition: all 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .btn-action:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        .btn-primary { background: linear-gradient(135deg, var(--primary), #818cf8); color: #fff; }
        .btn-secondary { background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border-color); backdrop-filter: blur(10px); }

        /* ==============================================================
           Context Menu (Right Click) - DRAGGABLE & INLINE RENAME
           ============================================================== */
        .context-menu {
            display: none; position: fixed; z-index: 9999; 
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 24px; 
            box-shadow: 0 0 30px rgba(56, 189, 248, 0.4), 
                        0 20px 50px rgba(0, 0, 0, 0.8), 
                        inset 0 2px 5px rgba(255, 255, 255, 0.2),
                        inset 0 0 10px rgba(255, 255, 255, 0.05); 
            width: 360px; color: #f8fafc;
            overflow: hidden; 
            border: 1px solid rgba(255, 255, 255, 0.3); 
            font-family: 'Tajawal', sans-serif;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .context-menu::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.15) 0%, transparent 60%);
            animation: rotateGlow 15s linear infinite; pointer-events: none; z-index: 0;
        }
        .context-menu::after {
            content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transform: skewX(-25deg); animation: shineGlow 6s infinite; pointer-events: none; z-index: 0;
        }
        @keyframes shineGlow { 0% { left: -100%; } 20% { left: 200%; } 100% { left: 200%; } }
        @keyframes rotateGlow { 100% { transform: rotate(360deg); } }

        .context-menu.fullscreen {
            width: 100vw !important; height: 100vh !important;
            top: 0 !important; left: 0 !important;
            border-radius: 0; border: none;
            transform: none !important;
        }
        .context-menu.fullscreen .c-body {
            max-height: calc(100vh - 160px);
        }

        .context-menu.active { 
            display: flex; flex-direction: column; 
            animation: popInGlass 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; 
        }
        @keyframes popInGlass { 
            0% { opacity: 0; transform: scale(0.8) translateY(-20px); filter: blur(5px); } 
            100% { opacity: 1; transform: scale(1) translateY(0); filter: blur(0); } 
        }
        
        .c-drag-header {
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.2), rgba(255, 255, 255, 0.05)); 
            padding: 16px 20px; font-weight: 900; font-size: 1.1rem; 
            color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); cursor: grab; display: flex; justify-content: space-between; align-items: center;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.8); position: relative; z-index: 1;
        }
        .c-drag-header:active { cursor: grabbing; }
        .c-maximize { cursor: pointer; color: #fff; font-size: 1.2rem; transition: 0.3s; padding: 0 5px; text-shadow: 0 0 5px rgba(255,255,255,0.5); }
        .c-maximize:hover { color: #38bdf8; text-shadow: 0 0 15px #38bdf8; transform: scale(1.2); }
        .c-close { cursor: pointer; color: #fff; font-size: 1.4rem; transition: 0.3s; padding: 0 5px; text-shadow: 0 0 5px rgba(255,255,255,0.5); }
        .c-close:hover { color: #ef4444; text-shadow: 0 0 15px #ef4444; transform: rotate(90deg); }

        .c-body { max-height: 85vh; overflow-y: auto; padding: 15px 0; position: relative; z-index: 1; }
        .c-body::-webkit-scrollbar { width: 6px; }
        .c-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 10px; }
        .c-body::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.5); }

        .c-title { padding: 0 20px 10px 20px; font-weight: 800; font-size: 0.85rem; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 1px; text-shadow: 0 1px 2px rgba(0,0,0,0.5); }
        
        .c-actions { display: flex; justify-content: space-between; align-items: center; padding: 8px 20px; }
        .c-btn-small { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); padding: 5px 15px; border-radius: 8px; cursor: pointer; font-weight: 900; font-size: 1.1rem; color: #fff; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
        .c-btn-small:hover { background: rgba(56, 189, 248, 0.4); border-color: #38bdf8; box-shadow: 0 0 15px rgba(56, 189, 248, 0.6); transform: translateY(-2px) scale(1.1); }

        .c-hr { height: 1px; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); margin: 15px 0; border: none; }

        .c-item-complex { padding: 12px 20px; transition: all 0.3s ease; border-left: 4px solid transparent; border-bottom: 1px solid rgba(255,255,255,0.03); position: relative; overflow: hidden; }
        .c-item-complex:hover { background: rgba(255,255,255,0.08); border-left-color: #38bdf8; transform: translateX(5px); box-shadow: inset 0 0 20px rgba(56, 189, 248, 0.1); }
        .c-item-complex.on { border-left-color: var(--success); background: rgba(16, 185, 129, 0.05); }
        
        .c-switch { width: 44px; height: 24px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; position: relative; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); cursor: pointer; box-shadow: inset 0 2px 5px rgba(0,0,0,0.5); }
        .c-switch::after { content: ''; position: absolute; left: 3px; top: 3px; width: 16px; height: 16px; background: #fff; border-radius: 50%; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow: 0 2px 4px rgba(0,0,0,0.3); }
        .c-item-complex.on .c-switch { background: linear-gradient(135deg, #10b981, #34d399); border-color: #10b981; box-shadow: 0 0 10px rgba(16, 185, 129, 0.5); }
        .c-item-complex.on .c-switch::after { transform: translateX(20px); box-shadow: 0 0 5px rgba(255,255,255,0.8); }

        .c-input-rename { width: 100%; padding: 8px 12px; margin-top: 8px; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; font-family: inherit; font-weight: 700; font-size: 0.95rem; color: #fff; transition: all 0.3s; background: rgba(0,0,0,0.3); box-shadow: inset 0 2px 5px rgba(0,0,0,0.3); }
        .c-input-rename:focus { outline: none; background: rgba(255,255,255,0.1); border-color: #38bdf8; box-shadow: 0 0 15px rgba(56,189,248,0.4), inset 0 2px 5px rgba(0,0,0,0.2); transform: translateY(-2px); }
        
        .color-swatch { width: 24px; height: 24px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); cursor: pointer; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow: 0 2px 5px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; font-size: 0.6rem; }
        .color-swatch:hover { transform: scale(1.3) rotate(15deg); border-color: #fff; box-shadow: 0 0 10px rgba(255,255,255,0.6); }

        .btn-save-layout { width: calc(100% - 40px); margin: 15px 20px; padding: 14px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; font-weight: 900; font-size: 1.1rem; cursor: pointer; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.4); position: relative; z-index: 1; overflow: hidden; }
        .btn-save-layout::before { content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent); transform: skewX(-25deg); transition: 0.5s; z-index: -1; }
        .btn-save-layout:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 15px 30px rgba(16, 185, 129, 0.6); border-color: #34d399; }
        .btn-save-layout:hover::before { left: 200%; }

        /* Dynamic Enterprise Variables & Clickable Numbers */
        #v-classic-ent table { --fs-text: 1.15rem; --fs-num: 1.15rem; --font-num: 'JetBrains Mono', monospace; transition: all 0.3s; table-layout: fixed; width: 100%; border: 4px solid #000000; }
        #v-classic-ent th { font-size: calc(var(--fs-text) * 1.1); position: relative; text-align: center; font-weight: 900; }
        
        .resizer {
            position: absolute;
            left: -4px;
            top: 0;
            bottom: 0;
            width: 10px;
            cursor: col-resize;
            background: transparent;
            z-index: 10;
        }
        .resizer:hover, .resizer.resizing {
            background: var(--primary);
            opacity: 0.8;
        }

        #v-classic-ent td:not(.num) { font-size: var(--fs-text); text-align: center; }
        
        #v-classic-ent td.num { 
            text-align: center;
            font-size: var(--fs-num); 
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            font-family: var(--font-num);
            letter-spacing: 0px;
            cursor: pointer; 
            position: relative;
        }
        #v-classic-ent td.num:hover {
            transform: scale(1.05);
            color: var(--primary-hover);
        }
        
        /* The Focused Specific Number Feature */
        .focused-num {
            transform: scale(1.6) translateY(-2px) !important;
            color: #8b5cf6 !important; /* Prominent purple */
            font-weight: 900 !important;
            background: #f3e8ff !important;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4), inset 0 0 0 2px #8b5cf6 !important;
            z-index: 50;
            display: inline-block;
            padding: 0 8px;
        }

        #v-classic-ent thead th { transition: background 0.3s; }
        #v-classic-ent thead { position: relative; }
        #v-classic-ent thead::before { content: '💡 اضغط (كليك يمين) هنا لتخصيص الأعمدة والخطوط والأسماء'; position: absolute; top: -30px; right: 0; font-size: 0.85rem; color: #6b7280; opacity: 0; transition: 0.3s; font-weight: bold; }
        #v-classic-ent thead:hover::before { opacity: 1; }

        /* ==============================================================
           Views Base & All 25 Model Styles
           ============================================================== */
        #print-container { background: transparent; transition: all 0.3s; }
        .template-view { display: none; animation: slideUpFade 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        .template-view.active { display: block; }
        @keyframes slideUpFade { from { opacity: 0; transform: translateY(30px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }

        /* Group 1: Classics */
        .classic-base table { width: 100%; border-collapse: collapse; text-align: right; }
        #v-classic-ent { background: #fff; padding: 40px; border-radius: 8px; color: #111827; }
        #v-classic-ent table th { background: #e2e8f0; color: #000000; padding: 10px 12px; border: 3px solid #111827; line-height: 1.2; }
        #v-classic-ent table td { padding: 5px 12px; border: 2px solid #475569; color: #000000; font-weight: 600; line-height: 1.3; }
        #v-classic-ent tr:nth-child(even) { background: #f9fafb; }
        #v-classic-ent .num { font-weight: 600; color: #111827; }

        #v-classic-inv { background: #f8fafc; padding: 50px; color: #0f172a; max-width: 900px; margin: 0 auto; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        #v-classic-inv table th { border-bottom: 2px solid #cbd5e1; padding: 15px; color: #475569; }
        #v-classic-inv table td { border-bottom: 1px solid #e2e8f0; padding: 15px; font-size: 1.05rem; }

        #v-classic-t { background: #fff; padding: 40px; color: #000; }
        #v-classic-t .t-grid { display: grid; grid-template-columns: 1fr 2px 1fr; gap: 20px; }
        #v-classic-t .t-divider { background: #000; width: 2px; }
        #v-classic-t table th, #v-classic-t table td { border-bottom: 1px dotted #ccc; padding: 8px; font-size: 0.9rem; }

        #v-classic-min { background: #fff; padding: 40px; color: #000; }
        #v-classic-min table { border-top: 2px solid #000; border-bottom: 2px solid #000; }
        #v-classic-min th { border-bottom: 1px solid #000; padding: 10px; }
        #v-classic-min td { padding: 10px; border-bottom: 1px solid #eee; }

        #v-classic-mat { background: #f1f5f9; padding: 30px; color: #0f172a; overflow-x: auto; }
        #v-classic-mat table th { background: #e2e8f0; border: 1px solid #cbd5e1; padding: 8px; font-size: 0.8rem; }
        #v-classic-mat table td { background: #fff; border: 1px solid #cbd5e1; padding: 8px; font-size: 0.8rem; }

        #v-classic-bank { background: #fff; padding: 40px; color: #000; border: 1px solid #ccc; font-family: 'Times New Roman', serif; }
        #v-classic-bank .bank-head { border-bottom: 4px double #000; padding-bottom: 10px; margin-bottom: 20px; text-align: center; }
        #v-classic-bank table th { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px; font-weight: bold; }
        #v-classic-bank table td { padding: 10px; }

        #v-classic-strict { background: #fff; padding: 40px; color: #000; }
        #v-classic-strict table { border: 3px solid #000; }
        #v-classic-strict table th, #v-classic-strict table td { border: 1px solid #000; padding: 12px; font-weight: bold; }

        #v-classic-auditor { background: #fff; padding: 40px; color: #000; }
        #v-classic-auditor table th { background: #ccc; border: 1px solid #888; padding: 15px; }
        #v-classic-auditor table td { border: 1px solid #888; padding: 15px; }
        #v-classic-auditor .audit-box { border: 1px dashed #666; height: 30px; width: 60px; display: inline-block; }

        /* Group 2: Modern & Mobile */
        #v-mod-fintech { max-width: 700px; margin: 0 auto; }
        #v-mod-fintech .ft-item { background: rgba(255,255,255,0.03); border-radius: 20px; padding: 20px; display: flex; align-items: center; margin-bottom: 15px; }
        
        #v-mod-cards .cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        #v-mod-cards .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 20px; }

        #v-mod-timeline .tl-item { display: flex; gap: 20px; margin-bottom: 20px; }
        #v-mod-timeline .tl-line { width: 4px; background: var(--primary); border-radius: 4px; position: relative; }
        #v-mod-timeline .tl-line::before { content:''; position: absolute; top:15px; left:-6px; width: 16px; height: 16px; border-radius: 50%; background: #fff; border: 4px solid var(--primary); }
        #v-mod-timeline .tl-content { background: var(--card-bg); padding: 20px; border-radius: 16px; flex-grow: 1; }

        #v-mod-wallet { max-width: 400px; margin: 0 auto; background: #000; border-radius: 30px; padding: 30px 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.8); border: 4px solid #333; }
        #v-mod-wallet .w-card { background: linear-gradient(45deg, #2563eb, #9333ea); border-radius: 20px; padding: 20px; color: #fff; margin-bottom: 20px; box-shadow: 0 10px 20px rgba(147, 51, 234, 0.4); }
        #v-mod-wallet .w-item { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #222; }

        #v-mod-chat { max-width: 600px; margin: 0 auto; background: #0f172a; border-radius: 20px; padding: 20px; border: 1px solid rgba(255,255,255,0.1); }
        #v-mod-chat .msg { padding: 15px 20px; border-radius: 20px; margin-bottom: 15px; max-width: 80%; }
        #v-mod-chat .msg.dr { background: rgba(16,185,129,0.2); align-self: flex-start; margin-left: auto; border-bottom-right-radius: 0; }
        #v-mod-chat .msg.cr { background: rgba(239,68,68,0.2); align-self: flex-end; margin-right: auto; border-bottom-left-radius: 0; }

        #v-mod-widget { max-width: 350px; margin: 0 auto; background: var(--card-bg); border-radius: 16px; padding: 15px; }
        #v-mod-widget .w-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed rgba(255,255,255,0.1); font-size: 0.9rem; }

        #v-mod-swipe { display: flex; gap: 20px; overflow-x: auto; padding: 20px 0; scroll-snap-type: x mandatory; }
        #v-mod-swipe .swipe-card { scroll-snap-align: center; min-width: 300px; background: linear-gradient(180deg, #1e293b, #0f172a); border-radius: 20px; padding: 30px; border: 1px solid rgba(56,189,248,0.3); }

        #v-mod-saas { background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); color: #334155; }
        #v-mod-saas table { border-collapse: separate; border-spacing: 0; width: 100%; }
        #v-mod-saas th { background: #f8fafc; color: #64748b; padding: 16px; font-weight: 600; text-align: right; border-bottom: 1px solid #e2e8f0; }
        #v-mod-saas td { padding: 16px; border-bottom: 1px solid #f1f5f9; }

        /* Group 3: Analytical */
        #v-ana-dash .dash-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px; }
        #v-ana-dash .dash-card { background: var(--card-bg); padding: 20px; border-radius: 16px; text-align: center; border: 1px solid rgba(255,255,255,0.05); }
        
        #v-ana-split { display: grid; grid-template-columns: 300px 1fr; gap: 20px; }
        #v-ana-split .side { background: var(--card-bg); padding: 20px; border-radius: 16px; }
        #v-ana-split .main { background: var(--card-bg); padding: 20px; border-radius: 16px; }

        #v-ana-month .m-group { background: var(--card-bg); margin-bottom: 20px; border-radius: 16px; overflow: hidden; }
        #v-ana-month .m-head { background: rgba(0,0,0,0.5); padding: 15px; font-weight: 800; color: var(--primary); }

        #v-ana-databar td { position: relative; z-index: 2; padding: 15px; }
        #v-ana-databar .bar { position: absolute; right: 0; top: 0; bottom: 0; z-index: -1; opacity: 0.2; }

        #v-ana-neon { background: #000; padding: 40px; border-radius: 20px; border: 1px solid #222; }
        #v-ana-neon td, #v-ana-neon th { border-bottom: 1px solid #111; padding: 15px; }
        #v-ana-neon .glow-dr { color: #0ff; text-shadow: 0 0 10px #0ff; }
        #v-ana-neon .glow-cr { color: #f0f; text-shadow: 0 0 10px #f0f; }

        #v-ana-donut { display: flex; gap: 30px; align-items: center; background: var(--card-bg); padding: 30px; border-radius: 20px; }
        .donut { width: 150px; height: 150px; border-radius: 50%; background: conic-gradient(var(--success) 0% 65%, var(--danger) 65% 100%); display: flex; align-items: center; justify-content: center; }
        .donut-inner { width: 110px; height: 110px; background: var(--card-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; }

        #v-ana-trend { background: var(--card-bg); padding: 30px; border-radius: 20px; }
        .trend-line { height: 100px; border-bottom: 2px solid #555; position: relative; margin-bottom: 40px; }
        .t-point { position: absolute; width: 10px; height: 10px; background: var(--primary); border-radius: 50%; bottom: 0; transform: translate(50%, 50%); }

        #v-ana-cat .cat-box { background: var(--card-bg); border: 1px solid rgba(255,255,255,0.1); padding: 20px; border-radius: 12px; margin-bottom: 15px; }

        #v-ana-heat table { border-collapse: collapse; width: 100%; }
        #v-ana-heat td { padding: 15px; text-align: right; }
        .heat-high { background: rgba(239, 68, 68, 0.4); color: #fff; }
        .heat-mid { background: rgba(239, 68, 68, 0.2); }
        .heat-low { background: rgba(16, 185, 129, 0.2); }

        /* Elegant Toggle Switch */
        .elegant-toggle { position: relative; display: flex; background: rgba(0, 0, 0, 0.4); border-radius: 12px; padding: 4px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: inset 0 2px 6px rgba(0,0,0,0.6); height: 48px; align-items: center; }
        .elegant-toggle input[type="radio"] { display: none; }
        .elegant-toggle label { flex: 1; text-align: center; font-size: 0.95rem; font-weight: 800; color: #64748b; cursor: pointer; z-index: 2; transition: all 0.3s ease; user-select: none; }
        .elegant-toggle label:hover { color: #e2e8f0; }
        .elegant-toggle input[type="radio"]:checked + label { color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
        .elegant-toggle .toggle-bg { position: absolute; top: 4px; right: 4px; bottom: 4px; width: calc(33.333% - 2.66px); background: linear-gradient(135deg, var(--primary), #8b5cf6); border-radius: 8px; z-index: 1; transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 4px 15px rgba(56,189,248,0.4), inset 0 1px 0 rgba(255,255,255,0.2); }
        .elegant-toggle input[id="st_all"]:checked ~ .toggle-bg { transform: translateX(0); }
        .elegant-toggle input[id="st_posted"]:checked ~ .toggle-bg { transform: translateX(-100%); }
        .elegant-toggle input[id="st_unposted"]:checked ~ .toggle-bg { transform: translateX(-200%); }

        /* 2-item toggle variant */
        .elegant-toggle-2 .toggle-bg { width: calc(50% - 4px); }
        .elegant-toggle-2 input[id="date_all"]:checked ~ .toggle-bg { transform: translateX(0); }
        .elegant-toggle-2 input[id="date_custom"]:checked ~ .toggle-bg { transform: translateX(-100%); }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .template-view { display: none !important; }
            .template-view.active { display: block !important; }
            #v-classic-ent { padding: 0 !important; }
            #print-container { background: #fff !important; }
        }
        
        @page {
            @bottom-center {
                content: counter(page) " / " counter(pages);
            }
        }

        /* Custom Searchable Select */
        .custom-select-container { position: relative; width: 100%; font-family: inherit; }
        .custom-select-input {
            width: 100%; padding: 12px; border-radius: 8px; 
            border: 1px solid rgba(255,255,255,0.1); 
            background: rgba(0,0,0,0.2); color: #fff; 
            font-family: inherit; font-size: 1rem;
            outline: none; transition: border-color 0.2s;
        }
        .custom-select-input:focus { border-color: var(--primary); }
        .custom-select-input::placeholder { color: rgba(255,255,255,0.4); }
        .custom-select-dropdown {
            position: absolute; top: 100%; right: 0; left: 0;
            margin-top: 5px; background: #1e293b; border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; max-height: 300px; overflow-y: auto;
            z-index: 1000; display: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }
        .custom-select-dropdown.active { display: block; }
        .cs-group { padding: 8px 12px; font-size: 0.8rem; color: var(--primary); font-weight: bold; background: rgba(0,0,0,0.2); }
        .cs-option { padding: 10px 15px; cursor: pointer; transition: background 0.2s; color: #f8fafc; font-size: 0.95rem; border-bottom: 1px solid rgba(255,255,255,0.02); }
        .cs-option:last-child { border-bottom: none; }
        .cs-option:hover { background: rgba(255,255,255,0.05); }
        .cs-option-desc { font-size: 0.75rem; color: var(--text-muted); margin-top: 3px; }
    </style>
</head>
<body>

<div class="container">
    <div class="no-print" style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
        <button onclick="history.back()" class="btn-secondary" style="padding: 8px 16px; font-size: 0.9rem; border-radius: 8px;">&rarr; عودة للبرنامج</button>
        <button onclick="openSettingsDrawer()" class="btn-primary" style="padding: 10px 20px; font-size: 1rem; border-radius: 8px; font-weight: bold; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #38bdf8, #2563eb); color: #fff; box-shadow: 0 4px 15px rgba(56,189,248,0.3); transition: 0.3s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">⚙️ إعدادات التقرير</button>
    </div>

    <!-- 12 Lightweight Template Switcher -->
    <div class="light-switcher no-print" style="margin-bottom: 25px; background: rgba(0,0,0,0.2); border-radius: 12px; padding: 15px; border: 1px solid rgba(255,255,255,0.05);">
        <div style="display: flex; gap: 10px; overflow-x: auto; padding: 5px 0; border-bottom: 1px solid rgba(255,255,255,0.05); align-items: center;">
            <strong style="color: var(--text-muted); font-size: 0.9rem; min-width: 120px;">أشكال كلاسيكية:</strong>
            <button class="v-btn btn-secondary active" onclick="changeView('v-classic-ent', this)" style="border-radius: 20px; padding: 6px 12px; white-space: nowrap;">🏢 مؤسسي</button>
            <button class="v-btn btn-secondary" onclick="changeView('v-classic-t', this, '#ffffff')" style="border-radius: 20px; padding: 6px 12px; white-space: nowrap;">⚖️ دفتر T</button>
            <button class="v-btn btn-secondary" onclick="changeView('v-classic-min', this, '#ffffff')" style="border-radius: 20px; padding: 6px 12px; white-space: nowrap;">⬜ مبسط</button>
            <button class="v-btn btn-secondary" onclick="changeView('v-classic-bank', this, '#ffffff')" style="border-radius: 20px; padding: 6px 12px; white-space: nowrap;">🏦 بنكي</button>
        </div>
        <div style="display: flex; gap: 10px; overflow-x: auto; padding: 10px 0 0 0; align-items: center;">
            <strong style="color: var(--text-muted); font-size: 0.9rem; min-width: 120px;">موبايل رفيع:</strong>
            <button class="v-btn btn-secondary" onclick="changeView('v-mod-fintech', this)" style="border-radius: 20px; padding: 6px 12px; white-space: nowrap;">💳 بنكي حديث</button>
            <button class="v-btn btn-secondary" onclick="changeView('v-mod-cards', this)" style="border-radius: 20px; padding: 6px 12px; white-space: nowrap;">🗂️ بطاقات</button>
            <button class="v-btn btn-secondary" onclick="changeView('v-mod-wallet', this, '#111')" style="border-radius: 20px; padding: 6px 12px; white-space: nowrap;">👛 محفظة</button>
            <button class="v-btn btn-secondary" onclick="changeView('v-mod-chat', this)" style="border-radius: 20px; padding: 6px 12px; white-space: nowrap;">💬 محادثة</button>
        </div>
    </div>

    <!-- The Visual Switcher / Simple Filter -->
    <div class="visual-switcher no-print" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 25px; margin-bottom: 30px; display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex-grow: 1; min-width: 250px;">
            <label style="display: block; margin-bottom: 8px; font-weight: bold; color: var(--text-muted);">اسم الحساب:</label>
            <div class="custom-select-container" id="cs-container" style="display: flex; gap: 5px; align-items: stretch;">
                <div style="position: relative; flex: 1;">
                    <input type="text" class="custom-select-input" id="cs-input" placeholder="ابحث في الحسابات (عملاء، موردين، بنوك...)" autocomplete="off" onclick="toggleCsDropdown()" onkeyup="filterCs()" oninput="filterCs()">
                    <input type="hidden" id="accountSelect" value="all_accounts">
                    <div class="custom-select-dropdown" id="cs-dropdown">
                        <!-- Populated by JS -->
                    </div>
                </div>
                <button type="button" class="btn-action" onclick="runAISmartSearch()" id="btn-smart-search" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); color:white; border:none; padding: 0 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(139, 92, 246, 0.3); font-weight: bold; cursor: pointer; transition: 0.3s; white-space: nowrap;" title="البحث بالمعنى (الذكاء الاصطناعي)">🤖 ذكي</button>
            </div>
        </div>
        </div>
        <div style="width: 280px;">
            <label style="display: block; margin-bottom: 8px; font-weight: bold; color: var(--text-muted);">حالة القيود:</label>
            <div class="elegant-toggle">
                <input type="radio" id="st_all" name="postingStatus" value="all" checked onchange="generateSimpleReport()">
                <label for="st_all">الكل</label>
                
                <input type="radio" id="st_posted" name="postingStatus" value="posted" onchange="generateSimpleReport()">
                <label for="st_posted">مرحل</label>
                
                <input type="radio" id="st_unposted" name="postingStatus" value="unposted" onchange="generateSimpleReport()">
                <label for="st_unposted">غير مرحل</label>
                
                <div class="toggle-bg"></div>
            </div>
        </div>
        <div style="width: 220px;">
            <label style="display: block; margin-bottom: 8px; font-weight: bold; color: var(--text-muted);">نطاق التقرير:</label>
            <div class="elegant-toggle elegant-toggle-2">
                <input type="radio" id="date_all" name="dateRange" value="all" checked onchange="toggleDateInputs()">
                <label for="date_all">المدة كاملة</label>
                
                <input type="radio" id="date_custom" name="dateRange" value="custom" onchange="toggleDateInputs()">
                <label for="date_custom">تحديد فترة</label>
                
                <div class="toggle-bg"></div>
            </div>
        </div>
        <div style="width: 220px;">
            <label style="display: block; margin-bottom: 8px; font-weight: bold; color: var(--text-muted);">خط الأرقام (إنجليزي):</label>
            <div class="elegant-toggle elegant-toggle-2">
                <input type="radio" id="font_jb" name="numFontTop" value="JetBrains Mono" checked onchange="setNumFont(this.value)">
                <label for="font_jb" style="font-family:'JetBrains Mono', monospace; font-size:0.85rem;" title="للمحاسبة والترتيب الدقيق">JetBrains</label>
                
                <input type="radio" id="font_inter" name="numFontTop" value="Inter" onchange="setNumFont(this.value)">
                <label for="font_inter" style="font-family:'Inter', sans-serif; font-size:0.85rem;" title="للأناقة والوضوح">Inter</label>
                
                <div class="toggle-bg"></div>
            </div>
        </div>
        <div id="customDateRange" style="display: none; gap: 15px; align-items: flex-end; animation: fadeIn 0.3s ease;">
            <div style="width: 150px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: var(--text-muted);">من تاريخ:</label>
                <input type="date" id="fromDate" onchange="generateSimpleReport()" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff; font-family: inherit; font-size: 1rem;">
            </div>
            <div style="width: 150px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: var(--text-muted);">إلى تاريخ:</label>
                <input type="date" id="toDate" onchange="generateSimpleReport()" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff; font-family: inherit; font-size: 1rem;">
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    <script>

        // Dynamic Company Header
        function loadCompanyHeader() {
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                
                const cName = document.getElementById('hdr-c-name');
                const cReg = document.getElementById('hdr-c-reg');
                const cVat = document.getElementById('hdr-c-vat');
                const cAddr = document.getElementById('hdr-c-addr');
                const cLogo = document.getElementById('company-logo');

                if (cName && cfg.companyName) cName.innerText = cfg.companyName;
                if (cReg && cfg.regNumber) cReg.innerText = 'سجل تجاري: ' + cfg.regNumber;
                if (cVat && cfg.vat) cVat.innerText = 'الرقم الضريبي: ' + cfg.vat;
                if (cAddr && cfg.address) cAddr.innerText = cfg.address;
                
                if (cLogo && cfg.logoBase64) {
                    cLogo.src = cfg.logoBase64;
                }
            } catch(e) {}
        }
        document.addEventListener('DOMContentLoaded', loadCompanyHeader);

        let accountsData = [];

        function loadAccountsFromDB() {
            accountsData = [{ id: 'all_accounts', name: '🌟 جميع الحسابات (شامل)', group: 'عام' }];
            
            // 1. العملاء
            const customers = (JSON.parse(localStorage.getItem('erp_customers_v1') || '[]')).filter(x => !x.deleted);
            customers.forEach(c => {
                accountsData.push({ id: c.id, name: c.name, phone: c.phone || '', group: 'العملاء' });
            });

            // 2. الموردين
            const suppliers = (JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]')).filter(x => !x.deleted);
            suppliers.forEach(s => {
                accountsData.push({ id: s.id, name: s.name, phone: s.phone || '', group: 'الموردين' });
            });

            // 3. البنوك
            const banks = (JSON.parse(localStorage.getItem('erp_banks_v1') || '[]')).filter(x => !x.deleted);
            banks.forEach(b => {
                accountsData.push({ id: b.id, name: b.bankName || b.name || 'بنك غير محدد', phone: b.accountNumber || '', group: 'البنوك' });
            });

            // 4. الخزن
            const safes = (JSON.parse(localStorage.getItem('erp_safes_v1') || '[]')).filter(x => !x.deleted);
            safes.forEach(s => {
                accountsData.push({ id: s.id, name: s.safeName || s.name || 'خزينة غير محددة', phone: '', group: 'الخزن' });
            });

            // 5. الحسابات والمصروفات
            const generalAccounts = (JSON.parse(localStorage.getItem('erp_accounts_v1') || '[]')).filter(x => !x.deleted);
            generalAccounts.forEach((acc, idx) => {
                accountsData.push({ id: acc.id, name: acc.name || 'غير محدد', phone: '', group: 'المصروفات والحسابات' });
            });
        }

        function initCsDropdown() {
            loadAccountsFromDB();
            const dd = document.getElementById('cs-dropdown');
            let html = '';
            const groups = [...new Set(accountsData.map(a => a.group))];
            groups.forEach(g => {
                html += `<div class="cs-group" data-group="${g}">${g}</div>`;
                accountsData.filter(a => a.group === g).forEach(a => {
                    const desc = a.phone ? `<div class="cs-option-desc">رقم التواصل: ${a.phone}</div>` : '';
                    html += `<div class="cs-option" data-id="${a.id}" data-name="${a.name}" onclick="selectCsOption('${a.id}', '${a.name}')">
                                ${a.name}
                                ${desc}
                             </div>`;
                });
            });
            dd.innerHTML = html;
            
            document.addEventListener('click', function(e) {
                if(!e.target.closest('#cs-container')) {
                    document.getElementById('cs-dropdown').classList.remove('active');
                }
            });

            document.getElementById('cs-input').value = accountsData[0].name;
        }

        function toggleCsDropdown() {
            document.getElementById('cs-dropdown').classList.toggle('active');
            filterCs();
        }

        function selectCsOption(id, name) {
            document.getElementById('cs-input').value = name;
            document.getElementById('accountSelect').value = id;
            document.getElementById('cs-dropdown').classList.remove('active');
            generateSimpleReport();
        }

        function filterCs() {
            const rawQuery = document.getElementById('cs-input').value;
            const query = window.normalizeArabic ? window.normalizeArabic(rawQuery) : rawQuery.toLowerCase();
            const dd = document.getElementById('cs-dropdown');
            const options = dd.querySelectorAll('.cs-option');
            const groups = dd.querySelectorAll('.cs-group');
            
            if (query === '') {
                document.getElementById('accountSelect').value = '';
            }

            let groupHasVisible = {};
            groups.forEach(g => groupHasVisible[g.getAttribute('data-group')] = false);
            
            options.forEach(opt => {
                const textRaw = opt.innerText;
                const text = window.normalizeArabic ? window.normalizeArabic(textRaw) : textRaw.toLowerCase();
                const grp = accountsData.find(a => a.id === opt.getAttribute('data-id')).group;
                if (text.includes(query)) {
                    opt.style.display = 'block';
                    groupHasVisible[grp] = true;
                } else {
                    opt.style.display = 'none';
                }
            });
            
            groups.forEach(g => {
                const grpName = g.getAttribute('data-group');
                g.style.display = groupHasVisible[grpName] ? 'block' : 'none';
            });
            
            dd.classList.add('active');
        }

        function toggleDateInputs() {
            const isCustom = document.getElementById('date_custom').checked;
            const container = document.getElementById('customDateRange');
            if(isCustom) {
                container.style.display = 'flex';
                const dtFrom = document.getElementById('fromDate');
                const dtTo = document.getElementById('toDate');
                if(!dtFrom.value) {
                    const today = new Date();
                    dtFrom.valueAsDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    dtTo.valueAsDate = today;
                }
            } else {
                container.style.display = 'none';
                document.getElementById('fromDate').value = '';
                document.getElementById('toDate').value = '';
            }
            generateSimpleReport();
        }

        function updatePrintTimestamp() {
            const timestampEl = document.getElementById('print-timestamp');
            if (timestampEl) {
                const now = new Date();
                const dOpts = { year: 'numeric', month: '2-digit', day: '2-digit' };
                const tOpts = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
                timestampEl.innerText = now.toLocaleDateString('en-GB', dOpts) + '  |  ' + now.toLocaleTimeString('ar-SA', tOpts);
            }
        }

        window.addEventListener('beforeprint', updatePrintTimestamp);

        
    function getAccountName(id) {
        if(!window.allAccountsCache) {
            let accs = [];
            try { accs = accs.concat(JSON.parse(localStorage.getItem('erp_accounts_v1'))||[]); }catch(e){}
            try { accs = accs.concat(JSON.parse(localStorage.getItem('erp_customers_v1'))||[]); }catch(e){}
            try { accs = accs.concat(JSON.parse(localStorage.getItem('erp_suppliers_v1'))||[]); }catch(e){}
            try { accs = accs.concat(JSON.parse(localStorage.getItem('erp_banks_v1'))||[]); }catch(e){}
            try { accs = accs.concat(JSON.parse(localStorage.getItem('erp_safes_v1'))||[]); }catch(e){}
            window.allAccountsCache = accs;
        }
        const found = window.allAccountsCache.find(a => a.id === id);
        return found ? (found.name || found.bankName || found.safeName || 'غير محدد') : id;
    }

    function loadTransactions(accountId) {
        transactions = [];
        if (!accountId) return;
        
        let vouchers = [];
        try {
            vouchers = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
        } catch(e) {}
        
        vouchers.sort((a, b) => new Date(a.date) - new Date(b.date));
        
        let runningBalance = 0;
        
        vouchers.forEach(v => {
            if (!v.rows) return;
            const accRows = v.rows.filter(r => r.accountId === accountId);
            if (accRows.length === 0) return;
            
            const otherRows = v.rows.filter(r => r.accountId !== accountId);
            let contraAccount = otherRows.length > 0 ? (otherRows.map(r => getAccountName(r.accountId)).join('، ')) : '-';
            if(contraAccount.length > 30) contraAccount = contraAccount.substring(0, 30) + '...';
            
            accRows.forEach(r => {
                const dr = parseFloat(r.debit) || 0;
                const cr = parseFloat(r.credit) || 0;
                runningBalance += (dr - cr);
                
                let dateStr = v.date || '-';
                if(dateStr !== '-') {
                    const d = new Date(dateStr);
                    if(!isNaN(d)) dateStr = d.toLocaleDateString('en-GB'); // dd/mm/yyyy
                }

                transactions.push({
                    date: dateStr,
                    ref: v.refNo || '-',
                    voucher: v.id || '-',
                    desc: r.description || v.notes || 'حركة مالية',
                    contraAccount: contraAccount,
                    notes: v.notes || '',
                    costCenter: v.costCenter || '-',
                    dr: dr,
                    cr: cr,
                    bal: runningBalance,
                    curr: v.currency || 'SDG',
                    rate: parseFloat(v.currencyRate) || 1,
                    dr_fc: dr / (parseFloat(v.currencyRate) || 1),
                    cr_fc: cr / (parseFloat(v.currencyRate) || 1),
                    bal_fc: runningBalance / (parseFloat(v.currencyRate) || 1)
                });
            });
        });
    }

    function generateSimpleReport() {
            const accSelect = document.getElementById('accountSelect');
            const csInput = document.getElementById('cs-input');
            const container = document.getElementById('print-container');
            const titleEl = document.getElementById('ent-rep-title');
            
            if(accSelect && accSelect.value) {
                container.style.display = 'block';
                const accName = csInput ? csInput.value : (accountsData ? accountsData[0].name : '');
                if(titleEl) {
                    titleEl.innerText = `كشف حساب - ${accName}`;
                }
                updatePrintTimestamp();
                
                // Fetch real data
                if(typeof loadTransactions === 'function') {
                    loadTransactions(accSelect.value);
                    if(typeof renderEnterprise === 'function') renderEnterprise();
                    if(typeof renderOthers === 'function') renderOthers();
                }
            }
        }
    </script>

    <!-- Actions Panel -->
    <div class="actions-panel no-print" style="flex-wrap: wrap;">
        <button class="btn-action btn-secondary" onclick="window.print()" title="طباعة">🖨️ طباعة وتوثيق</button>
        <button class="btn-action btn-primary" onclick="exportPDF()" title="تصدير PDF">📄 تصدير PDF</button>
        <button class="btn-action" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none;" onclick="exportExcel()" title="تصدير إكسل">📊 إكسل (Excel)</button>
        <button class="btn-action" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: #fff; border: none;" onclick="shareEmail()" title="مشاركة عبر الإيميل">📧 إيميل</button>
        <button class="btn-action" style="background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; border: none;" onclick="shareWhatsApp()" title="مشاركة عبر واتساب">💬 واتساب</button>
    </div>

    <!-- ==============================================================
         Dynamic Content Container
         ============================================================== -->
    <div id="print-container" style="padding: 20px; border-radius: 16px;">
        
        <!-- 1. Enterprise Classic (Advanced Customizable) -->
        <div id="v-classic-ent" class="template-view active classic-base">
            
            <!-- Company Header Area (approx 4cm height) -->
            <div class="report-header" style="display: flex; justify-content: space-between; align-items: flex-start; height: 4cm; border-bottom: 3px solid #111827; margin-bottom: 20px; padding-bottom: 15px; font-family: 'Tajawal', sans-serif;">
                
                <!-- Right Side: Company Details -->
                <div style="flex: 1; text-align: right; color: #111827;">
                    <h2 id="hdr-c-name" style="margin: 0 0 5px 0; font-size: 1.6rem; font-weight: 900;">شركة الأفق للتجارة</h2>
                    <p id="hdr-c-reg" style="margin: 0 0 3px 0; font-size: 0.95rem; font-weight: 600;">سجل تجاري: 1010101010</p>
                    <p id="hdr-c-vat" style="margin: 0 0 3px 0; font-size: 0.95rem; font-weight: 600;">الرقم الضريبي: 300000000000003</p>
                    <p id="hdr-c-addr" style="margin: 0 0 3px 0; font-size: 0.95rem; font-weight: 600;">الرياض، المملكة العربية السعودية</p>
                </div>

                <!-- Center Side: Report Title -->
                <div style="flex: 1; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                    <h2 id="ent-rep-title" style="margin: 0; padding: 10px 20px; background: #f8fafc; border-radius: 8px; border: 2px solid #111827; font-size: 1.4rem; color: #111827;">كشف حساب مؤسسي ديناميكي</h2>
                </div>

                <!-- Left Side: Logo Area -->
                <div style="flex: 1; text-align: left; display: flex; align-items: center; justify-content: flex-end; height: 100%;">
                    <!-- Logo Area (4cm width) -->
                    <div style="width: 4cm; height: 100%; border: 1px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: #f8fafc; overflow: hidden; position: relative;" title="منطقة الشعار - يتم جلب الشعار من البيانات الأساسية">
                        <img id="company-logo" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAgMTAwIj48Y2lyY2xlIGN4PSI1MCIgY3k9IjUwIiByPSI0MCIgZmlsbD0iI2UydThmMCIvPjx0ZXh0IHg9IjUwIiB5PSI1NSIgZm9udC1zaXplPSIyMCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY0NzQ4YiI+TE9HTzwvdGV4dD48L3N2Zz4=" alt="شعار الشركة" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                    </div>
                </div>
            </div>

            <table id="ent-table">
                <thead id="ent-thead">
                    <!-- Rendered by JS -->
                </thead>
                <tbody id="ent-tbody">
                    <!-- Rendered by JS -->
                </tbody>
            </table>

            <!-- Advanced Analytics Dashboard -->
            <div id="analytics-dashboard" class="no-print" style="margin-top: 40px; padding: 25px; background: rgba(15, 23, 42, 0.6); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
                    <h3 style="margin: 0; color: #f8fafc; display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5rem;">📈</span> لوحة التحليل الديناميكي (Smart Analytics)
                    </h3>
                    <div style="color: #94a3b8; font-size: 0.9rem;">يتم توليد البيانات والرسومات البيانية تلقائياً بناءً على الحركات أعلاه</div>
                </div>
                
                </div>
                
                <!-- KPI Summary Cards -->
                <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 25px;">
                    <div style="background: linear-gradient(135deg, rgba(56,189,248,0.1), rgba(0,0,0,0.3)); border-radius: 12px; padding: 15px; border: 1px solid rgba(56,189,248,0.2); text-align: center;">
                        <div style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 5px; font-weight: bold;">الرصيد النهائي</div>
                        <div id="kpi-bal" class="num" style="color: #38bdf8; font-size: 1.5rem; font-weight: 900;">0.00</div>
                    </div>
                    <div style="background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(0,0,0,0.3)); border-radius: 12px; padding: 15px; border: 1px solid rgba(16,185,129,0.2); text-align: center;">
                        <div style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 5px; font-weight: bold;">إجمالي المدين</div>
                        <div id="kpi-dr" class="num" style="color: #10b981; font-size: 1.5rem; font-weight: 900;">0.00</div>
                    </div>
                    <div style="background: linear-gradient(135deg, rgba(244,63,94,0.1), rgba(0,0,0,0.3)); border-radius: 12px; padding: 15px; border: 1px solid rgba(244,63,94,0.2); text-align: center;">
                        <div style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 5px; font-weight: bold;">إجمالي الدائن</div>
                        <div id="kpi-cr" class="num" style="color: #f43f5e; font-size: 1.5rem; font-weight: 900;">0.00</div>
                    </div>
                    <div style="background: linear-gradient(135deg, rgba(168,85,247,0.1), rgba(0,0,0,0.3)); border-radius: 12px; padding: 15px; border: 1px solid rgba(168,85,247,0.2); text-align: center;">
                        <div style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 5px; font-weight: bold;">عدد الحركات</div>
                        <div id="kpi-count" class="num" style="color: #a855f7; font-size: 1.5rem; font-weight: 900;">0</div>
                    </div>
                    <div style="background: linear-gradient(135deg, rgba(251,191,36,0.1), rgba(0,0,0,0.3)); border-radius: 12px; padding: 15px; border: 1px solid rgba(251,191,36,0.2); text-align: center;">
                        <div style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 5px; font-weight: bold;">متوسط الحركة</div>
                        <div id="kpi-avg" class="num" style="color: #fbbf24; font-size: 1.5rem; font-weight: 900;">0.00</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div style="background: rgba(0,0,0,0.3); border-radius: 12px; padding: 15px; border: 1px solid rgba(255,255,255,0.05); position: relative;">
                        <h4 style="margin: 0 0 10px 0; color: #38bdf8; font-size: 1rem;">تحليل حركة الرصيد (Balance Trend)</h4>
                        <div id="chart-balance-trend" style="min-height: 250px;"></div>
                    </div>
                    <div style="background: rgba(0,0,0,0.3); border-radius: 12px; padding: 15px; border: 1px solid rgba(255,255,255,0.05); position: relative;">
                        <h4 style="margin: 0 0 10px 0; color: #10b981; font-size: 1rem;">تحليل التدفقات (Cash Flow)</h4>
                        <div id="chart-cash-flow" style="min-height: 250px;"></div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 20px;">
                    <div style="background: rgba(0,0,0,0.3); border-radius: 12px; padding: 15px; border: 1px solid rgba(255,255,255,0.05); position: relative; display: flex; flex-direction: column;">
                        <h4 style="margin: 0 0 10px 0; color: #f43f5e; font-size: 1rem;">التحليل النسبي (Distribution)</h4>
                        <div id="chart-distribution" style="flex-grow: 1; display: flex; align-items: center; justify-content: center;"></div>
                    </div>
                    <div style="background: rgba(0,0,0,0.3); border-radius: 12px; padding: 15px; border: 1px solid rgba(255,255,255,0.05); position: relative;">
                        <h4 style="margin: 0 0 10px 0; color: #a855f7; font-size: 1rem;">مؤشر النشاط الزمني (Velocity)</h4>
                        <div id="chart-velocity" style="min-height: 250px;"></div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div style="background: rgba(0,0,0,0.3); border-radius: 12px; padding: 15px; border: 1px solid rgba(255,255,255,0.05); position: relative;">
                        <h4 style="margin: 0 0 10px 0; color: #fbbf24; font-size: 1rem;">مخطط الشلال التراكمي (Waterfall)</h4>
                        <div id="chart-waterfall" style="min-height: 250px;"></div>
                    </div>
                    <div style="background: rgba(0,0,0,0.3); border-radius: 12px; padding: 15px; border: 1px solid rgba(255,255,255,0.05); position: relative;">
                        <h4 style="margin: 0 0 10px 0; color: #0ea5e9; font-size: 1rem;">التحليل المبعثر (Scatter Anomalies)</h4>
                        <div id="chart-scatter" style="min-height: 250px;"></div>
                    </div>
                </div>
            </div>
            
            <!-- Report Footer Area (Print Info) -->
            <div class="report-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 40px; padding-top: 15px; border-top: 2px dashed #94a3b8; font-family: 'Tajawal', sans-serif; color: #475569;">
                <div style="font-size: 0.95rem; font-weight: bold;">
                    <span style="color: #111827;">مدير النظام</span>
                </div>
                <div style="font-size: 0.95rem; font-weight: bold;">
                    <span id="print-timestamp" style="color: #111827; direction: ltr; display: inline-block;"></span>
                </div>
            </div>
        </div>

        <!-- HTML structures for the rest of the 24 models to be populated by JS -->
        <div id="v-classic-inv" class="template-view classic-base"><h1 style="color:var(--primary); font-size:3rem; margin:0;">INVOICE STATEMENT</h1><h2 style="color:#111827;">شركة الأفق للتجارة</h2><br><table><thead><tr><th>التاريخ</th><th>البيان</th><th>المبلغ</th><th>الرصيد</th></tr></thead><tbody id="tbl-inv"></tbody></table></div>
        <div id="v-classic-t" class="template-view"><h2 style="text-align:center;">حـ/ شركة الأفق للتجارة</h2><div class="t-grid"><div class="t-side"><h3 style="background:#eee; text-align:center;">منه (مدين)</h3><table id="tbl-t-dr"></table></div><div class="t-divider"></div><div class="t-side"><h3 style="background:#eee; text-align:center;">له (دائن)</h3><table id="tbl-t-cr"></table></div></div></div>
        <div id="v-classic-min" class="template-view classic-base"><h2>كشف حساب مبسط</h2><table><thead><tr><th>التاريخ</th><th>البيان</th><th>مدين</th><th>دائن</th><th>الرصيد</th></tr></thead><tbody id="tbl-min"></tbody></table></div>
        <div id="v-classic-mat" class="template-view classic-base"><h2>مصفوفة التفاصيل</h2><table><thead><tr><th>التاريخ</th><th>البيان</th><th>مركز التكلفة</th><th>الملاحظات</th><th>مدين</th><th>دائن</th><th>الرصيد</th></tr></thead><tbody id="tbl-mat"></tbody></table></div>
        <div id="v-classic-bank" class="template-view classic-base"><div class="bank-head"><h1>كشف حساب بنكي - BANK STATEMENT</h1><p>Customer: Al-Ofuq Co.</p></div><table><thead><tr><th>Date</th><th>Description</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead><tbody id="tbl-bank"></tbody></table></div>
        <div id="v-classic-strict" class="template-view classic-base"><h2>شبكة التدقيق الصارمة</h2><table><thead><tr><th>التاريخ</th><th>البيان</th><th>مدين</th><th>دائن</th><th>الرصيد</th></tr></thead><tbody id="tbl-strict"></tbody></table></div>
        <div id="v-classic-auditor" class="template-view classic-base"><h2>نموذج المراجع القانوني</h2><table><thead><tr><th>التاريخ</th><th>البيان</th><th>المبلغ</th><th>الرصيد</th><th>مطابقة المراجع</th></tr></thead><tbody id="tbl-aud"></tbody></table></div>
        
        <div id="v-mod-fintech" class="template-view"><div style="text-align:center; margin-bottom:30px;"><div style="color:var(--text-muted);">الرصيد المتوفر</div><div class="num" style="font-size:4rem; font-weight:900;">9,500.00</div></div><div id="tbl-fintech"></div></div>
        <div id="v-mod-cards" class="template-view"><div class="cards-grid" id="tbl-cards"></div></div>
        <div id="v-mod-timeline" class="template-view"><div id="tbl-timeline"></div></div>
        <div id="v-mod-wallet" class="template-view"><div class="w-card"><div style="opacity:0.8;">My Wallet Balance</div><div class="num" style="font-size:2.5rem; font-weight:900;">9,500.00</div></div><div id="tbl-wallet"></div></div>
        <div id="v-mod-chat" class="template-view"><div style="text-align:center; color:var(--text-muted); margin-bottom:20px;">اليوم</div><div id="tbl-chat" style="display:flex; flex-direction:column;"></div></div>
        <div id="v-mod-widget" class="template-view"><h3 style="margin-top:0;">الملخص</h3><div id="tbl-widget"></div><div class="w-row" style="border:none; font-weight:bold; font-size:1.5rem; margin-top:10px;"><span>الرصيد:</span><span class="num val-bal">9,500.00</span></div></div>
        <div id="v-mod-swipe" class="template-view"><div id="tbl-swipe" style="display:flex; gap:20px;"></div></div>
        <div id="v-mod-saas" class="template-view classic-base"><h2 style="color:#0f172a;">Account Statement</h2><table><thead><tr><th>Date</th><th>Description</th><th>Amount</th><th>Balance</th></tr></thead><tbody id="tbl-saas"></tbody></table></div>

        <div id="v-ana-dash" class="template-view classic-base"><div class="dash-grid"><div class="dash-card"><div>إجمالي المدين</div><h2 class="num val-dr">19,500.00</h2></div><div class="dash-card"><div>إجمالي الدائن</div><h2 class="num val-cr">10,000.00</h2></div><div class="dash-card"><div>الرصيد الصافي</div><h2 class="num val-bal">9,500.00</h2></div></div><table><thead><tr><th>التاريخ</th><th>البيان</th><th>المبلغ</th></tr></thead><tbody id="tbl-dash"></tbody></table></div>
        <div id="v-ana-split" class="template-view classic-base"><div class="side"><h2>شركة الأفق</h2><br><p>الرصيد:</p><h1 class="num val-bal">9,500.00</h1></div><div class="main"><table><thead><tr><th>التاريخ</th><th>البيان</th><th>المبلغ</th></tr></thead><tbody id="tbl-split"></tbody></table></div></div>
        <div id="v-ana-month" class="template-view classic-base"><div class="m-group"><div class="m-head">يونيو 2026</div><table><thead><tr><th>التاريخ</th><th>البيان</th><th>المبلغ</th></tr></thead><tbody id="tbl-month"></tbody></table></div></div>
        <div id="v-ana-databar" class="template-view classic-base"><table><thead><tr><th>التاريخ</th><th>البيان</th><th>مدين</th><th>دائن</th></tr></thead><tbody id="tbl-databar"></tbody></table></div>
        <div id="v-ana-neon" class="template-view classic-base"><h2 style="color:#fff; text-shadow:0 0 10px #fff;">// NEON_LEDGER</h2><table><thead><tr><th>DATE</th><th>DESC</th><th>DEBIT</th><th>CREDIT</th></tr></thead><tbody id="tbl-neon"></tbody></table></div>
        <div id="v-ana-donut" class="template-view classic-base"><div class="donut"><div class="donut-inner num">9,500</div></div><div style="flex-grow:1;"><table><thead><tr><th>التاريخ</th><th>البيان</th><th>المبلغ</th></tr></thead><tbody id="tbl-donut"></tbody></table></div></div>
        <div id="v-ana-trend" class="template-view classic-base"><div class="trend-line"><div class="t-point" style="left:10%; bottom:15px;"></div><div class="t-point" style="left:50%; bottom:60px;"></div><div class="t-point" style="left:90%; bottom:20px;"></div></div><table><thead><tr><th>التاريخ</th><th>البيان</th><th>المبلغ</th></tr></thead><tbody id="tbl-trend"></tbody></table></div>
        <div id="v-ana-cat" class="template-view"><div class="cat-box"><h3 class="val-dr">المبيعات (مدين)</h3><div id="tbl-cat-dr"></div></div><div class="cat-box"><h3 class="val-cr">المقبوضات (دائن)</h3><div id="tbl-cat-cr"></div></div></div>
        <div id="v-ana-heat" class="template-view classic-base"><table><thead><tr><th>التاريخ</th><th>البيان</th><th>مدين</th><th>دائن</th></tr></thead><tbody id="tbl-heat"></tbody></table></div>

    </div>
</div>

<!-- Context Menu Element (Side Drawer) -->
<div id="drawerOverlay" class="drawer-overlay" onclick="closeMenu()"></div>
<div id="contextMenu" class="context-menu no-print">
    <div class="c-drag-header" id="drag-header">
        <span>⚙️ إعدادات التقرير</span>
        <div style="display: flex; gap: 10px; align-items: center;">
            <span class="c-close" onclick="closeMenu()">✖</span>
        </div>
    </div>
    
    <div class="c-body">
        <div class="c-title">تخصيص الأعمدة والأرقام (كل عمود مستقل)</div>
        <div id="col-toggles-container">
            <!-- Rendered by JS -->
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin:15px 20px 5px 20px; padding:10px; background:rgba(255,255,255,0.05); border-radius:8px;">
            <span style="color:#f8fafc; font-size:0.9rem; font-weight:bold;">خط الأرقام:</span>
            <div style="display:flex; gap:5px;" id="num-font-toggles">
                <button class="c-btn-small" style="padding:4px 12px; font-weight:bold; font-family:'JetBrains Mono', monospace; font-size:0.8rem;" onclick="setNumFont('JetBrains Mono')">JetBrains</button>
                <button class="c-btn-small" style="padding:4px 12px; font-weight:bold; font-family:'Inter', sans-serif; font-size:0.8rem;" onclick="setNumFont('Inter')">Inter</button>
            </div>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin:15px 20px 5px 20px; padding:10px; background:rgba(255,255,255,0.05); border-radius:8px;">
            <span style="color:#f8fafc; font-size:0.9rem; font-weight:bold;">الخانات العشرية:</span>
            <div style="display:flex; gap:5px;" id="decimals-toggles">
                <button class="c-btn-small" style="padding:4px 12px; font-weight:bold; background:rgba(255,255,255,0.1); color:#cbd5e1;" onclick="setDecimals(0)">0</button>
                <button class="c-btn-small" style="padding:4px 12px; font-weight:bold; background:rgba(255,255,255,0.1); color:#cbd5e1;" onclick="setDecimals(2)">2</button>
                <button class="c-btn-small" style="padding:4px 12px; font-weight:bold; background:rgba(255,255,255,0.1); color:#cbd5e1;" onclick="setDecimals(3)">3</button>
            </div>
        </div>
        <button class="btn-save-layout" onclick="saveLayout()">💾 حفظ التصميم النهائي</button>
    </div>
</div>

<div id="rowContextMenu" class="context-menu no-print" style="width: 260px;">
    <div class="c-drag-header">
        <span>📄 خيارات السند</span>
        <span class="c-close" onclick="closeRowMenu()">✖</span>
    </div>
    <div class="c-body" style="padding: 15px;">
        <button class="btn-save-layout" style="margin:5px 0; padding:12px;" onclick="viewVoucher()">👁️ عرض أصل السند</button>
        <button class="btn-save-layout" style="margin:10px 0 5px 0; padding:12px; background:linear-gradient(135deg, #f59e0b, #d97706);" onclick="printVoucher()">🖨️ طباعة السند</button>
    </div>
</div>

<style>
/* New Context Menu Custom Design - Side Drawer */
#contextMenu.context-menu {
    background: linear-gradient(135deg, #061121 0%, #0c1f36 100%);
    border: none;
    border-left: 1px solid rgba(56, 189, 248, 0.3);
    box-shadow: -10px 0 40px rgba(0, 0, 0, 0.8);
    position: fixed;
    top: 0 !important;
    right: 0 !important;
    left: auto !important;
    bottom: 0 !important;
    width: 420px;
    max-width: 90vw;
    height: 100vh !important;
    border-radius: 0;
    transform: translateX(100%);
    transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1), visibility 0.4s;
    display: flex !important;
    visibility: hidden;
    flex-direction: column;
    z-index: 10001;
    animation: none !important;
}
#contextMenu.context-menu.active {
    transform: translateX(0);
    visibility: visible;
}
.drawer-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
    z-index: 10000; opacity: 0; visibility: hidden; transition: 0.4s ease;
}
.drawer-overlay.active {
    opacity: 1; visibility: visible;
}
#contextMenu .c-drag-header {
    background: transparent;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    text-shadow: none;
    cursor: default;
}
#contextMenu .c-title {
    color: #f8fafc;
    text-align: center;
    font-size: 0.95rem;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.c-item-complex {
    background: transparent !important;
    padding: 15px 20px !important;
    border-bottom: 1px solid rgba(255,255,255,0.05) !important;
    border-left: 2px solid transparent !important;
    transition: 0.3s;
}
.c-item-complex.on {
    border-left-color: #00ff88 !important;
}
.setting-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}
.switch input { opacity: 0; width: 0; height: 0; }
.slider {
    position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
    background-color: #1e293b; transition: .4s; border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.1);
}
.slider:before {
    position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 3px;
    background-color: white; transition: .4s; border-radius: 50%;
}
input:checked + .slider { background-color: #00ff88; border-color: #00ff88; }
input:checked + .slider:before { transform: translateX(20px); }

.default-label {
    color: #64748b; font-size: 0.85rem; font-weight: 700;
}
.input-row {
    display: flex; gap: 10px; align-items: stretch;
}
.column-name-input {
    flex-grow: 1;
    background: #030f1c !important;
    border: 1px solid rgba(255,255,255,0.05) !important;
    border-radius: 8px !important;
    padding: 10px 15px !important;
    color: #fff !important;
    font-size: 0.95rem !important;
    font-weight: 700 !important;
    text-align: right;
    box-shadow: inset 0 2px 5px rgba(0,0,0,0.5) !important;
}
.column-name-input:focus {
    outline: none;
    border-color: #38bdf8 !important;
    box-shadow: 0 0 10px rgba(56,189,248,0.3) !important;
}
.reset-btn {
    background: rgba(56,189,248,0.1);
    border: 1px solid rgba(56,189,248,0.2);
    border-radius: 8px;
    width: 42px; display: flex; align-items: center; justify-content: center;
    color: #38bdf8; cursor: pointer; transition: 0.3s; font-size: 1.1rem;
}
.reset-btn:hover { background: #38bdf8; color: #fff; }
.font-row {
    justify-content: flex-end; gap: 8px; display: flex; align-items: center;
}
.font-label {
    color: #94a3b8; font-size: 0.85rem; font-weight: 700;
}
.font-btn {
    background: #0a192f;
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 6px;
    width: 28px; height: 28px;
    color: #fff; font-weight: 900;
    cursor: pointer; transition: 0.3s;
    display: flex; align-items: center; justify-content: center; font-family: inherit;
}
.font-btn:hover { background: rgba(255,255,255,0.1); }
.font-btn.active { background: #007bff; border-color: #007bff; }
.color-row {
    display: flex; justify-content: space-between; align-items: center;
}
.color-picker-group {
    display: flex; align-items: center; gap: 8px;
}
.color-label {
    color: #f8fafc; font-size: 0.85rem; font-weight: 700;
}
.color-input-wrapper {
    display: flex; align-items: center; gap: 5px;
}
.color-input-wrapper input[type="color"] {
    width: 24px; height: 24px;
    padding: 0; border: none; border-radius: 4px;
    cursor: pointer; background: transparent;
}
.clear-color {
    background: #b91c1c; border: none; border-radius: 50%;
    width: 18px; height: 18px; color: #fff; font-size: 0.6rem;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
}
.clear-color:hover { background: #ef4444; }
</style>

<script>
    // --- Data Rendering Engine (All Data) ---
    let transactions = [];

    function formatNumber(num, colDecimals) { 
        if(num === 0 || num === null || num === undefined) return '-';
        const d = (colDecimals !== undefined) ? colDecimals : ((typeof entState !== 'undefined' && entState.decimals !== undefined) ? entState.decimals : 2);
        return num.toLocaleString('en-US', {minimumFractionDigits: d, maximumFractionDigits: d}); 
    }

    // --- Dynamic Rendering for ALL models ---
    function renderOthers() {
        let htmlInv='', htmlTDr='', htmlTCr='', htmlMin='', htmlMat='', htmlBank='', htmlStrict='', htmlAud='', htmlFintech='', htmlCards='', htmlTimeline='', htmlWallet='', htmlChat='', htmlWidget='', htmlSwipe='', htmlSaas='', htmlDash='', htmlSplit='', htmlMonth='', htmlDatabar='', htmlNeon='', htmlDonut='', htmlTrend='', htmlCatDr='', htmlCatCr='', htmlHeat='';

        transactions.forEach(t => {
            const drStr = t.dr ? formatNumber(t.dr) : '-';
            const crStr = t.cr ? formatNumber(t.cr) : '-';
            const balStr = formatNumber(t.bal);
            const drClass = t.dr ? 'val-dr' : '';
            const crClass = t.cr ? 'val-cr' : '';

            const trBase = `<tr><td class="num">${t.date}</td><td>${t.desc}</td><td class="num ${drClass}">${drStr}</td><td class="num ${crClass}">${crStr}</td><td class="num val-bal">${balStr}</td></tr>`;
            htmlMin += trBase; htmlStrict += trBase; htmlBank += trBase; 
            
            const dashRow = `<tr><td class="num">${t.date}</td><td>${t.desc}</td><td class="num">${t.dr?'+'+drStr:'-'+crStr}</td></tr>`;
            htmlDash += dashRow; htmlSplit += dashRow; htmlMonth += dashRow; htmlDonut += dashRow; htmlTrend += dashRow;
            
            htmlInv += `<tr><td class="num">${t.date}</td><td>${t.desc}</td><td class="num">${t.dr?drStr:'('+crStr+')'}</td><td class="num">${balStr}</td></tr>`;
            htmlSaas += htmlInv;

            if(t.dr) htmlTDr += `<tr><td class="num">${t.date}</td><td>${t.desc}</td><td class="num">${drStr}</td></tr>`;
            if(t.cr) htmlTCr += `<tr><td class="num">${t.date}</td><td>${t.desc}</td><td class="num">${crStr}</td></tr>`;

            htmlMat += `<tr><td class="num">${t.date}</td><td>${t.desc}</td><td>${t.costCenter||'-'}</td><td>${t.notes||'-'}</td><td class="num">${drStr}</td><td class="num">${crStr}</td><td class="num">${balStr}</td></tr>`;
            htmlAud += `<tr><td class="num">${t.date}</td><td>${t.desc}</td><td class="num">${t.dr?drStr:crStr}</td><td class="num">${balStr}</td><td><div class="audit-box"></div></td></tr>`;

            const icon = t.dr ? '<div class="ft-icon out" style="color:var(--danger)">↑</div>' : '<div class="ft-icon in" style="color:var(--success)">↓</div>';
            htmlFintech += `<div class="ft-item">${icon}<div style="flex-grow:1;"><div style="font-weight:700;">${t.desc}</div><div style="font-size:0.8rem; color:var(--text-muted);">${t.date}</div></div><div class="num ${t.dr?'val-dr':'val-cr'}">${t.dr?'+'+drStr:'-'+crStr}</div></div>`;
            
            htmlCards += `<div class="card"><div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span class="num">${t.date}</span><span class="${drClass||crClass}">${t.dr?'مدين':'دائن'}</span></div><div style="font-size:1.2rem; font-weight:700; margin-bottom:15px;">${t.desc}</div><div class="num ${drClass||crClass}" style="font-size:1.5rem;">${t.dr?drStr:crStr}</div></div>`;
            
            htmlTimeline += `<div class="tl-item"><div class="tl-line"></div><div class="tl-content"><div class="num" style="color:var(--text-muted);">${t.date}</div><h3 style="margin:5px 0;">${t.desc}</h3><div class="num ${drClass||crClass}">${t.dr?'+'+drStr:'-'+crStr}</div></div></div>`;
            
            htmlWallet += `<div class="w-item"><div><div style="color:#fff;">${t.desc}</div><div class="num" style="color:#888; font-size:0.8rem;">${t.date}</div></div><div class="num ${t.dr?'':'val-cr'}" style="${t.dr?'color:#fff;':''}">${t.dr?'+'+drStr:'-'+crStr}</div></div>`;

            htmlChat += `<div class="msg ${t.dr?'dr':'cr'}"><div style="font-weight:700;">${t.desc}</div><div class="num">${t.dr?drStr:crStr}</div><div class="num" style="font-size:0.7rem; opacity:0.7;">${t.date}</div></div>`;

            htmlWidget += `<div class="w-row"><span>${t.desc}</span><span class="num ${drClass||crClass}">${t.dr?drStr:crStr}</span></div>`;
            
            htmlSwipe += `<div class="swipe-card"><div class="num">${t.date}</div><h2>${t.desc}</h2><div class="num ${drClass||crClass}" style="font-size:2rem;">${t.dr?drStr:crStr}</div></div>`;

            htmlDatabar += `<tr><td class="num">${t.date}</td><td>${t.desc}</td><td class="num"><div class="bar" style="background:var(--success); width:${t.dr?t.dr/150:0}%;"></div>${drStr}</td><td class="num"><div class="bar" style="background:var(--danger); width:${t.cr?t.cr/100:0}%;"></div>${crStr}</td></tr>`;
            
            htmlNeon += `<tr><td class="num">${t.date}</td><td>${t.desc}</td><td class="num glow-dr">${drStr}</td><td class="num glow-cr">${crStr}</td></tr>`;

            if(t.dr) htmlCatDr += `<div style="display:flex; justify-content:space-between; padding:5px 0;"><span>${t.desc}</span><span class="num">${drStr}</span></div>`;
            if(t.cr) htmlCatCr += `<div style="display:flex; justify-content:space-between; padding:5px 0;"><span>${t.desc}</span><span class="num">${crStr}</span></div>`;

            const heatClass = t.dr > 10000 || t.cr > 9000 ? 'heat-high' : 'heat-low';
            htmlHeat += `<tr class="${heatClass}"><td class="num">${t.date}</td><td>${t.desc}</td><td class="num">${drStr}</td><td class="num">${crStr}</td></tr>`;
        });

        document.getElementById('tbl-inv').innerHTML = htmlInv;
        document.getElementById('tbl-t-dr').innerHTML = htmlTDr;
        document.getElementById('tbl-t-cr').innerHTML = htmlTCr;
        document.getElementById('tbl-min').innerHTML = htmlMin;
        document.getElementById('tbl-mat').innerHTML = htmlMat;
        document.getElementById('tbl-bank').innerHTML = htmlBank;
        document.getElementById('tbl-strict').innerHTML = htmlStrict;
        document.getElementById('tbl-aud').innerHTML = htmlAud;
        
        document.getElementById('tbl-fintech').innerHTML = htmlFintech;
        document.getElementById('tbl-cards').innerHTML = htmlCards;
        document.getElementById('tbl-timeline').innerHTML = htmlTimeline;
        document.getElementById('tbl-wallet').innerHTML = htmlWallet;
        document.getElementById('tbl-chat').innerHTML = htmlChat;
        document.getElementById('tbl-widget').innerHTML = htmlWidget;
        document.getElementById('tbl-swipe').innerHTML = htmlSwipe;
        document.getElementById('tbl-saas').innerHTML = htmlSaas;

        document.getElementById('tbl-dash').innerHTML = htmlDash;
        document.getElementById('tbl-split').innerHTML = htmlSplit;
        document.getElementById('tbl-month').innerHTML = htmlMonth;
        document.getElementById('tbl-databar').innerHTML = htmlDatabar;
        document.getElementById('tbl-neon').innerHTML = htmlNeon;
        document.getElementById('tbl-donut').innerHTML = htmlDonut;
        document.getElementById('tbl-trend').innerHTML = htmlTrend;
        document.getElementById('tbl-cat-dr').innerHTML = htmlCatDr;
        document.getElementById('tbl-cat-cr').innerHTML = htmlCatCr;
        document.getElementById('tbl-heat').innerHTML = htmlHeat;
    }


    // --- Enterprise Specific Customization State ---
    const entState = {
        decimals: 2,
        numFont: 'JetBrains Mono',
        columns: [
            { id: 'desc', defaultName: 'البيان', name: 'البيان', visible: true, isNum: false, fontSize: 0.95, color: '', isBold: false },
            { id: 'date', defaultName: 'التاريخ', name: 'التاريخ', visible: true, isNum: true, fontSize: 0.95, color: '', isBold: false },
            { id: 'contraAccount', defaultName: 'الحساب المقابل', name: 'الحساب المقابل', visible: true, isNum: false, fontSize: 0.95, color: '', isBold: false },
            { id: 'notes', defaultName: 'ملاحظات', name: 'ملاحظات إضافية', visible: false, isNum: false, fontSize: 0.95, color: '', isBold: false },
            { id: 'costCenter', defaultName: 'مركز التكلفة', name: 'مركز التكلفة', visible: false, isNum: false, fontSize: 0.95, color: '', isBold: false },
            
            { id: 'dr', defaultName: 'مدين', name: 'مدين', visible: true, isNum: true, isMoney: true, fontSize: 0.95, color: '', isBold: false },
            { id: 'cr', defaultName: 'دائن', name: 'دائن', visible: true, isNum: true, isMoney: true, fontSize: 0.95, color: '', isBold: false },
            { id: 'bal', defaultName: 'الرصيد', name: 'الرصيد', visible: true, isNum: true, isMoney: true, fontSize: 0.95, color: '', isBold: false },
            
            { id: 'curr', defaultName: 'العملة', name: 'العملة', visible: false, isNum: false, fontSize: 0.95, color: '', isBold: false },
            { id: 'rate', defaultName: 'سعر الصرف', name: 'سعر الصرف', visible: false, isNum: true, fontSize: 0.95, color: '', isBold: false },
            { id: 'dr_fc', defaultName: 'مدين (أجنبي)', name: 'مدين (أجنبي)', visible: false, isNum: true, isMoney: true, fontSize: 0.95, color: '', isBold: false },
            { id: 'cr_fc', defaultName: 'دائن (أجنبي)', name: 'دائن (أجنبي)', visible: false, isNum: true, isMoney: true, fontSize: 0.95, color: '', isBold: false },
            { id: 'bal_fc', defaultName: 'رصيد (أجنبي)', name: 'رصيد (أجنبي)', visible: false, isNum: true, isMoney: true, fontSize: 0.95, color: '', isBold: false }
        ]
    };

    function renderEnterprise() {
        const thead = document.getElementById('ent-thead');
        const tbody = document.getElementById('ent-tbody');
        const table = document.getElementById('ent-table');
        
        table.style.removeProperty('--fs-text');
        table.style.removeProperty('--fs-num');
        
        const fontStr = entState.numFont === 'Inter' ? "'Inter', sans-serif" : "'JetBrains Mono', monospace";
        table.style.setProperty('--font-num', fontStr);

        let hasAnyWidth = false;
        let thHtml = '<tr>';
        entState.columns.forEach((col, index) => {
            if (col.visible) {
                const fs = col.fontSize ? (col.fontSize + 0.1) + 'rem' : '1.05rem';
                const c = col.color ? `color: ${col.color} !important;` : '';
                const bg = col.bgColor ? `background-color: ${col.bgColor} !important;` : '';
                const fw = col.isBold ? `font-weight: 800 !important;` : '';
                const w = col.width ? `width: ${col.width}px;` : '';
                if(col.width) hasAnyWidth = true;
                thHtml += `<th style="font-size: ${fs}; ${c} ${bg} ${fw} ${w}" data-index="${index}">
                            ${col.name}
                            <div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div>
                           </th>`;
            }
        });
        thHtml += '</tr>';
        thead.innerHTML = thHtml;
        
        if (hasAnyWidth) {
            table.style.width = 'max-content';
        } else {
            table.style.width = '100%';
        }

        let tbHtml = '';
        transactions.forEach(t => {
            tbHtml += `<tr data-ref="${t.voucher || t.ref || ''}">`;
            entState.columns.forEach(col => {
                if (col.visible) {
                    const val = t[col.id];
                    let displayVal = val;
                    if (col.isMoney && typeof val === 'number') displayVal = formatNumber(val, col.decimals);
                    const numClass = col.isNum ? 'num' : '';
                    let colorClass = '';
                    if (!col.color) {
                        if (col.id === 'dr' || col.id === 'dr_fc') colorClass = val ? 'val-dr' : '';
                        if (col.id === 'cr' || col.id === 'cr_fc') colorClass = val ? 'val-cr' : '';
                    }
                    if (col.id === 'bal' || col.id === 'bal_fc') colorClass = 'val-bal';
                    
                    const clickAttr = col.isNum ? `onclick="toggleNumHighlight(this)"` : '';
                    
                    let colStyles = '';
                    if(col.fontSize) colStyles += `font-size: ${col.fontSize}rem !important; `;
                    if(col.color) colStyles += `color: ${col.color} !important; `;
                    if(col.bgColor) colStyles += `background-color: ${col.bgColor} !important; `;
                    if(col.isBold) colStyles += `font-weight: 800 !important; `;
                    const colorStyle = colStyles ? `style="${colStyles}"` : '';
                    
                    tbHtml += `<td class="${numClass} ${colorClass}" ${clickAttr} ${colorStyle}>${displayVal || '-'}</td>`;
                }
            });
            tbHtml += '</tr>';
        });
        tbody.innerHTML = tbHtml;
        bindResizers();
        renderAnalytics();
    }

    let startX, startWidth, resizingColIndex, resizingTh;
    
    function bindResizers() {
        const table = document.getElementById('ent-table');
        if (!table) return;
        const resizers = table.querySelectorAll('.resizer');
        resizers.forEach(resizer => {
            resizer.addEventListener('mousedown', initResize);
            resizer.addEventListener('dblclick', autoSize);
        });
    }

    function autoSize(e) {
        e.stopPropagation();
        const th = e.target.parentElement;
        const colIndex = th.getAttribute('data-index');
        th.style.width = 'auto'; 
        entState.columns[colIndex].width = null;
        renderEnterprise();
    }

    function initResize(e) {
        e.stopPropagation();
        resizingTh = e.target.parentElement;
        resizingColIndex = resizingTh.getAttribute('data-index');
        startX = e.clientX;
        startWidth = parseInt(window.getComputedStyle(resizingTh).width, 10);
        
        const ths = document.querySelectorAll('#ent-thead th');
        ths.forEach((th) => {
            if (!th.style.width) {
                const computed = window.getComputedStyle(th).width;
                th.style.width = computed;
                const idx = th.getAttribute('data-index');
                if (idx) entState.columns[idx].width = parseFloat(computed);
            }
        });
        
        const table = document.getElementById('ent-table');
        table.style.width = 'max-content';
        
        document.addEventListener('mousemove', doResize);
        document.addEventListener('mouseup', stopResize);
        e.target.classList.add('resizing');
    }

    function doResize(e) {
        const delta = startX - e.clientX; 
        const newWidth = startWidth + delta;
        if (newWidth > 40) {
            resizingTh.style.width = newWidth + 'px';
            entState.columns[resizingColIndex].width = newWidth;
        }
    }

    function stopResize(e) {
        document.removeEventListener('mousemove', doResize);
        document.removeEventListener('mouseup', stopResize);
        document.querySelectorAll('.resizer').forEach(r => r.classList.remove('resizing'));
        renderEnterprise(); 
    }

    function inlineRename(index, newName) {
        if (newName && newName.trim() !== '') {
            entState.columns[index].name = newName.trim();
        } else {
            entState.columns[index].name = entState.columns[index].defaultName;
        }
        renderEnterprise();
        if (!newName || newName.trim() === '') renderContextToggles();
    }

    function resetColumnName(index) {
        entState.columns[index].name = entState.columns[index].defaultName;
        renderEnterprise();
        renderContextToggles();
    }

    function adjustColFont(index, amount) {
        if(!entState.columns[index].fontSize) entState.columns[index].fontSize = 0.95;
        entState.columns[index].fontSize = Math.max(0.6, Math.min(2.0, entState.columns[index].fontSize + amount));
        renderEnterprise();
    }

    function changeColColor(index, color) {
        entState.columns[index].color = color;
        renderEnterprise();
    }

    function changeColBg(index, color) {
        entState.columns[index].bgColor = color;
        renderEnterprise();
    }

    function setDecimals(d) {
        entState.decimals = d;
        renderEnterprise();
        updateDecimalsUI();
    }

    function updateDecimalsUI() {
        const dContainer = document.getElementById('decimals-toggles');
        if (dContainer) {
            Array.from(dContainer.children).forEach(btn => {
                const val = parseInt(btn.innerText);
                btn.style.background = (val === entState.decimals) ? 'var(--primary)' : 'rgba(255,255,255,0.1)';
                btn.style.color = (val === entState.decimals) ? '#fff' : '#cbd5e1';
            });
        }
    }

    function setNumFont(fontName) {
        entState.numFont = fontName;
        renderEnterprise();
        updateFontUI();
    }

    function updateFontUI() {
        const fContainer = document.getElementById('num-font-toggles');
        if (fContainer) {
            Array.from(fContainer.children).forEach(btn => {
                const isMatch = btn.innerText.includes(entState.numFont.split(' ')[0]);
                btn.style.background = isMatch ? 'var(--primary)' : 'rgba(255,255,255,0.1)';
                btn.style.color = isMatch ? '#fff' : '#cbd5e1';
            });
        }
        
        const radioJb = document.getElementById('font_jb');
        const radioInter = document.getElementById('font_inter');
        if (radioJb && radioInter) {
            if (entState.numFont === 'JetBrains Mono') {
                radioJb.checked = true;
            } else {
                radioInter.checked = true;
            }
        }
    }

    function adjustColDecimals(index, delta) {
        if (entState.columns[index].decimals === undefined) {
            entState.columns[index].decimals = entState.decimals;
        }
        let newD = entState.columns[index].decimals + delta;
        if (newD < 0) newD = 0;
        if (newD > 5) newD = 5;
        entState.columns[index].decimals = newD;
        renderEnterprise();
        renderContextToggles();
    }

    function toggleColBold(index) {
        entState.columns[index].isBold = !entState.columns[index].isBold;
        renderEnterprise();
        renderContextToggles();
    }

    function toggleColumn(index) {
        entState.columns[index].visible = !entState.columns[index].visible;
        renderEnterprise();
        renderContextToggles();
    }

    function renderContextToggles() {
        const container = document.getElementById('col-toggles-container');
        let html = '';
        entState.columns.forEach((col, index) => {
            const isOn = col.visible ? 'on' : '';
            html += `<div class="c-item-complex ${isOn}">
    <div class="setting-row">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="drag-handle" style="cursor: grab; color: #94a3b8; font-size: 1.2rem; padding: 0 5px;" title="اسحب للترتيب">☰</span>
            <span class="default-label">الافتراضي: ${col.defaultName}</span>
        </div>
        <label class="switch">
            <input type="checkbox" ${col.visible ? 'checked' : ''} onchange="toggleColumn(${index})">
            <span class="slider round"></span>
        </label>
    </div>
    
    <div class="setting-row input-row" style="margin-top: 10px;">
        <input type="text" class="column-name-input" value="${col.name}" onchange="inlineRename(${index}, this.value)" placeholder="أدخل اسماً جديداً...">
        <button class="reset-btn" onclick="resetColumnName(${index})" title="إرجاع للاسم الافتراضي">🔄</button>
    </div>
    
    <div class="setting-row font-row" style="margin-top: 15px;">
        <span class="font-label">الخط:</span>
        <button class="font-btn dec" onclick="adjustColFont(${index}, -0.05)">-</button>
        <button class="font-btn inc" onclick="adjustColFont(${index}, 0.05)">+</button>
        <button class="font-btn bold ${col.isBold ? 'active' : ''}" onclick="toggleColBold(${index})">B</button>
    </div>

    ${col.isMoney ? `
    <div class="setting-row font-row" style="margin-top: 10px;">
        <span class="font-label">عشري:</span>
        <button class="font-btn dec" onclick="adjustColDecimals(${index}, -1)">-</button>
        <span class="dec-val">${col.decimals !== undefined ? col.decimals : entState.decimals}</span>
        <button class="font-btn inc" onclick="adjustColDecimals(${index}, 1)">+</button>
    </div>
    ` : ''}
    
    <div class="setting-row color-row" style="margin-top: 15px;">
        <div class="color-picker-group">
            <span class="color-label">لون النص:</span>
            <div class="color-input-wrapper">
                <input type="color" value="${col.color || '#000000'}" onchange="changeColColor(${index}, this.value)">
                <button class="clear-color" onclick="changeColColor(${index}, '')">✖</button>
            </div>
        </div>
        <div class="color-picker-group">
            <span class="color-label">لون الخلفية:</span>
            <div class="color-input-wrapper">
                <input type="color" value="${col.bgColor || '#ffffff'}" onchange="changeColBg(${index}, this.value)">
                <button class="clear-color" onclick="changeColBg(${index}, '')">✖</button>
            </div>
        </div>
    </div>
</div>`;
        });
        container.innerHTML = html;
        
        if (window.sortableInstance) {
            window.sortableInstance.destroy();
        }
        window.sortableInstance = new Sortable(container, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: function (evt) {
                const oldIndex = evt.oldIndex;
                const newIndex = evt.newIndex;
                if (oldIndex !== newIndex) {
                    const movedItem = entState.columns.splice(oldIndex, 1)[0];
                    entState.columns.splice(newIndex, 0, movedItem);
                    renderEnterprise();
                    renderContextToggles();
                }
            }
        });
    }
    
    function toggleNumHighlight(cell) {
        if (cell.classList.contains('focused-num')) {
            cell.classList.remove('focused-num');
        } else {
            cell.classList.add('focused-num');
        }
    }

    const ctxMenu = document.getElementById('contextMenu');
    const drawerOverlay = document.getElementById('drawerOverlay');

    function openSettingsDrawer() {
        closeRowMenu();
        ctxMenu.classList.add('active');
        if(drawerOverlay) drawerOverlay.classList.add('active');
        renderContextToggles();
    }

    let currentRowRef = '';
    
    document.getElementById('v-classic-ent').addEventListener('contextmenu', function(e) {
        e.preventDefault();
        closeMenu();
        closeRowMenu();

        const tr = e.target.closest('tbody tr');
        if (tr) {
            currentRowRef = tr.getAttribute('data-ref') || '';
            const rowMenu = document.getElementById('rowContextMenu');
            if(rowMenu) {
                let posX = e.clientX, posY = e.clientY;
                if (window.innerWidth - posX < 260) posX = window.innerWidth - 270;
                rowMenu.style.left = posX + 'px';
                rowMenu.style.top = posY + 'px';
                rowMenu.classList.add('active');
            }
        }
    });
    
    function closeMenu() {
        ctxMenu.classList.remove('active');
        if(drawerOverlay) drawerOverlay.classList.remove('active');
    }

    function toggleMaximizeMenu() {
        const menu = document.getElementById('contextMenu');
        menu.classList.toggle('fullscreen');
        if (menu.classList.contains('fullscreen')) {
            menu.style.top = '0px';
            menu.style.left = '0px';
        }
    }

    function closeRowMenu() {
        const rowMenu = document.getElementById('rowContextMenu');
        if (rowMenu) rowMenu.classList.remove('active');
    }

    function viewVoucher() {
        if(currentRowRef) {
            let page = 'journal-voucher.php';
            if (currentRowRef.startsWith('INV')) page = 'sales-invoice.php';
            else if (currentRowRef.startsWith('RCP')) page = 'cash-receipt.php';
            else if (currentRowRef.startsWith('EXP') || currentRowRef.startsWith('PAY')) page = 'payment-voucher.php';
            
            window.open(page + '?id=' + encodeURIComponent(currentRowRef), '_blank');
        }
        closeRowMenu();
    }
    
    function printVoucher() {
        if(currentRowRef) {
            let page = 'journal-voucher.php';
            if (currentRowRef.startsWith('INV')) page = 'sales-invoice.php';
            else if (currentRowRef.startsWith('RCP')) page = 'cash-receipt.php';
            else if (currentRowRef.startsWith('EXP') || currentRowRef.startsWith('PAY')) page = 'payment-voucher.php';
            
            window.open(page + '?id=' + encodeURIComponent(currentRowRef) + '&print=true', '_blank');
        }
        closeRowMenu();
    }

    function changeView(viewId, btnElement, bgColor) {
        document.querySelectorAll('.v-btn').forEach(btn => btn.classList.remove('active'));
        if(btnElement) btnElement.classList.add('active');

        document.querySelectorAll('.template-view').forEach(view => {
            view.classList.remove('active');
        });
        const selectedView = document.getElementById(viewId);
        if (selectedView) selectedView.classList.add('active');

        const body = document.body;
        if (bgColor) {
            body.style.backgroundColor = bgColor;
            document.getElementById('print-container').style.backgroundColor = bgColor;
        } else {
            body.style.backgroundColor = 'var(--bg-dark)';
            document.getElementById('print-container').style.backgroundColor = 'transparent';
        }
    }

    let chartsInstance = {};

    function renderAnalytics() {
        if (typeof ApexCharts === 'undefined') return;

        let categories = [];
        let drData = [];
        let crData = [];
        let balData = [];
        let totalDr = 0;
        let totalCr = 0;

        const grouped = {};
        transactions.forEach(t => {
            if (!grouped[t.date]) {
                grouped[t.date] = { dr: 0, cr: 0, bal: parseFloat(t.bal) || 0 };
            }
            const d = parseFloat(t.dr) || 0;
            const c = parseFloat(t.cr) || 0;
            grouped[t.date].dr += d;
            grouped[t.date].cr += c;
            grouped[t.date].bal = parseFloat(t.bal) || grouped[t.date].bal;
            
            totalDr += d;
            totalCr += c;
        });

        let waterfallData = [];
        let scatterData = [];

        for (const [date, data] of Object.entries(grouped)) {
            categories.push(date);
            drData.push(data.dr);
            crData.push(data.cr);
            balData.push(data.bal);
            
            let net = data.dr - data.cr;
            waterfallData.push({ x: date, y: net });
            
            if (data.dr > 0) scatterData.push([new Date(date).getTime(), data.dr]);
            if (data.cr > 0) scatterData.push([new Date(date).getTime(), -data.cr]);
        }

        const txCount = transactions.length;
        const avgTx = txCount > 0 ? (totalDr + totalCr) / txCount : 0;
        const finalBal = balData.length > 0 ? balData[balData.length - 1] : 0;
        
        document.getElementById('kpi-bal').innerText = formatNumber(finalBal, entState.decimals);
        document.getElementById('kpi-dr').innerText = formatNumber(totalDr, entState.decimals);
        document.getElementById('kpi-cr').innerText = formatNumber(totalCr, entState.decimals);
        document.getElementById('kpi-count').innerText = txCount;
        document.getElementById('kpi-avg').innerText = formatNumber(avgTx, entState.decimals);

        const commonOptions = {
            chart: { background: 'transparent', toolbar: { show: false }, animations: { enabled: true, easing: 'easeinout', speed: 800 } },
            theme: { mode: 'dark' },
            grid: { borderColor: 'rgba(255,255,255,0.05)' },
            tooltip: { theme: 'dark' }
        };

        const optBalance = {
            ...commonOptions,
            series: [{ name: 'الرصيد', data: balData }],
            chart: { type: 'area', height: 250, background: 'transparent', toolbar: { show: false } },
            colors: ['#38bdf8'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: { categories: categories, labels: { style: { colors: '#94a3b8', fontFamily: 'Tajawal' } } },
            yaxis: { labels: { style: { colors: '#94a3b8' } } }
        };

        const optCashFlow = {
            ...commonOptions,
            series: [{ name: 'مدين', data: drData }, { name: 'دائن', data: crData }],
            chart: { type: 'bar', height: 250, background: 'transparent', toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
            colors: ['#10b981', '#f43f5e'],
            dataLabels: { enabled: false },
            stroke: { show: true, width: 2, colors: ['transparent'] },
            xaxis: { categories: categories, labels: { style: { colors: '#94a3b8', fontFamily: 'Tajawal' } } },
            yaxis: { labels: { style: { colors: '#94a3b8' } } }
        };

        const optDist = {
            ...commonOptions,
            series: [totalDr, totalCr],
            labels: ['إجمالي المدين', 'إجمالي الدائن'],
            chart: { type: 'donut', height: 220, background: 'transparent' },
            colors: ['#10b981', '#f43f5e'],
            plotOptions: { pie: { donut: { size: '70%', labels: { show: true, name: { color: '#fff' }, value: { color: '#fff' } } } } },
            dataLabels: { enabled: false },
            stroke: { show: true, colors: 'transparent' },
            legend: { position: 'bottom', labels: { colors: '#fff' }, fontFamily: 'Tajawal' }
        };

        const optVelocity = {
            ...commonOptions,
            series: [{ name: 'حجم الحركة', data: drData.map((d, i) => d + crData[i]) }],
            chart: { type: 'radar', height: 250, background: 'transparent', toolbar: { show: false } },
            labels: categories,
            stroke: { width: 2, colors: ['#a855f7'] },
            fill: { opacity: 0.2, colors: ['#a855f7'] },
            markers: { size: 4, colors: ['#fff'], strokeColors: '#a855f7', strokeWidth: 2 }
        };

        if (chartsInstance.balance) chartsInstance.balance.destroy();
        chartsInstance.balance = new ApexCharts(document.querySelector("#chart-balance-trend"), optBalance);
        chartsInstance.balance.render();

        if (chartsInstance.cashflow) chartsInstance.cashflow.destroy();
        chartsInstance.cashflow = new ApexCharts(document.querySelector("#chart-cash-flow"), optCashFlow);
        chartsInstance.cashflow.render();

        if (chartsInstance.dist) chartsInstance.dist.destroy();
        chartsInstance.dist = new ApexCharts(document.querySelector("#chart-distribution"), optDist);
        chartsInstance.dist.render();

        const optWaterfall = {
            ...commonOptions,
            series: [{ name: 'صافي التغير', data: waterfallData }],
            chart: { type: 'bar', height: 250, background: 'transparent', toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 4, colors: { ranges: [{ from: -999999999, to: -0.01, color: '#f43f5e' }, { from: 0.01, to: 999999999, color: '#10b981' }] } } },
            dataLabels: { enabled: false },
            xaxis: { type: 'category', labels: { style: { colors: '#94a3b8', fontFamily: 'Tajawal' } } },
            yaxis: { labels: { style: { colors: '#94a3b8' } } }
        };

        const optScatter = {
            ...commonOptions,
            series: [{ name: 'حركات', data: scatterData }],
            chart: { type: 'scatter', height: 250, background: 'transparent', toolbar: { show: false }, zoom: { enabled: false } },
            colors: ['#0ea5e9'],
            xaxis: { type: 'datetime', labels: { style: { colors: '#94a3b8', fontFamily: 'Tajawal' }, formatter: function(val) { return new Date(val).toLocaleDateString('ar-SA'); } } },
            yaxis: { labels: { style: { colors: '#94a3b8' } } },
            markers: { size: 6, strokeWidth: 0, hover: { size: 9 } }
        };

        if (chartsInstance.velocity) chartsInstance.velocity.destroy();
        chartsInstance.velocity = new ApexCharts(document.querySelector("#chart-velocity"), optVelocity);
        chartsInstance.velocity.render();

        if (chartsInstance.waterfall) chartsInstance.waterfall.destroy();
        chartsInstance.waterfall = new ApexCharts(document.querySelector("#chart-waterfall"), optWaterfall);
        chartsInstance.waterfall.render();

        if (chartsInstance.scatter) chartsInstance.scatter.destroy();
        chartsInstance.scatter = new ApexCharts(document.querySelector("#chart-scatter"), optScatter);
        chartsInstance.scatter.render();
    }

    function exportPDF() {
        const element = document.getElementById('print-container');
        const opt = {
            margin:       [10, 10, 15, 10],
            filename:     'Account_Statement.pdf',
            image:        { type: 'jpeg', quality: 1 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).toPdf().get('pdf').then(function (pdf) {
            const totalPages = pdf.internal.getNumberOfPages();
            if (totalPages > 1) {
                for (let i = 1; i <= totalPages; i++) {
                    pdf.setPage(i);
                    pdf.setFontSize(10);
                    pdf.setTextColor(100);
                    // Use standard numbers like '1 / 2'
                    pdf.text(i + ' / ' + totalPages, pdf.internal.pageSize.getWidth() / 2, pdf.internal.pageSize.getHeight() - 8, { align: 'center' });
                }
            }
        }).save();
    }

    function exportExcel() {
        if(typeof XLSX === 'undefined') {
            alert('يتم الآن تحميل مكتبة إكسل، يرجى المحاولة بعد قليل.');
            return;
        }
        const activeView = document.querySelector('.template-view.active');
        if(!activeView) return alert('الرجاء اختيار تقرير لعرضه أولاً');
        
        let table = activeView.querySelector('table');
        if(!table) {
            table = document.getElementById('ent-table');
        }
        if(!table) return alert('هذا النموذج لا يدعم التصدير المباشر إلى إكسل حالياً');
        
        const wb = XLSX.utils.table_to_book(table, {sheet: "كشف حساب"});
        XLSX.writeFile(wb, 'Account_Statement.xlsx');
    }

    function shareEmail() {
        const accSelect = document.getElementById('accountSelect');
        const accName = accSelect.value ? accSelect.options[accSelect.selectedIndex].text : 'عام';
        const subject = encodeURIComponent(`كشف حساب - ${accName}`);
        const body = encodeURIComponent(`مرحباً،\n\nتجدون في المرفقات كشف حساب (${accName}).\n\nمع التحية.`);
        window.location.href = `mailto:?subject=${subject}&body=${body}`;
        setTimeout(() => alert("تلميح: لا تنسَ إرفاق ملف الـ PDF أو الـ Excel الذي قمت بتنزيله داخل رسالة الإيميل."), 500);
    }

    function shareWhatsApp() {
        const accSelect = document.getElementById('accountSelect');
        const selectedOption = accSelect.options[accSelect.selectedIndex];
        const accName = accSelect.value ? selectedOption.text : 'عام';
        let phone = selectedOption.getAttribute('data-phone');
        
        if (accSelect.value) {
            try {
                const customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                const cust = customers.find(c => c.id === accSelect.value);
                if (cust) phone = cust.whatsapp || cust.phone || phone;
            } catch(e) {}
            try {
                const suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                const sup = suppliers.find(s => s.id === accSelect.value);
                if (sup) phone = sup.whatsapp || sup.phone || phone;
            } catch(e) {}
        }
        
        const text = encodeURIComponent(`مرحباً، أود مشاركة كشف حساب (${accName}) معك.`);
        
        let url = `https://wa.me/`;
        if (phone) {
            const cleanPhone = phone.replace(/[^0-9]/g, '');
            url += `${cleanPhone}`;
        }
        url += `?text=${text}`;
        
        window.open(url, '_blank');
        setTimeout(() => alert("تلميح: لا تنسَ إرفاق المستند (PDF أو Excel) في محادثة الواتساب بعد تحميله."), 500);
    }

    function saveLayout() {
        localStorage.setItem('erp_entState_v2', JSON.stringify(entState));
        const btn = document.querySelector('.btn-save-layout');
        const oldText = btn.innerHTML;
        btn.innerHTML = '✅ تم الحفظ بنجاح!';
        btn.style.background = 'linear-gradient(135deg, #38bdf8, #2563eb)';
        setTimeout(() => {
            btn.innerHTML = oldText;
            btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
        }, 2000);
    }

    // Initialize
    window.onload = () => {
        initCsDropdown();
        generateSimpleReport();
        const savedState = localStorage.getItem('erp_entState_v2');
        if (savedState) {
            try {
                const parsed = JSON.parse(savedState);
                if (parsed.decimals !== undefined) entState.decimals = parsed.decimals;
                entState.columns = entState.columns.map(defCol => {
                    const savedCol = parsed.columns.find(c => c.id === defCol.id);
                    return savedCol ? { ...defCol, ...savedCol } : defCol;
                });
            } catch(e) { console.error('Error loading state', e); }
        }

        renderEnterprise(); 
        renderOthers();     
        updateDecimalsUI();
        updateFontUI();
        
        // Auto-select template from URL if present
        const urlParams = new URLSearchParams(window.location.search);
        const modelId = urlParams.get('model');
        if (modelId) {
            changeView(modelId, null);
            document.getElementById('print-container').scrollIntoView({behavior: 'smooth'});
        }
        
        updateFontUI();
    };
</script>


<script>
function runAISmartSearch() {
    const input = document.getElementById('cs-input');
    const q = input.value.trim();
    const btn = document.getElementById('btn-smart-search');
    if (!q) { showToast('الرجاء كتابة وصف للحساب أولاً', 'warning'); input.focus(); return; }
    if (typeof accountsData === 'undefined' || accountsData.length <= 1) { showToast('قائمة الحسابات غير متوفرة بعد', 'error'); return; }
    btn.innerHTML = '⏳...'; btn.disabled = true;
    let accountsList = accountsData.filter(a => a.id !== 'all_accounts').map(a => `${a.id} - ${a.name} (${a.group})`).join('\n');
    let promptMsg = "أنت مساعد بحث ذكي في نظام محاسبي. المستخدم يبحث عن الحساب الأقرب للمعنى التالي: '" + q + "'.\nإليك قائمة الحسابات المتاحة:\n" + accountsList + "\n\nاستخرج فقط وفقط 'ID' (الكود) الخاص بأقرب حساب يطابق نية المستخدم. لا تكتب أي كلمة أخرى، فقط الـ ID.";
    fetch('api/ai-assistant.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ message: promptMsg, context: '' }) })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = '🤖 ذكي'; btn.disabled = false;
        if (data.reply) {
            let matchId = data.reply.trim().replace(/[^a-zA-Z0-9_-]/g, '');
            let matchedAccount = accountsData.find(a => a.id === matchId);
            if (matchedAccount) {
                if (typeof selectCsOption === 'function') {
                    selectCsOption(matchedAccount.id, matchedAccount.name);
                    showToast('تم إيجاد الحساب الذكي: ' + matchedAccount.name);
                }
            } else { showToast('لم يتم العثور على حساب مطابق بدقة.', 'warning'); }
        } else { showToast('حدث خطأ في البحث الذكي.', 'error'); }
    })
    .catch(err => { btn.innerHTML = '🤖 ذكي'; btn.disabled = false; showToast('تعذر الاتصال بالخادم.', 'error'); });
}
</script>
</body>
</html>
