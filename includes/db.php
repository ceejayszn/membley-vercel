<?php
/**
 * includes/db.php
 *
 * Database connection manager for Membley Adventist Church.
 *
 * Connection strategy (ordered by priority):
 *   A. DATABASE_URL / POSTGRES_URL environment variable (Vercel / Render)
 *   B. SUPABASE_PROJECT_REF + SUPABASE_DB_PASSWORD environment variables
 *   C. SQLite fallback — local development only (/data or includes/)
 *
 * IMPORTANT: No credentials are hardcoded in this file.
 * Set all secrets via environment variables (see .env.example).
 *
 * Schema creation is handled by migrate.php — NOT here.
 * This file only opens a database connection.
 */

// ── Shared utilities ──────────────────────────────────────────────────────────
if (!function_exists('membley_log')) {
    require_once __DIR__ . '/security.php';
}

// ── Connection result ─────────────────────────────────────────────────────────
$pdo        = null;
$isPostgres = false;

// ── Helper: open a Supabase pooler connection ──────────────────────────────────
function _membley_pg_connect(string $host, string $ref, string $pass): PDO {
    $dsn = "pgsql:host={$host};port=6543;dbname=postgres;sslmode=require";
    return new PDO($dsn, "postgres.{$ref}", $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true,
        PDO::ATTR_TIMEOUT            => 5,
    ]);
}

// ── Path A: Full DATABASE_URL ─────────────────────────────────────────────────
$dbUrl = getenv('DATABASE_URL')
      ?: getenv('POSTGRES_URL')
      ?: getenv('POSTGRES_PRISMA_URL')
      ?: getenv('SUPABASE_DB_URL')
      ?: '';

try {
    if (!empty($dbUrl)) {
        $p      = parse_url($dbUrl);
        $host   = $p['host']   ?? '';
        $port   = isset($p['port']) ? (int)$p['port'] : 5432;
        $user   = urldecode($p['user']  ?? 'postgres');
        $pass   = urldecode($p['pass']  ?? '');
        $dbname = ltrim($p['path'] ?? 'postgres', '/');

        // Supabase direct host (port 5432 blocked on Vercel) → reroute to pooler
        if (preg_match('/db\.([a-z0-9]+)\.supabase\.co/', $host, $m)) {
            $ref = $m[1];
            // Try the region from DATABASE_URL first, then fallback to env or default
            $knownHost = getenv('SUPABASE_POOLER_HOST') ?: 'aws-0-eu-central-1.pooler.supabase.com';
            try {
                $pdo = _membley_pg_connect($knownHost, $ref, $pass);
            } catch (PDOException $e) {
                // Rerouted host failed — try the preferred region only (avoid 24s cascade)
                $pdo = _membley_pg_connect('aws-0-eu-central-1.pooler.supabase.com', $ref, $pass);
            }

        // Already a pooler URL — extract ref from user like "postgres.XXXX"
        } elseif (str_contains($host, 'pooler.supabase.com')) {
            $ref = str_contains($user, '.') ? explode('.', $user, 2)[1] : '';
            if (empty($ref)) {
                $ref = getenv('SUPABASE_PROJECT_REF') ?: '';
            }
            $pdo = _membley_pg_connect($host, $ref, $pass);

        // Other Postgres (non-Supabase pooler)
        } else {
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => true,
                PDO::ATTR_TIMEOUT            => 8,
            ]);
        }
        $isPostgres = true;

    // ── Path B: Explicit SUPABASE env vars (no DATABASE_URL) ─────────────────
    } elseif (getenv('SUPABASE_PROJECT_REF') && getenv('SUPABASE_DB_PASSWORD')) {
        $ref  = getenv('SUPABASE_PROJECT_REF');
        $pass = getenv('SUPABASE_DB_PASSWORD');
        $host = getenv('SUPABASE_POOLER_HOST') ?: 'aws-0-eu-central-1.pooler.supabase.com';
        $pdo  = _membley_pg_connect($host, $ref, $pass);
        $isPostgres = true;
    }

} catch (PDOException $supabaseErr) {
    membley_log('error', 'Supabase connection failed: ' . $supabaseErr->getMessage(), [
        'host' => $host ?? 'unknown',
    ]);
    $pdo = null; // Proceed to SQLite fallback
}

