<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../cors.php';

if (empty($_SESSION['user_id'])) {
    json_out(['error' => 'Unauthenticated'], 401);
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT id, username, full_name, role, group_name FROM users WHERE id = ?');
$stmt->execute([current_user_id()]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    json_out(['error' => 'Unauthenticated'], 401);
}

json_out(['user' => $user]);
