<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$latest_blogs = [];
try {
    $stmt = $pdo->query("SELECT * FROM blogs WHERE status = 'published' ORDER BY created_at DESC LIMIT 1");
    $latest_blogs = $stmt->fetchAll();
} catch (PDOException $e) {
}

$upcoming_events = [];
try {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
    $all_events = $stmt->fetchAll();
    $today = date('Y-m-d');
    foreach ($all_events as $ev) {
        $expiry_date = date('Y-m-d', strtotime($ev['event_date'] . ' +2 days'));
        if ($today <= $expiry_date) {
            $upcoming_events[] = $ev;
        }
    }
} catch (PDOException $e) {
}
?>

<section class="hero">
    <div class="hero-slider">
                <div class="slide active" style="background-image: linear-gradient(rgba(0,47,93,0.35), rgba(0,47,93,0.35)), url('assets/images/church_banner.png'); background-size: cover; background-position: center;">
            <div class="container">
                <div class="hero-content">
                    <span class="section-subtitle" style="color: var(--accent); font-weight: 800;">Welcome to Membley SDA Church</span>
                    <h1 class="hero-title">A Sanctuary of <span>Hope</span>, Faith & Love</h1>
                    <p class="hero-desc">We invite you to worship with us this Sabbath and experience the transformative power of God's grace in Ruiru.</p>
                    <div class="hero-actions">
                        <a href="about.php" class="btn btn-primary">Learn More About Us</a>
                        <a href="giving.php" class="btn btn-accent"><i class="fa-solid fa-heart"></i> Give Online</a>
                    </div>
                </div>
            </div>
        </div>
                <div class="slide" style="background-image: linear-gradient(rgba(0,47,93,0.5), rgba(0,47,93,0.5)), url('assets/images/adventurer_banner.jpg'); background-size: cover; background-position: center;">
            <div class="container">
                <div class="hero-content">
                    <span class="section-subtitle" style="color: var(--accent); font-weight: 800;">Nurturing the Next Generation</span>
                    <h1 class="hero-title">Youth, Pathfinders & <span>Adventurers</span></h1>
                    <p class="hero-desc">Discover vibrant programs designed to guide our children and youth into a lifelong relationship with Jesus.</p>
                    <div class="hero-actions">
                        <a href="ministries.php" class="btn btn-primary">Our Departments</a>
                        <a href="contact.php" class="btn btn-outline" style="color: white; border-color: white;">Get in Touch</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section style="position: relative; z-index: 20;">
    <div class="container">
        <div class="services-banner">
            <h2 style="color: white; font-size: 1.6rem; margin-bottom: 1.5rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 1rem;">
                <i class="fa-regular fa-clock"></i> Weekly Worship Services
            </h2>
            <div class="services-grid">
                <div class="service-item">
                    <div class="service-time-title">Sabbath School</div>
                    <div class="service-time-hours">Saturdays | 9:00 AM - 10:40 AM</div>
                    <p style="font-size: 0.85rem; color: rgba(255,255,255,0.7); margin-top: 0.5rem;">Interactive study of the quarterly Bible Lesson in classes.</p>
                </div>
                <div class="service-item">
                    <div class="service-time-title">Divine Service</div>
                    <div class="service-time-hours">Saturdays | 10:50 AM - 12:30 PM</div>
                    <p style="font-size: 0.85rem; color: rgba(255,255,255,0.7); margin-top: 0.5rem;">Praise, prayer, and sermon delivery by the Pastor or Elders.</p>
                </div>
                <div class="service-item">
                    <div class="service-time-title">Adventist Youth (AY)</div>
                    <div class="service-time-hours">Saturdays | 4:00 PM - 5:30 PM</div>
                    <p style="font-size: 0.85rem; color: rgba(255,255,255,0.7); margin-top: 0.5rem;">Vibrant sessions including youth discussions, singing, and quizzes.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding container" id="events-showcase">
    <div class="section-header" style="margin-bottom: 2.5rem;">
        <span class="section-subtitle"><i class="fa-solid fa-calendar-star"></i> Upcoming Events & Convocations</span>
        <h2 class="section-title">What's Happening at Membley SDA</h2>
    </div>

    <?php if (!empty($upcoming_events)): ?>
        <?php foreach ($upcoming_events as $index => $event): ?>
            <?php 
                $is_new = ($event['is_featured'] == 1 || $index == 0);
                $formatted_day = date('d', strtotime($event['event_date']));
                $formatted_month = date('M Y', strtotime($event['event_date']));
            ?>
            <?php if ($is_new): ?>
                                <div class="event-card-green-outline" style="margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                        <span class="badge-green-outline">
                            <i class="fa-solid fa-sparkles"></i> NEW UPCOMING EVENT
                        </span>
                    </div>

                    <div class="event-flyer-box">
                                                <div class="flyer-main-details">
                            <span class="flyer-presenter"><i class="fa-solid fa-church"></i> <?php echo htmlspecialchars(!empty($event['subtitle']) ? 'MEMBLEY ADVENTIST PRESENTS' : 'MEMBLEY SDA CHURCH'); ?></span>
                            <h3 class="flyer-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <?php if (!empty($event['subtitle'])): ?>
                                <div class="flyer-subtitle">
                                    <span class="ribbon-10yrs"><i class="fa-solid fa-ribbon"></i> CELEBRATING 10 YRS</span>
                                    <span>OF FELLOWSHIP AND FAMILY</span>
                                </div>
                            <?php endif; ?>
                            <p style="color: rgba(255,255,255,0.85); font-size: 1rem; margin-top: 0.5rem;">
                                <?php echo htmlspecialchars($event['description']); ?>
                            </p>

                            <div class="flyer-meta-grid">
                                <div class="flyer-meta-card">
                                    <div class="flyer-meta-icon"><i class="fa-solid fa-calendar-day"></i></div>
                                    <div class="flyer-meta-text">
                                        <small>Date</small>
                                        <strong><?php echo date('d M Y (l)', strtotime($event['event_date'])); ?></strong>
                                    </div>
                                </div>
                                <div class="flyer-meta-card">
                                    <div class="flyer-meta-icon"><i class="fa-solid fa-clock"></i></div>
                                    <div class="flyer-meta-text">
                                        <small>Start Time</small>
                                        <strong><?php echo htmlspecialchars($event['event_time']); ?></strong>
                                    </div>
                                </div>
                                <div class="flyer-meta-card" style="grid-column: 1 / -1;">
                                    <div class="flyer-meta-icon"><i class="fa-solid fa-location-dot"></i></div>
                                    <div class="flyer-meta-text">
                                        <small>Venue Location</small>
                                        <strong><?php echo htmlspecialchars($event['location']); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                                                <div class="flyer-poster-wrapper">
                            <?php if (!empty($event['image_url']) && file_exists(__DIR__ . '/' . $event['image_url'])): ?>
                                <a href="events.php" title="Click to view event details and RSVP" style="display: block; width: 100%; max-width: 440px; text-decoration: none;">
                                    <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="flyer-poster-img">
                                </a>
                            <?php else: ?>
                                <a href="events.php" style="text-decoration: none; display: block; width: 100%;">
                                    <div style="padding: 1.5rem; background: #030e18; border-radius: 12px; border: 2px solid #84cc16; text-align: center;">
                                        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #38bdf8; letter-spacing: 1px; margin-bottom: 0.5rem;">MEMBLEY ADVENTIST</div>
                                        <div class="flyer-date-pill">
                                            <span class="flyer-date-big"><?php echo $formatted_day; ?></span>
                                            <span class="flyer-date-month"><?php echo $formatted_month; ?></span>
                                        </div>
                                        <div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 0.6rem; color: #ffffff; font-size: 0.85rem; font-weight: 700; margin-bottom: 1rem;">
                                            <i class="fa-solid fa-play"></i> START AT <?php echo htmlspecialchars($event['event_time']); ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                                        <div class="event-rsvp-cta">
                        <div>
                            <div class="event-rsvp-title">
                                <i class="fa-solid fa-circle-question" style="color: var(--accent); font-size: 1.3rem;"></i>
                                <span>Will you be attending Homecoming Sabbath?</span>
                            </div>
                            <p style="color: rgba(255,255,255,0.75); font-size: 0.9rem; margin-top: 0.25rem;">
                                Let us know you're coming! Quick RSVP with your name and church.
                            </p>
                        </div>
                        <div style="display: flex; gap: 0.85rem; flex-wrap: wrap; align-items: center;">
                            <a href="rsvp.php" class="btn-fill-info">
                                <i class="fa-solid fa-pen-to-square"></i> Fill Info & RSVP
                            </a>
                            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode("Join us for the Membley SDA Homecoming Sabbath (Celebrating 10 Yrs of Fellowship & Family) on Oct 31, 2026! Will you be attending? Confirm your attendance here: https://" . ($_SERVER['HTTP_HOST'] ?? 'membleyadventist.org') . "/rsvp.php"); ?>" target="_blank" class="btn-whatsapp-share">
                                <i class="fa-brands fa-whatsapp" style="font-size: 1.25rem;"></i> Share on WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                                <div class="event-list-card" style="margin-bottom: 1.5rem;">
                    <div style="background-color: var(--primary-dark); color: white; padding: 1rem; border-radius: 8px; text-align: center; min-width: 90px;">
                        <span style="font-size: 1.6rem; font-weight: 800; display: block; line-height: 1; color: var(--accent);"><?php echo $formatted_day; ?></span>
                        <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase;"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                    </div>
                    <div style="flex: 1;">
                        <span style="background-color: rgba(16,185,129,0.12); color: #059669; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 4px; display: inline-block; margin-bottom: 0.4rem;"><?php echo htmlspecialchars($event['category']); ?></span>
                        <h3 style="color: var(--primary); margin-bottom: 0.4rem; font-size: 1.25rem;"><?php echo htmlspecialchars($event['title']); ?></h3>
                        <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.3rem;"><i class="fa-solid fa-clock"></i> <?php echo htmlspecialchars($event['event_time']); ?></p>
                        <p style="font-size: 0.9rem; color: var(--text-muted);"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 2rem; background: var(--bg-white); border-radius: 12px; border: 1px solid var(--border-color);">
            <p style="color: var(--text-muted);">No upcoming events scheduled right now. Check back soon!</p>
        </div>
    <?php endif; ?>
