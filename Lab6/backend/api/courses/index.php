<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../cors.php';

if (empty($_SESSION['user_id']) || current_role() !== 'professor') {
    json_out(['error' => 'Forbidden'], 403);
}

$pdo = get_db();
$uid = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT id, name FROM courses WHERE professor_id = ? ORDER BY name');
    $stmt->execute([$uid]);
    json_out(['courses' => $stmt->fetchAll()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_body();
    $name = trim($body['name'] ?? '');
    if (strlen($name) < 2 || strlen($name) > 100) {
        json_out(['error' => 'Course name must be 2–100 characters.'], 422);
    }
    $stmt = $pdo->prepare('INSERT INTO courses (name, professor_id) VALUES (?,?)');
    $stmt->execute([$name, $uid]);
    $id = (int) $pdo->lastInsertId();
    json_out(['course' => ['id' => $id, 'name' => $name]], 201);
}

json_out(['error' => 'Method not allowed'], 405);
