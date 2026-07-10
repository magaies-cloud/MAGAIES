<?php $page_title = 'إدارة الشركاء 2026 - مقاييس (MAGAIEES)'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>إدارة الشركاء 2026 - مقاييس (MAGAIEES)</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
        <style>
        .container { padding: 20px; padding-bottom: 100px; }
        
        .tabs {
            display: flex;
            background: var(--surface-color);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--glass-border);
        }
        .tab-btn {
            flex: 1;
            padding: 15px;
            text-align: center;
            background: none;
            border: none;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s;
        }
        .tab-btn.active {
            background: var(--primary-accent);
            color: white;
        }

        .form-group { margin-bottom: 15px; }
        .form-label { display: block; margin-bottom: 5px; font-size: 0.9rem; color: var(--text-secondary); }
        .form-input, .form-select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--glass-border);
            background: var(--bg-color);
            color: var(--text-primary);
        }
        .btn-add {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background: var(--success-color);
            color: white;
            font-weight: 800;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }

        .partners-list { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }
        .partner-card {
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 10px;
            animation: fadeIn 0.3s ease-out;
        }
        .partner-name { font-weight: 800; font-size: 1.1rem; }
        .partner-type { font-size: 0.8rem; color: white; padding: 3px 8px; border-radius: 5px; }
        .type-CUSTOMER { background: #38bdf8; }
        .type-SUPPLIER { background: #fbbf24; color: #1e293b; }
        .type-EMPLOYEE { background: #34d399; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>


    <nav class="navbar glass-panel">
        <button class="back-btn" onclick="history.back()">🔙</button>
        <h1 class="brand-title">إدارة جهات الاتصال</h1>
        <div style="width: 30px;"></div>
    </nav>

    <div class="container">
        <!-- Add New Form -->
        <div class="glass-panel" style="padding: 20px; border-radius: 15px;">
            <h3 style="margin-top: 0; color: var(--primary-accent);">إضافة شريك جديد</h3>
            
            <div class="form-group">
                <label class="form-label">الاسم</label>
                <input type="text" class="form-input" id="p-name" placeholder="مثال: أحمد (عميل)، شركة كذا...">
            </div>
            
            <div class="form-group">
                <label class="form-label">النوع</label>
                <select class="form-select" id="p-type">
                    <option value="CUSTOMER">عميل (مبيعات)</option>
                    <option value="SUPPLIER">مورد (مشتريات/مصروفات)</option>
                    <option value="EMPLOYEE">موظف</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">رقم الهاتف (اختياري)</label>
                <input type="text" class="form-input" id="p-phone">
            </div>

            <button class="btn-add" onclick="addPartner()">+ حفظ الشريك</button>
        </div>

        <!-- Filters -->
        <div class="tabs" style="margin-top: 25px;">
            <button class="tab-btn active" onclick="loadPartners('')">الكل</button>
            <button class="tab-btn" onclick="loadPartners('CUSTOMER')">العملاء</button>
            <button class="tab-btn" onclick="loadPartners('SUPPLIER')">الموردون</button>
        </div>

        <!-- List -->
        <div class="partners-list" id="list-container">
            <!-- Populated via JS -->
            <div style="text-align:center; color:var(--text-secondary);">جاري التحميل...</div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=1783054968.70701"></script>
    <script>
        // Mock data for UI preview
        let localPartners = [
            { id: 1, name: 'شركة مقاييس التجارية', type: 'CUSTOMER', phone: '0912345678' },
            { id: 2, name: 'مصنع الإسمنت الحديث', type: 'SUPPLIER', phone: '0123456789' },
            { id: 3, name: 'أحمد محمد إبراهيم', type: 'EMPLOYEE', phone: '0111111111' }
        ];

        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) {
                window.location.href = 'login.php';
            }
            loadPartners('');
        });

        function loadPartners(filterType) {
            // Update active tab UI
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            if(event && event.target && event.target.classList) {
                 event.target.classList.add('active');
            } else {
                 document.querySelector('.tab-btn').classList.add('active');
            }

            const container = document.getElementById('list-container');
            container.innerHTML = '';

            let filtered = localPartners;
            if (filterType) {
                filtered = localPartners.filter(p => p.type === filterType);
            }

            if (filtered.length === 0) {
                container.innerHTML = '<div style="text-align:center;">لا توجد بيانات</div>';
                return;
            }

            filtered.forEach(p => {
                const typeLabel = p.type === 'CUSTOMER' ? 'عميل' : p.type === 'SUPPLIER' ? 'مورد' : 'موظف';
                container.innerHTML += \`
                    <div class="partner-card glass-panel">
                        <div>
                            <div class="partner-name">\${p.name}</div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top:5px;">\${p.phone || 'لا يوجد هاتف'}</div>
                        </div>
                        <div class="partner-type type-\${p.type}">\${typeLabel}</div>
                    </div>
                \`;
            });
        }

        async function addPartner() {
            const name = document.getElementById('p-name').value;
            const type = document.getElementById('p-type').value;
            const phone = document.getElementById('p-phone').value;

            if (!name) {
                alert('يرجى إدخال الاسم');
                return;
            }

            // Mock backend call
            localPartners.unshift({
                id: Date.now(),
                name, type, phone
            });

            alert('تمت الإضافة بنجاح!');
            document.getElementById('p-name').value = '';
            document.getElementById('p-phone').value = '';
            
            // Reload the currently active tab
            document.querySelector('.tab-btn.active').click();
        }
    </script>
</body>
</html>

