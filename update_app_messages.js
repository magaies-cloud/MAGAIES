const fs = require('fs');

const filePath = 'c:/xampp/htdocs/magaees/js/app.js';
let content = fs.readFileSync(filePath, 'utf8');

const messageLogic = `
// ==========================================
// SYSTEM BROADCAST MESSAGES
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    // Only fetch on main pages with a header to avoid popups on login screen
    if (!document.querySelector('.header-controls') && !document.querySelector('.premium-header')) return;
    
    setTimeout(async () => {
        const token = localStorage.getItem('token');
        if (!token) return;
        
        try {
            const res = await fetch('api_index.php/system/messages/active', {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const messages = await res.json();
            
            if (messages && messages.length > 0) {
                // Filter messages based on sessionStorage if show_once is false
                const msgsToShow = messages.filter(m => {
                    if (m.show_once == 0) {
                        return !sessionStorage.getItem('msg_viewed_' + m.id);
                    }
                    return true; // if show_once == 1, backend already filters it
                });
                
                if (msgsToShow.length > 0) {
                    showSystemMessages(msgsToShow, 0);
                }
            }
        } catch (e) {
            console.log('Error fetching system messages', e);
        }
    }, 1500); // Delay slightly to let the page load completely
});

function showSystemMessages(messages, index) {
    if (index >= messages.length) return;
    
    const msg = messages[index];
    
    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.background = 'rgba(5, 3, 8, 0.85)';
    overlay.style.backdropFilter = 'blur(12px)';
    overlay.style.WebkitBackdropFilter = 'blur(12px)';
    overlay.style.zIndex = '999999';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.opacity = '0';
    overlay.style.transition = 'opacity 0.4s ease';
    
    const themeColors = {
        'info': '#38bdf8',
        'success': '#10b981',
        'warning': '#f59e0b',
        'danger': '#ef4444'
    };
    const tColor = themeColors[msg.theme] || '#38bdf8';
    const tIcon = msg.theme === 'success' ? '✅' : msg.theme === 'warning' ? '⚠️' : msg.theme === 'danger' ? '🚨' : 'ℹ️';
    
    const modal = document.createElement('div');
    modal.style.background = 'rgba(15, 23, 42, 0.7)';
    modal.style.border = '1px solid rgba(255, 255, 255, 0.1)';
    modal.style.borderTop = '4px solid ' + tColor;
    modal.style.borderRadius = '16px';
    modal.style.padding = '30px';
    modal.style.maxWidth = '500px';
    modal.style.width = '90%';
    modal.style.textAlign = 'center';
    modal.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(255,255,255,0.02)';
    modal.style.transform = 'scale(0.8) translateY(20px)';
    modal.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
    
    modal.innerHTML = \`
        <div style="font-size: 3rem; margin-bottom: 10px; animation: bounce 2s infinite;">\${tIcon}</div>
        <h2 style="color: #fff; font-weight: 900; margin-bottom: 15px; font-size: 1.5rem;">\${msg.title}</h2>
        <p style="color: var(--text-secondary); line-height: 1.8; font-size: 1.05rem; margin-bottom: 25px; white-space: pre-wrap;">\${msg.message}</p>
        <button id="close-sys-msg-btn" style="background: \${tColor}; color: #fff; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 800; font-size: 1rem; cursor: pointer; transition: transform 0.2s, filter 0.2s; box-shadow: 0 4px 15px \${tColor}40;">حسناً، فهمت</button>
    \`;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Trigger animations
    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        modal.style.transform = 'scale(1) translateY(0)';
    });
    
    // Hover effects for button
    const btn = modal.querySelector('#close-sys-msg-btn');
    btn.onmouseover = () => { btn.style.transform = 'translateY(-2px)'; btn.style.filter = 'brightness(1.1)'; };
    btn.onmouseout = () => { btn.style.transform = 'translateY(0)'; btn.style.filter = 'brightness(1)'; };
    
    btn.onclick = async () => {
        // Dismiss animation
        overlay.style.opacity = '0';
        modal.style.transform = 'scale(0.9) translateY(-20px)';
        
        // Mark as read based on show_once
        if (msg.show_once == 1) {
            try {
                await fetch('api_index.php/system/messages/mark-read', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + localStorage.getItem('token') },
                    body: JSON.stringify({ id: msg.id })
                });
            } catch (e) {}
        } else {
            sessionStorage.setItem('msg_viewed_' + msg.id, 'true');
        }
        
        setTimeout(() => {
            document.body.removeChild(overlay);
            // Show next message if any
            showSystemMessages(messages, index + 1);
        }, 400);
    };
}
`;

if (!content.includes('SYSTEM BROADCAST MESSAGES')) {
    content += '\n' + messageLogic;
    fs.writeFileSync(filePath, content, 'utf8');
    console.log('app.js updated successfully with messages logic');
} else {
    console.log('app.js already contains messages logic');
}
