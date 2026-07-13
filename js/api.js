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

    const tenantSubdomain = getSubdomain();
    if (tenantSubdomain) {
        headers['X-Tenant-Subdomain'] = tenantSubdomain;
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
            window.location.href = 'login.php';
            throw new Error('Unauthorized');
        }

        const data = await response.json();
        
        if (!response.ok || data.error || data.success === false) {
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
    // 1. Check URL parameters for localhost testing
    const params = new URLSearchParams(window.location.search);
    if (params.has('tenant')) {
        return params.get('tenant');
    }
    
    // 2. Check hostname for live environment
    const host = window.location.hostname;
    if (/^[0-9.]+$/.test(host)) {
        return null;
    }
    const parts = host.split('.');
    
    // For localhost testing with modified hosts file (e.g. alofoq.localhost)
    if (host.endsWith('.localhost') && parts.length >= 2) {
        return parts[0];
    }
    
    // For live environment (e.g. alofoq.elsajana.com)
    if (parts.length > 2) {
        return parts[0];
    }
    return null;
}
