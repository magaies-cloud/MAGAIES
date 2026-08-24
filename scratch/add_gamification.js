const { Client } = require('pg');

async function addGamification() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        await client.query(`
            -- Add columns
            ALTER TABLE users ADD COLUMN IF NOT EXISTS merchant_points INTEGER DEFAULT 0;
            ALTER TABLE users ADD COLUMN IF NOT EXISTS merchant_tier TEXT DEFAULT 'beginner';

            -- Create function to update points on order completion
            CREATE OR REPLACE FUNCTION update_merchant_points_on_order()
            RETURNS TRIGGER AS $$
            DECLARE
                merchant_id UUID;
                points_to_add INTEGER := 0;
            BEGIN
                -- Only care if status changed to 'delivered' or 'cancelled'
                IF (TG_OP = 'UPDATE' AND NEW.status != OLD.status) THEN
                    -- Get the merchant's user_id from the store_id
                    SELECT user_id INTO merchant_id FROM stores WHERE id = NEW.store_id;

                    IF merchant_id IS NOT NULL THEN
                        IF NEW.status = 'delivered' THEN
                            -- +10 for completion
                            points_to_add := 10;
                            -- +1 for every 50,000 SDG
                            points_to_add := points_to_add + FLOOR(NEW.total_sdg / 50000)::INTEGER;
                            
                        ELSIF NEW.status = 'cancelled' THEN
                            -- Assuming cancelled by merchant or failed, -20 points
                            points_to_add := -20;
                        END IF;

                        -- Update points
                        IF points_to_add != 0 THEN
                            UPDATE users 
                            SET merchant_points = GREATEST(0, COALESCE(merchant_points, 0) + points_to_add)
                            WHERE id = merchant_id;
                        END IF;
                    END IF;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            -- Create trigger
            DROP TRIGGER IF EXISTS trg_update_merchant_points ON orders;
            CREATE TRIGGER trg_update_merchant_points
            AFTER UPDATE ON orders
            FOR EACH ROW
            EXECUTE FUNCTION update_merchant_points_on_order();

            -- Create function to calculate tier based on points
            CREATE OR REPLACE FUNCTION update_merchant_tier()
            RETURNS TRIGGER AS $$
            BEGIN
                IF (TG_OP = 'UPDATE' AND NEW.merchant_points != OLD.merchant_points) THEN
                    IF NEW.merchant_points >= 5000 THEN
                        NEW.merchant_tier := 'gold';
                    ELSIF NEW.merchant_points >= 2000 THEN
                        NEW.merchant_tier := 'silver';
                    ELSIF NEW.merchant_points >= 500 THEN
                        NEW.merchant_tier := 'bronze';
                    ELSE
                        NEW.merchant_tier := 'beginner';
                    END IF;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            -- Create trigger for tier calculation
            DROP TRIGGER IF EXISTS trg_update_merchant_tier ON users;
            CREATE TRIGGER trg_update_merchant_tier
            BEFORE UPDATE ON users
            FOR EACH ROW
            EXECUTE FUNCTION update_merchant_tier();

        `);
        console.log("Success: Added gamification columns and triggers");
    } catch (e) {
        console.log("Error:", e.message);
    } finally {
        await client.end();
    }
}
addGamification();
