<?php
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membley Seventh-day Adventist Church | Ruiru, Kenya</title>
    <meta name="description" content="Welcome to Membley Seventh-day Adventist Church in Ruiru, Kenya. Join us for Sabbath worship, youth ministries, bible study, and community outreach.">
    <link rel="icon" type="image/png" href="assets/images/sda_logo.png">
    <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php if ($current_page == 'index.php'): ?>
        <div class="top-bar">
        <div class="container">
            <div>
                <i class="fa-solid fa-location-dot"></i> Ruiru, Membley Estate, Kenya | 
                <i class="fa-solid fa-envelope"></i> <a href="mailto:membleyadventist@gmail.com">membleyadventist@gmail.com</a>
            </div>
            <div class="top-bar-links">
                <a href="https://www.instagram.com/membleyadventist/" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://www.tiktok.com/@membleyadventist" target="_blank"><i class="fa-brands fa-tiktok"></i></a>
                <a href="giving.php" style="color: var(--accent); font-weight: 700;"><i class="fa-solid fa-heart"></i> Give Online</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

        <header class="main-header">
        <div class="container header-container">
            <a href="index.php" class="logo-link">
                <img src="assets/images/church_logo.jpg" alt="Membley Seventh-day Adventist Church" class="logo-svg" style="height: 65px; width: auto; border-radius: 4px;">
            </a>

                        <ul class="nav-menu">
                <li><a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a></li>
                <li><a href="about.php" class="nav-link <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">About</a></li>
                <li><a href="ministries.php" class="nav-link <?php echo ($current_page == 'ministries.php') ? 'active' : ''; ?>">Ministries</a></li>
                <li><a href="events.php" class="nav-link <?php echo ($current_page == 'events.php' || $current_page == 'past-events.php' || $current_page == 'rsvp.php') ? 'active' : ''; ?>">Events/Announcements</a></li>
                <li><a href="blog.php" class="nav-link <?php echo ($current_page == 'blog.php' || $current_page == 'blog-single.php') ? 'active' : ''; ?>">Blog / Sermons</a></li>
                <li><a href="giving.php" class="nav-link <?php echo ($current_page == 'giving.php') ? 'active' : ''; ?>">Giving</a></li>
                <li><a href="members.php" class="nav-link <?php echo ($current_page == 'members.php') ? 'active' : ''; ?>">Members</a></li>
                <li><a href="contact.php" class="nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">Contact</a></li>
            </ul>

                        <button class="mobile-menu-btn" id="mobileMenuBtn">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

        <nav class="mobile-nav" id="mobileNav">
        <ul class="mobile-nav-list">
            <li><a href="index.php" class="mobile-nav-link">Home</a></li>
            <li><a href="about.php" class="mobile-nav-link">About</a></li>
            <li><a href="ministries.php" class="mobile-nav-link">Ministries</a></li>
            <li><a href="events.php" class="mobile-nav-link">Events</a></li>
            <li><a href="blog.php" class="mobile-nav-link">Blog & Sermons</a></li>
            <li><a href="giving.php" class="mobile-nav-link">Giving / Pledges</a></li>
            <li><a href="members.php" class="mobile-nav-link">Members / Registrations</a></li>
            <li><a href="contact.php" class="mobile-nav-link">Contact & Prayer</a></li>
        </ul>
    </nav>
