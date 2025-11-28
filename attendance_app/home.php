<?php
require_once __DIR__ . '/scripts/auth.php';
if (empty($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$role = role();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include __DIR__ . '/nav.php'; ?>
<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?></h2>
    <p>Select your section:</p>
    <div class="grid">
        <?php if ($role === 'teacher'): ?>
            <a class="card link-card" href="teacher/home.php">Teacher</a>
        <?php endif; ?>
        <?php if ($role === 'student'): ?>
            <a class="card link-card" href="student/home.php">Student</a>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
            <a class="card link-card" href="admin/home.php">Admin</a>
            <a class="card link-card" href="admin/statistics.php">Statistics</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