</section>

<section class="section-padding container">
    <div class="responsive-hero-grid">
        <div>
            <span class="section-subtitle">Welcome message</span>
            <h2 style="font-size: 2.2rem; margin-bottom: 1.5rem; color: var(--primary);">A Warm Welcome From Our Pastor</h2>
            <p style="margin-bottom: 1.25rem; color: var(--text-muted);">
                Welcome to the Membley Seventh-day Adventist Church community portal. It is our joy to receive you here. We believe that God has a special purpose for everyone, and our mission is to assist one another on this journey towards the Heavenly Canaan.
            </p>
            <p style="margin-bottom: 2rem; color: var(--text-muted);">
                Whether you are a resident in Ruiru seeking a regular place of worship, a visitor seeking truth, or just looking to participate in fellowship, we invite you to join us this Sabbath. May the Lord bless and keep you.
            </p>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background-color: #cbd5e1; background-image: url('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=150'); background-size: cover;"></div>
                <div>
                    <h4 style="color: var(--primary); margin: 0;">Pr. Joseph Mwaniki</h4>
                    <small style="color: var(--text-muted); font-weight: 600;">District Pastor, Membley SDA</small>
                </div>
            </div>
        </div>
        <div style="position: relative;">
                        <div style="height: 380px; border-radius: 12px; background-image: linear-gradient(rgba(8,43,67,0.1), rgba(8,43,67,0.1)), url('https://images.unsplash.com/photo-1548625361-155deee223d2?auto=format&fit=crop&q=80&w=600'); background-size: cover; background-position: center; box-shadow: var(--shadow-lg);"></div>
            <div style="position: absolute; bottom: -20px; left: -20px; background-color: var(--accent); color: var(--primary-dark); padding: 1.5rem; border-radius: 8px; box-shadow: var(--shadow-md); font-weight: 700;">
                <span style="font-size: 2rem; display: block; line-height: 1;">10+</span>
                <span style="font-size: 0.85rem; font-weight: 600;">Active Ministries</span>
            </div>
        </div>
    </div>
