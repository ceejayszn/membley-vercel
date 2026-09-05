<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

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

<section style="background-color: var(--primary-dark); color: white; padding: 3.5rem 0; text-align: center; background-image: linear-gradient(rgba(4,25,40,0.85), rgba(4,25,40,0.85)), url('https://images.unsplash.com/photo-1544427920-c49ccfb85579?auto=format&fit=crop&q=80&w=1200'); background-size: cover; background-position: center;">
    <div class="container">
        <h1 style="color: white; font-size: 2.5rem; margin-bottom: 0.5rem;">Events & Announcements</h1>
        <p style="color: var(--accent); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-size: 0.95rem;">Join us in our upcoming activities & convocations</p>
    </div>
</section>

<section class="section-padding container" style="max-width: 1000px; margin: 0 auto;">
    
    <?php if (!empty($upcoming_events)): ?>
        <div style="display: flex; flex-direction: column; gap: 2.5rem;">
            <?php foreach ($upcoming_events as $index => $event): ?>
                <?php 
                    $is_featured = ($event['is_featured'] == 1);
                    $formatted_day = date('d', strtotime($event['event_date']));
                    $formatted_month = date('M', strtotime($event['event_date']));
                ?>
                <?php if ($is_featured): ?>
                                        <div class="event-card-green-outline">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
                            <span class="badge-green-outline"><i class="fa-solid fa-sparkles"></i> Featured Upcoming Event</span>
                            <span style="color: #a3e635; font-weight: 700; font-size: 0.85rem;"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                        </div>

                        <div class="event-flyer-box">
                                                        <div class="flyer-main-details">
                                <span class="flyer-presenter"><?php echo htmlspecialchars(!empty($event['subtitle']) ? 'MEMBLEY ADVENTIST PRESENTS' : 'MEMBLEY SDA CHURCH'); ?></span>
                                <h2 class="flyer-title"><?php echo htmlspecialchars($event['title']); ?></h2>
                                
                                <?php if (!empty($event['subtitle'])): ?>
                                    <div class="flyer-subtitle">
                                        <span class="ribbon-10yrs">CELEBRATING 10 YRS</span>
                                        <span>OF FELLOWSHIP AND FAMILY</span>
                                    </div>
                                <?php endif; ?>
                                
                                <p style="color: rgba(255,255,255,0.88); font-size: 1rem; line-height: 1.6; margin-top: 0.25rem;">
                                    <?php echo htmlspecialchars($event['description']); ?>
                                </p>

                                <div class="flyer-meta-grid">
                                    <div class="flyer-meta-card">
                                        <div class="flyer-meta-icon"><i class="fa-solid fa-calendar-day" style="color: #84cc16;"></i></div>
                                        <div class="flyer-meta-text">
                                            <small>Event Date</small>
                                            <strong><?php echo date('d M Y (l)', strtotime($event['event_date'])); ?></strong>
                                        </div>
                                    </div>
                                    <div class="flyer-meta-card">
                                        <div class="flyer-meta-icon"><i class="fa-solid fa-clock" style="color: #84cc16;"></i></div>
                                        <div class="flyer-meta-text">
                                            <small>Start Time</small>
                                            <strong><?php echo htmlspecialchars($event['event_time']); ?></strong>
                                        </div>
                                    </div>
                                    <div class="flyer-meta-card" style="grid-column: 1 / -1;">
                                        <div class="flyer-meta-icon"><i class="fa-solid fa-location-dot" style="color: #84cc16;"></i></div>
                                        <div class="flyer-meta-text">
                                            <small>Venue Location</small>
                                            <strong><?php echo htmlspecialchars($event['location']); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                                        <div class="flyer-poster-wrapper">
                                <?php if (!empty($event['image_url']) && file_exists(__DIR__ . '/' . $event['image_url'])): ?>
                                    <a href="rsvp.php" title="Click to RSVP & Confirm Attendance" style="display: block; width: 100%; max-width: 440px; text-decoration: none;">
                                        <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="flyer-poster-img">
                                    </a>
                                <?php else: ?>
                                    <a href="rsvp.php" style="text-decoration: none; display: block; width: 100%;">
                                        <div style="padding: 1.5rem; background: #030e18; border-radius: 12px; border: 2px solid #84cc16; text-align: center;">
                                            <div class="flyer-date-pill">
                                                <span class="flyer-date-big"><?php echo $formatted_day; ?></span>
                                                <span class="flyer-date-month"><?php echo date('M Y', strtotime($event['event_date'])); ?></span>
                                            </div>
                                            <div style="font-size: 0.85rem; color: white; font-weight: 700;">
                                                <i class="fa-solid fa-location-dot" style="color: #84cc16;"></i> <?php echo htmlspecialchars($event['location']); ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                                                <div class="event-rsvp-cta" style="margin-top: 2rem;">
                            <div>
                                <div class="event-rsvp-title">
                                    <i class="fa-solid fa-circle-check" style="color: var(--accent); font-size: 1.3rem;"></i>
                                    <span>Reserve Your Seat for Homecoming Sabbath</span>
                                </div>
                                <p style="color: rgba(255,255,255,0.8); font-size: 0.9rem; margin-top: 0.25rem;">
                                    Confirm your attendance in seconds so we can welcome you and your family!
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

                                        <div class="event-list-card">
                        <div style="background-color: var(--primary-dark); color: white; padding: 1.25rem; border-radius: 10px; text-align: center; min-width: 100px;">
                            <span style="font-size: 1.9rem; font-weight: 800; display: block; line-height: 1; color: var(--accent);"><?php echo $formatted_day; ?></span>
                            <span style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase;"><?php echo $formatted_month; ?></span>
                        </div>
                        <div style="flex: 1;">
                            <span style="background-color: rgba(242,169,0,0.15); color: #a67300; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 4px; display: inline-block; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($event['category']); ?></span>
                            <h3 style="color: var(--primary); margin-bottom: 0.5rem; font-size: 1.4rem;"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 0.4rem;"><i class="fa-solid fa-clock"></i> <?php echo htmlspecialchars($event['event_time']); ?></p>
                            <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 0.5rem;"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                            <p style="font-size: 0.95rem; color: var(--text-dark); line-height: 1.5;"><?php echo htmlspecialchars($event['description']); ?></p>
                        </div>
                        <?php if (!empty($event['image_url'])): ?>
                            <div style="width: 140px; height: 130px; flex-shrink: 0; border-radius: 8px; background-image: url('<?php echo htmlspecialchars($event['image_url']); ?>'); background-size: cover; background-position: center; border: 1px solid var(--border-color);">
                            </div>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 3rem; background: var(--bg-white); border-radius: 12px; border: 1px solid var(--border-color);">
            <p style="color: var(--text-muted);">No upcoming events scheduled right now. Please check back soon!</p>
        </div>
    <?php endif; ?>

</section>

<?php require_once 'includes/footer.php'; ?>
