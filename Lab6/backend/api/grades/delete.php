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

$student_id = (int)($_GET['student_id'] ?? 0);
$course_id = (int)($_GET['course_id'] ?? 0);
$pdo = get_db();
$uid = current_user_id();

$own = $pdo->prepare('SELECT id FROM courses WHERE id = ? AND professor_id = ?');
$own->execute([$course_id, $uid]);
if (!$own->fetch()) {
    json_out(['error' => 'Forbidden'], 403);
}

$pdo->prepare('DELETE FROM grades WHERE student_id = ? AND course_id = ?')
    ->execute([$student_id, $course_id]);

json_out(['ok' => true]);
