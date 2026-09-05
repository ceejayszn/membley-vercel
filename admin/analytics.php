<?php
require_once 'auth.php';
check_auth();

require_once '../includes/db.php';

try {
    $total_visits = $pdo->query("SELECT COUNT(*) FROM visitor_tracking")->fetchColumn();
    $total_seconds = $pdo->query("SELECT SUM(time_spent) FROM visitor_tracking")->fetchColumn() ?: 0;
    
    $avg_time = $total_visits > 0 ? round($total_seconds / $total_visits) : 0;
    
    $mobile_count = $pdo->query("SELECT COUNT(*) FROM visitor_tracking WHERE device_type = 'Mobile'")->fetchColumn();
    $tablet_count = $pdo->query("SELECT COUNT(*) FROM visitor_tracking WHERE device_type = 'Tablet'")->fetchColumn();
    $desktop_count = $pdo->query("SELECT COUNT(*) FROM visitor_tracking WHERE device_type = 'Desktop'")->fetchColumn();

    $stmt = $pdo->query("SELECT * FROM visitor_tracking ORDER BY last_seen DESC LIMIT 100");
    $visitors = $stmt->fetchAll();
} catch (PDOException $e) {
    $total_visits = 0;
    $total_seconds = 0;
    $avg_time = 0;
    $mobile_count = 0;
    $tablet_count = 0;
    $desktop_count = 0;
    $visitors = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Analytics - Membley SDA Admin</title>
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
                <li><a href="rsvps.php" class="sidebar-link"><i class="fa-solid fa-calendar-check" style="margin-right: 0.5rem;"></i> Event RSVPs</a></li>
                <li><a href="members.php" class="sidebar-link"><i class="fa-solid fa-users" style="margin-right: 0.5rem;"></i> Members</a></li>
                <li><a href="forms.php" class="sidebar-link"><i class="fa-solid fa-wpforms" style="margin-right: 0.5rem;"></i> Manage Forms</a></li>
                <li><a href="analytics.php" class="sidebar-link active"><i class="fa-solid fa-chart-line" style="margin-right: 0.5rem;"></i> Visitor Analytics</a></li>
                <li><a href="blogs.php" class="sidebar-link"><i class="fa-solid fa-newspaper" style="margin-right: 0.5rem;"></i> Manage Blogs</a></li>
                <li><a href="submissions.php" class="sidebar-link"><i class="fa-solid fa-envelope-open-text" style="margin-right: 0.5rem;"></i> Submissions</a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="logout.php" class="sidebar-link" style="color: #ff8b8b;"><i class="fa-solid fa-right-from-bracket" style="margin-right: 0.5rem;"></i> Sign Out</a>
            </div>
        </aside>

                <main class="admin-main">
            <header class="admin-header">
                <div class="admin-title">Visitor IP & Device Analytics</div>
                <div class="admin-user">
                    Welcome, <span style="color: var(--primary-light);"><?php echo htmlspecialchars(get_logged_in_user()); ?></span>
                </div>
            </header>

            <div class="admin-content">
                                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Total Visits logged</div>
                        <div class="stat-val"><?php echo $total_visits; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Avg. Session Duration</div>
                        <div class="stat-val" style="color: #0369a1;"><?php echo $avg_time; ?>s</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Mobile vs Desktop</div>
                        <div class="stat-val" style="color: #9d174d; font-size: 1.5rem; margin-top: 0.75rem;">
                            <i class="fa-solid fa-mobile-screen"></i> <?php echo $mobile_count; ?> | 
                            <i class="fa-solid fa-laptop"></i> <?php echo $desktop_count; ?>
                        </div>
                    </div>
                </div>

                                <div class="card-table-wrap" style="margin-top: 2rem;">
                    <div class="card-table-header">
                        <span class="table-title">Recent Visitor Devices & Locations</span>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>First Visited</th>
                                    <th>IP Address</th>
                                    <th>Location</th>
                                    <th>ISP / Network Provider</th>
                                    <th>Device / Browser</th>
                                    <th>Time Spent</th>
                                    <th>Last Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($visitors)): ?>
                                    <?php foreach ($visitors as $v): ?>
                                        <tr>
                                            <td style="font-size: 0.85rem; color: #64748b;"><?php echo date('M d, H:i', strtotime($v['created_at'])); ?></td>
                                            <td style="font-family: monospace; font-weight: 700; color: var(--primary);"><?php echo htmlspecialchars($v['ip_address']); ?></td>
                                            <td>
                                                <i class="fa-solid fa-location-dot" style="color: var(--accent); margin-right: 0.25rem;"></i> 
                                                <?php echo htmlspecialchars($v['location']); ?>
                                            </td>
                                            <td style="font-size: 0.85rem; color: #334155;"><?php echo htmlspecialchars($v['network_isp']); ?></td>
                                            <td>
                                                <span class="badge badge-read" style="background-color: #f1f5f9; color: #1e293b;">
                                                    <?php 
                                                        if ($v['device_type'] == 'Mobile') echo '<i class="fa-solid fa-mobile-screen"></i> Mobile';
                                                        elseif ($v['device_type'] == 'Tablet') echo '<i class="fa-solid fa-tablet-screen-button"></i> Tablet';
                                                        else echo '<i class="fa-solid fa-laptop"></i> Desktop';
                                                    ?>
                                                </span>
                                                <span class="badge badge-read" style="background-color: #f1f5f9; color: #1e293b; margin-left: 0.25rem;">
                                                    <?php echo htmlspecialchars($v['browser']); ?>
                                                </span>
                                            </td>
                                            <td><strong><?php echo $v['time_spent']; ?> seconds</strong></td>
                                            <td style="font-size: 0.85rem; color: #64748b;"><?php echo date('H:i:s', strtotime($v['last_seen'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; color: #637381; padding: 2rem;">No visitor session logs recorded.</td>
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
