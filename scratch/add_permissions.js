const { Client } = require('pg');

async function addPermissions() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        await client.query(`
            ALTER TABLE users ADD COLUMN IF NOT EXISTS permissions JSONB DEFAULT '{}'::jsonb;
        `);
        console.log("Success: Added permissions column to users");
    } catch (e) {
        console.log("Error:", e.message);
    } finally {
        await client.end();
    }
}
addPermissions();
