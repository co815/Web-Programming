<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['error' => 'Method not allowed'], 405);
}

if (empty($_SESSION['user_id']) || current_role() !== 'professor') {
    json_out(['error' => 'Forbidden'], 403);
}

$body = json_body();
$student_id = (int)($body['student_id'] ?? 0);
$course_id = (int)($body['course_id'] ?? 0);
$grade = (string)($body['grade'] ?? '');

if (!validate_grade($grade)) {
    json_out(['error' => 'Grade must be a number between 1 and 10.'], 422);
}

$pdo = get_db();
$uid = current_user_id();

$own = $pdo->prepare('SELECT id FROM courses WHERE id = ? AND professor_id = ?');
$own->execute([$course_id, $uid]);
if (!$own->fetch()) {
    json_out(['error' => 'Forbidden'], 403);
}

$sv = $pdo->prepare('SELECT id FROM users WHERE id = ? AND role = ?');
$sv->execute([$student_id, 'student']);
if (!$sv->fetch()) {
    json_out(['error' => 'Student not found'], 404);
}

$pdo->prepare(
    'INSERT INTO grades (student_id, course_id, grade) VALUES (?,?,?)
     ON DUPLICATE KEY UPDATE grade = ?'
)->execute([$student_id, $course_id, (float)$grade, (float)$grade]);

json_out(['ok' => true]);
