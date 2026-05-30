<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_out(['error' => 'Method not allowed'], 405);
}

if (empty($_SESSION['user_id']) || current_role() !== 'student') {
    json_out(['error' => 'Forbidden'], 403);
}

$pdo = get_db();
$stmt = $pdo->prepare(
    'SELECT c.name AS course_name, u.full_name AS professor_name, g.grade
     FROM grades g
     JOIN courses c ON c.id = g.course_id
     JOIN users u ON u.id = c.professor_id
     WHERE g.student_id = ?
     ORDER BY c.name'
);
$stmt->execute([current_user_id()]);
json_out(['grades' => $stmt->fetchAll()]);
