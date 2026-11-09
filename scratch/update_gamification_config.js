const { Client } = require('pg');

async function updateGamificationConfig() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        await client.query(`
            -- Create settings table
            CREATE TABLE IF NOT EXISTS gamification_settings (
                id INTEGER PRIMARY KEY DEFAULT 1,
                points_per_order INTEGER DEFAULT 10,
                sales_threshold_sdg INTEGER DEFAULT 50000,
                points_per_sales_threshold INTEGER DEFAULT 1,
                penalty_cancel_order INTEGER DEFAULT 20,
                CONSTRAINT single_row CHECK (id = 1)
            );

            -- Insert default row if not exists
            INSERT INTO gamification_settings (id, points_per_order, sales_threshold_sdg, points_per_sales_threshold, penalty_cancel_order)
            VALUES (1, 10, 50000, 1, 20)
            ON CONFLICT (id) DO NOTHING;

            -- Enable RLS and add policy for public read, admin update (for simplicity, we'll allow all authenticated users to read)
            ALTER TABLE gamification_settings ENABLE ROW LEVEL SECURITY;
            
            DROP POLICY IF EXISTS "Enable read access for all users" ON gamification_settings;
            CREATE POLICY "Enable read access for all users" ON gamification_settings FOR SELECT USING (true);
            
            DROP POLICY IF EXISTS "Enable update for all" ON gamification_settings;
            CREATE POLICY "Enable update for all" ON gamification_settings FOR UPDATE USING (true);

            -- Update the trigger function to use dynamic values
            CREATE OR REPLACE FUNCTION update_merchant_points_on_order()
            RETURNS TRIGGER AS $$
            DECLARE
                merchant_id UUID;
                points_to_add INTEGER := 0;
                cfg_points_order INTEGER;
                cfg_threshold INTEGER;
                cfg_points_threshold INTEGER;
                cfg_penalty INTEGER;
            BEGIN
                IF (TG_OP = 'UPDATE' AND NEW.status != OLD.status) THEN
                    SELECT user_id INTO merchant_id FROM stores WHERE id = NEW.store_id;

                    IF merchant_id IS NOT NULL THEN
                        -- Fetch config
                        SELECT points_per_order, sales_threshold_sdg, points_per_sales_threshold, penalty_cancel_order
                        INTO cfg_points_order, cfg_threshold, cfg_points_threshold, cfg_penalty
                        FROM gamification_settings WHERE id = 1;

                        IF NEW.status = 'delivered' THEN
                            points_to_add := cfg_points_order;
                            
                            IF cfg_threshold > 0 THEN
                                points_to_add := points_to_add + (FLOOR(NEW.total_sdg / cfg_threshold) * cfg_points_threshold)::INTEGER;
                            END IF;
                            
                        ELSIF NEW.status = 'cancelled' THEN
                            points_to_add := -cfg_penalty;
                        END IF;

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

        `);
        console.log("Success: Added gamification settings and updated trigger");
    } catch (e) {
        console.log("Error:", e.message);
    } finally {
        await client.end();
    }
}
updateGamificationConfig();
