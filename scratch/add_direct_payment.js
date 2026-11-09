const { Client } = require('pg');

async function addDirectPaymentFields() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        await client.query(`
            ALTER TABLE users 
            ADD COLUMN IF NOT EXISTS direct_payment_enabled BOOLEAN DEFAULT false,
            ADD COLUMN IF NOT EXISTS bank_name VARCHAR(100),
            ADD COLUMN IF NOT EXISTS bank_account_name VARCHAR(100),
            ADD COLUMN IF NOT EXISTS bank_account_number VARCHAR(100);
        `);
        console.log("Success: Added direct payment fields to users table");
    } catch (e) {
        console.log("Error:", e.message);
    } finally {
        await client.end();
    }
}
addDirectPaymentFields();
