<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    json_out(['error' => 'Method not allowed'], 405);
}

if (empty($_SESSION['user_id']) || current_role() !== 'professor') {
    json_out(['error' => 'Forbidden'], 403);
}

$id = (int)($_GET['id'] ?? 0);
$uid = current_user_id();
$pdo = get_db();

$chk = $pdo->prepare('SELECT id FROM courses WHERE id = ? AND professor_id = ?');
$chk->execute([$id, $uid]);
if (!$chk->fetch()) {
    json_out(['error' => 'Not found'], 404);
}

$pdo->prepare('DELETE FROM courses WHERE id = ? AND professor_id = ?')->execute([$id, $uid]);
json_out(['ok' => true]);
