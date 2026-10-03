<?php
// setup_db.php - Run this once to create tables in Supabase
require_once 'includes/db.php';
require_once 'includes/migrate.php';

echo "<h1>Database Setup</h1>";

if ($pdo) {
    try {
        membley_run_migrations($pdo, true);
        
        // Wipe any existing users and force create the 'ceejay' admin with 'kali' password
        $pdo->exec("DELETE FROM users");
        $hash = password_hash("kali", PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, is_active) VALUES (:u, :p, 'admin', 1)");
        $stmt->execute([':u' => 'ceejay', ':p' => $hash]);

        echo "<p style='color:green;'>✅ Successfully created all tables and seeded default data in Supabase!</p>";
        echo "<p style='color:blue;'>✅ <b>Admin user 'ceejay' with password 'kali' has been successfully created!</b></p>";
        echo "<p>You can now go straight to <a href='admin/login.php'>admin/login.php</a> to log in.</p>";
        echo "<p><b>Security warning:</b> Please delete this setup_db.php file after you are done.</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ Migration failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color:red;'>❌ Could not connect to the database. Check your Vercel Environment Variables.</p>";
}
