<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';
$blog_id = $_POST['blog_id'] ?? '';
$device_id = $_POST['device_id'] ?? '';

if (empty($blog_id) || empty($device_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

try {
    if ($action === 'like') {
        // Check if already liked
        $check = $pdo->prepare("SELECT id FROM blog_likes WHERE blog_id = :blog_id AND device_id = :device_id");
        $check->execute([':blog_id' => $blog_id, ':device_id' => $device_id]);
        
        if ($check->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Already liked']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO blog_likes (blog_id, device_id) VALUES (:blog_id, :device_id)");
        $stmt->execute([':blog_id' => $blog_id, ':device_id' => $device_id]);

        // Get new like count
        $count = $pdo->prepare("SELECT COUNT(*) FROM blog_likes WHERE blog_id = :blog_id");
        $count->execute([':blog_id' => $blog_id]);
        $total_likes = $count->fetchColumn();

        echo json_encode(['status' => 'success', 'likes' => $total_likes]);
        exit;

    } elseif ($action === 'comment') {
        $author = trim($_POST['author_name'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (empty($author) || empty($content)) {
            echo json_encode(['status' => 'error', 'message' => 'Name and comment are required']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO blog_comments (blog_id, author_name, content, device_id) VALUES (:blog_id, :author, :content, :device_id)");
        $stmt->execute([
            ':blog_id' => $blog_id,
            ':author' => $author,
            ':content' => $content,
            ':device_id' => $device_id
        ]);

        $new_comment_id = $pdo->lastInsertId();
        $date = date('F d, Y H:i');

        echo json_encode([
            'status' => 'success',
            'comment' => [
                'id' => $new_comment_id,
                'author' => htmlspecialchars($author),
                'content' => nl2br(htmlspecialchars($content)),
                'date' => $date
            ]
        ]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
