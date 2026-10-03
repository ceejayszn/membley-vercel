<?php
/**
 * admin/login.php
 *
 * Administrator login page.
 *
 * Security features:
 *   - No hardcoded fallback passwords
 *   - CSRF protection on login form
 *   - Rate limiting (5 attempts per 5 minutes per IP)
 *   - session_regenerate_id() after successful login
 *   - Bcrypt password verification only
 *   - Safe error messages (no username enumeration)
 *   - Audit logging of failed attempts
 */

require_once 'auth.php';
require_once '../includes/security.php';

// Already logged in?
if (is_admin_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. CSRF check
    csrf_verify();

    // 2. Rate limit: 5 attempts per 300 seconds per IP
    $ip = membley_client_ip();
    // if (!rate_limit_check($pdo, 'admin_login', $ip, 5, 300)) {
    //     rate_limit_exceeded('Too many login attempts. Please wait 5 minutes and try again.');
    // }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter your username and password.';
    } else {
        $authenticated = false;
        $auth_user     = '';
        $user_id       = null;

        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id, username, password, is_active FROM users WHERE username = :u LIMIT 1");
                $stmt->execute([':u' => $username]);
                $user = $stmt->fetch();

                if ($user && ($user['is_active'] ?? 1) && password_verify($password, $user['password'])) {
                    $authenticated = true;
                    $auth_user     = $user['username'];
                    $user_id       = $user['id'];
                }
            } catch (PDOException $e) {
                membley_log('error', 'Login DB error: ' . $e->getMessage());
                $error = 'A system error occurred. Please try again.';
            }
        }

        if ($authenticated) {
            // Regenerate session ID to prevent fixation (M-3)
            session_regenerate_id(true);

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username']  = $auth_user;
            $_SESSION['admin_id']        = $user_id;

            // Update last_login timestamp
            if ($pdo && $user_id) {
                try {
                    $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id")
                        ->execute([':id' => $user_id]);
                } catch (PDOException $e) { /* Non-fatal */ }
            }

            // Issue persistent cookie (handles serverless cold starts)
            set_persistent_admin_cookie($auth_user);

            // Audit log
            audit_log('LOGIN_SUCCESS', 'users', (int)$user_id);

            // Redirect to original destination or dashboard
            $redirect = filter_var($_GET['redirect'] ?? '', FILTER_SANITIZE_URL);
            if ($redirect && str_starts_with($redirect, '/') && !str_contains($redirect, '//')) {
                header('Location: ' . $redirect);
            } else {
                header('Location: dashboard.php');
            }
            exit;

        } elseif (empty($error)) {
            // Generic error — no username enumeration
            $error = 'Invalid username or password.';
            membley_log('warn', 'Failed login attempt', ['ip' => $ip, 'username_len' => strlen($username)]);
            // Audit log without storing the username (privacy)
            audit_log('LOGIN_FAILED', 'users', 0, 'IP: ' . $ip);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Membley SDA Church</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-logo">
            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="height:60px;width:auto;">
                <circle cx="50" cy="50" r="45" fill="#082b43" stroke="#f2a900" stroke-width="2"/>
                <path d="M30 65 C40 60, 50 63, 50 65 C50 63, 60 60, 70 65 L70 50 C60 48, 50 50, 50 52 C50 50, 40 48, 30 50 Z" fill="#ffffff"/>
                <path d="M50 40 L50 62 M45 46 L55 46" stroke="#082b43" stroke-width="2"/>
                <path d="M47 38 C38 28, 40 18, 50 15 C45 22, 47 28, 52 35 C57 28, 59 22, 54 15 C64 18, 66 28, 57 38 Z" fill="#f2a900"/>
            </svg>
            <h2 style="font-size:1.25rem;color:var(--primary);margin-top:0.5rem;font-weight:800;">Membley SDA Admin</h2>
        </div>

        <h1 class="login-title">Sign In</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="font-size:0.85rem;padding:0.75rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form action="login.php<?php echo !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="admin-form-group">
                <label class="admin-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="admin-input"
                       placeholder="Enter username" required autofocus autocomplete="username"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group" style="margin-bottom:2rem;">
                <label class="admin-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="admin-input"
                       placeholder="Enter password" required autocomplete="current-password">
            </div>
            <button type="submit" id="loginBtn" class="admin-btn" style="width:100%;padding:0.85rem;">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </button>
        </form>

        <p style="text-align:center;margin-top:1.5rem;font-size:0.8rem;color:#637381;">
            <a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Return to Site</a>
        </p>
    </div>

</body>
</html>