// ── Path C: SQLite fallback (local dev or DB unavailable) ─────────────────────
if ($pdo === null) {
    $isProduction = (getenv('APP_ENV') === 'production')
                 || !empty(getenv('VERCEL'))
                 || !empty(getenv('RENDER'));

    if ($isProduction) {
        // In production, SQLite is only for emergency continuity.
        // Data written here does NOT persist across serverless invocations reliably.
        membley_log('warn', 'Falling back to SQLite in production environment — data may not persist');
    }

    // Choose writable location
    if (is_dir('/data') && is_writable('/data')) {
        // Render persistent disk
        $db_file = '/data/church.db';
        if (!file_exists($db_file) && file_exists(__DIR__ . '/church.db')) {
            @copy(__DIR__ . '/church.db', $db_file);
        }
    } elseif (is_dir('/tmp') && is_writable('/tmp')) {
        // Vercel serverless /tmp (ephemeral — for emergency continuity only)
        $db_file = '/tmp/church.db';
        if (!file_exists($db_file)) {
            // Try Vercel Blob first
            $blobToken = getenv('BLOB_READ_WRITE_TOKEN') ?: '';
            $downloaded = false;
            if ($blobToken && function_exists('curl_init')) {
                $ch = curl_init('https://blob.vercel-storage.com');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER     => [
                        'authorization: Bearer ' . $blobToken,
                        'x-api-version: 7'
                    ],
                    CURLOPT_TIMEOUT        => 3,
                ]);
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($code >= 200 && $code < 300) {
                    $bdata = json_decode($resp, true);
                    foreach ($bdata['blobs'] ?? [] as $b) {
                        if (strpos($b['pathname'], 'church.db') !== false && !empty($b['url'])) {
                            $remote = @file_get_contents($b['url']);
                            if ($remote && strlen($remote) > 1000) {
                                @file_put_contents($db_file, $remote);
                                $downloaded = true;
                                break;
                            }
                        }
                    }
                }
            }
            if (!$downloaded && file_exists(__DIR__ . '/church.db')) {
                @copy(__DIR__ . '/church.db', $db_file);
            }
        }
    } else {
        $db_file = __DIR__ . '/church.db';
    }

    try {
        $pdo = new PDO("sqlite:{$db_file}", null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 5,
        ]);
        $pdo->exec("PRAGMA journal_mode=WAL");
        $pdo->exec("PRAGMA foreign_keys=ON");
        $pdo->exec("PRAGMA busy_timeout=3000");
        $isPostgres = false;

        // Sync back to Blob after writes (best-effort, non-blocking)
        if (isset($db_file) && strpos($db_file, '/tmp') !== false) {
            register_shutdown_function(function() use ($db_file) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && file_exists($db_file) && filesize($db_file) > 1000) {
                    try { uploadToVercelBlob($db_file, 'church.db'); }
                    catch (Exception $e) {
                        membley_log('error', 'SQLite Blob sync failed: ' . $e->getMessage());
                    }
                }
            });
        }
    } catch (PDOException $sqliteErr) {
        membley_log('error', 'SQLite connection also failed: ' . $sqliteErr->getMessage());
        // Show user-facing error without any credentials or internals
        http_response_code(503);
        include __DIR__ . '/../includes/db_error.php';
        exit;
    }
}

// ── Ensure schema is current ───────────────────────────────────────────────────
// Migrations run only if the schema version doesn't match, avoiding the
// overhead of running CREATE TABLE IF NOT EXISTS on every request.
// On a cold start or new deployment, the version check is fast (~1ms).
if ($pdo) {
    try {
        // Read the stored schema version (stored in analytics table as a sentinel)
        $versionKey  = '__schema_version__';
        $targetVersion = '7'; // Increment this when migrate.php adds new tables/columns

        $vStmt = $pdo->prepare("SELECT views FROM analytics WHERE page = :p LIMIT 1");
        $vStmt->execute([':p' => $versionKey]);
        $storedVersion = $vStmt->fetchColumn();

        if ($storedVersion !== $targetVersion) {
            require_once __DIR__ . '/migrate.php';
            membley_run_migrations($pdo, false);

            // Record the new schema version
            if ($isPostgres) {
                $pdo->prepare("INSERT INTO analytics (page, views, clicks, time_spent) VALUES (:p, :v, 0, 0) ON CONFLICT (page) DO UPDATE SET views = :v")
                    ->execute([':p' => $versionKey, ':v' => $targetVersion]);
            } else {
                $pdo->prepare("INSERT OR REPLACE INTO analytics (page, views, clicks, time_spent) VALUES (:p, :v, 0, 0)")
                    ->execute([':p' => $versionKey, ':v' => $targetVersion]);
            }
        }
    } catch (PDOException $migErr) {
        // Non-fatal on normal requests — log and continue. The app can still serve content.
        membley_log('warn', 'Schema version check failed: ' . $migErr->getMessage());
        // If fresh install, run migrations regardless
        require_once __DIR__ . '/migrate.php';
        membley_run_migrations($pdo, false);
    }
}

