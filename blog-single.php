<?php
if (!isset($_COOKIE['device_id'])) {
    $device_id = bin2hex(random_bytes(16));
    setcookie('device_id', $device_id, time() + (86400 * 365), "/"); // 1 year
} else {
    $device_id = $_COOKIE['device_id'];
}

require_once 'includes/db.php';
require_once 'includes/header.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$post = null;

if (!empty($slug)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM blogs WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        $post = $stmt->fetch();
    } catch (PDOException $e) {
    }
}

$likes_count = 0;
$user_has_liked = false;
$comments = [];

if ($post) {
    // Increment real views
    $update_views = $pdo->prepare("UPDATE blogs SET real_views = COALESCE(real_views, 0) + 1 WHERE id = :id");
    $update_views->execute([':id' => $post['id']]);
    $post['real_views'] = ($post['real_views'] ?? 0) + 1;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_likes WHERE blog_id = :blog_id");
    $stmt->execute([':blog_id' => $post['id']]);
    $likes_count = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT id FROM blog_likes WHERE blog_id = :blog_id AND device_id = :device_id");
    $stmt->execute([':blog_id' => $post['id'], ':device_id' => $device_id]);
    $user_has_liked = (bool)$stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM blog_comments WHERE blog_id = :blog_id ORDER BY created_at DESC");
    $stmt->execute([':blog_id' => $post['id']]);
    $comments = $stmt->fetchAll();
} else {
    echo "<div class='container section-padding' style='text-align: center;'><h2>Blog Post Not Found</h2><p><a href='blog.php'>Return to Blog</a></p></div>";
    require_once 'includes/footer.php';
    exit;
}
?>

<section style="background-color: var(--primary-dark); color: white; padding: 5rem 0; background-image: linear-gradient(rgba(4,25,40,0.85), rgba(4,25,40,0.85)), url('<?php echo !empty($post['image_url']) ? htmlspecialchars($post['image_url']) : 'https://images.unsplash.com/photo-1490730141103-6cac27aaab94?auto=format&fit=crop&q=80&w=1200'; ?>'); background-size: cover; background-position: center;">
    <div class="container" style="max-width: 800px; text-align: center;">
        <span style="background-color: var(--accent); color: var(--primary-dark); font-size: 0.8rem; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 50px; text-transform: uppercase; display: inline-block; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($post['category']); ?>
        </span>
        <h1 style="color: white; font-size: 2.5rem; margin-bottom: 1rem; line-height: 1.2;"><?php echo htmlspecialchars($post['title']); ?></h1>
        <div style="font-size: 0.95rem; color: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; gap: 1.5rem;">
            <span><i class="fa-regular fa-calendar"></i> Published on <?php echo date('F d, Y', strtotime($post['created_at'])); ?></span>
            <span><i class="fa-solid fa-eye"></i> <?php echo ($post['real_views'] ?? 0) + ($post['fake_views'] ?? 0); ?> Views</span>
        </div>
    </div>
</section>

