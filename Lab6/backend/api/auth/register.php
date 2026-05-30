<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['error' => 'Method not allowed'], 405);
}

$body = json_body();
$username = trim($body['username'] ?? '');
$full_name = trim($body['full_name'] ?? '');
$role = $body['role'] ?? '';
$group = trim($body['group_name'] ?? '');
$password = $body['password'] ?? '';

$errors = [];
if (!validate_username($username))
    $errors[] = 'Username: 1–50 chars, letters/digits/underscores only.';
if ($full_name === '' || strlen($full_name) > 100)
    $errors[] = 'Full name must be 1–100 characters.';
if (!in_array($role, ['professor', 'student'], true))
    $errors[] = 'Role must be professor or student.';
if ($role === 'student' && !validate_group($group))
    $errors[] = 'Select a valid group.';
if (strlen($password) < 6)
    $errors[] = 'Password must be at least 6 characters.';

if (!empty($errors)) {
    json_out(['errors' => $errors], 422);
}

$pdo = get_db();
$chk = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$chk->execute([$username]);
if ($chk->fetch()) {
    json_out(['errors' => ['Username already taken.']], 422);
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$grp = $role === 'student' ? $group : null;
$ins = $pdo->prepare(
    'INSERT INTO users (username, password_hash, role, full_name, group_name) VALUES (?,?,?,?,?)'
);
$ins->execute([$username, $hash, $role, $full_name, $grp]);
$id = (int) $pdo->lastInsertId();

session_regenerate_id(true);
$_SESSION['user_id'] = $id;
$_SESSION['role'] = $role;

json_out(['user' => [
    'id' => $id,
    'username' => $username,
    'full_name' => $full_name,
    'role' => $role,
    'group_name' => $grp,
]]);
