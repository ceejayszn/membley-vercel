<?php
/**
 * api/blog_interact.php
 * Handles blog likes and comments via AJAX.
 * Security: rate limited per IP, input validated, errors never exposed to users.
 */
require_once '../includes/db.php';
require_once '../includes/security.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$action    = $_POST['action']    ?? '';
$blog_id   = intval($_POST['blog_id']   ?? 0);
$device_id = substr(trim($_POST['device_id'] ?? ''), 0, 64);

if ($blog_id <= 0 || empty($device_id) || empty($action)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Sanitize device_id — only alphanumeric and hyphens
if (!preg_match('/^[a-zA-Z0-9\-]+$/', $device_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid device identifier']);
    exit;
}

// Verify blog exists before any action
try {
    $exists = $pdo->prepare("SELECT id FROM blogs WHERE id = :id LIMIT 1");
    $exists->execute([':id' => $blog_id]);
    if (!$exists->fetch()) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Blog not found']);
        exit;
    }
} catch (PDOException $e) {
    membley_log('error', 'blog_interact: blog existence check failed', ['blog_id' => $blog_id]);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Service temporarily unavailable']);
    exit;
}

$ip = membley_client_ip();

try {
    if ($action === 'like') {
        // Rate limit: 10 likes per IP per hour
        if (!rate_limit_check($pdo, 'blog_like', $ip, 10, 3600)) {
            http_response_code(429);
            echo json_encode(['status' => 'error', 'message' => 'Too many requests. Please wait.']);
            exit;
        }

        // Check if already liked by this device
        $check = $pdo->prepare("SELECT id FROM blog_likes WHERE blog_id = :blog_id AND device_id = :device_id");
        $check->execute([':blog_id' => $blog_id, ':device_id' => $device_id]);

        if ($check->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Already liked']);
            exit;
        }

        $pdo->prepare("INSERT INTO blog_likes (blog_id, device_id) VALUES (:blog_id, :device_id)")
            ->execute([':blog_id' => $blog_id, ':device_id' => $device_id]);

        $total = $pdo->prepare("SELECT COUNT(*) FROM blog_likes WHERE blog_id = :blog_id");
        $total->execute([':blog_id' => $blog_id]);
        $likes = (int)$total->fetchColumn();

        echo json_encode(['status' => 'success', 'likes' => $likes]);
        exit;

    } elseif ($action === 'comment') {
        // Rate limit: 5 comments per IP per 10 minutes
        if (!rate_limit_check($pdo, 'blog_comment', $ip, 5, 600)) {
            http_response_code(429);
            echo json_encode(['status' => 'error', 'message' => 'You are posting too fast. Please wait a moment.']);
            exit;
        }

        $author  = sanitize_text(trim($_POST['author_name'] ?? ''), 100);
        $content = sanitize_text(trim($_POST['content'] ?? ''), 1000);

        if (empty($author) || empty($content)) {
            echo json_encode(['status' => 'error', 'message' => 'Name and comment are required']);
            exit;
        }

        $pdo->prepare("INSERT INTO blog_comments (blog_id, author_name, content, device_id) VALUES (:blog_id, :author, :content, :device_id)")
            ->execute([
                ':blog_id'   => $blog_id,
                ':author'    => $author,
                ':content'   => $content,
                ':device_id' => $device_id,
            ]);

        $new_id = $pdo->lastInsertId();

        echo json_encode([
            'status'  => 'success',
            'comment' => [
                'id'      => (int)$new_id,
                'author'  => htmlspecialchars($author, ENT_QUOTES, 'UTF-8'),
                'content' => nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')),
                'date'    => date('F d, Y H:i'),
            ],
        ]);
        exit;

    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
    }

} catch (PDOException $e) {
    membley_log('error', 'blog_interact DB error: ' . $e->getMessage(), ['action' => $action, 'blog_id' => $blog_id]);
    http_response_code(500);
    // Never expose raw DB errors to public
    echo json_encode(['status' => 'error', 'message' => 'Service temporarily unavailable. Please try again.']);
    exit;
}
