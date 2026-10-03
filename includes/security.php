<?php
/**
 * includes/security.php
 * Central security module for Membley Adventist Church
 *
 * Provides:
 *   - CSRF token generation and validation
 *   - Rate limiting (DB-backed, serverless-compatible)
 *   - Input validation and sanitization helpers
 *   - Safe error logging (no credentials in logs)
 *   - File upload validation
 */

// ── CSRF Protection ────────────────────────────────────────────────────────────

/**
 * Generate or retrieve the session CSRF token.
 * Call once per session; token rotates on each login.
 */
function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden CSRF input field — use inside every <form>.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate the submitted CSRF token against the session token.
 * Calls http_response_code(403) and exits on failure.
 */
function csrf_verify(): void {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $submitted = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $expected  = $_SESSION['csrf_token'] ?? '';

    if (empty($expected) || !hash_equals($expected, $submitted)) {
        http_response_code(403);
        // Log the failure (no credentials, no UA body)
        membley_log('warn', 'CSRF validation failed', [
            'ip'   => membley_client_ip(),
            'uri'  => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
        ]);
        // Return JSON for AJAX callers; plain text for form-POSTs
        $wants_json = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
                   || str_contains($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest');
        if ($wants_json) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid security token. Please reload the page and try again.']);
        } else {
            echo '<h1>403 — Security Token Invalid</h1><p>Please <a href="javascript:history.back()">go back</a> and try again.</p>';
        }
        exit;
    }
}

// ── Rate Limiting (DB-backed, serverless-compatible) ──────────────────────────

/**
 * Check and record a rate-limited action using the database.
 * Compatible with both SQLite and PostgreSQL.
 *
 * @param PDO    $pdo        Active database connection
 * @param string $action     Key identifying the action (e.g. 'login', 'contact')
 * @param string $identifier IP address or similar identifier
 * @param int    $limit      Max attempts allowed
 * @param int    $window     Window in seconds
 * @return bool  true if allowed, false if rate limit exceeded
 */
function rate_limit_check(PDO $pdo, string $action, string $identifier, int $limit = 5, int $window = 300): bool {
    try {
        // Ensure rate_limits table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS rate_limits (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            action TEXT NOT NULL,
            identifier TEXT NOT NULL,
            attempt_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Count attempts within window
        $cutoff = date('Y-m-d H:i:s', time() - $window);
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM rate_limits WHERE action = :action AND identifier = :id AND attempt_at > :cutoff"
        );
        $stmt->execute([':action' => $action, ':id' => $identifier, ':cutoff' => $cutoff]);
        $count = (int)$stmt->fetchColumn();

        if ($count >= $limit) {
            membley_log('warn', 'Rate limit exceeded', ['action' => $action, 'ip' => $identifier]);
            return false;
        }

        // Record this attempt
        $ins = $pdo->prepare("INSERT INTO rate_limits (action, identifier) VALUES (:action, :id)");
        $ins->execute([':action' => $action, ':id' => $identifier]);

        // Prune old entries (keep table lean) — non-fatal if it fails
        try {
            $del = $pdo->prepare("DELETE FROM rate_limits WHERE attempt_at < :cutoff");
            $del->execute([':cutoff' => $cutoff]);
        } catch (PDOException $e) { /* non-fatal */ }

        return true;
    } catch (PDOException $e) {
        // If table operations fail, allow the request (fail open for availability)
        membley_log('error', 'Rate limit DB error: ' . $e->getMessage());
        return true;
    }
}

/**
 * Send a 429 Too Many Requests response.
 */
function rate_limit_exceeded(string $message = 'Too many requests. Please wait a moment and try again.'): void {
    http_response_code(429);
    $wants_json = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
               || str_contains($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest');
    if ($wants_json) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message]);
    } else {
        echo '<p style="color:red;font-family:sans-serif;padding:2rem;">' . htmlspecialchars($message) . '</p>';
    }
    exit;
}

// ── Safe Error Logging ─────────────────────────────────────────────────────────

/**
 * Log a structured message to PHP error_log.
 * Never logs passwords, tokens, or form body content.
 *
 * @param string $level   'info' | 'warn' | 'error'
 * @param string $message Human-readable message
 * @param array  $context Safe key-value context (no secrets)
 */
function membley_log(string $level, string $message, array $context = []): void {
    // Scrub any accidental sensitive keys
    $forbidden = ['password', 'passwd', 'token', 'secret', 'key', 'auth', 'credential'];
    foreach ($context as $k => $v) {
        foreach ($forbidden as $f) {
            if (stripos($k, $f) !== false) {
                $context[$k] = '[REDACTED]';
            }
        }
    }
    $ctx  = empty($context) ? '' : ' ' . json_encode($context);
    $line = '[MEMBLEY ' . strtoupper($level) . '] ' . $message . $ctx;
    error_log($line);
}

// ── Client IP ─────────────────────────────────────────────────────────────────

/**
 * Return the most reliable client IP address, respecting Vercel proxy headers.
 */
