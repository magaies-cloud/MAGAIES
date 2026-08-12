<?php
// /root/auto_ssl.php
$envPath = '/var/www/magaees/.env';
if (!file_exists($envPath)) { die("Error: .env not found\n"); }

$envVars = [];
$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $envVars[trim($key)] = trim($value);
    }
}

$host = $envVars['DB_HOST'] ?? '127.0.0.1';
$port = $envVars['DB_PORT'] ?? '3306';
$db   = $envVars['DB_NAME'] ?? 'magaees';
$user = $envVars['DB_USER'] ?? 'root';
$pass = $envVars['DB_PASS'] ?? '';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage() . "\n");
}

// Get all subdomains
$stmt = $pdo->query("SELECT subdomain FROM tenants WHERE status = 'active'");
$subdomains = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!in_array('c', $subdomains)) { $subdomains[] = 'c'; }

$reloadNginx = false;

foreach ($subdomains as $sub) {
    if (empty($sub) || $sub === 'www') continue;
    $domain = $sub . ".elsajana.com";
    $wwwDomain = "www." . $domain;
    
    if (!is_dir("/etc/letsencrypt/live/$domain")) {
        echo "[" . date('Y-m-d H:i:s') . "] Generating SSL for $domain and $wwwDomain...\n";
        $cmd = "certbot certonly --webroot -w /var/www/magaees -d " . escapeshellarg($domain) . " -d " . escapeshellarg($wwwDomain) . " --non-interactive --agree-tos --email C00105@gmail.com";
        system($cmd, $retval);
        
        if ($retval === 0) {
            echo "[" . date('Y-m-d H:i:s') . "] Successfully generated SSL for $domain.\n";
        } else {
            echo "[" . date('Y-m-d H:i:s') . "] Failed to generate SSL for $domain.\n";
            continue;
        }
    }
    
    $confFile = "/etc/nginx/sites-available/$domain.conf";
    if (!file_exists($confFile)) {
        echo "[" . date('Y-m-d H:i:s') . "] Creating Nginx config for $domain...\n";
        $nginxConfig = <<<NGINX
server {
    listen 80;
    server_name $domain $wwwDomain;
    return 301 https://\$host\$request_uri;
}

server {
    listen 443 ssl;
    server_name $domain $wwwDomain;
    root /var/www/magaees;
    index index.html login.html index.php;

    ssl_certificate /etc/letsencrypt/live/$domain/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/$domain/privkey.pem;
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         HIGH:!aNULL:!MD5;

    include /etc/nginx/snippets/saas-rules.conf;
}
NGINX;
        file_put_contents($confFile, $nginxConfig);
        symlink($confFile, "/etc/nginx/sites-enabled/$domain.conf");
        $reloadNginx = true;
    }
}

if ($reloadNginx) {
    echo "[" . date('Y-m-d H:i:s') . "] Reloading Nginx...\n";
    system("systemctl reload nginx");
}

?>
