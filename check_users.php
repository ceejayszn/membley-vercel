<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->query("SELECT id, username, is_active FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
