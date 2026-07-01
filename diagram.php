<?php $page_title = 'تحليل نظام السجانة (Magaees ERP)'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تحليل نظام السجانة (Magaees ERP)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
            line-height: 1.6;
        }
        h1, h2 {
            color: #2c3e50;
        }
        .mermaid {
            margin: 30px 0;
            display: flex;
            justify-content: center;
        }
        .content {
            margin-bottom: 30px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>
    <script>
        mermaid.initialize({ startOnLoad: true, theme: 'default' });
    </script>
</head>
<body>
    <h1>مخطط وتحليل نظام السجانة السحابي (ERP)</h1>
    
    <div class="content">
        <h2>1. نظرة عامة على النظام</h2>
        <p>نظام "السجانة" هو نظام ERP سحابي شامل مصمم لإدارة الموارد، المبيعات، المخازن، والحسابات. يعتمد النظام على معمارية Client-Server حديثة.</p>
    </div>

    <h2>2. المخطط الهيكلي (Architecture Diagram)</h2>
    <div class="mermaid">
        graph TD
        subgraph "واجهة المستخدم (Frontend)"
            UI[واجهات HTML/CSS/JS]
            Dash[لوحة التحكم والتقارير]
            Modules[أنظمة: مبيعات، مخازن، حسابات]
            UI --- Dash
            UI --- Modules
        end

        subgraph "الخادم الوسيط (Backend & API)"
            PHP[PHP API - api_index.php]
            Auth[نظام المصادقة والصلاحيات]
            Routing[نظام التوجيه والتحكم]
            PHP --- Auth
            PHP --- Routing
        end

        subgraph "الخدمات المساعدة (Microservices)"
            Node[Node.js Server]
            WA[خدمة الواتساب - whatsapp-web.js]
            Node --- WA
        end

        subgraph "قواعد البيانات (Database)"
            DB[(MySQL / MariaDB)]
        end

        UI <-->|AJAX / Fetch API| PHP
        PHP <-->|PDO| DB
        Node <-->|API Calls| PHP
        Modules -.-> WA
    </div>

    <div class="content">
        <h2>3. سير العمل (Workflow)</h2>
        <p><strong>أ. واجهة المستخدم:</strong> يتفاعل المستخدم مع واجهات مصممة بتقنيات HTML/CSS/JS وتصميم Glassmorphism العصري. يتم إرسال الطلبات عبر AJAX إلى الخادم.</p>
        <p><strong>ب. معالجة البيانات (Backend):</strong> يستقبل ملف <code>api_index.php</code> الطلبات ويقوم بتوجيهها (Routing) والتحقق من الصلاحيات والتوكن.</p>
        <p><strong>ج. قاعدة البيانات:</strong> يتم تنفيذ الاستعلامات عبر <code>PDO</code> في ملف <code>api/db.php</code> للتفاعل مع جداول النظام المتعددة.</p>
        <p><strong>د. الخدمات الإضافية:</strong> يتم استخدام خادم <code>Node.js</code> لتقديم خدمات إضافية مثل إشعارات الواتساب أو التكامل مع خدمات خارجية.</p>
    </div>
</body>
</html>
