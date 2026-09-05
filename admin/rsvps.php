<?php
require_once 'auth.php';
check_auth();

require_once '../includes/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($action == 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM event_rsvps WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header('Location: rsvps.php?msg=deleted');
        exit;
    } catch (PDOException $e) {
    }
}

if ($action == 'export') {
    $export_sql = "SELECT created_at, full_name, is_membley_member, church_from, phone, attendees_count, inquiry, phone_model, device_type, os, browser, ip_address, location, network_isp FROM event_rsvps ORDER BY created_at DESC";

    try {
        $stmt = $pdo->prepare($export_sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = "homecoming_rsvps_" . date('Ymd_His') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['Registration Date', 'Full Name', 'Is Membley Member', 'Church From', 'Phone Number', 'Attendees Count', 'Inquiry/Notes', 'Phone Model', 'Device Type', 'Operating System', 'Browser', 'IP Address', 'Location', 'Network/ISP']);
        
        foreach ($rows as $row) {
            $row['is_membley_member'] = ($row['is_membley_member'] == 1) ? 'Yes' : 'No';
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    } catch (PDOException $e) {
        die("Export failed: " . $e->getMessage());
    }
}

$total_rsvps = 0;
$total_attendees = 0;
$members_count = 0;
$visitors_count = 0;

try {
    $stats = $pdo->query("SELECT 
        COUNT(*) as total_records,
        SUM(attendees_count) as total_people,
        SUM(CASE WHEN is_membley_member = 1 THEN 1 ELSE 0 END) as members,
        SUM(CASE WHEN is_membley_member = 0 THEN 1 ELSE 0 END) as visitors
        FROM event_rsvps")->fetch();
    
    $total_rsvps = $stats['total_records'] ?? 0;
    $total_attendees = $stats['total_people'] ?? 0;
    $members_count = $stats['members'] ?? 0;
    $visitors_count = $stats['visitors'] ?? 0;
} catch (PDOException $e) {}

try {
    $stmt = $pdo->query("SELECT * FROM event_rsvps ORDER BY created_at DESC");
    $rsvps = $stmt->fetchAll();
} catch (PDOException $e) {
    $rsvps = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event RSVPs & Attendance - Membley SDA Admin</title>
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
                <li><a href="rsvps.php" class="sidebar-link active"><i class="fa-solid fa-calendar-check" style="margin-right: 0.5rem;"></i> Event RSVPs</a></li>
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
                <div class="admin-title">Homecoming Attendance RSVPs</div>
                <div class="admin-user">
                    Welcome, <span style="color: var(--primary-light);"><?php echo htmlspecialchars(get_logged_in_user()); ?></span>
                </div>
            </header>

            <div class="admin-content">
                
                                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2rem;">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(132, 204, 22, 0.2); color: #65a30d;">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <div class="stat-data">
                            <div class="stat-value"><?php echo number_format($total_rsvps); ?></div>
                            <div class="stat-label">Total Registrations</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(13, 75, 133, 0.15); color: var(--primary-light);">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="stat-data">
                            <div class="stat-value"><?php echo number_format($total_attendees); ?></div>
                            <div class="stat-label">Expected Headcount</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.15); color: #10b981;">
                            <i class="fa-solid fa-church"></i>
                        </div>
                        <div class="stat-data">
                            <div class="stat-value"><?php echo number_format($members_count); ?></div>
                            <div class="stat-label">Membley Members</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                            <i class="fa-solid fa-hand-holding-heart"></i>
                        </div>
                        <div class="stat-data">
                            <div class="stat-value"><?php echo number_format($visitors_count); ?></div>
                            <div class="stat-label">Visitors / Guests</div>
                        </div>
                    </div>
                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h3 style="color: var(--primary); margin: 0; font-size: 1.25rem;">Homecoming Sabbath 10 Yrs Attendance List</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.2rem;">Showing all confirmed RSVPs with tracked device info and phone models.</p>
                    </div>
                    <a href="rsvps.php?action=export" class="admin-btn" style="background-color: #16a34a;"><i class="fa-solid fa-file-excel"></i> Export All to CSV</a>
                </div>

                                <div class="card-table-wrap">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Attendee Name</th>
                                    <th>Member Status / Church</th>
                                    <th>Phone / Inquiry</th>
                                    <th>Tracked Device / Phone Model</th>
                                    <th>IP & Location</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($rsvps)): ?>
                                    <?php foreach ($rsvps as $row): ?>
                                        <tr>
                                            <td style="font-size: 0.85rem; color: #64748b; white-space: nowrap;">
                                                <?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?>
                                            </td>
                                            <td>
                                                <strong style="color: var(--primary); font-size: 0.95rem; display: block;"><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                                <small style="color: #64748b; font-weight: 600;"><i class="fa-solid fa-users"></i> <?php echo intval($row['attendees_count']); ?> Person(s)</small>
                                            </td>
                                            <td>
                                                <?php if ($row['is_membley_member'] == 1): ?>
                                                    <span style="background: rgba(132, 204, 22, 0.2); color: #4d7c0f; font-weight: 700; font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-block; margin-bottom: 0.25rem;">
                                                        <i class="fa-solid fa-check"></i> Membley Member
                                                    </span>
                                                <?php else: ?>
                                                    <span style="background: rgba(59, 130, 246, 0.12); color: #1d4ed8; font-weight: 700; font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-block; margin-bottom: 0.25rem;">
                                                        Visitor / Guest
                                                    </span>
                                                <?php endif; ?>
                                                <div style="font-size: 0.85rem; color: #334155;">
                                                    <i class="fa-solid fa-place-of-worship" style="color: #94a3b8;"></i> <?php echo htmlspecialchars($row['church_from']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="font-weight: 600; font-size: 0.9rem; color: #0f172a;">
                                                    <i class="fa-solid fa-phone" style="color: #64748b; font-size: 0.8rem;"></i> <?php echo htmlspecialchars($row['phone']); ?>
                                                </div>
                                                <?php if (!empty($row['inquiry'])): ?>
                                                    <div style="font-size: 0.85rem; color: #475569; margin-top: 0.35rem; background: #f8fafc; padding: 0.4rem 0.6rem; border-radius: 4px; border-left: 3px solid #84cc16;">
                                                        <?php echo htmlspecialchars($row['inquiry']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div style="font-weight: 700; color: #0369a1; font-size: 0.85rem;">
                                                    <i class="fa-solid fa-mobile-screen"></i> <?php echo htmlspecialchars($row['phone_model']); ?>
                                                </div>
                                                <small style="color: #64748b; font-size: 0.8rem;">
                                                    <?php echo htmlspecialchars($row['device_type']); ?> • <?php echo htmlspecialchars($row['browser']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div style="font-family: monospace; font-size: 0.85rem; color: #334155;">
                                                    <i class="fa-solid fa-network-wired" style="color: #94a3b8;"></i> <?php echo htmlspecialchars($row['ip_address']); ?>
                                                </div>
                                                <small style="color: #64748b; display: block; font-size: 0.8rem;">
                                                    <?php echo htmlspecialchars($row['location']); ?> (<?php echo htmlspecialchars($row['network_isp']); ?>)
                                                </small>
                                            </td>
                                            <td style="text-align: right;">
                                                <a href="rsvps.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this RSVP record?');" style="color: #e11d48; font-size: 0.9rem; padding: 0.35rem 0.6rem; border-radius: 4px; background: rgba(225, 29, 72, 0.1);" title="Delete Record">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 3rem; color: #64748b;">
                                            <i class="fa-solid fa-calendar-xmark" style="font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: #cbd5e1;"></i>
                                            No RSVP registrations received yet. Share the link <a href="../rsvp.php" target="_blank" style="color: var(--primary-light); font-weight: 700;">rsvp.php</a> with church members and visitors!
                                        </td>
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
