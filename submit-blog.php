<?php
require_once 'includes/db.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$valid_token = false;
$token_id = null;

if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT * FROM blog_invites WHERE token = :token AND is_used = 0");
    $stmt->execute([':token' => $token]);
    $invite = $stmt->fetch();
    if ($invite) {
        $valid_token = true;
        $token_id = $invite['id'];
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $category = trim($_POST['category'] ?? 'General');

    if (empty($title) || empty($content)) {
        $message = '<div class="alert alert-danger" style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px;">Title and content are required.</div>';
    } else {
        // Generate a basic slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug .= '-' . time(); // Ensure uniqueness

        if (empty($excerpt)) {
            $excerpt = substr(strip_tags($content), 0, 150) . '...';
        }

        try {
            $pdo->beginTransaction();

            $insert = $pdo->prepare("INSERT INTO blogs (title, slug, content, excerpt, category, status) VALUES (:title, :slug, :content, :excerpt, :category, 'review')");
            $insert->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':content' => $content,
                ':excerpt' => $excerpt,
                ':category' => $category
            ]);

            $update_token = $pdo->prepare("UPDATE blog_invites SET is_used = 1 WHERE id = :id");
            $update_token->execute([':id' => $token_id]);

            $pdo->commit();
            $valid_token = false; // Token used
            $message = '<div class="alert alert-success" style="padding: 15px; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px; font-weight: bold;"><i class="fa-solid fa-check-circle"></i> Blog submitted successfully for review! The admin will review it before publishing.</div>';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = '<div class="alert alert-danger" style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px;">An error occurred while submitting. Please try again.</div>';
        }
    }
}

require_once 'includes/header.php';
?>

<section style="background-color: var(--primary-dark); color: white; padding: 4rem 0;">
    <div class="container" style="text-align: center;">
        <h1 style="color: white; font-size: 2.5rem; margin-bottom: 1rem;">Submit a Blog Post</h1>
        <p style="color: rgba(255,255,255,0.8); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Use this editor to format and submit your blog post for review.</p>
    </div>
</section>

<section class="section-padding container">
    <div style="max-width: 800px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: 12px; box-shadow: var(--shadow-md);">
        <?php echo $message; ?>

        <?php if ($valid_token): ?>
            <form method="post" action="submit-blog.php?token=<?php echo htmlspecialchars($token); ?>" id="blogForm">
                <div style="margin-bottom: 1.5rem;">
                    <label for="title" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Post Title *</label>
                    <input type="text" id="title" name="title" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;">
                </div>

                <div style="margin-bottom: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label for="category" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Category</label>
                        <select id="category" name="category" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;">
                            <option value="General">General</option>
                            <option value="Sermons">Sermons</option>
                            <option value="Youth & Kids">Youth & Kids</option>
                            <option value="Announcements">Announcements</option>
                            <option value="Ministries">Ministries</option>
                        </select>
                    </div>
                    <div>
                        <label for="excerpt" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Short Excerpt (Optional)</label>
                        <input type="text" id="excerpt" name="excerpt" placeholder="A brief summary..." style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;">
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Blog Content *</label>
                    <input type="hidden" name="content" id="hiddenContent">
                    <!-- Include Quill stylesheet -->
                    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
                    <div id="editor" style="height: 350px; font-family: inherit; font-size: 1rem;"></div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; border-radius: 8px;"><i class="fa-solid fa-paper-plane"></i> Upload for Review</button>
            </form>

            <!-- Include the Quill library -->
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
                    },
                    placeholder: 'Write your blog post here...'
                });

                document.getElementById('blogForm').onsubmit = function() {
                    var html = quill.root.innerHTML;
                    if (quill.getText().trim().length === 0) {
                        alert("Content cannot be empty.");
                        return false;
                    }
                    document.getElementById('hiddenContent').value = html;
                };
            </script>
        <?php elseif(empty($message)): ?>
            <div style="text-align: center; padding: 2rem;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 3rem; color: #d97706; margin-bottom: 1rem;"></i>
                <h2 style="color: var(--text-dark);">Invalid or Expired Link</h2>
                <p style="color: var(--text-muted); margin-top: 0.5rem;">This invite link is either invalid or has already been used.</p>
                <a href="index.php" class="btn btn-outline" style="margin-top: 1.5rem;">Return Home</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
