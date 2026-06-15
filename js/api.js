// js/api.js

// السيرفر المحلي (server.js) يعيد توجيه طلبات API تلقائياً للسيرفر الحي
const API_BASE_URL = 'api_index.php';


async function apiFetch(endpoint, method = 'GET', body = null) {
    const token = localStorage.getItem('authToken');
    
    const headers = {
        'Content-Type': 'application/json'
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const options = {
        method,
        headers
    };

    if (body) {
        options.body = JSON.stringify(body);
    }

    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, options);
        
        if (response.status === 401) {
            // Token expired or invalid
            localStorage.removeItem('authToken');
            localStorage.removeItem('user');
            window.location.href = 'login.html';
            throw new Error('Unauthorized');
        }

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'API Error');
        }

        return data;
    } catch (err) {
        console.error('API Fetch Error:', err);
        throw err;
    }
}

// Utility to extract tenant subdomain if needed
function getSubdomain() {
    const host = window.location.hostname;
    const parts = host.split('.');
    if (parts.length > 2) {
        return parts[0];
    }
    return null;
}