</section>

<section style="background-color: var(--bg-white);" class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Ministries & Departments</span>
            <h2 class="section-title">Nurturing All Age Groups</h2>
        </div>
        <div class="grid-3">
                        <div class="card">
                <div class="card-img" style="background-image: url('assets/images/adventurer_logo.png'); background-size: contain; background-repeat: no-repeat; background-color: var(--bg-light); background-position: center;">
                    <span class="card-tag">Children</span>
                </div>
                <div class="card-body">
                    <h3 class="card-title">Adventurous Club</h3>
                    <p class="card-desc">Catering to beginners, primary, and junior groups with exciting activities, songs, and Bible lessons to build a firm spiritual foundation.</p>
                    <a href="ministries.php?tab=children" class="btn btn-outline btn-sm">Learn More</a>
                </div>
            </div>
            
                        <div class="card">
                <div class="card-img" style="background-image: url('assets/images/pathfinder_logo.png'); background-size: contain; background-repeat: no-repeat; background-color: var(--bg-light);">
                    <span class="card-tag">Club</span>
                </div>
                <div class="card-body">
                    <h3 class="card-title">Pathfinders</h3>
                    <p class="card-desc">Instilling Christian values, life skills, camping, and outdoor exploration in children aged 6 to 15 years through the worldwide club movement.</p>
                    <a href="ministries.php?tab=clubs" class="btn btn-outline btn-sm">Learn More</a>
                </div>
            </div>

                        <div class="card">
                <div class="card-img" style="background-image: url('https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&q=80&w=400');">
                    <span class="card-tag">Youth</span>
                </div>
                <div class="card-body">
                    <h3 class="card-title">Adventist Youth (AY)</h3>
                    <p class="card-desc">Providing a vibrant space for ambassadors, young adults, and teens to share fellowship, discuss contemporary issues, and conduct evangelism.</p>
                    <a href="ministries.php?tab=youth" class="btn btn-outline btn-sm">Learn More</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding container">
    <div class="section-header">
        <span class="section-subtitle">News & Sermons</span>
        <h2 class="section-title">Latest Updates & Sermons</h2>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
        
                <div>
            <h3 style="margin-bottom: 1rem; color: var(--primary);">Recent Post</h3>
            <?php if (!empty($latest_blogs)): ?>
                <?php foreach ($latest_blogs as $post): ?>
                    <div class="card">
                        <div class="card-img" style="background-image: url('<?php echo !empty($post['image_url']) ? htmlspecialchars($post['image_url']) : 'https://images.unsplash.com/photo-1490730141103-6cac27aaab94?auto=format&fit=crop&q=80&w=400'; ?>');">
                            <span class="card-tag"><?php echo htmlspecialchars($post['category']); ?></span>
                        </div>
                        <div class="card-body">
                            <span class="card-date"><i class="fa-regular fa-calendar"></i> <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                            <h3 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p class="card-desc"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                            <a href="blog-single.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="btn btn-outline btn-sm">Read Post</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-muted);">No blog posts found. Check back soon!</p>
            <?php endif; ?>
        </div>

                <div>
            <h3 style="margin-bottom: 1rem; color: var(--primary);">Camp Meeting Update</h3>
            <div class="card" style="border: 2px solid var(--accent);">
                                <div class="card-img" style="background-image: url(''); background-color: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">[Camp Meeting Photo Placeholder]</span>
                    <span class="card-tag" style="background-color: var(--accent); color: var(--primary-dark);">Special Update</span>
                </div>
                <div class="card-body">
                    <span class="card-date"><i class="fa-solid fa-bullhorn"></i> Important Announcement</span>
                    <h3 class="card-title">Annual Camp Meeting 2026</h3>
                    <p class="card-desc">We are excited to share the latest updates on our upcoming annual camp meeting. Stay tuned for further details regarding speakers, schedule, and preparations.</p>
                    <a href="events.php" class="btn btn-accent btn-sm">View in Events</a>
                </div>
            </div>
        </div>

    </div>
    
    <div style="text-align: center; margin-top: 3rem;">
        <a href="blog.php" class="btn btn-primary">View All Blog Posts</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
