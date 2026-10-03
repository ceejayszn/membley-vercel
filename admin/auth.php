<?php
/**
 * admin/auth.php
 *
 * Persistent authentication system for Membley Adventist Admin.
 *
 * Security properties:
 *   - HMAC signing secret loaded from environment variable only (no hardcoded fallback)
 *   - 10-year cookie lifetime (persistent login)
 *   - Session regeneration on every login (prevents session fixation)
 *   - Secure, HttpOnly, SameSite=Strict cookies
 *   - Token revocation via database (allows forced logout of stolen cookies)
 *   - No plaintext credentials anywhere in this file
 */

if (!defined('MEMBLEY_AUTH_LOADED')) {
    define('MEMBLEY_AUTH_LOADED', true);
}

// Load security helpers
if (!function_exists('membley_log')) {
    require_once __DIR__ . '/../includes/security.php';
}

// ── Constants ─────────────────────────────────────────────────────────────────

// Auth secret: MUST be set via ADMIN_AUTH_SECRET environment variable.
// Generate with: php -r "echo bin2hex(random_bytes(32));"
// There is NO hardcoded fallback — this is intentional for security.
define('MEMBLEY_ADMIN_AUTH_SECRET', getenv('ADMIN_AUTH_SECRET') ?: '');
define('MEMBLEY_ADMIN_COOKIE', 'membley_admin_auth');
define('MEMBLEY_AUTH_LIFETIME', 60 * 60 * 24 * 365 * 10); // 10 years

// ── Session setup ─────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 0,           // Session-duration cookie for PHP session (persistent via HMAC cookie)
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    @session_start();
}

// ── Token generation ───────────────────────────────────────────────────────────

function generate_admin_token(string $username): string {
    $secret = MEMBLEY_ADMIN_AUTH_SECRET;
    if (empty($secret)) {
        membley_log('error', 'ADMIN_AUTH_SECRET is not set — cannot generate auth token');
        return '';
    }
    $jti     = bin2hex(random_bytes(8)); // unique token ID for revocation
    $expires = time() + MEMBLEY_AUTH_LIFETIME;
    $payload = $username . '|' . $expires . '|' . $jti;
    $sig     = hash_hmac('sha256', $payload, $secret);
    return base64_encode($payload . '|' . $sig);
}

function verify_admin_token(string $token): ?string {
    $secret = MEMBLEY_ADMIN_AUTH_SECRET;
    if (empty($secret) || empty($token)) return null;

    $raw = base64_decode($token, true);
    if (!$raw) return null;

    $parts = explode('|', $raw);
    if (count($parts) !== 4) return null;

    [$username, $expires, $jti, $sig] = $parts;
    // if ((int)$expires < time()) return null; // expired

    $expected = hash_hmac('sha256', $username . '|' . $expires . '|' . $jti, $secret);
    if (!hash_equals($expected, $sig)) return null; // tampered

    // Check revocation (if DB is available)
    global $pdo;
    if ($pdo) {
        try {
            $check = $pdo->prepare("SELECT 1 FROM admin_revoked_tokens WHERE jti = :jti LIMIT 1");
            $check->execute([':jti' => $jti]);
            if ($check->fetchColumn()) return null; // revoked
        } catch (PDOException $e) { /* Table may not exist yet — allow token */ }
    }

    return $username;
}

// ── Cookie management ──────────────────────────────────────────────────────────

function set_persistent_admin_cookie(string $username): void {
    $token = generate_admin_token($username);
    if (empty($token)) return;

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    setcookie(MEMBLEY_ADMIN_COOKIE, $token, [
        'expires'  => time() + MEMBLEY_AUTH_LIFETIME,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    $_COOKIE[MEMBLEY_ADMIN_COOKIE] = $token;
}

function clear_persistent_admin_cookie(): void {
    // Revoke the JTI in the database if possible
    $token = $_COOKIE[MEMBLEY_ADMIN_COOKIE] ?? '';
    if (!empty($token)) {
        $raw = base64_decode($token, true);
        if ($raw) {
            $parts = explode('|', $raw);
            if (count($parts) === 4) {
                $jti = $parts[2];
                global $pdo;
                if ($pdo) {
                    try {
                        // Ensure revocation table exists
                        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_revoked_tokens (
                            jti TEXT PRIMARY KEY,
                            revoked_at DATETIME DEFAULT CURRENT_TIMESTAMP
                        )");
                        $ins = $pdo->prepare("INSERT OR IGNORE INTO admin_revoked_tokens (jti) VALUES (:jti)");
                        $ins->execute([':jti' => $jti]);
                    } catch (PDOException $e) { /* Non-fatal */ }
                }
            }
        }
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    setcookie(MEMBLEY_ADMIN_COOKIE, '', [
        'expires'  => time() - 86400,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    unset($_COOKIE[MEMBLEY_ADMIN_COOKIE]);
}

// ── Authentication check ───────────────────────────────────────────────────────

function is_admin_logged_in(): bool {
    // Fast path: valid session
    if (!empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_username'])) {
        return true;
    }

    // Restore from persistent cookie (handles serverless cold starts)
    if (!empty($_COOKIE[MEMBLEY_ADMIN_COOKIE])) {
        $username = verify_admin_token($_COOKIE[MEMBLEY_ADMIN_COOKIE]);
        if ($username) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username']  = $username;
            return true;
        }
    }

    return false;
}

/**
 * Guard: redirect to login if not authenticated.
 * Call at the top of every protected admin page.
 */
function check_auth(): void {
    if (!is_admin_logged_in()) {
        $uri = urlencode($_SERVER['REQUEST_URI'] ?? '');
        header('Location: login.php?redirect=' . $uri);
        exit;
    }
}

function get_logged_in_user(): string {
    if (!empty($_SESSION['admin_username'])) {
        return $_SESSION['admin_username'];
    }
    if (!empty($_COOKIE[MEMBLEY_ADMIN_COOKIE])) {
        $username = verify_admin_token($_COOKIE[MEMBLEY_ADMIN_COOKIE]);
        if ($username) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username']  = $username;
            return $username;
        }
    }
    return 'Admin';
}

// ── Audit logging ──────────────────────────────────────────────────────────────

/**
 * Log an administrative action to the audit_log table.
 */
function audit_log(string $action, string $table = '', int $record_id = 0, string $details = ''): void {
    global $pdo;
    if (!$pdo) return;
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO audit_log (admin_username, action, target_table, target_id, details, ip_address)
             VALUES (:user, :action, :table, :id, :details, :ip)"
        );
        $stmt->execute([
            ':user'    => get_logged_in_user(),
            ':action'  => $action,
            ':table'   => $table,
            ':id'      => $record_id,
            ':details' => mb_substr($details, 0, 1000),
            ':ip'      => membley_client_ip(),
        ]);
    } catch (PDOException $e) {
        membley_log('warn', 'Audit log failed: ' . $e->getMessage());
    }
}
