<?php
require_once 'auth.php';
check_auth();

require_once '../includes/db.php';

try {
    $blog_count = $pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
    $contact_count = $pdo->query("SELECT COUNT(*) FROM submissions WHERE type = 'contact' AND status = 'unread'")->fetchColumn();
    $prayer_count = $pdo->query("SELECT COUNT(*) FROM submissions WHERE type = 'prayer' AND status = 'unread'")->fetchColumn();
    $pledge_count = $pdo->query("SELECT COUNT(*) FROM submissions WHERE type = 'pledge' AND status = 'unread'")->fetchColumn();
    $member_reg_count = $pdo->query("SELECT COUNT(*) FROM submissions WHERE type = 'member_registration' AND status = 'unread'")->fetchColumn();
    
    $stmt = $pdo->query("SELECT * FROM submissions ORDER BY created_at DESC LIMIT 5");
    $recent_submissions = $stmt->fetchAll();

    $total_views = $pdo->query("SELECT SUM(views) FROM analytics")->fetchColumn() ?: 0;
    $total_clicks = $pdo->query("SELECT SUM(clicks) FROM analytics")->fetchColumn() ?: 0;
    $total_time = $pdo->query("SELECT SUM(time_spent) FROM analytics")->fetchColumn() ?: 0;

    $analytics_pages = $pdo->query("SELECT * FROM analytics ORDER BY views DESC")->fetchAll();
} catch (PDOException $e) {
    $blog_count = 0;
    $contact_count = 0;
    $prayer_count = 0;
    $pledge_count = 0;
    $member_reg_count = 0;
    $recent_submissions = [];
    $total_views = 0;
    $total_clicks = 0;
    $total_time = 0;
    $analytics_pages = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Membley SDA Admin</title>
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
                <li><a href="dashboard.php" class="sidebar-link active"><i class="fa-solid fa-gauge" style="margin-right: 0.5rem;"></i> Dashboard</a></li>
                <li><a href="rsvps.php" class="sidebar-link"><i class="fa-solid fa-calendar-check" style="margin-right: 0.5rem;"></i> Event RSVPs</a></li>
                <li><a href="members.php" class="sidebar-link"><i class="fa-solid fa-users" style="margin-right: 0.5rem;"></i> Members</a></li>
                <li><a href="forms.php" class="sidebar-link"><i class="fa-solid fa-wpforms" style="margin-right: 0.5rem;"></i> Manage Forms</a></li>
                <li><a href="analytics.php" class="sidebar-link"><i class="fa-solid fa-chart-line" style="margin-right: 0.5rem;"></i> Visitor Analytics</a></li>
                <li><a href="blogs.php" class="sidebar-link"><i class="fa-solid fa-newspaper" style="margin-right: 0.5rem;"></i> Manage Blogs</a></li>
                <li><a href="submissions.php" class="sidebar-link"><i class="fa-solid fa-envelope-open-text" style="margin-right: 0.5rem;"></i> Submissions</a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="logout.php" class="sidebar-link" style="color: #ff8b8b;"><i class="fa-solid fa-right-from-bracket" style="margin-right: 0.5rem;"></i> Sign Out</a>
            </div>
        </aside>

                <main class="admin-main">
            <header class="admin-header">
                <div class="admin-title">Dashboard Overview</div>
                <div class="admin-user">
                    Welcome, <span style="color: var(--primary-light);"><?php echo htmlspecialchars(get_logged_in_user()); ?></span>
                </div>
            </header>

            <div class="admin-content">
                                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Total Blog Posts</div>
                        <div class="stat-val"><?php echo $blog_count; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">New Messages</div>
                        <div class="stat-val" style="color: #0369a1;"><?php echo $contact_count; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Prayer Requests</div>
                        <div class="stat-val" style="color: #9d174d;"><?php echo $prayer_count; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Pledges Received</div>
                        <div class="stat-val" style="color: #1e40af;"><?php echo $pledge_count; ?></div>
                    </div>
                    <div class="stat-card" style="border-top: 4px solid var(--primary-light);">
                        <div class="stat-label">New Member Regs</div>
                        <div class="stat-val" style="color: var(--primary-light);"><?php echo $member_reg_count; ?></div>
                    </div>
                </div>

                                <div class="stats-grid" style="margin-top: 1.5rem;">
                    <div class="stat-card" style="border-top: 4px solid var(--accent);">
                        <div class="stat-label">Total Site Views</div>
                        <div class="stat-val"><?php echo number_format($total_views); ?></div>
                    </div>
                    <div class="stat-card" style="border-top: 4px solid var(--primary-light);">
                        <div class="stat-label">Click Interactions</div>
                        <div class="stat-val"><?php echo number_format($total_clicks); ?></div>
                    </div>
                    <div class="stat-card" style="border-top: 4px solid var(--primary);">
                        <div class="stat-label">Total Time Spent</div>
                        <div class="stat-val">
                            <?php 
                                if ($total_time >= 3600) {
                                    echo round($total_time / 3600, 1) . ' hrs';
                                } elseif ($total_time >= 60) {
                                    echo round($total_time / 60, 1) . ' mins';
                                } else {
                                    echo $total_time . ' secs';
                                }
                            ?>
                        </div>
                    </div>
                </div>

                                <div class="card-table-wrap">
                    <div class="card-table-header">
                        <span class="table-title">Recent Submissions</span>
                        <a href="submissions.php" class="admin-btn-outline" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; margin: 0;">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Contact Details</th>
                                    <th>Subject / Pledge Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_submissions)): ?>
                                    <?php foreach ($recent_submissions as $sub): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y H:i', strtotime($sub['created_at'])); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo htmlspecialchars($sub['type']); ?>">
                                                    <?php echo ucfirst(htmlspecialchars($sub['type'])); ?>
                                                </span>
                                            </td>
                                            <td style="font-weight: 600;"><?php echo htmlspecialchars($sub['name']); ?></td>
                                            <td>
                                                <div style="font-size: 0.85rem; color: #555;"><?php echo htmlspecialchars($sub['email']); ?></div>
                                                <div style="font-size: 0.8rem; color: #888;"><?php echo htmlspecialchars($sub['phone']); ?></div>
                                            </td>
                                            <td>
                                                <?php if ($sub['type'] == 'pledge'): ?>
                                                    <strong>KES <?php echo number_format($sub['amount'], 2); ?></strong>
                                                <?php else: ?>
                                                    <span style="font-size: 0.9rem; color: #475569; display: inline-block; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                        <?php echo htmlspecialchars($sub['subject_message']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo htmlspecialchars($sub['status']); ?>">
                                                    <?php echo ucfirst(htmlspecialchars($sub['status'])); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #637381; padding: 2rem;">No submissions yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                                <div class="card-table-wrap" style="margin-top: 2rem;">
                    <div class="card-table-header">
                        <span class="table-title">Page-level Visitor Analytics</span>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Page Route</th>
                                    <th>Views</th>
                                    <th>Click Interactions</th>
                                    <th>Total Time Spent</th>
                                    <th>Avg Time Spent / View</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($analytics_pages)): ?>
                                    <?php foreach ($analytics_pages as $page): ?>
                                        <tr>
                                            <td style="font-family: monospace; font-weight: 700; color: var(--primary-light);">
                                                <?php echo htmlspecialchars($page['page']); ?>
                                            </td>
                                            <td><strong><?php echo number_format($page['views']); ?></strong></td>
                                            <td><?php echo number_format($page['clicks']); ?></td>
                                            <td>
                                                <?php 
                                                    if ($page['time_spent'] >= 60) {
                                                        echo round($page['time_spent'] / 60, 1) . ' mins';
                                                    } else {
                                                        echo $page['time_spent'] . ' secs';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $avg = $page['views'] > 0 ? round($page['time_spent'] / $page['views']) : 0;
                                                    echo $avg . ' secs';
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: #637381; padding: 2rem;">No traffic data logged yet. Open public pages to seed views.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
