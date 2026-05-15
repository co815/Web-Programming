<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    $dest = current_role() === 'professor' ? 'dashboard_professor.php' : 'dashboard_student.php';
    header('Location: ' . $dest);
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '') {
        $pdo  = get_db();
        $stmt = $pdo->prepare('SELECT id, password_hash, role FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $dest = $user['role'] === 'professor' ? 'dashboard_professor.php' : 'dashboard_student.php';
            header('Location: ' . $dest);
            exit;
        }
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Grades App</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container form-page">
    <form method="post" class="form-box">
        <h1>Grades App</h1>
        <h2>Log In</h2>
        <?php if ($error !== ''): ?>
            <p class="error"><?= h($error) ?></p>
        <?php endif; ?>
        <label>Username
            <input type="text" name="username" required autofocus autocomplete="username">
        </label>
        <label>Password
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button type="submit" class="btn">Log In</button>
        <p class="form-link"><a href="register.php">No account? Register</a></p>
    </form>
</div>
</body>
</html>
