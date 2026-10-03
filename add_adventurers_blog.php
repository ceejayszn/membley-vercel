<?php
require_once 'includes/db.php';

if (!$pdo) {
    die("Database connection failed.");
}

$title = "Little Hands, Big Faith: Celebrating Our Adventurers";
$slug = "celebrating-our-adventurers-" . time();
$category = "Kids";
$excerpt = "Our Adventurers remind us daily of what pure, unshakeable faith looks like. We celebrate their journey of learning, loving, and growing in Christ.";
$author = "ceejay";
$content = <<<HTML
<p><strong>Nurturing the Next Generation</strong></p>
<p>There is nothing quite as inspiring as witnessing the boundless energy and innocent faith of our young Adventurers. Week after week, they show up in their neat uniforms, eager to learn, ready to sing, and excited to discover more about the God who created them.</p>

<p>The Adventurer Club isn't just about fun activities and earning patches—though they certainly love both! It is about laying a foundational stone of faith that will support them for the rest of their lives. It is where they first learn what it means to be part of a spiritual family.</p>

<h3>"Because Jesus Loves Me, I Will Always Do My Best"</h3>
<p>This simple pledge is the heartbeat of the Adventurer ministry. We teach them that their worth isn't in being perfect, but in knowing that they are perfectly loved by Jesus. When children understand this, their natural response is to share that love with their families, their friends, and their community.</p>

<p>Every memory verse recited, every craft created, and every song sung is a stepping stone toward a lifelong relationship with Christ.</p>

<h3>A Call to Parents and the Church Family</h3>
<p>To the parents investing their time, energy, and patience into bringing these children to the club: <strong>Your labor is not in vain.</strong> You are planting seeds that will grow into trees of righteousness.</p>

<p>To our entire church family: Let us continue to surround these little ones with love, encouragement, and prayer. When they stand up front, let us smile at their courage. When they are restless, let us offer grace. They are not just the "church of tomorrow"—they are the church of today.</p>

<p>Let us continue to support our Adventurers as they learn to step out in faith, one small footprint at a time.</p>
<p>God bless our Adventurers!</p>
HTML;

try {
    $stmt = $pdo->prepare("INSERT INTO blogs (title, slug, content, excerpt, category, author_name, status) VALUES (:title, :slug, :content, :excerpt, :category, :author_name, 'published')");
    $stmt->execute([
        ':title' => $title,
        ':slug' => $slug,
        ':content' => $content,
        ':excerpt' => $excerpt,
        ':category' => $category,
        ':author_name' => $author
    ]);
    
    echo "<h2 style='color:green; font-family:sans-serif;'>✅ Blog Post 'Little Hands, Big Faith: Celebrating Our Adventurers' has been successfully published!</h2>";
    echo "<p style='font-family:sans-serif;'>You can now delete this add_adventurers_blog.php file.</p>";
} catch (Exception $e) {
    echo "<h2 style='color:red;'>❌ Failed to publish: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
