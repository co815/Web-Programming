<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (current_role() !== 'professor') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$group = $_GET['group'] ?? '';
$course_id = (int)($_GET['course_id'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 4;
$offset = ($page - 1) * $per_page;

if (!validate_group($group) || $course_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$pdo = get_db();

$own = $pdo->prepare('SELECT id FROM courses WHERE id = ? AND professor_id = ?');
$own->execute([$course_id, current_user_id()]);
if (!$own->fetch()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
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
$students = $stmt->fetchAll();

echo json_encode([
    'students' => $students,
    'total' => $total,
    'page' => $page,
    'per_page' => $per_page,
]);
