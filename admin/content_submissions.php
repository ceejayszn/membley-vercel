<?php
require_once 'auth.php';
check_auth();

require_once '../includes/db.php';

$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT * FROM content_submissions";
$params = [];
if (!empty($filter_status)) {
    $sql .= " WHERE status = :status";
    $params[':status'] = $filter_status;
}
$sql .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $submissions = $stmt->fetchAll();
} catch (PDOException $e) {
    $submissions = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Submissions - Membley SDA Admin</title>
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

                <li><a href="analytics.php" class="sidebar-link"><i class="fa-solid fa-chart-line" style="margin-right: 0.5rem;"></i> Visitor Analytics</a></li>

                <li><a href="blogs.php" class="sidebar-link"><i class="fa-solid fa-newspaper" style="margin-right: 0.5rem;"></i> Manage Blogs</a></li>

                <li><a href="content_submissions.php" class="sidebar-link active"><i class="fa-solid fa-file-signature" style="margin-right: 0.5rem;"></i> Content Submissions</a></li>

                <li><a href="submissions.php" class="sidebar-link"><i class="fa-solid fa-envelope-open-text" style="margin-right: 0.5rem;"></i> Form Submissions</a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="logout.php" class="sidebar-link" style="color: #ff8b8b;"><i class="fa-solid fa-right-from-bracket" style="margin-right: 0.5rem;"></i> Sign Out</a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="admin-title">Content Submissions (Reviews, News, etc.)</div>
                <div class="admin-user">
                    Welcome, <span style="color: var(--primary-light);"><?php echo htmlspecialchars(get_logged_in_user()); ?></span>
                </div>
            </header>

            <div class="admin-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <a href="content_submissions.php" class="admin-btn-outline" style="margin: 0; padding: 0.5rem 0.75rem; font-size: 0.85rem; border-color: <?php echo empty($filter_status) ? 'var(--primary)' : '#cbd5e1'; ?>; background-color: <?php echo empty($filter_status) ? '#f1f5f9' : 'transparent'; ?>;">All</a>
                        <a href="content_submissions.php?status=pending" class="admin-btn-outline" style="margin: 0; padding: 0.5rem 0.75rem; font-size: 0.85rem; border-color: <?php echo ($filter_status == 'pending') ? 'var(--primary)' : '#cbd5e1'; ?>; background-color: <?php echo ($filter_status == 'pending') ? '#f1f5f9' : 'transparent'; ?>;">Pending</a>
                        <a href="content_submissions.php?status=under_review" class="admin-btn-outline" style="margin: 0; padding: 0.5rem 0.75rem; font-size: 0.85rem; border-color: <?php echo ($filter_status == 'under_review') ? 'var(--primary)' : '#cbd5e1'; ?>; background-color: <?php echo ($filter_status == 'under_review') ? '#f1f5f9' : 'transparent'; ?>;">Under Review</a>
                        <a href="content_submissions.php?status=approved" class="admin-btn-outline" style="margin: 0; padding: 0.5rem 0.75rem; font-size: 0.85rem; border-color: <?php echo ($filter_status == 'approved') ? 'var(--primary)' : '#cbd5e1'; ?>; background-color: <?php echo ($filter_status == 'approved') ? '#f1f5f9' : 'transparent'; ?>;">Approved</a>
                        <a href="content_submissions.php?status=published" class="admin-btn-outline" style="margin: 0; padding: 0.5rem 0.75rem; font-size: 0.85rem; border-color: <?php echo ($filter_status == 'published') ? 'var(--primary)' : '#cbd5e1'; ?>; background-color: <?php echo ($filter_status == 'published') ? '#f1f5f9' : 'transparent'; ?>;">Published</a>
                        <a href="content_submissions.php?status=rejected" class="admin-btn-outline" style="margin: 0; padding: 0.5rem 0.75rem; font-size: 0.85rem; border-color: <?php echo ($filter_status == 'rejected') ? 'var(--primary)' : '#cbd5e1'; ?>; background-color: <?php echo ($filter_status == 'rejected') ? '#f1f5f9' : 'transparent'; ?>;">Rejected</a>
                    </div>
                </div>

                <div class="card-table-wrap">
                    <div class="card-table-header">
                        <span class="table-title">
                            <?php 
                                if (empty($filter_status)) echo 'All Submissions';
                                else echo ucfirst(htmlspecialchars(str_replace('_', ' ', $filter_status))) . ' Submissions';
                            ?>
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Submitter</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($submissions)): ?>
                                    <?php foreach ($submissions as $sub): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y H:i', strtotime($sub['created_at'])); ?></td>
                                            <td>
                                                <span class="badge" style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                                                    <?php echo htmlspecialchars($sub['submission_type']); ?>
                                                </span>
                                            </td>
                                            <td style="font-weight: 600; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?php echo htmlspecialchars($sub['title']); ?>
                                            </td>
                                            <td>
                                                <div style="font-size: 0.9rem; font-weight: 500;"><?php echo htmlspecialchars($sub['name']); ?></div>
                                                <div style="font-size: 0.8rem; color: #64748b;"><?php echo htmlspecialchars($sub['email']); ?></div>
                                            </td>
                                            <td>
                                                <?php 
                                                $badge_color = '#e2e8f0'; $text_color = '#475569';
                                                if($sub['status'] == 'pending') { $badge_color = '#fef3c7'; $text_color = '#92400e'; }
                                                elseif($sub['status'] == 'under_review') { $badge_color = '#e0f2fe'; $text_color = '#0369a1'; }
                                                elseif($sub['status'] == 'approved') { $badge_color = '#dcfce7'; $text_color = '#166534'; }
                                                elseif($sub['status'] == 'published') { $badge_color = '#cffafe'; $text_color = '#155e75'; }
                                                elseif($sub['status'] == 'rejected') { $badge_color = '#fee2e2'; $text_color = '#991b1b'; }
                                                ?>
                                                <span class="badge" style="background-color: <?php echo $badge_color; ?>; color: <?php echo $text_color; ?>;">
                                                    <?php echo ucfirst(htmlspecialchars(str_replace('_', ' ', $sub['status']))); ?>
                                                </span>
                                            </td>
                                            <td style="text-align: right; white-space: nowrap;">
                                                <a href="review_content.php?id=<?php echo $sub['id']; ?>" class="btn-sm btn-edit" style="background-color: var(--primary); color: white;"><i class="fa-solid fa-magnifying-glass"></i> Review</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #637381; padding: 2rem;">No submissions found.</td>
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
