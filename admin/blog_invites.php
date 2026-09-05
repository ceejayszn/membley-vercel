<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'generate') {
        $token = bin2hex(random_bytes(16));
        try {
            $stmt = $pdo->prepare("INSERT INTO blog_invites (token) VALUES (:token)");
            $stmt->execute([':token' => $token]);
            $_SESSION['message'] = "New invite link generated!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to generate link.";
        }
        header("Location: blog_invites.php");
        exit;
    } elseif ($_POST['action'] == 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM blog_invites WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $_SESSION['message'] = "Invite link deleted.";
        header("Location: blog_invites.php");
        exit;
    }
}

$invites = [];
try {
    $stmt = $pdo->query("SELECT * FROM blog_invites ORDER BY created_at DESC");
    $invites = $stmt->fetchAll();
} catch (PDOException $e) {
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Invites - Membley Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #002f5d;
            --accent: #d99e1a;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
        }
        body { font-family: 'Inter', -apple-system, sans-serif; margin: 0; padding: 0; background-color: var(--bg-light); color: #333; display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background-color: var(--primary); color: white; padding: 2rem 1rem; }
        .sidebar a { display: block; color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.75rem 1rem; margin-bottom: 0.5rem; border-radius: 6px; }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(255,255,255,0.1); color: white; }
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }
        .card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-danger { background: #dc2626; color: white; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { font-weight: 600; color: #64748b; }
        .badge { padding: 0.25rem 0.5rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div style="font-size: 1.2rem; font-weight: 700; margin-bottom: 2rem; text-align: center;">Membley Admin</div>
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="blogs.php"><i class="fa-solid fa-pen-nib"></i> Published Blogs</a>
        <a href="blogs_review.php"><i class="fa-solid fa-list-check"></i> Review Submissions</a>
        <a href="blog_invites.php" class="active"><i class="fa-solid fa-link"></i> Blog Invites</a>
        <a href="logout.php" style="margin-top: auto;"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1 style="margin-top: 0;">Manage Blog Invites</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="margin-top: 0;">Invite Authors</h2>
                    <p style="color: #64748b; margin: 0;">Generate unique one-time links for members to submit a blog post.</p>
                </div>
                <form method="post" action="blog_invites.php">
                    <input type="hidden" name="action" value="generate">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Generate Link</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-top: 0;">Active & Used Links</h2>
            <table>
                <thead>
                    <tr>
                        <th>Link</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invites as $invite): ?>
                        <?php 
                            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                            $host = $_SERVER['HTTP_HOST'];
                            $link = $protocol . $host . '/submit-blog.php?token=' . $invite['token'];
                        ?>
                        <tr>
                            <td>
                                <input type="text" value="<?php echo htmlspecialchars($link); ?>" readonly style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; background: #f8fafc;" onclick="this.select();">
                            </td>
                            <td>
                                <?php if ($invite['is_used']): ?>
                                    <span class="badge badge-red">Used</span>
                                <?php else: ?>
                                    <span class="badge badge-green">Active</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($invite['created_at'])); ?></td>
                            <td>
                                <form method="post" action="blog_invites.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this invite link?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $invite['id']; ?>">
                                    <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($invites)): ?>
                        <tr><td colspan="4" style="text-align: center;">No invite links generated yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
