<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

require_role('student');

$pdo = get_db();
$uid = current_user_id();

$stmt = $pdo->prepare(
    'SELECT c.name AS course_name, u.full_name AS professor_name, g.grade
     FROM grades g
     JOIN courses c ON c.id = g.course_id
     JOIN users u ON u.id = c.professor_id
     WHERE g.student_id = ?
     ORDER BY c.name'
);
$stmt->execute([$uid]);
$grades = $stmt->fetchAll();

$me = $pdo->prepare('SELECT full_name, group_name FROM users WHERE id = ?');
$me->execute([$uid]);
$student = $me->fetch();
if (!$student) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Grades</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <header class="page-header">
        <h1>My Grades</h1>
        <span class="muted">
            <?= h($student['full_name']) ?> &nbsp;&middot;&nbsp; Group <?= h($student['group_name']) ?>
        </span>
        <a href="logout.php" class="btn btn-secondary">Log Out</a>
    </header>

    <?php if (empty($grades)): ?>
        <div class="card"><p class="muted">No grades assigned yet.</p></div>
    <?php else: ?>
        <div class="card" style="padding:0; overflow:hidden;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Professor</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grades as $g): ?>
                    <tr>
                        <td><?= h($g['course_name']) ?></td>
                        <td><?= h($g['professor_name']) ?></td>
                        <td class="grade-cell"><?= h(number_format((float)$g['grade'], 2)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
