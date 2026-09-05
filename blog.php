<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$blogs = [];
try {
    $stmt = $pdo->query("SELECT * FROM blogs WHERE status = 'published' ORDER BY created_at DESC");
    $blogs = $stmt->fetchAll();
} catch (PDOException $e) {
}
?>

<section class="section-padding container">
    <div class="section-header" style="margin-bottom: 3rem;">
        <span class="section-subtitle"><i class="fa-solid fa-pen-nib"></i> Church Articles</span>
        <h2 class="section-title">Latest Blog Posts</h2>
    </div>

    <?php if (empty($blogs)): ?>
        <div style="text-align: center; max-width: 600px; margin: 0 auto; padding: 4rem 2rem; background-color: var(--bg-white); border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
            <i class="fa-solid fa-pen-nib" style="font-size: 3rem; color: var(--accent); margin-bottom: 1.5rem;"></i>
            <h2 style="color: var(--primary); font-size: 2rem; margin-bottom: 1rem;">No Blogs Yet!</h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 2rem;">
                Check back later for new articles.
            </p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;">
            <?php foreach ($blogs as $blog): ?>
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; transition: transform 0.3s ease;">
                    <?php if (!empty($blog['image_url'])): ?>
                        <div style="height: 200px; background-image: url('<?php echo htmlspecialchars($blog['image_url']); ?>'); background-size: cover; background-position: center;"></div>
                    <?php else: ?>
                        <div style="height: 200px; background: var(--primary-dark); display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-church" style="font-size: 4rem; color: rgba(255,255,255,0.1);"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div style="padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--primary-light); text-transform: uppercase; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($blog['category']); ?></span>
                        <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text-dark); line-height: 1.4;"><?php echo htmlspecialchars($blog['title']); ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem; flex-grow: 1;"><?php echo htmlspecialchars($blog['excerpt']); ?></p>
                        <a href="blog-single.php?slug=<?php echo urlencode($blog['slug']); ?>" class="btn btn-outline" style="align-self: flex-start;">Read More</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
