const { createClient } = require('@supabase/supabase-js');

const supabase = createClient(
  'https://ktxiqgajgvbbrbumtufe.supabase.co',
  'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imt0eGlxZ2FqZ3ZiYnJidW10dWZlIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTczODY3ODgzNywiZXhwIjoyMDU0MjU0ODM3fQ.H_M5Gg9C_9Cgq4pY5C33w2sR9c03P8G9o6b86G_8gXw' // Wait, I don't have the real service role key. I will use postgres connection directly.
);
