const { Client } = require('pg');

async function createWishlistSystem() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        console.log("Creating wishlists table...");
        await client.query(`
            CREATE TABLE IF NOT EXISTS wishlists (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                user_id UUID REFERENCES users(id) ON DELETE CASCADE,
                product_id UUID REFERENCES products(id) ON DELETE CASCADE,
                created_at TIMESTAMPTZ DEFAULT NOW(),
                UNIQUE(user_id, product_id)
            );
        `);

        // Enable RLS
        await client.query(`
            ALTER TABLE wishlists ENABLE ROW LEVEL SECURITY;
            
            DROP POLICY IF EXISTS "Users can read own wishlist" ON wishlists;
            CREATE POLICY "Users can read own wishlist" ON wishlists 
            FOR SELECT USING (auth.uid() = user_id);
            
            DROP POLICY IF EXISTS "Users can insert into own wishlist" ON wishlists;
            CREATE POLICY "Users can insert into own wishlist" ON wishlists 
            FOR INSERT WITH CHECK (auth.uid() = user_id);
            
            DROP POLICY IF EXISTS "Users can delete from own wishlist" ON wishlists;
            CREATE POLICY "Users can delete from own wishlist" ON wishlists 
            FOR DELETE USING (auth.uid() = user_id);
        `);

        console.log("Wishlist system database setup complete!");
        
    } catch (e) {
        console.log("Error:", e.message);
    } finally {
        await client.end();
    }
}
createWishlistSystem();
