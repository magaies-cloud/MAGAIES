const { Client } = require('pg');

async function createBuyerNotifications() {
    const client = new Client({
        connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
    });
    await client.connect();
    try {
        console.log("Setting up automatic triggers for buyer notifications...");
        
        await client.query(`
            CREATE OR REPLACE FUNCTION notify_buyer_on_order_status()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.status != OLD.status THEN
                    DECLARE
                        notif_title VARCHAR;
                        notif_body TEXT;
                    BEGIN
                        IF NEW.status = 'processing' THEN
                            notif_title := 'طلبك قيد التجهيز ⚙️';
                            notif_body := 'تم قبول طلبك رقم ' || substr(NEW.id::text, 1, 8) || ' وجاري تجهيزه.';
                        ELSIF NEW.status = 'shipped' THEN
                            notif_title := 'طلبك في الطريق 🚚';
                            notif_body := 'تم شحن طلبك رقم ' || substr(NEW.id::text, 1, 8) || ' وهو في طريقه إليك.';
                        ELSIF NEW.status = 'delivered' THEN
                            notif_title := 'تم تسليم الطلب ✅';
                            notif_body := 'تم تسليم طلبك رقم ' || substr(NEW.id::text, 1, 8) || '. لا تنسَ التقييم!';
                        ELSIF NEW.status = 'cancelled' THEN
                            notif_title := 'تم إلغاء الطلب ❌';
                            notif_body := 'تم إلغاء طلبك رقم ' || substr(NEW.id::text, 1, 8) || '.';
                        END IF;

                        IF notif_title IS NOT NULL THEN
                            INSERT INTO notifications (user_id, title, body, type, related_id)
                            VALUES (
                                NEW.buyer_id,
                                notif_title,
                                notif_body,
                                'order_status_' || NEW.status,
                                NEW.id
                            );
                        END IF;
                    END;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS on_order_status_trigger ON orders;
            CREATE TRIGGER on_order_status_trigger
            AFTER UPDATE ON orders
            FOR EACH ROW
            EXECUTE FUNCTION notify_buyer_on_order_status();
        `);

        console.log("Buyer notifications database setup complete!");
    } catch (e) {
        console.log("Error:", e.message);
    } finally {
        await client.end();
    }
}
createBuyerNotifications();
