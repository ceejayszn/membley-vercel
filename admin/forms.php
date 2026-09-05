<?php
require_once 'auth.php';
check_auth();

require_once '../includes/db.php';

try {
    $member_count = $pdo->query("SELECT COUNT(*) FROM submissions WHERE type = 'member_registration'")->fetchColumn();
    $pledge_count = $pdo->query("SELECT COUNT(*) FROM submissions WHERE type = 'pledge'")->fetchColumn();
    $prayer_count = $pdo->query("SELECT COUNT(*) FROM submissions WHERE type = 'prayer'")->fetchColumn();
    $contact_count = $pdo->query("SELECT COUNT(*) FROM submissions WHERE type = 'contact'")->fetchColumn();
} catch (PDOException $e) {
    $member_count = 0;
    $pledge_count = 0;
    $prayer_count = 0;
    $contact_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Forms - Membley SDA Admin</title>
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
                <li><a href="forms.php" class="sidebar-link active"><i class="fa-solid fa-wpforms" style="margin-right: 0.5rem;"></i> Manage Forms</a></li>
                <li><a href="blogs.php" class="sidebar-link"><i class="fa-solid fa-newspaper" style="margin-right: 0.5rem;"></i> Manage Blogs</a></li>
                <li><a href="submissions.php" class="sidebar-link"><i class="fa-solid fa-envelope-open-text" style="margin-right: 0.5rem;"></i> Submissions</a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="logout.php" class="sidebar-link" style="color: #ff8b8b;"><i class="fa-solid fa-right-from-bracket" style="margin-right: 0.5rem;"></i> Sign Out</a>
            </div>
        </aside>

                <main class="admin-main">
            <header class="admin-header">
                <div class="admin-title">Manage Forms & Submissions</div>
                <div class="admin-user">
                    Welcome, <span style="color: var(--primary-light);"><?php echo htmlspecialchars(get_logged_in_user()); ?></span>
                </div>
            </header>

            <div class="admin-content">
                
                <p style="color: #637381; margin-bottom: 2rem;">Manage active form categories, review submissions, and export data in Excel-compatible formats.</p>

                                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
                    
                                        <div style="background-color: var(--bg-white); border-radius: 12px; border: 1px solid var(--border-color); padding: 2rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm); min-height: 250px;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                                <i class="fa-solid fa-users" style="font-size: 2.5rem; color: var(--primary);"></i>
                                <span style="background-color: rgba(8,43,67,0.1); color: var(--primary); font-size: 0.8rem; font-weight: 700; padding: 0.25rem 0.50rem; border-radius: 4px;">Public Link</span>
                            </div>
                            <h3 style="color: var(--primary); font-size: 1.25rem; margin-bottom: 0.5rem;">Member Registration</h3>
                            <p style="font-size: 0.85rem; color: #637381; margin-bottom: 1.5rem;">Filled out by new or transferring members to register their profiles.</p>
                        </div>
                        <div>
                            <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                                <?php echo $member_count; ?> <span style="font-size: 0.85rem; font-weight: 500; color: #637381;">responses</span>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="submissions.php?type=member_registration" class="admin-btn-outline" style="margin: 0; padding: 0.5rem; font-size: 0.8rem; flex: 1; text-align: center;"><i class="fa-regular fa-eye"></i> View</a>
                                <a href="submissions.php?action=export&type=member_registration" class="admin-btn" style="margin: 0; padding: 0.5rem; font-size: 0.8rem; background-color: var(--success); flex: 1; text-align: center;"><i class="fa-solid fa-file-excel"></i> Export</a>
                            </div>
                        </div>
                    </div>

                                        <div style="background-color: var(--bg-white); border-radius: 12px; border: 1px solid var(--border-color); padding: 2rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm); min-height: 250px;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                                <i class="fa-solid fa-hand-holding-dollar" style="font-size: 2.5rem; color: #1e40af;"></i>
                                <span style="background-color: rgba(30,64,175,0.1); color: #1e40af; font-size: 0.8rem; font-weight: 700; padding: 0.25rem 0.50rem; border-radius: 4px;">Public Link</span>
                            </div>
                            <h3 style="color: var(--primary); font-size: 1.25rem; margin-bottom: 0.5rem;">Pledges & Pledging</h3>
                            <p style="font-size: 0.85rem; color: #637381; margin-bottom: 1.5rem;">Commitments registered by members to support church development and camp meetings.</p>
                        </div>
                        <div>
                            <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                                <?php echo $pledge_count; ?> <span style="font-size: 0.85rem; font-weight: 500; color: #637381;">responses</span>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="submissions.php?type=pledge" class="admin-btn-outline" style="margin: 0; padding: 0.5rem; font-size: 0.8rem; flex: 1; text-align: center;"><i class="fa-regular fa-eye"></i> View</a>
                                <a href="submissions.php?action=export&type=pledge" class="admin-btn" style="margin: 0; padding: 0.5rem; font-size: 0.8rem; background-color: var(--success); flex: 1; text-align: center;"><i class="fa-solid fa-file-excel"></i> Export</a>
                            </div>
                        </div>
                    </div>

                                        <div style="background-color: var(--bg-white); border-radius: 12px; border: 1px solid var(--border-color); padding: 2rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm); min-height: 250px;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                                <i class="fa-solid fa-hands-praying" style="font-size: 2.5rem; color: #9d174d;"></i>
                                <span style="background-color: rgba(157,23,77,0.1); color: #9d174d; font-size: 0.8rem; font-weight: 700; padding: 0.25rem 0.50rem; border-radius: 4px;">Public Link</span>
                            </div>
                            <h3 style="color: var(--primary); font-size: 1.25rem; margin-bottom: 0.5rem;">Prayer Requests</h3>
                            <p style="font-size: 0.85rem; color: #637381; margin-bottom: 1.5rem;">Submitted prayer requests from visitors and members for the elder board.</p>
                        </div>
                        <div>
                            <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                                <?php echo $prayer_count; ?> <span style="font-size: 0.85rem; font-weight: 500; color: #637381;">responses</span>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="submissions.php?type=prayer" class="admin-btn-outline" style="margin: 0; padding: 0.5rem; font-size: 0.8rem; flex: 1; text-align: center;"><i class="fa-regular fa-eye"></i> View</a>
                                <a href="submissions.php?action=export&type=prayer" class="admin-btn" style="margin: 0; padding: 0.5rem; font-size: 0.8rem; background-color: var(--success); flex: 1; text-align: center;"><i class="fa-solid fa-file-excel"></i> Export</a>
                            </div>
                        </div>
                    </div>

                                        <div style="background-color: var(--bg-white); border-radius: 12px; border: 1px solid var(--border-color); padding: 2rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm); min-height: 250px;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                                <i class="fa-solid fa-envelope" style="font-size: 2.5rem; color: #0369a1;"></i>
                                <span style="background-color: rgba(3,105,161,0.1); color: #0369a1; font-size: 0.8rem; font-weight: 700; padding: 0.25rem 0.50rem; border-radius: 4px;">Public Link</span>
                            </div>
                            <h3 style="color: var(--primary); font-size: 1.25rem; margin-bottom: 0.5rem;">Contact Inquiries</h3>
                            <p style="font-size: 0.85rem; color: #637381; margin-bottom: 1.5rem;">General messages and questions submitted from the website's contact form.</p>
                        </div>
                        <div>
                            <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                                <?php echo $contact_count; ?> <span style="font-size: 0.85rem; font-weight: 500; color: #637381;">responses</span>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="submissions.php?type=contact" class="admin-btn-outline" style="margin: 0; padding: 0.5rem; font-size: 0.8rem; flex: 1; text-align: center;"><i class="fa-regular fa-eye"></i> View</a>
                                <a href="submissions.php?action=export&type=contact" class="admin-btn" style="margin: 0; padding: 0.5rem; font-size: 0.8rem; background-color: var(--success); flex: 1; text-align: center;"><i class="fa-solid fa-file-excel"></i> Export</a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>

</body>
</html>
