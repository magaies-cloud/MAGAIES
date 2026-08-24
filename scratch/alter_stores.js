const { Client } = require('pg');

async function alterTable() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        await client.query(`
            ALTER TABLE stores 
            ADD COLUMN IF NOT EXISTS is_open BOOLEAN DEFAULT true,
            ADD COLUMN IF NOT EXISTS return_policy TEXT,
            ADD COLUMN IF NOT EXISTS working_hours TEXT;
        `);
        console.log("Success");
    } catch (e) {
        console.error(e);
    } finally {
        await client.end();
    }
}
alterTable();
