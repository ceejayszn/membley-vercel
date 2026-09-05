<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$past_events = [];
try {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
    $all_events = $stmt->fetchAll();
    $today = date('Y-m-d');
    foreach ($all_events as $ev) {
        $expiry_date = date('Y-m-d', strtotime($ev['event_date'] . ' +2 days'));
        if ($today > $expiry_date) {
            $past_events[] = $ev;
        }
    }
} catch (PDOException $e) {
}
?>

<section style="background-color: var(--primary-dark); color: white; padding: 4rem 0; text-align: center; background-image: linear-gradient(rgba(4,25,40,0.85), rgba(4,25,40,0.85)), url('https://images.unsplash.com/photo-1544427920-c49ccfb85579?auto=format&fit=crop&q=80&w=1200'); background-size: cover; background-position: center;">
    <div class="container">
        <span style="color: var(--accent); font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;"><i class="fa-solid fa-box-archive"></i> Church Event Archives</span>
        <h1 style="color: white; font-size: 2.75rem; margin-bottom: 0.5rem;">Past Events & Convocations</h1>
        <p style="color: rgba(255,255,255,0.8); font-size: 1.05rem; max-width: 650px; margin: 0 auto;">Events automatically move to this archive 2 days after their occurrence to keep our community updated on past milestones and gatherings.</p>
    </div>
</section>

<div style="background-color: var(--bg-white); border-bottom: 1px solid var(--border-color); padding: 1rem 0;">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="events.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Upcoming Events</a>
        </div>
        <div style="font-size: 0.9rem; color: var(--text-muted);">
            Showing archived events older than 2 days
        </div>
    </div>
</div>

<section class="section-padding container">
    <?php if (!empty($past_events)): ?>
        <div style="display: flex; flex-direction: column; gap: 1.75rem;">
            <?php foreach ($past_events as $event): ?>
                <?php 
                    $formatted_day = date('d', strtotime($event['event_date']));
                    $formatted_month = date('M Y', strtotime($event['event_date']));
                ?>
                <div class="past-event-card">
                    <div style="background-color: #475569; color: white; padding: 1rem; border-radius: 8px; text-align: center; min-width: 95px;">
                        <span style="font-size: 1.6rem; font-weight: 800; display: block; line-height: 1; color: var(--accent);"><?php echo $formatted_day; ?></span>
                        <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase;"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                    </div>
                    
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                            <span class="past-badge"><i class="fa-solid fa-check"></i> Concluded</span>
                            <span style="background-color: rgba(8,43,67,0.1); color: var(--primary); font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 4px; display: inline-block;">
                                <?php echo htmlspecialchars($event['category']); ?>
                            </span>
                        </div>

                        <h3 style="color: var(--primary); margin-bottom: 0.4rem; font-size: 1.35rem;"><?php echo htmlspecialchars($event['title']); ?></h3>
                        <?php if (!empty($event['subtitle'])): ?>
                            <h4 style="color: var(--accent); margin-bottom: 0.5rem; font-size: 1rem; font-weight: 600;"><?php echo htmlspecialchars($event['subtitle']); ?></h4>
                        <?php endif; ?>

                        <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.3rem;"><i class="fa-solid fa-clock"></i> <?php echo htmlspecialchars($event['event_time']); ?></p>
                        <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.75rem;"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                        
                        <p style="font-size: 0.95rem; color: var(--text-dark); line-height: 1.5;">
                            <?php echo htmlspecialchars($event['description']); ?>
                        </p>
                    </div>

                    <?php if (!empty($event['image_url'])): ?>
                        <div style="width: 140px; height: 120px; flex-shrink: 0; border-radius: 8px; background-image: url('<?php echo htmlspecialchars($event['image_url']); ?>'); background-size: cover; background-position: center; border: 1px solid var(--border-color);">
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 3.5rem 1.5rem; background: var(--bg-white); border-radius: 12px; border: 1px solid var(--border-color); max-width: 600px; margin: 0 auto;">
            <i class="fa-solid fa-box-open" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h3 style="color: var(--primary); margin-bottom: 0.5rem;">No Past Events Found</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">There are no archived events older than 2 days in the database yet.</p>
            <a href="events.php" class="btn btn-primary">View Upcoming Events</a>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
