<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['comment_id']);
    $stmt = $pdo->prepare("DELETE FROM blog_comments WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $_SESSION['message'] = "Comment deleted successfully.";
    header("Location: manage_comments.php");
    exit;
}

$comments = [];
try {
    $stmt = $pdo->query("
        SELECT c.*, b.title as blog_title 
        FROM blog_comments c 
        JOIN blogs b ON c.blog_id = b.id 
        ORDER BY c.created_at DESC
    ");
    $comments = $stmt->fetchAll();
} catch (PDOException $e) {}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Comments - Membley Admin</title>
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
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); vertical-align: top; }
        th { font-weight: 600; color: #64748b; }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: inline-block; text-decoration: none; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.85rem; }
        .alert { padding: 1rem; background: #dcfce7; color: #166534; border-radius: 6px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div style="font-size: 1.2rem; font-weight: 700; margin-bottom: 2rem; text-align: center;">Membley Admin</div>
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="blogs.php"><i class="fa-solid fa-pen-nib"></i> Published Blogs</a>
        <a href="blogs_review.php"><i class="fa-solid fa-list-check"></i> Review Submissions</a>
        <a href="blog_invites.php"><i class="fa-solid fa-link"></i> Blog Invites</a>
        <a href="blog_analytics.php"><i class="fa-solid fa-chart-bar"></i> Analytics (Superpowers)</a>
        <a href="manage_comments.php" class="active"><i class="fa-solid fa-comments"></i> Manage Comments</a>
        <a href="logout.php" style="margin-top: auto;"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1 style="margin-top: 0;">Manage Comments</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Author</th>
                        <th>Comment</th>
                        <th>Blog / Media Post</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $c): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($c['author_name']); ?></strong></td>
                            <td style="max-width: 300px;">
                                <?php echo nl2br(htmlspecialchars($c['content'])); ?>
                            </td>
                            <td><a href="../blog-single.php?slug=<?php echo urlencode($c['blog_title']); ?>" target="_blank" style="color: var(--primary);"><?php echo htmlspecialchars($c['blog_title']); ?></a></td>
                            <td style="color: #64748b; font-size: 0.9rem;"><?php echo date('M d, Y H:i', strtotime($c['created_at'])); ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="comment_id" value="<?php echo $c['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($comments)): ?>
                        <tr><td colspan="5" style="text-align: center; color: #64748b;">No comments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
