<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors = [];
$v = ['username' => '', 'full_name' => '', 'role' => 'student', 'group_name' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $role = $_POST['role'] ?? '';
    $group_name = $_POST['group_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $v = compact('username', 'full_name', 'role', 'group_name');

    if (!validate_username($username))
        $errors[] = 'Username: 1–50 chars, letters/digits/underscores only.';
    if ($full_name === '' || strlen($full_name) > 100)
        $errors[] = 'Full name must be 1–100 characters.';
    if (!in_array($role, ['professor', 'student'], true))
        $errors[] = 'Select a valid role.';
    if ($role === 'student' && !validate_group($group_name))
        $errors[] = 'Select a valid group.';
    if (strlen($password) < 6)
        $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)
        $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'Username already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $grp = $role === 'student' ? $group_name : null;
            $ins = $pdo->prepare(
                'INSERT INTO users (username, password_hash, role, full_name, group_name) VALUES (?,?,?,?,?)'
            );
            $ins->execute([$username, $hash, $role, $full_name, $grp]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            $_SESSION['role']    = $role;
            $dest = $role === 'professor' ? 'dashboard_professor.php' : 'dashboard_student.php';
            header('Location: ' . $dest);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register — Grades App</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container form-page">
    <form method="post" class="form-box" id="reg-form">
        <h2>Create Account</h2>
        <?php foreach ($errors as $e): ?>
            <p class="error"><?= h($e) ?></p>
        <?php endforeach; ?>
        <label>Username
            <input type="text" name="username" value="<?= h($v['username']) ?>"
                   required pattern="[a-zA-Z0-9_]{1,50}" autocomplete="username">
        </label>
        <label>Full Name
            <input type="text" name="full_name" value="<?= h($v['full_name']) ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" required minlength="6" autocomplete="new-password">
        </label>
        <label>Confirm Password
            <input type="password" name="confirm_password" required minlength="6" autocomplete="new-password">
        </label>
        <fieldset>
            <legend>Role</legend>
            <label>
                <input type="radio" name="role" value="professor"
                       <?= $v['role'] === 'professor' ? 'checked' : '' ?>> Professor
            </label>
            <label>
                <input type="radio" name="role" value="student"
                       <?= $v['role'] !== 'professor' ? 'checked' : '' ?>> Student
            </label>
        </fieldset>
        <div id="group-field" class="<?= $v['role'] === 'professor' ? 'hidden' : '' ?>">
            <label>Group
                <select name="group_name" id="group-select">
                    <option value="">— select group —</option>
                    <?php foreach (get_groups() as $g): ?>
                        <option value="<?= h($g) ?>" <?= $v['group_name'] === $g ? 'selected' : '' ?>>
                            <?= h($g) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <button type="submit" class="btn">Register</button>
        <p class="form-link"><a href="index.php">Already have an account? Log in</a></p>
    </form>
</div>
<script>
(function () {
    const radios = document.querySelectorAll('input[name="role"]');
    const gField = document.getElementById('group-field');
    const gSelect = document.getElementById('group-select');

    function toggle() {
        const isProfessor = document.querySelector('input[name="role"]:checked')?.value === 'professor';
        gField.classList.toggle('hidden', isProfessor);
        gSelect.required = !isProfessor;
    }

    radios.forEach(function (r) { r.addEventListener('change', toggle); });
    toggle();
}());
</script>
</body>
</html>
