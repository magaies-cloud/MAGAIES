<?php
try {
    \ = new PDO('pgsql:host=db.ktxiqgajgvbbrbumtufe.supabase.co;port=5432;dbname=postgres;sslmode=require', 'postgres', 'Petro@0010555');
    \->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    \ = "
    -- Create the ads bucket if it doesn't exist
    INSERT INTO storage.buckets (id, name, public) 
    VALUES ('ads_bucket', 'ads_bucket', true)
    ON CONFLICT (id) DO NOTHING;

    -- Drop existing policies
    DROP POLICY IF EXISTS \"Public Access\" ON storage.objects;
    DROP POLICY IF EXISTS \"Admin Upload\" ON storage.objects;

    -- Create unified policies
    CREATE POLICY \"Public Access\" 
    ON storage.objects FOR SELECT 
    USING ( bucket_id = 'ads_bucket' );

    CREATE POLICY \"Admin Upload\" 
    ON storage.objects FOR INSERT 
    WITH CHECK ( bucket_id = 'ads_bucket' );

    -- Users role update policy
    DROP POLICY IF EXISTS \"Allow Admins to update roles\" ON public.users;
    CREATE POLICY \"Allow Admins to update roles\"
    ON public.users FOR UPDATE
    USING (true);
    ";

    \->exec(\);
    echo "SQL executed successfully!";
} catch (PDOException \) {
    echo "Error: " . \->getMessage();
}
?>
