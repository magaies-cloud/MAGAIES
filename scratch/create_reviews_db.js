const { Client } = require('pg');

async function createReviewsSystem() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        console.log("Adding rating columns to products table...");
        await client.query(`
            ALTER TABLE products ADD COLUMN IF NOT EXISTS average_rating NUMERIC(3,2) DEFAULT 0.0;
            ALTER TABLE products ADD COLUMN IF NOT EXISTS reviews_count INTEGER DEFAULT 0;
        `);

        console.log("Creating product_reviews table...");
        await client.query(`
            CREATE TABLE IF NOT EXISTS product_reviews (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                product_id UUID REFERENCES products(id) ON DELETE CASCADE,
                buyer_id UUID REFERENCES users(id) ON DELETE CASCADE,
                rating INTEGER CHECK (rating >= 1 AND rating <= 5) NOT NULL,
                comment TEXT,
                created_at TIMESTAMPTZ DEFAULT NOW(),
                UNIQUE(product_id, buyer_id)
            );
        `);

        // Enable RLS
        await client.query(`
            ALTER TABLE product_reviews ENABLE ROW LEVEL SECURITY;
            
            DROP POLICY IF EXISTS "Public can read reviews" ON product_reviews;
            CREATE POLICY "Public can read reviews" ON product_reviews 
            FOR SELECT USING (true);
            
            DROP POLICY IF EXISTS "Buyers can insert their own reviews" ON product_reviews;
            CREATE POLICY "Buyers can insert their own reviews" ON product_reviews 
            FOR INSERT WITH CHECK (auth.uid() = buyer_id);
        `);

        console.log("Setting up automatic trigger to update product rating...");
        await client.query(`
            CREATE OR REPLACE FUNCTION update_product_rating()
            RETURNS TRIGGER AS $$
            DECLARE
                avg_rating NUMERIC(3,2);
                count_reviews INTEGER;
            BEGIN
                -- Calculate new average and count
                SELECT ROUND(AVG(rating)::numeric, 2), COUNT(rating)
                INTO avg_rating, count_reviews
                FROM product_reviews
                WHERE product_id = NEW.product_id;
                
                -- Update products table
                UPDATE products 
                SET average_rating = COALESCE(avg_rating, 0.0),
                    reviews_count = COALESCE(count_reviews, 0)
                WHERE id = NEW.product_id;
                
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS on_review_added_trigger ON product_reviews;
            CREATE TRIGGER on_review_added_trigger
            AFTER INSERT OR UPDATE ON product_reviews
            FOR EACH ROW
            EXECUTE FUNCTION update_product_rating();
            
            DROP TRIGGER IF EXISTS on_review_deleted_trigger ON product_reviews;
            CREATE TRIGGER on_review_deleted_trigger
            AFTER DELETE ON product_reviews
            FOR EACH ROW
            EXECUTE FUNCTION update_product_rating();
        `);

        console.log("Reviews system database setup complete!");
        
    } catch (e) {
        console.log("Error:", e.message);
    } finally {
        await client.end();
    }
}
createReviewsSystem();
