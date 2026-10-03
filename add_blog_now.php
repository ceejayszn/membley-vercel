<?php
require_once 'includes/db.php';

if (!$pdo) {
    die("Database connection failed.");
}

$title = "Christian, Yet Not Christlike";
$slug = "christian-yet-not-christlike-" . time();
$category = "Youth";
$excerpt = "There is something unsettling about becoming so familiar with the things of God that they no longer move us...";
$author = "ceejay";
$content = <<<HTML
<p><strong>We Have a Big Problem</strong></p>
<p>There is something unsettling about becoming so familiar with the things of God that they no longer move us.</p>
<p>We can attend church every week. We can sit through Sabbath School, participate in worship, sing hymns, listen to sermons, quote Scripture, and still somehow miss the very One all of it was meant to point us toward.</p>
<p>That should concern us.</p>
<p>But even that, we do not do.</p>
<p>We arrive late. We miss Sabbath Communion. We hear the sermons but overlook Scripture. We murmur songs instead of truly worshipping.</p>
<p>How bad is it?</p>
<p>Perhaps we are asking ourselves the wrong questions. The more uncomfortable question should be:</p>
<p><em>“Is my life actually becoming what God intended when He called me to be His?”</em></p>
<h3>When Sabbath Becomes Just Another Sabbath</h3>
<p>We know what we should and shouldn't do. We know where we are and aren't supposed to be. We know when it begins and ends. But somehow, between knowing the rules and keeping the schedule, we can forget the purpose.</p>
<p>We can become experts at <strong>observing the Sabbath while failing to experience what the Sabbath was designed to produce in us.</strong></p>
<ul>
  <li>Rest.</li>
  <li>Worship.</li>
  <li>Relationship with God.</li>
  <li>Mercy.</li>
  <li>Service.</li>
  <li>Reflection.</li>
  <li>Renewal.</li>
</ul>
<p>There you have it.</p>
<h3>The Danger Isn't Always Rebellion</h3>
<p>Sometimes we imagine that the greatest threat to our spiritual lives is abandoning church altogether.</p>
<p>But there is another danger:</p>
<p><strong>Remaining in church while gradually losing Christ.</strong></p>
<p>You can be present without being engaged.</p>
<p>You can participate without surrendering.</p>
<p>You can know the language of Christianity without living its principles.</p>
<p>You can defend the truth while treating people without love.</p>
<p>You can condemn hypocrisy while failing to examine your own.</p>
<p>And perhaps most dangerously, you can become so accustomed to religious activity that you stop noticing the distance between <strong>what you do and why God asked you to do it.</strong></p>
<p>That is where the question becomes personal:</p>
<p><em>“Are we doing what the Lord designed—or merely what we have become accustomed to doing?”</em></p>
<h3>Church, But Not Christ</h3>
<p>The church is supposed to be a community of people being transformed by Christ.</p>
<p>Yet sometimes we can become more concerned with being identified as Christians than with becoming Christlike.</p>
<p>We can know who belongs.</p>
<p>We can know who doesn't.</p>
<p>We can know the standards.</p>
<p>We can know the expectations.</p>
<p>We can know the labels.</p>
<p>But do we know the heart of Christ?</p>
<p>Because Christianity was never supposed to be merely a system for identifying ourselves.</p>
<p><strong>It was supposed to be a life surrendered to Jesus.</strong></p>
<p>And if our religious identity becomes more important than our relationship with Christ, we have a problem.</p>
<h3>It Is Not Only About Your Salvation</h3>
<p>This is where the conversation becomes bigger than <em>me</em>.</p>
<p>Our Christianity affects other people.</p>
<p>The way I treat my brother affects his understanding of the church.</p>
<p>The way I treat my sister affects whether she experiences Christianity as a place of grace or judgment.</p>
<p>The way young people are treated affects whether they see the church as a spiritual home or merely an institution they are expected to attend.</p>
<p>The hypocrisy we tolerate today can become the cynicism someone carries tomorrow.</p>
<p>That means doing better is not merely about securing <strong>my own salvation</strong>.</p>
<p>It is also about refusing to become an obstacle in someone else's walk with God.</p>
<p>Paul puts it powerfully:</p>
<blockquote>
  <p>“Let no man seek his own, but every man another's wealth.”</p>
</blockquote>
<p>Our faith is communal.</p>
<p>We are members of one body.</p>
<p>So perhaps the question isn't simply:</p>
<p><em>“Am I right with God?”</em></p>
<p>Perhaps we should also ask:</p>
<p><strong>“What kind of Christian am I helping my brother or sister become?”</strong></p>
<h3>We Need to Do Better</h3>
<p>This is not a call to abandon church.</p>
<p>It is a call to <strong>be the church God intended.</strong></p>
<p>It is not a call to stop keeping Sabbath.</p>
<p>It is a call to <strong>rediscover why God gave us Sabbath.</strong></p>
<p>It is not a call to throw away standards.</p>
<p>It is a call to make sure our standards are producing <strong>Christlike character rather than religious pride.</strong></p>
<p>It is not a call to point fingers at hypocrites.</p>
<p>It is a call to look in the mirror.</p>
<p>Because perhaps the greatest revival we could experience is not getting more people into church.</p>
<p>Perhaps it is getting the people already in church to <strong>fall in love with Christ again.</strong></p>
<ul>
  <li>To pray differently.</li>
  <li>To worship genuinely.</li>
  <li>To serve intentionally.</li>
  <li>To forgive quickly.</li>
  <li>To correct lovingly.</li>
  <li>To stop performing Christianity and start living it.</li>
  <li>To stop asking, <em>“What will people think?”</em> and start asking, <em>“What does the Lord require of me?”</em></li>
</ul>
<h3>Not Church, Not Christ</h3>
<p><strong>My Submission to You:</strong></p>
<p><em>Let our Christianity point them toward Christ—not merely toward church.</em></p>
<p>Because if people encounter our religion but never encounter our Saviour, we have missed the point.</p>
<p><strong>Romans 8:5–6</strong></p>
<p><strong>SDAH 530 — It Is Well With My Soul</strong></p>
<p>God Bless.</p>
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
    
    echo "<h2 style='color:green; font-family:sans-serif;'>✅ Blog Post 'Christian, Yet Not Christlike' has been successfully published!</h2>";
    echo "<p style='font-family:sans-serif;'>You can now delete this add_blog_now.php file.</p>";
} catch (Exception $e) {
    echo "<h2 style='color:red;'>❌ Failed to publish: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
