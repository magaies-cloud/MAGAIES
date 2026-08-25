const { Client } = require('pg');

async function createPayoutsTable() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        await client.query(`
            CREATE TABLE IF NOT EXISTS merchant_payout_requests (
                id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
                merchant_id UUID REFERENCES auth.users(id) NOT NULL,
                amount DECIMAL(12, 2) NOT NULL,
                status VARCHAR(20) DEFAULT 'pending', -- pending, approved, rejected
                bank_name VARCHAR(100),
                account_name VARCHAR(100),
                account_number VARCHAR(100),
                created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
                updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
            );

            ALTER TABLE merchant_payout_requests ENABLE ROW LEVEL SECURITY;

            DROP POLICY IF EXISTS "Merchants can read their own payouts" ON merchant_payout_requests;
            CREATE POLICY "Merchants can read their own payouts" ON merchant_payout_requests FOR SELECT USING (merchant_id = auth.uid());

            DROP POLICY IF EXISTS "Merchants can insert their own payouts" ON merchant_payout_requests;
            CREATE POLICY "Merchants can insert their own payouts" ON merchant_payout_requests FOR INSERT WITH CHECK (merchant_id = auth.uid());

            DROP POLICY IF EXISTS "Admins can view all payouts" ON merchant_payout_requests;
            CREATE POLICY "Admins can view all payouts" ON merchant_payout_requests FOR SELECT USING (EXISTS (SELECT 1 FROM users WHERE id = auth.uid() AND role = 'admin'));

            DROP POLICY IF EXISTS "Admins can update payouts" ON merchant_payout_requests;
            CREATE POLICY "Admins can update payouts" ON merchant_payout_requests FOR UPDATE USING (EXISTS (SELECT 1 FROM users WHERE id = auth.uid() AND role = 'admin'));
        `);
        console.log("Success: Created merchant_payout_requests table");
    } catch (e) {
        console.log("Error:", e.message);
    } finally {
        await client.end();
    }
}
createPayoutsTable();
