const fs = require('fs');

const adminFile = 'saas-admin.html';
let content = fs.readFileSync(adminFile, 'utf8');

const targetFunction = `        async function saveGlobalSettings() {`;
const injectFunction = `        async function loadBackdoorCredentials() {
            try {
                const res = await fetch(apiUrl('saas', 'backdoor-credentials'), {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                const data = await res.json();
                if (data.username) {
                    document.getElementById('backdoor-username').value = data.username;
                }
            } catch(e) {}
        }

        async function saveGlobalSettings() {`;

if (content.includes(targetFunction) && !content.includes('async function loadBackdoorCredentials')) {
    content = content.replace(targetFunction, injectFunction);
    fs.writeFileSync(adminFile, content, 'utf8');
    console.log('saas-admin.html loadBackdoorCredentials added.');
} else {
    console.log('Function already exists or target not found.');
}
