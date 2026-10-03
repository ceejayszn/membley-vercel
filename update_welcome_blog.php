<?php
require_once 'includes/db.php';

if (!$pdo) {
    die("Database connection failed.");
}

try {
    $stmt = $pdo->prepare("UPDATE blogs SET image_url = '/assets/images/welcome_img.webp' WHERE slug = 'welcome-to-our-new-website'");
    $stmt->execute();
    
    echo "<h2 style='color:green; font-family:sans-serif;'>✅ The 'Welcome to our New Website!' blog post has been successfully updated with the new image!</h2>";
    echo "<p style='font-family:sans-serif;'>You can now safely delete this update_welcome_blog.php file.</p>";
} catch (Exception $e) {
    echo "<h2 style='color:red;'>❌ Failed to update: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
