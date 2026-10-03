<?php
/**
 * admin/includes/sidebar.php
 *
 * Shared admin sidebar — include in every admin page.
 * Replaces 13 copies of duplicated sidebar HTML.
 *
 * Usage: require_once 'includes/sidebar.php';
 *
 * Sets $current_admin_page based on the calling filename.
 */
$current_admin_page = basename($_SERVER['SCRIPT_NAME']);
$admin_user = get_logged_in_user();
?>
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-church"></i> Membley SDA Admin
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="sidebar-link <?php echo $current_admin_page === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-gauge" style="margin-right:0.5rem;"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="rsvps.php" class="sidebar-link <?php echo $current_admin_page === 'rsvps.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-calendar-check" style="margin-right:0.5rem;"></i> Event RSVPs
            </a>
        </li>
        <li>
            <a href="members.php" class="sidebar-link <?php echo $current_admin_page === 'members.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-users" style="margin-right:0.5rem;"></i> Members
            </a>
        </li>
        <li>
            <a href="forms.php" class="sidebar-link <?php echo $current_admin_page === 'forms.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-wpforms" style="margin-right:0.5rem;"></i> Manage Forms
            </a>
        </li>
        <li>
            <a href="analytics.php" class="sidebar-link <?php echo $current_admin_page === 'analytics.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-line" style="margin-right:0.5rem;"></i> Visitor Analytics
            </a>
        </li>
        <li>
            <a href="blogs.php" class="sidebar-link <?php echo in_array($current_admin_page, ['blogs.php', 'blogs_review.php', 'blog_analytics.php', 'blog_invites.php', 'manage_comments.php']) ? 'active' : ''; ?>">
                <i class="fa-solid fa-newspaper" style="margin-right:0.5rem;"></i> Manage Blogs
            </a>
        </li>
        <li>
            <a href="content_submissions.php" class="sidebar-link <?php echo in_array($current_admin_page, ['content_submissions.php', 'review_content.php']) ? 'active' : ''; ?>">
                <i class="fa-solid fa-file-signature" style="margin-right:0.5rem;"></i> Content Submissions
            </a>
        </li>
        <li>
            <a href="submissions.php" class="sidebar-link <?php echo $current_admin_page === 'submissions.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-envelope-open-text" style="margin-right:0.5rem;"></i> Submissions
            </a>
        </li>
        <li>
            <a href="diagnostics.php" class="sidebar-link <?php echo $current_admin_page === 'diagnostics.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-stethoscope" style="margin-right:0.5rem;"></i> Diagnostics
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <span style="font-size:0.8rem;color:rgba(255,255,255,0.5);display:block;margin-bottom:0.5rem;">
            <i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($admin_user); ?>
        </span>
    </div>
</aside>
