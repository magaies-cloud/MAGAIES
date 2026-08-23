<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!extension_loaded('pdo_pgsql')) {
    echo "pdo_pgsql is not loaded.\n";
} else {
    echo "pdo_pgsql is loaded.\n";
}

try {
    \ = new PDO('pgsql:host=db.ktxiqgajgvbbrbumtufe.supabase.co;port=5432;dbname=postgres;sslmode=require', 'postgres', 'Petro@0010555');
    \->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected via IPv6!\n";
} catch (Exception \) {
    echo "Error: " . \->getMessage();
}
?>
