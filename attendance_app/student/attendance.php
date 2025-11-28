<?php
require_once __DIR__ . '/../scripts/db_connect.php';
require_once __DIR__ . '/../scripts/auth.php';
require_role('student');

$uid = $_SESSION['user']['id'];
$course_id = (int)($_GET['course_id'] ?? 0);

$course = $pdo->prepare("SELECT * FROM courses WHERE id=?");
$course->execute([$course_id]);
$c = $course->fetch();
if (!$c) die("Course not found.");

$rows = $pdo->prepare("
    SELECT s.session_date, ar.status
    FROM attendance_records ar
    JOIN attendance_sessions s ON s.id=ar.session_id
    WHERE ar.student_id=? AND s.course_id=?
    ORDER BY s.session_date DESC
");
$rows->execute([$uid, $course_id]);
$data = $rows->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>My attendance</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include '../nav.php'; ?>
<div class="container">
    <h2><?= htmlspecialchars($c['title']) ?> — My attendance</h2>
    <table class="table">
        <tr><th>Date</th><th>Status</th></tr>
        <?php if (!empty($data)): foreach ($data as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['session_date']) ?></td>
            <td><?= htmlspecialchars($r['status']) ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="2">No attendance records yet.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