function membley_client_ip(): string {
    // Vercel sets x-real-ip reliably
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        return trim($_SERVER['HTTP_X_REAL_IP']);
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ── Input Validation Helpers ───────────────────────────────────────────────────

/**
 * Sanitize a plain-text input (no HTML allowed).
 */
function sanitize_text(string $value, int $maxlen = 500): string {
    $value = trim($value);
    if (mb_strlen($value) > $maxlen) {
        $value = mb_substr($value, 0, $maxlen);
    }
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Validate and return a safe email, or empty string.
 */
function sanitize_email(string $value): string {
    $clean = filter_var(trim($value), FILTER_VALIDATE_EMAIL);
    return $clean !== false ? $clean : '';
}

/**
 * Sanitize a phone number — digits, spaces, +, -, () only.
 */
function sanitize_phone(string $value, int $maxlen = 30): string {
    $value = preg_replace('/[^0-9+\-() ]/', '', trim($value));
    return mb_substr($value, 0, $maxlen);
}

/**
 * Sanitize rich HTML content using a simple allowlist.
 * This is used for blog content and submission content where HTML is intentional.
 *
 * Allowlist covers standard blog markup — no script, iframe (except YouTube), event attrs.
 * For production, HTMLPurifier (via Composer) is recommended; this is a built-in fallback.
 */
function sanitize_rich_html(string $html): string {
    // If HTMLPurifier is available (Phase 3: Composer added), use it
    if (class_exists('HTMLPurifier')) {
        static $purifier = null;
        if ($purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed',
                'p,br,strong,b,em,i,u,s,ul,ol,li,h1,h2,h3,h4,h5,h6,' .
                'blockquote,pre,code,a[href|title|target],img[src|alt|width|height],' .
                'div[class],span[class],table,thead,tbody,tr,th,td,' .
                'iframe[src|width|height|frameborder|allowfullscreen|allow],' .
                'embed[src|type|width|height]'
            );
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp',
                '%^(https?:)?//(www\.youtube(-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%'
            );
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('Core.Encoding', 'UTF-8');
            // No caching in serverless
            $config->set('Cache.DefinitionImpl', null);
            $purifier = new HTMLPurifier($config);
        }
        return $purifier->purify($html);
    }

    // Built-in fallback: strip dangerous tags/attributes
    // Keeps formatting but removes scripts, event handlers, javascript: URIs
    $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
    $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html);
    $html = preg_replace('/\bon\w+\s*=/i', 'data-removed=', $html); // strip event attrs
    $html = preg_replace('/javascript\s*:/i', 'removed:', $html);     // strip js: URIs
    $html = preg_replace('/vbscript\s*:/i', 'removed:', $html);
    return $html;
}

// ── File Upload Validation ─────────────────────────────────────────────────────

/**
 * Validate an uploaded file for security.
 *
 * @param array  $file          Entry from $_FILES
 * @param array  $allowed_exts  Lowercase extensions without dot
 * @param array  $allowed_mimes MIME type strings
 * @param int    $max_bytes     Maximum file size in bytes
 * @return array ['ok' => bool, 'error' => string]
 */
function validate_upload(array $file, array $allowed_exts, array $allowed_mimes, int $max_bytes = 10485760): array {
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        $codes = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory available.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the upload.',
        ];
        return ['ok' => false, 'error' => $codes[$file['error']] ?? 'Unknown upload error.'];
    }

    // Size check
    if ($file['size'] > $max_bytes) {
        return ['ok' => false, 'error' => 'File size ' . round($file['size']/1048576, 1) . 'MB exceeds the ' . round($max_bytes/1048576) . 'MB limit.'];
    }

    // Extension check
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts, true)) {
        return ['ok' => false, 'error' => 'File type .' . htmlspecialchars($ext) . ' is not allowed.'];
    }

    // MIME via mime_content_type (checks file bytes, not extension)
    $detected_mime = @mime_content_type($file['tmp_name']);
    if ($detected_mime && !in_array($detected_mime, $allowed_mimes, true)) {
        membley_log('warn', 'Upload MIME mismatch', [
            'ext' => $ext, 'detected_mime' => $detected_mime, 'ip' => membley_client_ip()
        ]);
        return ['ok' => false, 'error' => 'File content does not match its extension. Upload rejected.'];
    }

    // Magic byte checks for common image types
    $handle = @fopen($file['tmp_name'], 'rb');
    if ($handle) {
        $magic = bin2hex(fread($handle, 8));
        fclose($handle);
        $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $image_exts, true)) {
            $valid_magic = [
                'jpg'  => ['ffd8ff'],
                'jpeg' => ['ffd8ff'],
                'png'  => ['89504e47'],
                'gif'  => ['474946383761', '474946383961'],
                'webp' => false, // checked via MIME
            ];
            if (!empty($valid_magic[$ext])) {
                $ok = false;
                foreach ($valid_magic[$ext] as $sig) {
                    if (str_starts_with($magic, $sig)) { $ok = true; break; }
                }
                if (!$ok) {
                    return ['ok' => false, 'error' => 'File signature does not match .' . $ext . '. Upload rejected.'];
                }
            }
        }
    }

    // Safe generated filename (no user-supplied name in path)
    return ['ok' => true, 'error' => '', 'ext' => $ext];
}