<article class="section-padding container" style="max-width: 800px; background-color: var(--bg-white); border-radius: 12px; box-shadow: var(--shadow-sm); margin-top: -3rem; position: relative; z-index: 10; padding: 3rem 2.5rem;">
    <?php if (!empty($post['video_url'])): ?>
        <div style="margin-bottom: 2rem; border-radius: 8px; overflow: hidden; box-shadow: var(--shadow-sm); position: relative; padding-bottom: 56.25%; height: 0; background: #000;">
            <iframe src="<?php echo htmlspecialchars($post['video_url']); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    <?php endif; ?>

    <?php if (!empty($post['image_url'])): ?>
        <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width: 100%; border-radius: 8px; margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
    <?php endif; ?>

    <div class="blog-content" style="color: var(--text-dark); font-size: 1.1rem; line-height: 1.8;">
        <?php 
            $content = $post['content'];
            $pdf_url = '';
            if (preg_match('/href=["\']([^"\']+\.pdf)["\']/i', $content, $matches)) {
                $pdf_url = $matches[1];
            }
            echo $content; 
        ?>

        <?php if (!empty($pdf_url)): ?>
            <div class="pdf-newspaper-container" style="margin-top: 3rem; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
                <div style="background: var(--bg-light); padding: 15px; border-bottom: 1px solid #e2e8f0; text-align: center; font-weight: bold; color: var(--primary-dark); display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-newspaper"></i> Newspaper View Mode
                </div>
                <embed src="<?php echo htmlspecialchars($pdf_url); ?>#toolbar=0&navpanes=0&scrollbar=0&view=FitH" type="application/pdf" width="100%" height="900px" style="border: none;" />
            </div>
        <?php endif; ?>
    </div>

    <div style="border-top: 1px solid var(--border-color); margin-top: 3rem; padding-top: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <a href="blog.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Blog</a>
        
        <?php
        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $encoded_url = urlencode($current_url);
        $encoded_title = urlencode($post['title']);
        $total_likes = $likes_count + ($post['fake_likes'] ?? 0);
        ?>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <button id="likeBtn" class="btn btn-outline" style="cursor: pointer; <?php echo $user_has_liked ? 'color: var(--primary); border-color: var(--primary); background: rgba(0,47,93,0.1); cursor: default;' : ''; ?>" data-blog-id="<?php echo $post['id']; ?>" data-fake-likes="<?php echo $post['fake_likes'] ?? 0; ?>" <?php echo $user_has_liked ? 'disabled' : ''; ?>>
                <i class="fa-solid fa-thumbs-up"></i> <span id="likeCount"><?php echo $total_likes; ?></span> Likes
            </button>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted);">Share:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $encoded_url; ?>" target="_blank" style="color: #3b5998;"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo $encoded_url; ?>&text=<?php echo $encoded_title; ?>" target="_blank" style="color: #1da1f2;"><i class="fa-brands fa-twitter"></i></a>
                <a href="https://api.whatsapp.com/send?text=<?php echo $encoded_title; ?>%20<?php echo $encoded_url; ?>" target="_blank" style="color: #25d366;"><i class="fa-brands fa-whatsapp"></i></a>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
        <h3 style="margin-bottom: 1.5rem;"><i class="fa-regular fa-comments"></i> Comments (<span id="commentCount"><?php echo count($comments); ?></span>)</h3>
        
        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
            <h4 style="margin-top: 0; margin-bottom: 1rem;">Leave a Comment</h4>
            <form id="commentForm">
                <input type="hidden" id="blog_id" value="<?php echo $post['id']; ?>">
                <div style="margin-bottom: 1rem;">
                    <input type="text" id="commentAuthor" placeholder="Your Name" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; font-size: 1rem;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <textarea id="commentContent" placeholder="Your Comment..." required rows="4" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; resize: vertical; font-family: inherit; font-size: 1rem;"></textarea>
                </div>
                <button type="submit" id="submitComment" class="btn btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 6px;">Post Comment</button>
            </form>
            <div id="commentError" style="color: #dc2626; margin-top: 0.5rem; display: none; font-size: 0.9rem;"></div>
        </div>

        <div id="commentsList" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php foreach($comments as $c): ?>
                <div style="background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="font-weight: bold; margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                        <span><i class="fa-solid fa-user-circle" style="color: var(--text-muted);"></i> <?php echo htmlspecialchars($c['author_name']); ?></span>
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal;"><?php echo date('M d, Y', strtotime($c['created_at'])); ?></span>
                    </div>
                    <div style="color: var(--text-dark); line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($c['content'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if(empty($comments)): ?>
                <p id="noComments" style="color: var(--text-muted);">No comments yet. Be the first to share your thoughts!</p>
            <?php endif; ?>
        </div>
    </div>
</article>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return '';
    }
    const deviceId = getCookie('device_id');

    // Handle Likes
    const likeBtn = document.getElementById('likeBtn');
    if (likeBtn && !likeBtn.disabled) {
        likeBtn.addEventListener('click', function() {
            const blogId = this.getAttribute('data-blog-id');
            const formData = new URLSearchParams();
            formData.append('action', 'like');
            formData.append('blog_id', blogId);
            formData.append('device_id', deviceId);

            fetch('api/blog_interact.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const fakeLikes = parseInt(likeBtn.getAttribute('data-fake-likes')) || 0;
                    document.getElementById('likeCount').textContent = data.likes + fakeLikes;
                    likeBtn.disabled = true;
                    likeBtn.style.color = 'var(--primary)';
                    likeBtn.style.borderColor = 'var(--primary)';
                    likeBtn.style.background = 'rgba(0,47,93,0.1)';
                    likeBtn.style.cursor = 'default';
                }
            });
        });
    }

    // Handle Comments
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('submitComment');
            btn.disabled = true;
            btn.textContent = 'Posting...';
            
            const blogId = document.getElementById('blog_id').value;
            const author = document.getElementById('commentAuthor').value;
            const content = document.getElementById('commentContent').value;

            const formData = new URLSearchParams();
            formData.append('action', 'comment');
            formData.append('blog_id', blogId);
            formData.append('device_id', deviceId);
            formData.append('author_name', author);
            formData.append('content', content);

            fetch('api/blog_interact.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.textContent = 'Post Comment';
                if (data.status === 'success') {
                    // Prepend new comment
                    const commentsList = document.getElementById('commentsList');
                    const noComments = document.getElementById('noComments');
                    if (noComments) noComments.remove();

                    const newCommentHtml = `
                        <div style="background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color);">
                            <div style="font-weight: bold; margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                                <span><i class="fa-solid fa-user-circle" style="color: var(--text-muted);"></i> ${data.comment.author}</span>
                                <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal;">Just now</span>
                            </div>
                            <div style="color: var(--text-dark); line-height: 1.6;">
                                ${data.comment.content}
                            </div>
                        </div>
                    `;
                    commentsList.insertAdjacentHTML('afterbegin', newCommentHtml);
                    
                    // Update count
                    const countSpan = document.getElementById('commentCount');
                    countSpan.textContent = parseInt(countSpan.textContent) + 1;
                    
                    // Clear form
                    commentForm.reset();
                } else {
                    const err = document.getElementById('commentError');
                    err.textContent = data.message;
                    err.style.display = 'block';
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.textContent = 'Post Comment';
            });
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
