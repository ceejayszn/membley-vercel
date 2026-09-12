<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $blog_id = intval($_POST['blog_id']);
    
    if ($_POST['action'] == 'add_fake_views') {
        $amount = intval($_POST['amount']);
        $stmt = $pdo->prepare("UPDATE blogs SET fake_views = fake_views + :amount WHERE id = :id");
        $stmt->execute([':amount' => $amount, ':id' => $blog_id]);
        $_SESSION['message'] = "Added $amount fake views!";
    } elseif ($_POST['action'] == 'add_fake_likes') {
        $amount = intval($_POST['amount']);
        $stmt = $pdo->prepare("UPDATE blogs SET fake_likes = fake_likes + :amount WHERE id = :id");
        $stmt->execute([':amount' => $amount, ':id' => $blog_id]);
        $_SESSION['message'] = "Added $amount fake likes!";
    }
    header("Location: blog_analytics.php");
    exit;
}

$blogs = [];
try {
    $stmt = $pdo->query("
        SELECT b.*, 
               (SELECT COUNT(*) FROM blog_likes WHERE blog_id = b.id) as real_likes,
               (SELECT COUNT(*) FROM blog_comments WHERE blog_id = b.id) as comment_count
        FROM blogs b 
        ORDER BY b.created_at DESC
    ");
    $blogs = $stmt->fetchAll();
} catch (PDOException $e) {}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Analytics - Membley Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary: #002f5d; --accent: #d99e1a; --bg-light: #f8fafc; --border-color: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; margin: 0; display: flex; min-height: 100vh; background: var(--bg-light); color: #333; }
        .sidebar { width: 250px; background: var(--primary); color: white; padding: 2rem 1rem; }
        .sidebar a { display: block; color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.75rem 1rem; margin-bottom: 0.5rem; border-radius: 6px; }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(255,255,255,0.1); color: white; }
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }
        .card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { font-weight: 600; color: #64748b; }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: inline-block; text-decoration: none; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.85rem; }
        .alert { padding: 1rem; background: #dcfce7; color: #166534; border-radius: 6px; margin-bottom: 1rem; }
        .flex-form { display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem; }
        .flex-form input { width: 60px; padding: 0.25rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div style="font-size: 1.2rem; font-weight: 700; margin-bottom: 2rem; text-align: center;">Membley Admin</div>
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="blogs.php"><i class="fa-solid fa-pen-nib"></i> Published Blogs</a>
        <a href="blogs_review.php"><i class="fa-solid fa-list-check"></i> Review Submissions</a>
        <a href="blog_invites.php"><i class="fa-solid fa-link"></i> Blog Invites</a>
        <a href="blog_analytics.php" class="active"><i class="fa-solid fa-chart-bar"></i> Analytics (Superpowers)</a>
        <a href="manage_comments.php"><i class="fa-solid fa-comments"></i> Manage Comments</a>
        <a href="logout.php" style="margin-top: auto;"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1 style="margin-top: 0;">Blog Analytics & Superpowers</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Views (Real + Fake)</th>
                        <th>Likes (Real + Fake)</th>
                        <th>Comments</th>
                        <th>Superpowers</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blogs as $blog): ?>
                        <tr>
                            <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <strong><?php echo htmlspecialchars($blog['title']); ?></strong><br>
                                <small style="color: #64748b;"><?php echo htmlspecialchars($blog['category']); ?></small>
                            </td>
                            <td>
                                <div><span style="color: green; font-weight: bold;"><?php echo $blog['real_views'] ?? 0; ?></span> Real</div>
                                <div><span style="color: purple; font-weight: bold;"><?php echo $blog['fake_views'] ?? 0; ?></span> Generated</div>
                                <div style="font-size: 0.85rem; color: #64748b; margin-top: 0.25rem;">Total: <?php echo ($blog['real_views'] ?? 0) + ($blog['fake_views'] ?? 0); ?></div>
                            </td>
                            <td>
                                <div><span style="color: green; font-weight: bold;"><?php echo $blog['real_likes'] ?? 0; ?></span> Real</div>
                                <div><span style="color: purple; font-weight: bold;"><?php echo $blog['fake_likes'] ?? 0; ?></span> Generated</div>
                                <div style="font-size: 0.85rem; color: #64748b; margin-top: 0.25rem;">Total: <?php echo ($blog['real_likes'] ?? 0) + ($blog['fake_likes'] ?? 0); ?></div>
                            </td>
                            <td>
                                <strong><?php echo $blog['comment_count']; ?></strong>
                            </td>
                            <td>
                                <form method="post" class="flex-form">
                                    <input type="hidden" name="action" value="add_fake_views">
                                    <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                                    <input type="number" name="amount" value="50" min="1">
                                    <button type="submit" class="btn btn-primary btn-sm">+ Views</button>
                                </form>
                                <form method="post" class="flex-form">
                                    <input type="hidden" name="action" value="add_fake_likes">
                                    <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                                    <input type="number" name="amount" value="10" min="1">
                                    <button type="submit" class="btn btn-primary btn-sm" style="background: purple;">+ Likes</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
