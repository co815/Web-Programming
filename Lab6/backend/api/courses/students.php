<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_out(['error' => 'Method not allowed'], 405);
}

if (empty($_SESSION['user_id']) || current_role() !== 'professor') {
    json_out(['error' => 'Forbidden'], 403);
}

$course_id = (int)($_GET['course_id'] ?? 0);
$group = $_GET['group'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 4;
$offset = ($page - 1) * $per_page;

if (!validate_group($group) || $course_id <= 0) {
    json_out(['error' => 'Invalid parameters'], 400);
}

$pdo = get_db();
$uid = current_user_id();

$own = $pdo->prepare('SELECT id FROM courses WHERE id = ? AND professor_id = ?');
$own->execute([$course_id, $uid]);
if (!$own->fetch()) {
    json_out(['error' => 'Forbidden'], 403);
}

$cnt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role = ? AND group_name = ?');
$cnt->execute(['student', $group]);
$total = (int) $cnt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT u.id, u.full_name, g.grade
     FROM users u
     LEFT JOIN grades g ON g.student_id = u.id AND g.course_id = ?
     WHERE u.role = ? AND u.group_name = ?
     ORDER BY u.full_name
     LIMIT $per_page OFFSET $offset"
);
$stmt->execute([$course_id, 'student', $group]);

json_out([
    'students' => $stmt->fetchAll(),
    'total'=> $total,
    'page' => $page,
    'per_page' => $per_page,
]);
