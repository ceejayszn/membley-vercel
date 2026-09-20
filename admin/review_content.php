<?php
require_once 'auth.php';
check_auth();

require_once '../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: content_submissions.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_status') {
        $status = $_POST['status'] ?? 'pending';
        $admin_notes = $_POST['admin_notes'] ?? '';
        $rejection_reason = $_POST['rejection_reason'] ?? '';
        
        try {
            $stmt = $pdo->prepare("UPDATE content_submissions SET status = :status, admin_notes = :admin_notes, rejection_reason = :rejection_reason, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->execute([
                ':status' => $status,
                ':admin_notes' => $admin_notes,
                ':rejection_reason' => $rejection_reason,
                ':id' => $id
            ]);
            $message = '<div class="alert alert-success" style="padding: 15px; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px;">Submission updated successfully.</div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger" style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px;">Error updating: ' . $e->getMessage() . '</div>';
        }
    } elseif ($action == 'publish_to_blog') {
        try {
            $pdo->beginTransaction();
            
            // Get submission details
            $stmt = $pdo->prepare("SELECT * FROM content_submissions WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $sub = $stmt->fetch();
            
            if ($sub) {
                // Generate slug
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $sub['title'])));
                $slug .= '-' . time();
                
                $excerpt = $sub['summary'];
                if (empty($excerpt)) {
                    $excerpt = substr(strip_tags($sub['content']), 0, 150) . '...';
                }
                
                // Determine image path (fixing relative path since it's going to public root)
                $image_url = $sub['featured_image'];
                if ($image_url && strpos($image_url, 'assets/') === 0) {
                    // Path is fine for blogs table which assumes relative from root
                }
                
                // Insert into blogs
                $insert = $pdo->prepare("INSERT INTO blogs (title, slug, content, excerpt, category, author_name, image_url, status) VALUES (:title, :slug, :content, :excerpt, :category, :author_name, :image_url, 'published')");
                $insert->execute([
                    ':title' => $sub['title'],
                    ':slug' => $slug,
                    ':content' => $sub['content'],
                    ':excerpt' => $excerpt,
                    ':category' => $sub['submission_type'],
                    ':author_name' => $sub['name'],
                    ':image_url' => $image_url
                ]);
                
                // Update submission status
                $update = $pdo->prepare("UPDATE content_submissions SET status = 'published', published_at = CURRENT_TIMESTAMP WHERE id = :id");
                $update->execute([':id' => $id]);
                
                $pdo->commit();
                $message = '<div class="alert alert-success" style="padding: 15px; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px;">Successfully published to the public feed!</div>';
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = '<div class="alert alert-danger" style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px;">Error publishing: ' . $e->getMessage() . '</div>';
        }
    }
}

// Fetch current data
$stmt = $pdo->prepare("SELECT * FROM content_submissions WHERE id = :id");
$stmt->execute([':id' => $id]);
$submission = $stmt->fetch();

