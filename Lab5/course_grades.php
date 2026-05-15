<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

require_role('professor');

$pdo = get_db();
$uid = current_user_id();

$course_id = (int)($_GET['course_id'] ?? 0);

$chk = $pdo->prepare('SELECT id, name FROM courses WHERE id = ? AND professor_id = ?');
$chk->execute([$course_id, $uid]);
$course = $chk->fetch();

if (!$course) {
    header('Location: dashboard_professor.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $student_id = (int)($_POST['student_id'] ?? 0);
    $ret_group = $_POST['current_group']  ?? '';
    $ret_page = max(1, (int)($_POST['current_page'] ?? 1));
    $msg = '';

    $group_ok = validate_group($ret_group);

    if ($action === 'save_grade') {
        $grade = $_POST['grade'] ?? '';
        if (!validate_grade($grade)) {
            $msg = 'error:Grade must be a number between 1 and 10.';
        } elseif (!$group_ok) {
            $msg = 'error:Invalid group.';
        } else {
            $sv = $pdo->prepare('SELECT id FROM users WHERE id = ? AND role = ? AND group_name = ?');
            $sv->execute([$student_id, 'student', $ret_group]);
            if ($sv->fetch()) {
                $pdo->prepare(
                    'INSERT INTO grades (student_id, course_id, grade) VALUES (?,?,?)
                     ON DUPLICATE KEY UPDATE grade = ?'
                )->execute([$student_id, $course_id, (float)$grade, (float)$grade]);
                $msg = 'success:Grade saved.';
            }
        }
    } elseif ($action === 'delete_grade') {
        if ($group_ok) {
            $sv = $pdo->prepare('SELECT id FROM users WHERE id = ? AND role = ? AND group_name = ?');
            $sv->execute([$student_id, 'student', $ret_group]);
            if ($sv->fetch()) {
                $pdo->prepare('DELETE FROM grades WHERE student_id = ? AND course_id = ?')
                    ->execute([$student_id, $course_id]);
                $msg = 'success:Grade deleted.';
            }
        }
    }

    $qs = http_build_query([
        'course_id' => $course_id,
        'group' => $ret_group,
        'page' => $ret_page,
        'msg' => $msg,
    ]);
    header('Location: course_grades.php?' . $qs);
    exit;
}

$msg = $_GET['msg'] ?? '';
[$msgType, $msgText] = (strpos($msg, ':') !== false) ? explode(':', $msg, 2) : ['', $msg];
$msgType = in_array($msgType, ['error', 'success'], true) ? $msgType : '';
$initGroup = $_GET['group'] ?? '';
$initPage  = max(1, (int)($_GET['page'] ?? 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grades — <?= h($course['name']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <header class="page-header">
        <h1>Grades: <?= h($course['name']) ?></h1>
        <a href="dashboard_professor.php" class="btn btn-secondary">← Back</a>
    </header>

    <?php if ($msgText !== ''): ?>
        <p class="<?= h($msgType) ?>"><?= h($msgText) ?></p>
    <?php endif; ?>

    <section class="card">
        <div id="group-row">
            <label for="group-select">Select Group:</label>
            <select id="group-select">
                <option value="">— choose group —</option>
                <?php foreach (get_groups() as $g): ?>
                    <option value="<?= h($g) ?>" <?= $initGroup === $g ? 'selected' : '' ?>>
                        <?= h($g) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </section>

    <section id="students-section" class="card hidden">
        <div id="students-list"></div>
        <div id="pagination" class="pagination"></div>
    </section>
</div>

<div id="grade-modal" class="modal hidden" role="dialog" aria-modal="true">
    <button class="modal-close" id="modal-close-btn" title="Close">&#x2715;</button>
    <h2 id="modal-student-name"></h2>
    <form method="post" id="grade-form">
        <input type="hidden" name="action" value="save_grade">
        <input type="hidden" name="student_id" id="modal-student-id">
        <input type="hidden" name="current_group" id="modal-current-group">
        <input type="hidden" name="current_page" id="modal-current-page">
        <label>Grade (1–10)
            <input type="number" name="grade" id="modal-grade"
                   min="1" max="10" step="0.01" required>
        </label>
        <div class="modal-actions">
            <button type="submit" class="btn">Save</button>
            <button type="button" id="delete-btn" class="btn btn-danger hidden">Delete Grade</button>
        </div>
    </form>
    <form method="post" id="delete-form" style="display:none">
        <input type="hidden" name="action" value="delete_grade">
        <input type="hidden" name="student_id" id="del-student-id">
        <input type="hidden" name="current_group" id="del-current-group">
        <input type="hidden" name="current_page" id="del-current-page">
    </form>
</div>
<div id="modal-overlay" class="modal-overlay hidden"></div>

<script>
(function () {
    var COURSE_ID = <?= json_encode($course_id) ?>;
    var currentGroup = '';
    var currentPage = <?= json_encode($initPage) ?>;

    var groupSel = document.getElementById('group-select');
    var studentsSection = document.getElementById('students-section');
    var studentsList = document.getElementById('students-list');
    var paginationEl = document.getElementById('pagination');
    var modal = document.getElementById('grade-modal');
    var overlay = document.getElementById('modal-overlay');

    function loadStudents(group, page) {
        if (!group) { return; }
        currentGroup = group;
        currentPage  = page;
        var url = 'ajax/get_students.php?group=' + encodeURIComponent(group)
                + '&course_id=' + COURSE_ID + '&page=' + page;
        fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (data) { renderStudents(data); })
            .catch(function () {
                studentsSection.classList.remove('hidden');
                studentsList.textContent = '';
                var p = document.createElement('p');
                p.className = 'error';
                p.textContent = 'Failed to load students.';
                studentsList.appendChild(p);
                paginationEl.textContent = '';
            });
    }

    function renderStudents(data) {
        studentsSection.classList.remove('hidden');
        studentsList.textContent = '';
        paginationEl.textContent = '';

        if (!data.students || data.students.length === 0) {
            var p = document.createElement('p');
            p.className = 'muted';
            p.textContent = 'No students in this group.';
            studentsList.appendChild(p);
            return;
        }

        data.students.forEach(function (s) {
            var card = document.createElement('div');
            card.className = 'student-card';
            var grade = s.grade !== null ? parseFloat(s.grade) : null;
            card.addEventListener('click', function () {
                openModal(parseInt(s.id, 10), s.full_name, grade);
            });

            var nameSpan = document.createElement('span');
            nameSpan.className = 'student-name';
            nameSpan.textContent = s.full_name;

            var gradeSpan = document.createElement('span');
            gradeSpan.className = grade !== null ? 'student-grade' : 'no-grade';
            gradeSpan.textContent = grade !== null ? grade.toFixed(2) : '—';

            card.appendChild(nameSpan);
            card.appendChild(gradeSpan);
            studentsList.appendChild(card);
        });

        var totalPages = Math.ceil(data.total / data.per_page);

        if (data.page > 1) {
            var prev = document.createElement('button');
            prev.className = 'btn btn-small';
            prev.textContent = '← Prev';
            (function (p) {
                prev.addEventListener('click', function () { loadStudents(currentGroup, p); });
            }(data.page - 1));
            paginationEl.appendChild(prev);
        }

        var info = document.createElement('span');
        info.className = 'page-info';
        info.textContent = 'Page ' + data.page + ' / ' + totalPages + ' (' + data.total + ' students)';
        paginationEl.appendChild(info);

        if (data.page < totalPages) {
            var next = document.createElement('button');
            next.className = 'btn btn-small';
            next.textContent = 'Next →';
            (function (p) {
                next.addEventListener('click', function () { loadStudents(currentGroup, p); });
            }(data.page + 1));
            paginationEl.appendChild(next);
        }
    }

    function openModal(studentId, name, grade) {
        document.getElementById('modal-student-name').textContent = name;
        document.getElementById('modal-student-id').value = studentId;
        document.getElementById('del-student-id').value = studentId;
        document.getElementById('modal-current-group').value = currentGroup;
        document.getElementById('modal-current-page').value = currentPage;
        document.getElementById('del-current-group').value = currentGroup;
        document.getElementById('del-current-page').value = currentPage;
        document.getElementById('modal-grade').value = grade !== null ? grade : '';

        var deleteBtn = document.getElementById('delete-btn');
        deleteBtn.classList.toggle('hidden', grade === null);

        modal.classList.remove('hidden');
        overlay.classList.remove('hidden');
        document.getElementById('modal-grade').focus();
    }

    function closeModal() {
        modal.classList.add('hidden');
        overlay.classList.add('hidden');
    }

    document.getElementById('modal-close-btn').addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);

    document.getElementById('delete-btn').addEventListener('click', function () {
        if (confirm('Delete this grade?')) {
            document.getElementById('delete-form').submit();
        }
    });

    groupSel.addEventListener('change', function () {
        loadStudents(this.value, 1);
    });

    if (groupSel.value) {
        loadStudents(groupSel.value, currentPage);
    }
}());
</script>
</body>
</html>
