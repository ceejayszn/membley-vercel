<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$media_posts = [];
try {
    $stmt = $pdo->query("SELECT * FROM blogs WHERE status = 'published' AND category = 'Media TV' ORDER BY created_at DESC");
    $media_posts = $stmt->fetchAll();
} catch (PDOException $e) {
}
?>

<section style="background-color: var(--primary-dark); color: white; padding: 4rem 0;">
    <div class="container" style="text-align: center;">
        <h1 style="color: white; font-size: 2.5rem; margin-bottom: 1rem;">Membley Media TV</h1>
        <p style="color: rgba(255,255,255,0.8); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Watch our latest sermons, event highlights, and special video broadcasts.</p>
    </div>
</section>

<section class="section-padding container">
    <?php if (empty($media_posts)): ?>
        <div style="text-align: center; max-width: 600px; margin: 0 auto; padding: 4rem 2rem; background-color: var(--bg-white); border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
            <i class="fa-solid fa-video" style="font-size: 3rem; color: var(--accent); margin-bottom: 1.5rem;"></i>
            <h2 style="color: var(--primary); font-size: 2rem; margin-bottom: 1rem;">No Videos Yet!</h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 2rem;">
                Check back later for exciting video content.
            </p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;">
            <?php foreach ($media_posts as $post): ?>
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; transition: transform 0.3s ease;">
                    <div style="position: relative; height: 200px; background-color: #000;">
                        <?php if (!empty($post['video_url'])): ?>
                            <iframe src="<?php echo htmlspecialchars($post['video_url']); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen></iframe>
                        <?php elseif (!empty($post['image_url'])): ?>
                            <div style="width: 100%; height: 100%; background-image: url('<?php echo htmlspecialchars($post['image_url']); ?>'); background-size: cover; background-position: center;"></div>
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-play-circle" style="font-size: 4rem; color: rgba(255,255,255,0.2);"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column;">
                        <h3 style="font-size: 1.25rem; margin-bottom: 0.25rem; color: var(--text-dark); line-height: 1.4;"><?php echo htmlspecialchars($post['title']); ?></h3>
                        <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.75rem;"><i class="fa-solid fa-pen-fancy"></i> By <?php echo htmlspecialchars($post['author_name'] ?? 'Membley Admin'); ?></div>
                        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem; flex-grow: 1;"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                            <span style="font-size: 0.85rem; color: #64748b;"><i class="fa-solid fa-eye"></i> <?php echo ($post['real_views'] ?? 0) + ($post['fake_views'] ?? 0); ?></span>
                            <a href="blog-single.php?slug=<?php echo urlencode($post['slug']); ?>" class="btn btn-outline btn-sm" style="font-size: 0.85rem; padding: 0.25rem 0.75rem;">Watch & Discuss</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
