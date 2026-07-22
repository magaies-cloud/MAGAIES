// UnifiedBrain Module & AI Hub Controller
const UnifiedBrain = {
    isOpen: false,
    isMenuOpen: false,
    isLoading: false,
    lastData: null,
    
    init() {
        this.createAIHub();
        this.createPanel();
        this.quickHealthCheck();
    },
    
    createAIHub() {
        // 1. Create Overlay for AI Hub Speed Dial Menu
        const menuOverlay = document.createElement('div');
        menuOverlay.className = 'ai-hub-menu-overlay';
        menuOverlay.id = 'ai-hub-menu-overlay';
        menuOverlay.onclick = () => this.closeMenu();
        document.body.appendChild(menuOverlay);

        // 2. Create AI Hub Speed Dial Popup Menu
        const hubMenu = document.createElement('div');
        hubMenu.className = 'ai-hub-menu';
        hubMenu.id = 'ai-hub-menu';
        hubMenu.innerHTML = `
            <div class="ai-hub-header">
                <div class="ai-hub-title-group">
                    <div class="ai-hub-title">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="filter: drop-shadow(0 0 8px rgba(56, 189, 248, 0.8)); flex-shrink: 0;">
                            <path d="M12 3v3m0 12v3M3 12h3m12 0h3m-3.5-6.5-2.1 2.1m-6.8 6.8-2.1 2.1m0-11 2.1 2.1m6.8 6.8 2.1 2.1"/>
                            <circle cx="12" cy="12" r="4" fill="#38bdf8" fill-opacity="0.4"/>
                        </svg>
                        <span>مركز الذكاء الاصطناعي</span>
                    </div>
                    <p class="ai-hub-subtitle">اختر أداة الذكاء الاصطناعي المطلوبة للنظام</p>
                </div>
                <button class="ai-hub-close-btn" onclick="UnifiedBrain.closeMenu()">&times;</button>
            </div>
            
            <!-- Option 1: Unified Smart Brain -->
            <div class="ai-hub-item ai-hub-item-brain" onclick="UnifiedBrain.openBrainFromMenu()">
                <div class="ai-hub-icon" style="background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); color: #ffffff; box-shadow: 0 6px 20px rgba(168, 85, 247, 0.6);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 4.44-5.04Z"/>
                        <path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-4.44-5.04Z"/>
                        <path d="M12 7.5h2.5"/>
                        <path d="M12 12h3"/>
                        <path d="M12 16.5h2"/>
                        <path d="M9.5 7.5H12"/>
                        <path d="M9 12h3"/>
                        <path d="M10 16.5h2"/>
                    </svg>
                </div>
                <div class="ai-hub-text">
                    <h4>
                        العقل الذكي الموحد
                        <span class="ai-hub-badge-mini" style="background: rgba(168, 85, 247, 0.25); color: #e9d5ff; border: 1px solid rgba(168, 85, 247, 0.4);">⚡ استراتيجي</span>
                    </h4>
                    <p>المدير العام ومراجع الحسابات (ربط تحليلي شامل لكل الأقسام)</p>
                </div>
            </div>

            <!-- Option 2: AI Assistant Chatbot -->
            <div class="ai-hub-item ai-hub-item-chat" onclick="UnifiedBrain.openChatFromMenu()">
                <div class="ai-hub-icon" style="background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%); color: #ffffff; box-shadow: 0 6px 20px rgba(56, 189, 248, 0.6);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="10" rx="4"/>
                        <circle cx="12" cy="5" r="2" fill="#ffffff" fill-opacity="0.4"/>
                        <path d="M12 7v4"/>
                        <line x1="8" y1="15" x2="8" y2="15.01" stroke-width="3"/>
                        <line x1="16" y1="15" x2="16" y2="15.01" stroke-width="3"/>
                        <path d="M9 18.5h6"/>
                        <path d="M2 15h1"/>
                        <path d="M21 15h1"/>
                    </svg>
                </div>
                <div class="ai-hub-text">
                    <h4>
                        المساعد الذكي للبيانات
                        <span class="ai-hub-badge-mini" style="background: rgba(56, 189, 248, 0.25); color: #bae6fd; border: 1px solid rgba(56, 189, 248, 0.4);">💬 استعلام فوري</span>
                    </h4>
                    <p>محادثة فورية والاستعلام السريع عن الفواتير والديون والأصناف</p>
                </div>
            </div>

            <!-- Option 3: Full Page Analytics -->
            <a href="unified-brain.php" class="ai-hub-item ai-hub-item-full">
                <div class="ai-hub-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.21 15.89A10 10 0 1 1 8 2.83"/>
                        <path d="M22 12A10 10 0 0 0 12 2v10z"/>
                    </svg>
                </div>
                <div class="ai-hub-text">
                    <h4>
                        فتح الشاشة الكاملة للتحليل
                        <span class="ai-hub-badge-mini" style="background: rgba(16, 185, 129, 0.25); color: #a7f3d0; border: 1px solid rgba(16, 185, 129, 0.4);">📈 تحليلات 360°</span>
                    </h4>
                    <p>عرض الجداول الشاملة، مؤشرات الأداء والرسوم البيانية التفاعلية</p>
                </div>
            </a>
        `;
        document.body.appendChild(hubMenu);

        // 3. Create Single Unified Floating Action Button (FAB)
        const fab = document.createElement('button');
        fab.className = 'ub-fab';
        fab.id = 'ub-fab';
        fab.title = 'مركز الذكاء الاصطناعي (AI Hub)';
        fab.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i><span class="ub-fab-badge" id="ub-fab-badge" style="display:none">0</span>';
        fab.onclick = () => this.toggleMenu();
        document.body.appendChild(fab);
    },

    toggleMenu() {
        const chatbotWin = document.getElementById('chatbot-window');
        const isChatOpen = chatbotWin && chatbotWin.classList.contains('open');

        // إذا كان المساعد الذكي (Chatbot) مفتوحاً: إخفائه فوراً والعودة لمركز الذكاء الاصطناعي أوتوماتيكياً!
        if (isChatOpen) {
            chatbotWin.classList.remove('open');
            if (this.isOpen) this.close();
            this.openMenu();
            return;
        }

        // إذا كانت لوحة العقل الذكي مفتوحة: إغلاقها والعودة للقائمة
        if (this.isOpen) {
            this.close();
            this.openMenu();
            return;
        }

        this.isMenuOpen ? this.closeMenu() : this.openMenu();
    },

    openMenu() {
        this.isMenuOpen = true;
        document.getElementById('ai-hub-menu-overlay')?.classList.add('active');
        document.getElementById('ai-hub-menu')?.classList.add('active');
        document.getElementById('ub-fab')?.classList.add('active');
    },

    closeMenu() {
        this.isMenuOpen = false;
        document.getElementById('ai-hub-menu-overlay')?.classList.remove('active');
        document.getElementById('ai-hub-menu')?.classList.remove('active');
        document.getElementById('ub-fab')?.classList.remove('active');
    },

    openBrainFromMenu() {
        document.getElementById('chatbot-window')?.classList.remove('open');
        this.closeMenu();
        this.open();
    },

    openChatFromMenu() {
        this.close();
        this.closeMenu();
        const chatbotWin = document.getElementById('chatbot-window');
        if (chatbotWin) {
            chatbotWin.classList.add('open');
            const input = document.getElementById('chatbot-input');
            if (input) input.focus();
        } else {
            const chatbotFab = document.getElementById('chatbot-fab');
            if (chatbotFab) chatbotFab.click();
        }
    },
    
    createPanel() {
        const overlay = document.createElement('div');
        overlay.className = 'ub-overlay';
        overlay.id = 'ub-overlay';
        overlay.onclick = () => this.close();
        
        const panel = document.createElement('div');
        panel.className = 'ub-panel';
        panel.id = 'ub-panel';
    createPanel() {
        const overlay = document.createElement('div');
        overlay.className = 'ub-overlay';
        overlay.id = 'ub-overlay';
        overlay.onclick = () => this.close();
        
        const panel = document.createElement('div');
        panel.className = 'ub-panel';
        panel.id = 'ub-panel';
        panel.innerHTML = `
            <div class="ub-panel-header">
                <div class="ub-panel-title">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c084fc" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="filter: drop-shadow(0 0 6px rgba(192, 132, 252, 0.6));">
                        <path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 4.44-5.04Z"/>
                        <path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-4.44-5.04Z"/>
                        <path d="M12 7.5h2.5"/><path d="M12 12h3"/><path d="M12 16.5h2"/><path d="M9.5 7.5H12"/><path d="M9 12h3"/><path d="M10 16.5h2"/>
                    </svg>
                    <span>العقل الذكي الموحد</span>
                </div>
                <button class="ub-close-btn" onclick="UnifiedBrain.close()">&times;</button>
            </div>
            <div class="ub-panel-body" id="ub-panel-body">
                <div class="ub-loading" id="ub-loading" style="display:none">
                    <div class="ub-brain-pulse">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 4.44-5.04Z"/>
                            <path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-4.44-5.04Z"/>
                        </svg>
                    </div>
                    <p>جاري تحليل بيانات جميع الأقسام...</p>
                </div>
                <div id="ub-content">
                    <div class="ub-health-section" id="ub-health-section">
                        <div class="ub-health-circle-wrap">
                            <svg class="ub-health-svg" viewBox="0 0 120 120">
                                <circle class="ub-health-bg" cx="60" cy="60" r="52" />
                                <circle class="ub-health-progress" id="ub-health-progress" cx="60" cy="60" r="52" />
                            </svg>
                            <div class="ub-health-value">
                                <span class="ub-health-number" id="ub-health-number">--</span>
                                <span class="ub-health-label" id="ub-health-label">جاري التحليل</span>
                            </div>
                        </div>
                        <div class="ub-health-factors" id="ub-health-factors"></div>
                    </div>
                    
                    <div class="ub-depts-grid" id="ub-depts-grid"></div>
                    
                    <div class="ub-recommendations" id="ub-recommendations">
                        <div class="ub-section-title" style="display:flex; align-items:center; gap:8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                            </svg> 
                            التوصيات الاستراتيجية
                        </div>
                        <div id="ub-recs-content" class="ub-recs-placeholder">اضغط "تشغيل التحليل الذكي" لجلب التوصيات</div>
                    </div>
                </div>
            </div>
            <div class="ub-panel-footer">
                <button class="ub-action-btn ub-action-primary" id="ub-analyze-btn" onclick="UnifiedBrain.fullAnalysis()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; margin-left:6px;">
                        <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                    </svg> 
                    تشغيل التحليل الذكي
                </button>
                <a href="unified-brain.php" class="ub-action-btn ub-action-secondary" style="display:flex; align-items:center; justify-content:center; gap:8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>
                    </svg> 
                    الشاشة الكاملة
                </a>
            </div>
        `;
        
        document.body.appendChild(overlay);
        document.body.appendChild(panel);
    },
        
        document.body.appendChild(overlay);
        document.body.appendChild(panel);
    },
    
    toggle() { this.isOpen ? this.close() : this.open(); },
    
    open() {
        document.getElementById('chatbot-window')?.classList.remove('open');
        this.closeMenu();
        this.isOpen = true;
        document.getElementById('ub-overlay')?.classList.add('active');
        document.getElementById('ub-panel')?.classList.add('active');
    },
    
    close() {
        this.isOpen = false;
        document.getElementById('ub-overlay')?.classList.remove('active');
        document.getElementById('ub-panel')?.classList.remove('active');
    },
    
    async quickHealthCheck() {
        try {
            const res = await fetch('api/unified-brain.php');
            if (!res.ok) throw new Error('Network response was not ok');
            const data = await res.json();
            this.updateHealthUI(data);
            this.updateDepartmentsUI(data);
            this.lastData = data;
            
            const criticalCount = Object.values(data.departments || {}).filter(d => d.status === 'critical').length;
            const warningCount = Object.values(data.departments || {}).filter(d => d.status === 'warning').length;
            if (criticalCount > 0) {
                this.showBadge(criticalCount, 'critical');
            } else if (warningCount > 0) {
                this.showBadge(warningCount, 'warning');
            }
        } catch(e) { console.error('Unified Brain health check failed:', e); }
    },
    
    async fullAnalysis() {
        if (this.isLoading) return;
        this.isLoading = true;
        document.getElementById('ub-loading').style.display = 'flex';
        document.getElementById('ub-content').style.display = 'none';
        document.getElementById('ub-analyze-btn').disabled = true;
        
        try {
            const res = await fetch('api/unified-brain.php', { method: 'POST' });
            if (!res.ok) throw new Error('Network response was not ok');
            const data = await res.json();
            this.lastData = data;
            this.updateHealthUI(data);
            this.updateDepartmentsUI(data);
            this.updateRecommendationsUI(data);
        } catch(e) {
            document.getElementById('ub-recs-content').innerHTML = '<span style="color:#f87171">تعذر الاتصال بخادم الذكاء الاصطناعي</span>';
        } finally {
            this.isLoading = false;
            document.getElementById('ub-loading').style.display = 'none';
            document.getElementById('ub-content').style.display = 'block';
            document.getElementById('ub-analyze-btn').disabled = false;
        }
    },
    
    updateHealthUI(data) {
        const score = data.health_score || 0;
        const label = data.health_label || '--';
        
        const numEl = document.getElementById('ub-health-number');
        if (numEl) {
            this.animateNumber(numEl, parseInt(numEl.textContent) || 0, score, 1200);
        }
        const labelEl = document.getElementById('ub-health-label');
        if (labelEl) labelEl.textContent = label;
        
        const circle = document.getElementById('ub-health-progress');
        if (circle) {
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
            if (numEl) numEl.style.color = color;
        }
        
        const factorsEl = document.getElementById('ub-health-factors');
        if (factorsEl) {
            if (data.health_factors && data.health_factors.length > 0) {
                factorsEl.innerHTML = data.health_factors.map(f => 
                    `<span class="ub-factor-tag">⚡ ${f}</span>`
                ).join('');
            } else {
                factorsEl.innerHTML = '<span class="ub-factor-tag ub-factor-good">✅ الوضع العام ممتاز</span>';
            }
        }
    },
    
    updateDepartmentsUI(data) {
        const grid = document.getElementById('ub-depts-grid');
        if (!grid) return;
        const depts = data.departments || {};
        const deptConfig = {
            inventory: { 
                svg: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z"/><path d="M6 18h12"/><path d="M6 14h12"/><path d="M12 22V10"/></svg>`, 
                label: 'المخازن', 
                color: '#fbbf24' 
            },
            treasury: { 
                svg: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="12" x2="14" y2="10"/></svg>`, 
                label: 'الخزينة', 
                color: '#10b981' 
            },
            sales: { 
                svg: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>`, 
                label: 'المبيعات', 
                color: '#38bdf8' 
            },
            customers: { 
                svg: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c084fc" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 1 0 7.75"/></svg>`, 
                label: 'العملاء', 
                color: '#c084fc' 
            }
        };
        
        const statusColors = { good: '#10b981', warning: '#f59e0b', critical: '#f87171' };
        const statusLabels = { good: 'جيد', warning: 'تحذير', critical: 'حرج' };
        
        grid.innerHTML = Object.entries(deptConfig).map(([key, cfg]) => {
            const dept = depts[key] || { status: 'good', score: 0, summary: 'لا بيانات' };
            const statusColor = statusColors[dept.status] || '#94a3b8';
            return `
                <div class="ub-dept-card">
                    <div class="ub-dept-header">
                        <div class="ub-dept-icon" style="color: ${cfg.color}; display: flex; align-items: center; justify-content: center;">${cfg.svg}</div>
                        <div class="ub-dept-name">${cfg.label}</div>
                        <div class="ub-dept-status" style="background: ${statusColor}20; color: ${statusColor}; border: 1px solid ${statusColor}40">
                            <span class="ub-status-dot" style="background: ${statusColor}"></span>
                            ${statusLabels[dept.status] || dept.status}
                        </div>
                    </div>
                    <div class="ub-dept-summary">${dept.summary}</div>
                </div>
            `;
        }).join('');
    },
    
    updateRecommendationsUI(data) {
        const content = document.getElementById('ub-recs-content');
        if (!content) return;
        if (data.ai_recommendations) {
            let html = data.ai_recommendations
                .replace(/\*\*(.*?)\*\*/g, '<strong style="color:#38bdf8">$1</strong>')
                .replace(/🔴/g, '<span style="color:#f87171">🔴</span>')
                .replace(/🟡/g, '<span style="color:#fbbf24">🟡</span>')
                .replace(/🟢/g, '<span style="color:#34d399">🟢</span>')
                .replace(/\n/g, '<br>');
            content.innerHTML = html;
            content.className = 'ub-recs-content';
        } else {
            content.innerHTML = 'لم تتوفر توصيات. حاول مرة أخرى.';
        }
    },
    
    showBadge(count, type) {
        const badge = document.getElementById('ub-fab-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = 'flex';
        }
        const fab = document.getElementById('ub-fab');
        if (fab && type === 'critical') {
            fab.classList.add('ub-fab--alert');
        }
    },
    
    animateNumber(el, start, end, duration) {
        const range = end - start;
        const startTime = performance.now();
        function step(timestamp) {
            const elapsed = timestamp - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            el.textContent = Math.round(start + range * eased);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => UnifiedBrain.init(), 1000);
});
