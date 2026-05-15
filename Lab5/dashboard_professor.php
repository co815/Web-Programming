<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

require_role('professor');

$pdo = get_db();
$uid = current_user_id();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['course_name'] ?? '');
        if (strlen($name) < 2 || strlen($name) > 100) {
            $msg = 'error:Course name must be 2–100 characters.';
        } else {
            $pdo->prepare('INSERT INTO courses (name, professor_id) VALUES (?,?)')
                ->execute([$name, $uid]);
            $msg = 'success:Course created.';
        }
    } elseif ($action === 'delete') {
        $cid = (int)($_POST['course_id'] ?? 0);
        $chk = $pdo->prepare('SELECT id FROM courses WHERE id = ? AND professor_id = ?');
        $chk->execute([$cid, $uid]);
        if ($chk->fetch()) {
            $pdo->prepare('DELETE FROM courses WHERE id = ? AND professor_id = ?')->execute([$cid, $uid]);
            $msg = 'success:Course deleted.';
        }
    }

    header('Location: dashboard_professor.php?msg=' . urlencode($msg));
    exit;
}

$msg = $_GET['msg'] ?? '';
[$msgType, $msgText] = (strpos($msg, ':') !== false) ? explode(':', $msg, 2) : ['', $msg];
$msgType = in_array($msgType, ['error', 'success'], true) ? $msgType : '';

$stmt = $pdo->prepare('SELECT id, name FROM courses WHERE professor_id = ? ORDER BY name');
$stmt->execute([$uid]);
$courses = $stmt->fetchAll();

$me = $pdo->prepare('SELECT full_name FROM users WHERE id = ?');
$me->execute([$uid]);
$professor = $me->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — Professor</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <header class="page-header">
        <h1>My Courses</h1>
        <span class="muted"><?= h($professor['full_name']) ?></span>
        <a href="logout.php" class="btn btn-secondary">Log Out</a>
    </header>

    <?php if ($msgText !== ''): ?>
        <p class="<?= h($msgType) ?>"><?= h($msgText) ?></p>
    <?php endif; ?>

    <section class="card">
        <h2>Add Course</h2>
        <form method="post" class="inline-form">
            <input type="hidden" name="action" value="create">
            <input type="text" name="course_name" placeholder="Course name" required
                   minlength="2" maxlength="100">
            <button type="submit" class="btn">Add</button>
        </form>
    </section>

    <section class="card">
        <h2>Course List</h2>
        <?php if (empty($courses)): ?>
            <p class="muted">No courses yet. Add one above.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Course</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($courses as $c): ?>
                    <tr>
                        <td><?= h($c['name']) ?></td>
                        <td class="actions">
                            <a href="course_grades.php?course_id=<?= (int)$c['id'] ?>"
                               class="btn btn-small">Manage Grades</a>
                            <form method="post" style="display:inline"
                                  onsubmit="return confirm('Delete course and all its grades?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="course_id" value="<?= (int)$c['id'] ?>">
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
