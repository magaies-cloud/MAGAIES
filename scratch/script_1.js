
    // Make Sidebar Draggable and Resizable, with Permanent Saving
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('settings-sidebar');
        const header = sidebar.querySelector('.sidebar-header');
        let isDragging = false;
        let startX, startY, initialLeft, initialTop;

        header.addEventListener('mousedown', (e) => {
            if(e.target.classList.contains('close-sidebar')) return;
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            
            // get actual computed style
            const style = window.getComputedStyle(sidebar);
            initialLeft = parseInt(style.left, 10);
            initialTop = parseInt(style.top, 10);
            
            // Disable translate centering so top/left work cleanly
            sidebar.style.transform = 'none';
            sidebar.style.left = initialLeft + 'px';
            sidebar.style.top = initialTop + 'px';
            
            sidebar.style.transition = 'none'; // disable CSS transition during drag
            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            sidebar.style.left = (initialLeft + dx) + 'px';
            sidebar.style.top = (initialTop + dy) + 'px';
        });

        document.addEventListener('mouseup', () => {
            if(isDragging) {
                isDragging = false;
                document.body.style.userSelect = 'auto';
                saveSidebarConfig();
            }
        });
        
        // Listen for resize changes via MutationObserver
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "style" && sidebar.classList.contains('open') && !isDragging) {
                   saveSidebarConfig();
                }
            });
        });
        observer.observe(sidebar, { attributes: true });

        function saveSidebarConfig() {
            if (!window.APP_READY) return; // Prevent wiping settings before DB is loaded!
            if(typeof getTenantKey === 'function') {
                let settings = {};
                try {
                    const raw = localStorage.getItem('erp_settings_v1');
                    if (raw) settings = JSON.parse(raw);
                } catch(e) {}
                
                settings.sgl_sidebar_ui = {
                    left: sidebar.style.left,
                    top: sidebar.style.top,
                    width: sidebar.style.width,
                    height: sidebar.style.height,
                    transform: sidebar.style.transform
                };
                
                localStorage.setItem('erp_settings_v1', JSON.stringify(settings));
            }
        }
    });
