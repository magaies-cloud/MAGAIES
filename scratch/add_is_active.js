const { Client } = require('pg');

async function run() {
  const client = new Client({
    connectionString: 'postgres://postgres.ktxiqgajgvbbrbumtufe:Petro@0010555@aws-0-ap-northeast-2.pooler.supabase.com:6543/postgres'
  });
  
  await client.connect();
  
  try {
    await client.query(`
      ALTER TABLE public.products ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE;
    `);
    console.log("Added is_active column successfully");
  } catch (err) {
    console.error(err);
  } finally {
    await client.end();
  }
}

run();
