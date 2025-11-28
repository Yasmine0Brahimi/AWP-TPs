<?php
require_once __DIR__ . '/scripts/db_connect.php';
require_once __DIR__ . '/scripts/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
            header("Location: home.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login - Attendance App</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="centered">
    <div class="card">
        <h2>Attendance App</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" id="loginForm">
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="row">
            <a class="link" href="scripts/init_schema.php">Initialize schema</a>
            <a class="link" href="scripts/seed_demo_data.php">Seed demo data</a>
        </div>
    </div>
</body>
</html>
