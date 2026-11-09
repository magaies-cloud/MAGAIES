const { Client } = require('pg');

async function enableRealtime() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        await client.query(`
            alter publication supabase_realtime add table users;
        `);
        console.log("Success");
    } catch (e) {
        console.log("Might already be enabled:", e.message);
    } finally {
        await client.end();
    }
}
enableRealtime();
