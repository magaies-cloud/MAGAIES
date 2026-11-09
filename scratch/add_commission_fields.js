const { Client } = require('pg');

async function addCommissionFields() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        await client.query(`
            ALTER TABLE gamification_settings 
            ADD COLUMN IF NOT EXISTS enable_commission BOOLEAN DEFAULT false,
            ADD COLUMN IF NOT EXISTS commission_percentage DECIMAL(5,2) DEFAULT 0.00;
        `);
        console.log("Success: Added commission fields to gamification_settings");
    } catch (e) {
        console.log("Error:", e.message);
    } finally {
        await client.end();
    }
}
addCommissionFields();
