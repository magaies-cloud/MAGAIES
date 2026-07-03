<?php $page_title = 'إدارة المهام (شغل)'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المهام (شغل) | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: 'Tajawal', sans-serif; overflow-x: hidden; }
        
        .jobs-container { max-width: 1600px; margin: 0 auto; padding: 20px 30px; height: 100vh; display: flex; flex-direction: column; }
        
        .header-panel {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px; padding: 20px 30px; margin-bottom: 20px;
            display: flex; justify-content: space-between; align-items: center;
            backdrop-filter: blur(10px);
        }

        .header-title { font-size: 1.8rem; font-weight: 900; color: #fff; margin: 0; display: flex; align-items: center; gap: 10px; }
        .header-title span { background: rgba(56, 189, 248, 0.2); padding: 10px; border-radius: 12px; color: #38bdf8; }

        .btn-action { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-family: 'Tajawal'; font-weight: bold; transition: 0.2s; text-decoration: none; display: inline-block; font-size: 1rem;}
        .btn-action:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(59,130,246,0.4); }

        .kanban-board {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; flex: 1; min-height: 0;
        }

        .kanban-col {
            background: rgba(18, 15, 25, 0.6); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);
            display: flex; flex-direction: column; overflow: hidden;
        }

        .col-header {
            padding: 15px 20px; font-size: 1.2rem; font-weight: 800; color: #fff; border-bottom: 2px solid;
            display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2);
        }
        
        .col-header .badge { background: rgba(255,255,255,0.1); padding: 2px 10px; border-radius: 20px; font-size: 0.9rem; }

        .col-todo { border-bottom-color: #3b82f6; }
        .col-doing { border-bottom-color: #fbbf24; }
        .col-done { border-bottom-color: #10b981; }

        .col-body {
            padding: 15px; overflow-y: auto; flex: 1;
        }

        .job-card {
            background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px; padding: 15px; margin-bottom: 15px; cursor: grab;
            transition: all 0.2s; position: relative; border-right: 4px solid #64748b;
        }
        
        .job-card:active { cursor: grabbing; transform: scale(0.98); }
        .job-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); border-color: rgba(255,255,255,0.2); }

        .job-card.priority-high { border-right-color: #ef4444; }
        .job-card.priority-medium { border-right-color: #f59e0b; }
        .job-card.priority-low { border-right-color: #3b82f6; }

        .job-title { font-size: 1.1rem; font-weight: 700; color: #e2e8f0; margin: 0 0 5px 0; }
        .job-desc { font-size: 0.85rem; color: #94a3b8; margin: 0 0 15px 0; line-height: 1.5; }
        
        .job-footer { display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 10px; }
        .job-user { background: rgba(56,189,248,0.1); color: #38bdf8; padding: 3px 8px; border-radius: 6px; font-weight: 600; }
        .job-date { color: #64748b; font-family: 'Outfit'; }

        /* Modal Styles */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(5px);
            display: none; justify-content: center; align-items: center; z-index: 9999;
        }
        .modal {
            background: #1e293b; border-radius: 16px; padding: 30px; width: 90%; max-width: 500px;
            border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; color: #94a3b8; margin-bottom: 5px; font-weight: 600; }
        .form-control {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
            color: white; padding: 10px 15px; border-radius: 8px; font-family: 'Tajawal'; outline: none;
        }
        .form-control:focus { border-color: #3b82f6; }

        /* Drag over effect */
        .drag-over { background: rgba(255,255,255,0.05); }

    </style>
</head>
<body>

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>


    <div class="jobs-container">
        
        <div class="premium-header">
            <div style="display:flex; align-items:center; gap:20px;">
                <h1 class="header-title"><span>💼</span> متابعة المهام (شغل)</h1>
                <a data-href="dashboard-pro.php" onclick="window.location.href='dashboard-pro.php'" style="color:#94a3b8; text-decoration:none;">الرئيسية</a>
            </div>
            <div>
                <button class="btn-action" onclick="openModal()">➕ إضافة مهمة جديدة</button>
            </div>
        </div>

        <div class="kanban-board">
            
            <div class="kanban-col" id="col-todo" ondrop="drop(event, 'todo')" ondragover="allowDrop(event)" ondragleave="dragLeave(event)">
                <div class="col-header col-todo">
                    مهام جديدة (To Do) <span class="badge" id="count-todo">0</span>
                </div>
                <div class="col-body" id="list-todo"></div>
            </div>

            <div class="kanban-col" id="col-doing" ondrop="drop(event, 'doing')" ondragover="allowDrop(event)" ondragleave="dragLeave(event)">
                <div class="col-header col-doing">
                    قيد التنفيذ (In Progress) <span class="badge" id="count-doing">0</span>
                </div>
                <div class="col-body" id="list-doing"></div>
            </div>

            <div class="kanban-col" id="col-done" ondrop="drop(event, 'done')" ondragover="allowDrop(event)" ondragleave="dragLeave(event)">
                <div class="col-header col-done">
                    مكتملة (Done) <span class="badge" id="count-done">0</span>
                </div>
                <div class="col-body" id="list-done"></div>
            </div>

        </div>

    </div>

    <!-- Add Job Modal -->
    <div class="modal-overlay" id="jobModal">
        <div class="modal">
            <h2 style="margin-top:0; color:#fff;">إضافة مهمة جديدة</h2>
            
            <div class="form-group">
                <label class="form-label">عنوان المهمة</label>
                <input type="text" id="jTitle" class="form-control" placeholder="مثال: مراجعة حساب شركة السلام">
            </div>
            
            <div class="form-group">
                <label class="form-label">التفاصيل / المطلوب</label>
                <textarea id="jDesc" class="form-control" rows="3" placeholder="اكتب تفاصيل المهمة بوضوح..."></textarea>
            </div>
            
            <div class="form-group" style="display:flex; gap:15px;">
                <div style="flex:1;">
                    <label class="form-label">إسناد إلى (الموظف)</label>
                    <select id="jUser" class="form-control">
                        <option value="">-- اختر الموظف --</option>
                    </select>
                </div>
                <div style="flex:1;">
                    <label class="form-label">الأولوية</label>
                    <select id="jPriority" class="form-control">
                        <option value="high">🔴 عاجل جداً</option>
                        <option value="medium" selected>🟡 أولوية عادية</option>
                        <option value="low">🔵 منخفضة</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:25px;">
                <button onclick="closeModal()" style="background:transparent; color:#94a3b8; border:none; padding:10px 20px; cursor:pointer; font-family:'Tajawal';">إلغاء</button>
                <button onclick="saveJob()" class="btn-action">حفظ المهمة</button>
            </div>
        </div>
    </div>

    <!-- Required for DBReady and IndexedDB -->
    <script src="js/localforage.min.js"></script>
    <script src="js/app.js?v=1783054968.0814"></script>
    <script>
        let jobs = [];

        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) { window.location.href = 'login.php'; return; }
            
            // Load Employees
            const users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]');
            const select = document.getElementById('jUser');
            users.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.fullName || u.username;
                opt.textContent = u.fullName || u.username;
                select.appendChild(opt);
            });

            // Load Jobs
            jobs = JSON.parse(localStorage.getItem('erp_jobs_v1') || '[]');
            renderJobs();
        });

        function renderJobs() {
            document.getElementById('list-todo').innerHTML = '';
            document.getElementById('list-doing').innerHTML = '';
            document.getElementById('list-done').innerHTML = '';

            let cTodo = 0, cDoing = 0, cDone = 0;

            jobs.forEach(j => {
                const card = document.createElement('div');
                card.className = `job-card priority-${j.priority}`;
                card.draggable = true;
                card.id = j.id;
                card.ondragstart = drag;

                const dateStr = new Date(j.createdAt).toLocaleDateString('ar-SA', {month:'short', day:'numeric'});

                card.innerHTML = `
                    <button onclick="deleteJob('${j.id}')" style="position:absolute; top:10px; left:10px; background:transparent; border:none; color:#64748b; cursor:pointer;" title="حذف">&times;</button>
                    <h3 class="job-title">${j.title}</h3>
                    <p class="job-desc">${j.desc || 'لا توجد تفاصيل إضافية'}</p>
                    <div class="job-footer">
                        <span class="job-user">👤 ${j.user || 'غير محدد'}</span>
                        <span class="job-date">⏱️ ${dateStr}</span>
                    </div>
                `;

                if (j.status === 'todo') { document.getElementById('list-todo').appendChild(card); cTodo++; }
                else if (j.status === 'doing') { document.getElementById('list-doing').appendChild(card); cDoing++; }
                else if (j.status === 'done') { document.getElementById('list-done').appendChild(card); cDone++; }
            });

            document.getElementById('count-todo').innerText = cTodo;
            document.getElementById('count-doing').innerText = cDoing;
            document.getElementById('count-done').innerText = cDone;
        }

        // --- Drag & Drop Logic ---
        function allowDrop(ev) {
            ev.preventDefault();
            ev.currentTarget.classList.add('drag-over');
        }

        function dragLeave(ev) {
            ev.currentTarget.classList.remove('drag-over');
        }

        function drag(ev) {
            ev.dataTransfer.setData("jobId", ev.target.id);
        }

        function drop(ev, newStatus) {
            ev.preventDefault();
            const col = document.getElementById('col-' + newStatus);
            col.classList.remove('drag-over');

            const jobId = ev.dataTransfer.getData("jobId");
            const jobIndex = jobs.findIndex(j => j.id === jobId);
            if(jobIndex > -1) {
                jobs[jobIndex].status = newStatus;
                localStorage.setItem('erp_jobs_v1', JSON.stringify(jobs));
                renderJobs();
                // Optionally log
            }
        }

        // --- Modal Logic ---
        function openModal() {
            document.getElementById('jobModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('jobModal').style.display = 'none';
            document.getElementById('jTitle').value = '';
            document.getElementById('jDesc').value = '';
        }

        function saveJob() {
            const title = document.getElementById('jTitle').value.trim();
            if(!title) { alert('الرجاء كتابة عنوان المهمة'); return; }

            const newJob = {
                id: 'job_' + Date.now(),
                title: title,
                desc: document.getElementById('jDesc').value.trim(),
                user: document.getElementById('jUser').value,
                priority: document.getElementById('jPriority').value,
                status: 'todo',
                createdAt: Date.now()
            };

            jobs.push(newJob);
            localStorage.setItem('erp_jobs_v1', JSON.stringify(jobs));
            
            closeModal();
            renderJobs();
        }

        function deleteJob(id) {
            if(confirm('هل أنت متأكد من حذف هذه المهمة نهائياً؟')) {
                jobs = jobs.filter(j => j.id !== id);
                localStorage.setItem('erp_jobs_v1', JSON.stringify(jobs));
                renderJobs();
            }
        }
    </script>
</body>
</html>
