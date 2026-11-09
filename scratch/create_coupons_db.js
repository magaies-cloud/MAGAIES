const { Client } = require('pg');

async function createCouponsSystem() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        // 1. Add global toggle to platform settings
        await client.query(`
            ALTER TABLE gamification_settings 
            ADD COLUMN IF NOT EXISTS enable_coupons BOOLEAN DEFAULT true;
        `);
        console.log("Added enable_coupons to gamification_settings.");

        // 2. Create coupons table
        await client.query(`
            CREATE TABLE IF NOT EXISTS coupons (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                merchant_id UUID REFERENCES users(id) ON DELETE CASCADE, -- null means admin/platform coupon
                code VARCHAR(50) UNIQUE NOT NULL,
                discount_type VARCHAR(20) NOT NULL CHECK (discount_type IN ('percentage', 'fixed')),
                discount_value DECIMAL(10,2) NOT NULL,
                min_order_value DECIMAL(10,2) DEFAULT 0,
                max_discount DECIMAL(10,2), -- useful for percentage e.g., 20% off up to 5000 SDG
                usage_limit INT, -- max times this coupon can be used
                used_count INT DEFAULT 0,
                expires_at TIMESTAMPTZ,
                is_active BOOLEAN DEFAULT true,
                created_at TIMESTAMPTZ DEFAULT NOW()
            );
        `);
        console.log("Created coupons table.");

        // Enable RLS and add basic policies
        await client.query(`
            ALTER TABLE coupons ENABLE ROW LEVEL SECURITY;
            
            -- Anyone can read active coupons to apply them
            DROP POLICY IF EXISTS "Anyone can read coupons" ON coupons;
            CREATE POLICY "Anyone can read coupons" ON coupons 
            FOR SELECT USING (true);
            
            -- Merchants can manage their own coupons
            DROP POLICY IF EXISTS "Merchants manage own coupons" ON coupons;
            CREATE POLICY "Merchants manage own coupons" ON coupons 
            FOR ALL USING (auth.uid() = merchant_id);
            
            -- Admins can manage all coupons
            DROP POLICY IF EXISTS "Admins manage all coupons" ON coupons;
            CREATE POLICY "Admins manage all coupons" ON coupons 
            FOR ALL USING (
                EXISTS (
                    SELECT 1 FROM users 
                    WHERE users.id = auth.uid() 
                    AND users.role = 'admin'
                )
            );
        `);
        console.log("Applied RLS policies to coupons.");
        
    } catch (e) {
        console.log("Error:", e.message);
    } finally {
        await client.end();
    }
}
createCouponsSystem();
