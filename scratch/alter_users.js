const { Client } = require('pg');

async function alterTable() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        await client.query(`
            ALTER TABLE users ADD COLUMN IF NOT EXISTS last_seen TIMESTAMP WITH TIME ZONE DEFAULT NOW();
        `);
        console.log("Success");
    } catch (e) {
        console.error(e);
    } finally {
        await client.end();
    }
}
alterTable();
