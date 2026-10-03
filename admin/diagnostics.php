<?php
/**
 * admin/diagnostics.php  (previously dbcheck.php)
 *
 * Safe system diagnostics — requires admin authentication.
 * Does NOT expose credentials, passwords, tokens, or full connection strings.
 * Available only to authenticated administrators.
 */

require_once 'auth.php';
check_auth(); // Requires valid admin session — no unauthenticated access

require_once '../includes/security.php';
require_once '../includes/db.php';

$isPostgres = $isPostgres ?? false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Diagnostics — Membley SDA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .diag-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
        .diag-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem 1.25rem; }
        .diag-label { font-size: 0.8rem; text-transform: uppercase; color: #637381; font-weight: 700; }
        .diag-value { font-size: 1rem; font-weight: 600; color: var(--primary); margin-top: 0.25rem; }
        .ok { color: #16a34a; } .warn { color: #b45309; } .err { color: #dc2626; }
        @media (max-width: 600px) { .diag-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="admin-container">
    <?php require_once 'includes/sidebar.php'; ?>
    <main class="admin-main">
        <header class="admin-header">
            <div class="admin-title">System Diagnostics</div>
            <div class="admin-user">Signed in as <strong><?php echo htmlspecialchars(get_logged_in_user()); ?></strong></div>
        </header>

        <div style="padding: 1.5rem;">

            <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--primary);">Database Status</h2>
            <div class="diag-grid">
                <div class="diag-card">
                    <div class="diag-label">Connection</div>
                    <div class="diag-value <?php echo $pdo ? 'ok' : 'err'; ?>">
                        <?php echo $pdo ? '✅ Connected' : '❌ Not Connected'; ?>
                    </div>
                </div>
                <div class="diag-card">
                    <div class="diag-label">Driver</div>
                    <div class="diag-value"><?php echo $isPostgres ? 'PostgreSQL (Supabase)' : 'SQLite (Local/Fallback)'; ?></div>
                </div>
                <?php if ($pdo && $isPostgres): ?>
                <div class="diag-card">
                    <div class="diag-label">Latency</div>
                    <?php
                        $t = microtime(true);
                        try { $pdo->query("SELECT 1"); $ms = round((microtime(true) - $t) * 1000); $class = $ms < 100 ? 'ok' : ($ms < 300 ? 'warn' : 'err'); }
                        catch (PDOException $e) { $ms = -1; $class = 'err'; }
                    ?>
                    <div class="diag-value <?php echo $class; ?>"><?php echo $ms >= 0 ? "{$ms}ms" : 'Error'; ?></div>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($pdo): ?>
            <h2 style="font-size: 1.1rem; margin-bottom: 1rem; margin-top: 1.5rem; color: var(--primary);">Table Record Counts</h2>
            <div class="diag-grid">
                <?php
                $tables = ['users', 'blogs', 'events', 'event_rsvps', 'submissions', 'analytics', 'visitor_tracking', 'blog_comments'];
                foreach ($tables as $tbl):
                    try { $cnt = $pdo->query("SELECT COUNT(*) FROM $tbl")->fetchColumn(); }
                    catch (PDOException $e) { $cnt = 'N/A'; }
                ?>
                <div class="diag-card">
                    <div class="diag-label"><?php echo htmlspecialchars($tbl); ?></div>
                    <div class="diag-value"><?php echo htmlspecialchars((string)$cnt); ?> rows</div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <h2 style="font-size: 1.1rem; margin-bottom: 1rem; margin-top: 1.5rem; color: var(--primary);">Environment Configuration</h2>
            <div class="diag-grid">
                <?php
                $checks = [
                    'DATABASE_URL set'          => !empty(getenv('DATABASE_URL')),
                    'BLOB_READ_WRITE_TOKEN set'  => !empty(getenv('BLOB_READ_WRITE_TOKEN')),
                    'ADMIN_AUTH_SECRET set'      => !empty(getenv('ADMIN_AUTH_SECRET')),
                    'APP_ENV'                   => getenv('APP_ENV') ?: '(not set)',
                    'PHP Version'               => PHP_VERSION,
                    'SQLite Extension'          => extension_loaded('pdo_sqlite') ? 'Loaded' : 'Missing',
                    'PostgreSQL Extension'      => extension_loaded('pdo_pgsql') ? 'Loaded' : 'Missing',
                    'cURL Extension'            => extension_loaded('curl') ? 'Loaded' : 'Missing',
                ];
                foreach ($checks as $label => $val):
                    $isOk = is_bool($val) ? $val : true;
                    $display = is_bool($val) ? ($val ? '✅ Yes' : '❌ Not Set') : htmlspecialchars($val);
                    $class   = is_bool($val) ? ($val ? 'ok' : 'err') : '';
                ?>
                <div class="diag-card">
                    <div class="diag-label"><?php echo htmlspecialchars($label); ?></div>
                    <div class="diag-value <?php echo $class; ?>"><?php echo $display; ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <p style="font-size:0.8rem;color:#637381;margin-top:1.5rem;">
                <i class="fa-solid fa-shield-halved"></i> This page is restricted to authenticated administrators only. No credentials are displayed.
            </p>
        </div>
    </main>
</div>
</body>
</html>
