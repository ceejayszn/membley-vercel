<?php
require_once 'includes/db.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $submission_type = trim($_POST['submission_type'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($name) || empty($email) || empty($submission_type) || empty($title) || empty($content)) {
        $message = '<div class="alert alert-danger" style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px;">Please fill in all required fields.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger" style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px;">Invalid email format.</div>';
    } else {
        try {
            $pdo->beginTransaction();

            $image_url = trim($_POST['image_url'] ?? '');
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
                $file_type = mime_content_type($_FILES['featured_image']['tmp_name']);
                
                if (in_array($file_type, $allowed_types) && $_FILES['featured_image']['size'] <= 5000000) {
                    $ext = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
                    $new_filename = 'submissions/' . uniqid('img_') . '.' . $ext;
                    $image_url = uploadToVercelBlob($_FILES['featured_image']['tmp_name'], $new_filename);
                } else {
                    throw new Exception("Invalid image file or file too large (Max 5MB).");
                }
            }

            $attachment_url = trim($_POST['attachment_url'] ?? '');
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
                $file_type = mime_content_type($_FILES['attachment']['tmp_name']);
                if ($file_type == 'application/pdf' && $_FILES['attachment']['size'] <= 10000000) {
                    $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                    $new_filename = 'submissions/' . uniqid('doc_') . '.' . $ext;
                    $attachment_url = uploadToVercelBlob($_FILES['attachment']['tmp_name'], $new_filename);
                } else {
                    throw new Exception("Attachment must be a PDF and under 10MB.");
                }
            }

            $insert = $pdo->prepare("
                INSERT INTO content_submissions 
                (name, email, phone, submission_type, title, summary, content, featured_image, attachment, status) 
                VALUES (:name, :email, :phone, :type, :title, :summary, :content, :image, :attachment, 'pending')
            ");
            
            $insert->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':type' => $submission_type,
                ':title' => $title,
                ':summary' => $summary,
                ':content' => $content,
                ':image' => $image_url,
                ':attachment' => $attachment_url
            ]);

            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = '<div class="alert alert-danger" style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px;">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

require_once 'includes/header.php';
?>

<section style="background-color: var(--primary-dark); color: white; padding: 4rem 0;">
    <div class="container" style="text-align: center;">
        <h1 style="color: white; font-size: 2.5rem; margin-bottom: 1rem;">Share Something With Membley</h1>
        <p style="color: rgba(255,255,255,0.8); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
            Have a story, review, update, announcement, correction, or useful information to share? 
            Send it to us and our team will review it before anything is published.
        </p>
    </div>
</section>

<section class="section-padding container">
    <div style="max-width: 800px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: 12px; box-shadow: var(--shadow-md);">
        <?php if ($success): ?>
            <div style="text-align: center; padding: 2rem;">
                <i class="fa-solid fa-circle-check" style="font-size: 4rem; color: var(--success); margin-bottom: 1rem;"></i>
                <h2 style="color: var(--text-dark); margin-bottom: 1rem;">Submission Received</h2>
                <p style="color: var(--text-muted); font-size: 1.1rem;">
                    Thank you for sharing this with Membley. Your submission has been received and will be reviewed by our team before publication.
                </p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 2rem; display: inline-block;">Return to Home</a>
            </div>
        <?php else: ?>
            <?php echo $message; ?>
            
            <form method="post" action="submit.php" id="submissionForm" enctype="multipart/form-data">
                <h3 style="margin-bottom: 1rem; color: var(--primary); border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem;">Your Details</h3>
                
                <div style="margin-bottom: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label for="name" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Name *</label>
                        <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;">
                    </div>
                    <div>
                        <label for="email" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Email *</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;">
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <label for="phone" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Phone (Optional)</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;">
                </div>

                <h3 style="margin-bottom: 1rem; color: var(--primary); border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem;">What Are You Sharing?</h3>

                <div style="margin-bottom: 1.5rem; display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
                    <div>
                        <label for="submission_type" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Submission Type *</label>
                        <select id="submission_type" name="submission_type" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem; background-color: white;">
                            <option value="">Select an option</option>
                            <option value="Blog / Article">Blog / Article</option>
                            <option value="Review">Review</option>
                            <option value="News / Update">News / Update</option>
                            <option value="Announcement">Announcement</option>
                            <option value="Community Information">Community Information</option>
                            <option value="Correction">Correction</option>
                            <option value="Event">Event</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="title" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Title *</label>
                        <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" placeholder="E.g., Youth Choir Needs New Instruments" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;">
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <label for="summary" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Short Description / Summary (Optional)</label>
                    <textarea id="summary" name="summary" rows="2" placeholder="Briefly summarize what this is about..." style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem; resize: vertical;"><?php echo htmlspecialchars($_POST['summary'] ?? ''); ?></textarea>
                </div>

                <h3 style="margin-bottom: 1rem; color: var(--primary); border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem;">Your Content</h3>

                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Main Content *</label>
                    <input type="hidden" name="content" id="hiddenContent">
                    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
                    <div id="editor" style="height: 300px; font-family: inherit; font-size: 1rem; background: white;">
                        <?php echo $_POST['content'] ?? ''; ?>
                    </div>
                </div>

                <h3 style="margin-bottom: 1rem; color: var(--primary); border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem;">Images & Documents</h3>

                <div style="margin-bottom: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label for="featured_image" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Featured Image Upload (Optional)</label>
                        <input type="file" id="featured_image" name="featured_image" accept="image/jpeg,image/png,image/webp" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; background: var(--bg-light);">
                        <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">JPG, PNG, WebP (Max 5MB).</small>
                        
                        <label for="image_url" style="display: block; font-weight: 600; margin-bottom: 0.5rem; margin-top: 1rem; color: var(--text-dark);">OR Image URL</label>
                        <input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.jpg" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem;">
                    </div>
                    <div>
                        <label for="attachment" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Supporting PDF Upload (Optional)</label>
                        <input type="file" id="attachment" name="attachment" accept="application/pdf" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; background: var(--bg-light);">
                        <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">PDF only (Max 10MB).</small>
                        
                        <label for="attachment_url" style="display: block; font-weight: 600; margin-bottom: 0.5rem; margin-top: 1rem; color: var(--text-dark);">OR Document URL</label>
                        <input type="url" id="attachment_url" name="attachment_url" placeholder="https://example.com/doc.pdf" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem;">
                    </div>
                </div>

                <div style="margin-top: 2.5rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.25rem; font-size: 1.1rem; border-radius: 8px; font-weight: 600;">
                        <i class="fa-solid fa-paper-plane" style="margin-right: 0.5rem;"></i> Submit for Review
                    </button>
                    <p style="text-align: center; color: var(--text-muted); font-size: 0.9rem; margin-top: 1rem;">By submitting, you agree to allow the administrator to review and publish this content.</p>
                </div>
            </form>

            <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
            <script>
                var quill = new Quill('#editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [2, 3, false] }],
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['link', 'clean']
                        ]
                    },
                    placeholder: 'Write your content here...'
                });

                document.getElementById('submissionForm').onsubmit = function(e) {
                    var html = quill.root.innerHTML;
                    if (quill.getText().trim().length === 0) {
                        e.preventDefault();
                        alert("Please enter the main content.");
                        return false;
                    }
                    document.getElementById('hiddenContent').value = html;
                };
                
                // Select previous value if it exists
                const prevType = "<?php echo htmlspecialchars($_POST['submission_type'] ?? ''); ?>";
                if(prevType) {
                    document.getElementById('submission_type').value = prevType;
                }
            </script>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
