const { Client } = require('pg');

async function createNotificationsSystem() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        console.log("Creating notifications table...");
        await client.query(`
            CREATE TABLE IF NOT EXISTS notifications (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                user_id UUID REFERENCES users(id) ON DELETE CASCADE,
                title VARCHAR(255) NOT NULL,
                body TEXT NOT NULL,
                type VARCHAR(50) NOT NULL,
                related_id UUID,
                is_read BOOLEAN DEFAULT false,
                created_at TIMESTAMPTZ DEFAULT NOW()
            );
        `);

        // Enable RLS
        await client.query(`
            ALTER TABLE notifications ENABLE ROW LEVEL SECURITY;
            
            DROP POLICY IF EXISTS "Users can read own notifications" ON notifications;
            CREATE POLICY "Users can read own notifications" ON notifications 
            FOR SELECT USING (auth.uid() = user_id);
            
            DROP POLICY IF EXISTS "Users can update own notifications" ON notifications;
            CREATE POLICY "Users can update own notifications" ON notifications 
            FOR UPDATE USING (auth.uid() = user_id);
        `);

        // Enable Realtime
        await client.query(`
            alter publication supabase_realtime add table notifications;
        `).catch(() => console.log("Publication already exists."));

        console.log("Setting up automatic triggers...");
        
        // Trigger: New Order -> Notify Merchant
        await client.query(`
            CREATE OR REPLACE FUNCTION notify_merchant_on_new_order()
            RETURNS TRIGGER AS $$
            DECLARE
                merchant_owner_id UUID;
            BEGIN
                -- Get the owner of the store
                SELECT owner_id INTO merchant_owner_id FROM stores WHERE id = NEW.store_id;
                
                IF merchant_owner_id IS NOT NULL THEN
                    INSERT INTO notifications (user_id, title, body, type, related_id)
                    VALUES (
                        merchant_owner_id,
                        'طلب جديد 📦',
                        'لقد تلقيت طلباً جديداً بقيمة ' || NEW.total_amount || ' ج.س',
                        'new_order',
                        NEW.id
                    );
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS on_new_order_trigger ON orders;
            CREATE TRIGGER on_new_order_trigger
            AFTER INSERT ON orders
            FOR EACH ROW
            EXECUTE FUNCTION notify_merchant_on_new_order();
        `);

        // Trigger: Payout Approved -> Notify Merchant
        await client.query(`
            CREATE OR REPLACE FUNCTION notify_merchant_on_payout()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.status = 'approved' AND OLD.status = 'pending' THEN
                    INSERT INTO notifications (user_id, title, body, type, related_id)
                    VALUES (
                        NEW.merchant_id,
                        'تم تحويل أرباحك 💰',
                        'تمت الموافقة على طلب سحب مبلغ ' || NEW.amount || ' ج.س وتحويله لحسابك.',
                        'payout_approved',
                        NEW.id
                    );
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS on_payout_approved_trigger ON merchant_payout_requests;
            CREATE TRIGGER on_payout_approved_trigger
            AFTER UPDATE ON merchant_payout_requests
            FOR EACH ROW
            EXECUTE FUNCTION notify_merchant_on_payout();
        `);

        console.log("Notifications system database setup complete!");
        
    } catch (e) {
        console.log("Error:", e.message);
    } finally {
        await client.end();
    }
}
createNotificationsSystem();
