<?php
// setup_db.php - Run this once to create tables in Supabase
require_once 'includes/db.php';
require_once 'includes/migrate.php';

echo "<h1>Database Setup</h1>";

if ($pdo) {
    try {
        membley_run_migrations($pdo, true);
        echo "<p style='color:green;'>✅ Successfully created all tables and seeded default data in Supabase!</p>";
        echo "<p>You can now visit <a href='admin/setup.php'>admin/setup.php</a> to create your admin account.</p>";
        echo "<p><b>Security warning:</b> Please delete this setup_db.php file after you are done.</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ Migration failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color:red;'>❌ Could not connect to the database. Check your Vercel Environment Variables.</p>";
}
