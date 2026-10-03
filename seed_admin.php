<?php
require_once 'includes/db.php';
try {
    $pdo->exec("DELETE FROM users");
    $hash = password_hash("kali", PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, is_active) VALUES (:u, :p, 'admin', 1)");
    $stmt->execute([':u' => 'ceejay', ':p' => $hash]);
    echo "Admin user 'ceejay' created successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