if (!$submission) {
    header('Location: content_submissions.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Content - Membley SDA Admin</title>
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

                <li><a href="content_submissions.php" class="sidebar-link active"><i class="fa-solid fa-file-signature" style="margin-right: 0.5rem;"></i> Content Submissions</a></li>

                <li><a href="submissions.php" class="sidebar-link"><i class="fa-solid fa-envelope-open-text" style="margin-right: 0.5rem;"></i> Form Submissions</a></li>

                <li><a href="blogs.php" class="sidebar-link"><i class="fa-solid fa-newspaper" style="margin-right: 0.5rem;"></i> Manage Blogs</a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="logout.php" class="sidebar-link" style="color: #ff8b8b;"><i class="fa-solid fa-right-from-bracket" style="margin-right: 0.5rem;"></i> Sign Out</a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="admin-title">Review Submission</div>
                <div class="admin-user">
                    <a href="content_submissions.php" class="admin-btn-outline" style="margin: 0; padding: 0.5rem 0.75rem; font-size: 0.85rem;"><i class="fa-solid fa-arrow-left"></i> Back to List</a>
                </div>
            </header>

            <div class="admin-content">
                <?php echo $message; ?>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    <!-- Left Column: Content -->
                    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="margin-bottom: 1rem;">
                            <span class="badge" style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 0.9rem;">
                                <?php echo htmlspecialchars($submission['submission_type']); ?>
                            </span>
                            <span style="color: var(--text-muted); font-size: 0.9rem; margin-left: 1rem;">
                                Submitted on <?php echo date('M d, Y \a\t H:i', strtotime($submission['created_at'])); ?>
                            </span>
                        </div>
                        
                        <h2 style="margin-top: 0; margin-bottom: 0.5rem; color: var(--text-dark);"><?php echo htmlspecialchars($submission['title']); ?></h2>
                        
                        <?php if(!empty($submission['summary'])): ?>
                            <p style="color: var(--text-muted); font-style: italic; margin-bottom: 1.5rem; padding-left: 1rem; border-left: 3px solid var(--primary-light);">
                                <?php echo nl2br(htmlspecialchars($submission['summary'])); ?>
                            </p>
                        <?php endif; ?>

                        <?php if(!empty($submission['featured_image'])): 
                            $img_url = get_media_url($submission['featured_image'], true);
                        ?>
                            <div style="margin-bottom: 1.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; text-align: center;">
                                <div style="margin-bottom: 1rem; max-height: 480px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #0f172a; border-radius: 6px;">
                                    <img src="<?php echo htmlspecialchars($img_url); ?>" alt="Featured Image" style="max-width: 100%; max-height: 480px; object-fit: contain;">
                                </div>
                                <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                                    <a href="<?php echo htmlspecialchars($img_url); ?>" target="_blank" class="admin-btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                                        <i class="fa-solid fa-arrow-up-right-from-square" style="margin-right: 0.4rem;"></i> Open Full Image
                                    </a>
                                    <a href="<?php echo htmlspecialchars($img_url); ?>" download target="_blank" class="admin-btn" style="font-size: 0.85rem; padding: 0.5rem 1rem; background-color: var(--primary); text-decoration: none;">
                                        <i class="fa-solid fa-download" style="margin-right: 0.4rem;"></i> Download Image
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div style="border-top: 1px solid #e2e8f0; padding-top: 1.5rem; margin-bottom: 2rem; line-height: 1.6; color: #334155;">
                            <?php echo $submission['content']; // Raw HTML from rich text ?>
                        </div>

                        <?php if(!empty($submission['attachment'])): 
                            $doc_url = get_media_url($submission['attachment'], true);
                        ?>
                            <div style="background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                                <div style="display: flex; align-items: center;">
                                    <i class="fa-solid fa-file-pdf" style="color: #ef4444; font-size: 1.5rem; margin-right: 0.75rem;"></i> 
                                    <strong style="color: var(--text-dark);">Attached Document / PDF</strong>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="<?php echo htmlspecialchars($doc_url); ?>" target="_blank" class="admin-btn-outline" style="font-size: 0.85rem; padding: 0.45rem 0.85rem;">
                                        <i class="fa-solid fa-eye" style="margin-right: 0.3rem;"></i> View PDF
                                    </a>
                                    <a href="<?php echo htmlspecialchars($doc_url); ?>" download target="_blank" class="admin-btn" style="font-size: 0.85rem; padding: 0.45rem 0.85rem; background-color: var(--primary); text-decoration: none;">
                                        <i class="fa-solid fa-download" style="margin-right: 0.3rem;"></i> Download PDF
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right Column: Meta & Actions -->
                    <div>
                        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                            <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: var(--text-dark); border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">Submitter Details</h3>
                            <p style="margin-bottom: 0.5rem;"><strong>Name:</strong> <?php echo htmlspecialchars($submission['name']); ?></p>
                            <p style="margin-bottom: 0.5rem;"><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>"><?php echo htmlspecialchars($submission['email']); ?></a></p>
                            <?php if(!empty($submission['phone'])): ?>
                                <p style="margin-bottom: 0.5rem;"><strong>Phone:</strong> <?php echo htmlspecialchars($submission['phone']); ?></p>
                            <?php endif; ?>
                        </div>

                        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: var(--text-dark); border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">Moderation</h3>
                            
                            <form method="post" action="review_content.php?id=<?php echo $id; ?>">
                                <input type="hidden" name="action" value="update_status">
                                
                                <div style="margin-bottom: 1rem;">
                                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Status</label>
                                    <select name="status" style="width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #cbd5e1;">
                                        <option value="pending" <?php echo $submission['status']=='pending'?'selected':'';?>>Pending</option>
                                        <option value="under_review" <?php echo $submission['status']=='under_review'?'selected':'';?>>Under Review</option>
                                        <option value="approved" <?php echo $submission['status']=='approved'?'selected':'';?>>Approved</option>
                                        <option value="published" <?php echo $submission['status']=='published'?'selected':'';?>>Published</option>
                                        <option value="rejected" <?php echo $submission['status']=='rejected'?'selected':'';?>>Rejected</option>
                                        <option value="needs_changes" <?php echo $submission['status']=='needs_changes'?'selected':'';?>>Needs Changes</option>
                                    </select>
                                </div>
                                
                                <div style="margin-bottom: 1rem;">
                                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Admin Notes (Internal)</label>
                                    <textarea name="admin_notes" rows="3" style="width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #cbd5e1;"><?php echo htmlspecialchars($submission['admin_notes'] ?? ''); ?></textarea>
                                </div>

                                <div style="margin-bottom: 1.5rem;">
                                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Rejection/Change Reason</label>
                                    <textarea name="rejection_reason" rows="3" style="width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #cbd5e1;"><?php echo htmlspecialchars($submission['rejection_reason'] ?? ''); ?></textarea>
                                </div>

                                <button type="submit" class="admin-btn" style="width: 100%; justify-content: center;"><i class="fa-solid fa-floppy-disk"></i> Save Moderation</button>
                            </form>

                            <?php if ($submission['status'] == 'approved'): ?>
                                <hr style="margin: 1.5rem 0; border: 0; border-top: 1px solid #e2e8f0;">
                                <form method="post" action="review_content.php?id=<?php echo $id; ?>" onsubmit="return confirm('Are you sure you want to publish this content to the public feed?');">
                                    <input type="hidden" name="action" value="publish_to_blog">
                                    <button type="submit" class="admin-btn" style="width: 100%; justify-content: center; background-color: var(--success);"><i class="fa-solid fa-globe"></i> Publish to Website</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($submission['status'] == 'published'): ?>
                                <hr style="margin: 1.5rem 0; border: 0; border-top: 1px solid #e2e8f0;">
                                <div style="padding: 1rem; background: #cffafe; color: #155e75; border-radius: 6px; text-align: center;">
                                    <i class="fa-solid fa-circle-check"></i> This content is currently published on the public website.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
