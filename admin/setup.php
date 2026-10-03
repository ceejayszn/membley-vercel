<?php
/**
 * admin/setup.php
 *
 * First-run administrator account creation wizard.
 * This page is only accessible when:
 *   1. No admin users exist in the database, OR
 *   2. A valid setup token exists in the temp directory.
 *
 * After creating the first admin, this page becomes inaccessible
 * until the setup token is regenerated (by deleting all admin users).
 */

require_once 'auth.php';
require_once '../includes/security.php';
require_once '../includes/db.php';

$setupTokenFile = sys_get_temp_dir() . '/membley_setup.token';

// Check if setup is allowed
$setupAllowed = false;
$tokenFromFile = @file_get_contents($setupTokenFile);

if ($pdo) {
    try {
        $count = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $setupAllowed = ($count === 0);
    } catch (PDOException $e) {
        $setupAllowed = true; // Table may not exist yet
    }
}

// Also allow if correct token supplied via GET
if (!$setupAllowed && !empty($_GET['token']) && $tokenFromFile && hash_equals(trim($tokenFromFile), $_GET['token'])) {
    $setupAllowed = true;
}

if (!$setupAllowed) {
    http_response_code(403);
    echo '<h1 style="font-family:sans-serif;text-align:center;padding:3rem;">Setup not available.</h1>';
    echo '<p style="font-family:sans-serif;text-align:center;">Admin accounts already exist. <a href="login.php">Sign in</a>.</p>';
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validation
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } elseif (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username must be at least 3 characters and contain only letters, numbers, and underscores.';
    } elseif (strlen($password) < 12) {
        $error = 'Password must be at least 12 characters.';
    } elseif ($password !== $password2) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, is_active) VALUES (:u, :p, 'admin', 1)");
            $stmt->execute([':u' => $username, ':p' => $hash]);

            // Remove setup token (one-time use)
            @unlink($setupTokenFile);

            $success = 'Administrator account created successfully. You can now sign in.';
            membley_log('info', 'First admin account created via setup wizard', ['ip' => membley_client_ip()]);

        } catch (PDOException $e) {
            $error = 'Failed to create account: ' . ($e->getCode() === '23000' ? 'Username already taken.' : 'Database error.');
            membley_log('error', 'Setup wizard DB error: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>First-Time Setup — Membley SDA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="login-body">
<div class="login-card" style="max-width:440px;">
    <h1 class="login-title" style="font-size:1.3rem;">🛠️ Administrator Setup</h1>
    <p style="font-size:0.9rem;color:#637381;text-align:center;margin-bottom:1.5rem;">Create the first administrator account for Membley SDA Admin Panel.</p>

    <?php if ($success): ?>
        <div class="alert" style="background:#d1fae5;color:#065f46;padding:1rem;border-radius:8px;margin-bottom:1rem;">
            ✅ <?php echo htmlspecialchars($success); ?>
            <br><a href="login.php" style="color:#065f46;font-weight:700;">→ Go to Login</a>
        </div>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-danger" style="font-size:0.85rem;padding:0.75rem;margin-bottom:1rem;">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php echo csrf_field(); ?>
            <?php if (!empty($_GET['token'])): ?>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            <?php endif; ?>

            <div class="admin-form-group">
                <label class="admin-label">Username</label>
                <input type="text" name="username" class="admin-input" placeholder="e.g. churchadmin" required minlength="3" pattern="[a-zA-Z0-9_]+" title="Letters, numbers, underscores only">
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Password <small style="color:#888;">(min 12 characters)</small></label>
                <input type="password" name="password" class="admin-input" placeholder="Strong password" required minlength="12" autocomplete="new-password">
            </div>
            <div class="admin-form-group" style="margin-bottom:1.5rem;">
                <label class="admin-label">Confirm Password</label>
                <input type="password" name="password2" class="admin-input" placeholder="Repeat password" required minlength="12" autocomplete="new-password">
            </div>
            <button type="submit" class="admin-btn" style="width:100%;padding:0.85rem;">Create Administrator Account</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
