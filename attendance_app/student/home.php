<?php
require_once __DIR__ . '/../scripts/db_connect.php';
require_once __DIR__ . '/../scripts/auth.php';
require_role('student');

$uid = $_SESSION['user']['id'];

$courses = $pdo->prepare("
    SELECT c.* 
    FROM courses c
    JOIN enrollments e ON e.course_id = c.id
    WHERE e.student_id = ?
    ORDER BY c.title
");
$courses->execute([$uid]);
$data = $courses->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Home</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include '../nav.php'; ?>
<div class="container">
    <h2>📚 My Courses</h2>
    <ul class="list">
        <?php if (!empty($data)): foreach ($data as $c): ?>
            <li class="list-item">
                <strong><?= htmlspecialchars($c['title'] ?? '') ?></strong>
                <span class="muted">(<?= htmlspecialchars($c['code'] ?? '') ?>)</span>
                <div class="row">
                    <a class="btn btn-outline" href="attendance.php?course_id=<?= $c['id'] ?>">📅 Attendance</a>
                    <a class="btn btn-outline" href="history.php?course_id=<?= $c['id'] ?>">📖 My History</a>
                </div>
            </li>
        <?php endforeach; else: ?>
            <li class="list-item">No courses enrolled.</li>
        <?php endif; ?>
    </ul>
</div>
</body>
</html>
