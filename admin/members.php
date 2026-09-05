<?php
require_once 'auth.php';
check_auth();

require_once '../includes/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id     = isset($_GET['id'])     ? intval($_GET['id']) : 0;
$msg    = isset($_GET['msg'])    ? $_GET['msg'] : '';

if (!empty($action) && $id > 0) {
    if ($action === 'read' || $action === 'resolve') {
        $new_status = ($action === 'read') ? 'read' : 'resolved';
        try {
            $stmt = $pdo->prepare("UPDATE submissions SET status = :status WHERE id = :id AND type = 'member_registration'");
            $stmt->execute([':status' => $new_status, ':id' => $id]);
        } catch (PDOException $e) {}
        header('Location: members.php?msg=updated');
        exit;
    }
    if ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM submissions WHERE id = :id AND type = 'member_registration'");
            $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {}
        header('Location: members.php?msg=deleted');
        exit;
    }
}

if ($action === 'export') {
    try {
        $stmt = $pdo->query("SELECT created_at, name, email, phone, subject_message, status FROM submissions WHERE type = 'member_registration' ORDER BY created_at DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $filename = "member_registrations_" . date('Ymd_His') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Registration Date', 'Full Name', 'Email', 'Phone', 'Details', 'Status']);
        foreach ($rows as $row) { fputcsv($out, $row); }
        fclose($out);
        exit;
    } catch (PDOException $e) { die("Export failed: " . $e->getMessage()); }
}

$stat_total    = 0;
$stat_unread   = 0;
$stat_read     = 0;
$stat_resolved = 0;
try {
    $stat_total    = $pdo->query("SELECT COUNT(*) FROM submissions WHERE type='member_registration'")->fetchColumn();
    $stat_unread   = $pdo->query("SELECT COUNT(*) FROM submissions WHERE type='member_registration' AND status='unread'")->fetchColumn();
    $stat_read     = $pdo->query("SELECT COUNT(*) FROM submissions WHERE type='member_registration' AND status='read'")->fetchColumn();
    $stat_resolved = $pdo->query("SELECT COUNT(*) FROM submissions WHERE type='member_registration' AND status='resolved'")->fetchColumn();
} catch (PDOException $e) {}

$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$sql    = "SELECT * FROM submissions WHERE type = 'member_registration'";
$params = [];
if (!empty($filter_status)) {
    $sql .= " AND status = :status";
    $params[':status'] = $filter_status;
}
$sql .= " ORDER BY created_at DESC";
$members = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $members = $stmt->fetchAll();
} catch (PDOException $e) { $members = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Registrations — Membley SDA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.35rem 1.5rem; font-size: 0.82rem; margin-top: 0.5rem; color: #475569; }
        .detail-grid span { font-weight: 700; color: #1e293b; }
        .member-row td { vertical-align: top; padding: 1rem 0.75rem; }
        .stats-mini { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem; }
        .stat-mini-card { background: #fff; border: 1px solid var(--border-color); border-radius: 10px; padding: 1rem 1.5rem; flex: 1; min-width: 140px; text-align: center; }
        .stat-mini-card .num { font-size: 2rem; font-weight: 800; color: var(--primary); }
        .stat-mini-card .lbl { font-size: 0.78rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .flash-success { background: rgba(34,197,94,0.1); color: #15803d; border: 1px solid rgba(34,197,94,0.25); padding: 0.75rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600; }
        .flash-danger  { background: rgba(239,68,68,0.1);  color: #dc2626;  border: 1px solid rgba(239,68,68,0.25);  padding: 0.75rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600; }
        .filter-bar { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; margin-bottom: 1.5rem; }
        .filter-pill { padding: 0.4rem 1rem; border-radius: 50px; border: 1.5px solid #e2e8f0; background: transparent; color: #475569; font-size: 0.82rem; font-weight: 600; text-decoration: none; transition: all 0.2s; }
        .filter-pill:hover, .filter-pill.active { border-color: var(--primary); background: rgba(0,47,93,0.07); color: var(--primary); }
        .btn-danger-sm { background: rgba(239,68,68,0.08); color: #dc2626; border: 1px solid rgba(239,68,68,0.2); padding: 0.35rem 0.65rem; font-size: 0.78rem; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block; cursor: pointer; }
        .btn-danger-sm:hover { background: rgba(239,68,68,0.18); }
    </style>
</head>
<body>
<div class="admin-container">

        <aside class="admin-sidebar">
        <div class="sidebar-brand"><i class="fa-solid fa-church"></i> Membley SDA Admin</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"  class="sidebar-link"><i class="fa-solid fa-gauge"              style="margin-right:0.5rem;"></i> Dashboard</a></li>
            <li><a href="rsvps.php"      class="sidebar-link"><i class="fa-solid fa-calendar-check"     style="margin-right:0.5rem;"></i> Event RSVPs</a></li>
            <li><a href="members.php"    class="sidebar-link active"><i class="fa-solid fa-users"       style="margin-right:0.5rem;"></i> Members</a></li>
            <li><a href="forms.php"      class="sidebar-link"><i class="fa-solid fa-wpforms"            style="margin-right:0.5rem;"></i> Manage Forms</a></li>
            <li><a href="analytics.php"  class="sidebar-link"><i class="fa-solid fa-chart-line"         style="margin-right:0.5rem;"></i> Visitor Analytics</a></li>
            <li><a href="blogs.php"      class="sidebar-link"><i class="fa-solid fa-newspaper"          style="margin-right:0.5rem;"></i> Manage Blogs</a></li>
            <li><a href="submissions.php" class="sidebar-link"><i class="fa-solid fa-envelope-open-text" style="margin-right:0.5rem;"></i> Submissions</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php" class="sidebar-link" style="color:#ff8b8b;"><i class="fa-solid fa-right-from-bracket" style="margin-right:0.5rem;"></i> Sign Out</a>
        </div>
    </aside>

        <main class="admin-main">
        <header class="admin-header">
            <div class="admin-title"><i class="fa-solid fa-users" style="margin-right:0.5rem;color:var(--primary-light);"></i> Member Registrations</div>
            <div class="admin-user">Welcome, <span style="color:var(--primary-light);"><?php echo htmlspecialchars(get_logged_in_user()); ?></span></div>
        </header>

        <div class="admin-content">

            <?php if ($msg === 'updated'): ?>
                <div class="flash-success"><i class="fa-solid fa-circle-check"></i> Status updated successfully.</div>
            <?php elseif ($msg === 'deleted'): ?>
                <div class="flash-danger"><i class="fa-solid fa-trash"></i> Record deleted.</div>
            <?php endif; ?>

                        <div class="stats-mini">
                <div class="stat-mini-card">
                    <div class="num"><?php echo $stat_total; ?></div>
                    <div class="lbl">Total Registered</div>
                </div>
                <div class="stat-mini-card" style="border-top:3px solid #f59e0b;">
                    <div class="num" style="color:#b45309;"><?php echo $stat_unread; ?></div>
                    <div class="lbl">Pending Review</div>
                </div>
                <div class="stat-mini-card" style="border-top:3px solid #0369a1;">
                    <div class="num" style="color:#0369a1;"><?php echo $stat_read; ?></div>
                    <div class="lbl">Reviewed</div>
                </div>
                <div class="stat-mini-card" style="border-top:3px solid #16a34a;">
                    <div class="num" style="color:#16a34a;"><?php echo $stat_resolved; ?></div>
                    <div class="lbl">Processed</div>
                </div>
            </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
                                <div class="filter-bar">
                    <a href="members.php"              class="filter-pill <?php echo empty($filter_status) ? 'active' : ''; ?>">All</a>
                    <a href="members.php?status=unread"   class="filter-pill <?php echo $filter_status==='unread'   ? 'active' : ''; ?>">Pending</a>
                    <a href="members.php?status=read"     class="filter-pill <?php echo $filter_status==='read'     ? 'active' : ''; ?>">Reviewed</a>
                    <a href="members.php?status=resolved" class="filter-pill <?php echo $filter_status==='resolved' ? 'active' : ''; ?>">Processed</a>
                </div>
                                <a href="members.php?action=export" class="admin-btn" style="background-color:var(--success);"><i class="fa-solid fa-file-excel"></i> Export CSV</a>
            </div>

                        <div class="card-table-wrap">
                <div class="card-table-header">
                    <span class="table-title">Member Registration Forms</span>
                    <span style="font-size:0.8rem;color:#64748b;"><?php echo count($members); ?> record(s)</span>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Full Name</th>
                                <th>Contact</th>
                                <th>Registration Details</th>
                                <th>Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($members)): ?>
                            <?php foreach ($members as $m): ?>
                                <?php
                                    $details_raw = $m['subject_message'] ?? '';
                                    $detail_lines = [];
                                    foreach (explode("\n", $details_raw) as $line) {
                                        $parts = explode(':', $line, 2);
                                        if (count($parts) === 2) {
                                            $detail_lines[trim($parts[0])] = trim($parts[1]);
                                        }
                                    }
                                ?>
                                <tr class="member-row">
                                    <td style="white-space:nowrap;font-size:0.82rem;color:#64748b;">
                                        <?php echo date('M d, Y', strtotime($m['created_at'])); ?><br>
                                        <span style="font-size:0.78rem;"><?php echo date('H:i', strtotime($m['created_at'])); ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight:700;color:var(--primary);font-size:0.95rem;"><?php echo htmlspecialchars($m['name']); ?></div>
                                        <?php if (!empty($detail_lines['Gender'])): ?>
                                            <span style="font-size:0.78rem;color:#64748b;"><?php echo htmlspecialchars($detail_lines['Gender']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:0.85rem;">
                                        <div><?php echo htmlspecialchars($m['email']); ?></div>
                                        <div style="color:#64748b;"><?php echo htmlspecialchars($m['phone']); ?></div>
                                    </td>
                                    <td>
                                        <div class="detail-grid">
                                            <?php if (!empty($detail_lines['Date of Birth'])): ?>
                                                <div>Date of Birth: <span><?php echo htmlspecialchars($detail_lines['Date of Birth']); ?></span></div>
                                            <?php endif; ?>
                                            <?php if (!empty($detail_lines['Address'])): ?>
                                                <div>Address: <span><?php echo htmlspecialchars($detail_lines['Address']); ?></span></div>
                                            <?php endif; ?>
                                            <?php if (!empty($detail_lines['Sabbath School Class'])): ?>
                                                <div>SS Class: <span><?php echo htmlspecialchars($detail_lines['Sabbath School Class']); ?></span></div>
                                            <?php endif; ?>
                                            <?php if (!empty($detail_lines['Previous Church'])): ?>
                                                <div>Transfer From: <span><?php echo htmlspecialchars($detail_lines['Previous Church']); ?></span></div>
                                            <?php endif; ?>
                                            <?php if (!empty($detail_lines['Baptised'])): ?>
                                                <div>Baptised: <span><?php echo htmlspecialchars($detail_lines['Baptised']); ?></span></div>
                                            <?php endif; ?>
                                            <?php if (!empty($detail_lines['Notes']) && $detail_lines['Notes'] !== 'None'): ?>
                                                <div style="grid-column:1/-1;">Notes: <span><?php echo htmlspecialchars($detail_lines['Notes']); ?></span></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo htmlspecialchars($m['status']); ?>">
                                            <?php echo ucfirst($m['status']); ?>
                                        </span>
                                    </td>
                                    <td style="text-align:right;white-space:nowrap;">
                                        <div style="display:flex;flex-direction:column;gap:0.35rem;align-items:flex-end;">
                                        <?php if ($m['status'] === 'unread'): ?>
                                            <a href="members.php?action=read&id=<?php echo $m['id']; ?><?php echo $filter_status ? '&status='.$filter_status : ''; ?>" class="btn-sm btn-edit"><i class="fa-regular fa-eye"></i> Mark Read</a>
                                        <?php elseif ($m['status'] === 'read'): ?>
                                            <a href="members.php?action=resolve&id=<?php echo $m['id']; ?><?php echo $filter_status ? '&status='.$filter_status : ''; ?>" class="btn-sm btn-edit" style="background:rgba(34,197,94,0.1);color:#16a34a;border-color:rgba(34,197,94,0.2);"><i class="fa-regular fa-circle-check"></i> Processed</a>
                                        <?php endif; ?>
                                        <a href="members.php?action=delete&id=<?php echo $m['id']; ?>" class="btn-danger-sm" onclick="return confirm('Delete this registration permanently?')"><i class="fa-regular fa-trash-can"></i> Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center;color:#64748b;padding:3rem 1rem;">
                                    <i class="fa-solid fa-users" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:0.75rem;"></i>
                                    No member registrations found<?php echo $filter_status ? " with status <strong>$filter_status</strong>" : ''; ?>.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>    </main>
</div>
</body>
</html>
