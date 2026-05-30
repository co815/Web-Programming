<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['error' => 'Method not allowed'], 405);
}

$body = json_body();
$username = trim($body['username'] ?? '');
$password = $body['password'] ?? '';

if ($username === '' || $password === '') {
    json_out(['error' => 'Username and password required'], 400);
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT id, password_hash, role, full_name, group_name FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_out(['error' => 'Invalid username or password'], 401);
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

json_out(['user' => [
    'id' => $user['id'],
    'username' => $username,
    'full_name' => $user['full_name'],
    'role' => $user['role'],
    'group_name' => $user['group_name'],
]]);
