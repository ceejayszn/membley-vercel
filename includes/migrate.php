<?php
/**
 * includes/migrate.php
 *
 * Database migration and schema management for Membley Adventist Church.
 *
 * Usage:
 *   - Automatically called from db.php for SQLite (local dev only).
 *   - For PostgreSQL (production), run manually:
 *       php migrate.php
 *     Or run via Vercel Build Command before deployment.
 *
 * This file is idempotent — safe to run multiple times.
 * It does NOT drop tables or delete data.
 */

/**
 * Run all pending migrations.
 *
 * @param PDO  $pdo      Active database connection
 * @param bool $seed     Whether to seed default data (only on fresh installs)
 */
function membley_run_migrations(PDO $pdo, bool $seed = true): void {
    $isPostgres = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql');
    $pk   = $isPostgres ? 'SERIAL PRIMARY KEY'               : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $dt   = $isPostgres ? 'TIMESTAMP DEFAULT NOW()'          : 'DATETIME DEFAULT CURRENT_TIMESTAMP';
    $dtNN = $isPostgres ? 'TIMESTAMP'                        : 'DATETIME';

    // ── Create tables ─────────────────────────────────────────────────────────

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id $pk,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT DEFAULT 'admin',
        is_active INTEGER DEFAULT 1,
        last_login $dtNN,
        created_at $dt
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS blogs (
        id $pk,
        title TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        content TEXT NOT NULL,
        excerpt TEXT NOT NULL,
        image_url TEXT,
        video_url TEXT,
        category TEXT DEFAULT 'General',
        author_name TEXT DEFAULT 'Membley Admin',
        status TEXT DEFAULT 'published',
        fake_likes INTEGER DEFAULT 0,
        real_views INTEGER DEFAULT 0,
        fake_views INTEGER DEFAULT 0,
        created_at $dt
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_invites (
        id $pk,
        token TEXT UNIQUE NOT NULL,
        is_used INTEGER DEFAULT 0,
        created_by TEXT,
        created_at $dt
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_comments (
        id $pk,
        blog_id INTEGER NOT NULL,
        author_name TEXT NOT NULL,
        content TEXT NOT NULL,
        device_id TEXT NOT NULL,
        is_approved INTEGER DEFAULT 1,
        created_at $dt
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_likes (
        id $pk,
        blog_id INTEGER NOT NULL,
        device_id TEXT NOT NULL,
        created_at $dt,
        UNIQUE(blog_id, device_id)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS submissions (
        id $pk,
        type TEXT NOT NULL,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT,
        subject_message TEXT,
        amount REAL DEFAULT 0.00,
        status TEXT DEFAULT 'unread',
        created_at $dt
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS content_submissions (
        id $pk,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT,
        submission_type TEXT NOT NULL,
        title TEXT NOT NULL,
        summary TEXT,
        content TEXT NOT NULL,
        featured_image TEXT,
        attachment TEXT,
        status TEXT DEFAULT 'pending',
        admin_notes TEXT,
        rejection_reason TEXT,
        created_at $dt,
        updated_at $dtNN,
        reviewed_at $dtNN,
        published_at $dtNN
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS analytics (
        page TEXT PRIMARY KEY,
        views INTEGER DEFAULT 0,
        clicks INTEGER DEFAULT 0,
        time_spent INTEGER DEFAULT 0
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS visitor_tracking (
        id $pk,
        ip_address TEXT NOT NULL,
        user_agent TEXT NOT NULL,
        device_type TEXT,
        browser TEXT,
        location TEXT DEFAULT 'Unknown',
        network_isp TEXT DEFAULT 'Unknown',
        time_spent INTEGER DEFAULT 0,
        last_seen $dt,
        created_at $dt
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id $pk,
        title TEXT NOT NULL,
        subtitle TEXT,
        description TEXT,
        event_date DATE NOT NULL,
        event_time TEXT,
        location TEXT,
        category TEXT DEFAULT 'General',
        is_featured INTEGER DEFAULT 0,
        image_url TEXT,
        created_at $dt
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS event_rsvps (
        id $pk,
        event_id INTEGER DEFAULT 1,
        event_title TEXT DEFAULT 'Homecoming Sabbath',
        full_name TEXT NOT NULL,
        is_membley_member INTEGER DEFAULT 0,
        church_from TEXT,
        phone TEXT,
        attendees_count INTEGER DEFAULT 1,
        additional_names TEXT,
        inquiry TEXT,
        ip_address TEXT,
        device_type TEXT,
        phone_model TEXT,
        browser TEXT,
        os TEXT,
        location TEXT DEFAULT 'Unknown',
        network_isp TEXT DEFAULT 'Unknown',
        user_agent TEXT,
        created_at $dt
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS rate_limits (
        id $pk,
        action TEXT NOT NULL,
        identifier TEXT NOT NULL,
        attempt_at $dt
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_log (
        id $pk,
        admin_username TEXT NOT NULL,
        action TEXT NOT NULL,
        target_table TEXT,
        target_id INTEGER,
        details TEXT,
        ip_address TEXT,
        created_at $dt
    )");

    // ── Safe column additions (idempotent) ────────────────────────────────────
    $safe_alters = [
        ["ALTER TABLE blogs ADD COLUMN video_url TEXT", 'blogs', 'video_url'],
        ["ALTER TABLE blogs ADD COLUMN fake_likes INTEGER DEFAULT 0", 'blogs', 'fake_likes'],
        ["ALTER TABLE blogs ADD COLUMN real_views INTEGER DEFAULT 0", 'blogs', 'real_views'],
        ["ALTER TABLE blogs ADD COLUMN fake_views INTEGER DEFAULT 0", 'blogs', 'fake_views'],
        ["ALTER TABLE blogs ADD COLUMN author_name TEXT DEFAULT 'Membley Admin'", 'blogs', 'author_name'],
        ["ALTER TABLE blogs ADD COLUMN status TEXT DEFAULT 'published'", 'blogs', 'status'],
        ["ALTER TABLE users ADD COLUMN role TEXT DEFAULT 'admin'", 'users', 'role'],
        ["ALTER TABLE users ADD COLUMN is_active INTEGER DEFAULT 1", 'users', 'is_active'],
        ["ALTER TABLE users ADD COLUMN last_login DATETIME", 'users', 'last_login'],
        ["ALTER TABLE event_rsvps ADD COLUMN additional_names TEXT", 'event_rsvps', 'additional_names'],
        ["ALTER TABLE blog_comments ADD COLUMN is_approved INTEGER DEFAULT 1", 'blog_comments', 'is_approved'],
    ];

    foreach ($safe_alters as [$sql, $table, $col]) {
        // Check if column already exists before attempting ALTER
        try {
            if ($isPostgres) {
                $check = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_name=:t AND column_name=:c");
                $check->execute([':t' => $table, ':c' => $col]);
                if (!$check->fetchColumn()) $pdo->exec($sql);
            } else {
                $pdo->exec($sql); // SQLite ignores duplicate column errors with try/catch
            }
        } catch (PDOException $e) { /* Column already exists — safe to ignore */ }
    }

    // ── Indexes ───────────────────────────────────────────────────────────────
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_blogs_slug ON blogs(slug)",
        "CREATE INDEX IF NOT EXISTS idx_blogs_status ON blogs(status)",
        "CREATE INDEX IF NOT EXISTS idx_blogs_created ON blogs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_submissions_type_status ON submissions(type, status)",
        "CREATE INDEX IF NOT EXISTS idx_rsvps_event ON event_rsvps(event_id)",
        "CREATE INDEX IF NOT EXISTS idx_rsvps_phone ON event_rsvps(phone)",
        "CREATE INDEX IF NOT EXISTS idx_visitor_ip ON visitor_tracking(ip_address)",
        "CREATE INDEX IF NOT EXISTS idx_visitor_seen ON visitor_tracking(last_seen)",
        "CREATE INDEX IF NOT EXISTS idx_rate_limits_action ON rate_limits(action, identifier)",
        "CREATE INDEX IF NOT EXISTS idx_rate_limits_time ON rate_limits(attempt_at)",
        "CREATE INDEX IF NOT EXISTS idx_audit_log_admin ON audit_log(admin_username)",
        "CREATE INDEX IF NOT EXISTS idx_audit_log_time ON audit_log(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_blog_likes_blog ON blog_likes(blog_id)",
        "CREATE INDEX IF NOT EXISTS idx_blog_comments_blog ON blog_comments(blog_id)",
    ];

    foreach ($indexes as $idx_sql) {
        try { $pdo->exec($idx_sql); }
        catch (PDOException $e) { /* Index may already exist */ }
    }

    // ── Seed default data on fresh installs ───────────────────────────────────
    if ($seed) {
        _membley_seed($pdo, $isPostgres);
    }
}

function _membley_seed(PDO $pdo, bool $isPostgres): void {
    // Seed admin users only if users table is empty
    $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount === 0) {
        // NOTE: Passwords use password_hash with bcrypt.
        // Default passwords are intentionally NOT documented here.
        // Administrators must set their own passwords via a secure setup process.
        // The setup wizard is available at /admin/setup.php on first run.
        $setupToken = bin2hex(random_bytes(16));
        file_put_contents(sys_get_temp_dir() . '/membley_setup.token', $setupToken);
        error_log('[MEMBLEY] Fresh install detected. Setup token written to temp dir. Visit /admin/setup.php to create the first admin account.');
    }

    // Seed events only if events table is empty
    $evCount = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    if ($evCount === 0) {
        $seedEvents = [
            [
                'title'       => 'Homecoming Sabbath',
                'subtitle'    => 'Celebrating 10 Yrs of Fellowship and Family',
                'description' => 'Membley Adventist presents Homecoming Sabbath celebrating 10 years of fellowship and family! Join us for a monumental Sabbath of thanksgiving, praise, and community gathering at Membley Park Estate.',
                'event_date'  => '2026-10-31',
                'event_time'  => '8:00 AM',
                'location'    => 'Membley Park Estate, Ruiru, Kenya',
                'category'    => '10th Anniversary Convocation',
                'is_featured' => 1,
                'image_url'   => '/assets/images/homecoming_flyer.png',
            ],
        ];

        $stmt = $pdo->prepare("INSERT INTO events (title, subtitle, description, event_date, event_time, location, category, is_featured, image_url) VALUES (:title, :subtitle, :description, :event_date, :event_time, :location, :category, :is_featured, :image_url)");
        foreach ($seedEvents as $e) {
            $stmt->execute([
                ':title' => $e['title'], ':subtitle' => $e['subtitle'], ':description' => $e['description'],
                ':event_date' => $e['event_date'], ':event_time' => $e['event_time'], ':location' => $e['location'],
                ':category' => $e['category'], ':is_featured' => $e['is_featured'], ':image_url' => $e['image_url'],
            ]);
        }
    }

    // Seed sample blog posts only if blogs table is empty
    $blogCount = (int)$pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
    if ($blogCount === 0) {
        $seeds = [
            ['Welcome to our New Website!', 'welcome-to-our-new-website', '<p>We are delighted to launch the brand new website for Membley Seventh-day Adventist Church.</p>', 'Welcome to the launch of our new church portal.', 'Announcements'],
            ['The Power of Prayer', 'the-power-of-prayer', '<p>In these challenging times, prayer remains our constant link to the Almighty.</p>', 'Join us as we explore the scriptural power of prayer.', 'Sermons'],
        ];
        $stmt = $pdo->prepare("INSERT INTO blogs (title, slug, content, excerpt, category) VALUES (:title, :slug, :content, :excerpt, :category)");
        foreach ($seeds as [$t, $s, $c, $e, $cat]) {
            $stmt->execute([':title' => $t, ':slug' => $s, ':content' => $c, ':excerpt' => $e, ':category' => $cat]);
        }
    }
}

// ── CLI entry point ────────────────────────────────────────────────────────────
if (PHP_SAPI === 'cli' && basename(__FILE__) === 'migrate.php') {
    // When run directly: php includes/migrate.php [--seed]
    $doSeed = in_array('--seed', $argv ?? [], true);
    require_once __DIR__ . '/security.php';
    require_once __DIR__ . '/db.php';
    if (!$pdo) {
        echo "ERROR: No database connection available.\n";
        exit(1);
    }
    membley_run_migrations($pdo, $doSeed);
    echo "Migrations complete." . ($doSeed ? " Seeding applied." : "") . "\n";
}
