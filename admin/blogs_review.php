<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $id = $_POST['id'];
    if ($_POST['action'] == 'accept') {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $excerpt = trim($_POST['excerpt']);
        
        $stmt = $pdo->prepare("UPDATE blogs SET title = :title, content = :content, excerpt = :excerpt, status = 'published' WHERE id = :id");
        $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':excerpt' => $excerpt,
            ':id' => $id
        ]);
        $_SESSION['message'] = "Blog post published successfully!";
        header("Location: blogs_review.php");
        exit;
    } elseif ($_POST['action'] == 'reject') {
        $stmt = $pdo->prepare("UPDATE blogs SET status = 'draft' WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $_SESSION['message'] = "Blog post moved to drafts.";
        header("Location: blogs_review.php");
        exit;
    } elseif ($_POST['action'] == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $_SESSION['message'] = "Blog post deleted permanently.";
        header("Location: blogs_review.php");
        exit;
    }
}

$review_blogs = [];
try {
    $stmt = $pdo->query("SELECT * FROM blogs WHERE status IN ('review', 'draft') ORDER BY created_at DESC");
    $review_blogs = $stmt->fetchAll();
} catch (PDOException $e) {
}

$edit_blog = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = :id AND status IN ('review', 'draft')");
    $stmt->execute([':id' => $id]);
    $edit_blog = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Submissions - Membley Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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
        .card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: #16a34a; color: white; }
        .btn-warning { background: #d97706; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-outline { background: transparent; border: 1px solid var(--border-color); color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { font-weight: 600; color: #64748b; }
        .badge { padding: 0.25rem 0.5rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; }
        .badge-yellow { background: #fef08a; color: #854d0e; }
        .badge-gray { background: #e2e8f0; color: #475569; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: #dcfce7; color: #166534; }
        input[type="text"] { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div style="font-size: 1.2rem; font-weight: 700; margin-bottom: 2rem; text-align: center;">Membley Admin</div>
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="blogs.php"><i class="fa-solid fa-pen-nib"></i> Published Blogs</a>
        <a href="blogs_review.php" class="active"><i class="fa-solid fa-list-check"></i> Review Submissions</a>
        <a href="blog_invites.php"><i class="fa-solid fa-link"></i> Blog Invites</a>
        <a href="logout.php" style="margin-top: auto;"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1 style="margin-top: 0;">Review Blog Submissions</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <?php if ($edit_blog): ?>
            <div class="card">
                <h2>Review: <?php echo htmlspecialchars($edit_blog['title']); ?></h2>
                <span class="badge <?php echo $edit_blog['status'] == 'review' ? 'badge-yellow' : 'badge-gray'; ?>">
                    <?php echo strtoupper($edit_blog['status']); ?>
                </span>
                
                <form method="post" action="blogs_review.php" id="reviewForm" style="margin-top: 1.5rem;">
                    <input type="hidden" name="id" value="<?php echo $edit_blog['id']; ?>">
                    <input type="hidden" name="action" id="actionInput" value="accept">
                    
                    <label style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($edit_blog['title']); ?>" required>
                    
                    <label style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Excerpt</label>
                    <input type="text" name="excerpt" value="<?php echo htmlspecialchars($edit_blog['excerpt']); ?>">
                    
                    <label style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Content</label>
                    <input type="hidden" name="content" id="hiddenContent">
                    <div id="editor" style="height: 400px; margin-bottom: 1.5rem;">
                        <?php echo $edit_blog['content']; ?>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-success" onclick="document.getElementById('actionInput').value='accept';"><i class="fa-solid fa-check"></i> Accept & Publish</button>
                        <button type="submit" class="btn btn-warning" onclick="document.getElementById('actionInput').value='reject';"><i class="fa-solid fa-file-invoice"></i> Move to Drafts</button>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this completely?');" onmousedown="document.getElementById('actionInput').value='delete';"><i class="fa-solid fa-trash"></i> Delete</button>
                        <a href="blogs_review.php" class="btn btn-outline" style="margin-left: auto;">Cancel</a>
                    </div>
                </form>

                <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
                <script>
                    var quill = new Quill('#editor', {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                ['blockquote'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                [{ 'indent': '-1'}, { 'indent': '+1' }],
                                [{ 'align': [] }],
                                ['link', 'image'],
                                ['clean']
                            ]
                        }
                    });

                    document.getElementById('reviewForm').onsubmit = function() {
                        if(document.getElementById('actionInput').value === 'delete') return true;
                        document.getElementById('hiddenContent').value = quill.root.innerHTML;
                    };
                </script>
            </div>
        <?php else: ?>
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($review_blogs as $blog): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($blog['title']); ?></td>
                                <td>
                                    <?php if ($blog['status'] == 'review'): ?>
                                        <span class="badge badge-yellow">Pending Review</span>
                                    <?php else: ?>
                                        <span class="badge badge-gray">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($blog['created_at'])); ?></td>
                                <td>
                                    <a href="blogs_review.php?edit=<?php echo $blog['id']; ?>" class="btn btn-primary btn-sm">Review</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($review_blogs)): ?>
                            <tr><td colspan="4" style="text-align: center;">No blogs pending review or in drafts.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
