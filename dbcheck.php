<?php
// ============================================================
// DB DIAGNOSTIC PAGE — remove after debugging is complete
// Access at: https://your-site.vercel.app/dbcheck.php
// ============================================================

// Simple secret key to prevent public access
$key = $_GET['key'] ?? '';
if ($key !== 'membley2026') {
    http_response_code(403);
    die('403 Forbidden. Append ?key=membley2026 to the URL.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DB Diagnostic — Membley</title>
<style>
  body { font-family: monospace; background: #1a1a2e; color: #e0e0e0; padding: 2rem; }
  h2 { color: #d99e1a; border-bottom: 1px solid #444; padding-bottom: .5rem; }
  .ok  { color: #4ade80; font-weight: bold; }
  .err { color: #f87171; font-weight: bold; }
  .warn{ color: #fbbf24; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
  td, th { padding: .4rem .8rem; border: 1px solid #333; text-align: left; font-size: .85rem; }
  th { background: #002f5d; color: #d99e1a; }
  tr:nth-child(even) { background: #111827; }
  pre { background: #0f172a; padding: 1rem; border-radius: 6px; overflow-x: auto; font-size: .8rem; }
</style>
</head>
<body>

<h2>🔍 Environment Variables (DB-related)</h2>
<table>
<tr><th>Variable</th><th>Value (first 80 chars)</th><th>Status</th></tr>
<?php
$dbVars = ['DATABASE_URL','POSTGRES_URL','POSTGRES_PRISMA_URL','POSTGRES_URL_NON_POOLING',
           'SUPABASE_DB_URL','SUPABASE_URL','SUPABASE_ANON_KEY','SUPABASE_SERVICE_ROLE_KEY',
           'SUPABASE_PROJECT_REF','SUPABASE_DB_PASSWORD','PGHOST','PGPORT','PGUSER','PGDATABASE'];

foreach ($dbVars as $var) {
    $val = getenv($var) ?: ($_ENV[$var] ?? ($_SERVER[$var] ?? null));
    $display = $val ? htmlspecialchars(substr($val, 0, 80)) . (strlen($val) > 80 ? '...' : '') : '(not set)';
    $status  = $val ? '<span class="ok">✓ SET</span>' : '<span class="warn">— missing</span>';
    echo "<tr><td>$var</td><td>$display</td><td>$status</td></tr>\n";
}
?>
</table>

<h2>🌐 Pooler Region Tests</h2>
<p class="warn">Testing all Supabase pooler regions with your project ref + password...</p>
<table>
<tr><th>Region / Host</th><th>Port</th><th>Result</th></tr>
<?php
$ref  = getenv('SUPABASE_PROJECT_REF') ?: 'ohhnfwxeuwkebyvogwkd';
$pass = getenv('SUPABASE_DB_PASSWORD')  ?: '00110211946150';

// Also try to extract from DATABASE_URL if set
$dbUrl = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL') ?? '';
if ($dbUrl) {
    $p = parse_url($dbUrl);
    if (!empty($p['pass'])) $pass = urldecode($p['pass']);
    // extract ref from user like postgres.XXXX
    if (!empty($p['user']) && str_contains($p['user'], '.')) {
        $ref = explode('.', urldecode($p['user']))[1] ?? $ref;
    }
}

$regions = [
    'aws-0-ap-southeast-1' => 'Asia Pacific (Singapore)',
    'aws-0-eu-central-1'   => 'Europe (Frankfurt)',
    'aws-0-us-east-1'      => 'US East (N. Virginia)',
    'aws-0-us-west-1'      => 'US West (N. California)',
    'aws-0-ap-northeast-1' => 'Asia Pacific (Tokyo)',
    'aws-0-ap-south-1'     => 'Asia Pacific (Mumbai)',
];

$successHost = null;

foreach ($regions as $region => $label) {
    $host = "{$region}.pooler.supabase.com";
    $user = "postgres.{$ref}";
    $dsn  = "pgsql:host={$host};port=6543;dbname=postgres;sslmode=require";
    $start = microtime(true);
    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE  => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT  => 6,
        ]);
        $ms = round((microtime(true) - $start) * 1000);
        echo "<tr><td><strong>{$host}</strong><br><small>{$label}</small></td><td>6543</td><td><span class='ok'>✅ CONNECTED in {$ms}ms</span></td></tr>\n";
        $successHost = $host;
        $pdo = null;
    } catch (PDOException $e) {
        $ms = round((microtime(true) - $start) * 1000);
        $msg = htmlspecialchars(substr($e->getMessage(), 0, 120));
        echo "<tr><td><strong>{$host}</strong><br><small>{$label}</small></td><td>6543</td><td><span class='err'>❌ {$msg}</span> <small>({$ms}ms)</small></td></tr>\n";
    }
}
?>
</table>

<?php if ($successHost): ?>
<h2>✅ Working Connection String</h2>
<pre>postgresql://postgres.<?= $ref ?>:<?= htmlspecialchars($pass) ?>@<?= $successHost ?>:6543/postgres</pre>
<p class="ok">Copy the string above into your vercel.json env block as DATABASE_URL and POSTGRES_URL.</p>
<?php else: ?>
<h2 class="err">❌ No Region Connected</h2>
<p class="err">All pooler regions failed. Your Supabase project may be <strong>PAUSED</strong>.</p>
<p>👉 Go to <a href="https://supabase.com/dashboard/project/<?= $ref ?>" style="color:#d99e1a">
   supabase.com/dashboard/project/<?= $ref ?></a> and click <strong>Restore Project</strong> if it's paused.</p>
<?php endif; ?>

<h2>🧾 Raw DATABASE_URL Parse</h2>
<pre><?php
$raw = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL') ?: '(not set)';
if ($raw !== '(not set)') {
    $p = parse_url($raw);
    $p['pass'] = $p['pass'] ? '***hidden***' : '(empty)';
    print_r($p);
} else {
    echo $raw;
}
?></pre>

</body>
</html>
