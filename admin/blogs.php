<?php
require_once 'auth.php';
check_auth();

require_once '../includes/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$error = '';
$success = '';

function generateSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
}

if ($action == 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header('Location: blogs.php?msg=deleted');
        exit;
    } catch (PDOException $e) {
        $error = 'Failed to delete blog post.';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');

    if (empty($title) || empty($content) || empty($excerpt)) {
        $error = 'Please fill in Title, Excerpt, and Content.';
    } else {
        $slug = generateSlug($title);
        
        if ($action == 'add') {
            try {
                $check = $pdo->prepare("SELECT COUNT(*) FROM blogs WHERE slug = :slug");
                $check->execute([':slug' => $slug]);
                if ($check->fetchColumn() > 0) {
                    $slug .= '-' . time();
                }

                $stmt = $pdo->prepare("INSERT INTO blogs (title, slug, content, excerpt, image_url, category) VALUES (:title, :slug, :content, :excerpt, :image_url, :category)");
                $stmt->execute([
                    ':title' => $title,
                    ':slug' => $slug,
                    ':content' => $content,
                    ':excerpt' => $excerpt,
                    ':image_url' => $image_url,
                    ':category' => $category
                ]);
                header('Location: blogs.php?msg=added');
                exit;
            } catch (PDOException $e) {
                $error = 'Failed to add blog post: ' . $e->getMessage();
            }
        } elseif ($action == 'edit' && $id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE blogs SET title = :title, content = :content, excerpt = :excerpt, image_url = :image_url, category = :category WHERE id = :id");
                $stmt->execute([
                    ':title' => $title,
                    ':content' => $content,
                    ':excerpt' => $excerpt,
                    ':image_url' => $image_url,
                    ':category' => $category,
                    ':id' => $id
                ]);
                header('Location: blogs.php?msg=updated');
                exit;
            } catch (PDOException $e) {
                $error = 'Failed to update blog post: ' . $e->getMessage();
            }
        }
    }
}

$post_data = null;
if ($action == 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $post_data = $stmt->fetch();
    if (!$post_data) {
        header('Location: blogs.php');
        exit;
    }
}

$blogs = [];
if (empty($action)) {
    try {
        $stmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC");
        $blogs = $stmt->fetchAll();
    } catch (PDOException $e) {
    }
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
if ($msg == 'added') $success = 'Blog post created successfully.';
if ($msg == 'updated') $success = 'Blog post updated successfully.';
if ($msg == 'deleted') $success = 'Blog post deleted successfully.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blogs - Membley SDA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="admin-container">
                <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <i class="fa-solid fa-church"></i> Membley SDA Admin
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-gauge" style="margin-right: 0.5rem;"></i> Dashboard</a></li>
                <li><a href="forms.php" class="sidebar-link"><i class="fa-solid fa-wpforms" style="margin-right: 0.5rem;"></i> Manage Forms</a></li>
                <li><a href="analytics.php" class="sidebar-link"><i class="fa-solid fa-chart-line" style="margin-right: 0.5rem;"></i> Visitor Analytics</a></li>
                <li><a href="blogs.php" class="sidebar-link active"><i class="fa-solid fa-newspaper" style="margin-right: 0.5rem;"></i> Manage Blogs</a></li>
                <li><a href="submissions.php" class="sidebar-link"><i class="fa-solid fa-envelope-open-text" style="margin-right: 0.5rem;"></i> Submissions</a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="logout.php" class="sidebar-link" style="color: #ff8b8b;"><i class="fa-solid fa-right-from-bracket" style="margin-right: 0.5rem;"></i> Sign Out</a>
            </div>
        </aside>

                <main class="admin-main">
            <header class="admin-header">
                <div class="admin-title">
                    <?php 
                        if ($action == 'add') echo 'Create Blog Post';
                        elseif ($action == 'edit') echo 'Edit Blog Post';
                        else echo 'Manage Blog & Sermons';
                    ?>
                </div>
                <div class="admin-user">
                    Welcome, <span style="color: var(--primary-light);"><?php echo htmlspecialchars(get_logged_in_user()); ?></span>
                </div>
            </header>

            <div class="admin-content">
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                                <?php if ($action == 'add' || $action == 'edit'): ?>
                    <form action="blogs.php?action=<?php echo $action; ?><?php echo ($action == 'edit') ? '&id='.$id : ''; ?>" method="POST" class="admin-form">
                        <div class="admin-form-group">
                            <label class="admin-label" for="title">Post Title *</label>
                            <input type="text" id="title" name="title" class="admin-input" placeholder="e.g. Preparing for Camp Meeting" value="<?php echo htmlspecialchars($post_data['title'] ?? ''); ?>" required>
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-label" for="category">Category</label>
                            <select id="category" name="category" class="admin-select" style="background-color: white;">
                                <option value="Announcements" <?php echo (($post_data['category'] ?? '') == 'Announcements') ? 'selected' : ''; ?>>Announcements</option>
                                <option value="Sermons" <?php echo (($post_data['category'] ?? '') == 'Sermons') ? 'selected' : ''; ?>>Sermons</option>
                                <option value="Youth & Kids" <?php echo (($post_data['category'] ?? '') == 'Youth & Kids') ? 'selected' : ''; ?>>Youth & Kids</option>
                                <option value="Health Ministries" <?php echo (($post_data['category'] ?? '') == 'Health Ministries') ? 'selected' : ''; ?>>Health Ministries</option>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-label" for="image_url">Feature Image URL</label>
                            <input type="url" id="image_url" name="image_url" class="admin-input" placeholder="https://images.unsplash.com/..." value="<?php echo htmlspecialchars($post_data['image_url'] ?? ''); ?>">
                            <small style="color: #637381; font-size: 0.8rem;">Leave blank to use default placeholder image.</small>
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-label" for="excerpt">Brief Excerpt *</label>
                            <input type="text" id="excerpt" name="excerpt" class="admin-input" placeholder="Enter a single sentence summary of the post..." value="<?php echo htmlspecialchars($post_data['excerpt'] ?? ''); ?>" required>
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-label" for="content">Full Post Content (HTML Allowed) *</label>
                            <textarea id="content" name="content" class="admin-textarea" style="min-height: 250px;" required><?php echo htmlspecialchars($post_data['content'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="admin-btn"><i class="fa-solid fa-save"></i> Save Post</button>
                        <a href="blogs.php" class="admin-btn-outline">Cancel</a>
                    </form>

                                <?php else: ?>
                    <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
                        <a href="blogs.php?action=add" class="admin-btn"><i class="fa-solid fa-plus"></i> Add New Post</a>
                    </div>

                    <div class="card-table-wrap">
                        <div class="card-table-header">
                            <span class="table-title">Published Posts</span>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Excerpt</th>
                                        <th style="text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($blogs)): ?>
                                        <?php foreach ($blogs as $b): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($b['created_at'])); ?></td>
                                                <td style="font-weight: 600;"><?php echo htmlspecialchars($b['title']); ?></td>
                                                <td><span class="badge badge-read"><?php echo htmlspecialchars($b['category']); ?></span></td>
                                                <td><span style="font-size: 0.85rem; color: #555;"><?php echo htmlspecialchars($b['excerpt']); ?></span></td>
                                                <td style="text-align: right;">
                                                    <a href="blogs.php?action=edit&id=<?php echo $b['id']; ?>" class="btn-sm btn-edit"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                                                    <a href="blogs.php?action=delete&id=<?php echo $b['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this post?');"><i class="fa-regular fa-trash-can"></i> Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; color: #637381; padding: 2rem;">No posts published yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>

</body>
</html>