// ── Shared helper functions ────────────────────────────────────────────────────

/**
 * Upload a local file to Vercel Blob Storage.
 * Token is read exclusively from environment — no hardcoded fallback.
 */
function uploadToVercelBlob(string $filePath, string $destinationName): ?string {
    $token = getenv('BLOB_READ_WRITE_TOKEN')
          ?: getenv('membleyvercelstorage_READ_WRITE_TOKEN')
          ?: '';

    // Dynamic scan for any blob token in environment (Vercel auto-injects various names)
    if (empty($token)) {
        foreach (array_merge($_SERVER ?? [], $_ENV ?? []) as $key => $val) {
            if (is_string($key) && is_string($val) &&
                (stripos($key, 'READ_WRITE_TOKEN') !== false || stripos($key, 'BLOB_TOKEN') !== false) &&
                !empty($val)) {
                $token = $val;
                break;
            }
        }
    }

    if (empty($token)) {
        membley_log('warn', 'BLOB_READ_WRITE_TOKEN not set — falling back to local storage');
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
        $targetPath = $uploadDir . basename($destinationName);
        if (@copy($filePath, $targetPath)) {
            return 'uploads/' . basename($destinationName);
        }
        return null;
    }

    $url  = 'https://blob.vercel-storage.com/' . rawurlencode($destinationName);
    $data = @file_get_contents($filePath);
    if ($data === false) {
        throw new Exception("Could not read file: {$filePath}");
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => $data,
        CURLOPT_HTTPHEADER     => [
            'authorization: Bearer ' . $token,
            'x-api-version: 7',
        ],
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        $result = json_decode($response, true);
        return $result['url'] ?? null;
    }

    throw new Exception("Blob upload failed (HTTP {$httpCode})");
}

/**
 * Resolve a stored image path or URL for web display.
 */
function get_media_url(string $path, bool $is_admin = false): string {
    if (empty($path)) return '';
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    $clean = ltrim($path, '/');
    return $is_admin ? '../' . $clean : '/' . $clean;
}

/**
 * Returns a download proxy URL for media.
 */
function get_download_url(string $path, bool $is_admin = false): string {
    if (empty($path)) return '';
    $raw_url = get_media_url($path, false);
    $prefix  = $is_admin ? '../' : '';
    return $prefix . 'download.php?url=' . urlencode($raw_url);
}

/**
 * Parse single or multi-file image/document fields.
 */
function parse_media_list(string $str): array {
    if (empty($str)) return [];
    $decoded = json_decode($str, true);
    if (is_array($decoded)) return array_filter(array_map('trim', $decoded));
    if (str_contains($str, ',')) return array_filter(array_map('trim', explode(',', $str)));
    return [trim($str)];
}

/**
 * DB-compatible date comparison helper.
 * Returns SQL expression for "datetime N seconds ago" compatible with both DBs.
 */
function db_datetime_ago(bool $isPostgres, int $seconds): string {
    return $isPostgres
        ? "NOW() - INTERVAL '{$seconds} seconds'"
        : "datetime('now', '-{$seconds} seconds')";
}

/**
 * DB-compatible "insert if not exists" for analytics page tracking.
 */
function db_upsert_analytics(PDO $pdo, bool $isPostgres, string $page): void {
    if ($isPostgres) {
        $pdo->prepare("INSERT INTO analytics (page, views, clicks, time_spent) VALUES (:p, 0, 0, 0) ON CONFLICT (page) DO NOTHING")
            ->execute([':p' => $page]);
    } else {
        $pdo->prepare("INSERT OR IGNORE INTO analytics (page, views, clicks, time_spent) VALUES (:p, 0, 0, 0)")
            ->execute([':p' => $page]);
    }
}
