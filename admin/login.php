<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'A database error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Membley SDA Church</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-logo">
                        <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="height: 60px; width: auto;">
                <circle cx="50" cy="50" r="45" fill="#082b43" stroke="#f2a900" stroke-width="2"/>
                <path d="M30 65 C40 60, 50 63, 50 65 C50 63, 60 60, 70 65 L70 50 C60 48, 50 50, 50 52 C50 50, 40 48, 30 50 Z" fill="#ffffff"/>
                <path d="M50 40 L50 62 M45 46 L55 46" stroke="#082b43" stroke-width="2"/>
                <path d="M47 38 C38 28, 40 18, 50 15 C45 22, 47 28, 52 35 C57 28, 59 22, 54 15 C64 18, 66 28, 57 38 Z" fill="#f2a900"/>
            </svg>
            <h2 style="font-size: 1.25rem; color: var(--primary); margin-top: 0.5rem; font-weight: 800;">Membley SDA Admin</h2>
        </div>

        <h1 class="login-title">Sign In</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="font-size: 0.85rem; padding: 0.75rem;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="admin-form-group">
                <label class="admin-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="admin-input" placeholder="Enter username" required autofocus>
            </div>
            <div class="admin-form-group" style="margin-bottom: 2rem;">
                <label class="admin-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="admin-input" placeholder="Enter password" required>
            </div>
            <button type="submit" class="admin-btn" style="width: 100%; padding: 0.85rem;"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
        </form>
        
        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.8rem; color: #637381;">
            <a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Return to Site</a>
        </p>
    </div>

</body>
</html>
